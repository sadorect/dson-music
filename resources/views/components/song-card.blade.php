@props(['title', 'artist', 'image'])

<div class="w-[200px] p-3 hover:bg-black/10 rounded-md cursor-pointer flex-shrink-0">
    <div class="w-full h-48 rounded-xl overflow-hidden shadow-md mb-2">
        <img src="{{ $image }}" 
             alt="{{ $title }}" 
             onerror="this.style.display='none';
                    this.parentElement.style.background='linear-gradient(to bottom right, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }})';
                    this.parentElement.style.backgroundImage='linear-gradient(to bottom right, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}), url(https://source.unsplash.com/1600x900/?{{ urlencode($title) }},{{ urlencode($artist) }})'">
    </div>
    <div class="text-white text-sm font-semibold truncate">{{ $title }}</div>
    <div class="text-gray-400 text-xs truncate">{{ $artist }}</div>
</div>
