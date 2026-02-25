<x-guest-layout>
    <div class="flex flex-col gap-6">

        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-500/20 rounded-full mb-4">
                <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-white text-2xl font-bold">Two-Factor Verification</h1>
            <p class="text-white/50 text-sm mt-1">Enter the 6-digit code from your authenticator app.</p>
        </div>

        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-lg px-4 py-3">{{ session('error') }}</div>
        @endif

        @if(session('warning'))
            <div class="bg-yellow-500/10 border border-yellow-500/30 text-yellow-400 text-sm rounded-lg px-4 py-3">{{ session('warning') }}</div>
        @endif

        <form method="POST" action="{{ route('2fa.verify') }}" x-data="{ useRecovery: false }">
            @csrf

            <div x-show="!useRecovery">
                <x-input-label for="code" value="Authentication Code" />
                <x-text-input id="code" name="code" type="text" inputmode="numeric"
                              pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code"
                              class="block mt-1 w-full text-center text-2xl tracking-[0.5em] font-mono"
                              placeholder="000000" autofocus />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div x-show="useRecovery" x-cloak>
                <x-input-label for="recovery_code" value="Recovery Code" />
                <x-text-input id="recovery_code" name="recovery_code" type="text"
                              class="block mt-1 w-full font-mono tracking-widest text-center uppercase"
                              placeholder="XXXX-XXXX" />
                <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-col gap-3">
                <x-primary-button class="w-full justify-center">
                    Verify Identity
                </x-primary-button>

                <button type="button" @click="useRecovery = !useRecovery"
                        class="text-sm text-white/40 hover:text-orange-400 transition-colors text-center">
                    <span x-text="useRecovery ? '← Use authenticator app instead' : 'Use a recovery code instead'"></span>
                </button>
            </div>
        </form>

        <div class="text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-white/30 hover:text-white/60 transition-colors">
                    Sign out and return to login
                </button>
            </form>
        </div>

    </div>
</x-guest-layout>
