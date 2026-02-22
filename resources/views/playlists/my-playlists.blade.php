@extends('layouts.app')

@section('title', 'My Playlists')

@section('content')
<div class="min-h-screen bg-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-black">My Playlists</h1>
                <p class="mt-2 text-black/60">Manage your personal playlists</p>
            </div>
            <a href="{{ route('playlists.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Playlist
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if($playlists->isEmpty())
            <div class="bg-white border border-black/10 rounded-lg shadow-md p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-black">No playlists yet</h3>
                <p class="mt-1 text-sm text-black/60">Get started by creating your first playlist.</p>
                <div class="mt-6">
                    <a href="{{ route('playlists.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Your First Playlist
                    </a>
                </div>
            </div>
        @else
            <!-- Playlists Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($playlists as $playlist)
                    @php
                        $playlistTracks = $playlist->tracks->map(function ($track) {
                            return [
                                'id' => $track->id,
                                'title' => $track->title,
                                'artist' => $track->artist->artist_name ?? 'Unknown Artist',
                                'artwork' => $track->cover_art ? Storage::disk('s3')->url($track->cover_art) : asset('images/default-track-cover.jpg'),
                                'audioUrl' => route('tracks.stream', $track),
                                'format' => pathinfo($track->file_path ?? '', PATHINFO_EXTENSION) ?: 'mp3',
                            ];
                        })->values();
                    @endphp
                    <div class="bg-white border border-black/10 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="aspect-square bg-gradient-to-br from-orange-500 to-[#2b1306] flex items-center justify-center relative group">
                            <a href="{{ route('playlists.show', $playlist) }}" class="w-full h-full flex items-center justify-center">
                                <svg class="w-20 h-20 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                </svg>
                            </a>

                            <div class="absolute top-3 right-3 flex gap-2 opacity-0 group-hover:opacity-100 transition">
                                <button
                                    onclick='window.libraryActions.playPlaylist(@json($playlistTracks))'
                                    class="force-white h-9 w-9 rounded-full bg-black/75 text-white flex items-center justify-center hover:bg-black"
                                    aria-label="Play playlist"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>
                                    </svg>
                                </button>
                                <button
                                    onclick='window.libraryActions.queueTracks(@json($playlistTracks), @json($playlist->name . " queued"))'
                                    class="force-white h-9 w-9 rounded-full bg-black/75 text-white flex items-center justify-center hover:bg-black"
                                    aria-label="Queue playlist"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h8" />
                                    </svg>
                                </button>
                                <button
                                    onclick='window.libraryActions.share(@json(route("playlists.show", $playlist)), @json($playlist->name))'
                                    class="force-white h-9 w-9 rounded-full bg-black/75 text-white flex items-center justify-center hover:bg-black"
                                    aria-label="Share playlist"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.228 12.733 9.95 12.25 10.79 11.95m2.42-.6a4.5 4.5 0 116.364-6.364 4.5 4.5 0 01-6.364 6.364zM6 20a4 4 0 100-8 4 4 0 000 8zm12 0a4 4 0 100-8 4 4 0 000 8z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="p-4">
                            <a href="{{ route('playlists.show', $playlist) }}" class="font-semibold text-black hover:text-orange-600 truncate block">
                                {{ $playlist->name }}
                            </a>
                            <p class="text-sm text-black/65 mt-1">
                                {{ $playlist->tracks_count }} {{ Str::plural('track', $playlist->tracks_count) }}
                            </p>
                            <p class="text-xs text-black/50 mt-2 flex items-center">
                                @if($playlist->is_public)
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Public
                                @else
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Private
                                @endif
                            </p>
                            <div class="mt-3 flex space-x-2">
                                <a href="{{ route('playlists.edit', $playlist) }}" class="flex-1 text-center px-3 py-1 bg-orange-100 text-orange-700 rounded hover:bg-orange-200 text-sm transition">
                                    Edit
                                </a>
                                <form action="{{ route('playlists.destroy', $playlist) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this playlist?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm transition">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
