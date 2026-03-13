<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component
{
    public function with(): array
    {
        return [
            'seo' => [
                'title' => 'Artist Guide',
                'description' => 'Learn how to set up your artist profile, upload music, and grow on GrinMuzik.',
                'canonical' => route('artist-guide'),
                'json_ld' => [
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'HowTo',
                        'name' => 'How to get started as an artist on GrinMuzik',
                        'description' => 'A step-by-step guide for artists joining GrinMuzik.',
                        'step' => [
                            ['@type' => 'HowToStep', 'name' => 'Create an account'],
                            ['@type' => 'HowToStep', 'name' => 'Set up your artist profile'],
                            ['@type' => 'HowToStep', 'name' => 'Upload tracks and organize releases'],
                            ['@type' => 'HowToStep', 'name' => 'Configure donations and share your music'],
                        ],
                    ],
                ],
            ],
            'steps' => [
                [
                    'number' => '1',
                    'title' => 'Create your account',
                    'body' => 'Register for free, choose the artist role, and unlock the artist dashboard and publishing workflow.',
                ],
                [
                    'number' => '2',
                    'title' => 'Set up your public profile',
                    'body' => 'Add a stage name, bio, profile image, banner, and genre signals so listeners immediately understand your sound.',
                ],
                [
                    'number' => '3',
                    'title' => 'Upload tracks and releases',
                    'body' => 'Publish songs with cover art, metadata, genre, and descriptions, then group related tracks into albums when needed.',
                ],
                [
                    'number' => '4',
                    'title' => 'Choose your access model',
                    'body' => 'Keep tracks free for open discovery or set a donation amount when you want fans to unlock them directly.',
                ],
                [
                    'number' => '5',
                    'title' => 'Share and grow',
                    'body' => 'Promote your public artist page, show up in charts and new releases, and build momentum through consistent publishing.',
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

            <div class="relative z-10 grid gap-8 lg:grid-cols-[minmax(0,1.2fr),minmax(280px,0.8fr)] lg:items-end">
                <div class="max-w-3xl">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Artist Guide</p>
                    <h1 class="text-3xl font-black tracking-tight text-gray-900 sm:text-5xl">Everything an independent artist needs to launch on GrinMuzik.</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-relaxed text-gray-600 sm:text-base">
                        From profile setup to uploads, donations, and promotion, this is the core workflow for getting your music live and discoverable.
                    </p>
                </div>

                <div class="rounded-[1.8rem] border border-white/60 bg-white/80 p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary/70">At a Glance</p>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-gray-600">
                        <div class="rounded-2xl bg-white/80 px-4 py-3">
                            <p class="text-lg font-black text-gray-900">Free</p>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-400">To start</p>
                        </div>
                        <div class="rounded-2xl bg-white/80 px-4 py-3">
                            <p class="text-lg font-black text-gray-900">Direct</p>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-400">Support flow</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-5">
            @foreach($steps as $step)
                <article class="glass-card rounded-[1.8rem] p-5 lg:col-span-1">
                    <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-black text-white">
                        {{ $step['number'] }}
                    </div>
                    <h2 class="mt-4 text-lg font-black tracking-tight text-gray-900">{{ $step['title'] }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ $step['body'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <article class="glass-card rounded-[2rem] p-6 sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Publishing Tips</p>
                <ul class="mt-4 space-y-3 text-sm leading-relaxed text-gray-600">
                    <li class="flex items-start gap-3"><span class="mt-0.5 text-primary">•</span><span>Use strong cover art and a clean description for each release.</span></li>
                    <li class="flex items-start gap-3"><span class="mt-0.5 text-primary">•</span><span>Keep your artist bio current so your page feels active and credible.</span></li>
                    <li class="flex items-start gap-3"><span class="mt-0.5 text-primary">•</span><span>Balance free discovery tracks with donation-unlock releases where it makes sense.</span></li>
                    <li class="flex items-start gap-3"><span class="mt-0.5 text-primary">•</span><span>Share your public artist page and individual track links consistently.</span></li>
                </ul>
            </article>

            <article class="glass-card rounded-[2rem] p-6 sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Where Discovery Happens</p>
                <p class="mt-4 text-sm leading-relaxed text-gray-600 sm:text-base">
                    Strong new releases can surface on the homepage, in charts, through public playlists, and across artist and track pages. Consistency matters more than a single upload spike.
                </p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('charts') }}" wire:navigate class="rounded-full border border-white/70 bg-white/80 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-white">View Charts</a>
                    <a href="{{ route('new-releases') }}" wire:navigate class="rounded-full border border-white/70 bg-white/80 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-white">See New Releases</a>
                </div>
            </article>
        </section>

        <section class="glass-card rounded-[2rem] p-6 text-center sm:p-8">
            <h2 class="text-2xl font-black tracking-tight text-gray-900">Ready to put your catalog in front of listeners?</h2>
            <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-gray-600 sm:text-base">
                Create your artist account, build your page, and start publishing music with a direct support path built in.
            </p>
            <a href="{{ route('register') }}"
               wire:navigate
               class="mt-5 inline-flex items-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500">
                Create Artist Account
            </a>
        </section>
    </div>
</div>
