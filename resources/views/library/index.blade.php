@extends('layouts.app')

@section('title', 'Your Library')

@section('content')
<div class="p-4 sm:p-6" x-data="{ tab: 'playlists' }">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Your Library</h1>
        <p class="text-sm text-gray-500 mt-1">Manage playlists, resume listening, and jump back into liked tracks.</p>
    </div>

    <div class="flex flex-wrap gap-2 mb-6">
        <button @click="tab = 'playlists'" :class="tab === 'playlists' ? 'bg-orange-500 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-orange-300 hover:text-orange-700'" class="px-4 py-2 rounded-full text-sm font-medium transition-colors">Playlists</button>
        <button @click="tab = 'recent'" :class="tab === 'recent' ? 'bg-orange-500 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-orange-300 hover:text-orange-700'" class="px-4 py-2 rounded-full text-sm font-medium transition-colors">Recently Played</button>
        <button @click="tab = 'liked'" :class="tab === 'liked' ? 'bg-orange-500 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-orange-300 hover:text-orange-700'" class="px-4 py-2 rounded-full text-sm font-medium transition-colors">Liked Tracks</button>
        <button @click="tab = 'artists'" :class="tab === 'artists' ? 'bg-orange-500 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-orange-300 hover:text-orange-700'" class="px-4 py-2 rounded-full text-sm font-medium transition-colors">Followed Artists</button>
        <button @click="tab = 'downloads'" :class="tab === 'downloads' ? 'bg-orange-500 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-orange-300 hover:text-orange-700'" class="px-4 py-2 rounded-full text-sm font-medium transition-colors">Downloads</button>
    </div>

    <section x-show="tab === 'playlists'" x-cloak>
        <div class="flex justify-end mb-4">
            <a href="{{ route('playlists.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-500 text-white rounded-full text-sm font-medium hover:bg-orange-600 transition-colors shadow-sm">New Playlist</a>
        </div>

        @if($playlists->isEmpty())
            <div class="bg-white border border-orange-100 rounded-lg p-6 text-gray-500 text-sm">No playlists yet. Create one to start organizing your tracks.</div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($playlists as $playlist)
                    <a href="{{ route('playlists.show', $playlist) }}" class="bg-white border border-orange-100 rounded-lg p-4 hover:border-orange-300 hover:shadow-sm transition">
                        <div class="text-gray-900 font-semibold truncate">{{ $playlist->name }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $playlist->tracks_count }} {{ Str::plural('track', $playlist->tracks_count) }}</div>
                        <div class="text-xs text-gray-400 mt-2">{{ $playlist->is_public ? 'Public' : 'Private' }}</div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section x-show="tab === 'recent'" x-cloak>
        @if($recentPlays->isEmpty())
            <div class="bg-white border border-orange-100 rounded-lg p-6 text-gray-500 text-sm">No listening history yet. Play a track to see it here.</div>
        @else
            <div class="space-y-2">
                @foreach($recentPlays as $play)
                    @if($play->track)
                        <div class="bg-white border border-orange-100 rounded-lg p-3 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-gray-900 text-sm font-medium truncate">{{ $play->track->title }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ $play->track->artist->artist_name ?? 'Unknown Artist' }}</div>
                                <div class="text-[11px] text-gray-400">{{ number_format($play->track->play_count ?? 0) }} plays</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    @click="$dispatch('track:play', {
                                        id: {{ $play->track->id }},
                                        title: @js($play->track->title),
                                        artist: @js($play->track->artist->artist_name ?? 'Unknown Artist'),
                                        artwork: @js($play->track->cover_art ? Storage::disk('s3')->url($play->track->cover_art) : asset('images/default-track-cover.jpg')),
                                        audioUrl: @js(route('tracks.stream', $play->track)),
                                        format: @js(pathinfo($play->track->file_path ?? '', PATHINFO_EXTENSION) ?: 'mp3')
                                    })"
                                    class="px-3 py-2 bg-orange-100 text-orange-800 rounded-full text-xs font-medium hover:bg-orange-200 transition-colors"
                                >Play</button>
                                <button
                                    @click="window.libraryActions.share(@js(route('tracks.show', $play->track)), @js($play->track->title))"
                                    class="px-3 py-2 bg-gray-100 text-gray-700 rounded-full text-xs font-medium hover:bg-gray-200 transition-colors"
                                >Share</button>
                                <button
                                    @click="window.libraryActions.toggleTrackLike({{ $play->track->id }})"
                                    class="px-3 py-2 bg-gray-100 text-gray-700 rounded-full text-xs font-medium hover:bg-gray-200 transition-colors"
                                >Like</button>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </section>

    <section x-show="tab === 'liked'" x-cloak>
        @if($likedTracks->isEmpty())
            <div class="bg-white border border-orange-100 rounded-lg p-6 text-gray-500 text-sm">No liked tracks yet. Like tracks to build your favorites.</div>
        @else
            @php
                $userPlaylists = auth()->user()->playlists()->select('id', 'name')->latest()->take(20)->get();
            @endphp
            <div class="space-y-2">
                @foreach($likedTracks as $track)
                    <div class="bg-white border border-orange-100 rounded-lg p-3 flex items-center justify-between gap-3" x-data="{ open: false, playlistId: '{{ $userPlaylists->first()->id ?? '' }}', hidden: false }" x-show="!hidden" x-transition>
                        <div class="min-w-0">
                            <div class="text-gray-900 text-sm font-medium truncate">{{ $track->title }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ $track->artist->artist_name ?? 'Unknown Artist' }}</div>
                            <div class="text-[11px] text-gray-400">{{ number_format($track->play_count ?? 0) }} plays</div>
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <button
                                @click="$dispatch('track:play', {
                                    id: {{ $track->id }},
                                    title: @js($track->title),
                                    artist: @js($track->artist->artist_name ?? 'Unknown Artist'),
                                    artwork: @js($track->cover_art ? Storage::disk('s3')->url($track->cover_art) : asset('images/default-track-cover.jpg')),
                                    audioUrl: @js(route('tracks.stream', $track)),
                                    format: @js(pathinfo($track->file_path ?? '', PATHINFO_EXTENSION) ?: 'mp3')
                                })"
                                class="px-3 py-2 bg-orange-100 text-orange-800 rounded-full text-xs font-medium hover:bg-orange-200 transition-colors"
                            >Play</button>

                            <button
                                @click="window.libraryActions.share(@js(route('tracks.show', $track)), @js($track->title))"
                                class="px-3 py-2 bg-gray-100 text-gray-700 rounded-full text-xs font-medium hover:bg-gray-200 transition-colors"
                            >Share</button>

                            <button
                                @click="window.libraryActions.toggleTrackLike({{ $track->id }}).then((result) => { if (result.success) hidden = true; })"
                                class="px-3 py-2 bg-gray-100 text-gray-700 rounded-full text-xs font-medium hover:bg-red-100 hover:text-red-700 transition-colors"
                            >Unlike</button>

                            <div class="relative" @click.outside="open = false">
                                <button @click="open = !open" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-full text-xs font-medium hover:bg-gray-200 transition-colors">+ Add</button>
                                <div x-show="open" x-cloak class="absolute right-0 mt-2 w-56 bg-white border border-orange-200 rounded-lg shadow-lg p-3 z-30">
                                    @if($userPlaylists->isEmpty())
                                        <a href="{{ route('playlists.create') }}" class="text-xs text-orange-600 hover:text-orange-700 font-medium">Create a playlist first &rarr;</a>
                                    @else
                                        <select x-model="playlistId" class="w-full bg-orange-50 text-gray-800 text-xs rounded px-2 py-2 border border-orange-200 focus:outline-none focus:ring-1 focus:ring-orange-400">
                                            @foreach($userPlaylists as $playlist)
                                                <option value="{{ $playlist->id }}">{{ $playlist->name }}</option>
                                            @endforeach
                                        </select>
                                        <button
                                            class="mt-2 w-full bg-orange-500 text-white rounded px-2 py-2 text-xs font-medium hover:bg-orange-600 transition-colors"
                                            @click="window.libraryActions.addTrackToPlaylist({{ $track->id }}, playlistId); open = false"
                                        >
                                            Save to Playlist
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section x-show="tab === 'artists'" x-cloak>
        @if($followedArtists->isEmpty())
            <div class="bg-white border border-orange-100 rounded-lg p-6 text-gray-500 text-sm">No followed artists yet. Follow artists to see updates here.</div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($followedArtists as $artist)
                    <div class="bg-white border border-orange-100 rounded-lg p-4 hover:border-orange-300 hover:shadow-sm transition">
                        <a href="{{ route('artists.show', $artist) }}" class="text-gray-900 font-semibold truncate block hover:text-orange-600 transition-colors">{{ $artist->artist_name }}</a>
                        <div class="text-xs text-gray-500 mt-1">{{ $artist->tracks_count ?? 0 }} tracks • {{ $artist->followers_count ?? 0 }} followers</div>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('artists.show', $artist) }}" class="px-3 py-2 bg-orange-100 text-orange-800 rounded-full text-xs font-medium hover:bg-orange-200 transition-colors">View</a>
                            <button
                                @click="window.libraryActions.share(@js(route('artists.show', $artist)), @js($artist->artist_name))"
                                class="px-3 py-2 bg-gray-100 text-gray-700 rounded-full text-xs font-medium hover:bg-gray-200 transition-colors"
                            >Share</button>
                            <form action="{{ route('artists.unfollow', $artist) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-full text-xs font-medium hover:bg-red-100 hover:text-red-700 transition-colors">Unfollow</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section x-show="tab === 'downloads'" x-cloak>
        @if($downloads->isEmpty())
            <div class="bg-white border border-orange-100 rounded-lg p-6 text-gray-500 text-sm">No downloads yet. Downloaded tracks will appear here.</div>
        @else
            <div class="space-y-2">
                @foreach($downloads as $download)
                    @if($download->track)
                        <div class="bg-white border border-orange-100 rounded-lg p-3 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-gray-900 text-sm font-medium truncate">{{ $download->track->title }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ $download->track->artist->artist_name ?? 'Unknown Artist' }}</div>
                                <div class="text-[11px] text-gray-400">{{ ucfirst($download->status ?? 'unknown') }} • {{ optional($download->created_at)->diffForHumans() }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    @click="$dispatch('track:play', {
                                        id: {{ $download->track->id }},
                                        title: @js($download->track->title),
                                        artist: @js($download->track->artist->artist_name ?? 'Unknown Artist'),
                                        artwork: @js($download->track->cover_art ? Storage::disk('s3')->url($download->track->cover_art) : asset('images/default-track-cover.jpg')),
                                        audioUrl: @js(route('tracks.stream', $download->track)),
                                        format: @js(pathinfo($download->track->file_path ?? '', PATHINFO_EXTENSION) ?: 'mp3')
                                    })"
                                    class="px-3 py-2 bg-orange-100 text-orange-800 rounded-full text-xs font-medium hover:bg-orange-200 transition-colors"
                                >Play</button>
                                <button
                                    @click="window.libraryActions.toggleTrackLike({{ $download->track->id }})"
                                    class="px-3 py-2 bg-gray-100 text-gray-700 rounded-full text-xs font-medium hover:bg-gray-200 transition-colors"
                                >Like</button>
                                <button
                                    @click="window.libraryActions.share(@js(route('tracks.show', $download->track)), @js($download->track->title))"
                                    class="px-3 py-2 bg-gray-100 text-gray-700 rounded-full text-xs font-medium hover:bg-gray-200 transition-colors"
                                >Share</button>
                                <a href="{{ route('tracks.download', $download->track) }}" class="px-3 py-2 bg-orange-100 text-orange-800 rounded-full text-xs font-medium hover:bg-orange-200 transition-colors">Download</a>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
