<x-guest-layout>
    <div class="flex flex-col gap-6">

        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-500/20 rounded-full mb-4">
                <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h1 class="text-white text-2xl font-bold">Recovery Codes</h1>
            <p class="text-white/50 text-sm mt-1">
                {{ $remainingCount }} of 8 codes remaining. Store these somewhere safe — each can only be used once.
            </p>
        </div>

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 text-sm rounded-lg px-4 py-3">{{ session('success') }}</div>
        @endif

        @if($remainingCount <= 2)
            <div class="bg-yellow-500/10 border border-yellow-500/30 text-yellow-300 text-sm rounded-lg px-4 py-3 flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
                </svg>
                <span>Only {{ $remainingCount }} code(s) left. Regenerate your codes now.</span>
            </div>
        @endif

        {{-- Codes grid --}}
        <div class="bg-white/5 border border-white/10 rounded-xl p-5">
            @if(empty($recoveryCodes))
                <p class="text-white/40 text-sm text-center">All recovery codes have been used.</p>
            @else
                <div class="grid grid-cols-2 gap-2">
                    @foreach($recoveryCodes as $code)
                        <code class="bg-black/30 text-orange-300 font-mono text-sm px-3 py-2 rounded-lg tracking-wider text-center select-all">
                            {{ $code }}
                        </code>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Regenerate form --}}
        <form method="POST" action="{{ route('2fa.regenerate-codes') }}" class="flex flex-col gap-4">
            @csrf
            <p class="text-white/50 text-xs">Regenerating will invalidate all current codes and create 8 new ones.</p>

            <div>
                <x-input-label for="password" value="Confirm Your Password to Regenerate" />
                <x-text-input id="password" name="password" type="password"
                              class="block mt-1 w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <button type="submit"
                    class="w-full py-2.5 rounded-xl border border-orange-500/40 text-orange-400 text-sm font-medium hover:bg-orange-500/10 transition-colors">
                Regenerate Recovery Codes
            </button>
        </form>

        <a href="{{ route('dashboard') }}" class="text-center text-xs text-white/30 hover:text-white/60 transition-colors">
            ← Back to dashboard
        </a>

    </div>
</x-guest-layout>
