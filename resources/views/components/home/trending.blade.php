@props(['title', 'tracks'])

<div class="w-full">
    <div class="w-full">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-2xl font-bold text-white">{{ $title }}</h2>
            <a href="#" class="text-sm text-gray-400 hover:text-white">Show all</a>
        </div>

        <div class="flex gap-2 overflow-x-scroll scroll-smooth w-full [&::-webkit-scrollbar]:hidden [&::-webkit-scrollbar-track]:hidden [&::-webkit-scrollbar-thumb]:hidden">
            
                <x-song-card 
                    title="Song Title" 
                    artist="Artist name" 
                    image="#" />
                
                    <x-song-card 
                    title="Song Title" 
                    artist="Artist name" 
                    image="#" />

                    <x-song-card 
                    title="Song Title" 
                    artist="Artist name" 
                    image="#" />

                    <x-song-card 
                    title="Song Title" 
                    artist="Artist name" 
                    image="#" />


                    <x-song-card 
                    title="Song Title" 
                    artist="Artist name" 
                    image="#" />


                    <x-song-card 
                    title="Song Title" 
                    artist="Artist name" 
                    image="#" />


                    <x-song-card 
                    title="Song Title" 
                    artist="Artist name" 
                    image="#" />

                    <x-song-card 
                    title="Song Title" 
                    artist="Artist name" 
                    image="#" />
        
        </div>
    </div>
</div>
