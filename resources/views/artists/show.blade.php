@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-lg">
        <!-- Artist Header -->
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <img src="{{ Storage::url($artist->profile_image) }}" class="w-32 h-32 rounded-full object-cover">
                    <div>
                        <h1 class="text-3xl font-bold">{{ $artist->artist_name }}</h1>
                        <p class="text-gray-600">{{ $artist->genre }}</p>
                        <p class="text-sm text-gray-500">{{ $artist->followers_count }} followers</p>
                    </div>
                </div>
                
                <!-- Follow Button -->
                @auth
                    @if(auth()->user()->id !== $artist->user_id)
                        <form action="{{ auth()->user()->isFollowing($artist) ? route('artists.unfollow', $artist) : route('artists.follow', $artist) }}" 
                              method="POST">
                            @csrf
                            @if(auth()->user()->isFollowing($artist))
                                @method('DELETE')
                            @endif
                            <button type="submit" 
                                    class="px-6 py-2 rounded-full {{ auth()->user()->isFollowing($artist) ? 'bg-gray-200 hover:bg-gray-300' : 'bg-blue-600 text-white hover:bg-blue-700' }}">
                                {{ auth()->user()->isFollowing($artist) ? 'Following' : 'Follow' }}
                            </button>
                        </form>
                    @endif
                @endauth
            </div>
        </div>

        <!-- Artist Bio -->
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold mb-2">About</h3>
            <p class="text-gray-600">{{ $artist->bio }}</p>
        </div>

        <!-- Latest Tracks -->
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Latest Tracks</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($artist->tracks()->latest()->take(6)->get() as $track)
                    @include('tracks.card', ['track' => $track])
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
