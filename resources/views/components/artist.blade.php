@props(['title', 'artist', 'image', 'url' => null])

@php
    $defaultSlug = \Illuminate\Support\Str::slug($title);
    $artistUrl = $url ?? route('artists.showPublic', ['slug' => $defaultSlug]);
@endphp

<a href="{{ $artistUrl }}" class="w-[200px] p-3 bg-white border border-black/10 hover:border-orange-400 hover:bg-orange-50/40 rounded-md cursor-pointer flex-shrink-0 transition-colors">
    <div class="w-full h-48 rounded-full overflow-hidden shadow-md mb-2">
        <img src="{{ $image }}" 
             alt="{{ $title }}" 
             onerror="this.style.display='none';
                    this.parentElement.style.background='linear-gradient(to bottom right, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }})';
                    this.parentElement.style.backgroundImage='linear-gradient(to bottom right, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}, #{{ str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) }}), url(https://source.unsplash.com/1600x900/?{{ urlencode($title) }},{{ urlencode($artist) }})'">
    </div>
    <div class="text-black text-sm font-semibold truncate">{{ $title }}</div>
    <div class="text-black/60 text-xs truncate">{{ $artist }}</div>
</a>
