<div class="py-16 bg-gray-50">
  <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold mb-8">Featured Artists</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          @foreach($artists as $artist)
              <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition hover:scale-105">
                  <img src="{{ Storage::url($artist->profile_image) }}" class="w-full h-48 object-cover">
                  <div class="p-4">
                      <div class="flex items-center justify-between mb-3">
                          <h3 class="font-bold text-lg">{{ $artist->artist_name }}</h3>
                          @if($artist->is_verified)
                              <span class="bg-dson-red text-white text-xs px-2 py-1 rounded-full">Verified</span>
                          @endif
                      </div>
                      <p class="text-gray-600 text-sm mb-4">{{ $artist->genre }}</p>
                      <div class="flex justify-between items-center">
                          <div class="text-sm text-gray-500">
                              <span>{{ $artist->tracks_count }} tracks</span>
                              <span class="mx-2">•</span>
                              <span>{{ $artist->followers_count }} followers</span>
                          </div>
                          <a href="{{ route('artists.show', $artist) }}" 
                             class="text-dson-red hover:opacity-75 font-medium transition">
                              View Profile →
                          </a>
                      </div>
                  </div>
              </div>
          @endforeach
      </div>
  </div>
</div>
