@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-lg">
        <!-- Track Header -->
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <img src="{{ Storage::url($track->artwork) }}" class="w-24 h-24 rounded-lg object-cover">
                    <div>
                        <h1 class="text-2xl font-bold">{{ $track->title }}</h1>
                        <p class="text-gray-600">{{ $track->artist->artist_name }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Like Button -->
                    <button 
                        class="flex items-center space-x-2 {{ $track->isLikedBy(auth()->user()) ? 'text-red-500' : 'text-gray-500' }}"
                        onclick="toggleLike({{ $track->id }})"
                    >
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                        </svg>
                        <span id="likes-count">{{ $track->likes_count }}</span>
                    </button>

                    <!-- Download Button -->
                    <a href="{{ route('tracks.download', $track) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download
                        <span class="ml-1">({{ $track->downloads_count }})</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        @include('components.comments.section', [
            'type' => 'track',
            'model' => $track
        ])
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
        document.getElementById('likes-count').textContent = data.likes_count;
    });
}

document.getElementById('comment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const content = form.querySelector('textarea').value;

    fetch(`/tracks/{{ $track->id }}/comments`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ content })
    })
    .then(response => response.json())
    .then(data => {
        form.reset();
        location.reload();
    });
});
</script>
@endpush
@endsection
