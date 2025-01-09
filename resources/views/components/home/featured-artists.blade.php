<div class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8">Featured Artists</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @foreach($artists as $artist)
                <x-artist-card :artist="$artist" />
            @endforeach
        </div>
    </div>
</div>
