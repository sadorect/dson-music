<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component
{
    public function with(): array
    {
        return [
            'seo' => [
                'title' => 'About',
                'description' => 'Learn how GrinMuzik helps independent artists share music, reach listeners, and earn direct support.',
                'canonical' => route('about'),
                'json_ld' => [
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'AboutPage',
                        'name' => 'About GrinMuzik',
                        'url' => route('about'),
                        'description' => 'Learn how GrinMuzik helps independent artists share music, reach listeners, and earn direct support.',
                    ],
                ],
            ],
            'principles' => [
                [
                    'title' => 'Artist-first economics',
                    'body' => 'Artists decide what is free, what is donation-locked, and how they want to present their work without being squeezed by subscription-era gatekeeping.',
                ],
                [
                    'title' => 'Listener-led discovery',
                    'body' => 'Listeners can browse, search, chart, like, and build playlists around taste instead of being trapped in one narrow funnel.',
                ],
                [
                    'title' => 'Direct support',
                    'body' => 'When a fan wants to go deeper, support flows straight to the creator through direct donations and unlocks.',
                ],
            ],
            'audiences' => [
                [
                    'title' => 'For listeners',
                    'body' => 'Discover music you are unlikely to find in the mainstream cycle, save what matters, and support the artists behind it.',
                    'cta' => route('browse'),
                    'label' => 'Browse Music',
                ],
                [
                    'title' => 'For artists',
                    'body' => 'Upload tracks, build a public profile, organize releases, and grow an audience that is actively looking for independent music.',
                    'cta' => route('register'),
                    'label' => 'Join as Artist',
                ],
            ],
        ];
    }
};
?>

<div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl space-y-6">
        <section class="glass-card relative overflow-hidden rounded-[2rem] px-6 py-8 sm:px-8 sm:py-10">
            <div class="absolute inset-y-0 right-0 hidden w-1/3 bg-gradient-to-l from-primary/10 to-transparent lg:block"></div>

            <div class="relative z-10 grid gap-8 lg:grid-cols-[minmax(0,1.35fr),minmax(260px,0.75fr)] lg:items-end">
                <div class="max-w-3xl">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">About GrinMuzik</p>
                    <h1 class="text-3xl font-black tracking-tight text-gray-900 sm:text-5xl">Independent music deserves a better home.</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-relaxed text-gray-600 sm:text-base">
                        GrinMuzik exists to give independent artists a cleaner path to audiences, discovery, and direct support without burying them under major-label economics.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    @foreach([
                        ['label' => 'Independent', 'value' => 'Artist-first'],
                        ['label' => 'Discovery', 'value' => 'Human-curated'],
                        ['label' => 'Support', 'value' => 'Direct-to-artist'],
                    ] as $metric)
                        <div class="rounded-2xl border border-white/60 bg-white/80 px-4 py-3 shadow-sm">
                            <p class="text-lg font-black text-gray-900">{{ $metric['value'] }}</p>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-400">{{ $metric['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-3">
            @foreach($principles as $principle)
                <article class="glass-card rounded-[1.8rem] p-6">
                    <div class="mb-4 inline-flex rounded-full border border-white/70 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-primary-600">
                        Principle
                    </div>
                    <h2 class="text-lg font-black tracking-tight text-gray-900">{{ $principle['title'] }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ $principle['body'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="glass-card rounded-[2rem] p-6 sm:p-8">
            <div class="grid gap-8 lg:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Why We Built It</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900 sm:text-3xl">A platform shaped around creators, not just content inventory.</h2>
                </div>
                <div class="space-y-4 text-sm leading-relaxed text-gray-600 sm:text-base">
                    <p>Too much of modern streaming reduces music to interchangeable catalog volume. GrinMuzik is designed to restore context: artist identity, listener intent, and the relationship between discovery and support.</p>
                    <p>That means public artist pages, donation unlocks, playlists, charts, new releases, and a cleaner path from hearing a track to backing the person who made it.</p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            @foreach($audiences as $audience)
                <article class="glass-card rounded-[2rem] p-6 sm:p-8">
                    <div class="inline-flex rounded-full border border-white/70 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-primary-600">
                        Audience
                    </div>
                    <h2 class="mt-4 text-2xl font-black tracking-tight text-gray-900">{{ $audience['title'] }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-gray-600 sm:text-base">{{ $audience['body'] }}</p>
                    <a href="{{ $audience['cta'] }}"
                       wire:navigate
                       class="mt-5 inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500">
                        {{ $audience['label'] }}
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </article>
            @endforeach
        </section>

        <section class="glass-card rounded-[2rem] p-6 text-center sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Build With Us</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900 sm:text-3xl">Questions, feedback, partnerships, or artist support?</h2>
            <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-gray-600 sm:text-base">
                GrinMuzik is still being shaped with every release. If you want to collaborate, report issues, or help improve the platform, get in touch directly.
            </p>
            <a href="{{ route('contact') }}"
               wire:navigate
               class="mt-5 inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/80 px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-white">
                Contact Us
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </section>
    </div>
</div>
