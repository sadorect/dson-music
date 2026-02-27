<?php

use App\Models\Playlist;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component
{
    public bool   $creating  = false;
    public string $newTitle  = '';
    public bool   $newPublic = false;

    public ?int   $openPlaylistId   = null;
    public ?int   $addingToId       = null;  // playlist currently showing add-track panel
    public string $trackSearch      = '';

    public function createPlaylist(): void
    {
        $this->validate(['newTitle' => ['required', 'string', 'max:100']]);

        Playlist::create([
            'user_id'   => auth()->id(),
            'name'      => $this->newTitle,
            'is_public' => $this->newPublic,
        ]);

        $this->reset('newTitle', 'newPublic', 'creating');
    }

    public function deletePlaylist(int $id): void
    {
        Playlist::where('user_id', auth()->id())->findOrFail($id)->delete();
    }

    public function toggleOpen(int $id): void
    {
        $this->openPlaylistId = $this->openPlaylistId === $id ? null : $id;
    }

    public function removeTrack(int $playlistId, int $trackId): void
    {
        $pl = Playlist::where('user_id', auth()->id())->findOrFail($playlistId);
        $pl->tracks()->detach($trackId);
    }

    public function toggleAdding(int $playlistId): void
    {
        if ($this->addingToId === $playlistId) {
            $this->addingToId  = null;
            $this->trackSearch = '';
        } else {
            $this->addingToId  = $playlistId;
            $this->trackSearch = '';
            // also open the playlist if not already open
            $this->openPlaylistId = $playlistId;
        }
    }

    public function addTrack(int $playlistId, int $trackId): void
    {
        $pl = Playlist::where('user_id', auth()->id())->findOrFail($playlistId);

        // Skip if already in playlist
        if ($pl->tracks()->where('track_id', $trackId)->exists()) {
            return;
        }

        $maxPosition = $pl->tracks()->max('playlist_track.position') ?? 0;
        $pl->tracks()->attach($trackId, ['position' => $maxPosition + 1]);

        // Keep the search open so user can add more
        $this->trackSearch = '';
    }

    public function with(): array
    {
        $playlists = auth()->user()
            ->playlists()
            ->withCount('tracks')
            ->orderByDesc('updated_at')
            ->get();

        $openTracks = null;
        if ($this->openPlaylistId) {
            $pl = $playlists->firstWhere('id', $this->openPlaylistId);
            $openTracks = $pl?->tracks()->with('artistProfile')->orderByPivot('position')->get();
        }

        $searchResults = collect();
        if ($this->addingToId && strlen(trim($this->trackSearch)) >= 1) {
            $alreadyIn = $openTracks?->pluck('id') ?? collect();
            $searchResults = \App\Models\Track::query()
                ->with('artistProfile')
                ->where('is_published', true)
                ->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->trackSearch . '%')
                      ->orWhereHas('artistProfile', fn($q2) =>
                          $q2->where('stage_name', 'like', '%' . $this->trackSearch . '%')
                      );
                })
                ->whereNotIn('id', $alreadyIn)
                ->limit(8)
                ->get();
        }

        return compact('playlists', 'openTracks', 'searchResults');
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('listener.dashboard') }}" wire:navigate class="text-gray-500 hover:text-gray-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">My Playlists</h1>
        </div>
        <button wire:click="$set('creating', true)"
                class="glass-btn-primary glass-btn-primary-hover px-4 py-2 rounded-xl text-sm font-semibold">
            + New Playlist
        </button>
    </div>

    {{-- Create form --}}
    @if($creating)
    <div class="glass-card rounded-2xl p-5 space-y-4">
        <h2 class="font-semibold text-gray-700">New Playlist</h2>
        <div>
            <x-input-label for="newTitle" value="Name" />
            <x-text-input wire:model="newTitle" id="newTitle" class="block mt-1 w-full" type="text" placeholder="My awesome playlist" autofocus />
            <x-input-error :messages="$errors->get('newTitle')" class="mt-1" />
        </div>
        <label class="flex items-center gap-2 cursor-pointer text-sm">
            <input wire:model="newPublic" type="checkbox" class="rounded border-gray-300 text-red-500 focus:ring-red-400">
            <span class="text-gray-700">Make public</span>
        </label>
        <div class="flex gap-3">
            <button wire:click="createPlaylist" class="glass-btn-primary glass-btn-primary-hover px-4 py-2 rounded-xl text-sm font-semibold">Create</button>
            <button wire:click="$set('creating', false)" class="glass-btn glass-btn-hover px-4 py-2 rounded-xl text-sm">Cancel</button>
        </div>
    </div>
    @endif

    {{-- Playlist list --}}
    @if($playlists->isEmpty())
    <div class="glass-card rounded-2xl p-12 text-center text-sm text-gray-500">
        No playlists yet. Create your first one above!
    </div>
    @else
    <div class="space-y-3">
        @foreach($playlists as $pl)
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="flex items-center gap-4 px-5 py-4 cursor-pointer hover:bg-white/40 transition"
                 wire:click="toggleOpen({{ $pl->id }})">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-400 to-pink-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800">{{ $pl->name }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $pl->tracks_count }} tracks · {{ $pl->is_public ? 'Public' : 'Private' }}
                        @if($pl->is_public)
                            · <a href="{{ route('playlist.show', $pl->slug) }}" wire:navigate.prevent
                                 class="text-red-400 hover:text-red-600 transition" title="Public link">share ↗</a>
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <button wire:click.stop="deletePlaylist({{ $pl->id }})"
                            wire:confirm="Delete '{{ addslashes($pl->name) }}'?"
                            class="text-red-400 hover:text-red-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16"/></svg>
                    </button>
                    <svg class="w-4 h-4 text-gray-400 transition {{ $openPlaylistId === $pl->id ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>

            {{-- Tracks inside playlist --}}
            @if($openPlaylistId === $pl->id)
            @php $trackIds = $openTracks?->pluck('id')->toArray() ?? []; @endphp
            <div class="border-t border-white/30">
                @if(!$openTracks || $openTracks->isEmpty())
                    <p class="px-5 py-4 text-sm text-gray-500 text-center">No tracks yet. Add some below.</p>
                @else
                {{-- Play All / Queue All bar --}}
                <div class="flex items-center gap-2 px-5 py-2.5 bg-white/20 border-b border-white/20">
                    <button onclick="Livewire.dispatch('play-playlist', { ids: {{ json_encode($trackIds) }} })"
                            class="flex items-center gap-1.5 glass-btn-primary glass-btn-primary-hover px-3 py-1.5 rounded-lg text-xs font-semibold">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Play All
                    </button>
                    <button onclick="Livewire.dispatch('queue-playlist', { ids: {{ json_encode($trackIds) }} })"
                            class="flex items-center gap-1.5 glass-btn glass-btn-hover px-3 py-1.5 rounded-lg text-xs font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10"/>
                        </svg>
                        Add All to Queue
                    </button>
                </div>
                <ul class="divide-y divide-white/20">
                    @foreach($openTracks as $idx => $track)
                    <li class="flex items-center gap-4 px-5 py-2.5 hover:bg-white/30 transition group">
                        <span class="text-xs text-gray-400 w-4 shrink-0">{{ $idx + 1 }}</span>
                        <button @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
                                class="w-8 h-8 rounded-lg bg-gray-200 overflow-hidden shrink-0">
                            @if($track->getCoverUrl())
                                <img src="{{ $track->getCoverUrl() }}" class="w-full h-full object-cover" alt="">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                                </div>
                            @endif
                        </button>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $track->title }}</p>
                            <p class="text-xs text-gray-500">{{ $track->artistProfile?->stage_name }}</p>
                        </div>
                        <button wire:click="removeTrack({{ $pl->id }}, {{ $track->id }})"
                                class="text-red-400 hover:text-red-600 transition opacity-0 group-hover:opacity-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </li>
                    @endforeach
                </ul>
                @endif

                {{-- Add tracks panel --}}
                <div class="px-5 py-3 border-t border-white/20">
                    @if($addingToId === $pl->id)
                    <div class="space-y-2" wire:key="add-panel-{{ $pl->id }}">
                        <div class="flex gap-2">
                            <input
                                wire:model.live.debounce.250ms="trackSearch"
                                type="search"
                                placeholder="Search tracks by title or artist…"
                                class="flex-1 rounded-xl border border-white/40 bg-white/60 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
                                autofocus
                            >
                            <button wire:click="toggleAdding({{ $pl->id }})"
                                    class="text-xs text-gray-400 hover:text-gray-700 px-2 transition">
                                Cancel
                            </button>
                        </div>

                        @if(strlen(trim($trackSearch)) >= 1)
                            @if($searchResults->isEmpty())
                                <p class="text-xs text-gray-400 py-2 text-center">No tracks found.</p>
                            @else
                            <ul class="rounded-xl overflow-hidden border border-white/30 divide-y divide-white/20">
                                @foreach($searchResults as $result)
                                <li class="flex items-center gap-3 px-3 py-2 bg-white/50 hover:bg-white/80 transition cursor-pointer"
                                    wire:click="addTrack({{ $pl->id }}, {{ $result->id }})"
                                    wire:key="sr-{{ $result->id }}">
                                    <div class="w-8 h-8 rounded-lg bg-gray-200 overflow-hidden shrink-0">
                                        @if($result->getCoverUrl())
                                            <img src="{{ $result->getCoverUrl() }}" class="w-full h-full object-cover" alt="">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $result->title }}</p>
                                        <p class="text-xs text-gray-500">{{ $result->artistProfile?->stage_name }}</p>
                                    </div>
                                    <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        @else
                            <p class="text-xs text-gray-400 py-1">Type to search the catalogue…</p>
                        @endif
                    </div>
                    @else
                    <button wire:click="toggleAdding({{ $pl->id }})"
                            class="flex items-center gap-1.5 text-sm text-red-500 hover:text-red-700 font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add tracks
                    </button>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>
