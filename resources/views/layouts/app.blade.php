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
<body class="font-sans antialiased">
    <div class="min-h-screen">
        @include('layouts.navigation')
        
        <!-- Page Content -->
        <main>
            @yield('content')
            <x-player />
        <x-footer />
        
        @stack('scripts')
        </main>
        
      
    </div>
</body>
</html>


