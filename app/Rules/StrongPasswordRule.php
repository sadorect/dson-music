<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class StrongPasswordRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('The :attribute must be a string.');
            return;
        }

        // Minimum length
        if (strlen($value) < 8) {
            $fail('The :attribute must be at least 8 characters long.');
            return;
        }

        // Maximum length
        if (strlen($value) > 128) {
            $fail('The :attribute must not exceed 128 characters.');
            return;
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $value)) {
            $fail('The :attribute must contain at least one uppercase letter.');
            return;
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $value)) {
            $fail('The :attribute must contain at least one lowercase letter.');
            return;
        }

        // Check for at least one number
        if (!preg_match('/[0-9]/', $value)) {
            $fail('The :attribute must contain at least one number.');
            return;
        }

        // Check for at least one special character
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:"\\|,.<>\/?]/', $value)) {
            $fail('The :attribute must contain at least one special character.');
            return;
        }

        // Check for common weak patterns
        $this->checkWeakPatterns($value, $fail);

        // Check for sequential characters
        $this->checkSequentialCharacters($value, $fail);

        // Check for repeated characters
        $this->checkRepeatedCharacters($value, $fail);

        // Check against common password list (basic implementation)
        $this->checkCommonPasswords($value, $fail);
    }

    private function checkWeakPatterns(string $password, Closure $fail): void
    {
        $weakPatterns = [
            '/^password/i',
            '/^123/',
            '/^qwerty/i',
            '/^admin/i',
            '/^letmein/i',
            '/^welcome/i',
            '/^monkey/i',
            '/^dragon/i',
            '/^master/i',
            '/^sunshine/i',
        ];

        foreach ($weakPatterns as $pattern) {
            if (preg_match($pattern, $password)) {
                $fail('The :attribute contains a common weak pattern. Please choose a more secure password.');
                return;
            }
        }
    }

    private function checkSequentialCharacters(string $password, Closure $fail): void
    {
        // Check for sequential numbers (123, 456, etc.)
        if (preg_match('/(?:012|123|234|345|456|567|678|789)/', $password)) {
            $fail('The :attribute should not contain sequential numbers.');
            return;
        }

        // Check for sequential letters (abc, bcd, etc.)
        if (preg_match('/(?:abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz)/i', $password)) {
            $fail('The :attribute should not contain sequential letters.');
            return;
        }

        // Check for keyboard sequences
        $keyboardSequences = ['qwerty', 'asdf', 'zxcv', 'qwertyuiop', 'asdfghjkl', 'zxcvbnm'];
        foreach ($keyboardSequences as $sequence) {
            if (strpos(strtolower($password), $sequence) !== false) {
                $fail('The :attribute should not contain keyboard sequences.');
                return;
            }
        }
    }

    private function checkRepeatedCharacters(string $password, Closure $fail): void
    {
        // Check for 3 or more repeated characters
        if (preg_match('/(.)\1{2,}/', $password)) {
            $fail('The :attribute should not contain three or more repeated characters in a row.');
            return;
        }
    }

    private function checkCommonPasswords(string $password, Closure $fail): void
    {
        $commonPasswords = [
            'password', '123456', 'password123', 'admin', 'qwerty',
            'letmein', 'welcome', 'monkey', 'dragon', 'master',
            'sunshine', 'princess', 'football', 'baseball', 'shadow',
            'superman', 'azerty', '123qwe', 'qwerty123', '123abc',
            'password1', 'iloveyou', '1234', 'abc123', '111111',
            '123123', 'dragon1', 'baseball1', 'football1', 'shadow1',
            'superman1', 'azerty1', 'qwertyuiop', 'password1234'
        ];

        if (in_array(strtolower($password), $commonPasswords)) {
            $fail('The :attribute is too common. Please choose a more secure password.');
            return;
        }
    }
}