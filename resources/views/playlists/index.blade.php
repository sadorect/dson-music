@extends('layouts.app')

@section('title', 'Playlists')

@section('content')
<div class="min-h-screen bg-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-black">Playlists</h1>
                <p class="mt-2 text-black/60">Discover curated playlists from the community</p>
            </div>
            @auth
                <a href="{{ route('playlists.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Playlist
                </a>
            @endauth
        </div>

        @if($playlists->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-black/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-black">No playlists found</h3>
                <p class="mt-1 text-sm text-black/60">Get started by creating your first playlist.</p>
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
                    <div class="group bg-white border border-black/10 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="relative aspect-square bg-gradient-to-br from-orange-500 to-[#2b1306] flex items-center justify-center">
                            <a href="{{ route('playlists.show', $playlist) }}" class="w-full h-full flex items-center justify-center">
                                <svg class="w-20 h-20 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                </svg>
                            </a>

                            <div class="absolute top-3 right-3 flex gap-2 opacity-0 group-hover:opacity-100 transition">
                                <button
                                    onclick='window.libraryActions.playPlaylist(@json($playlistTracks))'
                                    class="force-white h-9 w-9 rounded-full bg-black/80 text-white flex items-center justify-center hover:bg-black"
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
                            <a href="{{ route('playlists.show', $playlist) }}" class="font-semibold text-black group-hover:text-orange-600 truncate block">{{ $playlist->name }}</a>
                            <p class="text-sm text-black/65 mt-1">
                                {{ $playlist->tracks_count }} {{ Str::plural('track', $playlist->tracks_count) }}
                            </p>
                            <p class="text-xs text-black/50 mt-2">
                                By {{ $playlist->user->name }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $playlists->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
