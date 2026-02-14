@props(['track', 'playlists'])

@php
    $trackPayload = [
        'id' => $track->id,
        'title' => $track->title,
        'artist' => $track->artist->artist_name ?? $track->artist?->user?->name ?? 'Unknown Artist',
        'artwork' => $track->cover_art ? Storage::disk('s3')->url($track->cover_art) : asset('images/default-track-cover.jpg'),
        'audioUrl' => route('tracks.stream', $track),
        'format' => pathinfo($track->file_path ?? '', PATHINFO_EXTENSION) ?: 'mp3',
    ];
@endphp

<div class="absolute top-2 right-2 z-20" x-data="{ open: false, playlistId: '{{ $playlists->first()->id ?? '' }}' }" @click.outside="open = false" @click.stop>
    <div class="flex items-center gap-2 transition sm:opacity-0 sm:group-hover:opacity-100 sm:pointer-events-none sm:group-hover:pointer-events-auto">
        <button
            @click.stop="$dispatch('track:play', @js($trackPayload))"
            class="hidden sm:flex h-9 w-9 rounded-full bg-black/70 text-white items-center justify-center hover:bg-black"
            aria-label="Preview track"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>
            </svg>
        </button>

        <button
            @click.stop="open = !open"
            class="h-9 w-9 rounded-full bg-black/70 text-white flex items-center justify-center hover:bg-black"
            aria-label="Track actions"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6h.01M12 12h.01M12 18h.01" />
            </svg>
        </button>
    </div>

    <div x-show="open" x-cloak class="absolute right-0 mt-2 w-56 bg-[#121212] border border-white/10 rounded-lg p-3 shadow-xl">
        <div class="space-y-2 mb-2">
            <button
                class="w-full bg-white/10 text-white rounded px-2 py-2 text-xs font-medium hover:bg-white/20"
                @click.stop="window.libraryActions.toggleTrackLike({{ $track->id }}); open = false"
            >
                Like / Unlike
            </button>
            <button
                class="w-full bg-white/10 text-white rounded px-2 py-2 text-xs font-medium hover:bg-white/20"
                @click.stop="window.libraryActions.share(@js(route('tracks.show', $track)), @js($track->title)); open = false"
            >
                Share Track
            </button>
            <button
                class="w-full bg-white/10 text-white rounded px-2 py-2 text-xs font-medium hover:bg-white/20"
                @click.stop="window.dispatchEvent(new CustomEvent('queue:add-next', { detail: @js($trackPayload) })); open = false"
            >
                Play Next
            </button>
            <button
                class="w-full bg-white/10 text-white rounded px-2 py-2 text-xs font-medium hover:bg-white/20"
                @click.stop="window.dispatchEvent(new CustomEvent('queue:add', { detail: @js($trackPayload) })); open = false"
            >
                Add to Queue
            </button>
        </div>

        @if($playlists->isEmpty())
            <a href="{{ route('playlists.create') }}" class="text-xs text-white/80 hover:text-white">Create playlist first</a>
        @else
            <select x-model="playlistId" class="w-full bg-white/10 text-white text-xs rounded px-2 py-2 border border-white/10">
                @foreach($playlists as $playlist)
                    <option value="{{ $playlist->id }}">{{ $playlist->name }}</option>
                @endforeach
            </select>
            <button
                class="mt-2 w-full bg-white text-black rounded px-2 py-2 text-xs font-medium"
                @click.stop="window.libraryActions.addTrackToPlaylist({{ $track->id }}, playlistId); open = false"
            >
                Add to Playlist
            </button>
        @endif
    </div>
</div>
