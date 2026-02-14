<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <img src="{{ Storage::url($track->cover_art) }}" class="w-full h-48 object-cover">
    <div class="p-4">
        <h3 class="font-bold text-lg mb-2">{{ $track->title }}</h3>
        <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
            <span>{{ $track->plays_count ?? 0 }} plays</span>
            <span>{{ $track->likes_count ?? 0 }} likes</span>
        </div>
        <div class="flex justify-between items-center">
            <button
                x-data
                @click="$dispatch('track:play', {
                    id: {{ $track->id }},
                    title: @js($track->title),
                    artist: @js($track->artist->artist_name ?? $track->artist?->user?->name ?? 'Unknown Artist'),
                    artwork: @js($track->cover_art ? Storage::disk('s3')->url($track->cover_art) : asset('images/default-track-cover.jpg')),
                    audioUrl: @js(route('tracks.stream', $track))
                })"
                class="text-primary-color hover:text-primary-color/80"
            >
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                </svg>
            </button>
            <a href="{{ route('tracks.download', $track) }}" class="text-gray-600 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
            <button 
    @click="$dispatch('queue:add', {
        id: {{ $track->id }},
        title: '{{ $track->title }}',
        artist: '{{ $track->artist->artist_name }}',
        artwork: '{{ Storage::disk('s3')->url($track->cover_art) }}',
        audioUrl: '{{ route('tracks.stream', $track) }}'
    })"
    class="dson-btn-secondary">
    Add to Queue
</button>

        </div>
    </div>
</div>
