<x-guest-layout>
    <h1 class="text-black text-3xl text-center py-4 font-semibold">Confirm Password</h1>
    <div class="mb-4 text-sm text-black/70">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Math CAPTCHA -->
        <x-math-captcha />

        <div class="flex justify-start sm:justify-end mt-6">
            <x-primary-button class="sm:w-auto sm:px-6">
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
