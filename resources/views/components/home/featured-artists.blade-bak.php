<div class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold">Featured Artists</h2>
            <a href="{{ route('artists.index') }}" class="text-red-600 hover:text-red-700">View All →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @foreach($artists as $artist)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition hover:scale-105">
                    <a href="{{ route('artists.show', $artist) }}" class="block">
                        <img src="{{ Storage::url($artist->profile_image) }}" class="w-full h-48 object-cover">
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
    </div>
</div>
