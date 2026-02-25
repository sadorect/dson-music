@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12 max-w-2xl">

    <div class="bg-white rounded-2xl shadow-lg border border-orange-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-orange-100 bg-gradient-to-r from-[#140b05] to-[#2b1306]">
            <h1 class="text-2xl font-bold text-orange-50">Become an Artist</h1>
            <p class="text-orange-300/70 text-sm mt-1">Set up your artist profile and start sharing your music.</p>
        </div>

        <form method="POST" action="{{ route('artist.register') }}" enctype="multipart/form-data" class="p-8 space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="artist_name">
                    Artist / Stage Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="artist_name" name="artist_name"
                       value="{{ old('artist_name') }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-400 focus:ring-orange-400 text-sm"
                       required maxlength="255"
                       placeholder="Your artist name…">
                @error('artist_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="genre">
                    Primary Genre <span class="text-red-500">*</span>
                </label>
                <select id="genre" name="genre"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-400 focus:ring-orange-400 text-sm"
                        required>
                    <option value="" disabled {{ old('genre') ? '' : 'selected' }}>Select a genre…</option>
                    @foreach(['pop','rock','hip-hop','r&b','jazz','gospel','classical','afrobeats','reggae','electronic','country','soul','blues','funk','latin'] as $g)
                        <option value="{{ $g }}" {{ old('genre') === $g ? 'selected' : '' }}>{{ ucwords($g) }}</option>
                    @endforeach
                </select>
                @error('genre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="4"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-400 focus:ring-orange-400 text-sm"
                          maxlength="2000"
                          placeholder="Tell fans a bit about yourself…">{{ old('bio') }}</textarea>
                @error('bio')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profile Photo</label>
                    <p class="text-xs text-gray-400 mb-2">Recommended 400×400px, max 2 MB</p>
                    <input type="file" name="profile_image" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                    @error('profile_image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cover / Banner Image</label>
                    <p class="text-xs text-gray-400 mb-2">Recommended 1500×500px, max 2 MB</p>
                    <input type="file" name="cover_image" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                    @error('cover_image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3 px-6 rounded-xl bg-orange-500 text-white font-semibold hover:bg-orange-600 active:scale-[0.98] transition-all shadow-md">
                Create Artist Profile
            </button>
        </form>
    </div>
</div>
@endsection
