@props(['title', 'artist', 'image'])

<div class="w-[200px] p-3 hover:bg-black/10 rounded-md cursor-pointer flex-shrink-0">
    <div class="w-full h-48 rounded-md overflow-hidden shadow-md mb-2">
        <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-cover">
    </div>
    <div class="text-white text-sm font-semibold truncate">{{ $title }}</div>
    <div class="text-gray-400 text-xs truncate">{{ $artist }}</div>
</div>
