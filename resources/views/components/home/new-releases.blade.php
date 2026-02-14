@props(['tracks'])

@php
    $libraryPlaylists = auth()->check()
        ? auth()->user()->playlists()->select('id', 'name')->latest()->take(20)->get()
        : collect();
@endphp

<div class="container mx-auto px-4" x-data="{ ready: false }" x-init="setTimeout(() => ready = true, 120)">
    <h2 class="text-2xl md:text-3xl font-bold mb-6 text-white">New Releases</h2>

    <div x-show="!ready" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 mb-4">
        @for($i = 0; $i < 8; $i++)
            <div class="rounded-lg p-3 bg-black/10">
                <div class="module-skeleton h-40 sm:h-48 rounded mb-3"></div>
                <div class="module-skeleton h-4 w-4/5 rounded mb-2"></div>
                <div class="module-skeleton h-3 w-2/3 rounded"></div>
            </div>
        @endfor
    </div>
    
    @if($tracks->isEmpty())
        <p class="text-gray-500">No new releases available at the moment.</p>
    @else
        <div x-show="ready" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            @foreach($tracks as $track)
                <div class="group relative bg-black/10 rounded-lg shadow-md overflow-hidden transition-transform hover:scale-105">
                    @auth
                        <x-track-floating-controls :track="$track" :playlists="$libraryPlaylists" />
                    @endauth
                    <div class="h-48">
                        @if($track->cover_art)
                            <img src="{{ Storage::disk('s3')->url($track->cover_art) }}" 
                                 alt="{{ $track->title }}" 
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none';
                                        this.parentElement.style.background='linear-gradient(to bottom right, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }})';
                                        this.parentElement.style.backgroundImage='linear-gradient(to bottom right, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}), url(https://source.unsplash.com/1600x900/?{{ urlencode($track->title) }})'">
                        @else
                            <div class="w-full h-full bg-gradient-to-br" 
                                 style="background-image: linear-gradient(to bottom right, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}), url(https://source.unsplash.com/1600x900/?{{ urlencode($track->title) }})">
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-lg  text-white">{{ $track->title }}</h3>
                        <p class="text-sm text-gray-600 ">{{ $track->artist->artist_name ?? $track->artist?->user?->name ?? 'Unknown Artist' }}</p>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">{{ $track->created_at->diffForHumans() }}</span>
                            <button
                                x-data
                                @click="$dispatch('track:play', {
                                    id: {{ $track->id }},
                                    title: @js($track->title),
                                    artist: @js($track->artist->artist_name ?? $track->artist?->user?->name ?? 'Unknown Artist'),
                                    artwork: @js($track->cover_art ? Storage::disk('s3')->url($track->cover_art) : asset('images/default-track-cover.jpg')),
                                    audioUrl: @js(route('tracks.stream', $track))
                                })"
                                class="text-primary-color hover:text-primary-color/80"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>