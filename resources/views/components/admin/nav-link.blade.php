@props(['active'])

@php
$classes = ($active ?? false)
    ? 'flex items-center px-6 py-3 text-orange-100 bg-orange-900/50 border-r-2 border-orange-500 font-medium'
    : 'flex items-center px-6 py-3 text-orange-200/80 hover:bg-white/10 hover:text-orange-50 transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
