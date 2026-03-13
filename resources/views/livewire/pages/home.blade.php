<?php

use App\Models\ArtistProfile;
use App\Models\Genre;
use App\Models\HomepageBannerSlide;
use App\Models\Playlist;
use App\Models\SiteSetting;
use App\Models\Track;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component {
    public function with(): array
    {
        $siteSettings = Schema::hasTable('site_settings') ? SiteSetting::current() : null;
        $supportsDiscoveryVisibility = SiteSetting::supportsDiscoveryVisibility();
        $supportsDiscoveryOrdering = SiteSetting::supportsDiscoveryOrdering();
        $showHomePersonalized = $supportsDiscoveryVisibility ? ($siteSettings?->show_home_personalized ?? true) : true;
        $showHomeEditorPicks = $supportsDiscoveryVisibility ? ($siteSettings?->show_home_editor_picks ?? true) : true;
        $homeEditorPicksPosition = $supportsDiscoveryOrdering ? ($siteSettings?->home_editor_picks_position ?? 'after-personalized') : 'after-personalized';

        $trending = Cache::remember('home.trending.v1', now()->addMinutes(5), fn () => Track::with(['artistProfile.user'])
            ->where('is_published', true)
            ->orderByDesc('play_count')
            ->take(16)
            ->get());

        $latest = Cache::remember('home.latest.v1', now()->addMinutes(5), fn () => Track::with(['artistProfile.user'])
            ->where('is_published', true)
            ->orderByDesc('created_at')
            ->take(8)
            ->get());

        $editorPicks = $showHomeEditorPicks
            ? Cache::remember('home.editor-picks.v1', now()->addMinutes(5), fn () => Track::query()
                ->with(['artistProfile.user', 'genre'])
                ->where('is_published', true)
                ->where('is_featured', true)
                ->orderByDesc('play_count')
                ->orderByDesc('downloads_count')
                ->take(6)
                ->get())
            : collect();

        $featuredArtists = Cache::remember('home.featured-artists.v2', now()->addMinutes(5), fn () => ArtistProfile::with('user')
            ->approved()
            ->when(ArtistProfile::supportsFeaturedCuration(), fn ($query) => $query->orderByDesc('is_featured'))
            ->orderByDesc('followers_count')
            ->take(6)
            ->get());

        $heroSlides = Cache::remember('home.hero-slides.v1', now()->addMinutes(5), fn () => Schema::hasTable('homepage_banner_slides')
            ? HomepageBannerSlide::query()->active()->get()
            : collect());

        $genres = Cache::remember('home.genres.v1', now()->addMinutes(10), fn () => Genre::withCount(['tracks' => fn ($q) => $q->where('is_published', true)])
            ->where('is_active', true)
            ->orderByDesc('tracks_count')
            ->take(12)
            ->get());

        $stats = Cache::remember('home.stats.v1', now()->addMinutes(5), fn () => [
            'tracks' => Track::where('is_published', true)->count(),
            'artists' => ArtistProfile::where('is_approved', true)->count(),
            'genres' => Genre::where('is_active', true)->count(),
            'playlists' => Playlist::where('is_public', true)->count(),
        ]);

        $personalizedTracks = collect();

        if ($showHomePersonalized && auth()->check()) {
            $user = auth()->user();

            $personalizedTracks = Cache::remember("home.personalized.{$user->id}.v1", now()->addMinutes(5), function () use ($user) {
                $tasteSeedTracks = collect()
                    ->merge($user->likes()->with(['artistProfile.user', 'genre'])->latest('likes.created_at')->take(24)->get())
                    ->merge(
                        $user->playHistory()
                            ->with(['track.artistProfile.user', 'track.genre'])
                            ->latest()
                            ->take(36)
                            ->get()
                            ->pluck('track')
                            ->filter()
                    )
                    ->filter();

                $topGenreIds = $tasteSeedTracks
                    ->pluck('genre_id')
                    ->filter()
                    ->countBy()
                    ->sortDesc()
                    ->keys()
                    ->take(4)
                    ->values();

                $topArtistIds = $tasteSeedTracks
                    ->pluck('artist_profile_id')
                    ->filter()
                    ->countBy()
                    ->sortDesc()
                    ->keys()
                    ->take(4)
                    ->values();

                $excludeTrackIds = $tasteSeedTracks
                    ->pluck('id')
                    ->filter()
                    ->unique()
                    ->values();

                $query = Track::query()
                    ->with(['artistProfile.user', 'genre'])
                    ->where('is_published', true)
                    ->whereNotIn('id', $excludeTrackIds->all());

                if ($topGenreIds->isNotEmpty() || $topArtistIds->isNotEmpty()) {
                    $query->where(function ($nested) use ($topGenreIds, $topArtistIds): void {
                        if ($topGenreIds->isNotEmpty()) {
                            $nested->whereIn('genre_id', $topGenreIds->all());
                        }

                        if ($topArtistIds->isNotEmpty()) {
                            $method = $topGenreIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                            $nested->{$method}('artist_profile_id', $topArtistIds->all());
                        }
                    });
                }

                $tracks = $query
                    ->orderByDesc('is_featured')
                    ->orderByDesc('play_count')
                    ->orderByDesc('downloads_count')
                    ->take(8)
                    ->get();

                if ($tracks->isNotEmpty()) {
                    return $tracks;
                }

                return Track::query()
                    ->with(['artistProfile.user', 'genre'])
                    ->where('is_published', true)
                    ->orderByDesc('is_featured')
                    ->orderByDesc('play_count')
                    ->take(8)
                    ->get();
            });
        }

        return [
            'trending' => $trending,
            'latest' => $latest,
            'editorPicks' => $editorPicks,
            'featuredArtists' => $featuredArtists,
            'heroSlides' => $heroSlides,
            'genres' => $genres,
            'stats' => $stats,
            'personalizedTracks' => $personalizedTracks,
            'showHomePersonalized' => $showHomePersonalized,
            'showHomeEditorPicks' => $showHomeEditorPicks,
            'homeEditorPicksPosition' => $homeEditorPicksPosition,
            'seo' => [
                'title' => 'Independent Music Streaming',
                'description' => 'Stream independent music, discover new releases, explore charts, and support artists directly on GrinMuzik.',
                'canonical' => route('home'),
                'image' => $heroSlides->first()?->background_url,
                'json_ld' => [
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'WebPage',
                        'name' => 'GrinMuzik Home',
                        'url' => route('home'),
                        'description' => 'Discover independent music, explore playlists, and support artists on GrinMuzik.',
                    ],
                ],
            ],
        ];
    }
};
?>

@push('styles')
<style>
@keyframes ticker {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

.ticker-track {
    display: flex;
    width: max-content;
    animation: ticker 35s linear infinite;
}

.ticker-track.paused {
    animation-play-state: paused;
}

@keyframes verticalMarquee {
    0% { transform: translateY(0); }
    100% { transform: translateY(-50%); }
}

.vertical-marquee-track {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    animation: verticalMarquee 28s linear infinite;
}

.vertical-marquee-track.paused {
    animation-play-state: paused;
}

@keyframes countUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.stat-num {
    animation: countUp 0.6s ease-out forwards;
}

@keyframes floatIn {
    from { opacity: 0; transform: translateY(24px); }
    to { opacity: 1; transform: translateY(0); }
}

.float-card {
    opacity: 0;
}

.float-card.visible {
    animation: floatIn 0.5s ease-out forwards;
}

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
    to { transform: scale(1.18); opacity: 0; }
}

@keyframes noteBounce {
    0%, 100% { transform: translateY(0) rotate(-8deg); }
    50% { transform: translateY(-10px) rotate(6deg); }
}

.note-1 { animation: noteBounce 3s ease-in-out infinite; }
.note-2 { animation: noteBounce 3.5s ease-in-out infinite 0.5s; }
.note-3 { animation: noteBounce 4s ease-in-out infinite 1s; }
</style>
@endpush

<div class="min-h-screen overflow-x-hidden">
    @if($heroSlides->isNotEmpty())
        <section class="relative min-h-[62vh] overflow-hidden"
                 x-data="{
                    active: 0,
                    count: {{ $heroSlides->count() }},
                    timer: null,
                    start() {
                        if (this.count < 2 || this.timer) return;
                        this.timer = setInterval(() => this.next(), 7000);
                    },
                    stop() {
                        if (! this.timer) return;
                        clearInterval(this.timer);
                        this.timer = null;
                    },
                    next() {
                        this.active = (this.active + 1) % this.count;
                    },
                    prev() {
                        this.active = (this.active - 1 + this.count) % this.count;
                    }
                 }"
                 x-init="start()"
                 @mouseenter="stop()"
                 @mouseleave="start()">
            @foreach($heroSlides as $index => $slide)
                <div x-show="active === {{ $index }}"
                     x-transition.opacity.duration.700ms
                     class="absolute inset-0">
                    @if($slide->background_url)
                        <img src="{{ $slide->background_url }}"
                             alt="{{ $slide->effective_background_alt }}"
                             class="h-full w-full object-cover">
                    @else
                        <div class="h-full w-full bg-gradient-to-br from-primary-700 via-primary-800 to-primary-950"></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/45 to-black/60"></div>
                </div>
            @endforeach

            <div class="relative z-10 mx-auto flex min-h-[62vh] max-w-7xl items-center px-4 py-16 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    @foreach($heroSlides as $index => $slide)
                        <div x-show="active === {{ $index }}" x-transition.opacity.duration.500ms>
                            @if($slide->show_overlay_content)
                                @if($slide->badge_text)
                                    <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1.5 text-xs font-semibold text-white/85 shadow-sm backdrop-blur-sm">
                                        <span class="h-1.5 w-1.5 rounded-full bg-primary-300"></span>
                                        {{ $slide->badge_text }}
                                    </div>
                                @endif

                                @if($slide->heading)
                                    <h1 class="mb-4 text-4xl font-black leading-tight tracking-tight text-white sm:text-6xl">
                                        {{ $slide->heading }}
                                    </h1>
                                @endif

                                @if($slide->body)
                                    <p class="mb-8 max-w-2xl text-base leading-relaxed text-white/75 sm:text-lg">
                                        {{ $slide->body }}
                                    </p>
                                @endif

                                @if($slide->primary_button_label || $slide->secondary_button_label)
                                    <div class="flex flex-wrap gap-3">
                                        @if($slide->primary_button_label && $slide->primary_button_url)
                                            <a href="{{ $slide->primary_button_url }}"
                                               class="rounded-full bg-primary px-8 py-3 font-semibold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-primary-500 hover:shadow-lg">
                                                {{ $slide->primary_button_label }}
                                            </a>
                                        @endif

                                        @if($slide->secondary_button_label && $slide->secondary_button_url)
                                            <a href="{{ $slide->secondary_button_url }}"
                                               class="rounded-full border border-white/25 bg-white/10 px-8 py-3 font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">
                                                {{ $slide->secondary_button_label }}
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @if($heroSlides->count() > 1)
                <div class="absolute inset-x-0 bottom-6 z-20">
                    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-2">
                            @foreach($heroSlides as $index => $slide)
                                <button type="button"
                                        class="h-2.5 rounded-full transition"
                                        :class="active === {{ $index }} ? 'w-8 bg-white' : 'w-2.5 bg-white/40 hover:bg-white/70'"
                                        @click="active = {{ $index }}">
                                    <span class="sr-only">{{ $slide->name }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-black/20 text-white transition hover:bg-black/35"
                                    @click="prev()">
                                <span class="sr-only">Previous slide</span>
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button type="button"
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-black/20 text-white transition hover:bg-black/35"
                                    @click="next()">
                                <span class="sr-only">Next slide</span>
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    @else
        <section class="relative flex min-h-[56vh] items-center justify-center overflow-hidden px-4 text-center">
            <div class="pointer-events-none absolute -left-32 -top-32 h-[500px] w-[500px] rounded-full bg-primary-400 opacity-[0.08] blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -right-32 h-[500px] w-[500px] rounded-full bg-primary-300 opacity-[0.08] blur-3xl"></div>
            <div class="pointer-events-none absolute left-1/2 top-1/2 h-[700px] w-[700px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary-50 opacity-50 blur-3xl"></div>

            <span class="note-1 pointer-events-none absolute left-[12%] top-16 select-none text-3xl opacity-20">♪</span>
            <span class="note-2 pointer-events-none absolute right-[10%] top-24 select-none text-4xl opacity-20">♫</span>
            <span class="note-3 pointer-events-none absolute bottom-20 left-[20%] select-none text-2xl opacity-15">♩</span>
            <span class="note-1 pointer-events-none absolute bottom-16 right-[18%] select-none text-3xl opacity-15">♬</span>

            <div class="relative z-10">
                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-primary-100 bg-primary-50 px-4 py-1.5 text-xs font-semibold text-primary-600 shadow-sm">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-primary-500"></span>
                    {{ $stats['tracks'] }} tracks live now
                </div>
                <h1 class="mb-4 text-5xl font-black leading-none tracking-tight text-gray-900 sm:text-7xl">
                    {{ $siteName }}
                </h1>
                <p class="mx-auto mb-8 max-w-xl text-lg text-gray-500 sm:text-xl">
                    Independent music. Real artists. Support the creators you love.
                </p>
                @guest
                    <div class="flex flex-wrap justify-center gap-3">
                        <a href="{{ route('register') }}"
                           class="rounded-full bg-primary px-8 py-3 font-semibold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-primary-500 hover:shadow-lg">
                            Start Listening
                        </a>
                        <a href="{{ route('browse') }}"
                           class="rounded-full px-8 py-3 font-semibold glass-btn glass-btn-hover">
                            Browse Music
                        </a>
                    </div>
                @else
                    <a href="{{ route('browse') }}"
                       class="rounded-full bg-primary px-8 py-3 font-semibold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-primary-500 hover:shadow-lg">
                        Browse Music
                    </a>
                @endguest
            </div>
        </section>
    @endif

    <div class="bg-gradient-to-r from-primary to-primary-500 py-5 shadow-inner"
         x-data="{ visible: false }"
         x-init="
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    visible = true;
                    observer.disconnect();
                }
            }, { threshold: 0.3 });
            observer.observe($el);
         ">
        <div class="mx-auto grid max-w-5xl grid-cols-2 divide-x divide-y divide-white/20 text-center text-white sm:grid-cols-4 sm:divide-y-0">
            @foreach([
                ['n' => $stats['tracks'], 'label' => 'Published Tracks'],
                ['n' => $stats['artists'], 'label' => 'Artists'],
                ['n' => $stats['genres'], 'label' => 'Genres'],
                ['n' => $stats['playlists'], 'label' => 'Public Playlists', 'href' => route('playlists.public')],
            ] as $stat)
                @if(!empty($stat['href']))
                    <a href="{{ $stat['href'] }}" wire:navigate class="block px-6 py-3 transition hover:bg-white/10 sm:py-1">
                        <p class="stat-num text-2xl font-black sm:text-3xl" x-show="visible">{{ number_format($stat['n']) }}</p>
                        <p class="mt-0.5 text-xs text-white/80 sm:text-sm">{{ $stat['label'] }}</p>
                    </a>
                @else
                    <div class="px-6 py-3 sm:py-1">
                        <p class="stat-num text-2xl font-black sm:text-3xl" x-show="visible">{{ number_format($stat['n']) }}</p>
                        <p class="mt-0.5 text-xs text-white/80 sm:text-sm">{{ $stat['label'] }}</p>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    @if($showHomeEditorPicks && $homeEditorPicksPosition === 'before-personalized' && $editorPicks->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pb-4 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] glass-card p-5 sm:p-7">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Editor's Picks</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900 sm:text-3xl">Hand-picked tracks worth starting with</h2>
                        <p class="mt-2 max-w-2xl text-sm text-gray-500">These are the tracks the team is actively pushing to the front of discovery right now.</p>
                    </div>
                    <a href="{{ route('browse', ['sort' => 'popular']) }}" wire:navigate class="text-sm font-semibold text-primary transition hover:text-primary-600">Browse all discovery</a>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($editorPicks as $track)
                        <article class="rounded-[1.5rem] border border-white/60 bg-white/75 p-3 shadow-sm transition hover:-translate-y-0.5 hover:bg-white/95 hover:shadow-md">
                            <div class="flex items-center gap-3">
                                <button @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
                                        class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                    @if($track->getCoverUrl())
                                        <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->cover_alt }}" class="h-full w-full object-cover">
                                    @endif
                                </button>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('track.show', $track) }}" wire:navigate class="truncate text-sm font-semibold text-gray-900 hover:text-primary">
                                            {{ $track->title }}
                                        </a>
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em] text-amber-700">Curated</span>
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                        <a href="{{ route('artist.page', $track->artistProfile) }}" wire:navigate class="truncate hover:text-primary">
                                            {{ $track->artistProfile?->display_name ?? 'Unknown artist' }}
                                        </a>
                                        @if($track->mood_label)
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ $track->mood_label }}</span>
                                        @endif
                                        <x-track-duration :track="$track" class="text-gray-400" />
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center gap-3 text-[11px] font-medium text-gray-400">
                                        <span>{{ number_format($track->play_count) }} plays</span>
                                        <span>{{ number_format($track->downloads_count) }} downloads</span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @auth
        @if($showHomePersonalized && $personalizedTracks->isNotEmpty())
            <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                <div class="rounded-[2rem] glass-card p-5 sm:p-7">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">For You</p>
                            <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900 sm:text-3xl">Because of what you keep playing</h2>
                            <p class="mt-2 max-w-2xl text-sm text-gray-500">Recommendations shaped by your likes, recent listening, and the artists you keep circling back to.</p>
                        </div>
                        <a href="{{ route('listener.dashboard') }}" wire:navigate class="text-sm font-semibold text-primary transition hover:text-primary-600">Open taste profile</a>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach($personalizedTracks as $track)
                            <article class="rounded-[1.5rem] border border-white/60 bg-white/75 p-3 shadow-sm transition hover:-translate-y-0.5 hover:bg-white/95 hover:shadow-md">
                                <div class="flex items-center gap-3">
                                    <button @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
                                            class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                        @if($track->getCoverUrl())
                                            <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->cover_alt }}" class="h-full w-full object-cover">
                                        @endif
                                    </button>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('track.show', $track) }}" wire:navigate class="truncate text-sm font-semibold text-gray-900 hover:text-primary">
                                            {{ $track->title }}
                                        </a>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                            <a href="{{ route('artist.page', $track->artistProfile) }}" wire:navigate class="truncate hover:text-primary">
                                                {{ $track->artistProfile?->display_name ?? 'Unknown artist' }}
                                            </a>
                                            @if($track->genre)
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ $track->genre->name }}</span>
                                            @endif
                                            <x-track-duration :track="$track" class="text-gray-400" />
                                        </div>
                                        <div class="mt-3 flex flex-wrap items-center gap-3 text-[11px] font-medium text-gray-400">
                                            <span>{{ number_format($track->play_count) }} plays</span>
                                            <span>{{ number_format($track->downloads_count) }} downloads</span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endauth

    @if($showHomeEditorPicks && $homeEditorPicksPosition !== 'before-personalized' && $editorPicks->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pb-4 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] glass-card p-5 sm:p-7">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Editor's Picks</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900 sm:text-3xl">Hand-picked tracks worth starting with</h2>
                        <p class="mt-2 max-w-2xl text-sm text-gray-500">These are the tracks the team is actively pushing to the front of discovery right now.</p>
                    </div>
                    <a href="{{ route('browse', ['sort' => 'popular']) }}" wire:navigate class="text-sm font-semibold text-primary transition hover:text-primary-600">Browse all discovery</a>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($editorPicks as $track)
                        <article class="rounded-[1.5rem] border border-white/60 bg-white/75 p-3 shadow-sm transition hover:-translate-y-0.5 hover:bg-white/95 hover:shadow-md">
                            <div class="flex items-center gap-3">
                                <button @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
                                        class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                    @if($track->getCoverUrl())
                                        <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->cover_alt }}" class="h-full w-full object-cover">
                                    @endif
                                </button>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('track.show', $track) }}" wire:navigate class="truncate text-sm font-semibold text-gray-900 hover:text-primary">
                                            {{ $track->title }}
                                        </a>
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em] text-amber-700">Curated</span>
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                        <a href="{{ route('artist.page', $track->artistProfile) }}" wire:navigate class="truncate hover:text-primary">
                                            {{ $track->artistProfile?->display_name ?? 'Unknown artist' }}
                                        </a>
                                        @if($track->mood_label)
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ $track->mood_label }}</span>
                                        @endif
                                        <x-track-duration :track="$track" class="text-gray-400" />
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center gap-3 text-[11px] font-medium text-gray-400">
                                        <span>{{ number_format($track->play_count) }} plays</span>
                                        <span>{{ number_format($track->downloads_count) }} downloads</span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($trending->count())
        <section class="overflow-hidden bg-gray-50/80 py-10">
            <div class="mx-auto mb-5 flex max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-gray-800">Trending Now</h2>
                <a href="{{ route('browse', ['sort' => 'popular']) }}" class="text-sm font-medium text-primary hover:text-primary-600">See all &rarr;</a>
            </div>
            <div class="relative select-none overflow-hidden cursor-grab active:cursor-grabbing"
                 x-data="{ paused: false }"
                 @mouseenter="paused = true"
                 @mouseleave="paused = false">
                <div class="pointer-events-none absolute bottom-0 left-0 top-0 z-10 w-16 bg-gradient-to-r from-gray-50/80 to-transparent"></div>
                <div class="pointer-events-none absolute bottom-0 right-0 top-0 z-10 w-16 bg-gradient-to-l from-gray-50/80 to-transparent"></div>

                <div class="ticker-track gap-4 px-4" :class="{ 'paused': paused }">
                    @foreach([$trending, $trending] as $loopSet)
                        @foreach($loopSet as $track)
                            <div class="group w-40 shrink-0 cursor-pointer overflow-hidden rounded-2xl transition-transform hover:scale-[1.03] glass-card"
                                 @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })">
                                <div class="relative h-40 w-40">
                                    @if($track->getFirstMediaUrl('cover'))
                                        <img src="{{ $track->getFirstMediaUrl('cover', 'thumb') }}" alt="{{ $track->cover_alt }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary-100 to-primary-200">
                                            <svg class="h-10 w-10 text-primary-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/></svg>
                                        </div>
                                    @endif
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition group-hover:opacity-100">
                                        <svg class="h-10 w-10 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </div>
                                </div>
                                <div class="space-y-1 p-3">
                                    <p class="truncate text-sm font-semibold text-gray-800">{{ $track->title }}</p>
                                    <div class="flex items-center justify-between gap-2 text-[11px] text-gray-500">
                                        <span class="truncate">{{ $track->artistProfile->stage_name ?? ($track->artistProfile->user->name ?? '') }}</span>
                                        <x-track-duration :track="$track" class="shrink-0 text-[11px] text-gray-400" :icon="false" />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <div class="mx-auto max-w-7xl px-4 pb-8 sm:px-6 lg:px-8">
        @if($latest->count())
            <section class="mb-14 mt-12">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">New Releases</h2>
                        <p class="mt-1 text-sm text-gray-500">Fresh uploads moving through the latest stack.</p>
                    </div>
                    <a href="{{ route('browse') }}" class="text-sm font-medium text-primary hover:text-primary-600">See all &rarr;</a>
                </div>

                <div class="relative overflow-hidden rounded-[1.75rem] p-4 glass-card"
                     x-data="{ paused: false }"
                     @mouseenter="paused = true"
                     @mouseleave="paused = false">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-16 bg-gradient-to-b from-white via-white/95 to-transparent"></div>
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-white via-white/95 to-transparent"></div>

                    <div class="max-h-[26rem] overflow-hidden">
                        <div class="vertical-marquee-track" :class="{ 'paused': paused }">
                            @foreach([$latest, $latest] as $loopSet)
                                @foreach($loopSet as $i => $track)
                                    <div class="group flex items-center gap-3 rounded-2xl p-3 transition hover:bg-white/70"
                                         @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })">
                                        <span class="w-5 shrink-0 text-center text-sm text-gray-400 group-hover:hidden">{{ $i + 1 }}</span>
                                        <svg class="hidden h-5 w-5 shrink-0 text-primary group-hover:block" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        <div class="h-11 w-11 shrink-0 overflow-hidden rounded-xl">
                                            @if($track->getFirstMediaUrl('cover'))
                                                <img src="{{ $track->getFirstMediaUrl('cover', 'thumb') }}" alt="{{ $track->cover_alt }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="h-full w-full bg-gradient-to-br from-primary-100 to-primary-200"></div>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-medium text-gray-800">{{ $track->title }}</p>
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                                <a href="{{ route('artist.page', $track->artistProfile) }}" @click.stop class="truncate hover:text-primary">
                                                    {{ $track->artistProfile->stage_name ?? ($track->artistProfile->user->name ?? '') }}
                                                </a>
                                                <x-track-duration :track="$track" class="text-gray-400" />
                                            </div>
                                        </div>
                                        <span class="hidden shrink-0 text-xs text-gray-400 sm:block">{{ $track->created_at->diffForHumans(null, true) }} ago</span>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if($genres->count())
            <section class="mb-14">
                <h2 class="mb-5 text-2xl font-bold text-gray-800">Browse by Genre</h2>
                <div class="flex flex-wrap gap-3">
                    @php
                        $fallbackColors = ['#728FCE', '#f97316', '#eab308', '#22c55e', '#06b6d4', '#6366f1', '#ec4899', '#8b5cf6', '#14b8a6', '#5574b9', '#84cc16', '#0ea5e9'];
                    @endphp
                    @foreach($genres as $idx => $genre)
                        @php $color = $genre->color ?: $fallbackColors[$idx % count($fallbackColors)]; @endphp
                        <a href="{{ route('browse', ['genre' => $genre->slug]) }}"
                           class="genre-pill relative inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:scale-105 hover:shadow-md active:scale-95"
                           style="background: {{ $color }};">
                            {{ $genre->name }}
                            @if($genre->tracks_count > 0)
                                <span class="rounded-full bg-white/25 px-1.5 py-0.5 text-[10px] text-white">{{ $genre->tracks_count }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($featuredArtists->count())
            <section class="mb-14"
                     x-data="{ ready: false }"
                     x-init="
                        const observer = new IntersectionObserver((entries) => {
                            if (entries[0].isIntersecting) {
                                ready = true;
                                observer.disconnect();
                            }
                        }, { threshold: 0.1 });
                        observer.observe($el);
                     ">
                <h2 class="mb-5 text-2xl font-bold text-gray-800">Featured Artists</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-6">
                    @foreach($featuredArtists as $index => $artist)
                        <a href="{{ route('artist.page', $artist) }}"
                           class="float-card flex flex-col items-center gap-2 rounded-xl p-4 text-center transition glass-card glass-card-hover"
                           :class="{ 'visible': ready }"
                           style="animation-delay: {{ $index * 80 }}ms">
                            <div class="h-16 w-16 overflow-hidden rounded-full bg-gradient-to-br from-primary-100 to-primary-200 ring-2 ring-white shadow-sm">
                                @if($artist->getFirstMediaUrl('avatar'))
                                    <img src="{{ $artist->getFirstMediaUrl('avatar', 'thumb') }}" alt="{{ $artist->avatar_alt }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-xl font-black text-primary-400">
                                        {{ strtoupper(substr($artist->stage_name ?? $artist->user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-800">{{ $artist->stage_name ?? $artist->user->name }}</p>
                                <p class="text-[10px] {{ $artist->is_featured ? 'text-amber-600' : ($artist->is_verified ? 'text-primary' : 'text-gray-400') }}">
                                    {{ $artist->is_featured ? "Editor's Pick" : ($artist->is_verified ? 'Verified' : 'Artist') }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mb-14"
                 x-data="{ ready: false }"
                 x-init="
                    const observer = new IntersectionObserver((entries) => {
                        if (entries[0].isIntersecting) {
                            ready = true;
                            observer.disconnect();
                        }
                    }, { threshold: 0.1 });
                    observer.observe($el);
                 ">
            <h2 class="mb-2 text-2xl font-bold text-gray-800">How It Works</h2>
            <p class="mb-7 text-sm text-gray-500">Music for everyone. Income for artists.</p>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                @foreach([
                    ['icon' => '🎧', 'title' => 'Discover', 'desc' => 'Browse thousands of tracks across every genre. Search by artist, mood or vibe.', 'color' => 'from-primary-50 to-primary-100', 'border' => 'border-primary-100'],
                    ['icon' => '❤️', 'title' => 'Support', 'desc' => 'Unlock premium tracks with a small donation. Every rand goes directly to the artist.', 'color' => 'from-primary-50 to-blue-50', 'border' => 'border-primary-100'],
                    ['icon' => '🚀', 'title' => 'Create', 'desc' => 'Are you an artist? Upload your music, build a following and earn from your craft.', 'color' => 'from-blue-50 to-primary-50', 'border' => 'border-primary-100'],
                ] as $index => $step)
                    <div class="float-card rounded-2xl border bg-gradient-to-br p-6 transition hover:shadow-lg glass-card {{ $step['border'] }} {{ $step['color'] }}"
                         :class="{ 'visible': ready }"
                         style="animation-delay: {{ $index * 120 }}ms">
                        <div class="mb-4 text-4xl">{{ $step['icon'] }}</div>
                        <h3 class="mb-2 text-lg font-bold text-gray-800">{{ $step['title'] }}</h3>
                        <p class="text-sm leading-relaxed text-gray-500">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        @guest
            <section class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-primary-600 p-10 text-center shadow-xl">
                <div class="pointer-events-none absolute inset-0 opacity-10">
                    <span class="note-1 absolute left-8 top-4 text-6xl">♪</span>
                    <span class="note-2 absolute bottom-4 right-8 text-6xl">♫</span>
                    <span class="note-3 absolute right-1/4 top-1/2 text-5xl">♩</span>
                </div>
                <div class="relative z-10">
                    <h2 class="mb-2 text-3xl font-black text-white">Are you an artist?</h2>
                    <p class="mx-auto mb-7 max-w-sm text-base text-white/80">
                        Upload your music, grow your audience, and receive donations from your fans for free.
                    </p>
                    <div class="flex flex-wrap justify-center gap-3">
                        <a href="{{ route('register') }}"
                           class="inline-block rounded-full bg-white px-8 py-3 font-bold text-primary-600 shadow-lg transition hover:-translate-y-0.5 hover:bg-primary-50">
                            Join as Artist
                        </a>
                        <a href="{{ route('browse') }}"
                           class="inline-block rounded-full border border-white/50 px-8 py-3 font-semibold text-white transition hover:bg-white/10">
                            Just Browsing &rarr;
                        </a>
                    </div>
                </div>
            </section>
        @endguest
    </div>
</div>
