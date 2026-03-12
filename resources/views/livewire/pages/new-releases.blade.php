<?php

use App\Models\Album;
use App\Models\Track;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.glass-app')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $view = 'tracks';

    public function with(): array
    {
        $featuredTracks = Track::with(['artistProfile.user', 'genre'])
            ->where('is_published', true)
            ->latest()
            ->take(3)
            ->get();

        /** @var LengthAwarePaginator $tracks */
        $tracks = Track::with(['artistProfile.user', 'genre', 'album'])
            ->where('is_published', true)
            ->latest()
            ->paginate(24, pageName: 'tracksPage');

        $recentAlbums = Album::with(['artist', 'genre'])
            ->withCount('tracks')
            ->where('is_published', true)
            ->orderByDesc('release_date')
            ->orderByDesc('created_at')
            ->paginate(16, pageName: 'albumsPage');

        $followedTrackIds = auth()->check()
            ? auth()->user()->followedArtists()->pluck('artist_profiles.id')
            : collect();

        $followedFresh = $followedTrackIds->isNotEmpty()
            ? Track::with(['artistProfile.user', 'genre'])
                ->where('is_published', true)
                ->whereIn('artist_profile_id', $followedTrackIds)
                ->latest()
                ->take(6)
                ->get()
            : collect();

        $releaseStats = [
            'today' => Track::where('is_published', true)->whereDate('created_at', today())->count(),
            'thisWeek' => Track::where('is_published', true)->where('created_at', '>=', now()->startOfWeek())->count(),
            'albums' => Album::where('is_published', true)->count(),
        ];

        return [
            'featuredTracks' => $featuredTracks,
            'tracks' => $tracks,
            'trackGroups' => $this->groupByRecency($tracks->getCollection()),
            'recentAlbums' => $recentAlbums,
            'albumGroups' => $this->groupByRecency($recentAlbums->getCollection(), 'release_date'),
            'featuredAlbums' => $recentAlbums->getCollection()->take(2),
            'followedFresh' => $followedFresh,
            'releaseStats' => $releaseStats,
        ];
    }

    public function setView(string $value): void
    {
        if (! in_array($value, ['tracks', 'albums'], true)) {
            return;
        }

        $this->view = $value;
        $this->resetPage(pageName: 'tracksPage');
        $this->resetPage(pageName: 'albumsPage');
    }

    protected function groupByRecency(Collection $items, string $dateField = 'created_at'): Collection
    {
        return collect([
            'Today' => $items->filter(fn ($item) => optional($item->{$dateField})->isToday()),
            'This Week' => $items->filter(fn ($item) => optional($item->{$dateField})->isCurrentWeek() && ! optional($item->{$dateField})->isToday()),
            'Earlier' => $items->filter(fn ($item) => optional($item->{$dateField})->lt(now()->startOfWeek())),
        ])->filter(fn (Collection $group) => $group->isNotEmpty());
    }
};
?>

<div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="glass-card relative overflow-hidden rounded-[2rem] p-6 sm:p-8">
            <div class="absolute inset-y-0 right-0 w-1/3 bg-gradient-to-l from-primary/10 to-transparent"></div>
            <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Fresh Drops</p>
                    <h1 class="text-3xl font-black tracking-tight text-gray-900 sm:text-5xl">New releases with more pulse</h1>
                    <p class="mt-3 max-w-2xl text-sm text-gray-500 sm:text-base">Catch the newest track drops, fresh albums, and artists you already follow without digging through a plain list.</p>
                </div>

                <div class="grid grid-cols-3 gap-3 sm:gap-4">
                    @foreach([
                        ['label' => 'Today', 'value' => number_format($releaseStats['today'])],
                        ['label' => 'This Week', 'value' => number_format($releaseStats['thisWeek'])],
                        ['label' => 'Albums', 'value' => number_format($releaseStats['albums'])],
                    ] as $metric)
                        <div class="rounded-2xl border border-white/60 bg-white/80 px-4 py-3 text-center shadow-sm backdrop-blur-sm">
                            <p class="text-lg font-black text-gray-900 sm:text-2xl">{{ $metric['value'] }}</p>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-400">{{ $metric['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        @if($featuredTracks->isNotEmpty())
            <section class="grid gap-4 lg:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)]">
                @php $leadTrack = $featuredTracks->first(); @endphp
                <article class="glass-card overflow-hidden rounded-[2rem]">
                    <div class="grid gap-0 md:grid-cols-[16rem_minmax(0,1fr)]">
                        <div class="relative min-h-[16rem] bg-gradient-to-br from-primary-100 to-primary-200">
                            @if($leadTrack->getCoverUrl('large'))
                                <img src="{{ $leadTrack->getCoverUrl('large') }}" alt="{{ $leadTrack->title }}" class="h-full w-full object-cover">
                            @elseif($leadTrack->getCoverUrl())
                                <img src="{{ $leadTrack->getCoverUrl() }}" alt="{{ $leadTrack->title }}" class="h-full w-full object-cover">
                            @endif
                            <span class="absolute left-4 top-4 rounded-full bg-black/70 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-white">Just Dropped</span>
                        </div>
                        <div class="flex flex-col justify-between gap-5 p-6">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Editorial Pick</p>
                                <h2 class="mt-3 text-3xl font-black tracking-tight text-gray-900">{{ $leadTrack->title }}</h2>
                                <p class="mt-2 text-sm text-gray-500">{{ $leadTrack->artistProfile?->stage_name ?? 'Unknown Artist' }} &middot; {{ $leadTrack->genre?->name ?? 'Open format' }}</p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ $leadTrack->created_at->diffForHumans() }}</span>
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ $leadTrack->formatted_duration }}</span>
                                    @if($leadTrack->requires_donation)
                                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">${{ number_format($leadTrack->donation_amount, 2) }}</span>
                                    @else
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Free</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <button @click="Livewire.dispatch('play-track', { id: {{ $leadTrack->id }} })" class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-600">Play Now</button>
                                <button @click="Livewire.dispatch('queue-track', { id: {{ $leadTrack->id }} })" class="rounded-full border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary">Queue</button>
                                <a href="{{ route('artist.page', $leadTrack->artistProfile) }}" class="rounded-full border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary">View Artist</a>
                            </div>
                        </div>
                    </div>
                </article>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    @foreach($featuredTracks->slice(1) as $track)
                        <article class="glass-card flex items-center gap-4 rounded-[1.8rem] p-4 transition hover:-translate-y-0.5 hover:bg-white/85 hover:shadow-lg">
                            <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                @if($track->getCoverUrl())
                                    <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->title }}" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-base font-semibold text-gray-900">{{ $track->title }}</p>
                                <p class="truncate text-sm text-gray-500">{{ $track->artistProfile?->stage_name ?? 'Unknown Artist' }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600">{{ $track->created_at->diffForHumans() }}</span>
                                    @if($track->genre)
                                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600">{{ $track->genre->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if($followedFresh->isNotEmpty())
            <section class="glass-card rounded-[2rem] p-6 sm:p-8">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">For You</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">Fresh from artists you follow</h2>
                    </div>
                    <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ $followedFresh->count() }} picks</span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($followedFresh as $track)
                        <article class="glass-card rounded-[1.6rem] p-4 transition hover:-translate-y-0.5 hover:bg-white/90 hover:shadow-lg">
                            <div class="flex items-center gap-4">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                    @if($track->getCoverUrl())
                                        <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->title }}" class="h-full w-full object-cover">
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-gray-900">{{ $track->title }}</p>
                                    <p class="truncate text-sm text-gray-500">{{ $track->artistProfile?->stage_name ?? 'Unknown Artist' }}</p>
                                    <p class="mt-1 text-xs text-gray-400">{{ $track->created_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center gap-2">
                                <button @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })" class="rounded-full bg-primary px-3 py-2 text-xs font-semibold text-white transition hover:bg-primary-600">Play</button>
                                <button @click="Livewire.dispatch('queue-track', { id: {{ $track->id }} })" class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:border-primary hover:text-primary">Queue</button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="sticky top-20 z-20 rounded-[1.5rem] border border-white/60 bg-white/75 p-3 shadow-sm backdrop-blur-xl">
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="setView('tracks')"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $view === 'tracks' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-500 hover:text-gray-900' }}">
                    Tracks
                </button>
                <button wire:click="setView('albums')"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $view === 'albums' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-500 hover:text-gray-900' }}">
                    Albums
                </button>
            </div>
        </section>

        @if($view === 'tracks')
            <section class="space-y-6">
                @forelse($trackGroups as $label => $group)
                    <div class="glass-card overflow-hidden rounded-[2rem]">
                        <div class="border-b border-white/40 px-6 py-4">
                            <h3 class="text-lg font-black text-gray-900">{{ $label }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $label === 'Today' ? 'The freshest tracks landing right now.' : ($label === 'This Week' ? 'Recent uploads still building momentum.' : 'Worth circling back to.') }}</p>
                        </div>

                        <div class="divide-y divide-white/40">
                            @foreach($group as $track)
                                <article class="group flex flex-col gap-4 px-4 py-4 transition hover:bg-white/60 sm:px-6 md:flex-row md:items-center">
                                    <button @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
                                            class="relative h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 group/play">
                                        @if($track->getCoverUrl())
                                            <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->title }}" class="h-full w-full object-cover">
                                        @endif
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/35 opacity-0 transition group-hover/play:opacity-100">
                                            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        </div>
                                    </button>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <a href="{{ route('track.show', $track->slug) }}" wire:navigate class="block truncate text-base font-semibold text-gray-900 transition hover:text-primary">{{ $track->title }}</a>
                                                <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                                                    <span class="truncate">{{ $track->artistProfile?->stage_name ?? 'Unknown Artist' }}</span>
                                                    @if($track->genre)
                                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ $track->genre->name }}</span>
                                                    @endif
                                                    <x-track-duration :track="$track" class="text-gray-400" />
                                                </div>
                                            </div>
                                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">{{ $track->created_at->diffForHumans() }}</div>
                                        </div>

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @if($track->requires_donation)
                                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">${{ number_format($track->donation_amount, 2) }}</span>
                                            @else
                                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Free</span>
                                            @endif
                                            @if($track->album)
                                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ $track->album->title }}</span>
                                            @endif
                                        </div>

                                        <div class="mt-3 flex items-center gap-2">
                                            <button @click="Livewire.dispatch('queue-track', { id: {{ $track->id }} })"
                                                    class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-500 transition hover:border-primary hover:text-primary">
                                                Queue
                                            </button>
                                            @livewire('like-button', ['trackId' => $track->id], key('new-release-like-'.$track->id))
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="glass-card rounded-[2rem] px-6 py-16 text-center">
                        <p class="text-lg font-semibold text-gray-800">No tracks yet</p>
                        <p class="mt-2 text-sm text-gray-500">Check back soon for fresh uploads.</p>
                    </div>
                @endforelse

                @if($tracks->hasPages())
                    <div class="glass-card rounded-[1.5rem] px-6 py-4">{{ $tracks->links() }}</div>
                @endif
            </section>
        @else
            <section class="space-y-6">
                @if($featuredAlbums->isNotEmpty())
                    <div class="grid gap-4 lg:grid-cols-2">
                        @foreach($featuredAlbums as $album)
                            <article class="glass-card overflow-hidden rounded-[2rem] transition hover:-translate-y-0.5 hover:bg-white/90 hover:shadow-lg">
                                <div class="grid gap-0 md:grid-cols-[12rem_minmax(0,1fr)]">
                                    <div class="min-h-[12rem] bg-gradient-to-br from-primary-100 to-primary-200">
                                        @if($album->getCoverUrl('large'))
                                            <img src="{{ $album->getCoverUrl('large') }}" alt="{{ $album->title }}" class="h-full w-full object-cover">
                                        @elseif($album->getCoverUrl())
                                            <img src="{{ $album->getCoverUrl() }}" alt="{{ $album->title }}" class="h-full w-full object-cover">
                                        @endif
                                    </div>
                                    <div class="p-5">
                                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Album Spotlight</p>
                                        <h3 class="mt-3 text-2xl font-black tracking-tight text-gray-900">{{ $album->title }}</h3>
                                        <p class="mt-2 text-sm text-gray-500">{{ $album->artist?->stage_name ?? 'Unknown Artist' }} &middot; {{ ucfirst($album->type) }}</p>
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ $album->tracks_count }} tracks</span>
                                            @if($album->genre)
                                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ $album->genre->name }}</span>
                                            @endif
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ optional($album->release_date ?? $album->created_at)->diffForHumans() }}</span>
                                        </div>
                                        <div class="mt-5">
                                            <a href="{{ route('artist.page', $album->artist) }}" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary">View Artist</a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                @forelse($albumGroups as $label => $group)
                    <div class="glass-card overflow-hidden rounded-[2rem]">
                        <div class="border-b border-white/40 px-6 py-4">
                            <h3 class="text-lg font-black text-gray-900">{{ $label }}</h3>
                            <p class="mt-1 text-sm text-gray-500">Latest albums and projects gathered by release recency.</p>
                        </div>

                        <div class="grid gap-4 p-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($group as $album)
                                <article class="glass-card rounded-[1.6rem] p-4 transition hover:-translate-y-0.5 hover:bg-white/90 hover:shadow-lg">
                                    <div class="aspect-[1.1/1] overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                        @if($album->getCoverUrl())
                                            <img src="{{ $album->getCoverUrl() }}" alt="{{ $album->title }}" class="h-full w-full object-cover">
                                        @endif
                                    </div>
                                    <div class="mt-4">
                                        <h4 class="truncate text-base font-semibold text-gray-900">{{ $album->title }}</h4>
                                        <p class="mt-1 truncate text-sm text-gray-500">{{ $album->artist?->stage_name ?? 'Unknown Artist' }}</p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600">{{ ucfirst($album->type) }}</span>
                                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600">{{ $album->tracks_count }} tracks</span>
                                            @if($album->genre)
                                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600">{{ $album->genre->name }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-4 flex items-center justify-between">
                                            <span class="text-xs text-gray-400">{{ optional($album->release_date ?? $album->created_at)->diffForHumans() }}</span>
                                            <a href="{{ route('artist.page', $album->artist) }}" class="text-xs font-semibold text-primary transition hover:text-primary-600">Artist page</a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="glass-card rounded-[2rem] px-6 py-16 text-center">
                        <p class="text-lg font-semibold text-gray-800">No albums yet</p>
                        <p class="mt-2 text-sm text-gray-500">Albums and EPs will appear here as artists publish them.</p>
                    </div>
                @endforelse

                @if($recentAlbums->hasPages())
                    <div class="glass-card rounded-[1.5rem] px-6 py-4">{{ $recentAlbums->links() }}</div>
                @endif
            </section>
        @endif
    </div>
</div>
