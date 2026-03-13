<?php

use App\Models\Playlist;
use App\Models\Track;
use App\StagesLivewireUploads;
use App\Support\UploadLimits;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.glass-app')] class extends Component
{
    use StagesLivewireUploads;
    use WithFileUploads;

    public bool $showForm = false;
    public string $formMode = 'create';
    public ?int $editingPlaylistId = null;

    public string $title = '';
    public string $description = '';
    public bool $isPublic = false;
    public $coverFile = null;
    public bool $removeExistingCover = false;

    public ?int $openPlaylistId = null;
    public ?int $addingToId = null;
    public string $trackSearch = '';

    public ?string $feedbackMessage = null;
    public string $feedbackTone = 'success';

    public function openCreateForm(): void
    {
        $this->resetForm();
        $this->formMode = 'create';
        $this->showForm = true;
    }

    public function editPlaylist(int $id): void
    {
        $playlist = $this->ownedPlaylist($id);

        $this->formMode = 'edit';
        $this->editingPlaylistId = $playlist->id;
        $this->title = $playlist->name;
        $this->description = (string) $playlist->description;
        $this->isPublic = $playlist->is_public;
        $this->coverFile = null;
        $this->removeExistingCover = false;
        $this->showForm = true;
        $this->openPlaylistId = $playlist->id;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
    }

    public function savePlaylist(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:600'],
            'isPublic' => ['boolean'],
            'coverFile' => ['nullable', 'image', 'max:' . UploadLimits::imageKb()],
        ]);

        $playlist = $this->editingPlaylistId
            ? $this->ownedPlaylist($this->editingPlaylistId)
            : new Playlist(['user_id' => auth()->id()]);

        $playlist->fill([
            'name' => $this->title,
            'description' => $this->description ?: null,
            'is_public' => $this->isPublic,
        ]);

        $playlist->save();

        if ($this->removeExistingCover) {
            $playlist->getFirstMedia('cover')?->delete();
        }

        if ($this->coverFile) {
            $this->addStagedMedia(
                $playlist,
                $this->coverFile,
                'cover',
                Str::slug($playlist->name ?: 'playlist').'.'.$this->coverFile->getClientOriginalExtension()
            );
        }

        $this->openPlaylistId = $playlist->id;
        $this->setFeedback($this->editingPlaylistId ? 'Playlist updated.' : 'Playlist created.', 'success');
        $this->resetForm();
    }

    public function removeCoverAsset(): void
    {
        $this->coverFile = null;
        $this->removeExistingCover = true;
    }

    public function deletePlaylist(int $id): void
    {
        $playlist = $this->ownedPlaylist($id);
        $playlist->delete();

        if ($this->openPlaylistId === $id) {
            $this->openPlaylistId = null;
        }

        if ($this->addingToId === $id) {
            $this->addingToId = null;
            $this->trackSearch = '';
        }

        if ($this->editingPlaylistId === $id) {
            $this->resetForm();
        }

        $this->setFeedback('Playlist deleted.', 'success');
    }

    public function duplicatePlaylist(int $id): void
    {
        $source = $this->ownedPlaylist($id);

        $duplicate = Playlist::create([
            'user_id' => auth()->id(),
            'name' => $source->name.' Copy',
            'description' => $source->description,
            'is_public' => false,
        ]);

        $trackIds = $source->tracks()->orderByPivot('position')->pluck('tracks.id')->values();

        if ($trackIds->isNotEmpty()) {
            $duplicate->tracks()->attach(
                $trackIds->mapWithKeys(fn (int $trackId, int $index) => [$trackId => ['position' => $index + 1]])->all()
            );
        }

        $this->openPlaylistId = $duplicate->id;
        $this->setFeedback('Playlist duplicated.', 'success');
    }

    public function createFromLiked(): void
    {
        $likedTracks = auth()->user()
            ->likes()
            ->orderByDesc('likes.created_at')
            ->pluck('tracks.id')
            ->values();

        if ($likedTracks->isEmpty()) {
            $this->setFeedback('You do not have any liked tracks yet.', 'warning');

            return;
        }

        $playlist = Playlist::create([
            'user_id' => auth()->id(),
            'name' => 'Liked Mix',
            'description' => 'A quick playlist built from your liked tracks.',
            'is_public' => false,
        ]);

        $playlist->tracks()->attach(
            $likedTracks->mapWithKeys(fn (int $trackId, int $index) => [$trackId => ['position' => $index + 1]])->all()
        );

        $this->openPlaylistId = $playlist->id;
        $this->setFeedback('Created a playlist from your liked tracks.', 'success');
    }

    public function toggleOpen(int $id): void
    {
        $closing = $this->openPlaylistId === $id;

        $this->openPlaylistId = $closing ? null : $id;

        if ($closing && $this->addingToId === $id) {
            $this->addingToId = null;
            $this->trackSearch = '';
        }
    }

    public function toggleAdding(int $playlistId): void
    {
        if ($this->addingToId === $playlistId) {
            $this->addingToId = null;
            $this->trackSearch = '';

            return;
        }

        $this->addingToId = $playlistId;
        $this->trackSearch = '';
        $this->openPlaylistId = $playlistId;
    }

    public function removeTrack(int $playlistId, int $trackId): void
    {
        $playlist = $this->ownedPlaylist($playlistId);
        $playlist->tracks()->detach($trackId);

        $this->normalizeTrackPositions($playlist);
        $this->setFeedback('Track removed from playlist.', 'success');
    }

    public function moveTrack(int $playlistId, int $trackId, string $direction): void
    {
        if (! in_array($direction, ['up', 'down'], true)) {
            return;
        }

        $playlist = $this->ownedPlaylist($playlistId);
        $orderedIds = $playlist->tracks()->orderByPivot('position')->pluck('tracks.id')->values()->all();
        $currentIndex = array_search($trackId, $orderedIds, true);

        if ($currentIndex === false) {
            return;
        }

        $targetIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if (! array_key_exists($targetIndex, $orderedIds)) {
            return;
        }

        [$orderedIds[$currentIndex], $orderedIds[$targetIndex]] = [$orderedIds[$targetIndex], $orderedIds[$currentIndex]];

        foreach ($orderedIds as $index => $id) {
            $playlist->tracks()->updateExistingPivot($id, ['position' => $index + 1]);
        }
    }

    public function addTrack(int $playlistId, int $trackId): void
    {
        $playlist = $this->ownedPlaylist($playlistId);

        if ($playlist->tracks()->where('track_id', $trackId)->exists()) {
            $this->setFeedback('That track is already in this playlist.', 'warning');

            return;
        }

        $maxPosition = $playlist->tracks()->max('playlist_track.position') ?? 0;
        $playlist->tracks()->attach($trackId, ['position' => $maxPosition + 1]);

        $this->trackSearch = '';
        $this->setFeedback('Track added to playlist.', 'success');
    }

    public function with(): array
    {
        $user = auth()->user();

        $playlists = $user->playlists()
            ->with('media')
            ->withCount('tracks')
            ->orderByDesc('updated_at')
            ->get();

        $openPlaylist = $this->openPlaylistId ? $playlists->firstWhere('id', $this->openPlaylistId) : null;
        $openTracks = $openPlaylist
            ? $openPlaylist->tracks()->with(['artistProfile.user', 'media'])->orderByPivot('position')->get()
            : collect();

        $alreadyInPlaylist = collect();

        if ($this->addingToId) {
            $targetPlaylist = $this->addingToId === $this->openPlaylistId
                ? $openPlaylist
                : $playlists->firstWhere('id', $this->addingToId);

            if ($targetPlaylist) {
                $alreadyInPlaylist = $this->addingToId === $this->openPlaylistId
                    ? $openTracks->pluck('id')
                    : $targetPlaylist->tracks()->pluck('tracks.id');
            }
        }

        $searchResults = collect();
        $suggestedTracks = collect();

        if ($this->addingToId && strlen(trim($this->trackSearch)) >= 1) {
            $searchResults = Track::query()
                ->with(['artistProfile.user', 'media'])
                ->where('is_published', true)
                ->where(function ($query) {
                    $query->where('title', 'like', '%'.$this->trackSearch.'%')
                        ->orWhereHas('artistProfile', fn ($artistQuery) => $artistQuery->where('stage_name', 'like', '%'.$this->trackSearch.'%'));
                })
                ->whereNotIn('id', $alreadyInPlaylist)
                ->limit(8)
                ->get();
        } elseif ($this->addingToId) {
            $followedArtistIds = $user->followedArtists()->pluck('artist_profiles.id');

            if ($followedArtistIds->isNotEmpty()) {
                $suggestedTracks = Track::query()
                    ->with(['artistProfile.user', 'media'])
                    ->where('is_published', true)
                    ->whereIn('artist_profile_id', $followedArtistIds)
                    ->whereNotIn('id', $alreadyInPlaylist)
                    ->latest()
                    ->take(6)
                    ->get();
            }

            if ($suggestedTracks->isEmpty()) {
                $suggestedTracks = $user->likes()
                    ->with(['artistProfile.user', 'media'])
                    ->whereNotIn('tracks.id', $alreadyInPlaylist)
                    ->orderByDesc('likes.created_at')
                    ->take(6)
                    ->get();
            }

            if ($suggestedTracks->isEmpty()) {
                $suggestedTracks = Track::query()
                    ->with(['artistProfile.user', 'media'])
                    ->where('is_published', true)
                    ->whereNotIn('id', $alreadyInPlaylist)
                    ->latest()
                    ->take(6)
                    ->get();
            }
        }

        return [
            'playlists' => $playlists,
            'openPlaylist' => $openPlaylist,
            'openTracks' => $openTracks,
            'searchResults' => $searchResults,
            'suggestedTracks' => $suggestedTracks,
            'stats' => [
                'playlists' => $playlists->count(),
                'tracks' => (int) $playlists->sum('tracks_count'),
                'public' => $playlists->where('is_public', true)->count(),
                'liked' => $user->likes()->count(),
            ],
        ];
    }

    protected function ownedPlaylist(int $id): Playlist
    {
        return Playlist::where('user_id', auth()->id())->findOrFail($id);
    }

    protected function normalizeTrackPositions(Playlist $playlist): void
    {
        $playlist->tracks()
            ->orderByPivot('position')
            ->pluck('tracks.id')
            ->values()
            ->each(fn (int $id, int $index) => $playlist->tracks()->updateExistingPivot($id, ['position' => $index + 1]));
    }

    protected function resetForm(): void
    {
        $this->resetValidation();
        $this->showForm = false;
        $this->formMode = 'create';
        $this->editingPlaylistId = null;
        $this->title = '';
        $this->description = '';
        $this->isPublic = false;
        $this->coverFile = null;
        $this->removeExistingCover = false;
    }

    protected function setFeedback(string $message, string $tone): void
    {
        $this->feedbackMessage = $message;
        $this->feedbackTone = $tone;
    }
};
?>

<div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="glass-card relative overflow-hidden rounded-[2rem] p-6 sm:p-8">
            <div class="absolute inset-y-0 right-0 w-1/3 bg-gradient-to-l from-primary/10 to-transparent"></div>
            <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/50 bg-white/65 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-primary/70">
                        <a href="{{ route('listener.dashboard') }}" wire:navigate class="text-gray-500 transition hover:text-primary">Library</a>
                        <span>&middot;</span>
                        <span>Playlists</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-gray-900 sm:text-5xl">Build playlists with more personality</h1>
                    <p class="mt-3 max-w-2xl text-sm text-gray-500 sm:text-base">Shape public collections, keep private drafts, add cover art, and curate faster with liked-track and discovery shortcuts.</p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach([
                        ['label' => 'Playlists', 'value' => number_format($stats['playlists'])],
                        ['label' => 'Tracks', 'value' => number_format($stats['tracks'])],
                        ['label' => 'Public', 'value' => number_format($stats['public'])],
                        ['label' => 'Liked', 'value' => number_format($stats['liked'])],
                    ] as $metric)
                        <div class="rounded-2xl border border-white/60 bg-white/80 px-4 py-3 text-center shadow-sm backdrop-blur-sm">
                            <p class="text-lg font-black text-gray-900 sm:text-2xl">{{ $metric['value'] }}</p>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-400">{{ $metric['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="relative z-10 mt-6 flex flex-wrap gap-3">
                <button wire:click="openCreateForm" class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-600">
                    New Playlist
                </button>
                <button wire:click="createFromLiked" class="rounded-full border border-gray-200 bg-white/85 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary">
                    Create From Liked Tracks
                </button>
            </div>
        </section>

        @if($feedbackMessage)
            <div class="glass-card rounded-[1.5rem] border px-5 py-4 text-sm font-medium {{ $feedbackTone === 'warning' ? 'border-amber-200 bg-amber-50/80 text-amber-700' : 'border-emerald-200 bg-emerald-50/80 text-emerald-700' }}">
                {{ $feedbackMessage }}
            </div>
        @endif

        <section class="grid gap-8 lg:grid-cols-[minmax(0,1.35fr)_minmax(22rem,28rem)] lg:items-start">
            <div class="space-y-4">
                @if($playlists->isEmpty())
                    <div class="glass-card rounded-[2rem] px-6 py-16 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-gradient-to-br from-primary to-primary-600 shadow-lg">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M12 18h8"/>
                            </svg>
                        </div>
                        <h2 class="mt-5 text-2xl font-black tracking-tight text-gray-900">Start your first collection</h2>
                        <p class="mt-3 text-sm text-gray-500">Give it a mood, upload cover art, and start stacking tracks that belong together.</p>
                        <div class="mt-6 flex flex-wrap justify-center gap-3">
                            <button wire:click="openCreateForm" class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-600">
                                Create Blank Playlist
                            </button>
                            <button wire:click="createFromLiked" class="rounded-full border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary">
                                Build From Liked Tracks
                            </button>
                        </div>
                    </div>
                @else
                    @foreach($playlists as $playlist)
                        @php
                            $isOpen = $openPlaylistId === $playlist->id;
                            $playlistUrl = route('playlist.show', $playlist->slug);
                        @endphp
                        <article class="glass-card overflow-hidden rounded-[2rem]">
                            @php $playlistTrackIds = $playlist->tracks()->orderByPivot('position')->pluck('tracks.id')->values()->all(); @endphp

                            <div class="px-5 py-5 sm:px-6">
                                <button wire:click="toggleOpen({{ $playlist->id }})" class="flex w-full min-w-0 items-start gap-4 text-left">
                                    <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-[1.4rem] bg-gradient-to-br from-primary-200 via-primary-300 to-primary-500 shadow-sm">
                                        @if($playlist->getCoverUrl())
                                            <img src="{{ $playlist->getCoverUrl() }}" alt="{{ $playlist->name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-2xl font-black text-white/90">
                                                {{ strtoupper(Str::substr($playlist->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="absolute inset-x-2 bottom-2 rounded-full bg-black/55 px-2 py-1 text-center text-[10px] font-semibold uppercase tracking-[0.2em] text-white">
                                            {{ $playlist->is_public ? 'Public' : 'Private' }}
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h2 class="truncate text-lg font-black tracking-tight text-gray-900">{{ $playlist->name }}</h2>
                                            @if($playlist->is_public)
                                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Shareable</span>
                                            @endif
                                        </div>

                                        @if($playlist->description)
                                            <p class="mt-2 line-clamp-2 max-w-2xl text-sm text-gray-500">{{ $playlist->description }}</p>
                                        @else
                                            <p class="mt-2 text-sm text-gray-400">No description yet. Add one to give this collection more context.</p>
                                        @endif

                                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-400">
                                            <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">{{ $playlist->tracks_count }} tracks</span>
                                            <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">Updated {{ $playlist->updated_at->diffForHumans() }}</span>
                                            @if($playlist->is_public)
                                                <span class="rounded-full bg-primary/10 px-3 py-1 font-semibold text-primary">{{ $playlist->slug }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </button>

                                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-white/40 pt-4">
                                    <button onclick="Livewire.dispatch('play-playlist', { ids: {{ json_encode($playlistTrackIds) }} })"
                                            class="rounded-full bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-600">
                                        Play
                                    </button>
                                    <button onclick="Livewire.dispatch('queue-playlist', { ids: {{ json_encode($playlistTrackIds) }} })"
                                            class="rounded-full border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary">
                                        Queue
                                    </button>
                                    <button wire:click="toggleAdding({{ $playlist->id }})"
                                            class="rounded-full border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary">
                                        Add Tracks
                                    </button>
                                    <button wire:click="editPlaylist({{ $playlist->id }})"
                                            class="rounded-full border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary">
                                        Edit
                                    </button>
                                    <button wire:click="duplicatePlaylist({{ $playlist->id }})"
                                            class="rounded-full border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary">
                                        Duplicate
                                    </button>
                                    @if($playlist->is_public)
                                        <a href="{{ $playlistUrl }}" wire:navigate class="rounded-full border border-primary/20 bg-primary/10 px-4 py-2.5 text-sm font-semibold text-primary transition hover:bg-primary/15">
                                            View Public Page
                                        </a>
                                        <div x-data="{ copied: false }" class="contents">
                                            <button
                                                @click.prevent="
                                                    const url = '{{ $playlistUrl }}';
                                                    if (navigator.share) {
                                                        navigator.share({ title: '{{ addslashes($playlist->name) }}', url });
                                                    } else if (navigator.clipboard) {
                                                        navigator.clipboard.writeText(url).then(() => {
                                                            copied = true;
                                                            setTimeout(() => copied = false, 1800);
                                                        });
                                                    }
                                                "
                                                class="rounded-full border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary"
                                            >
                                                <span x-show="!copied">Copy Link</span>
                                                <span x-show="copied" style="display:none">Copied</span>
                                            </button>
                                        </div>
                                    @endif
                                    <button wire:click="deletePlaylist({{ $playlist->id }})"
                                            wire:confirm="Delete '{{ addslashes($playlist->name) }}'?"
                                            class="rounded-full border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-100">
                                        Delete
                                    </button>
                                    <button wire:click="toggleOpen({{ $playlist->id }})"
                                            class="ml-auto inline-flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition hover:border-primary hover:text-primary">
                                        <svg class="h-4 w-4 transition {{ $isOpen ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            @if($isOpen)
                                <div class="border-t border-white/40 px-5 py-5 sm:px-6">
                                    @php
                                        $openDuration = $openTracks->sum('duration');
                                    @endphp
                                    <div class="mb-4 flex flex-wrap items-center gap-2 text-xs text-gray-400">
                                        <span class="rounded-full bg-white/70 px-3 py-1 font-semibold text-gray-600">{{ $openTracks->count() }} tracks ready</span>
                                        @if($openDuration > 0)
                                            <span class="rounded-full bg-white/70 px-3 py-1 font-semibold text-gray-600">{{ gmdate('H:i:s', $openDuration) }} total</span>
                                        @endif
                                        @if($playlist->is_public)
                                            <a href="{{ $playlistUrl }}" wire:navigate class="rounded-full bg-white/70 px-3 py-1 font-semibold text-primary transition hover:text-primary-600">Open public page</a>
                                        @endif
                                    </div>

                                    @if($openTracks->isEmpty())
                                        <div class="rounded-[1.5rem] border border-dashed border-white/60 bg-white/40 px-6 py-12 text-center">
                                            <p class="text-lg font-semibold text-gray-800">This playlist is still empty</p>
                                            <p class="mt-2 text-sm text-gray-500">Use the add tracks panel below to start shaping it.</p>
                                        </div>
                                    @else
                                        <div class="space-y-3">
                                            @foreach($openTracks as $index => $track)
                                                <div class="glass-card flex flex-col gap-4 rounded-[1.5rem] p-4 transition hover:bg-white/85 sm:flex-row sm:items-center">
                                                    <div class="flex items-center gap-4">
                                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gray-100 text-sm font-black text-gray-600">{{ $index + 1 }}</span>
                                                        <button @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
                                                                class="relative h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                                            @if($track->getCoverUrl())
                                                                <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->title }}" class="h-full w-full object-cover">
                                                            @endif
                                                        </button>
                                                    </div>

                                                    <div class="min-w-0 flex-1">
                                                        <a href="{{ route('track.show', $track->slug) }}" wire:navigate class="block truncate text-base font-semibold text-gray-900 transition hover:text-primary">{{ $track->title }}</a>
                                                        <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                                                            <span>{{ $track->artistProfile?->stage_name ?? 'Unknown Artist' }}</span>
                                                            <x-track-duration :track="$track" class="text-gray-400" />
                                                        </div>
                                                    </div>

                                                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                                        <button wire:click="moveTrack({{ $playlist->id }}, {{ $track->id }}, 'up')"
                                                                class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:border-primary hover:text-primary">
                                                            Up
                                                        </button>
                                                        <button wire:click="moveTrack({{ $playlist->id }}, {{ $track->id }}, 'down')"
                                                                class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:border-primary hover:text-primary">
                                                            Down
                                                        </button>
                                                        <button @click="Livewire.dispatch('queue-track', { id: {{ $track->id }} })"
                                                                class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:border-primary hover:text-primary">
                                                            Queue
                                                        </button>
                                                        <button wire:click="removeTrack({{ $playlist->id }}, {{ $track->id }})"
                                                                class="rounded-full border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-100">
                                                            Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="mt-5 rounded-[1.6rem] border border-white/50 bg-white/45 p-4 sm:p-5">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <h3 class="text-base font-black text-gray-900">Add tracks</h3>
                                                <p class="mt-1 text-sm text-gray-500">Search directly or use suggestions from artists you follow and tracks you already like.</p>
                                            </div>
                                            <button wire:click="toggleAdding({{ $playlist->id }})"
                                                    class="rounded-full border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary">
                                                {{ $addingToId === $playlist->id ? 'Hide Panel' : 'Open Panel' }}
                                            </button>
                                        </div>

                                        @if($addingToId === $playlist->id)
                                            <div class="mt-4 space-y-4" wire:key="add-panel-{{ $playlist->id }}">
                                                <div class="flex flex-col gap-3 sm:flex-row">
                                                    <input
                                                        wire:model.live.debounce.300ms="trackSearch"
                                                        type="search"
                                                        placeholder="Search by track title or artist name..."
                                                        class="w-full rounded-2xl border border-white/40 bg-white/80 px-4 py-3 text-sm text-gray-700 shadow-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30"
                                                    >
                                                    <button wire:click="$set('trackSearch', '')"
                                                            class="rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-600 transition hover:border-primary hover:text-primary">
                                                        Clear
                                                    </button>
                                                </div>

                                                @if(strlen(trim($trackSearch)) >= 1)
                                                    <div class="space-y-3">
                                                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Search Results</p>
                                                        @if($searchResults->isEmpty())
                                                            <div class="rounded-2xl bg-white/70 px-4 py-8 text-center text-sm text-gray-500">No tracks matched your search.</div>
                                                        @else
                                                            <div class="space-y-3">
                                                                @foreach($searchResults as $result)
                                                                    <button wire:click="addTrack({{ $playlist->id }}, {{ $result->id }})"
                                                                            wire:key="playlist-search-result-{{ $result->id }}"
                                                                            class="glass-card flex w-full items-center gap-4 rounded-[1.4rem] p-4 text-left transition hover:bg-white/85">
                                                                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                                                            @if($result->getCoverUrl())
                                                                                <img src="{{ $result->getCoverUrl() }}" alt="{{ $result->title }}" class="h-full w-full object-cover">
                                                                            @endif
                                                                        </div>
                                                                        <div class="min-w-0 flex-1">
                                                                            <p class="truncate text-sm font-semibold text-gray-900">{{ $result->title }}</p>
                                                                            <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                                                                <span>{{ $result->artistProfile?->stage_name ?? 'Unknown Artist' }}</span>
                                                                                <x-track-duration :track="$result" class="text-gray-400" />
                                                                            </div>
                                                                        </div>
                                                                        <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">Add</span>
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="space-y-3">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Suggested Tracks</p>
                                                            <button wire:click="createFromLiked" class="text-xs font-semibold text-primary transition hover:text-primary-600">
                                                                New playlist from liked tracks
                                                            </button>
                                                        </div>
                                                        <div class="grid gap-3 lg:grid-cols-2">
                                                            @foreach($suggestedTracks as $suggested)
                                                                <button wire:click="addTrack({{ $playlist->id }}, {{ $suggested->id }})"
                                                                        wire:key="playlist-suggested-track-{{ $suggested->id }}"
                                                                        class="glass-card flex w-full items-center gap-4 rounded-[1.4rem] p-4 text-left transition hover:bg-white/85">
                                                                    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                                                        @if($suggested->getCoverUrl())
                                                                            <img src="{{ $suggested->getCoverUrl() }}" alt="{{ $suggested->title }}" class="h-full w-full object-cover">
                                                                        @endif
                                                                    </div>
                                                                    <div class="min-w-0 flex-1">
                                                                        <p class="truncate text-sm font-semibold text-gray-900">{{ $suggested->title }}</p>
                                                                        <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                                                            <span>{{ $suggested->artistProfile?->stage_name ?? 'Unknown Artist' }}</span>
                                                                            <x-track-duration :track="$suggested" class="text-gray-400" />
                                                                        </div>
                                                                    </div>
                                                                    <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">Add</span>
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </article>
                    @endforeach
                @endif
            </div>

            <aside class="mx-auto w-full max-w-xl space-y-4 pt-2 lg:sticky lg:top-24 lg:mx-0 lg:max-w-none lg:pt-0">
                <section class="glass-card rounded-[2rem] p-6 sm:p-7">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">
                                {{ $formMode === 'edit' ? 'Edit Playlist' : 'Create Playlist' }}
                            </p>
                            <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-900">
                                {{ $formMode === 'edit' ? 'Refine your playlist' : 'Design a new collection' }}
                            </h2>
                        </div>
                        @if($showForm)
                            <button wire:click="cancelForm" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-primary hover:text-primary">
                                Close
                            </button>
                        @endif
                    </div>

                    @if(!$showForm)
                        <div class="mt-5 space-y-4">
                            <p class="text-sm text-gray-500">Open the form to create a new playlist or edit an existing one with description, privacy, and cover art.</p>
                            <div class="grid gap-3">
                                <button wire:click="openCreateForm" class="rounded-2xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-600">
                                    New Blank Playlist
                                </button>
                                <button wire:click="createFromLiked" class="rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary">
                                    Quick Build From Liked
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 space-y-5">
                            <div class="grid gap-5">
                                <div>
                                    <label for="playlist-title" class="text-sm font-semibold text-gray-700">Playlist Name</label>
                                    <input wire:model.blur="title" id="playlist-title" type="text" placeholder="Late Night Gems"
                                           class="mt-2 w-full rounded-2xl border border-white/40 bg-white/80 px-4 py-3 text-sm text-gray-700 shadow-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30">
                                    @error('title') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="playlist-description" class="text-sm font-semibold text-gray-700">Description</label>
                                    <textarea wire:model.blur="description" id="playlist-description" rows="4" placeholder="Tell listeners what ties this playlist together..."
                                              class="mt-2 w-full rounded-2xl border border-white/40 bg-white/80 px-4 py-3 text-sm text-gray-700 shadow-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30"></textarea>
                                    @error('description') <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <label class="text-sm font-semibold text-gray-700">Cover Art</label>
                                        @if($coverFile || ($editingPlaylistId && !$removeExistingCover && optional($playlists->firstWhere('id', $editingPlaylistId))->getCoverUrl()))
                                            <button wire:click="removeCoverAsset" type="button" class="text-xs font-semibold text-rose-600 transition hover:text-rose-700">
                                                Remove cover
                                            </button>
                                        @endif
                                    </div>

                                    <label class="flex cursor-pointer items-center justify-center rounded-[1.6rem] border border-dashed border-white/60 bg-white/60 px-5 py-6 text-center transition hover:border-primary/50 hover:bg-white/80">
                                        <input wire:model="coverFile" type="file" accept="image/png,image/jpeg,image/webp" class="hidden">
                                        <div class="space-y-2">
                                            <p class="text-sm font-semibold text-gray-700">Upload playlist cover</p>
                                            <p class="text-xs text-gray-500">Optional. JPG, PNG or WEBP up to {{ UploadLimits::formatKilobytes(UploadLimits::imageKb()) }}.</p>
                                        </div>
                                    </label>
                                    @error('coverFile') <p class="text-xs font-medium text-rose-600">{{ $message }}</p> @enderror

                                    @if($coverFile)
                                        <div class="overflow-hidden rounded-[1.6rem] bg-white/70 p-3">
                                            <img src="{{ $coverFile->temporaryUrl() }}" alt="Cover preview" class="h-44 w-full rounded-[1.2rem] object-cover">
                                        </div>
                                    @elseif($editingPlaylistId && !$removeExistingCover && optional($playlists->firstWhere('id', $editingPlaylistId))->getCoverUrl())
                                        <div class="overflow-hidden rounded-[1.6rem] bg-white/70 p-3">
                                            <img src="{{ $playlists->firstWhere('id', $editingPlaylistId)->getCoverUrl() }}" alt="Current cover" class="h-44 w-full rounded-[1.2rem] object-cover">
                                        </div>
                                    @endif
                                </div>

                                <label class="flex items-start gap-3 rounded-[1.4rem] border border-white/40 bg-white/60 px-4 py-4">
                                    <input wire:model="isPublic" type="checkbox" class="mt-1 rounded border-gray-300 text-primary focus:ring-primary/40">
                                    <span>
                                        <span class="block text-sm font-semibold text-gray-700">Make playlist public</span>
                                        <span class="mt-1 block text-xs text-gray-500">Public playlists can be shared on their own page. Leave it off for private drafts and personal sequencing.</span>
                                    </span>
                                </label>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <button wire:click="savePlaylist" class="rounded-2xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-600">
                                    {{ $formMode === 'edit' ? 'Save Changes' : 'Create Playlist' }}
                                </button>
                                <button wire:click="cancelForm" class="rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    @endif
                </section>

                <section class="glass-card rounded-[2rem] p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Shortcut Ideas</p>
                    <div class="mt-4 space-y-3 text-sm text-gray-500">
                        <div class="rounded-[1.4rem] bg-white/60 px-4 py-4">
                            <p class="font-semibold text-gray-800">Mood boards</p>
                            <p class="mt-1">Use descriptions to explain the energy, era, or intent behind a playlist.</p>
                        </div>
                        <div class="rounded-[1.4rem] bg-white/60 px-4 py-4">
                            <p class="font-semibold text-gray-800">Draft privately</p>
                            <p class="mt-1">Keep works-in-progress private until the sequencing and cover art are ready.</p>
                        </div>
                        <div class="rounded-[1.4rem] bg-white/60 px-4 py-4">
                            <p class="font-semibold text-gray-800">Use suggestions</p>
                            <p class="mt-1">The add-track panel pulls in artists you follow and music you already like.</p>
                        </div>
                    </div>
                </section>
            </aside>
        </section>
    </div>
</div>
