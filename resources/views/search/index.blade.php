@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="searchResults()">
    <div class="max-w-4xl mx-auto">
        <!-- Search Input -->
        <div class="mb-8">
            <div class="relative">
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    @input.debounce.300ms="search"
                    placeholder="Search for tracks, albums, or artists..." 
                    class="w-full px-4 py-3 rounded-lg border focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <div class="absolute right-3 top-3 text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Results Sections -->
        <div class="space-y-8">
            <!-- Tracks -->
            <div x-show="results.tracks.length > 0">
                <h2 class="text-xl font-bold mb-4">Tracks</h2>
                <div class="space-y-2">
                    <template x-for="track in results.tracks" :key="track.id">
                        <div class="flex items-center p-3 hover:bg-gray-50 rounded-lg">
                            <img :src="track.cover_art" class="w-12 h-12 rounded object-cover">
                            <div class="ml-4 flex-grow">
                                <h3 x-text="track.title" class="font-medium"></h3>
                                <p x-text="track.artist.artist_name" class="text-sm text-gray-600"></p>
                            </div>
                            <button @click="playTrack(track)" class="dson-btn">Play</button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Albums -->
            <div x-show="results.albums.length > 0">
                <h2 class="text-xl font-bold mb-4">Albums</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <template x-for="album in results.albums" :key="album.id">
                        <a :href="`/albums/${album.id}`" class="group">
                            <img :src="album.cover_art" class="w-full aspect-square rounded-lg object-cover">
                            <h3 x-text="album.title" class="mt-2 font-medium group-hover:text-red-600"></h3>
                            <p x-text="album.artist.artist_name" class="text-sm text-gray-600"></p>
                        </a>
                    </template>
                </div>
            </div>

            <!-- Artists -->
            <div x-show="results.artists.length > 0">
                <h2 class="text-xl font-bold mb-4">Artists</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <template x-for="artist in results.artists" :key="artist.id">
                        <a :href="`/artists/${artist.id}`" class="text-center group">
                            <img :src="artist.profile_image" class="w-32 h-32 mx-auto rounded-full object-cover">
                            <h3 x-text="artist.artist_name" class="mt-2 font-medium group-hover:text-red-600"></h3>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function searchResults() {
    return {
        searchQuery: '',
        results: {
            tracks: [],
            albums: [],
            artists: []
        },
        
        async search() {
            if (this.searchQuery.length < 2) {
                this.results = { tracks: [], albums: [], artists: [] };
                return;
            }
            
            try {
                const response = await fetch(`/search?q=${this.searchQuery}`);
                this.results = await response.json();
            } catch (error) {
                console.error('Search failed:', error);
            }
        }
    }
}
</script>
@endpush
@endsection
