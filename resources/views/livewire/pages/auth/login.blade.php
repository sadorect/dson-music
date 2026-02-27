<?php

use App\Livewire\Forms\LoginForm;
use App\Rules\CaptchaAnswer;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;
    public string $captcha = '';

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate([
            'captcha' => ['required', 'integer', new CaptchaAnswer],
        ]);

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-800">Welcome back</h1>
        <p class="text-sm text-gray-500 mt-1">Sign in to your GrinMusic account</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-4">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember" class="flex items-center gap-2 cursor-pointer">
                <input wire:model="form.remember" id="remember" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-red-500 focus:ring-red-400" name="remember">
                <span class="text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm text-red-500 hover:underline" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <x-math-captcha wire:model="captcha" error-bag="captcha" />

        <x-primary-button class="w-full justify-center mt-2">
            <span wire:loading.remove wire:target="login">{{ __('Sign In') }}</span>
            <span wire:loading wire:target="login">Signing in…</span>
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Don't have an account?
        <a href="{{ route('register') }}" wire:navigate class="text-red-500 font-medium hover:underline">Sign up free</a>
    </p>
</div>
