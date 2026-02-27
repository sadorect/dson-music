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
                ->where('is_published', true)
                ->orderByDesc('created_at')
                ->paginate(40),
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">New Releases</h1>
            <p class="text-sm text-gray-500">Fresh tracks, just uploaded</p>
        </div>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden">
        @if($tracks->isEmpty())
            <p class="p-12 text-center text-sm text-gray-500">No tracks yet — check back soon.</p>
        @else
        <ul class="divide-y divide-white/30">
            @foreach($tracks as $track)
            <li class="flex items-center gap-4 px-5 py-3 hover:bg-white/40 transition group">
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
                       class="text-sm font-semibold text-gray-800 hover:text-red-500 truncate block transition">{{ $track->title }}</a>
                    <p class="text-xs text-gray-500 truncate">{{ $track->artistProfile?->stage_name ?? '—' }}</p>
                </div>

                {{-- Age --}}
                <span class="text-xs text-gray-400 shrink-0 hidden sm:block">{{ $track->created_at->diffForHumans() }}</span>

                {{-- Add to queue --}}
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
        @if($tracks->hasPages())
        <div class="px-5 py-3 border-t border-white/30">{{ $tracks->links() }}</div>
        @endif
        @endif
    </div>
</div>
