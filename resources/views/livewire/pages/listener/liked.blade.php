<?php

use App\Models\Like;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.glass-app')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public function unlike(int $trackId): void
    {
        Like::where('user_id', auth()->id())->where('track_id', $trackId)->delete();
    }

    public function with(): array
    {
        $query = auth()->user()->likes()->with(['artist', 'genre'])
            ->orderByDesc('likes.created_at');

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        return ['tracks' => $query->paginate(20)];
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('listener.dashboard') }}" wire:navigate class="text-gray-500 hover:text-gray-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Liked Tracks</h1>
    </div>

    <x-text-input wire:model.live.debounce.300ms="search" class="w-full" type="search" placeholder="Search liked tracks…" />

    <div class="glass-card rounded-2xl overflow-hidden">
        @if($tracks->isEmpty())
            <p class="p-12 text-center text-sm text-gray-500">No liked tracks yet.</p>
        @else
        <ul class="divide-y divide-white/30">
            @foreach($tracks as $i => $track)
            <li class="flex items-center gap-4 px-5 py-3 hover:bg-white/40 transition group">
                <span class="text-xs text-gray-400 w-5 shrink-0 text-right">{{ ($tracks->currentPage() - 1) * $tracks->perPage() + $i + 1 }}</span>
                <button @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
                        class="w-10 h-10 rounded-lg bg-gray-200 overflow-hidden shrink-0">
                    @if($track->getCoverUrl())
                        <img src="{{ $track->getCoverUrl() }}" class="w-full h-full object-cover" alt="">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                        </div>
                    @endif
                </button>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $track->title }}</p>
                    <p class="text-xs text-gray-500">{{ $track->artist?->stage_name ?? '—' }}</p>
                </div>
                <div class="flex items-center gap-3 shrink-0 text-xs text-gray-500">
                    <span>{{ $track->formatted_duration }}</span>
                    <button wire:click="unlike({{ $track->id }})"
                            class="text-red-400 hover:text-red-600 transition opacity-0 group-hover:opacity-100"
                            title="Unlike">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </button>
                </div>
            </li>
            @endforeach
        </ul>
        <div class="px-5 py-3 border-t border-white/30">{{ $tracks->links() }}</div>
        @endif
    </div>
</div>
