<?php

use App\Models\ArtistProfile;
use App\Models\Genre;
use App\Models\SiteSetting;
use App\Models\Track;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component
{
    #[Url]
    public string $q = '';

    #[Url]
    public string $tab = 'tracks';

    public function with(): array
    {
        $siteSettings = Schema::hasTable('site_settings') ? SiteSetting::current() : null;
        $supportsDiscoveryVisibility = SiteSetting::supportsDiscoveryVisibility();
        $showSearchTrendingTracks = $supportsDiscoveryVisibility ? ($siteSettings?->show_search_trending_tracks ?? true) : true;
        $showSearchPopularArtists = $supportsDiscoveryVisibility ? ($siteSettings?->show_search_popular_artists ?? true) : true;

        $query = trim($this->q);
        $tracks = collect();
        $artists = collect();

        if (mb_strlen($query) >= 2) {
            $tracks = Track::search($query)
                ->query(fn ($builder) => $builder
                    ->with(['artistProfile.user', 'genre'])
                    ->where('is_published', true))
                ->get()
                ->take(18)
                ->values();

            if ($tracks->isEmpty()) {
                $tracks = Track::query()
                    ->with(['artistProfile.user', 'genre'])
                    ->where('is_published', true)
                    ->where(function ($builder) use ($query): void {
                        $term = '%' . $query . '%';

                        $builder
                            ->where('title', 'like', $term)
                            ->orWhere('description', 'like', $term)
                            ->orWhereHas('artistProfile', fn ($artistQuery) => $artistQuery->where('stage_name', 'like', $term));
                    })
                    ->orderByDesc('play_count')
                    ->orderByDesc('downloads_count')
                    ->take(18)
                    ->get();
            }

            $artists = ArtistProfile::search($query)
                ->query(fn ($builder) => $builder
                    ->with('user')
                    ->where('is_approved', true)
                    ->where('is_active', true))
                ->get()
                ->take(12)
                ->values();

            if ($artists->isEmpty()) {
                $artists = ArtistProfile::query()
                    ->with('user')
                    ->where('is_approved', true)
                    ->where('is_active', true)
                    ->where(function ($builder) use ($query): void {
                        $term = '%' . $query . '%';

                        $builder
                            ->where('stage_name', 'like', $term)
                            ->orWhere('bio', 'like', $term)
                            ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', $term));
                    })
                    ->orderByDesc('followers_count')
                    ->orderByDesc('total_plays')
                    ->take(12)
                    ->get();
            }
        }

        $relatedArtists = $tracks
            ->pluck('artistProfile')
            ->filter()
            ->unique('id')
            ->take(4)
            ->values();

        $queryLabel = $query === '' ? 'Search' : "Search {$query}";
        $tracksSummary = $query === ''
            ? 'Discovery is blending current standouts with the strongest track signals on the platform.'
            : 'Track matches are ranked from direct query hits first, then stronger play and download momentum.';
        $artistsSummary = $query === ''
            ? 'Artist picks are weighted toward the profiles people are finding most often right now.'
            : 'Artist matches are pulled from direct name/bio relevance and then tightened by audience traction.';

        return [
            'tracks' => $tracks,
            'artists' => $artists,
            'resultCounts' => [
                'tracks' => $tracks->count(),
                'artists' => $artists->count(),
            ],
            'topTrack' => $tracks->first(),
            'relatedArtists' => $relatedArtists,
            'tracksSummary' => $tracksSummary,
            'artistsSummary' => $artistsSummary,
            'suggestedSearches' => Cache::remember('search.suggested-terms.v1', now()->addMinutes(15), function () {
                $terms = collect()
                    ->merge(Genre::query()->where('is_active', true)->orderByDesc('sort_order')->orderBy('name')->take(5)->pluck('name'))
                    ->merge(ArtistProfile::query()->where('is_approved', true)->where('is_active', true)->orderByDesc('followers_count')->take(5)->pluck('stage_name'))
                    ->merge(Track::query()->where('is_published', true)->orderByDesc('play_count')->take(5)->pluck('title'));

                return $terms
                    ->filter()
                    ->map(fn (string $term) => Str::limit($term, 28, ''))
                    ->unique()
                    ->take(10)
                    ->values();
            }),
            'featuredTracks' => $showSearchTrendingTracks
                ? Cache::remember('search.featured-tracks.v1', now()->addMinutes(10), fn () => Track::query()
                    ->with(['artistProfile.user', 'genre'])
                    ->where('is_published', true)
                    ->orderByDesc('is_featured')
                    ->orderByDesc('play_count')
                    ->take(4)
                    ->get())
                : collect(),
            'featuredArtists' => $showSearchPopularArtists
                ? Cache::remember('search.featured-artists.v1', now()->addMinutes(10), fn () => ArtistProfile::query()
                    ->with('user')
                    ->where('is_approved', true)
                    ->where('is_active', true)
                    ->when(ArtistProfile::supportsFeaturedCuration(), fn ($query) => $query->orderByDesc('is_featured'))
                    ->orderByDesc('followers_count')
                    ->take(4)
                    ->get())
                : collect(),
            'showSearchTrendingTracks' => $showSearchTrendingTracks,
            'showSearchPopularArtists' => $showSearchPopularArtists,
            'seo' => [
                'title' => $queryLabel,
                'description' => $query === ''
                    ? 'Search GrinMuzik for tracks, artists, genres, and fresh discoveries.'
                    : 'Search results for ' . $query . ' across tracks and artist profiles on GrinMuzik.',
                'canonical' => route('search', $query !== '' ? ['q' => $query, 'tab' => $this->tab] : []),
                'robots' => 'noindex,follow',
            ],
        ];
    }

    public function switchTab(string $tab): void
    {
        if (! in_array($tab, ['tracks', 'artists'], true)) {
            return;
        }

        $this->tab = $tab;
    }
};
?>

<div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl space-y-6">
        <section class="glass-card rounded-[2rem] p-6 sm:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Search Library</p>
                    <h1 class="text-3xl font-black tracking-tight text-gray-900 sm:text-4xl">Search tracks, artists, and your next obsession</h1>
                    <p class="mt-2 text-sm text-gray-500 sm:text-base">Search stays fast, but the page now also helps you discover what to try next when you are not sure what to type.</p>
                </div>

                <div class="flex items-center gap-2 rounded-full border border-white/60 bg-white/70 p-1 shadow-sm">
                    <button
                        wire:click="switchTab('tracks')"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $tab === 'tracks' ? 'bg-primary text-white shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                        Tracks
                        <span class="ml-1 opacity-80">{{ $resultCounts['tracks'] }}</span>
                    </button>
                    <button
                        wire:click="switchTab('artists')"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $tab === 'artists' ? 'bg-primary text-white shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                        Artists
                        <span class="ml-1 opacity-80">{{ $resultCounts['artists'] }}</span>
                    </button>
                </div>
            </div>

            <div class="relative mt-6">
                <svg class="pointer-events-none absolute left-5 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                <input
                    wire:model.live.debounce.300ms="q"
                    type="search"
                    placeholder="Search tracks, artists, genres..."
                    autofocus
                    class="w-full rounded-[1.6rem] border border-white/60 bg-white/85 py-4 pl-14 pr-5 text-base text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-primary/40 focus:ring-4 focus:ring-primary/10 sm:text-lg">
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Try</span>
                @foreach($suggestedSearches as $term)
                    <button
                        type="button"
                        wire:click="$set('q', @js($term))"
                        class="rounded-full border border-white/70 bg-white/80 px-3 py-1.5 text-sm font-semibold text-gray-600 transition hover:border-primary hover:text-primary">
                        {{ $term }}
                    </button>
                @endforeach
            </div>

            @if(strlen(trim($q)) >= 2)
                <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                    <span class="inline-flex h-2 w-2 rounded-full bg-primary"></span>
                    <span>Results for</span>
                    <span class="font-semibold text-gray-800">"{{ $q }}"</span>
                    <span>&middot;</span>
                    <span>{{ $resultCounts['tracks'] }} tracks</span>
                    <span>&middot;</span>
                    <span>{{ $resultCounts['artists'] }} artists</span>
                </div>
            @endif
        </section>

        @if(strlen(trim($q)) < 2)
            @if($showSearchTrendingTracks || $showSearchPopularArtists)
            <section class="grid gap-6 {{ $showSearchTrendingTracks && $showSearchPopularArtists ? 'lg:grid-cols-[minmax(0,1.05fr),minmax(280px,0.95fr)]' : 'lg:grid-cols-1' }}">
                @if($showSearchTrendingTracks)
                <div class="glass-card rounded-[2rem] p-6 sm:p-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Trending Tracks</p>
                    <p class="mt-2 text-sm text-gray-500">{{ $tracksSummary }}</p>
                    <div class="mt-5 space-y-3">
                        @foreach($featuredTracks as $track)
                            <button class="group flex w-full items-center gap-4 rounded-[1.4rem] border border-white/40 bg-white/60 p-3 text-left transition hover:bg-white/85"
                                    @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })">
                                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                    @if($track->getCoverUrl())
                                        <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->cover_alt }}" class="h-full w-full object-cover">
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="truncate text-sm font-semibold text-gray-900">{{ $track->title }}</p>
                                        @if($track->is_featured)
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em] text-amber-700">Curated</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                        <span class="truncate">{{ $track->artistProfile?->display_name ?? 'Unknown artist' }}</span>
                                        @if($track->mood_label)
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ $track->mood_label }}</span>
                                        @endif
                                        <x-track-duration :track="$track" class="text-gray-400" />
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($showSearchPopularArtists)
                <div class="glass-card rounded-[2rem] p-6 sm:p-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Popular Artists</p>
                    <p class="mt-2 text-sm text-gray-500">{{ $artistsSummary }}</p>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        @foreach($featuredArtists as $artist)
                            <a href="{{ route('artist.page', $artist) }}"
                               wire:navigate
                               class="rounded-[1.4rem] border border-white/40 bg-white/60 p-4 text-center transition hover:bg-white/85">
                                <div class="mx-auto h-20 w-20 overflow-hidden rounded-full bg-gradient-to-br from-primary-100 to-primary-200 ring-2 ring-white/80">
                                    @if($artist->getFirstMediaUrl('avatar'))
                                        <img src="{{ $artist->getFirstMediaUrl('avatar', 'thumb') }}" alt="{{ $artist->avatar_alt }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-2xl font-black text-primary-400">
                                            {{ strtoupper(substr($artist->display_name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <p class="mt-3 truncate text-sm font-semibold text-gray-900">{{ $artist->display_name }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ $artist->is_featured ? "Editor's Pick · " : '' }}{{ number_format($artist->followers_count) }} followers</p>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </section>
            @endif
        @elseif($tab === 'tracks')
            <section class="space-y-6">
                @if($topTrack)
                    <article class="glass-card overflow-hidden rounded-[2rem]">
                        <div class="grid gap-0 md:grid-cols-[14rem_minmax(0,1fr)]">
                            <div class="min-h-[14rem] bg-gradient-to-br from-primary-100 to-primary-200">
                                @if($topTrack->getCoverUrl('large'))
                                    <img src="{{ $topTrack->getCoverUrl('large') }}" alt="{{ $topTrack->cover_alt }}" class="h-full w-full object-cover">
                                @elseif($topTrack->getCoverUrl())
                                    <img src="{{ $topTrack->getCoverUrl() }}" alt="{{ $topTrack->cover_alt }}" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="p-6">
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Top Match</p>
                                <h2 class="mt-3 text-3xl font-black tracking-tight text-gray-900">{{ $topTrack->title }}</h2>
                                <p class="mt-2 text-sm text-gray-500">{{ $topTrack->artistProfile?->display_name ?? 'Unknown artist' }} @if($topTrack->genre)&middot; {{ $topTrack->genre->name }}@endif</p>
                                <p class="mt-2 text-sm text-gray-500">{{ $tracksSummary }}</p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ number_format($topTrack->play_count) }} plays</span>
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ number_format($topTrack->downloads_count) }} downloads</span>
                                    @if($topTrack->mood_label)
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ $topTrack->mood_label }}</span>
                                    @endif
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ $topTrack->formatted_duration }}</span>
                                </div>
                                <div class="mt-5 flex flex-wrap gap-3">
                                    <button @click="Livewire.dispatch('play-track', { id: {{ $topTrack->id }} })" class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-600">Play Match</button>
                                    <a href="{{ route('track.show', $topTrack->slug) }}" wire:navigate class="rounded-full border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary">Open Track</a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endif

                @if($relatedArtists->isNotEmpty())
                    <div class="glass-card rounded-[2rem] p-5 sm:p-6">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Related Artists</p>
                                <h3 class="mt-2 text-xl font-black tracking-tight text-gray-900">Artists appearing in these track results</h3>
                                <p class="mt-2 text-sm text-gray-500">{{ $artistsSummary }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach($relatedArtists as $artist)
                                <a href="{{ route('artist.page', $artist) }}" wire:navigate class="rounded-[1.4rem] border border-white/40 bg-white/60 p-4 text-center transition hover:bg-white/85">
                                    <div class="mx-auto h-16 w-16 overflow-hidden rounded-full bg-gradient-to-br from-primary-100 to-primary-200 ring-2 ring-white/80">
                                        @if($artist->getFirstMediaUrl('avatar'))
                                            <img src="{{ $artist->getFirstMediaUrl('avatar', 'thumb') }}" alt="{{ $artist->avatar_alt }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-xl font-black text-primary-400">
                                                {{ strtoupper(substr($artist->display_name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <p class="mt-3 truncate text-sm font-semibold text-gray-900">{{ $artist->display_name }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <section wire:loading.class="opacity-60" class="space-y-3 transition-opacity">
                    @forelse($tracks as $track)
                        <div class="glass-card group flex items-center gap-4 rounded-[1.6rem] p-4 transition hover:-translate-y-0.5 hover:bg-white/85 hover:shadow-lg"
                             @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })">
                            <div class="relative h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                @if($track->getCoverUrl())
                                    <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->cover_alt }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <svg class="h-6 w-6 text-primary-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/></svg>
                                    </div>
                                @endif
                                <div class="absolute inset-0 flex items-center justify-center bg-black/35 opacity-0 transition group-hover:opacity-100">
                                    <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-base font-semibold text-gray-900">{{ $track->title }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                                    <span class="truncate">{{ $track->artistProfile?->display_name ?? 'Unknown artist' }}</span>
                                    @if($track->genre)
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ $track->genre->name }}</span>
                                    @endif
                                    <x-track-duration :track="$track" class="text-gray-400" />
                                </div>
                            </div>

                            <div class="hidden shrink-0 items-center gap-3 sm:flex">
                                @if($track->requires_donation)
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                        ${{ number_format($track->donation_amount, 2) }}
                                    </span>
                                @endif
                                @livewire('like-button', ['trackId' => $track->id], key('search-like-'.$track->id))
                            </div>
                        </div>
                    @empty
                        <div class="glass-card rounded-[2rem] px-6 py-14 text-center">
                            <p class="text-lg font-semibold text-gray-800">No tracks found</p>
                            <p class="mt-2 text-sm text-gray-500">Nothing matched "{{ $q }}". Try another title, artist, or spelling.</p>
                        </div>
                    @endforelse
                </section>
            </section>
        @else
            <section wire:loading.class="opacity-60" class="space-y-6 transition-opacity">
                @if($artists->isNotEmpty())
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach($artists as $artist)
                            <a href="{{ route('artist.page', $artist) }}"
                               wire:navigate
                               class="glass-card flex flex-col items-center gap-3 rounded-[1.6rem] p-5 text-center transition hover:-translate-y-0.5 hover:bg-white/85 hover:shadow-lg">
                                <div class="h-24 w-24 overflow-hidden rounded-full bg-gradient-to-br from-primary-100 to-primary-200 ring-2 ring-white/80 shadow-sm">
                                    @if($artist->getFirstMediaUrl('avatar'))
                                        <img src="{{ $artist->getFirstMediaUrl('avatar', 'thumb') }}" alt="{{ $artist->avatar_alt }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-2xl font-black text-primary-400">
                                            {{ strtoupper(substr($artist->display_name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $artist->display_name }}</p>
                                    <p class="mt-1 text-xs {{ $artist->is_verified ? 'text-primary' : 'text-gray-400' }}">
                                        {{ $artist->is_verified ? 'Verified Artist' : 'Artist Profile' }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-400">{{ number_format($artist->followers_count) }} followers</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="glass-card col-span-full rounded-[2rem] px-6 py-14 text-center">
                        <p class="text-lg font-semibold text-gray-800">No artists found</p>
                        <p class="mt-2 text-sm text-gray-500">Nothing matched "{{ $q }}". Try another artist name or spelling.</p>
                    </div>
                @endif
            </section>
        @endif
    </div>
</div>
