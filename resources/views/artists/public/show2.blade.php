@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Artist Header -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="relative h-48 bg-gradient-to-r from-purple-600 to-blue-600">
            <img src="{{ $artist->profile_image ? Storage::disk('s3')->url($artist->profile_image) : asset('images/default-profile.webp') }}" 
                 class="absolute bottom-0 left-8 transform translate-y-1/2 w-32 h-32 rounded-full border-4 border-white object-cover"
                 onerror="this.src='{{ asset('images/default-profile.webp') }}'">
        </div>
        
        <div class="pt-20 pb-6 px-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold">{{ $artist->artist_name }}</h1>
                    <p class="text-gray-600">{{ $artist->genre }}</p>
                    <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                        <span>{{ $artist->tracks_count }} tracks</span>
                        <span>{{ $artist->followers_count }} followers</span>
                    </div>
                </div>
                
                @auth
                    @if(auth()->user()->id !== $artist->user_id)
                        <form action="{{ auth()->user()->isFollowing($artist) ? route('artists.unfollow', $artist) : route('artists.follow', $artist) }}" 
                              method="POST">
                            @csrf
                            @if(auth()->user()->isFollowing($artist))
                                @method('DELETE')
                            @endif
                            <button type="submit" 
                                    class="px-6 py-2 rounded-full {{ auth()->user()->isFollowing($artist) ? 'bg-gray-200 hover:bg-gray-300' : 'bg-purple-600 text-white hover:bg-purple-700' }}">
                                {{ auth()->user()->isFollowing($artist) ? 'Following' : 'Follow' }}
                            </button>
                        </form>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <!-- Tracks Section -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($artist->tracks as $track)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <img src="{{ $track->cover_art ? Storage::disk('s3')->url($track->cover_art) : asset('images/default-cover.webp') }}" 
                     class="w-full h-48 object-cover"
                     onerror="this.src='{{ asset('images/default-cover.webp') }}'">
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-2">{{ $track->title }}</h3>
                    <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                        <span>{{ $track->plays_count }} plays</span>
                        <span>{{ $track->likes_count }} likes</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <button x-data
                        @click="$dispatch('track:play', {
                            id: {{ $track->id }},
                            title: '{{ $track->title }}',
                            artist: '{{ $track->artist->artist_name }}',
                            artwork: '{{ $track->cover_art ? Storage::disk('s3')->url($track->cover_art) : '/images/default-cover.webp' }}',
                            audioUrl: '{{ route('tracks.stream', $track) }}',
                            format: '{{ $track->file_path ? pathinfo($track->file_path, PATHINFO_EXTENSION) : 'mp3' }}'
                        })"  class="text-purple-600 hover:text-purple-700">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <button x-data
                        @click="
                            console.log('Adding track to queue:', {
                                id: {{ $track->id }},
                                title: '{{ $track->title }}',
                                artist: '{{ $track->artist->artist_name }}',
                                artwork: '{{ $track->cover_art ? Storage::disk('s3')->url($track->cover_art) : '/default-cover.jpg' }}',
                                audioUrl: '{{ route('tracks.stream', $track) }}'
                            });
                            $dispatch('queue:add', {
                                id: {{ $track->id }},
                                title: '{{ $track->title }}',
                                artist: '{{ $track->artist->artist_name }}',
                                artwork: '{{ $track->cover_art ? Storage::disk('s3')->url($track->cover_art) : '/default-cover.jpg' }}',
                                audioUrl: '{{ route('tracks.stream', $track) }}'
                            });"
                        class="dson-btn-secondary">
                        Add to Queue
                    </button>
                    


                        <button 
                        x-data="{ liked: {{ auth()->check() && auth()->user()->likes()->where('likeable_id', $track->id)->exists() ? 'true' : 'false' }} }"
                        @click=" @auth
                            fetch('{{ route('tracks.like', $track) }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                liked = !liked;
                                $refs.likeCount.textContent = data.likes_count;
                            })
                            @else
                            window.location.href = '{{ route('login') }}'
                        @endauth
                        "
                        class="flex items-center space-x-1 text-gray-600 hover:text-red-600 transition-colors"
                    >
                        <svg 
                            :class="{ 'text-red-600 fill-current': liked }"
                            class="w-6 h-6" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span x-ref="likeCount">{{ $track->likes()->count() }}</span>
                    </button>

                        <a href="{{ route('tracks.download', $track) }}" class="text-gray-600 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
function playTrack(trackId) {
    // Implement audio player functionality
}
</script>
@endpush
@endsection