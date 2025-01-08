@extends('layouts.artist')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="relative h-64">
                <img src="{{ Storage::url($track->cover_art) }}" 
                     alt="{{ $track->title }}" 
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent">
                    <div class="absolute bottom-6 left-6">
                        <h1 class="text-3xl font-bold text-white mb-2">{{ $track->title }}</h1>
                        <div class="flex items-center space-x-4 text-white">
                            <span>{{ $track->genre }}</span>
                            <span>•</span>
                            <span>{{ $track->release_date->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center space-x-6">
                        <button onclick="playTrack('{{ $track->id }}')" 
                                class="flex items-center space-x-2 px-4 py-2 bg-red-600 text-white rounded-full hover:bg-red-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            </svg>
                            <span>Play Track</span>
                        </button>
                        
                        <div class="flex items-center space-x-4">
                            <div class="text-center">
                                <span class="block text-2xl font-bold">{{ number_format($track->play_count) }}</span>
                                <span class="text-sm text-gray-500">Plays</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <a href="{{ route('artist.tracks.edit', $track) }}" 
                           class="flex items-center space-x-2 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            <span>Edit Track</span>
                        </a>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold mb-4">Track Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-sm text-gray-500">Album</span>
                            <span>{{ $track->album->title ?? 'Single Release' }}</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500">Duration</span>
                            <span>{{ gmdate('i:s', $track->duration) }}</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500">Status</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $track->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($track->status) }}
                            </span>
                        </div>
                    </div>
                </div>
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
