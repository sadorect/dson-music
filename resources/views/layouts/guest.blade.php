<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.seo-head', ['pageTitle' => $title ?? null])
    @if($siteSettings?->favicon_url)
        <link rel="icon" href="{{ $siteSettings->favicon_url }}">
        <link rel="shortcut icon" href="{{ $siteSettings->favicon_url }}">
        <link rel="apple-touch-icon" href="{{ $siteSettings->favicon_url }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700&family=inter:300,400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
    @stack('styles')
</head>
<body class="glass-gradient-light min-h-screen antialiased">

    {{-- Decorative background blobs --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-primary-200/30 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-primary-100/40 rounded-full blur-3xl"></div>
    </div>

    <div class="relative min-h-screen flex flex-col items-center justify-center px-4 py-12">

        {{-- Logo --}}
        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2 mb-8 group">
            <x-application-logo class="h-8 w-auto object-contain transition-transform group-hover:scale-110" />
            <span class="text-2xl font-bold text-gray-800 tracking-tight">{{ $siteName }}</span>
        </a>

        {{-- Card --}}
        <div class="glass-panel w-full max-w-md rounded-2xl shadow-glass px-8 py-8">
            {{ $slot }}
        </div>

        {{-- Footer link --}}
        <p class="mt-6 text-xs text-gray-400">
            &copy; {{ date('Y') }} {{ $siteName }} &middot; <a href="{{ route('home') }}" wire:navigate class="hover:text-primary-400 transition">Back to home</a>
        </p>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
