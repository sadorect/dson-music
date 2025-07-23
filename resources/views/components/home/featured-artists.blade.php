@props(['artists'])

<div class="container mx-auto px-4">
    <h2 class="text-xl md:text-2xl font-bold mb-2 text-white">Featured Artists</h2>
    
    @if($artists->isEmpty())
        <p class="text-gray-500">No featured artists available at the moment.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($artists as $artist)
                <div class="bg-black/10 rounded-lg shadow-md overflow-hidden transition-transform hover:scale-105">
                    <div class="h-48 relative">
                        @if($artist->profile_photo_path)
                            <img src="{{ Storage::url($artist->profile_photo_path) }}" 
                                 alt="{{ $artist->name }}" 
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none';
                                        this.parentElement.style.background='linear-gradient(to bottom right, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }})';
                                        this.parentElement.style.backgroundImage='linear-gradient(to bottom right, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}), url(https://source.unsplash.com/1600x900/?{{ urlencode($artist->name) }})'">
                        @else
                            <div class="w-full h-full bg-gradient-to-br" 
                                 style="background-image: linear-gradient(to bottom right, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}), url(https://source.unsplash.com/1600x900/?{{ urlencode($artist->name) }})">
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-lg text-white">{{ $artist->name }}</h3>
                        <div class="text-sm text-gray-600">
                            <span>{{ $artist->tracks_count }} Tracks</span> • 
                            <span>{{ $artist->followers_count }} Followers</span>
                        </div>
                        <a href="#" class="text-primary-color hover:text-primary/80 text-sm font-medium">View Profile</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>