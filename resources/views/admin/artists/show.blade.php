@extends('layouts.admin')

@section('title', 'Artist Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <div class="relative h-64">
            <img src="{{ Storage::url($artist->cover_image) }}" class="w-full h-full object-cover rounded-t-lg">
            <div class="absolute bottom-0 left-0 right-0 p-6 bg-gradient-to-t from-black/60">
                <div class="flex items-center">
                    <img src="{{ Storage::url($artist->profile_image) }}" class="w-24 h-24 rounded-full border-4 border-white">
                    <div class="ml-6 text-white">
                        <h1 class="text-3xl font-bold">{{ $artist->artist_name }}</h1>
                        <p class="text-lg">{{ $artist->genre }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Bio</h2>
                <p class="text-gray-600">{{ $artist->bio }}</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-xl font-semibold mb-4">Tracks ({{ $artist->tracks->count() }})</h2>
                    <div class="space-y-4">
                        @foreach($artist->tracks as $track)
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <img src="{{ Storage::url($track->cover_art) }}" class="w-16 h-16 rounded object-cover">
                                <div class="ml-4">
                                    <h3 class="font-medium">{{ $track->title }}</h3>
                                    <p class="text-sm text-gray-500">{{ number_format($track->play_count) }} plays</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold mb-4">Albums ({{ $artist->albums->count() }})</h2>
                    <div class="space-y-4">
                        @foreach($artist->albums as $album)
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <img src="{{ Storage::url($album->cover_art) }}" class="w-16 h-16 rounded object-cover">
                                <div class="ml-4">
                                    <h3 class="font-medium">{{ $album->title }}</h3>
                                    <p class="text-sm text-gray-500">{{ $album->tracks->count() }} tracks</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
