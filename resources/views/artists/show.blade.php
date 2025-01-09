@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-lg">
        <!-- Artist Header -->
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <img src="{{ Storage::url($artist->profile_image) }}" class="w-32 h-32 rounded-full object-cover">
                    <div>
                        <h1 class="text-3xl font-bold">{{ $artist->artist_name }}</h1>
                        <p class="text-gray-600">{{ $artist->genre }}</p>
                        <p class="text-sm text-gray-500">{{ $artist->followers_count }} followers</p>
                    </div>
                </div>
                
                <!-- Follow Button -->
                @auth
                    @if(auth()->user()->id !== $artist->user_id)
                        <form action="{{ auth()->user()->isFollowing($artist) ? route('artists.unfollow', $artist) : route('artists.follow', $artist) }}" 
                              method="POST">
                            @csrf
                            @if(auth()->user()->isFollowing($artist))
                                @method('DELETE')
                            @endif
                            <button type="submit" 
                                    class="px-6 py-2 rounded-full {{ auth()->user()->isFollowing($artist) ? 'bg-gray-200 hover:bg-gray-300' : 'bg-blue-600 text-white hover:bg-blue-700' }}">
                                {{ auth()->user()->isFollowing($artist) ? 'Following' : 'Follow' }}
                            </button>
                        </form>
                    @endif
                @endauth
            </div>
        </div>

        <!-- Latest Tracks -->
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Latest Tracks</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($artist->tracks as $track)
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <img src="{{ Storage::url($track->cover_art) }}" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h4 class="font-bold text-lg mb-2">{{ $track->title }}</h4>
                            <div class="flex justify-between items-center mb-4">
                                <div class="text-sm text-gray-500">
                                    {{ $track->plays_count }} plays
                                </div>
                                <div class="flex items-center space-x-4">
                                    <!-- Like Button -->
                                    <button onclick="toggleLike({{ $track->id }})" 
                                            class="like-button flex items-center space-x-1" 
                                            data-track="{{ $track->id }}">
                                        <svg class="w-6 h-6 {{ $track->isLikedBy(auth()->user()) ? 'text-red-500' : 'text-gray-400' }}" 
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                                        </svg>
                                        <span class="likes-count">{{ $track->likes_count }}</span>
                                    </button>
                                    
                                    <!-- Download Button -->
                                    <a href="{{ route('tracks.download', $track) }}" 
                                       class="text-gray-600 hover:text-gray-800 flex items-center space-x-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        <span>{{ $track->downloads_count }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleLike(trackId) {
    fetch(`/tracks/${trackId}/like`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const likeButton = document.querySelector(`.like-button[data-track="${trackId}"]`);
        const likesCount = likeButton.querySelector('.likes-count');
        const icon = likeButton.querySelector('svg');
        
        likesCount.textContent = data.likes_count;
        icon.classList.toggle('text-red-500');
        icon.classList.toggle('text-gray-400');
    });
}
</script>
@endpush
@endsection
