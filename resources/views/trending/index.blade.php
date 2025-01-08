@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Trending Now</h1>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            @foreach($trendingTracks as $track)
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <img src="{{ Storage::url($track->cover_art) }}" 
                     alt="{{ $track->title }}" 
                     class="w-full aspect-square object-cover rounded-t-lg">
                
                <div class="p-4">
                    <h3 class="font-semibold truncate">{{ $track->title }}</h3>
                    <p class="text-sm text-gray-600">{{ $track->artist->artist_name }}</p>
                    
                    <div class="flex items-center justify-between mt-3">
                        <span class="text-sm text-gray-500">
                            {{ number_format($track->play_count) }} plays
                        </span>
                        <button onclick="playTrack({{ $track->id }})" class="dson-btn-sm">
                            Play
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
