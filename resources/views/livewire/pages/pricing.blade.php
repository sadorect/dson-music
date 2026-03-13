<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component
{
    public function with(): array
    {
        return [
            'seo' => [
                'title' => 'Pricing and Donations',
                'description' => 'See how free listening, donation unlocks, and artist earnings work on GrinMuzik.',
                'canonical' => route('pricing'),
                'json_ld' => [
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'WebPage',
                        'name' => 'Pricing and Donations',
                        'url' => route('pricing'),
                        'description' => 'See how free listening, donation unlocks, and artist earnings work on GrinMuzik.',
                    ],
                ],
            ],
            'listenerPoints' => [
                'Stream free tracks with no subscription.',
                'Use likes, playlists, charts, and search without a paid tier.',
                'Unlock donation-gated tracks only when you choose to support an artist.',
            ],
            'artistPoints' => [
                'Upload tracks and albums without a platform commission.',
                'Set donation amounts per track when you want deeper fan support.',
                'Keep ownership of your music and your audience presence.',
            ],
        ];
    }
};
?>

<div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl space-y-6">
        <section class="glass-card relative overflow-hidden rounded-[2rem] px-6 py-8 sm:px-8 sm:py-10">
            <div class="absolute inset-y-0 right-0 hidden w-1/3 bg-gradient-to-l from-emerald-200/30 to-transparent lg:block"></div>

            <div class="relative z-10 grid gap-8 lg:grid-cols-[minmax(0,1.35fr),minmax(260px,0.8fr)] lg:items-end">
                <div class="max-w-3xl">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Pricing and Donations</p>
                    <h1 class="text-3xl font-black tracking-tight text-gray-900 sm:text-5xl">Listening stays open. Support stays direct.</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-relaxed text-gray-600 sm:text-base">
                        GrinMuzik is built so listeners can explore music freely while artists can still earn directly when fans want to unlock and support specific tracks.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    @foreach([
                        ['label' => 'Listeners', 'value' => 'Free'],
                        ['label' => 'Platform Cut', 'value' => '$0'],
                        ['label' => 'Support Model', 'value' => 'Donations'],
                    ] as $metric)
                        <div class="rounded-2xl border border-white/60 bg-white/80 px-4 py-3 shadow-sm">
                            <p class="text-lg font-black text-gray-900">{{ $metric['value'] }}</p>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-400">{{ $metric['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <article class="glass-card rounded-[2rem] p-6 sm:p-8">
                <div class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-emerald-700">
                    For Listeners
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <p class="text-4xl font-black tracking-tight text-gray-900">Free</p>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Forever</span>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-gray-600 sm:text-base">
                    Browse the catalog, play tracks, follow artists, build playlists, and use the discovery features without paying a subscription fee.
                </p>
                <ul class="mt-5 space-y-3 text-sm text-gray-600">
                    @foreach($listenerPoints as $point)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </article>

            <article class="glass-card rounded-[2rem] p-6 sm:p-8">
                <div class="inline-flex rounded-full bg-primary/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-primary-700">
                    For Artists
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <p class="text-4xl font-black tracking-tight text-gray-900">$0</p>
                    <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary-700">No GrinMuzik commission</span>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-gray-600 sm:text-base">
                    Artists keep control over how their music is shared and monetized. Stripe processing fees still apply to donations, but GrinMuzik does not take a platform cut.
                </p>
                <ul class="mt-5 space-y-3 text-sm text-gray-600">
                    @foreach($artistPoints as $point)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary-700">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </article>
        </section>

        <section class="grid gap-4 lg:grid-cols-3">
            @foreach([
                ['title' => 'Free tracks', 'body' => 'Any track marked free can be streamed immediately by listeners without payment.'],
                ['title' => 'Donation unlocks', 'body' => 'Artists can set a minimum donation to unlock certain tracks while keeping others open.'],
                ['title' => 'Stripe processing', 'body' => 'Payment processing is handled by Stripe. Their fees are separate from the platform itself.'],
            ] as $item)
                <article class="glass-card rounded-[1.8rem] p-6">
                    <h2 class="text-lg font-black tracking-tight text-gray-900">{{ $item['title'] }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ $item['body'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="glass-card rounded-[2rem] p-6 text-center sm:p-8">
            <h2 class="text-2xl font-black tracking-tight text-gray-900">Ready to publish without a platform tax?</h2>
            <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-gray-600 sm:text-base">
                Set up an artist account, upload your catalog, and decide how you want fans to experience and support your work.
            </p>
            <div class="mt-5 flex flex-wrap justify-center gap-3">
                <a href="{{ route('register') }}"
                   wire:navigate
                   class="inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500">
                    Start for Free
                </a>
                <a href="https://stripe.com/pricing"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/80 px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-white">
                    View Stripe Pricing
                </a>
            </div>
        </section>
    </div>
</div>
