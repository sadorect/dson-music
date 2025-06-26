@props(['artists'])

<div class="container mx-auto px-4">
    <h2 class="text-2xl md:text-3xl font-bold mb-6">Featured Artists</h2>
    
    @if($artists->isEmpty())
        <p class="text-gray-500">No featured artists available at the moment.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($artists as $artist)
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition-transform hover:scale-105">
                    <div class="h-48 bg-gray-200">
                        @if($artist->profile_photo_path)
                            <img src="{{ Storage::url($artist->profile_photo_path) }}" alt="{{ $artist->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                <span class="text-gray-500">No Image</span>
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1">{{ $artist->name }}</h3>
                        <div class="text-sm text-gray-600 mb-2">
                            <span>{{ $artist->tracks_count }} Tracks</span> • 
                            <span>{{ $artist->followers_count }} Followers</span>
                        </div>
                        <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View Profile</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>