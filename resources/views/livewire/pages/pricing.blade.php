<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
new #[Layout('layouts.glass-app')] class extends Component {}; ?>

<div class="max-w-3xl mx-auto px-4 py-12 space-y-8">

    <div class="text-center space-y-3">
        <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-br from-green-500 to-teal-500 items-center justify-center mx-auto">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-800">Pricing &amp; Donations</h1>
        <p class="text-gray-500">Listening is free. Supporting artists is priceless.</p>
    </div>

    {{-- Listener --}}
    <div class="glass-card rounded-2xl p-7 space-y-4">
        <h2 class="text-xl font-bold text-gray-800">For Listeners</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="bg-white/50 rounded-xl p-4 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black text-gray-800">Free</span>
                    <span class="text-xs bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full">Forever</span>
                </div>
                <p class="text-sm text-gray-600">Stream all free tracks, browse artists, build playlists, like songs, and enjoy the full catalogue — no subscription needed.</p>
            </div>
            <div class="bg-white/50 rounded-xl p-4 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black text-gray-800">Donate</span>
                    <span class="text-xs bg-red-100 text-red-600 font-semibold px-2 py-0.5 rounded-full">Your choice</span>
                </div>
                <p class="text-sm text-gray-600">Some tracks are donation-locked. The artist sets a minimum. Pay once, unlock forever, and 100% of your donation goes to the artist (minus Stripe's processing fee).</p>
            </div>
        </div>
    </div>

    {{-- Artist --}}
    <div class="glass-card rounded-2xl p-7 space-y-4">
        <h2 class="text-xl font-bold text-gray-800">For Artists</h2>
        <div class="bg-white/50 rounded-xl p-4 space-y-3">
            <div class="flex items-center gap-2">
                <span class="text-2xl font-black text-gray-800">$0</span>
                <span class="text-xs bg-violet-100 text-violet-700 font-semibold px-2 py-0.5 rounded-full">GrinMuzik takes nothing</span>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed">We charge no commission. Upload unlimited tracks, build your profile, and receive donations directly. Stripe charges a standard processing fee (typically ~2.9% + $0.30 per transaction), which comes out of each donation before it reaches your account.</p>
        </div>
        <ul class="text-sm text-gray-600 space-y-2 list-none">
            @foreach([
                'Unlimited track uploads',
                'Set your own donation amounts per track',
                'Mix of free + paid tracks',
                'Full artist profile with bio, photo, banner',
                'Album organisation',
                'Direct Stripe payouts',
            ] as $feature)
            <li class="flex items-center gap-2">
                <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                {{ $feature }}
            </li>
            @endforeach
        </ul>
        <a href="{{ route('register') }}" wire:navigate
           class="inline-flex items-center gap-2 glass-btn-primary glass-btn-primary-hover px-5 py-2.5 rounded-xl text-sm font-semibold">
            Start for free
        </a>
    </div>

    <p class="text-xs text-center text-gray-400">Stripe payment processing fees are set by Stripe, not GrinMuzik. See <a href="https://stripe.com/pricing" target="_blank" class="underline hover:text-gray-600">stripe.com/pricing</a> for current rates.</p>

</div>
