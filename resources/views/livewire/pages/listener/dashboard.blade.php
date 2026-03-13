<?php

use App\Models\Track;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function with(): array
    {
        $user = auth()->user();

        $recentHistory = $user->playHistory()
            ->with(['track.artistProfile.user', 'track.genre'])
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        $likedTracks = $user->likes()
            ->with(['artistProfile.user', 'genre'])
            ->orderByDesc('likes.created_at')
            ->take(6)
            ->get();

        $playlists = $user->playlists()
            ->withCount('tracks')
            ->orderByDesc('updated_at')
            ->take(6)
            ->get();

        $tasteSeedTracks = collect()
            ->merge($user->likes()->with(['artistProfile.user', 'genre'])->orderByDesc('likes.created_at')->take(40)->get())
            ->merge(
                $user->playHistory()
                    ->with(['track.artistProfile.user', 'track.genre'])
                    ->orderByDesc('created_at')
                    ->take(60)
                    ->get()
                    ->pluck('track')
                    ->filter()
            )
            ->filter();

        $topGenres = $tasteSeedTracks
            ->filter(fn (Track $track) => $track->genre)
            ->groupBy(fn (Track $track) => $track->genre->id)
            ->map(fn (Collection $group) => [
                'genre' => $group->first()->genre,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->take(4)
            ->values();

        $topArtists = $tasteSeedTracks
            ->filter(fn (Track $track) => $track->artistProfile)
            ->groupBy(fn (Track $track) => $track->artistProfile->id)
            ->map(fn (Collection $group) => [
                'artist' => $group->first()->artistProfile,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->take(4)
            ->values();

        $excludeTrackIds = $user->likes()->pluck('tracks.id')
            ->merge($user->playHistory()->latest()->take(50)->pluck('track_id'))
            ->filter()
            ->unique()
            ->values();

        $recommendedTracks = Track::query()
            ->with(['artistProfile.user', 'genre'])
            ->where('is_published', true)
            ->when(
                $topGenres->isNotEmpty() || $topArtists->isNotEmpty(),
                function ($query) use ($topGenres, $topArtists): void {
                    $query->where(function ($nested) use ($topGenres, $topArtists): void {
                        if ($topGenres->isNotEmpty()) {
                            $nested->whereIn('genre_id', $topGenres->pluck('genre.id')->all());
                        }

                        if ($topArtists->isNotEmpty()) {
                            $method = $topGenres->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                            $nested->{$method}('artist_profile_id', $topArtists->pluck('artist.id')->all());
                        }
                    });
                }
            )
            ->whereNotIn('id', $excludeTrackIds->all())
            ->orderByDesc('play_count')
            ->orderByDesc('downloads_count')
            ->take(6)
            ->get();

        if ($recommendedTracks->isEmpty()) {
            $recommendedTracks = Track::query()
                ->with(['artistProfile.user', 'genre'])
                ->where('is_published', true)
                ->whereNotIn('id', $excludeTrackIds->all())
                ->orderByDesc('play_count')
                ->take(6)
                ->get();
        }

        return [
            'recentHistory' => $recentHistory,
            'likedTracks' => $likedTracks,
            'playlists' => $playlists,
            'likedCount' => $user->likes()->count(),
            'historyCount' => $user->playHistory()->count(),
            'playlistCount' => $user->playlists()->count(),
            'topGenres' => $topGenres,
            'topArtists' => $topArtists,
            'recommendedTracks' => $recommendedTracks,
            'tasteStats' => [
                'artists' => $tasteSeedTracks->pluck('artist_profile_id')->filter()->unique()->count(),
                'genres' => $tasteSeedTracks->pluck('genre_id')->filter()->unique()->count(),
                'hours' => number_format(max(0, round($tasteSeedTracks->sum('duration') / 3600, 1)), 1),
            ],
        ];
    }
};
?>

<div class="max-w-6xl mx-auto px-4 py-8 space-y-8">

    <section class="glass-card rounded-[2rem] p-6 sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Listener Dashboard</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-gray-900 sm:text-4xl">My Music</h1>
                <p class="mt-2 max-w-2xl text-sm text-gray-500 sm:text-base">Welcome back, {{ auth()->user()->name }}. Your listening profile is taking shape, and the dashboard now reflects what you actually come back to.</p>
            </div>

            <div class="inline-flex items-center gap-2 rounded-full border border-white/60 bg-white/80 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-gray-500 shadow-sm">
                <span>{{ number_format($historyCount) }}</span>
                <span>Total plays tracked</span>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach([
            ['label' => 'Liked Tracks', 'value' => $likedCount, 'href' => route('listener.liked'), 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
            ['label' => 'Playlists', 'value' => $playlistCount, 'href' => route('listener.playlists'), 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
            ['label' => 'Plays', 'value' => $historyCount, 'href' => route('listener.history'), 'icon' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ] as $stat)
            <a href="{{ $stat['href'] }}" wire:navigate class="glass-card rounded-2xl p-5 hover:shadow-glass-hover transition">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-primary-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $stat['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800">{{ number_format($stat['value']) }}</p>
                        <p class="text-xs text-gray-500">{{ $stat['label'] }}</p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <section class="grid gap-6 lg:grid-cols-[minmax(0,1.05fr),minmax(260px,0.95fr)]">
        <div class="glass-card rounded-[2rem] p-6 sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Taste Map</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">What your listening says about you</h2>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center">
                    @foreach([
                        ['label' => 'Artists', 'value' => number_format($tasteStats['artists'])],
                        ['label' => 'Genres', 'value' => number_format($tasteStats['genres'])],
                        ['label' => 'Hours', 'value' => $tasteStats['hours']],
                    ] as $metric)
                        <div class="rounded-2xl border border-white/60 bg-white/80 px-3 py-2 shadow-sm">
                            <p class="text-lg font-black text-gray-900">{{ $metric['value'] }}</p>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-400">{{ $metric['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Top Genres</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @forelse($topGenres as $item)
                        <a href="{{ route('browse', ['genre' => $item['genre']->slug]) }}" wire:navigate class="rounded-full border border-white/70 bg-white/80 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-primary hover:text-primary">
                            {{ $item['genre']->name }}
                            <span class="ml-1 text-xs text-gray-400">{{ $item['count'] }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">Start liking and playing more music to build your genre profile.</p>
                    @endforelse
                </div>
            </div>

            <div class="mt-6">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Artists On Repeat</p>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @forelse($topArtists as $item)
                        <a href="{{ route('artist.page', $item['artist']) }}" wire:navigate class="rounded-[1.4rem] border border-white/50 bg-white/70 p-4 text-center transition hover:bg-white/90">
                            <div class="mx-auto h-14 w-14 overflow-hidden rounded-full bg-gradient-to-br from-primary-100 to-primary-200 ring-2 ring-white/80">
                                @if($item['artist']->getFirstMediaUrl('avatar'))
                                    <img src="{{ $item['artist']->getFirstMediaUrl('avatar', 'thumb') }}" alt="{{ $item['artist']->avatar_alt }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-lg font-black text-primary-400">
                                        {{ strtoupper(substr($item['artist']->display_name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <p class="mt-3 truncate text-sm font-semibold text-gray-900">{{ $item['artist']->display_name }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ $item['count'] }} signals</p>
                        </a>
                    @empty
                        <p class="col-span-full text-sm text-gray-500">Your favorite artists will start showing here once you build some listening history.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="glass-card rounded-[2rem] p-6 sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Because You Liked</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">Recommended next plays</h2>
            <div class="mt-5 space-y-3">
                @forelse($recommendedTracks as $track)
                    <article class="rounded-[1.4rem] border border-white/50 bg-white/70 p-3 transition hover:bg-white/90">
                        <div class="flex items-center gap-3">
                            <button @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })" class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                @if($track->getCoverUrl())
                                    <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->cover_alt }}" class="h-full w-full object-cover">
                                @endif
                            </button>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-900">{{ $track->title }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                    <span class="truncate">{{ $track->artistProfile?->display_name ?? 'Unknown artist' }}</span>
                                    @if($track->genre)
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ $track->genre->name }}</span>
                                    @endif
                                    <x-track-duration :track="$track" class="text-gray-400" />
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-gray-500">Recommendations will appear once your listening profile has a bit more shape.</p>
                @endforelse
            </div>
        </div>
    </section>

    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/40 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Recently Played</h2>
            <a href="{{ route('listener.history') }}" wire:navigate class="text-sm text-primary hover:underline">See all &rarr;</a>
        </div>
        @if($recentHistory->isEmpty())
            <p class="px-6 py-8 text-sm text-gray-500 text-center">No plays yet. Start exploring music.</p>
        @else
            <ul class="divide-y divide-white/30">
                @foreach($recentHistory as $play)
                    @if($play->track)
                        <li class="flex items-center gap-4 px-5 py-3 hover:bg-white/40 transition">
                            <button @click="Livewire.dispatch('play-track', { id: {{ $play->track->id }} })"
                                    class="w-10 h-10 rounded-lg bg-gray-200 overflow-hidden shrink-0">
                                @if($play->track->getCoverUrl())
                                    <img src="{{ $play->track->getCoverUrl() }}" class="w-full h-full object-cover" alt="{{ $play->track->cover_alt }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                                    </div>
                                @endif
                            </button>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $play->track->title }}</p>
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                    <span>{{ $play->track->artistProfile?->display_name ?? 'Unknown artist' }}</span>
                                    <x-track-duration :track="$play->track" class="text-gray-400" />
                                </div>
                            </div>
                            <span class="text-xs text-gray-400 shrink-0">{{ $play->created_at->diffForHumans() }}</span>
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif
    </div>

    <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr),minmax(0,1fr)]">
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/40 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Liked Tracks</h2>
                <a href="{{ route('listener.liked') }}" wire:navigate class="text-sm text-primary hover:underline">See all &rarr;</a>
            </div>
            @if($likedTracks->isEmpty())
                <p class="px-6 py-8 text-sm text-gray-500 text-center">No liked tracks yet.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 p-4">
                    @foreach($likedTracks as $track)
                        <button @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
                                class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/60 transition text-left">
                            <div class="w-10 h-10 rounded-lg bg-gray-200 overflow-hidden shrink-0">
                                @if($track->getCoverUrl())
                                    <img src="{{ $track->getCoverUrl() }}" class="w-full h-full object-cover" alt="{{ $track->cover_alt }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-gray-800 truncate">{{ $track->title }}</p>
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                    <span class="truncate">{{ $track->artistProfile?->display_name }}</span>
                                    <x-track-duration :track="$track" class="text-gray-400" />
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/40 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">My Playlists</h2>
                <a href="{{ route('listener.playlists') }}" wire:navigate class="text-sm text-primary hover:underline">See all &rarr;</a>
            </div>
            @if($playlists->isEmpty())
                <p class="px-6 py-8 text-sm text-gray-500 text-center">No playlists yet.</p>
            @else
                <ul class="divide-y divide-white/30">
                    @foreach($playlists as $pl)
                        <li class="flex items-center gap-3 px-5 py-3 hover:bg-white/40 transition">
                            <div class="w-11 h-11 rounded-2xl overflow-hidden bg-gradient-to-br from-primary to-primary-600 flex items-center justify-center shrink-0">
                                @if($pl->getCoverUrl())
                                    <img src="{{ $pl->getCoverUrl() }}" alt="{{ $pl->cover_alt }}" class="h-full w-full object-cover">
                                @else
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $pl->name }}</p>
                                <p class="text-xs text-gray-500">{{ $pl->tracks_count }} tracks</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $pl->is_public ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $pl->is_public ? 'Public' : 'Private' }}
                                </span>
                                @if($pl->is_public)
                                    <a href="{{ route('playlist.show', $pl->slug) }}" wire:navigate class="hidden text-xs font-semibold text-primary sm:inline">View</a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
