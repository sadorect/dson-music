@props(['title', 'artists'])

@php
    $displayArtists = $artists->count() > 0 && $artists->count() < 8
        ? $artists->concat($artists)
        : $artists;
@endphp

<div class="w-full" x-data="{ ready: false }" x-init="setTimeout(() => ready = true, 120)">
    <div class="w-full">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl sm:text-3xl font-bold text-black">{{ $title }}</h2>
            <a href="{{ route('artists.index') }}" class="text-sm text-black/60 hover:text-orange-600 transition-colors">Show all →</a>
        </div>

        <p class="text-xs text-black/50 mb-3 sm:hidden">Swipe to explore artists</p>

        <div x-show="!ready" class="flex gap-3 overflow-x-auto pb-2 w-full">
            @for($i = 0; $i < 6; $i++)
                <div class="module-card w-[200px] p-3 rounded-md flex-shrink-0">
                    <div class="module-skeleton w-full aspect-square rounded-full mb-2"></div>
                    <div class="module-skeleton h-4 w-3/4 rounded mb-1"></div>
                    <div class="module-skeleton h-3 w-2/3 rounded"></div>
                </div>
            @endfor
        </div>

        <div x-show="ready" class="module-scroller marquee-rtl flex gap-3 overflow-x-auto scroll-smooth pb-2 w-full [&::-webkit-scrollbar]:hidden [&::-webkit-scrollbar-track]:hidden [&::-webkit-scrollbar-thumb]:hidden">
            @forelse($displayArtists as $artist)
                <x-artist
                    :title="$artist->artist_name"
                    :artist="($artist->tracks_count ?? 0) . ' tracks'"
                    :image="$artist->profile_image ? \Illuminate\Support\Facades\Storage::url($artist->profile_image) : asset('images/default-artist-image.jpg')"
                    :url="route('artists.show', $artist)" />
            @empty
                <p class="text-sm text-black/60">No artists available yet.</p>
            @endforelse
        </div>
    </div>

</div>
