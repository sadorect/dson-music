<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UrlOrInternalPath implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (is_string($value) && str_starts_with($value, '/')) {
            return;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return;
        }

        $fail('Use a full URL like https://example.com or an internal path like /browse.');
    }
}
