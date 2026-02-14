@extends('layouts.app')

@section('title', $playlist->name)

@section('content')
@php
    $isOwner = auth()->check() && Auth::id() === $playlist->user_id;
    $orderedTracks = $playlist->tracks->sortBy('pivot.position')->values();
    $trackItems = $orderedTracks->map(function ($track) {
        return [
            'id' => $track->id,
            'title' => $track->title,
            'artist' => $track->artist->artist_name ?? 'Unknown Artist',
            'url' => route('tracks.show', $track),
            'audioUrl' => route('tracks.stream', $track),
            'artwork' => $track->cover_art ? Storage::disk('s3')->url($track->cover_art) : asset('images/default-track-cover.jpg'),
            'format' => pathinfo($track->file_path ?? '', PATHINFO_EXTENSION) ?: 'mp3',
        ];
    })->values();
@endphp
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Playlist Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-6">
                    <div class="w-32 h-32 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $playlist->name }}</h1>
                        @if($playlist->description)
                            <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $playlist->description }}</p>
                        @endif
                        <div class="mt-4 flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                            <span>By {{ $playlist->user->name }}</span>
                            <span>•</span>
                            <span>{{ $playlist->tracks->count() }} {{ Str::plural('track', $playlist->tracks->count()) }}</span>
                            <span>•</span>
                            <span class="flex items-center">
                                @if($playlist->is_public)
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Public
                                @else
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Private
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                
                @auth
                    @if(Auth::id() === $playlist->user_id)
                        <div class="flex space-x-2">
                            <a href="{{ route('playlists.edit', $playlist) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </a>
                            <form action="{{ route('playlists.destroy', $playlist) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this playlist?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endif
                @endauth
            </div>
        </div>

        <!-- Tracks List -->
        @if($playlist->tracks->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tracks in playlist</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add some tracks to get started.</p>
            </div>
        @else
            @if($isOwner)
                <div
                    x-data="playlistReorder(@js($trackItems), '{{ route('playlists.reorder', $playlist) }}')"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 sm:p-6"
                >
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Reorder Tracks</h2>
                        <button
                            @click="saveOrder"
                            :disabled="isSaving"
                            class="px-4 py-2 rounded-lg text-sm font-medium bg-black text-white disabled:opacity-60"
                        >
                            <span x-show="!isSaving">Save Order</span>
                            <span x-show="isSaving">Saving...</span>
                        </button>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Drag tracks to reorder without interrupting playback.</p>

                    <div class="space-y-2">
                        <template x-for="(track, index) in tracks" :key="track.id">
                            <div
                                class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700"
                                draggable="true"
                                @dragstart="draggingIndex = index"
                                @dragover.prevent
                                @drop.prevent="moveTrack(draggingIndex, index)"
                            >
                                <div class="cursor-move text-gray-400">⋮⋮</div>
                                <div class="text-xs text-gray-500 w-6" x-text="index + 1"></div>
                                <div class="min-w-0 flex-1">
                                    <a :href="track.url" class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600" x-text="track.title"></a>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="track.artist"></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        class="px-3 py-2 rounded-full bg-black/10 text-xs"
                                        @click="$dispatch('track:play', {
                                            id: track.id,
                                            title: track.title,
                                            artist: track.artist,
                                            artwork: track.artwork,
                                            audioUrl: track.audioUrl,
                                            format: track.format
                                        })"
                                    >Play</button>
                                    <form :action="`{{ url('/playlists/'.$playlist->id.'/tracks') }}/${track.id}`" method="POST" class="inline" onsubmit="return confirm('Remove this track from playlist?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 text-xs hover:text-red-800">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            @else
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Artist</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Album</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Duration</th>
                            @auth
                                @if(Auth::id() === $playlist->user_id)
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                @endif
                            @endauth
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($orderedTracks as $index => $track)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('tracks.show', $track) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $track->title }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    @if($track->artist)
                                        <a href="{{ route('artists.show', $track->artist) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                            {{ $track->artist->artist_name }}
                                        </a>
                                    @else
                                        Unknown
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    @if($track->album)
                                        {{ $track->album->title }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $track->duration ?? '-' }}
                                </td>
                                @auth
                                    @if(Auth::id() === $playlist->user_id)
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <form action="{{ route('playlists.remove-track', [$playlist, $track]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this track from playlist?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    Remove
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                @endauth
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
