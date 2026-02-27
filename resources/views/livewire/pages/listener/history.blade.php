<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.glass-app')] class extends Component
{
    use WithPagination;

    public function with(): array
    {
        return [
            'history' => auth()->user()
                ->playHistory()
                ->with(['track.artist'])
                ->orderByDesc('created_at')
                ->paginate(30),
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('listener.dashboard') }}" wire:navigate class="text-gray-500 hover:text-gray-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Play History</h1>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden">
        @if($history->isEmpty())
            <p class="p-12 text-center text-sm text-gray-500">No plays yet.</p>
        @else
        <ul class="divide-y divide-white/30">
            @foreach($history as $play)
            @if($play->track)
            <li class="flex items-center gap-4 px-5 py-3 hover:bg-white/40 transition">
                    <button @click="Livewire.dispatch('play-track', { id: {{ $play->track->id }} })"
                        class="w-10 h-10 rounded-lg bg-gray-200 overflow-hidden shrink-0">
                    @if($play->track->getCoverUrl())
                        <img src="{{ $play->track->getCoverUrl() }}" class="w-full h-full object-cover" alt="">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                        </div>
                    @endif
                </button>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $play->track->title }}</p>
                    <p class="text-xs text-gray-500">{{ $play->track->artist?->stage_name ?? 'Unknown' }}</p>
                </div>
                <span class="text-xs text-gray-400 shrink-0">{{ $play->created_at->diffForHumans() }}</span>
            </li>
            @endif
            @endforeach
        </ul>
        <div class="px-5 py-3 border-t border-white/30">{{ $history->links() }}</div>
        @endif
    </div>
</div>
