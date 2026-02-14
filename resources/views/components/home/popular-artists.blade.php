@props(['title', 'artists'])

<div class="w-full" x-data="{ ready: false }" x-init="setTimeout(() => ready = true, 120)">
        <div class="w-full">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-2xl font-bold text-white">{{$title}}</h2>
                <a href="{{ route('artists.index') }}" class="text-sm text-gray-400 hover:text-white">Show all</a>
            </div>

            <p class="text-xs text-white/50 mb-2 sm:hidden">Swipe to explore artists</p>

            <div x-show="!ready" class="flex gap-3 overflow-x-auto pb-2 w-full">
                @for($i = 0; $i < 6; $i++)
                    <div class="module-card w-[200px] p-3 rounded-md flex-shrink-0">
                        <div class="module-skeleton w-full aspect-square rounded-full mb-2"></div>
                        <div class="module-skeleton h-4 w-3/4 rounded mb-1"></div>
                        <div class="module-skeleton h-3 w-2/3 rounded"></div>
                    </div>
                @endfor
            </div>

            <div x-show="ready" class="module-scroller flex gap-3 overflow-x-auto scroll-smooth pb-2 w-full [&::-webkit-scrollbar]:hidden [&::-webkit-scrollbar-track]:hidden [&::-webkit-scrollbar-thumb]:hidden">
                @forelse($artists as $artist)
                    <div class="module-card">
                        <x-artist 
                            :title="$artist->artist_name"
                            :artist="($artist->tracks_count ?? 0) . ' tracks'"
                            :image="$artist->profile_image ? \Illuminate\Support\Facades\Storage::url($artist->profile_image) : asset('images/default-artist-image.jpg')"
                            :url="route('artists.show', $artist)" />
                    </div>
                @empty
                    <p class="text-sm text-white/60">No artists available yet.</p>
                @endforelse
            </div>
        </div>

</div>
