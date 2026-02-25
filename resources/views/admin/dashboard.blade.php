@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-blue-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-500 bg-opacity-10">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <h2 class="text-gray-500 text-sm">Total Users</h2>
                <p class="text-2xl font-semibold">{{ number_format($stats['users_count']) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-red-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-500 bg-opacity-10">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
            <div class="ml-4">
                <h2 class="text-gray-500 text-sm">Total Tracks</h2>
                <p class="text-2xl font-semibold">{{ number_format($stats['tracks_count']) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-green-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-500 bg-opacity-10">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            <div class="ml-4">
                <h2 class="text-gray-500 text-sm">Total Artists</h2>
                <p class="text-2xl font-semibold">{{ number_format($stats['artists_count']) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-orange-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-orange-500 bg-opacity-10">
                <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <h2 class="text-gray-500 text-sm">Pending Reviews</h2>
                <p class="text-2xl font-semibold">{{ number_format($stats['pending_tracks_count'] ?? 0) }}</p>
                @if(($stats['pending_tracks_count'] ?? 0) > 0)
                    <a href="{{ route('admin.tracks.review.index') }}" class="text-xs text-orange-600 hover:underline">Review now &rarr;</a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex items-center justify-between">
            <h2 class="text-lg font-semibold">Recent Tracks</h2>
            <a href="{{ route('admin.tracks.index') }}" class="text-xs text-orange-600 hover:underline">View all</a>
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
        <div class="p-4 border-b flex items-center justify-between">
            <h2 class="text-lg font-semibold">Recent Users</h2>
            <a href="{{ route('admin.users.index') }}" class="text-xs text-orange-600 hover:underline">View all</a>
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

{{-- Pending Approvals Widget --}}
@if(($stats['pending_tracks_count'] ?? 0) > 0)
<div class="mt-6 bg-white rounded-lg shadow border-l-4 border-orange-500">
    <div class="p-4 border-b border-orange-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-full bg-orange-50">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-gray-900">Pending Track Approvals</h2>
                <p class="text-xs text-gray-500">{{ number_format($stats['pending_tracks_count']) }} {{ Str::plural('track', $stats['pending_tracks_count']) }} awaiting review</p>
            </div>
        </div>
        <a href="{{ route('admin.tracks.review.index') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors">
            Review All
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
    <div class="divide-y divide-gray-50">
        @foreach($stats['pending_tracks'] as $track)
            <div class="flex items-center px-4 py-3 hover:bg-orange-50/50 transition-colors">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $track->title }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $track->artist->artist_name ?? '—' }} &bull; {{ optional($track->created_at)->diffForHumans() }}</p>
                </div>
                <div class="ml-4 flex items-center gap-2 shrink-0">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                        {{ ucfirst($track->status ?? $track->approval_status ?? 'pending') }}
                    </span>
                    <a href="{{ route('admin.tracks.review.show', $track) }}"
                       class="text-xs text-orange-600 hover:text-orange-800 font-medium hover:underline">
                        Review &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    @if($stats['pending_tracks_count'] > 8)
        <div class="px-4 py-3 bg-orange-50/50 border-t border-orange-100 text-center">
            <a href="{{ route('admin.tracks.review.index') }}" class="text-sm text-orange-600 hover:underline font-medium">
                + {{ number_format($stats['pending_tracks_count'] - 8) }} more pending — view all &rarr;
            </a>
        </div>
    @endif
</div>
@endif
@endsection
