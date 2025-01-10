@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Track Details: {{ $track->title }}</h1>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold 
                        {{ $track->approval_status === 'approved' ? 'bg-green-100 text-green-800' : 
                           ($track->approval_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($track->approval_status) }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <img src="{{ Storage::url($track->cover_art) }}" 
                             alt="{{ $track->title }}" 
                             class="w-full h-64 object-cover rounded-lg">
                    </div>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Artist</h3>
                            <p class="text-lg text-gray-900">{{ $track->artist->artist_name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Genre</h3>
                            <p class="text-lg text-gray-900">{{ $track->genre }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Release Date</h3>
                            <p class="text-lg text-gray-900">{{ $track->release_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Play Count</h3>
                            <p class="text-lg text-gray-900">{{ number_format($track->play_count) }}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Preview Track</h3>
                    <audio controls class="w-full">
                        <source src="{{ Storage::url($track->file_path) }}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.tracks.edit', $track) }}" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Edit Track
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
