<?php

use App\Models\Track;
use App\Models\ArtistProfile;
use App\Models\Genre;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.glass-app')] class extends Component {
    public function with(): array
    {
        return [
            'trending' => Track::with(['artistProfile.user'])
                ->where('is_published', true)
                ->orderByDesc('play_count')
                ->take(16)
                ->get(),

            'latest' => Track::with(['artistProfile.user'])
                ->where('is_published', true)
                ->orderByDesc('created_at')
                ->take(8)
                ->get(),

            'featuredArtists' => ArtistProfile::with('user')
                ->where('is_approved', true)
                ->orderByDesc('followers_count')
                ->take(6)
                ->get(),

            'genres' => Genre::withCount(['tracks' => fn($q) => $q->where('is_published', true)])
                ->where('is_active', true)
                ->orderByDesc('tracks_count')
                ->take(12)
                ->get(),

            'stats' => [
                'tracks'  => Track::where('is_published', true)->count(),
                'artists' => ArtistProfile::where('is_approved', true)->count(),
                'genres'  => Genre::where('is_active', true)->count(),
            ],
        ];
    }
};
?>

@push('styles')
<style>
/* ── Trending ticker ── */
@keyframes ticker {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.ticker-track {
    display: flex;
    width: max-content;
    animation: ticker 35s linear infinite;
}
.ticker-track.paused { animation-play-state: paused; }

/* ── Count-up numbers ── */
@keyframes countUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}
.stat-num { animation: countUp 0.6s ease-out forwards; }

/* ── Feature cards float in ── */
@keyframes floatIn {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}
.float-card { opacity: 0; }
.float-card.visible { animation: floatIn 0.5s ease-out forwards; }

/* ── Genre pill pulse ring ── */
.genre-pill:hover::before {
    content: '';
    position: absolute;
    inset: -3px;
    border-radius: 9999px;
    background: inherit;
    opacity: 0.25;
    animation: pulse-ring 0.6s ease-out;
}
@keyframes pulse-ring {
    from { transform: scale(1); opacity: 0.4; }
    to   { transform: scale(1.18); opacity: 0; }
}

/* ── Music note bounce ── */
@keyframes noteBounce {
    0%, 100% { transform: translateY(0) rotate(-8deg); }
    50%       { transform: translateY(-10px) rotate(6deg); }
}
.note-1 { animation: noteBounce 3s ease-in-out infinite; }
.note-2 { animation: noteBounce 3.5s ease-in-out infinite 0.5s; }
.note-3 { animation: noteBounce 4s ease-in-out infinite 1s; }

/* ── Wave divider ── */
.wave-divider svg { display: block; }
</style>
@endpush

<div class="min-h-screen overflow-x-hidden">

    {{-- ── Hero ───────────────────────────────────────────── --}}
    <section class="relative flex items-center justify-center min-h-[56vh] text-center px-4 overflow-hidden">
        {{-- Decorative blobs --}}
        <div class="absolute -top-32 -left-32 w-[500px] h-[500px] bg-red-400 rounded-full blur-3xl opacity-[0.08] pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-[500px] h-[500px] bg-rose-300 rounded-full blur-3xl opacity-[0.08] pointer-events-none"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-red-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>

        {{-- Floating music notes --}}
        <span class="note-1 absolute top-16 left-[12%] text-3xl select-none opacity-20 pointer-events-none">♪</span>
        <span class="note-2 absolute top-24 right-[10%] text-4xl select-none opacity-20 pointer-events-none">♫</span>
        <span class="note-3 absolute bottom-20 left-[20%] text-2xl select-none opacity-15 pointer-events-none">♩</span>
        <span class="note-1 absolute bottom-16 right-[18%] text-3xl select-none opacity-15 pointer-events-none">♬</span>

        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-red-50 border border-red-100 text-red-600 text-xs font-semibold px-4 py-1.5 rounded-full mb-5 shadow-sm">
                <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
                {{ $stats['tracks'] }} tracks live now
            </div>
            <h1 class="text-5xl sm:text-7xl font-black text-gray-900 leading-none mb-4 tracking-tight">
                Grin<span class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-rose-400">Muzik</span>
            </h1>
            <p class="text-gray-500 text-lg sm:text-xl max-w-xl mx-auto mb-8">
                Independent music. Real artists. Support the creators you love.
            </p>
            @guest
                <div class="flex flex-wrap gap-3 justify-center">
                    <a href="{{ route('register') }}"
                       class="bg-red-500 hover:bg-red-600 text-white px-8 py-3 rounded-full font-semibold transition shadow-md hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0">
                        Start Listening
                    </a>
                    <a href="{{ route('browse') }}"
                       class="glass-btn glass-btn-hover px-8 py-3 rounded-full font-semibold">
                        Browse Music
                    </a>
                </div>
            @else
                <a href="{{ route('browse') }}"
                   class="bg-red-500 hover:bg-red-600 text-white px-8 py-3 rounded-full font-semibold transition shadow-md hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0">
                    Browse Music
                </a>
            @endguest
        </div>
    </section>

    {{-- ── Stats bar ───────────────────────────────────────── --}}
    <div class="bg-gradient-to-r from-red-500 to-rose-500 py-5 shadow-inner"
         x-data="{ visible: false }"
         x-init="
            const obs = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) { visible = true; obs.disconnect(); }
            }, { threshold: 0.3 });
            obs.observe($el);
         ">
        <div class="max-w-3xl mx-auto grid grid-cols-3 divide-x divide-white/30 text-center text-white">
            @foreach([
                ['n' => $stats['tracks'],  'label' => 'Published Tracks', 'icon' => 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3'],
                ['n' => $stats['artists'], 'label' => 'Artists',         'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['n' => $stats['genres'],  'label' => 'Genres',           'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
            ] as $s)
                <div class="px-6 py-1">
                    <p class="text-2xl sm:text-3xl font-black stat-num" x-show="visible">{{ number_format($s['n']) }}</p>
                    <p class="text-xs sm:text-sm text-white/80 mt-0.5">{{ $s['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Trending ticker ─────────────────────────────────── --}}
    @if($trending->count())
        <section class="py-10 bg-gray-50/80 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-5 flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800">🔥 Trending Now</h2>
                <a href="{{ route('browse', ['sort' => 'popular']) }}" class="text-red-500 hover:text-red-600 text-sm font-medium">See all →</a>
            </div>
            <div
                class="relative overflow-hidden cursor-grab active:cursor-grabbing select-none"
                x-data="{ paused: false }"
                @mouseenter="paused = true"
                @mouseleave="paused = false"
            >
                {{-- Fade edges --}}
                <div class="absolute left-0 top-0 bottom-0 w-16 bg-gradient-to-r from-gray-50/80 to-transparent z-10 pointer-events-none"></div>
                <div class="absolute right-0 top-0 bottom-0 w-16 bg-gradient-to-l from-gray-50/80 to-transparent z-10 pointer-events-none"></div>

                <div class="ticker-track gap-4 px-4" :class="{ 'paused': paused }">
                    {{-- First set --}}
                    @foreach($trending as $track)
                        <div class="group w-36 shrink-0 glass-card overflow-hidden cursor-pointer transition-transform hover:scale-105"
                             @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })">
                            <div class="relative w-36 h-36">
                                @if($track->getFirstMediaUrl('cover'))
                                    <img src="{{ $track->getFirstMediaUrl('cover', 'thumb') }}" alt="{{ $track->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-red-100 to-rose-200 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/></svg>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                    <svg class="w-10 h-10 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </div>
                            <div class="p-2">
                                <p class="text-gray-800 text-xs font-semibold truncate">{{ $track->title }}</p>
                                <p class="text-gray-500 text-[11px] truncate">{{ $track->artistProfile->stage_name ?? ($track->artistProfile->user->name ?? '') }}</p>
                            </div>
                        </div>
                    @endforeach
                    {{-- Duplicate set for seamless loop --}}
                    @foreach($trending as $track)
                        <div class="group w-36 shrink-0 glass-card overflow-hidden cursor-pointer transition-transform hover:scale-105"
                             @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })">
                            <div class="relative w-36 h-36">
                                @if($track->getFirstMediaUrl('cover'))
                                    <img src="{{ $track->getFirstMediaUrl('cover', 'thumb') }}" alt="{{ $track->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-red-100 to-rose-200 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/></svg>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                    <svg class="w-10 h-10 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </div>
                            <div class="p-2">
                                <p class="text-gray-800 text-xs font-semibold truncate">{{ $track->title }}</p>
                                <p class="text-gray-500 text-[11px] truncate">{{ $track->artistProfile->stage_name ?? ($track->artistProfile->user->name ?? '') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">

        {{-- ── New releases ────────────────────────────────── --}}
        @if($latest->count())
            <section class="mb-14 mt-12"
                     x-data="{ shown: [] }"
                     x-init="
                        const obs = new IntersectionObserver((entries) => {
                            entries.forEach(e => { if(e.isIntersecting) shown.push(parseInt(e.target.dataset.idx)); });
                        }, { threshold: 0.1 });
                        document.querySelectorAll('[data-track-row]').forEach(el => obs.observe(el));
                     ">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-2xl font-bold text-gray-800">✨ New Releases</h2>
                    <a href="{{ route('browse') }}" class="text-red-500 hover:text-red-600 text-sm font-medium">See all →</a>
                </div>
                <div class="space-y-2">
                    @foreach($latest as $i => $track)
                        <div class="float-card flex items-center gap-3 p-3 rounded-xl glass-card glass-card-hover cursor-pointer transition group"
                             data-track-row
                             data-idx="{{ $i }}"
                             :class="{ 'visible': shown.includes({{ $i }}) }"
                             style="animation-delay: {{ $i * 60 }}ms"
                             @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })">
                            <span class="w-5 text-center text-gray-400 text-sm shrink-0 group-hover:hidden">{{ $i + 1 }}</span>
                            <svg class="w-5 h-5 text-red-500 hidden group-hover:block shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0">
                                @if($track->getFirstMediaUrl('cover'))
                                    <img src="{{ $track->getFirstMediaUrl('cover', 'thumb') }}" alt="{{ $track->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-red-100 to-rose-200"></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-gray-800 text-sm font-medium truncate">{{ $track->title }}</p>
                                <a href="{{ route('artist.page', $track->artistProfile) }}" @click.stop class="text-gray-500 text-xs hover:text-red-500 truncate">
                                    {{ $track->artistProfile->stage_name ?? ($track->artistProfile->user->name ?? '') }}
                                </a>
                            </div>
                            <span class="text-gray-400 text-xs shrink-0 hidden sm:block">{{ $track->created_at->diffForHumans(null, true) }} ago</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── Browse by Genre ─────────────────────────────── --}}
        @if($genres->count())
            <section class="mb-14">
                <h2 class="text-2xl font-bold text-gray-800 mb-5">🎵 Browse by Genre</h2>
                <div class="flex flex-wrap gap-3">
                    @php
                        $fallbackColors = ['#ef4444','#f97316','#eab308','#22c55e','#06b6d4','#6366f1','#ec4899','#8b5cf6','#14b8a6','#f43f5e','#84cc16','#0ea5e9'];
                    @endphp
                    @foreach($genres as $idx => $genre)
                        @php $color = $genre->color ?: $fallbackColors[$idx % count($fallbackColors)]; @endphp
                        <a href="{{ route('browse', ['genre' => $genre->slug]) }}"
                           class="genre-pill relative inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold text-white shadow-sm transition hover:scale-105 hover:shadow-md active:scale-95"
                           style="background: {{ $color }};">
                            {{ $genre->name }}
                            @if($genre->tracks_count > 0)
                                <span class="bg-white/25 text-white text-[10px] px-1.5 py-0.5 rounded-full">{{ $genre->tracks_count }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── Featured Artists ────────────────────────────── --}}
        @if($featuredArtists->count())
            <section class="mb-14"
                     x-data="{ ready: false }"
                     x-init="
                        const obs = new IntersectionObserver((entries) => {
                            if (entries[0].isIntersecting) { ready = true; obs.disconnect(); }
                        }, { threshold: 0.1 });
                        obs.observe($el);
                     ">
                <h2 class="text-2xl font-bold text-gray-800 mb-5">🎤 Featured Artists</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
                    @foreach($featuredArtists as $i => $artist)
                        <a href="{{ route('artist.page', $artist) }}"
                           class="float-card flex flex-col items-center gap-2 p-4 rounded-xl glass-card glass-card-hover transition text-center"
                           :class="{ 'visible': ready }"
                           style="animation-delay: {{ $i * 80 }}ms">
                            <div class="w-16 h-16 rounded-full overflow-hidden bg-gradient-to-br from-red-100 to-rose-200 ring-2 ring-white shadow-sm">
                                @if($artist->getFirstMediaUrl('avatar'))
                                    <img src="{{ $artist->getFirstMediaUrl('avatar', 'thumb') }}" alt="{{ $artist->stage_name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-xl font-black text-red-400">
                                        {{ strtoupper(substr($artist->stage_name ?? $artist->user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-gray-800 text-xs font-semibold">{{ $artist->stage_name ?? $artist->user->name }}</p>
                                @if($artist->is_verified)
                                    <p class="text-red-500 text-[10px] font-medium">✓ Verified</p>
                                @else
                                    <p class="text-gray-400 text-[10px]">Artist</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── How it works ────────────────────────────────── --}}
        <section class="mb-14"
                 x-data="{ ready: false }"
                 x-init="
                    const obs = new IntersectionObserver((entries) => {
                        if (entries[0].isIntersecting) { ready = true; obs.disconnect(); }
                    }, { threshold: 0.1 });
                    obs.observe($el);
                 ">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">How It Works</h2>
            <p class="text-gray-500 text-sm mb-7">Music for everyone. Income for artists.</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                @foreach([
                    ['icon' => '🎧', 'title' => 'Discover', 'desc' => 'Browse thousands of tracks across every genre. Search by artist, mood or vibe.', 'color' => 'from-red-50 to-rose-50', 'border' => 'border-red-100'],
                    ['icon' => '❤️', 'title' => 'Support',  'desc' => 'Unlock premium tracks with a small donation. Every rand goes directly to the artist.', 'color' => 'from-orange-50 to-red-50', 'border' => 'border-orange-100'],
                    ['icon' => '🚀', 'title' => 'Create',   'desc' => 'Are you an artist? Upload your music, build a following and earn from your craft.', 'color' => 'from-rose-50 to-pink-50', 'border' => 'border-rose-100'],
                ] as $i => $step)
                    <div class="float-card glass-card p-6 border {{ $step['border'] }} bg-gradient-to-br {{ $step['color'] }} hover:shadow-lg transition"
                         :class="{ 'visible': ready }"
                         style="animation-delay: {{ $i * 120 }}ms">
                        <div class="text-4xl mb-4">{{ $step['icon'] }}</div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $step['title'] }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ── Artist CTA ──────────────────────────────────── --}}
        @guest
            <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 p-10 text-center shadow-xl mb-6">
                <div class="absolute top-0 left-0 right-0 bottom-0 opacity-10 pointer-events-none">
                    <span class="absolute top-4 left-8 text-6xl note-1">♪</span>
                    <span class="absolute bottom-4 right-8 text-6xl note-2">♫</span>
                    <span class="absolute top-1/2 right-1/4 text-5xl note-3">♩</span>
                </div>
                <div class="relative z-10">
                    <h2 class="text-3xl font-black text-white mb-2">Are you an artist?</h2>
                    <p class="text-white/80 text-base mb-7 max-w-sm mx-auto">Upload your music, grow your audience, and receive donations from your fans — for free.</p>
                    <div class="flex flex-wrap gap-3 justify-center">
                        <a href="{{ route('register') }}"
                           class="bg-white text-red-600 hover:bg-red-50 px-8 py-3 rounded-full font-bold transition shadow-lg hover:-translate-y-0.5 inline-block">
                            Join as Artist
                        </a>
                        <a href="{{ route('browse') }}"
                           class="border border-white/50 text-white hover:bg-white/10 px-8 py-3 rounded-full font-semibold transition inline-block">
                            Just Browsing →
                        </a>
                    </div>
                </div>
            </section>
        @endguest

    </div>
</div>
