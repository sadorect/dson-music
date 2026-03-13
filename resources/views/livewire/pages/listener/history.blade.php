<?php

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

    public function with(): array
    {
        $user = auth()->user();

        $query = $user
            ->playHistory()
            ->with(['track.artistProfile.user', 'track.genre'])
            ->orderByDesc('created_at');

        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';

            $query->whereHas('track', function ($trackQuery) use ($term): void {
                $trackQuery
                    ->where('title', 'like', $term)
                    ->orWhereHas('artistProfile', fn ($artistQuery) => $artistQuery->where('stage_name', 'like', $term));
            });
        }

        return [
            'history' => $query->paginate(24),
            'historyCount' => $user->playHistory()->count(),
            'uniqueTracks' => $user->playHistory()->whereNotNull('track_id')->distinct('track_id')->count('track_id'),
            'likedCount' => $user->likes()->count(),
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
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Listening Activity</p>
                        <h1 class="text-3xl font-black tracking-tight text-gray-900 sm:text-4xl">Play History</h1>
                    </div>
                </div>
                <p class="mt-4 max-w-2xl text-sm text-gray-500 sm:text-base">Track your recent listening, jump back into a song instantly, and reconnect with the artists and genres you keep returning to.</p>
            </div>

            <div class="grid grid-cols-3 gap-3 sm:min-w-[320px]">
                @foreach([
                    ['label' => 'Plays', 'value' => number_format($historyCount)],
                    ['label' => 'Unique Tracks', 'value' => number_format($uniqueTracks)],
                    ['label' => 'Liked', 'value' => number_format($likedCount)],
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
                <span class="sr-only">Search play history</span>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Search history by track or artist"
                    class="w-full rounded-2xl border border-white/60 bg-white/90 px-4 py-3 text-sm text-gray-700 placeholder-gray-400 shadow-sm outline-none transition focus:border-primary/30 focus:ring-2 focus:ring-primary/10"
                >
            </label>

            <a href="{{ route('browse') }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500">
                Discover More Music
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </section>

    <section class="glass-card overflow-hidden rounded-[2rem]">
        <div class="flex flex-col gap-2 border-b border-white/40 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 class="text-lg font-black tracking-tight text-gray-900">Recent listening</h2>
                <p class="text-sm text-gray-500">
                    @if(trim($search) !== '')
                        Search results for "{{ $search }}"
                    @else
                        Your latest playback activity across the site
                    @endif
                </p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-500">
                <span>{{ number_format($history->total()) }}</span>
                <span>{{ \Illuminate\Support\Str::plural('entry', $history->total()) }}</span>
            </div>
        </div>

        @if($history->isEmpty())
            <div class="px-6 py-20 text-center">
                <p class="text-lg font-semibold text-gray-800">No plays yet</p>
                <p class="mt-2 text-sm text-gray-500">Start listening to tracks and your activity timeline will show up here.</p>
            </div>
        @else
            <ul class="divide-y divide-white/30">
                @foreach($history as $play)
                    @if($play->track)
                        <li class="group flex items-center gap-3 px-4 py-3 transition hover:bg-white/45 sm:px-6">
                            <button
                                @click="Livewire.dispatch('play-track', { id: {{ $play->track->id }} })"
                                class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200"
                                aria-label="Play {{ $play->track->title }}"
                            >
                                @if($play->track->getCoverUrl())
                                    <img src="{{ $play->track->getCoverUrl() }}" class="h-full w-full object-cover" alt="{{ $play->track->title }}">
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <svg class="h-5 w-5 text-primary-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/></svg>
                                    </div>
                                @endif
                            </button>

                            <div class="min-w-0 flex-1">
                                <a href="{{ route('track.show', $play->track->slug) }}" wire:navigate class="block truncate text-sm font-semibold text-gray-900 transition hover:text-primary">
                                    {{ $play->track->title }}
                                </a>
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                    @if($play->track->artistProfile)
                                        <a href="{{ route('artist.page', $play->track->artistProfile) }}" wire:navigate class="truncate hover:text-primary">
                                            {{ $play->track->artistProfile->stage_name ?? $play->track->artistProfile->user?->name ?? 'Unknown artist' }}
                                        </a>
                                    @else
                                        <span>Unknown artist</span>
                                    @endif
                                    @if($play->track->genre)
                                        <span>&middot;</span>
                                        <span>{{ $play->track->genre->name }}</span>
                                    @endif
                                    <x-track-duration :track="$play->track" class="text-gray-400" />
                                </div>
                            </div>

                            <div class="hidden text-xs text-gray-400 md:block">{{ number_format($play->track->play_count) }} plays</div>
                            <span class="shrink-0 text-xs text-gray-400">{{ $play->created_at->diffForHumans() }}</span>
                        </li>
                    @endif
                @endforeach
            </ul>

            <div class="border-t border-white/30 px-5 py-4">{{ $history->links() }}</div>
        @endif
    </section>
</div>
