<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\MathCaptcha;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'           => ['required', 'string', 'email'],
            'password'        => ['required', 'string'],
            'captcha_answer'  => ['required', 'string', new MathCaptcha],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // DB-level lockout check (separate from RateLimiter)
        $dbUser = User::where('email', $this->email)->first();
        if ($dbUser && $dbUser->locked_until && now()->lt($dbUser->locked_until)) {
            $minutes = (int) now()->diffInMinutes($dbUser->locked_until) + 1;
            throw ValidationException::withMessages([
                'email' => "Your account is temporarily locked. Please try again in {$minutes} minute(s).",
            ]);
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Increment DB failure counter; lock after 5 consecutive failures
            if ($dbUser) {
                $attempts = ($dbUser->failed_login_attempts ?? 0) + 1;
                $update = ['failed_login_attempts' => $attempts];
                if ($attempts >= 5) {
                    $update['locked_until'] = now()->addMinutes(30);
                }
                $dbUser->update($update);
            }

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
