<div class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8">New Releases</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @foreach($tracks as $track)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition hover:scale-105">
                    <a href="{{ route('tracks.show', $track) }}" class="block">
                        <div class="relative">
                            @if($track->cover_art)
                 

                            <img src="{{ Storage::disk('s3')->url($track->cover_art) }}" class="w-full h-48 object-cover">
                        @else
                         {{-- Add a default image fallback --}}
        <img src="{{ asset('images/default-cover.jpg') }}" class="w-full h-48 object-cover">
        @endif
                            <div class="absolute top-2 right-2 bg-red-600 text-white text-xs px-2 py-1 rounded-full">
                                NEW
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2">{{ $track->title }}</h3>
                            <a href="{{ route('artists.show', $track->artist) }}" class="text-gray-600 text-sm mb-4 hover:text-red-600">
                                {{ $track->artist->artist_name }}
                            </a>
                            <div class="flex justify-between items-center mt-4">
                                <span class="text-sm text-gray-500">{{ $track->release_date->diffForHumans() }}</span>
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
