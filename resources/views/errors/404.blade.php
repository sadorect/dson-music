<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Not Found — GrinMusic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased" style="background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 30%, #ffe4e6 60%, #fef3c7 100%);">

    {{-- Decorative blobs --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute -top-40 -right-32 w-[500px] h-[500px] rounded-full opacity-20"
             style="background: radial-gradient(circle, #f472b6 0%, transparent 70%);"></div>
        <div class="absolute -bottom-32 -left-24 w-[400px] h-[400px] rounded-full opacity-20"
             style="background: radial-gradient(circle, #fb7185 0%, transparent 70%);"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full opacity-10"
             style="background: radial-gradient(circle, #fbbf24 0%, transparent 60%);"></div>
    </div>

    <div class="relative min-h-screen flex flex-col items-center justify-center px-4 text-center">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="mb-10 flex items-center gap-2 group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-lg group-hover:scale-105 transition">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/>
                </svg>
            </div>
            <span class="text-xl font-bold text-gray-800 tracking-tight">GrinMusic</span>
        </a>

        {{-- Glass card --}}
        <div class="glass-panel rounded-3xl p-10 max-w-md w-full shadow-xl">

            {{-- Animated note --}}
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-red-100 to-rose-200 flex items-center justify-center">
                        <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
                        </svg>
                    </div>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold w-7 h-7 rounded-full flex items-center justify-center shadow">
                        404
                    </span>
                </div>
            </div>

            <h1 class="text-3xl font-bold text-gray-800 mb-2">Track not found</h1>
            <p class="text-gray-500 text-sm leading-relaxed mb-8">
                This page must have been remixed out of existence. Let's get you back to the music.
            </p>

            {{-- Actions --}}
            <div class="space-y-3">
                <a href="{{ route('home') }}"
                   class="glass-btn-primary glass-btn-primary-hover flex items-center justify-center gap-2 w-full px-6 py-3 rounded-2xl text-sm font-semibold transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    Go home
                </a>

                <a href="{{ route('browse') }}"
                   class="flex items-center justify-center gap-2 w-full px-6 py-3 rounded-2xl text-sm font-semibold text-gray-600 bg-white/60 hover:bg-white/80 border border-white/50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Browse music
                </a>
            </div>
        </div>

        <p class="mt-8 text-xs text-gray-400">
            Error 404 &mdash; Page not found
        </p>
    </div>

</body>
</html>
