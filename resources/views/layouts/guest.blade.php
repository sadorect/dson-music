<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'GrinMusic') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700&family=inter:300,400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="glass-gradient-light min-h-screen antialiased">

    {{-- Decorative background blobs --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-red-200/30 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-rose-100/40 rounded-full blur-3xl"></div>
    </div>

    <div class="relative min-h-screen flex flex-col items-center justify-center px-4 py-12">

        {{-- Logo --}}
        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2 mb-8 group">
            <svg class="w-8 h-8 text-red-500 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9 3v10.55A4 4 0 107 17V7h8V3H9z"/>
            </svg>
            <span class="text-2xl font-bold text-gray-800 tracking-tight">GrinMusic</span>
        </a>

        {{-- Card --}}
        <div class="glass-panel w-full max-w-md rounded-2xl shadow-glass px-8 py-8">
            {{ $slot }}
        </div>

        {{-- Footer link --}}
        <p class="mt-6 text-xs text-gray-400">
            &copy; {{ date('Y') }} GrinMusic &mdash; <a href="{{ route('home') }}" wire:navigate class="hover:text-red-400 transition">Back to home</a>
        </p>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
