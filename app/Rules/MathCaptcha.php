<?php

namespace App\Rules;

use App\Services\CaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MathCaptcha implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * Checks the submitted captcha_answer against the session-stored answer.
     * The session captcha is cleared by CaptchaService::validate(), so a fresh
     * question will be generated on the next page render.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! CaptchaService::validate((string) $value)) {
            $fail('Incorrect answer. Please solve the security question and try again.');
        }
    }
}
