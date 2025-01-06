@props(['active'])

@php
$classes = ($active ?? false)
    ? 'nav-link inline-flex items-center px-1 pt-1 text-white font-medium leading-5 focus:outline-none transition duration-150 ease-in-out active'
    : 'nav-link inline-flex items-center px-1 pt-1 text-gray-300 hover:text-white font-medium leading-5 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
