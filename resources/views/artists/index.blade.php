@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">All Artists</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($artists as $artist)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition hover:scale-105">
                <a href="{{ route('artists.show', $artist) }}" class="block">
                    <img src="{{ $artist->profile_image ? Storage::url($artist->profile_image) : asset('images/default-profile.jpg') }}" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-bold text-lg">{{ $artist->artist_name }}</h3>
                            @if($artist->is_verified)
                                <span class="bg-red-600 text-white text-xs px-2 py-1 rounded-full">Verified</span>
                            @endif
                        </div>
                        <p class="text-gray-600 text-sm mb-4">{{ $artist->genre }}</p>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span>{{ $artist->tracks_count }} tracks</span>
                            <span>{{ $artist->followers_count }} followers</span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $artists->links() }}
    </div>
</div>
@endsection
