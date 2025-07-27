@props(['trendingTracks'])

<div class="container mx-auto px-4">
    <h2 class="text-xl md:text-2xl font-bold mb-2 text-white">Play Again</h2>
    
    @if($trendingTracks->isEmpty())
        <p class="text-gray-500">No trending tracks available at the moment.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($trendingTracks as $track)
                <div class="bg-black/10 rounded-lg shadow-md overflow-hidden transition-transform hover:scale-105">
                    <div class="h-48 relative">
                        @if($track->cover_path)
                            <img src="{{ Storage::url($track->cover_path) }}" 
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
                    <div class="p-4 flex flex-col ">
                        <h3 class="font-bold text-lg text-white">{{ $track->title }}</h3>
                        <p class="text-sm text-gray-600 ">{{ $track->artist->name ?? 'Unknown Artist' }}</p>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">{{ $track->plays_count }} plays</span>
                            <button class="text-primary-color hover:text-primary/80">
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