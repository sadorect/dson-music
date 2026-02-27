<?php

use App\Models\Playlist;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component
{
    public Playlist $playlist;

    public function mount(Playlist $playlist): void
    {
        // Allow access if public, or if the authenticated user owns it
        if (!$playlist->is_public && auth()->id() !== $playlist->user_id) {
            abort(403, 'This playlist is private.');
        }
    }

    public function with(): array
    {
        $tracks = $this->playlist
            ->tracks()
            ->with('artistProfile')
            ->orderByPivot('position')
            ->get();

        return [
            'tracks'   => $tracks,
            'trackIds' => $tracks->pluck('id')->toArray(),
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    {{-- Header --}}
    <div class="glass-card rounded-2xl p-6 flex items-center gap-5">
        {{-- Cover / icon --}}
        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-red-400 to-pink-500 flex items-center justify-center shrink-0 overflow-hidden">
            @if($playlist->getCoverUrl())
                <img src="{{ $playlist->getCoverUrl() }}" class="w-full h-full object-cover" alt="">
            @else
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Playlist</p>
            <h1 class="text-2xl font-bold text-gray-800 truncate">{{ $playlist->name }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                by {{ $playlist->user?->name ?? 'Unknown' }}
                &middot; {{ $tracks->count() }} {{ Str::plural('track', $tracks->count()) }}
                &middot; <span class="{{ $playlist->is_public ? 'text-green-600' : 'text-gray-400' }}">{{ $playlist->is_public ? 'Public' : 'Private' }}</span>
            </p>
        </div>

        {{-- Play All / Queue All --}}
        @if($tracks->isNotEmpty())
        <div class="flex items-center gap-2 shrink-0">
            <button
                onclick="Livewire.dispatch('play-playlist', { ids: {{ json_encode($trackIds) }} })"
                class="glass-btn-primary glass-btn-primary-hover flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                Play All
            </button>
            <button
                onclick="Livewire.dispatch('queue-playlist', { ids: {{ json_encode($trackIds) }} })"
                class="glass-btn glass-btn-hover flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium"
                title="Add all to queue">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10"/>
                </svg>
                Add to Queue
            </button>
        </div>
        @endif
    </div>

    {{-- Track list --}}
    <div class="glass-card rounded-2xl overflow-hidden">
        @if($tracks->isEmpty())
            <p class="p-12 text-center text-sm text-gray-500">This playlist has no tracks yet.</p>
        @else
        <ul class="divide-y divide-white/30">
            @foreach($tracks as $i => $track)
            <li class="flex items-center gap-4 px-5 py-3 hover:bg-white/40 transition group">
                <span class="text-xs text-gray-400 w-5 shrink-0 text-right">{{ $i + 1 }}</span>

                <button @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
                        class="relative w-10 h-10 rounded-xl bg-gray-200 overflow-hidden shrink-0 group/play">
                    @if($track->getCoverUrl())
                        <img src="{{ $track->getCoverUrl() }}" class="w-full h-full object-cover" alt="">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover/play:opacity-100 transition">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                </button>

                <div class="flex-1 min-w-0">
                    <a href="{{ route('track.show', $track->slug) }}" wire:navigate
                       class="text-sm font-semibold text-gray-800 hover:text-red-500 truncate block transition">{{ $track->title }}</a>
                    <p class="text-xs text-gray-500">{{ $track->artistProfile?->stage_name ?? '—' }}</p>
                </div>

                <span class="text-xs text-gray-400 shrink-0 hidden sm:block tabular-nums">{{ $track->formatted_duration }}</span>

                <button @click.stop="Livewire.dispatch('queue-track', { id: {{ $track->id }} })"
                        class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition shrink-0"
                        title="Add to queue">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </li>
            @endforeach
        </ul>
        @endif
    </div>
</div>
