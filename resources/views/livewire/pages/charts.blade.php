<?php

use App\Models\Track;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.glass-app')] class extends Component
{
    use WithPagination;

    public function with(): array
    {
        return [
            'tracks' => Track::with('artistProfile')
                ->withCount('likedByUsers as likes_count')
                ->where('is_published', true)
                ->orderByDesc('play_count')
                ->paginate(50),
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary to-primary-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M3 18v-6.75A6.75 6.75 0 0112 4.5h0a6.75 6.75 0 016.75 6.75V18"/><circle cx="8.25" cy="18.75" r="1.5"/><circle cx="15.75" cy="18.75" r="1.5"/></svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Charts</h1>
            <p class="text-sm text-gray-500">Top tracks by play count</p>
        </div>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden">
        @if($tracks->isEmpty())
            <p class="p-12 text-center text-sm text-gray-500">No tracks yet.</p>
        @else
        {{-- Header row --}}
        <div class="flex items-center gap-4 px-5 py-2 border-b border-white/30 text-[11px] font-semibold uppercase tracking-wide text-gray-400">
            <span class="w-7 shrink-0"></span>
            <span class="w-11 shrink-0"></span>
            <span class="flex-1">Title</span>
            <span class="hidden sm:block w-24 text-right">Plays</span>
            <span class="hidden sm:block w-16 text-right">Likes</span>
            <span class="hidden md:block w-16 text-right">Downloads</span>
            <span class="w-4 shrink-0"></span>
        </div>
        <ul class="divide-y divide-white/30">
            @foreach($tracks as $i => $track)
            <li class="flex items-center gap-4 px-5 py-3 hover:bg-white/40 transition group">
                {{-- Rank --}}
                <span class="text-sm font-bold w-7 shrink-0 text-right
                    {{ $i < 3 ? 'text-primary' : 'text-gray-400' }}">
                    {{ ($tracks->currentPage() - 1) * $tracks->perPage() + $i + 1 }}
                </span>

                {{-- Cover / play --}}
                <button @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
                        class="relative w-11 h-11 rounded-xl bg-gray-200 overflow-hidden shrink-0 group/play">
                    @if($track->getCoverUrl())
                        <img src="{{ $track->getCoverUrl() }}" class="w-full h-full object-cover" alt="">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover/play:opacity-100 transition">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                </button>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <a href="{{ route('track.show', $track->slug) }}" wire:navigate
                       class="text-sm font-semibold text-gray-800 hover:text-primary truncate block transition">{{ $track->title }}</a>
                    <p class="text-xs text-gray-500 truncate">{{ $track->artistProfile?->stage_name ?? '—' }}</p>
                </div>

                {{-- Plays --}}
                <div class="hidden sm:flex items-center gap-1 text-xs text-gray-400 tabular-nums shrink-0 w-24 justify-end">
                    <svg class="w-3.5 h-3.5 opacity-60" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    {{ number_format($track->play_count) }}
                </div>

                {{-- Likes --}}
                <div class="hidden sm:flex items-center gap-1 text-xs text-gray-400 tabular-nums shrink-0 w-16 justify-end">
                    <svg class="w-3.5 h-3.5 opacity-60" fill="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    {{ number_format($track->likes_count) }}
                </div>

                {{-- Downloads --}}
                <div class="hidden md:flex items-center gap-1 text-xs text-gray-400 tabular-nums shrink-0 w-16 justify-end">
                    <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    {{ number_format($track->downloads_count) }}
                </div>

                {{-- Add to queue --}}
                <button @click.stop="Livewire.dispatch('queue-track', { id: {{ $track->id }} })"
                        class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-primary transition shrink-0"
                        title="Add to queue">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </li>
            @endforeach
        </ul>
        @if($tracks->hasPages())
        <div class="px-5 py-3 border-t border-white/30">{{ $tracks->links() }}</div>
        @endif
        @endif
    </div>
</div>
