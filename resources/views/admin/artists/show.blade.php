@extends('layouts.admin')

@section('title', $artist->artist_name)

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold">Artist Details</h2>
            <div class="flex space-x-4">
                <a href="{{ route('admin.artists.edit', $artist) }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Edit Artist</a>
                <a href="{{ route('admin.analytics.artist', $artist) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">View Analytics</a>
            </div>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-6">
                <div class="flex items-center space-x-4">
                    <img src="{{ $artist->profile_image ? Storage::disk('s3')->url($artist->profile_image) : asset('images/default-profile.jpg') }}" alt="{{ $artist->artist_name }}" class="w-32 h-32 rounded-full object-cover">
                    <div>
                        <h3 class="text-xl font-semibold">{{ $artist->artist_name }}</h3>
                        <p class="text-gray-600">{{ $artist->genre }}</p>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $artist->is_verified ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $artist->is_verified ? 'Verified' : 'Unverified' }}
                        </span>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <h4 class="font-semibold mb-2">Bio</h4>
                    <p class="text-gray-600">{{ $artist->bio ?? 'No bio available' }}</p>
                </div>

                <div class="border-t pt-4">
                    <h4 class="font-semibold mb-2">Contact Information</h4>
                    <p class="text-gray-600">Email: {{ $artist->user->email }}</p>
                    <p class="text-gray-600">Joined: {{ $artist->created_at->format('M d, Y') }}</p>
                </div>
            </div>

            <div class="space-y-6">
                <div class="border-t md:border-t-0 pt-4 md:pt-0">
                    <h4 class="font-semibold mb-4">Latest Tracks</h4>
                    <div class="space-y-3">
                        @forelse($artist->tracks()->latest()->take(5)->get() as $track)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                <div>
                                    <p class="font-medium">{{ $track->title }}</p>
                                    <p class="text-sm text-gray-600">{{ $track->plays_count ?? 0 }} plays</p>
                                </div>
                                <span class="text-sm text-gray-500">{{ $track->created_at->format('M d, Y') }}</span>
                            </div>
                        @empty
                            <p class="text-gray-500">No tracks available</p>
                        @endforelse
                    </div>
                </div>

                <div class="border-t pt-4">
                    <h4 class="font-semibold mb-4">Statistics</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded">
                            <p class="text-sm text-gray-600">Total Tracks</p>
                            <p class="text-2xl font-semibold">{{ $artist->tracks_count }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded">
                            <p class="text-sm text-gray-600">Total Plays</p>
                            <p class="text-2xl font-semibold">{{ $artist->tracks->sum('plays_count') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
