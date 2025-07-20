<!-- Image Cell Component -->
<div class="relative aspect-square w-16 h-16 rounded-lg overflow-hidden">
    @if($image)
        <img src="{{ $image }}" alt="{{ $alt ?? '' }}" class="w-full h-full object-cover">
    @else
        <div class="w-full h-full" style="background: linear-gradient({{ $gradient }})"></div>
    @endif
</div>

@props(['image', 'alt' => '', 'gradient' => 'to-br, #' . str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT) . ', #' . str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT)])
