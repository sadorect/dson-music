@extends('layouts.artist')

@section('title', 'Edit Profile')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Artist Profile</h1>
            <p class="text-sm text-gray-500 mt-1">Keep your profile up to date so fans can find you.</p>
        </div>
        <a href="{{ route('artist.dashboard') }}" class="text-sm text-orange-600 hover:text-orange-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Dashboard
        </a>
    </div>

    <form action="{{ route('artist.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        {{-- ── Basic Info ────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-orange-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-orange-100 bg-orange-50/40">
                <h2 class="font-semibold text-gray-800">Basic Information</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="artist_name">Artist / Stage Name <span class="text-red-500">*</span></label>
                    <input type="text" id="artist_name" name="artist_name"
                           value="{{ old('artist_name', $artist->artist_name) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-400 focus:ring-orange-400 text-sm"
                           required maxlength="255">
                    @error('artist_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="genre">Primary Genre <span class="text-red-500">*</span></label>
                    <select id="genre" name="genre"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-400 focus:ring-orange-400 text-sm"
                            required>
                        @foreach(['pop','rock','hip-hop','r&b','jazz','gospel','classical','afrobeats','reggae','electronic','country','soul','blues','funk','latin'] as $g)
                            <option value="{{ $g }}" {{ old('genre', $artist->genre) === $g ? 'selected' : '' }}>{{ ucwords($g) }}</option>
                        @endforeach
                    </select>
                    @error('genre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="5"
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-400 focus:ring-orange-400 text-sm"
                              maxlength="2000" placeholder="Tell fans about yourself…">{{ old('bio', $artist->bio) }}</textarea>
                    @error('bio')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="custom_url">Custom Profile URL</label>
                    <div class="flex rounded-lg shadow-sm border border-gray-300 overflow-hidden">
                        <span class="inline-flex items-center px-3 bg-gray-50 text-gray-500 text-sm border-r border-gray-300 whitespace-nowrap">
                            /artists/
                        </span>
                        <input type="text" id="custom_url" name="custom_url"
                               value="{{ old('custom_url', $artist->custom_url) }}"
                               class="flex-1 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-orange-400"
                               placeholder="your-name" maxlength="100">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Lowercase letters, numbers and hyphens only.</p>
                    @error('custom_url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ── Images ────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-orange-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-orange-100 bg-orange-50/40">
                <h2 class="font-semibold text-gray-800">Images</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Profile Photo</label>
                    <div class="flex items-center gap-4 mb-3">
                        <img src="{{ $artist->profile_image ? Storage::url($artist->profile_image) : asset('images/default-profile.jpg') }}"
                             alt="Profile"
                             class="w-20 h-20 rounded-full object-cover border-2 border-orange-200 shadow-sm">
                        <div class="text-xs text-gray-500">
                            <p>Recommended: 400×400px</p>
                            <p>Max size: 3 MB</p>
                        </div>
                    </div>
                    <input type="file" name="profile_image" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                    @error('profile_image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Cover / Banner Image</label>
                    <div class="mb-3">
                        <img src="{{ $artist->cover_image ? Storage::url($artist->cover_image) : asset('images/default-cover.jpg') }}"
                             alt="Cover"
                             class="w-full h-24 rounded-lg object-cover border border-orange-200 shadow-sm">
                    </div>
                    <p class="text-xs text-gray-400 mb-2">Recommended: 1500×500px — Max 5 MB</p>
                    <input type="file" name="cover_image" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                    @error('cover_image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ── Social Links ──────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-orange-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-orange-100 bg-orange-50/40">
                <h2 class="font-semibold text-gray-800">Social Links</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach([
                    ['facebook',  'Facebook'],
                    ['twitter',   'Twitter / X'],
                    ['instagram', 'Instagram'],
                    ['tiktok',    'TikTok'],
                    ['youtube',   'YouTube'],
                ] as [$key, $label])
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                    <input type="url" name="social_{{ $key }}"
                           value="{{ old('social_' . $key, data_get($artist->social_links, $key)) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-400 focus:ring-orange-400 text-sm"
                           placeholder="https://…">
                    @error("social_$key")<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── Save ──────────────────────────────────────── --}}
        <div class="flex items-center justify-between bg-white rounded-xl shadow-sm border border-orange-100 px-6 py-4">
            <p class="text-xs text-gray-400">Changes are saved to your public profile immediately.</p>
            <div class="flex gap-3">
                <a href="{{ route('artist.dashboard') }}"
                   class="px-5 py-2 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 rounded-lg bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600 active:scale-95 transition-all shadow-sm">
                    Save Changes
                </button>
            </div>
        </div>

    </form>
</div>
@endsection
