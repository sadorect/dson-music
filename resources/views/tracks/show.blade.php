@extends('layouts.app')

@section('content')
@php
    $libraryPlaylists = auth()->check()
        ? auth()->user()->playlists()->select('id', 'name')->latest()->take(20)->get()
        : collect();
@endphp
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Track Main Info -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="{{ $track->cover_art ? Storage::disk('s3')->url($track->cover_art) : asset('images/default-track-cover.jpg') }}" 
                         class="w-full h-96 object-cover" 
                         alt="{{ $track->title }}">
                    
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h1 class="text-3xl font-bold">{{ $track->title }}</h1>
                            <span class="text-gray-500">{{ $track->release_date->format('M d, Y') }}</span>
                        </div>

                        <div class="flex items-center space-x-4 mb-6">
                            <a href="{{ route('artists.show', $track->artist) }}" 
                               class="flex items-center space-x-2 text-gray-600 hover:text-red-600">
                                <img src="{{ $track->artist->profile_image ? Storage::disk('s3')->url($track->artist->profile_image) : asset('images/default-artist-image.jpg') }}" 
                                     class="w-10 h-10 rounded-full object-cover">
                                <span class="font-medium">{{ $track->artist->artist_name }}</span>
                            </a>
                            <span class="text-gray-400">•</span>
                            <span class="text-gray-600">{{ $track->genre }}</span>
                        </div>

                        <div class="flex items-center space-x-4">
                            <button x-data
                            @click="
                                console.log('Track data:', {
                                    audioUrl: '{{ route('tracks.stream', $track) }}'
                                });
                                $dispatch('track:play', {
                                    id: {{ $track->id }},
                                    title: '{{ $track->title }}',
                                    artist: '{{ $track->artist->artist_name }}',
                                    artwork: '{{ $track->cover_art ? Storage::disk('s3')->url($track->cover_art) : '/default-cover.jpg' }}',
                                    audioUrl: '{{ route('tracks.stream', $track) }}',
                                    format: '{{ $track->file_path ? pathinfo($track->file_path, PATHINFO_EXTENSION) : 'mp3' }}'
                                })"
                                    class="dson-btn flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>
                                </svg>
                                <span>Play Track</span>
                            </button>
                            
                            @auth
                            <button 
                            x-data="{ liked: {{ auth()->check() && auth()->user()->likes()->where('likeable_id', $track->id)->exists() ? 'true' : 'false' }} }"
                            @click=" @auth
                                fetch('{{ route('tracks.like', $track) }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    liked = !liked;
                                    $refs.likeCount.textContent = data.likes_count;
                                })
                                @else
                                window.location.href = '{{ route('login') }}'
                            @endauth
                            "
                            class="flex items-center space-x-1 text-gray-600 hover:text-red-600 transition-colors"
                        >
                            <svg 
                                :class="{ 'text-red-600 fill-current': liked }"
                                class="w-6 h-6" 
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span x-ref="likeCount">{{ $track->likes()->count() }}</span>
                        </button>

                            @php
                                $listenerPlaylists = auth()->user()->playlists()->latest()->get();
                            @endphp

                            @if($listenerPlaylists->isNotEmpty())
                                <form action="{{ route('playlists.add-track', $listenerPlaylists->first()) }}" method="POST" class="flex items-center space-x-2">
                                    @csrf
                                    <input type="hidden" name="track_id" value="{{ $track->id }}">
                                    <select
                                        name="playlist_id"
                                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                        onchange="this.form.action = '{{ url('/playlists') }}/' + this.value + '/tracks'"
                                    >
                                        @foreach($listenerPlaylists as $playlist)
                                            <option value="{{ $playlist->id }}">{{ $playlist->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-3 py-2 bg-gray-900 text-white rounded-lg text-sm hover:bg-black">
                                        Add to Playlist
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('playlists.create') }}" class="px-3 py-2 bg-gray-900 text-white rounded-lg text-sm hover:bg-black">Create Playlist</a>
                            @endif
                            @endauth
                        </div>

                        <div class="mt-6">
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span>{{ number_format($track->play_count) }} plays</span>
                                <span>•</span>
                                <span>{{ $track->likes()->count() }} likes</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                @include('components.comments.section', [
                    'type' => 'track',
                    'model' => $track
                ])
                
            </div>

            <!-- Sidebar -->
            <div class="md:col-span-1">
                <!-- Related Tracks -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">More from this genre</h3>
                    <div class="space-y-4">
                        @foreach($relatedTracks as $relatedTrack)
                            <div class="group relative">
                                <a href="{{ route('tracks.show', $relatedTrack) }}" class="flex items-center space-x-3 hover:bg-gray-50 p-2 rounded-lg">
                                    <img src="{{ $relatedTrack->cover_art ? Storage::disk('s3')->url($relatedTrack->cover_art) : asset('images/default-track-cover.jpg') }}" 
                                         class="w-12 h-12 rounded object-cover">
                                    <div>
                                        <h4 class="font-medium text-sm">{{ $relatedTrack->title }}</h4>
                                        <p class="text-gray-500 text-xs">{{ $relatedTrack->artist->artist_name }}</p>
                                    </div>
                                </a>
                                @auth
                                    <x-track-floating-controls :track="$relatedTrack" :playlists="$libraryPlaylists" />
                                @endauth
                            </div>
                        @endforeach
                    </div>
                </div>
            <hr class="py-5">
                <!-- More from this Artist -->
               
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
  <h3 class="text-lg font-semibold mb-4">More from {{ $track->artist->artist_name }}</h3>
  <div class="space-y-4">
      @foreach($track->artist->tracks()
          ->where('id', '!=', $track->id)
          ->latest()
          ->take(4)
          ->get() as $artistTrack)
          <div class="group relative">
              <a href="{{ route('tracks.show', $artistTrack) }}" class="flex items-center space-x-3 hover:bg-gray-50 p-2 rounded-lg">
                  <img src="{{ $artistTrack->cover_art ? Storage::disk('s3')->url($artistTrack->cover_art) : asset('images/default-track-cover.jpg') }}" 
                       class="w-12 h-12 rounded object-cover">
                  <div>
                      <h4 class="font-medium text-sm">{{ $artistTrack->title }}</h4>
                      <p class="text-gray-500 text-xs">{{ $artistTrack->play_count }} plays</p>
                  </div>
              </a>
              @auth
                  <x-track-floating-controls :track="$artistTrack" :playlists="$libraryPlaylists" />
              @endauth
          </div>
      @endforeach
  </div>
</div>

            </div>
        </div>
    </div>
</div>
@endsection