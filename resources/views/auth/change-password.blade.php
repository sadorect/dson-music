<x-guest-layout>
    <div class="mb-6 text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-orange-100 rounded-full mb-3">
            <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h2 class="text-xl font-semibold text-gray-900">Change Your Password</h2>
        <p class="mt-1 text-sm text-gray-500">
            Your password must be updated before you can continue.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Validation Errors -->
    <x-input-error :messages="$errors->all()" class="mb-4" />

    <form method="POST" action="{{ route('password.change.store') }}" class="space-y-5">
        @csrf

        <!-- Current Password -->
        <div>
            <x-input-label for="current_password" :value="__('Current Password')" />
            <x-text-input
                id="current_password"
                name="current_password"
                type="password"
                class="mt-1 block w-full"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <!-- New Password -->
        <div>
            <x-input-label for="password" :value="__('New Password')" />
            <x-text-input
                id="password"
                name="password"
                type="password"
                class="mt-1 block w-full"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                class="mt-1 block w-full"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button class="w-full justify-center bg-orange-600 hover:bg-orange-700">
                {{ __('Update Password') }}
            </x-primary-button>
        </div>

        <div class="text-center">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-orange-600 underline">
                    {{ __('Log out instead') }}
                </button>
            </form>
        </div>
    </form>
</x-guest-layout>
