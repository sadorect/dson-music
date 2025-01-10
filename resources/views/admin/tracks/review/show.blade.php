@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Track Review: {{ $track->title }}</h1>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold 
                        {{ $track->download_type === 'free' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ ucfirst($track->download_type) }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <img src="{{ Storage::url($track->cover_art) }}" 
                             alt="{{ $track->title }}" 
                             class="w-full h-64 object-cover rounded-lg">
                    </div>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Artist</h3>
                            <p class="text-lg text-gray-900">{{ $track->artist->artist_name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Genre</h3>
                            <p class="text-lg text-gray-900">{{ $track->genre }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Release Date</h3>
                            <p class="text-lg text-gray-900">{{ $track->release_date->format('M d, Y') }}</p>
                        </div>
                        @if($track->download_type === 'donate')
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Minimum Donation</h3>
                            <p class="text-lg text-gray-900">${{ number_format($track->minimum_donation, 2) }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Preview Track</h3>
                    <audio controls class="w-full">
                        <source src="{{ Storage::url($track->file_path) }}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                </div>

                <div class="flex justify-end space-x-4">
                  <button type="button" 
                  onclick="toggleRejectModal(true)" 
                  class="px-4 py-2 border border-red-300 text-red-700 rounded-md hover:bg-red-50">
              Reject
          </button>
                    <form action="{{ route('admin.tracks.review.approve', $track) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Approve & Publish
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Provide Rejection Reason</h3>
            <form action="{{ route('admin.tracks.review.reject', $track) }}" method="POST">
                @csrf
                <textarea name="rejection_reason" 
                          rows="4" 
                          class="w-full border-gray-300 rounded-md shadow-sm"
                          placeholder="Explain why this track is being rejected..."></textarea>
                
                <div class="flex justify-end space-x-4 mt-4">
                  <button type="button" 
                  onclick="toggleRejectModal(false)"
                  class="px-4 py-2 border border-gray-300 rounded-md">
              Cancel
          </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Reject Track
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleRejectModal(show) {
    const modal = document.getElementById('rejectModal');
    if (show) {
        modal.classList.remove('hidden');
    } else {
        modal.classList.add('hidden');
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('rejectModal');
    if (event.target === modal) {
        toggleRejectModal(false);
    }
});
</script>
@endpush
@endsection
