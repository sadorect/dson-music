@extends('layouts.artist')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="p-6 sm:p-8">
                <h2 class="text-2xl font-bold mb-6">Edit Album</h2>

                <form action="{{ route('artist.albums.update', $album) }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Album Title</label>
                            <input type="text" 
                                   name="title" 
                                   value="{{ old('title', $album->title) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                @foreach(['album' => 'Album', 'EP' => 'EP', 'single' => 'Single'] as $value => $label)
                                    <option value="{{ $value }}" {{ $album->type === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" 
                                  rows="4" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">{{ old('description', $album->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cover Art</label>
                            <input type="file" 
                                   name="cover_art"
                                   accept="image/*"
                                   class="mt-1 block w-full">
                                   <div id="coverPreview" class="mt-2"></div>
                            @if($album->cover_art)
                                <img src="{{ Storage::url($album->cover_art) }}" 
                                     alt="Current cover" 
                                     class="mt-2 h-32 w-32 object-cover rounded">
                                     <div id="coverPreview" class="mt-2"></div>
                            @endif
                        </div>
                       <!-- Cover art Preview --> 
                  


                        <div>
                            <label class="block text-sm font-medium text-gray-700">Release Date</label>
                            <input type="date" 
                                   name="release_date" 
                                   value="{{ $album->release_date->format('Y-m-d') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            @foreach(['draft' => 'Draft', 'published' => 'Published', 'private' => 'Private'] as $value => $label)
                                <option value="{{ $value }}" {{ $album->status === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('artist.albums.index') }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" class="dson-btn">
                            Update Album
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const coverArtInput = document.querySelector('input[name="cover_art"]');
    const previewContainer = document.getElementById('coverPreview');

    coverArtInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewContainer.innerHTML = `
                    <img src="${e.target.result}" class="mt-2 h-32 w-32 object-cover rounded shadow-lg">
                `;
            }
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endpush

@endsection
