<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CaptchaAnswer implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $expected = session('captcha_answer');

        if ($expected === null || (int) $value !== (int) $expected) {
            $fail('The security answer is incorrect. Please try again.');
        }

        // Regenerate so re-submission requires a new answer
        session()->forget('captcha_answer');
    }
}
