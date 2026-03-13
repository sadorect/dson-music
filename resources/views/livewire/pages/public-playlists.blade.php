<?php

use App\Models\Playlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.glass-app')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $sort = 'updated';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Playlist::query()
            ->with(['user', 'media'])
            ->withCount('tracks')
            ->where('is_public', true)
            ->when($this->search !== '', function ($query): void {
                $term = '%' . trim($this->search) . '%';

                $query->where(function ($nested) use ($term): void {
                    $nested
                        ->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', $term));
                });
            });

        match ($this->sort) {
            'newest' => $query->latest('created_at'),
            'tracks' => $query->orderByDesc('tracks_count')->latest('updated_at'),
            'name' => $query->orderBy('name'),
            default => $query->latest('updated_at'),
        };

        /** @var LengthAwarePaginator $playlists */
        $playlists = $query->paginate(12);

        $featured = Cache::remember('public-playlists.featured.v1', now()->addMinutes(10), fn () => Playlist::query()
            ->with(['user', 'media'])
            ->withCount('tracks')
            ->where('is_public', true)
            ->orderByDesc('tracks_count')
            ->latest('updated_at')
            ->take(3)
            ->get());

        $stats = Cache::remember('public-playlists.stats.v1', now()->addMinutes(10), fn () => [
            'playlists' => Playlist::query()->where('is_public', true)->count(),
            'tracks' => Playlist::query()->where('is_public', true)->withCount('tracks')->get()->sum('tracks_count'),
            'updatedToday' => Playlist::query()->where('is_public', true)->whereDate('updated_at', today())->count(),
        ]);

        return [
            'playlists' => $playlists,
            'featured' => $featured,
            'stats' => $stats,
            'sortOptions' => [
                'updated' => 'Recently Updated',
                'newest' => 'Newest',
                'tracks' => 'Most Tracks',
                'name' => 'Alphabetical',
            ],
            'seo' => [
                'title' => 'Public Playlists',
                'description' => 'Explore public playlists curated by listeners and discover fresh music collections on GrinMuzik.',
                'canonical' => route('playlists.public'),
                'type' => 'website',
                'json_ld' => [
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'CollectionPage',
                        'name' => 'Public Playlists',
                        'url' => route('playlists.public'),
                        'description' => 'Explore public playlists curated by listeners and discover fresh music collections.',
                    ],
                ],
            ],
        ];
    }
};
?>

<div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="glass-card relative overflow-hidden rounded-[2rem] px-5 py-6 sm:px-8 sm:py-8">
            <div class="absolute inset-y-0 right-0 hidden w-1/3 bg-gradient-to-l from-primary/10 to-transparent lg:block"></div>

            <div class="relative z-10 grid gap-6 lg:grid-cols-[minmax(0,1.5fr),minmax(280px,0.9fr)] lg:items-end">
                <div class="max-w-3xl">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Public Library</p>
                    <h1 class="text-3xl font-black tracking-tight text-gray-900 sm:text-5xl">Community playlists worth diving into</h1>
                    <p class="mt-3 max-w-2xl text-sm text-gray-500 sm:text-base">
                        Browse listener-made collections, jump into shared moods, and open public playlist pages built for discovery.
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    @foreach([
                        ['label' => 'Playlists', 'value' => number_format($stats['playlists'])],
                        ['label' => 'Tracks', 'value' => number_format($stats['tracks'])],
                        ['label' => 'Updated Today', 'value' => number_format($stats['updatedToday'])],
                    ] as $metric)
                        <div class="rounded-2xl border border-white/60 bg-white/80 px-4 py-3 text-center shadow-sm backdrop-blur-sm">
                            <p class="text-lg font-black text-gray-900 sm:text-2xl">{{ $metric['value'] }}</p>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-400">{{ $metric['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="relative z-10 mt-6 grid gap-3 rounded-[1.6rem] border border-white/60 bg-white/75 p-3 shadow-sm backdrop-blur-sm md:grid-cols-[minmax(0,1fr),220px]">
                <label class="block">
                    <span class="sr-only">Search public playlists</span>
                    <input
                        type="search"
                        wire:model.live.debounce.350ms="search"
                        placeholder="Search playlists, descriptions, or curators"
                        class="w-full rounded-2xl border border-white/60 bg-white/90 px-4 py-3 text-sm text-gray-700 placeholder-gray-400 shadow-sm outline-none transition focus:border-primary/30 focus:ring-2 focus:ring-primary/10"
                    >
                </label>

                <label class="block">
                    <span class="sr-only">Sort playlists</span>
                    <select
                        wire:model.live="sort"
                        class="w-full rounded-2xl border border-white/60 bg-white/90 px-4 py-3 text-sm text-gray-700 shadow-sm outline-none transition focus:border-primary/30 focus:ring-2 focus:ring-primary/10"
                    >
                        @foreach($sortOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </section>

        @if($featured->isNotEmpty())
            <section class="grid gap-4 xl:grid-cols-3">
                @foreach($featured as $playlist)
                    <article class="group glass-card overflow-hidden rounded-[2rem] transition hover:-translate-y-0.5 hover:bg-white/90 hover:shadow-lg">
                        <a href="{{ route('playlist.show', $playlist->slug) }}" wire:navigate class="block">
                            <div class="relative aspect-[1.25/1] overflow-hidden bg-gradient-to-br from-primary-100 to-primary-200">
                                @if($playlist->getCoverUrl())
                                    <img src="{{ $playlist->getCoverUrl() }}" alt="{{ $playlist->cover_alt }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <svg class="h-14 w-14 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h10"/>
                                        </svg>
                                    </div>
                                @endif

                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 via-black/25 to-transparent p-5">
                                    <div class="inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/90">
                                        Featured Collection
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4 p-5">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="truncate text-lg font-black tracking-tight text-gray-900">{{ $playlist->name }}</h2>
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Public</span>
                                    </div>
                                    <p class="mt-2 line-clamp-2 text-sm text-gray-500">
                                        {{ $playlist->description ?: 'A listener-curated collection built for discovering more of the music you already love.' }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-400">
                                    <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">{{ $playlist->tracks_count }} tracks</span>
                                    <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">By {{ $playlist->user?->name ?? 'Unknown' }}</span>
                                    <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">{{ $playlist->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </section>
        @endif

        <section class="glass-card overflow-hidden rounded-[2rem]">
            <div class="flex flex-col gap-2 border-b border-white/40 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-lg font-black tracking-tight text-gray-900">Browse all public playlists</h2>
                    <p class="text-sm text-gray-500">
                        @if(trim($search) !== '')
                            Results for "{{ $search }}"
                        @else
                            Freshly updated collections from across the community
                        @endif
                    </p>
                </div>

                <div class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-500">
                    <span>{{ number_format($playlists->total()) }}</span>
                    <span>{{ \Illuminate\Support\Str::plural('playlist', $playlists->total()) }}</span>
                </div>
            </div>

            @if($playlists->isEmpty())
                <div class="px-6 py-20 text-center">
                    <p class="text-lg font-semibold text-gray-800">No matching public playlists yet</p>
                    <p class="mt-2 text-sm text-gray-500">Try a different search term or check back as listeners publish new collections.</p>
                </div>
            @else
                <div class="grid gap-4 p-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($playlists as $playlist)
                        <article class="glass-card rounded-[1.7rem] p-4 transition hover:-translate-y-0.5 hover:bg-white/90 hover:shadow-lg">
                            <a href="{{ route('playlist.show', $playlist->slug) }}" wire:navigate class="block">
                                <div class="flex items-start gap-4">
                                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                        @if($playlist->getCoverUrl())
                                            <img src="{{ $playlist->getCoverUrl() }}" alt="{{ $playlist->cover_alt }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center">
                                                <svg class="h-8 w-8 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h10"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="truncate text-base font-semibold text-gray-900">{{ $playlist->name }}</h3>
                                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Public</span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">Curated by {{ $playlist->user?->name ?? 'Unknown' }}</p>
                                        <p class="mt-2 line-clamp-2 text-sm text-gray-500">
                                            {{ $playlist->description ?: 'Curated tracks collected and shared with the community.' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-gray-400">
                                    <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">{{ $playlist->tracks_count }} tracks</span>
                                    <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">Updated {{ $playlist->updated_at->diffForHumans() }}</span>
                                </div>

                                <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-primary">
                                    Open playlist
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>

                @if($playlists->hasPages())
                    <div class="border-t border-white/40 px-6 py-4">{{ $playlists->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</div>
