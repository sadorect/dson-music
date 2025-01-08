@extends('layouts.admin')

@section('title', 'Edit Artist')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold">Edit Artist: {{ $artist->artist_name }}</h2>
        </div>

        <form action="{{ route('admin.artists.update', $artist) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Artist Name</label>
                    <input type="text" name="artist_name" value="{{ old('artist_name', $artist->artist_name) }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Genre</label>
                    <select name="genre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @foreach(['Pop', 'Rock', 'Hip Hop', 'R&B', 'Jazz', 'Classical'] as $genre)
                            <option value="{{ $genre }}" {{ $artist->genre === $genre ? 'selected' : '' }}>
                                {{ $genre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Bio</label>
                <textarea name="bio" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('bio', $artist->bio) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Profile Image</label>
                    <input type="file" name="profile_image" class="mt-1 block w-full">
                    @if($artist->profile_image)
                        <img src="{{ Storage::url($artist->profile_image) }}" class="mt-2 h-32 w-32 object-cover rounded">
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Cover Image</label>
                    <input type="file" name="cover_image" class="mt-1 block w-full">
                    @if($artist->cover_image)
                        <img src="{{ Storage::url($artist->cover_image) }}" class="mt-2 h-32 w-full object-cover rounded">
                    @endif
                </div>
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_verified" value="1" {{ $artist->is_verified ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm">
                    <span class="ml-2">Verified Artist</span>
                </label>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.artists.index') }}" class="px-4 py-2 border rounded-md text-gray-700">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Update Artist
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
