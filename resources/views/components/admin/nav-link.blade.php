@props(['active'])

@php
$classes = ($active ?? false)
    ? 'flex items-center px-6 py-3 text-white bg-gray-800'
    : 'flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
