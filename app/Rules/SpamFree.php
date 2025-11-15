<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SpamFree implements ValidationRule
{
    protected array $blockedPhrases = [
        'buy now',
        'work from home',
        'visit my profile',
        'click here',
        'http://',
        'https://',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $content = strtolower($value);

        foreach ($this->blockedPhrases as $phrase) {
            if (str_contains($content, $phrase)) {
                $fail('Your comment looks like spam. Please edit and try again.');
                return;
            }
        }

        if ($this->hasRepeatedCharacters($content)) {
            $fail('Please avoid using excessive repeated characters in comments.');
            return;
        }

        if ($this->hasExcessiveMentions($content)) {
            $fail('Too many mentions detected. Please keep comments readable.');
        }
    }

    protected function hasRepeatedCharacters(string $content): bool
    {
        return (bool) preg_match('/(.)\\1{6,}/u', $content);
    }

    protected function hasExcessiveMentions(string $content): bool
    {
        preg_match_all('/@/u', $content, $matches);
        return count($matches[0]) > 5;
    }
}
