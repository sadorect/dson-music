@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-500 bg-opacity-10">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <h2 class="text-gray-600 text-sm">Total Users</h2>
                <p class="text-2xl font-semibold">{{ number_format($stats['users_count']) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-500 bg-opacity-10">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
            <div class="ml-4">
                <h2 class="text-gray-600 text-sm">Total Tracks</h2>
                <p class="text-2xl font-semibold">{{ number_format($stats['tracks_count']) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-500 bg-opacity-10">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            <div class="ml-4">
                <h2 class="text-gray-600 text-sm">Total Artists</h2>
                <p class="text-2xl font-semibold">{{ number_format($stats['artists_count']) }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Recent Tracks</h2>
        </div>
        <div class="p-4">
            <div class="space-y-4">
                @foreach($stats['recent_tracks'] as $track)
                <div class="flex items-center">
                    <img src="{{ $track->cover_art ? Storage::url($track->cover_art) : '/default-cover.jpg' }}" class="w-12 h-12 rounded object-cover">
                    <div class="ml-4">
                        <h3 class="font-medium">{{ $track->title }}</h3>
                        <p class="text-sm text-gray-500">{{ $track->artist->artist_name }}</p>
                    </div>
                    <div class="ml-auto text-sm text-gray-500">
                        {{ $track->created_at->diffForHumans() }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Recent Users</h2>
        </div>
        <div class="p-4">
            <div class="space-y-4">
                @foreach($stats['recent_users'] as $user)
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                        <span class="text-xl font-medium text-gray-600">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-medium">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                    <div class="ml-auto text-sm text-gray-500">
                        {{ $user->created_at->diffForHumans() }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
