<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <meta name="description" content="GRIN Music - Your premium music streaming platform">
    <meta name="keywords" content="music, streaming, songs, artists, playlists, audio">
    <meta name="author" content="GRIN Music">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="GRIN Music">
    <meta property="og:description" content="Your premium music streaming platform">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/dson-music-og.jpg') }}">



    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="GRIN Music">
    <meta name="twitter:description" content="Your premium music streaming platform">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">


    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    <title>@yield('title', config('app.name', 'GRIN Music'))</title>
    @if(setting('favicon_url'))
    <link rel="icon" type="image/webp" href="{{ setting('favicon_url') }}">
    @endif
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">



    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.3/howler.min.js"></script>
    @vite(['resources/css/app.css', 'resources/css/dson-theme.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs" defer></script>

    <script>
        window.recaptchaSiteKey = "{{ config('services.recaptcha.site_key') }}";
    </script>
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    @stack('scripts')
</head>

<body class="font-sans antialiased h-screen bg-bg overflow-hidden">
    <div class="flex flex-col h-screen">
        <div class="shrink-0">
            @include('layouts.navigation')
        </div>

        <!-- Page Content -->
        <main class=" h-[80%]">
            <div class="flex p-3 gap-8 h-full">
                <div class="w-9/12 bg-white/5 rounded-lg overflow-y-scroll h-full [&::-webkit-scrollbar]:hidden [&::-webkit-scrollbar-track]:hidden [&::-webkit-scrollbar-thumb]:hidden">
                    @yield('content')

                    <x-footer />
                </div>

                <div class="w-3/12 bg-white/[5%] rounded-lg p-6 flex flex-col gap-4 justify-between">


                    <div class="flex flex-col gap-6">
                        <h1 class="text-white font-semibold text-lg">Your Library</h1>
                        <div class="bg-black/10 p-6 rounded-lg">
                            <h1 class="text-white font-semibold text-lg">Create your first playlist</h1>
                            <p class="text-gray-400">Start creating your first playlist</p>
                            <button class="bg-white my-2 text-black px-4 py-2 rounded-full">Create Playlist</button>
                        </div>

                        <div class="bg-black/10 p-6 rounded-lg">
                            <h1 class="text-white font-semibold text-lg">Let's find some podcasts to follow</h1>
                            <p class="text-gray-400">We'll keep you updated on the latest episodes</p>
                            <button class="bg-white my-2 text-black px-4 py-2 rounded-full">Find Podcasts</button>
                        </div>
                    </div>


                    <div class=" flex flex-col gap-4">
                        <div class="flex flex-wrap items-center gap-4 ">
                            <a href="" class="text-xs text-white/50">Legal</a>
                            <a href="" class="text-xs text-white/50">Safety and Privacy center</a>
                            <a href="" class="text-xs text-white/50">Privacy Policy</a>
                            <a href="" class="text-xs text-white/50">Cookies</a>
                            <a href="" class="text-xs text-white/50">About Us</a>
                            <a href="" class="text-xs text-white/50">Accessibility</a>
                        </div>

                        <button class="text-sm gap-3 w-fit border text-white/50 border-white/50 rounded-full px-4 py-2 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white/50 lucide lucide-globe-icon lucide-globe"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>    
                        English
                        </button>
                    </div>

                </div>
            </div>

        </main>

        <div class="shrink-0">
            <x-player />
        </div>


        {{-- <x-footer /> --}}


    </div>
</body>

</html>