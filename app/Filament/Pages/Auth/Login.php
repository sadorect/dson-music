<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public string $captchaQuestion = '';

    public function mount(): void
    {
        parent::mount();
        $this->generateCaptcha();
    }

    protected function generateCaptcha(): void
    {
        $a = random_int(2, 12);
        $b = random_int(1, 10);
        $this->captchaQuestion = "{$a} + {$b}";
        session(['filament_captcha_answer' => $a + $b]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
                $this->getCaptchaFormComponent(),
            ]);
    }

    protected function getCaptchaFormComponent(): TextInput
    {
        return TextInput::make('captcha')
            ->label(fn (): string => "Security check: what is {$this->captchaQuestion} ?")
            ->required()
            ->integer()
            ->inputMode('numeric')
            ->autocomplete('off')
            ->placeholder('Your answer');
    }

    public function authenticate(): ?LoginResponse
    {
        $captchaInput = (int) ($this->data['captcha'] ?? '');
        $expected     = session('filament_captcha_answer');

        if ($expected === null || $captchaInput !== (int) $expected) {
            $this->generateCaptcha();

            throw ValidationException::withMessages([
                'data.captcha' => 'The security answer is incorrect. Please try again.',
            ]);
        }

        session()->forget('filament_captcha_answer');

        return parent::authenticate();
    }
}
