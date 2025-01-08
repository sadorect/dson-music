@extends('layouts.artist')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="p-6 sm:p-8">
                <h2 class="text-2xl font-bold mb-6">Add New Track</h2>

                <form action="{{ route('artist.tracks.store') }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      class="space-y-6">
                    @csrf
                    
                    @if(request('album_id'))
                        <input type="hidden" name="album_id" value="{{ request('album_id') }}">
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Track Title</label>
                        <input type="text" 
                               name="title" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Genre</label>
                        <select name="genre" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            <option value="pop">Pop</option>
                            <option value="rock">Rock</option>
                            <option value="hip-hop">Hip Hop</option>
                            <option value="jazz">Jazz</option>
                            <option value="classical">Classical</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Track File</label>
                        <input type="file" 
                               name="track_file"
                               accept="audio/*"
                               class="mt-1 block w-full">
                               <p class="mt-1 text-sm text-gray-500">Maximum file size: {{ ini_get('upload_max_filesize') }}B. Supported formats: MP3, WAV</p>
                          @error('track_file')
                              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                          @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cover Art</label>
                        <input type="file" 
                               name="cover_art"
                               accept="image/*"
                               class="mt-1 block w-full">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Release Date</label>
                        <input type="date" 
                               name="release_date" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="private">Private</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="{{ url()->previous() }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="dson-btn">
                            Add Track
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
