<x-guest-layout>
    <div class="flex flex-col gap-6">

        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-500/20 rounded-full mb-4">
                <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-white text-2xl font-bold">Set Up Two-Factor Auth</h1>
            <p class="text-white/50 text-sm mt-1">Scan the QR code with your authenticator app, then enter the 6-digit code to confirm.</p>
        </div>

        {{-- Step 1: Scan QR --}}
        <div class="bg-white/5 border border-white/10 rounded-xl p-5">
            <p class="text-white/70 text-sm font-medium mb-4">1 — Scan with Google Authenticator or Authy</p>
            <div class="flex justify-center">
                {{-- QR code image (generated via external service using the URL) --}}
                <img src="https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl={{ urlencode($qrCodeUrl) }}"
                     alt="2FA QR Code" class="rounded-lg border-4 border-white w-48 h-48">
            </div>
            <p class="text-white/40 text-xs text-center mt-3">Can't scan? Enter this key manually:</p>
            <p class="text-orange-400 font-mono text-sm text-center tracking-widest mt-1 select-all break-all">{{ $secret }}</p>
        </div>

        {{-- Step 2: Confirm --}}
        <form method="POST" action="{{ route('2fa.enable') }}" class="flex flex-col gap-4">
            @csrf

            <p class="text-white/70 text-sm font-medium">2 — Enter the 6-digit code to activate</p>

            <div>
                <x-input-label for="code" value="Authentication Code" />
                <x-text-input id="code" name="code" type="text" inputmode="numeric"
                              pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code"
                              class="block mt-1 w-full text-center text-2xl tracking-[0.5em] font-mono"
                              placeholder="000000" />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" value="Confirm Your Password" />
                <x-text-input id="password" name="password" type="password"
                              class="block mt-1 w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center">
                Enable Two-Factor Authentication
            </x-primary-button>
        </form>

    </div>
</x-guest-layout>
