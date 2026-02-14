@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Search Input -->
        <div class="mb-8">
            <form action="{{ route('search') }}" method="GET">
                <input 
                    type="text" 
                    name="q"
                    value="{{ $query }}"
                    class="w-full px-4 py-3 rounded-lg border"
                    placeholder="Search for tracks, albums, or artists...">
            </form>
        </div>

        <!-- Results Sections -->
        <div class="space-y-8">
            @if($tracks->count() > 0)
            <div>
                <h2 class="text-xl font-bold mb-4">Tracks</h2>
                <div class="space-y-2">
                    @foreach($tracks as $track)
                    <div class="flex items-center p-3 hover:bg-gray-50 rounded-lg">
                        <img src="{{ $track->cover_art ? Storage::url($track->cover_art) : asset('images/default-track-cover.jpg') }}" class="w-12 h-12 rounded object-cover">
                        <div class="ml-4 flex-grow">
                            <h3 class="font-medium">{{ $track->title }}</h3>
                            <p class="text-sm text-gray-600">{{ $track->artist->artist_name }}</p>
                        </div>
                        <button onclick="playTrack({{ $track->id }})" class="dson-btn">Play</button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($artists->count() > 0)
            <div>
                <h2 class="text-xl font-bold mb-4">Artists</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($artists as $artist)
                    <a href="/artists/{{ $artist->id }}" class="text-center group">
                        <img src="{{ $artist->profile_image ? Storage::url($artist->profile_image) : asset('images/default-profile.jpg') }}" class="w-32 h-32 mx-auto rounded-full object-cover">
                        <h3 class="mt-2 font-medium group-hover:text-red-600">{{ $artist->artist_name }}</h3>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
