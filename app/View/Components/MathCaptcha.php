<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MathCaptcha extends Component
{
    public int $a;
    public int $b;
    public string $question;

    public function __construct()
    {
        $this->a = random_int(2, 12);
        $this->b = random_int(1, 10);
        $this->question = "{$this->a} + {$this->b}";

        // Store correct answer in session so backend can validate
        session(['captcha_answer' => $this->a + $this->b]);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.math-captcha');
    }
}
