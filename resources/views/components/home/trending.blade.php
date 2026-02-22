@props(['title', 'tracks'])

@php
    $libraryPlaylists = auth()->check()
        ? auth()->user()->playlists()->select('id', 'name')->latest()->take(20)->get()
        : collect();
@endphp

<div class="w-full" x-data="{ ready: false }" x-init="setTimeout(() => ready = true, 120)">
    <div class="w-full">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl sm:text-3xl font-bold text-black">{{ $title }}</h2>
            <a href="{{ route('trending') }}" class="text-sm text-black/60 hover:text-orange-600 transition-colors">Show all →</a>
        </div>

        <p class="text-xs text-black/50 mb-3 sm:hidden">Swipe to explore tracks</p>

        <div x-show="!ready" class="flex gap-3 overflow-x-auto pb-2 w-full">
            @for($i = 0; $i < 6; $i++)
                <div class="module-card w-[155px] sm:w-[180px] md:w-[200px] p-3 rounded-md flex-shrink-0">
                    <div class="module-skeleton w-full aspect-square rounded-xl mb-2"></div>
                    <div class="module-skeleton h-4 w-4/5 rounded mb-1"></div>
                    <div class="module-skeleton h-3 w-2/3 rounded"></div>
                </div>
            @endfor
        </div>

        <div x-show="ready" class="module-scroller marquee-ltr flex gap-3 overflow-x-auto scroll-smooth pb-2 w-full [&::-webkit-scrollbar]:hidden [&::-webkit-scrollbar-track]:hidden [&::-webkit-scrollbar-thumb]:hidden">
            @forelse($tracks as $track)
                <div class="module-card group relative w-[155px] sm:w-[180px] md:w-[200px] p-3 bg-white border border-black/10 hover:border-orange-400 hover:bg-orange-50/40 rounded-md flex-shrink-0">
                    @auth
                        <x-track-floating-controls :track="$track" :playlists="$libraryPlaylists" />
                    @endauth
                    <button
                        class="w-full text-left"
                        x-data
                        @click="$dispatch('track:play', {
                            id: {{ $track->id }},
                            title: @js($track->title),
                            artist: @js($track->artist->artist_name ?? $track->artist?->user?->name ?? 'Unknown Artist'),
                            artwork: @js($track->cover_art ? \Illuminate\Support\Facades\Storage::disk('s3')->url($track->cover_art) : asset('images/default-track-cover.jpg')),
                            audioUrl: @js(route('tracks.stream', $track))
                        })"
                    >
                        <div class="w-full aspect-square rounded-xl overflow-hidden shadow-md mb-2">
                            <img
                                src="{{ $track->cover_art ? \Illuminate\Support\Facades\Storage::disk('s3')->url($track->cover_art) : asset('images/default-track-cover.jpg') }}"
                                alt="{{ $track->title }}"
                                class="h-full w-full object-cover"
                            >
                        </div>
                        <div class="text-black text-sm font-semibold truncate">{{ $track->title }}</div>
                        <div class="text-black/60 text-xs truncate">{{ $track->artist->artist_name ?? $track->artist?->user?->name ?? 'Unknown Artist' }}</div>
                    </button>
                </div>
            @empty
                <p class="text-sm text-black/60">No trending tracks available yet.</p>
            @endforelse
        </div>
    </div>
</div>
