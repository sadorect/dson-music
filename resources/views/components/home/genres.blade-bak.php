<div class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8">Explore Genres</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach($genres as $genre)
                <div class="relative group overflow-hidden rounded-lg h-48">
                    <img src="{{ asset('images/genres/' . strtolower($genre) . '.jpg') }}" 
                         class="w-full h-full object-cover transform transition group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent">
                        <div class="absolute bottom-4 left-4">
                            <h3 class="text-white text-xl font-bold">{{ $genre }}</h3>
                            <p class="text-gray-200 text-sm">{{ $genreCounts[$genre] ?? 0 }} tracks</p>
                        </div>
                    </div>
                    <a href="" 
                       class="absolute inset-0 z-10">
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
