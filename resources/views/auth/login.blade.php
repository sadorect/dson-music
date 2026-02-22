<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class='flex flex-col gap-4'>

   <form method="POST" action="{{ route('login') }}" >
        @csrf


        <!-- HEADING -->
         <h1 class="text-black text-3xl text-center py-4 font-semibold">Hy 👋, Welcome back</h1>
         <p class="text-center text-sm text-black/60 mb-2">Log in to continue your music journey.</p>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center ">
                <input id="remember_me" type="checkbox" class="rounded border-black/20 text-primary-color shadow-sm focus:ring-primary-color" name="remember">
                <span class="ms-2 text-sm text-black/60">{{ __('Remember me') }}</span>
            </label>
        </div>

        <!-- Math CAPTCHA -->
        <x-math-captcha />

        <div class="flex flex-col gap-4 mt-6">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-black/60 hover:text-orange-600 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-color" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="">
                {{ __('Log in') }}
            </x-primary-button>

            @if (Route::has('register'))
                <a class="underline text-sm text-black/70 hover:text-orange-600 text-center rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-color" href="{{ route('register') }}">
                    {{ __('Dont have an account? Sign Up') }}
                </a>
            @endif
        </div>
    </form>


   </div>
</x-guest-layout>
