<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
new #[Layout('layouts.glass-app')] class extends Component {}; ?>

<div class="max-w-3xl mx-auto px-4 py-12 space-y-8">

    <div class="text-center space-y-3">
        <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-500 items-center justify-center mx-auto">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-800">Artist Guide</h1>
        <p class="text-gray-500">Everything you need to get started and grow on GrinMuzik.</p>
    </div>

    {{-- Step 1 --}}
    <div class="glass-card rounded-2xl p-7 space-y-3">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-red-500 text-white text-sm font-bold flex items-center justify-center shrink-0">1</span>
            <h2 class="text-lg font-bold text-gray-800">Create Your Account</h2>
        </div>
        <p class="text-gray-600 leading-relaxed">Sign up for free at GrinMuzik. During registration you'll be asked whether you're joining as a listener or an artist. Choose <strong>Artist</strong> to unlock your artist dashboard.</p>
        <a href="{{ route('register') }}" wire:navigate class="inline-flex items-center gap-1.5 text-red-500 hover:text-red-700 text-sm font-medium transition">
            Get started →
        </a>
    </div>

    {{-- Step 2 --}}
    <div class="glass-card rounded-2xl p-7 space-y-3">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-red-500 text-white text-sm font-bold flex items-center justify-center shrink-0">2</span>
            <h2 class="text-lg font-bold text-gray-800">Set Up Your Artist Profile</h2>
        </div>
        <p class="text-gray-600 leading-relaxed">Choose a stage name, write a bio, upload a profile photo and a banner. Your artist page is public — anyone can visit it and browse your catalogue. Make a strong first impression.</p>
    </div>

    {{-- Step 3 --}}
    <div class="glass-card rounded-2xl p-7 space-y-3">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-red-500 text-white text-sm font-bold flex items-center justify-center shrink-0">3</span>
            <h2 class="text-lg font-bold text-gray-800">Upload Your Tracks</h2>
        </div>
        <p class="text-gray-600 leading-relaxed">Head to your <strong>Tracks</strong> page and hit <strong>Upload Track</strong>. We support MP3, WAV, FLAC, and OGG formats. Add a cover image, write a title and description, assign a genre, and set a duration. You can also organise tracks into albums.</p>
        <ul class="text-sm text-gray-600 space-y-1.5 mt-2 list-disc list-inside">
            <li>Mark a track as <strong>Free</strong> so anyone can stream it immediately.</li>
            <li>Or require a <strong>donation</strong> to unlock — you set the minimum amount.</li>
            <li>Publish or unpublish any track at any time.</li>
        </ul>
    </div>

    {{-- Step 4 --}}
    <div class="glass-card rounded-2xl p-7 space-y-3">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-red-500 text-white text-sm font-bold flex items-center justify-center shrink-0">4</span>
            <h2 class="text-lg font-bold text-gray-800">Earn From Your Music</h2>
        </div>
        <p class="text-gray-600 leading-relaxed">Donations go directly to you via Stripe. You'll receive payouts to your bank account based on your Stripe settings. GrinMuzik takes no cut — we just charge the Stripe processing fee.</p>
    </div>

    {{-- Step 5 --}}
    <div class="glass-card rounded-2xl p-7 space-y-3">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-red-500 text-white text-sm font-bold flex items-center justify-center shrink-0">5</span>
            <h2 class="text-lg font-bold text-gray-800">Grow Your Audience</h2>
        </div>
        <p class="text-gray-600 leading-relaxed">Share your artist page link with fans. Tracks with high play counts appear in the <a href="{{ route('charts') }}" wire:navigate class="text-red-500 hover:text-red-700">Charts</a>. Freshly uploaded tracks appear in <a href="{{ route('new-releases') }}" wire:navigate class="text-red-500 hover:text-red-700">New Releases</a>. Engage your listeners and keep uploading.</p>
    </div>

    <div class="text-center pt-2">
        <p class="text-sm text-gray-500 mb-3">Ready to share your music with the world?</p>
        <a href="{{ route('register') }}" wire:navigate
           class="inline-flex items-center gap-2 glass-btn-primary glass-btn-primary-hover px-6 py-3 rounded-xl font-semibold">
            Create Artist Account — Free
        </a>
    </div>

</div>
