<?php

use App\Models\Like;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.glass-app')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function unlike(int $trackId): void
    {
        Like::where('user_id', auth()->id())->where('track_id', $trackId)->delete();
    }

    public function with(): array
    {
        $user = auth()->user();

        $query = $user->likes()
            ->with(['artistProfile.user', 'genre'])
            ->orderByDesc('likes.created_at');

        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';

            $query->where(function ($nested) use ($term): void {
                $nested
                    ->where('title', 'like', $term)
                    ->orWhereHas('artistProfile', fn ($artistQuery) => $artistQuery->where('stage_name', 'like', $term))
                    ->orWhereHas('genre', fn ($genreQuery) => $genreQuery->where('name', 'like', $term));
            });
        }

        return [
            'tracks' => $query->paginate(18),
            'likedCount' => $user->likes()->count(),
            'artistCount' => $user->likes()->distinct('artist_profile_id')->count('artist_profile_id'),
            'playlistCount' => $user->playlists()->count(),
        ];
    }
};
?>

<div class="max-w-6xl mx-auto px-4 py-8 space-y-6">
    <section class="glass-card rounded-[2rem] p-6 sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('listener.dashboard') }}" wire:navigate class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/60 bg-white/75 text-gray-500 transition hover:text-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Library</p>
                        <h1 class="text-3xl font-black tracking-tight text-gray-900 sm:text-4xl">Liked Tracks</h1>
                    </div>
                </div>
                <p class="mt-4 max-w-2xl text-sm text-gray-500 sm:text-base">Revisit the songs you have already saved, jump back into playback, and keep shaping your personal taste profile.</p>
            </div>

            <div class="grid grid-cols-3 gap-3 sm:min-w-[320px]">
                @foreach([
                    ['label' => 'Liked', 'value' => number_format($likedCount)],
                    ['label' => 'Artists', 'value' => number_format($artistCount)],
                    ['label' => 'Playlists', 'value' => number_format($playlistCount)],
                ] as $metric)
                    <div class="rounded-2xl border border-white/60 bg-white/80 px-4 py-3 text-center shadow-sm">
                        <p class="text-xl font-black text-gray-900">{{ $metric['value'] }}</p>
                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-400">{{ $metric['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 grid gap-3 rounded-[1.6rem] border border-white/60 bg-white/75 p-3 shadow-sm md:grid-cols-[minmax(0,1fr),220px]">
            <label class="block">
                <span class="sr-only">Search liked tracks</span>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Search liked tracks, artists, or genres"
                    class="w-full rounded-2xl border border-white/60 bg-white/90 px-4 py-3 text-sm text-gray-700 placeholder-gray-400 shadow-sm outline-none transition focus:border-primary/30 focus:ring-2 focus:ring-primary/10"
                >
            </label>

            <a href="{{ route('listener.playlists') }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500">
                Open My Playlists
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </section>

    <section class="glass-card overflow-hidden rounded-[2rem]">
        <div class="flex flex-col gap-2 border-b border-white/40 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 class="text-lg font-black tracking-tight text-gray-900">Saved tracks</h2>
                <p class="text-sm text-gray-500">
                    @if(trim($search) !== '')
                        Search results for "{{ $search }}"
                    @else
                        Every song you have liked so far
                    @endif
                </p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-500">
                <span>{{ number_format($tracks->total()) }}</span>
                <span>{{ \Illuminate\Support\Str::plural('track', $tracks->total()) }}</span>
            </div>
        </div>

        @if($tracks->isEmpty())
            <div class="px-6 py-20 text-center">
                <p class="text-lg font-semibold text-gray-800">No liked tracks yet</p>
                <p class="mt-2 text-sm text-gray-500">Like songs while browsing and they will show up here for quick access.</p>
            </div>
        @else
            <ul class="divide-y divide-white/30">
                @foreach($tracks as $i => $track)
                    <li class="group flex items-center gap-3 px-4 py-3 transition hover:bg-white/45 sm:px-6">
                        <div class="w-8 shrink-0 text-center text-sm font-semibold text-gray-400 group-hover:hidden">
                            {{ ($tracks->currentPage() - 1) * $tracks->perPage() + $i + 1 }}
                        </div>
                        <button
                            @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
                            class="hidden w-8 shrink-0 items-center justify-center text-primary group-hover:flex"
                            aria-label="Play {{ $track->title }}"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </button>

                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                            @if($track->getCoverUrl())
                                <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->title }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <svg class="h-5 w-5 text-primary-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/></svg>
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <a href="{{ route('track.show', $track->slug) }}" wire:navigate class="block truncate text-sm font-semibold text-gray-900 transition hover:text-primary">
                                {{ $track->title }}
                            </a>
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                @if($track->artistProfile)
                                    <a href="{{ route('artist.page', $track->artistProfile) }}" wire:navigate class="truncate hover:text-primary">
                                        {{ $track->artistProfile->stage_name ?? $track->artistProfile->user?->name ?? 'Unknown artist' }}
                                    </a>
                                @else
                                    <span>Unknown artist</span>
                                @endif
                                @if($track->genre)
                                    <span>&middot;</span>
                                    <span>{{ $track->genre->name }}</span>
                                @endif
                                <x-track-duration :track="$track" class="text-gray-400" />
                            </div>
                        </div>

                        <div class="hidden text-xs text-gray-400 md:block">{{ number_format($track->play_count) }} plays</div>

                        <button wire:click="unlike({{ $track->id }})"
                                class="inline-flex items-center gap-2 rounded-full border border-white/60 bg-white/80 px-3 py-2 text-xs font-semibold text-red-500 shadow-sm transition hover:bg-red-50 hover:text-red-600"
                                title="Remove from liked tracks">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            <span class="hidden sm:inline">Unlike</span>
                        </button>
                    </li>
                @endforeach
            </ul>

            <div class="border-t border-white/30 px-5 py-4">{{ $tracks->links() }}</div>
        @endif
    </section>
</div>
