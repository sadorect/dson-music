@extends('layouts.artist')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="p-6 sm:p-8">
                <h2 class="text-2xl font-bold mb-6">{{ isset($album) ? 'Edit Release' : 'New Release' }}</h2>

                <form action="{{ isset($album) ? route('artist.albums.update', $album) : route('artist.albums.store') }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      class="space-y-6"
                      id="albumForm">
                    @csrf
                    @if(isset($album)) @method('PUT') @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                <input type="text" 
                                       name="title" 
                                       value="{{ old('title', $album->title ?? '') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Release Type</label>
                                <select name="type" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    @foreach(['album' => 'Album', 'EP' => 'EP', 'single' => 'Single'] as $value => $label)
                                        <option value="{{ $value }}" 
                                                {{ (old('type', $album->type ?? '') == $value) ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Release Date</label>
                                <input type="date" 
                                       name="release_date" 
                                       value="{{ old('release_date', isset($album) ? $album->release_date->format('Y-m-d') : '') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cover Art</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md relative" 
                                     id="coverArtPreview">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="cover_art" class="relative cursor-pointer bg-white rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                                                <span>Upload a file</span>
                                                <input id="cover_art" name="cover_art" type="file" class="sr-only" accept="image/*">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                    </div>
                                    @if(isset($album) && $album->cover_art)
                                        <img src="{{ Storage::url($album->cover_art) }}" 
                                             class="absolute inset-0 w-full h-full object-cover rounded-md">
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    @foreach(['draft' => 'Draft', 'published' => 'Published', 'private' => 'Private'] as $value => $label)
                                        <option value="{{ $value }}" 
                                                {{ (old('status', $album->status ?? '') == $value) ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" 
                                  rows="4" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">{{ old('description', $album->description ?? '') }}</textarea>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('artist.albums.index') }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="dson-btn">
                            {{ isset($album) ? 'Update Release' : 'Create Release' }}
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
    const coverArtInput = document.getElementById('cover_art');
    const previewContainer = document.getElementById('coverArtPreview');

    coverArtInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'absolute inset-0 w-full h-full object-cover rounded-md';
                
                const existingImg = previewContainer.querySelector('img');
                if (existingImg) {
                    existingImg.remove();
                }
                
                previewContainer.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    });

    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        previewContainer.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        previewContainer.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        previewContainer.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        previewContainer.classList.add('border-red-500');
    }

    function unhighlight(e) {
        previewContainer.classList.remove('border-red-500');
    }

    previewContainer.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const file = dt.files[0];
        
        coverArtInput.files = dt.files;
        const event = new Event('change');
        coverArtInput.dispatchEvent(event);
    }
});
</script>
@endpush
@endsection
