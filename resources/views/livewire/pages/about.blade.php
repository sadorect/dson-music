<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
new #[Layout('layouts.glass-app')] class extends Component {}; ?>

<div class="max-w-3xl mx-auto px-4 py-12 space-y-8">

    <div class="text-center space-y-3">
        <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-br from-red-500 to-pink-500 items-center justify-center mx-auto">
            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M9 3v10.55A4 4 0 107 17V7h8V3H9z"/></svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-800">About GrinMuzik</h1>
        <p class="text-gray-500 max-w-xl mx-auto">Independent music. Real artists. Support the creators you love.</p>
    </div>

    <div class="glass-card rounded-2xl p-7 space-y-5 text-gray-700 leading-relaxed">
        <h2 class="text-xl font-bold text-gray-800">Our Mission</h2>
        <p>GrinMuzik was built for one simple reason: independent artists deserve a platform that puts <strong>them first</strong>. In a landscape dominated by algorithmic curration and major-label deals, we believe the most exciting music comes from artists who make it on their own terms.</p>
        <p>We provide a place where musicians can upload their work, build a following, and actually earn a living — directly from the fans who love them — through donations, exclusive track unlocks, and direct support.</p>
    </div>

    <div class="glass-card rounded-2xl p-7 space-y-5 text-gray-700 leading-relaxed">
        <h2 class="text-xl font-bold text-gray-800">For Listeners</h2>
        <p>Discover artists you won't find anywhere else. Browse, search, and stream freely. When you find something you love, go further — support the artist directly, unlock exclusive tracks, and build playlists that tell your story.</p>
    </div>

    <div class="glass-card rounded-2xl p-7 space-y-5 text-gray-700 leading-relaxed">
        <h2 class="text-xl font-bold text-gray-800">For Artists</h2>
        <p>Upload your tracks, set up your profile, and reach listeners who are actively seeking new music. You decide what's free and what can be unlocked with a donation. No gatekeepers. No hidden algorithms suppressing your reach. Just you and your audience.</p>
        <a href="{{ route('register') }}" wire:navigate
           class="inline-flex items-center gap-2 glass-btn-primary glass-btn-primary-hover px-5 py-2.5 rounded-xl text-sm font-semibold mt-2">
            Join as an Artist
        </a>
    </div>

    <div class="glass-card rounded-2xl p-7 space-y-4 text-gray-700 leading-relaxed">
        <h2 class="text-xl font-bold text-gray-800">Built With Care</h2>
        <p>GrinMuzik is an independent project. We're a small team passionate about music and technology. Questions, partnerships, or feedback? <a href="{{ route('contact') }}" wire:navigate class="text-red-500 hover:text-red-700 font-medium">Get in touch →</a></p>
    </div>

</div>
