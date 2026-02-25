<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PasswordPolicyMiddleware
{
    /**
     * Routes that are exempt from the password-change requirement.
     */
    protected array $exempt = [
        'password.change',
        'password.update-forced',
        'logout',
        '2fa.challenge',
        '2fa.verify',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Bypass for routes that handle the password change itself
        if (in_array($request->route()?->getName(), $this->exempt)) {
            return $next($request);
        }

        // Force password change if flagged by admin or if password is too old
        if ($user->must_change_password) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You must change your password before continuing.',
                    'redirect' => route('password.change'),
                ], 403);
            }

            return redirect()->route('password.change')
                ->with('warning', 'You must change your password before continuing.');
        }

        // Optional: force change after N days (configurable)
        $maxAgeDays = config('auth.password_max_age_days', 0);

        if ($maxAgeDays > 0 && $user->password_changed_at) {
            $daysSinceChange = now()->diffInDays($user->password_changed_at);

            if ($daysSinceChange >= $maxAgeDays) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Your password has expired. Please update it.',
                        'redirect' => route('password.change'),
                    ], 403);
                }

                return redirect()->route('password.change')
                    ->with('warning', 'Your password has expired. Please set a new one.');
            }
        }

        return $next($request);
    }
}
