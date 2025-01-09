<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <img src="{{ Storage::url($artist->profile_image) }}" class="w-full h-48 object-cover">
    <div class="p-4">
        <h3 class="font-bold text-lg mb-2">{{ $artist->artist_name }}</h3>
        <p class="text-gray-600 text-sm mb-4">{{ $artist->genre }}</p>
        <div class="flex justify-between items-center">
            <div class="text-sm text-gray-500">
                <span>{{ $artist->tracks_count }} tracks</span>
                <span class="mx-2">•</span>
                <span>{{ $artist->followers_count }} followers</span>
            </div>
            <a href="{{ route('artists.show', $artist) }}" class="text-purple-600 hover:text-purple-700 font-medium">
                View Profile →
            </a>
        </div>
    </div>
</div>
