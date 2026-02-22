<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CaptchaService
{
    /**
     * Generate a new math captcha question, store it in session, and return the question string.
     */
    public static function generate(): string
    {
        $a = rand(2, 9);
        $b = rand(2, 9);
        $ops = ['+', '-', '×'];
        $op = $ops[array_rand($ops)];

        // Keep subtraction non-negative
        if ($op === '-' && $b > $a) {
            [$a, $b] = [$b, $a];
        }

        $answer = match ($op) {
            '+'  => $a + $b,
            '-'  => $a - $b,
            '×'  => $a * $b,
        };

        $question = "What is {$a} {$op} {$b}?";

        Session::put('captcha_question', $question);
        Session::put('captcha_answer', (string) $answer);

        return $question;
    }

    /**
     * Return the current session question, generating one if it doesn't exist yet.
     */
    public static function getQuestion(): string
    {
        if (! Session::has('captcha_question')) {
            return self::generate();
        }

        return Session::get('captcha_question');
    }

    /**
     * Validate the submitted answer against the session-stored answer.
     * Always clears the captcha from session after checking (force a new one on next render).
     */
    public static function validate(string $submitted): bool
    {
        $expected = Session::get('captcha_answer');

        // Always clear after one attempt
        Session::forget('captcha_question');
        Session::forget('captcha_answer');

        return $expected !== null && trim($submitted) === $expected;
    }
}
