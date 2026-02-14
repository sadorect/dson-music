@extends('layouts.app')

@section('content')
@php
    $coverImage = $artist->cover_image
        ? \Illuminate\Support\Facades\Storage::url($artist->cover_image)
        : 'https://source.unsplash.com/1600x900/?music,artist';
    $monthlyListeners = number_format($artist->followers_count ?? 0);
@endphp

<div class="w-full">
    <div class="relative h-[320px] w-full">
        <img src="{{ $coverImage }}" alt="{{ $artist->artist_name }} cover" class="w-full h-full object-cover">

        <div class="flex flex-col gap-2 absolute p-8 bottom-0 left-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent w-full">
            <div class="flex items-center gap-2">
                @if($artist->is_verified)
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary-color">
                        <path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>
                    <p class="text-white text-sm">Verified Artist</p>
                @endif
            </div>
            <h1 class="text-5xl md:text-7xl font-bold text-white">{{ $artist->artist_name }}</h1>
            <p class="text-white/80 text-sm">{{ $monthlyListeners }} followers</p>
        </div>
    </div>

    <div class="p-4 space-y-6">
        <div>
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-bold text-white text-lg">Popular</h2>
                @if($popularTracks->count() >= 5)
                    <a href="#popular" class="text-white/60 text-sm">See more</a>
                @endif
            </div>
            <div class="space-y-2">
                @forelse($popularTracks as $index => $track)
                    <div class="flex gap-4 items-center p-2 hover:bg-white/10 rounded-md">
                        <div class="w-1/12 text-center text-white/60">{{ $index + 1 }}</div>
                        <div class="w-9/12">
                            <p class="text-white">{{ $track->title }}</p>
                            <p class="text-white/60 text-sm">{{ $track->artist->artist_name }}</p>
                        </div>
                        <div class="w-2/12 text-right text-white/60 text-sm">
                            {{ $track->duration ? gmdate('i:s', $track->duration) : '—' }}
                        </div>
                    </div>
                @empty
                    <p class="text-white/60 text-sm">No tracks yet.</p>
                @endforelse
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-2xl font-bold text-white">Discography</h2>
                <a href="#" class="text-sm text-gray-400 hover:text-white">Show all</a>
            </div>
            <div class="flex gap-4 overflow-x-auto pb-2">
                @forelse($latestAlbums as $album)
                    @php
                        $albumCover = $album->cover_image ? \Illuminate\Support\Facades\Storage::url($album->cover_image) : 'https://source.unsplash.com/400x400/?album';
                    @endphp
                    <div class="w-[200px] flex-shrink-0">
                        <div class="w-full h-48 rounded-xl overflow-hidden shadow-md mb-2">
                            <img src="{{ $albumCover }}" alt="{{ $album->title }}" class="w-full h-full object-cover">
                        </div>
                        <p class="text-white text-sm font-semibold truncate">{{ $album->title }}</p>
                        <p class="text-gray-400 text-xs">{{ optional($album->release_date)->format('Y') ?? '—' }}</p>
                    </div>
                @empty
                    <p class="text-white/60 text-sm">No albums published yet.</p>
                @endforelse
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-2xl font-bold text-white">Featuring {{ $artist->artist_name }}</h2>
                <a href="#" class="text-sm text-gray-400 hover:text-white">Show all</a>
            </div>
            <div class="flex gap-4 overflow-x-auto pb-2">
                @forelse($featuredTracks as $track)
                    @php
                        $image = $track->cover_art ? \Illuminate\Support\Facades\Storage::url($track->cover_art) : 'https://source.unsplash.com/400x400/?music';
                    @endphp
                    <div class="w-[200px] flex-shrink-0">
                        <div class="w-full h-48 rounded-xl overflow-hidden shadow-md mb-2">
                            <img src="{{ $image }}" alt="{{ $track->title }}" class="w-full h-full object-cover">
                        </div>
                        <p class="text-white text-sm font-semibold truncate">{{ $track->title }}</p>
                        <p class="text-gray-400 text-xs truncate">{{ $track->artist->artist_name }}</p>
                    </div>
                @empty
                    <p class="text-white/60 text-sm">No featured tracks yet.</p>
                @endforelse
            </div>
        </div>

        <div>
            <h2 class="text-2xl font-bold text-white mb-4">About the artist</h2>
            <div class="relative h-[280px] w-full rounded-xl overflow-hidden">
                <img src="{{ $coverImage }}" alt="{{ $artist->artist_name }} background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-transparent p-6 flex flex-col justify-end gap-2">
                    <p class="text-white/80 text-sm">{{ $artist->bio ?? 'This artist has not added a bio yet.' }}</p>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-2xl font-bold text-white mb-4">Fans also like</h2>
            <div class="flex gap-4 overflow-x-auto pb-2">
                @forelse($relatedArtists as $related)
                    @php
                        $relatedImage = $related->profile_image ? \Illuminate\Support\Facades\Storage::url($related->profile_image) : 'https://source.unsplash.com/400x400/?artist,portrait';
                    @endphp
                    <x-artist
                        :title="$related->artist_name"
                        :artist="$related->genre ?? 'Artist'"
                        :image="$relatedImage"
                        :url="route('artists.show', $related)" />
                @empty
                    <p class="text-white/60 text-sm">No similar artists yet.</p>
                @endforelse
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-2xl font-bold text-white">Appears on</h2>
                <a href="#" class="text-sm text-gray-400 hover:text-white">Show all</a>
            </div>
            <div class="flex gap-4 overflow-x-auto pb-2">
                @forelse($appearsOn as $track)
                    @php
                        $trackImage = $track->cover_art ? \Illuminate\Support\Facades\Storage::url($track->cover_art) : 'https://source.unsplash.com/400x400/?music,cover';
                    @endphp
                    <div class="w-[200px] flex-shrink-0">
                        <div class="w-full h-48 rounded-xl overflow-hidden shadow-md mb-2">
                            <img src="{{ $trackImage }}" alt="{{ $track->title }}" class="w-full h-full object-cover">
                        </div>
                        <p class="text-white text-sm font-semibold truncate">{{ $track->title }}</p>
                        <p class="text-gray-400 text-xs truncate">{{ $track->artist->artist_name }}</p>
                    </div>
                @empty
                    <p class="text-white/60 text-sm">No collaborative tracks available.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection