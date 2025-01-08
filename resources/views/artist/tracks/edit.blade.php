@extends('layouts.artist')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="p-6 sm:p-8">
                <h2 class="text-2xl font-bold mb-6">Edit Track</h2>

                <form action="{{ route('artist.tracks.update', $track) }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Track Title</label>
                            <input type="text" 
                                   name="title" 
                                   value="{{ old('title', $track->title) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Genre</label>
                            <select name="genre" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                @foreach(['pop', 'rock', 'hip-hop', 'jazz', 'classical'] as $genre)
                                    <option value="{{ $genre }}" {{ $track->genre === $genre ? 'selected' : '' }}>
                                        {{ ucfirst($genre) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">New Track File (Optional)</label>
                            <input type="file" 
                                   name="track_file"
                                   accept="audio/*"
                                   class="mt-1 block w-full">
                                   <p class="mt-1 text-sm text-gray-500">Maximum file size: {{ ini_get('upload_max_filesize') }}B. Supported formats: MP3, WAV</p>
                          @error('track_file')
                              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                          @enderror
                            <p class="mt-2 text-sm text-gray-500">Current file: {{ basename($track->file_path) }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cover Art (Optional)</label>
                            <input type="file" 
                                   name="cover_art"
                                   accept="image/*"
                                   class="mt-1 block w-full">
                            @if($track->cover_art)
                                <img src="{{ Storage::url($track->cover_art) }}" 
                                     alt="Current cover" 
                                     class="mt-2 h-20 w-20 object-cover rounded">
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Release Date</label>
                            <input type="date" 
                                   name="release_date" 
                                   value="{{ $track->release_date->format('Y-m-d') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                @foreach(['draft', 'published', 'private'] as $status)
                                    <option value="{{ $status }}" {{ $track->status === $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('artist.tracks.index') }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" class="dson-btn">
                            Update Track
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
