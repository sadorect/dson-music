<div>
    @if($unlocked)
        {{-- Already unlocked — show play button --}}
        <button
            @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
            class="flex items-center gap-2 bg-green-600 hover:bg-green-500 text-white px-6 py-3 rounded-full font-semibold transition shadow-lg">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            Play (Unlocked)
        </button>
    @else
        {{-- Stripe payment gate --}}
        <div
            x-data="{
                open: false,
                loading: false,
                processing: false,
                error: null,
                stripe: null,
                elements: null,
                paymentElement: null,

                async init() {
                    this.stripe = Stripe('{{ config('cashier.key') }}');
                },

                async openModal() {
                    @auth
                        this.open = true;
                        this.error = null;
                        await this.$nextTick();
                        await this.mountStripe();
                    @else
                        window.location.href = '{{ route('login') }}';
                    @endauth
                },

                async mountStripe() {
                    this.loading = true;
                    try {
                        const res = await fetch('{{ route('donation.intent', $track) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ type: 'unlock' }),
                        });
                        const data = await res.json();

                        if (data.already_unlocked) {
                            this.open = false;
                            $wire.confirmUnlocked();
                            return;
                        }

                        this.elements = this.stripe.elements({ clientSecret: data.client_secret, appearance: { theme: 'night' } });
                        this.paymentElement = this.elements.create('payment');
                        this.paymentElement.mount(this.$refs.paymentEl);
                    } catch (e) {
                        this.error = 'Could not load payment form. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                },

                async pay() {
                    this.processing = true;
                    this.error = null;
                    const { error } = await this.stripe.confirmPayment({
                        elements: this.elements,
                        redirect: 'if_required',
                    });
                    if (error) {
                        this.error = error.message;
                        this.processing = false;
                    } else {
                        this.open = false;
                        await $wire.confirmUnlocked();
                    }
                },
            }"
        >
            {{-- Trigger button --}}
            <button
                @click="openModal()"
                class="flex items-center gap-2 bg-yellow-500 hover:bg-yellow-400 text-black px-6 py-3 rounded-full font-semibold transition shadow-lg">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18 8h-1V6A5 5 0 0 0 7 6v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2Zm-6 9a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm3.1-9H8.9V6a3.1 3.1 0 1 1 6.2 0v2Z"/>
                </svg>
                Unlock for ${{ number_format($track->donation_amount, 2) }}
            </button>

            {{-- Modal --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @keydown.escape.window="open = false"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                style="display: none;"
            >
                <div class="bg-gray-900 border border-white/10 rounded-2xl p-6 w-full max-w-md shadow-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-white">Unlock Track</h3>
                        <button @click="open = false" class="text-white/50 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="bg-white/5 rounded-xl p-3 mb-4 flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg overflow-hidden shrink-0">
                            @if($track->getFirstMediaUrl('cover'))
                                <img src="{{ $track->getFirstMediaUrl('cover', 'thumb') }}" alt="{{ $track->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-purple-800 to-indigo-900"></div>
                            @endif
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm">{{ $track->title }}</p>
                            <p class="text-white/50 text-xs">{{ $track->artistProfile->stage_name ?? $track->artistProfile->user->name }}</p>
                        </div>
                        <span class="ml-auto text-lg font-bold text-yellow-400">${{ number_format($track->donation_amount, 2) }}</span>
                    </div>

                    {{-- Stripe Elements mount point --}}
                    <div x-show="loading" class="py-8 text-center text-white/50 text-sm">Loading payment form…</div>
                    <div x-ref="paymentEl" x-show="!loading" class="mb-4"></div>

                    <div x-show="error" class="text-red-400 text-sm mb-3" x-text="error"></div>

                    <button
                        @click="pay()"
                        :disabled="processing || loading"
                        class="w-full bg-purple-600 hover:bg-purple-500 disabled:opacity-50 text-white py-3 rounded-xl font-semibold transition"
                    >
                        <span x-show="!processing">Pay & Unlock</span>
                        <span x-show="processing">Processing…</span>
                    </button>

                    <p class="text-center text-white/30 text-xs mt-3">Powered by Stripe · Secure payment</p>
                </div>
            </div>
        </div>

        {{-- Load Stripe.js only on pages that need it --}}
        @once
            <script src="https://js.stripe.com/v3/" defer></script>
        @endonce
    @endif
</div>
