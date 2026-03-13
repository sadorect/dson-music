<?php

use App\Models\ArtistProfile;
use App\Models\Genre;
use App\Models\SiteSetting;
use App\Models\Track;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.glass-app')] class extends Component {
    use WithPagination;

    #[Url]
    public ?string $genre = null;

    #[Url]
    public ?string $mood = null;

    #[Url]
    public string $sort = 'latest';

    protected function moodDefinitions(): array
    {
        return Track::moodDefinitions();
    }

    protected function discoverySettings(): ?SiteSetting
    {
        static $settings = false;

        if ($settings === false) {
            $settings = Schema::hasTable('site_settings') ? SiteSetting::current() : null;
        }

        return $settings;
    }

    protected function discoveryToggle(string $key, bool $default = true): bool
    {
        if (! SiteSetting::supportsDiscoveryVisibility()) {
            return $default;
        }

        return (bool) ($this->discoverySettings()?->{$key} ?? $default);
    }

    protected function applyMoodFilter(Builder $query): Builder
    {
        if (
            ! $this->discoveryToggle('show_browse_mood_filters')
            || ! $this->mood
            || ! isset($this->moodDefinitions()[$this->mood])
        ) {
            return $query;
        }

        $definition = $this->moodDefinitions()[$this->mood];

        return $query->where(function (Builder $nested) use ($definition): void {
            $nested->where(function (Builder $moodQuery) use ($definition): void {
                foreach ($definition['moods'] as $index => $moodValue) {
                    $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                    $moodQuery->{$method}("REPLACE(REPLACE(LOWER(COALESCE(mood, '')), '-', ' '), '_', ' ') = ?", [$moodValue]);
                }
            });

            if (!empty($definition['genres'])) {
                $nested->orWhereHas('genre', fn (Builder $genreQuery) => $genreQuery->whereIn('slug', $definition['genres']));
            }
        });
    }

    protected function baseTrackQuery(): Builder
    {
        $query = Track::query()
            ->with(['artistProfile.user', 'genre'])
            ->where('is_published', true)
            ->when($this->genre, function (Builder $query): void {
                $query->whereHas('genre', fn (Builder $genreQuery) => $genreQuery->where('slug', $this->genre));
            });

        return $this->applyMoodFilter($query);
    }

    protected function buildPersonalizedTracks(): \Illuminate\Support\Collection
    {
        if (!auth()->check()) {
            return collect();
        }

        $user = auth()->user();

        return Cache::remember("browse.personalized.{$user->id}.{$this->genre}.v1", now()->addMinutes(5), function () use ($user) {
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

            $topGenreIds = $tasteSeedTracks->pluck('genre_id')->filter()->countBy()->sortDesc()->keys()->take(4)->values();
            $topArtistIds = $tasteSeedTracks->pluck('artist_profile_id')->filter()->countBy()->sortDesc()->keys()->take(4)->values();
            $excludeTrackIds = $tasteSeedTracks->pluck('id')->filter()->unique()->values();

            $query = $this->baseTrackQuery()->whereNotIn('id', $excludeTrackIds->all());

            if ($topGenreIds->isNotEmpty() || $topArtistIds->isNotEmpty()) {
                $query->where(function (Builder $nested) use ($topGenreIds, $topArtistIds): void {
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

            return $this->baseTrackQuery()
                ->orderByDesc('is_featured')
                ->orderByDesc('play_count')
                ->take(8)
                ->get();
        });
    }

    public function with(): array
    {
        $showBrowseMoodFilters = $this->discoveryToggle('show_browse_mood_filters');
        $showBrowseEditorPicks = $this->discoveryToggle('show_browse_editor_picks');
        $showBrowsePersonalized = $this->discoveryToggle('show_browse_personalized');
        $showBrowseFreshThisWeek = $this->discoveryToggle('show_browse_fresh_this_week');
        $showBrowseArtistsToWatch = $this->discoveryToggle('show_browse_artists_to_watch');
        $showBrowseSupportDirect = $this->discoveryToggle('show_browse_support_direct');
        $browseEditorPicksPosition = SiteSetting::supportsDiscoveryOrdering()
            ? ($this->discoverySettings()?->browse_editor_picks_position ?? 'before-personalized')
            : 'before-personalized';

        $genres = Cache::remember('browse.genres.v2', now()->addMinutes(10), fn () => Genre::query()
            ->withCount(['tracks' => fn (Builder $query) => $query->where('is_published', true)])
            ->active()
            ->get());

        $selectedGenre = $genres->firstWhere('slug', $this->genre);
        $selectedMood = $showBrowseMoodFilters && $this->mood && isset($this->moodDefinitions()[$this->mood])
            ? ['slug' => $this->mood, ...$this->moodDefinitions()[$this->mood]]
            : null;
        $filterKey = ($this->genre ?: 'all').'.'.(($showBrowseMoodFilters && $this->mood) ? $this->mood : 'all');

        $tracksQuery = $this->baseTrackQuery();

        if ($this->sort === 'popular') {
            $tracksQuery->orderByDesc('play_count')->orderByDesc('downloads_count');
        } else {
            $tracksQuery->orderByDesc('created_at');
        }

        $spotlightTracks = Cache::remember("browse.spotlight.{$filterKey}.v1", now()->addMinutes(5), fn () => $this->baseTrackQuery()
            ->orderByDesc('is_featured')
            ->orderByDesc('play_count')
            ->orderByDesc('downloads_count')
            ->take(5)
            ->get());

        $editorPicks = $showBrowseEditorPicks
            ? Cache::remember("browse.editor-picks.{$filterKey}.v1", now()->addMinutes(5), fn () => $this->baseTrackQuery()
                ->where('is_featured', true)
                ->orderByDesc('play_count')
                ->orderByDesc('downloads_count')
                ->take(6)
                ->get())
            : collect();

        $freshTracks = $showBrowseFreshThisWeek
            ? Cache::remember("browse.fresh.{$filterKey}.v1", now()->addMinutes(5), fn () => $this->baseTrackQuery()
                ->latest()
                ->take(6)
                ->get())
            : collect();

        $supportTracks = $showBrowseSupportDirect
            ? Cache::remember("browse.support-direct.{$filterKey}.v1", now()->addMinutes(5), fn () => $this->baseTrackQuery()
                ->where('is_free', false)
                ->orderByDesc('downloads_count')
                ->orderByDesc('play_count')
                ->take(6)
                ->get())
            : collect();

        $topArtists = $showBrowseArtistsToWatch
            ? Cache::remember("browse.top-artists.{$filterKey}.v1", now()->addMinutes(5), function () use ($selectedGenre) {
                return ArtistProfile::query()
                    ->approved()
                    ->with('user')
                    ->when($selectedGenre, fn (Builder $query) => $query->whereHas('genres', fn (Builder $genreQuery) => $genreQuery->whereKey($selectedGenre->id)))
                    ->when(ArtistProfile::supportsFeaturedCuration(), fn (Builder $query) => $query->orderByDesc('is_featured'))
                    ->orderByDesc('is_verified')
                    ->orderByDesc('followers_count')
                    ->orderByDesc('total_plays')
                    ->take(6)
                    ->get();
            })
            : collect();

        $stats = Cache::remember("browse.stats.{$filterKey}.v1", now()->addMinutes(5), fn () => [
            'tracks' => (clone $this->baseTrackQuery())->count(),
            'artists' => ArtistProfile::query()
                ->approved()
                ->when($selectedGenre, fn (Builder $query) => $query->whereHas('genres', fn (Builder $genreQuery) => $genreQuery->whereKey($selectedGenre->id)))
                ->count(),
            'support' => (clone $this->baseTrackQuery())->where('is_free', false)->count(),
        ]);

        $heroTrack = $spotlightTracks->first();
        $personalizedTracks = $showBrowsePersonalized ? $this->buildPersonalizedTracks() : collect();

        $seoTitle = $selectedGenre
            ? "{$selectedGenre->name} Music"
            : ($selectedMood ? "{$selectedMood['label']} Music" : 'Browse Music');
        $seoDescription = $selectedGenre
            ? "Discover {$selectedGenre->name} tracks, rising artists, and fresh releases on GrinMuzik."
            : ($selectedMood
                ? "Browse {$selectedMood['label']} tracks, artists, and curated listening energy on GrinMuzik."
                : 'Browse independent tracks by genre, mood, and popularity on GrinMuzik.');

        return [
            'genres' => $genres,
            'moods' => collect($this->moodDefinitions()),
            'selectedGenre' => $selectedGenre,
            'selectedMood' => $selectedMood,
            'tracks' => $tracksQuery->paginate(24),
            'heroTrack' => $heroTrack,
            'editorPicks' => $editorPicks,
            'freshTracks' => $freshTracks,
            'supportTracks' => $supportTracks,
            'topArtists' => $topArtists,
            'stats' => $stats,
            'personalizedTracks' => $personalizedTracks,
            'showBrowseMoodFilters' => $showBrowseMoodFilters,
            'showBrowseEditorPicks' => $showBrowseEditorPicks,
            'showBrowsePersonalized' => $showBrowsePersonalized,
            'showBrowseFreshThisWeek' => $showBrowseFreshThisWeek,
            'showBrowseArtistsToWatch' => $showBrowseArtistsToWatch,
            'showBrowseSupportDirect' => $showBrowseSupportDirect,
            'browseEditorPicksPosition' => $browseEditorPicksPosition,
            'seo' => [
                'title' => $seoTitle,
                'description' => $seoDescription,
                'canonical' => route('browse', array_filter([
                    'genre' => $this->genre,
                    'mood' => $showBrowseMoodFilters ? $this->mood : null,
                    'sort' => $this->sort !== 'latest' ? $this->sort : null,
                ])),
                'image' => $heroTrack?->getCoverUrl(),
            ],
        ];
    }

    public function setGenre(?string $slug): void
    {
        $this->genre = $slug;
        $this->resetPage();
    }

    public function setMood(?string $slug): void
    {
        $this->mood = $slug;
        $this->resetPage();
    }

    public function setSort(string $value): void
    {
        $this->sort = $value;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->genre = null;
        $this->mood = null;
        $this->sort = 'latest';
        $this->resetPage();
    }
};
?>

<div class="min-h-screen pb-12">
    <section class="relative overflow-hidden px-4 pb-8 pt-8 sm:px-6 lg:px-8">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(239,68,68,0.14),_transparent_38%),radial-gradient(circle_at_top_right,_rgba(251,191,36,0.14),_transparent_30%),linear-gradient(180deg,_rgba(255,255,255,0.94),_rgba(248,250,252,0.98))]"></div>

        <div class="mx-auto max-w-7xl">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.15fr),360px]">
                <div class="rounded-[2rem] glass-card p-6 sm:p-8">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full border border-white/70 bg-white/75 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-primary/80">
                            Discovery Hub
                        </span>
                        @if($selectedGenre)
                            <span class="rounded-full bg-primary-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-primary-700">
                                {{ $selectedGenre->name }} Focus
                            </span>
                        @endif
                        @if($selectedMood)
                            <span class="rounded-full bg-gray-900 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white">
                                {{ $selectedMood['label'] }} Mood
                            </span>
                        @endif
                    </div>

                    <h1 class="mt-4 max-w-3xl text-4xl font-black tracking-tight text-gray-900 sm:text-5xl">
                        {{ $selectedGenre
                            ? 'Browse '.$selectedGenre->name.' without digging through noise'
                            : ($selectedMood
                                ? 'Browse the '.$selectedMood['label'].' lane without losing momentum'
                                : 'Browse what is moving right now across GrinMuzik') }}
                    </h1>
                    <p class="mt-4 max-w-2xl text-sm leading-relaxed text-gray-500 sm:text-base">
                        {{ $selectedGenre
                            ? ($selectedGenre->description ?: 'Fresh releases, fan favorites, donation-powered unlocks, and artists shaping this genre.')
                            : ($selectedMood
                                ? 'Jump into a mood-led stream built around '.$selectedMood['label'].' energy, from fresh tracks to artists that naturally fit the same space.'
                                : 'Jump between fresh drops, fan favorites, support-first releases, and artist scenes built for deeper discovery.') }}
                    </p>

                    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach([
                            ['label' => 'Tracks', 'value' => number_format($stats['tracks'])],
                            ['label' => 'Artists', 'value' => number_format($stats['artists'])],
                            ['label' => 'Support Unlocks', 'value' => number_format($stats['support'])],
                            ['label' => 'Lens', 'value' => $selectedMood['label'] ?? ($sort === 'popular' ? 'Popular' : 'Latest')],
                        ] as $stat)
                            <div class="rounded-2xl border border-white/70 bg-white/75 px-4 py-3 shadow-sm">
                                <p class="text-xl font-black text-gray-900">{{ $stat['value'] }}</p>
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-400">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button wire:click="setSort('latest')"
                                class="rounded-full px-5 py-2.5 text-sm font-semibold transition {{ $sort === 'latest' ? 'bg-primary text-white shadow-sm' : 'border border-white/70 bg-white/80 text-gray-600 hover:border-primary/30 hover:text-primary' }}">
                            Fresh First
                        </button>
                        <button wire:click="setSort('popular')"
                                class="rounded-full px-5 py-2.5 text-sm font-semibold transition {{ $sort === 'popular' ? 'bg-primary text-white shadow-sm' : 'border border-white/70 bg-white/80 text-gray-600 hover:border-primary/30 hover:text-primary' }}">
                            Fan Favorites
                        </button>
                        @if($genre || $sort !== 'latest')
                            <button wire:click="clearFilters"
                                    class="rounded-full border border-white/70 bg-white/80 px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:border-primary/30 hover:text-primary">
                                Clear Filters
                            </button>
                        @endif
                    </div>
                </div>

                <div class="rounded-[2rem] glass-card p-5 sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Spotlight Pick</p>
                    @if($heroTrack)
                        <div class="mt-4">
                            <button @click="Livewire.dispatch('play-track', { id: {{ $heroTrack->id }} })"
                                    class="group relative block aspect-[1.1] w-full overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-primary-100 to-primary-200">
                                @if($heroTrack->getCoverUrl('large'))
                                    <img src="{{ $heroTrack->getCoverUrl('large') }}" alt="{{ $heroTrack->cover_alt }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                                <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between gap-3">
                                    <div class="min-w-0 text-left">
                                        <p class="truncate text-lg font-black text-white">{{ $heroTrack->title }}</p>
                                        <p class="truncate text-sm text-white/80">{{ $heroTrack->artistProfile?->display_name ?? 'Unknown artist' }}</p>
                                    </div>
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur-sm">
                                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </div>
                                </div>
                            </button>

                            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                @if($heroTrack->genre)
                                    <a href="{{ route('browse', ['genre' => $heroTrack->genre->slug]) }}" wire:navigate class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600 transition hover:text-primary">
                                        {{ $heroTrack->genre->name }}
                                    </a>
                                @endif
                                @if($heroTrack->mood_label)
                                    <span class="rounded-full bg-white/15 px-3 py-1 font-semibold text-white/85 backdrop-blur-sm">{{ $heroTrack->mood_label }}</span>
                                @endif
                                <span>{{ number_format($heroTrack->play_count) }} plays</span>
                                <span>{{ number_format($heroTrack->downloads_count) }} downloads</span>
                                <x-track-duration :track="$heroTrack" class="text-gray-400" />
                            </div>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-gray-500">The next spotlight track will appear here once new music lands.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-4 sm:px-6 lg:px-8">
        <div class="rounded-[2rem] glass-card p-5 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Genre Entry Points</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">Choose a scene fast</h2>
                </div>
                <p class="text-sm text-gray-500">Tap a genre to refocus every recommendation block on this page.</p>
            </div>

            <div class="mt-5 flex flex-wrap gap-3">
                <button wire:click="setGenre(null)"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition {{ is_null($genre) ? 'bg-primary text-white shadow-sm' : 'border border-white/70 bg-white/80 text-gray-600 hover:border-primary/30 hover:text-primary' }}">
                    All Scenes
                </button>
                @foreach($genres as $index => $genreOption)
                    @php
                        $fallbackColors = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#06b6d4', '#3b82f6', '#ec4899', '#8b5cf6', '#14b8a6', '#64748b'];
                        $color = $genreOption->color ?: $fallbackColors[$index % count($fallbackColors)];
                    @endphp
                    <button wire:click="setGenre('{{ $genreOption->slug }}')"
                            class="rounded-full border px-4 py-2 text-sm font-semibold transition {{ $genre === $genreOption->slug ? 'text-white shadow-sm' : 'border-white/70 bg-white/80 text-gray-600 hover:shadow-sm' }}"
                            style="{{ $genre === $genreOption->slug ? 'background: '.$color.'; border-color: transparent;' : 'border-color: rgba(255,255,255,0.7);' }}">
                        {{ $genreOption->name }}
                        <span class="ml-1 text-[11px] {{ $genre === $genreOption->slug ? 'text-white/75' : 'text-gray-400' }}">{{ number_format($genreOption->tracks_count) }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    @if($showBrowseMoodFilters)
        <section class="mx-auto max-w-7xl px-4 pb-4 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] glass-card p-5 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Mood Filters</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">Browse by vibe, not just genre</h2>
                    </div>
                    <p class="text-sm text-gray-500">Each mood blends track metadata with genre-derived fallback logic.</p>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button wire:click="setMood(null)"
                            class="rounded-full px-4 py-2 text-sm font-semibold transition {{ is_null($mood) ? 'bg-gray-900 text-white shadow-sm' : 'border border-white/70 bg-white/80 text-gray-600 hover:border-gray-300 hover:text-gray-900' }}">
                        All Vibes
                    </button>
                    @foreach($moods as $slug => $definition)
                        <button wire:click="setMood('{{ $slug }}')"
                                class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $mood === $slug ? 'bg-gray-900 text-white shadow-sm' : 'border border-white/70 bg-white/80 text-gray-600 hover:border-gray-300 hover:text-gray-900' }}">
                            {{ $definition['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($showBrowseEditorPicks && $browseEditorPicksPosition === 'before-personalized' && $editorPicks->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pb-4 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] glass-card p-5 sm:p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Editor's Picks</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">Curated standouts for this lane</h2>
                        <p class="mt-2 max-w-2xl text-sm text-gray-500">
                            {{ $selectedGenre
                                ? 'Featured tracks the team is actively backing inside '.$selectedGenre->name.'.'
                                : ($selectedMood
                                    ? 'Featured tracks that best represent the '.$selectedMood['label'].' lane.'
                                    : 'Featured tracks hand-picked to anchor discovery across the catalog.') }}
                        </p>
                    </div>
                    <a href="{{ route('browse', array_filter(['genre' => $genre, 'mood' => $mood, 'sort' => 'popular'])) }}" wire:navigate class="text-sm font-semibold text-primary transition hover:text-primary-600">View lane</a>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
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
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @auth
        @if($showBrowsePersonalized && $personalizedTracks->isNotEmpty())
            <section class="mx-auto max-w-7xl px-4 pb-4 sm:px-6 lg:px-8">
                <div class="rounded-[2rem] glass-card p-5 sm:p-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">For You</p>
                            <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">Because of what you already play</h2>
                        </div>
                        <a href="{{ route('listener.dashboard') }}" wire:navigate class="text-sm font-semibold text-primary transition hover:text-primary-600">Open taste profile</a>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
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
                                            @if($track->mood_label)
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ $track->mood_label }}</span>
                                            @endif
                                            @if($track->genre)
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ $track->genre->name }}</span>
                                            @endif
                                            <x-track-duration :track="$track" class="text-gray-400" />
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

    @if($showBrowseEditorPicks && $browseEditorPicksPosition !== 'before-personalized' && $editorPicks->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pb-4 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] glass-card p-5 sm:p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Editor's Picks</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">Curated standouts for this lane</h2>
                        <p class="mt-2 max-w-2xl text-sm text-gray-500">
                            {{ $selectedGenre
                                ? 'Featured tracks the team is actively backing inside '.$selectedGenre->name.'.'
                                : ($selectedMood
                                    ? 'Featured tracks that best represent the '.$selectedMood['label'].' lane.'
                                    : 'Featured tracks hand-picked to anchor discovery across the catalog.') }}
                        </p>
                    </div>
                    <a href="{{ route('browse', array_filter(['genre' => $genre, 'mood' => $mood, 'sort' => 'popular'])) }}" wire:navigate class="text-sm font-semibold text-primary transition hover:text-primary-600">View lane</a>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
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
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @unless($selectedGenre)
        @if($showBrowseFreshThisWeek || $showBrowseArtistsToWatch)
            <section class="mx-auto max-w-7xl px-4 pb-4 sm:px-6 lg:px-8">
                <div class="grid gap-4 {{ $showBrowseFreshThisWeek && $showBrowseArtistsToWatch ? 'xl:grid-cols-[minmax(0,1.15fr),minmax(300px,0.85fr)]' : 'xl:grid-cols-1' }}">
                @if($showBrowseFreshThisWeek)
                <div class="rounded-[2rem] glass-card p-5 sm:p-6">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Fresh This Week</p>
                            <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">New movement worth checking early</h2>
                        </div>
                        <button wire:click="setSort('latest')" class="text-sm font-semibold text-primary transition hover:text-primary-600">Refresh latest</button>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @forelse($freshTracks as $track)
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
                                        @if($track->mood_label)
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ $track->mood_label }}</span>
                                        @endif
                                        <x-track-duration :track="$track" class="text-gray-400" />
                                    </div>
                                        <p class="mt-3 text-[11px] font-medium text-gray-400">{{ $track->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="col-span-full text-sm text-gray-500">Fresh releases will appear here once more tracks are published.</p>
                        @endforelse
                    </div>
                </div>
                @endif

                @if($showBrowseArtistsToWatch)
                <div class="rounded-[2rem] glass-card p-5 sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Artists To Watch</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">Scenes worth stepping into</h2>

                    <div class="mt-5 space-y-3">
                        @forelse($topArtists as $artist)
                            <a href="{{ route('artist.page', $artist) }}"
                               wire:navigate
                               class="flex items-center gap-3 rounded-[1.5rem] border border-white/60 bg-white/75 p-3 shadow-sm transition hover:-translate-y-0.5 hover:bg-white/95 hover:shadow-md">
                                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                    @if($artist->getFirstMediaUrl('avatar'))
                                        <img src="{{ $artist->getFirstMediaUrl('avatar', 'thumb') }}" alt="{{ $artist->avatar_alt }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-lg font-black text-primary-500">
                                            {{ strtoupper(substr($artist->display_name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-sm font-semibold text-gray-900">{{ $artist->display_name }}</p>
                                    @if($artist->is_featured)
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em] text-amber-700">Editor's Pick</span>
                                    @elseif($artist->is_verified)
                                        <span class="rounded-full bg-primary-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em] text-primary-700">Verified</span>
                                    @endif
                                </div>
                                    <p class="mt-1 text-xs text-gray-500">{{ number_format($artist->followers_count) }} followers · {{ number_format($artist->total_plays) }} plays</p>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-gray-500">Top artists will appear here as soon as discovery data fills in.</p>
                        @endforelse
                    </div>
                </div>
                @endif
                </div>
            </section>
        @endif

        @if($showBrowseSupportDirect && $supportTracks->isNotEmpty())
            <section class="mx-auto max-w-7xl px-4 pb-4 sm:px-6 lg:px-8">
                <div class="rounded-[2rem] glass-card p-5 sm:p-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Support Direct</p>
                            <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">Tracks that unlock artist support</h2>
                        </div>
                        <p class="text-sm text-gray-500">A focused lane for donation-powered releases and premium unlocks.</p>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($supportTracks as $track)
                            <article class="rounded-[1.5rem] border border-white/60 bg-white/75 p-3 shadow-sm transition hover:-translate-y-0.5 hover:bg-white/95 hover:shadow-md">
                                <div class="flex items-center gap-3">
                                    <button @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
                                            class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                        @if($track->getCoverUrl())
                                            <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->cover_alt }}" class="h-full w-full object-cover">
                                        @endif
                                    </button>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('track.show', $track) }}" wire:navigate class="truncate text-sm font-semibold text-gray-900 hover:text-primary">
                                                {{ $track->title }}
                                            </a>
                                            <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em] text-yellow-700">
                                                ${{ number_format($track->donation_amount, 2) }}
                                            </span>
                                        </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                        <a href="{{ route('artist.page', $track->artistProfile) }}" wire:navigate class="truncate hover:text-primary">
                                            {{ $track->artistProfile?->display_name ?? 'Unknown artist' }}
                                        </a>
                                        @if($track->mood_label)
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ $track->mood_label }}</span>
                                        @endif
                                        @if($track->genre)
                                            <span>{{ $track->genre->name }}</span>
                                        @endif
                                            <x-track-duration :track="$track" class="text-gray-400" />
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endunless

    <section class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Full Catalog</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">
                    {{ $selectedGenre
                        ? $selectedGenre->name.' tracks'
                        : ($selectedMood ? $selectedMood['label'].' tracks' : 'All discoverable tracks') }}
                </h2>
            </div>
            <p class="text-sm text-gray-500">Scroll the full catalog with the current genre, mood, and sort filters applied.</p>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
            @forelse($tracks as $track)
                <article class="group overflow-hidden rounded-[1.5rem] glass-card transition hover:-translate-y-1 hover:shadow-glass-hover">
                    <div @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })" class="relative block aspect-square w-full cursor-pointer overflow-hidden">
                        @if($track->getCoverUrl())
                            <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->cover_alt }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary-100 to-primary-200">
                                <svg class="h-10 w-10 text-primary-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/></svg>
                            </div>
                        @endif

                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent opacity-0 transition group-hover:opacity-100"></div>
                        <div class="absolute inset-x-0 bottom-3 flex items-center justify-between px-3 opacity-0 transition group-hover:opacity-100">
                            <button @click.stop="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur-sm transition hover:bg-white/25">
                                <span class="sr-only">Play {{ $track->title }}</span>
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </button>
                            <button @click.stop="Livewire.dispatch('queue-track', { id: {{ $track->id }} })"
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-black/35 text-white backdrop-blur-sm transition hover:bg-black/50">
                                <span class="sr-only">Add {{ $track->title }} to queue</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>

                        @if($track->requires_donation)
                            <span class="absolute right-2 top-2 rounded-full bg-yellow-400 px-2 py-1 text-[10px] font-bold text-black shadow-sm">
                                ${{ number_format($track->donation_amount, 2) }}
                            </span>
                        @endif
                    </div>

                    <div class="space-y-2 p-3">
                        <a href="{{ route('track.show', $track) }}" wire:navigate class="block truncate text-sm font-semibold text-gray-900 hover:text-primary">
                            {{ $track->title }}
                        </a>
                        <a href="{{ route('artist.page', $track->artistProfile) }}"
                           wire:navigate
                           class="block truncate text-xs text-gray-500 transition hover:text-primary">
                            {{ $track->artistProfile?->display_name ?? 'Unknown artist' }}
                        </a>
                        <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-gray-400">
                            <span>{{ number_format($track->play_count) }} plays</span>
                            @if($track->mood_label)
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500">{{ $track->mood_label }}</span>
                            @endif
                            <x-track-duration :track="$track" class="shrink-0 text-[11px] text-gray-400" :icon="false" />
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-[2rem] glass-card px-6 py-16 text-center">
                    <h3 class="text-xl font-bold text-gray-800">No tracks match this filter yet</h3>
                    <p class="mt-2 text-sm text-gray-500">Try another genre or switch back to the full catalog.</p>
                    <button wire:click="clearFilters"
                            class="mt-5 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-500">
                        Reset Browse
                    </button>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $tracks->links() }}
        </div>
    </section>
</div>
