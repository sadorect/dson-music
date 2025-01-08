@extends('layouts.artist')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('artist.albums.index') }}" class="flex items-center text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Albums
        </a>
    </div>


    
        <!-- Album Header -->
        <div class="flex flex-col md:flex-row gap-8 mb-8">
            <div class="w-full md:w-1/3">
                @if($album->cover_art)
                <img src="{{ Storage::url($album->cover_art) }}" 
                     alt="{{ $album->title }}" 
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                    <span class="text-gray-400">No cover art available</span>
                </div>
            @endif
            </div>
            
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-3 py-1 text-sm font-semibold text-white bg-red-500 rounded-full">
                        {{ ucfirst($album->type) }}
                    </span>
                    <span class="px-3 py-1 text-sm font-semibold {{ $album->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} rounded-full">
                        {{ ucfirst($album->status) }}
                    </span>
                </div>
                
                <h1 class="text-4xl font-bold mb-4">{{ $album->title }}</h1>
                <p class="text-gray-600 mb-6">{{ $album->description }}</p>
                
                <div class="flex items-center gap-6 text-gray-600">
                    <div>
                        <span class="block text-2xl font-bold">{{ $album->tracks->count() }}</span>
                        <span class="text-sm">Tracks</span>
                    </div>
                    <div>
                        <span class="block text-2xl font-bold">{{ $album->play_count }}</span>
                        <span class="text-sm">Plays</span>
                    </div>
                    <div>
                        <span class="block text-2xl font-bold">{{ $album->release_date->format('M Y') }}</span>
                        <span class="text-sm">Release Date</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tracks Section -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Tracks</h2>
                <a href="{{ route('artist.tracks.create', ['album_id' => $album->id]) }}" 
                   class="dson-btn">
                    Add Track
                </a>
            </div>

            <div class="space-y-4">
                @forelse($tracks as $track)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center gap-4">
                            <span class="text-gray-500">{{ $loop->iteration }}</span>
                            <div>
                                <h3 class="font-medium">{{ $track->title }}</h3>
                                <span class="text-sm text-gray-500">{{ gmdate('i:s', $track->duration) }}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-500">{{ number_format($track->play_count) }} plays</span>
                            <div class="flex gap-2">
                                <button class="text-gray-600 hover:text-gray-900" onclick="playTrack('{{ $track->id }}')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                                <a href="{{ route('artist.tracks.edit', $track) }}" class="text-gray-600 hover:text-gray-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <p class="text-gray-500">No tracks added yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function playTrack(trackId) {
    // Implement track playback functionality
}
</script>
@endpush
@endsection
