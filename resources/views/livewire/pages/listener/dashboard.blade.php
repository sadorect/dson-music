<?php

use App\Models\PlayHistory;
use App\Models\Track;
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

        return [
            'recentHistory'  => $user->playHistory()->with(['track.artist'])->orderByDesc('created_at')->take(8)->get(),
            'likedTracks'    => $user->likes()->with('artist')->orderByDesc('likes.created_at')->take(6)->get(),
            'playlists'      => $user->playlists()->withCount('tracks')->orderByDesc('updated_at')->take(6)->get(),
            'likedCount'     => $user->likes()->count(),
            'historyCount'   => $user->playHistory()->count(),
            'playlistCount'  => $user->playlists()->count(),
        ];
    }
}; ?>

<div class="max-w-6xl mx-auto px-4 py-8 space-y-8">

    <div>
        <h1 class="text-2xl font-bold text-gray-800">My Music</h1>
        <p class="text-sm text-gray-500 mt-0.5">Welcome back, {{ auth()->user()->name }}</p>
    </div>

    {{-- Quick stats --}}
    <div class="grid grid-cols-3 gap-4">
        @foreach([
            ['label' => 'Liked Tracks', 'value' => $likedCount,   'href' => route('listener.liked'),     'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
            ['label' => 'Playlists',    'value' => $playlistCount, 'href' => route('listener.playlists'), 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
            ['label' => 'Plays',        'value' => $historyCount,  'href' => route('listener.history'),   'icon' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ] as $stat)
        <a href="{{ $stat['href'] }}" wire:navigate class="glass-card rounded-2xl p-5 hover:shadow-glass-hover transition">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    {{-- Recently Played --}}
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/40 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Recently Played</h2>
            <a href="{{ route('listener.history') }}" wire:navigate class="text-sm text-red-500 hover:underline">See all →</a>
        </div>
        @if($recentHistory->isEmpty())
            <p class="px-6 py-8 text-sm text-gray-500 text-center">No plays yet. Start exploring music!</p>
        @else
        <ul class="divide-y divide-white/30">
            @foreach($recentHistory as $play)
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
        @endif
    </div>

    {{-- Liked Tracks --}}
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/40 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Liked Tracks</h2>
            <a href="{{ route('listener.liked') }}" wire:navigate class="text-sm text-red-500 hover:underline">See all →</a>
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
                        <img src="{{ $track->getCoverUrl() }}" class="w-full h-full object-cover" alt="">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                        </div>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-gray-800 truncate">{{ $track->title }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $track->artist?->stage_name }}</p>
                </div>
            </button>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Playlists --}}
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/40 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">My Playlists</h2>
            <a href="{{ route('listener.playlists') }}" wire:navigate class="text-sm text-red-500 hover:underline">See all →</a>
        </div>
        @if($playlists->isEmpty())
            <p class="px-6 py-8 text-sm text-gray-500 text-center">No playlists yet.</p>
        @else
        <ul class="divide-y divide-white/30">
            @foreach($playlists as $pl)
            <li class="flex items-center gap-3 px-5 py-3 hover:bg-white/40 transition">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-red-400 to-pink-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $pl->title }}</p>
                    <p class="text-xs text-gray-500">{{ $pl->tracks_count }} tracks</p>
                </div>
                <span class="text-xs text-gray-400">{{ $pl->is_public ? 'Public' : 'Private' }}</span>
            </li>
            @endforeach
        </ul>
        @endif
    </div>
</div>
