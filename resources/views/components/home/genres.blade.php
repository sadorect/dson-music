@props(['genres', 'genreCounts'])

<div class="container mx-auto px-4">
    <h2 class="text-2xl md:text-3xl font-bold mb-6 text-black">Explore Genres</h2>
    
    @if($genres->isEmpty())
        <p class="text-gray-500">No genres available at the moment.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-8 gap-4">
            @foreach($genres as $genre)
                @if(!empty($genre))
                    <a href="#" class="block bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-full p-4 text-center hover:from-orange-600 hover:to-orange-700 transition-all">
                        <h3 class="font-bold text-black">{{ $genre }}</h3>
                        <p class="text-sm opacity-80 text-black">{{ $genreCounts[$genre] ?? 0 }} tracks</p>
                    </a>
                @endif
            @endforeach
        </div>
    @endif
</div>