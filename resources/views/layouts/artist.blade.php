<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com">
<!-- SEO Meta Tags -->
    <title>@yield('title', config('app.name', 'DSON Music')) — Artist</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    

    <!-- Scripts -->
    
    @vite(['resources/css/app.css', 'resources/css/dson-theme.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.3/howler.min.js"></script>
    <script>
        window.recaptchaSiteKey = "{{ config('services.recaptcha.site_key') }}";
    </script>
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
</head>
<body class="brand-sitewide font-sans antialiased">
    @include('layouts.artist-sidebar')
    <div class="ml-64 min-h-screen bg-orange-50/30">
    @if(session('success') || session('error') || $errors->any())
        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="flex items-center gap-3 bg-orange-50 border border-orange-300 text-orange-800 rounded-lg px-4 py-3 mb-3">
                    <svg class="w-5 h-5 flex-shrink-0 text-orange-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-3 mb-3">
                    <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-3 mb-3">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
    
        <main class="py-4">
            @yield('content')
        </main>
    </div>
    
    @stack('scripts')
</body>
</html>
