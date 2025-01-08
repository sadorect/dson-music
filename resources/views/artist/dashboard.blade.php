@extends('layouts.artist')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Artist Dashboard</h1>
            <span class="px-4 py-2 bg-gradient-to-r from-purple-500 to-blue-500 text-white rounded-full">
                {{ $artist->is_verified ? '✓ Verified Artist' : 'Pending Verification' }}
            </span>
        </div>
        
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl shadow-lg text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-80">Total Plays</p>
                        <h3 class="text-3xl font-bold">{{ number_format($artist->tracks->sum('play_count')) }}</h3>
                    </div>
                    <svg class="w-12 h-12 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl shadow-lg text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-80">Followers</p>
                        <h3 class="text-3xl font-bold">{{ number_format($artist->followers()->count()) }}</h3>
                    </div>
                    <svg class="w-12 h-12 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-br from-pink-500 to-pink-600 p-6 rounded-xl shadow-lg text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-80">Total Likes</p>
                        <h3 class="text-3xl font-bold">{{ number_format($artist->tracks->sum('likes_count')) }}</h3>
                    </div>
                    <svg class="w-12 h-12 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl shadow-lg text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-80">Downloads</p>
                        <h3 class="text-3xl font-bold">{{ number_format($artist->tracks->sum('downloads_count')) }}</h3>
                    </div>
                    <svg class="w-12 h-12 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Recent Activity & Popular Tracks -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-xl font-semibold">Popular Tracks</h3>
                </div>
                <div class="p-6">
                    @foreach($artist->tracks()->withCount(['plays', 'likes'])->orderBy('plays_count', 'desc')->take(5)->get() as $track)
                    <div class="flex items-center justify-between mb-4 last:mb-0">
                        <div class="flex items-center space-x-3">
                            <img src="{{ Storage::url($track->cover_art) }}" class="w-12 h-12 rounded object-cover">
                            <div>
                                <h4 class="font-medium">{{ $track->title }}</h4>
                                <p class="text-sm text-gray-500">{{ number_format($track->plays_count) }} plays</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 text-gray-500">
                            <span>{{ number_format($track->likes_count) }}</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                            </svg>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-xl font-semibold">Recent Comments</h3>
                </div>
                <div class="p-6">
                    @foreach($artist->tracks()->with(['comments' => function($query) {
                        $query->latest()->take(5)->with('user');
                    }])->get()->pluck('comments')->flatten() as $comment)
                    <div class="mb-4 last:mb-0">
                        <div class="flex items-start space-x-3">
                            <img src="{{ $comment->user->profile_photo_url }}" class="w-8 h-8 rounded-full">
                            <div>
                                <p class="font-medium">{{ $comment->user->name }}</p>
                                <p class="text-gray-600">{{ $comment->content }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ $comment->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
