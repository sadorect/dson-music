<div class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold">Trending Now</h2>
            <a href="{{ route('trending') }}" class="text-red-600 hover:text-red-700">View All →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @foreach($trendingTracks as $track)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition hover:scale-105">
                    <a href="{{ route('tracks.show', $track) }}" class="block">
                        <img src="{{ Storage::url($track->cover_art) }}" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2">{{ $track->title }}</h3>
                            <p class="text-gray-600 text-sm">{{ $track->artist->artist_name }}</p>
                            <div class="mt-4 flex justify-between items-center">
                                <span class="text-sm text-gray-500">{{ number_format($track->play_count) }} plays</span>
                                <button onclick="event.preventDefault(); playTrack({{ $track->id }})" class="text-red-600 hover:text-red-700">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
