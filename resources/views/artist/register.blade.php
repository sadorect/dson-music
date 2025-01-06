@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Become an Artist</h1>
        
        <form method="POST" action="{{ route('artist.register') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div>
                <label class="block text-sm font-medium mb-2">Artist Name</label>
                <input type="text" name="artist_name" class="w-full rounded-lg" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Genre</label>
                <select name="genre" class="w-full rounded-lg" required>
                    <option value="pop">Pop</option>
                    <option value="rock">Rock</option>
                    <option value="hip-hop">Hip Hop</option>
                    <option value="jazz">Jazz</option>
                    <option value="classical">Classical</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Bio</label>
                <textarea name="bio" rows="4" class="w-full rounded-lg"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Profile Image</label>
                <input type="file" name="profile_image" accept="image/*">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Cover Image</label>
                <input type="file" name="cover_image" accept="image/*">
            </div>

            <button type="submit" class="dson-btn w-full">Register as Artist</button>
        </form>
    </div>
</div>
@endsection
