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

    <script>
        window.recaptchaSiteKey = "{{ config('services.recaptcha.site_key') }}";
    </script>
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
</head>

<body class="font-sans antialiased min-h-screen bg-bg overflow-x-hidden">
    <div class="flex flex-col min-h-screen">
        <div class="shrink-0">
            @include('layouts.navigation')
        </div>

        <!-- Page Content -->
        <main class="flex-1 overflow-hidden pb-24 lg:pb-28">
            <div class="flex h-full flex-col md:flex-row p-3 gap-3 md:gap-6">
                <div class="w-full md:w-8/12 lg:w-9/12 bg-white/5 rounded-lg overflow-y-auto h-full pb-6 [&::-webkit-scrollbar]:hidden [&::-webkit-scrollbar-track]:hidden [&::-webkit-scrollbar-thumb]:hidden">
                    @yield('content')

                    <x-footer />
                </div>

                <div class="hidden md:flex md:w-4/12 lg:w-3/12 bg-white/[5%] rounded-lg p-4 sm:p-6 flex-col gap-4 justify-between overflow-y-auto h-full [&::-webkit-scrollbar]:hidden [&::-webkit-scrollbar-track]:hidden [&::-webkit-scrollbar-thumb]:hidden">


                    <div class="flex flex-col gap-6">
                        <h1 class="text-white font-semibold text-lg">Your Library</h1>
                        @auth
                            @php
                                $recentPlaylists = auth()->user()->playlists()->withCount('tracks')->latest()->take(5)->get();
                                $recentPlays = auth()->user()->plays()
                                    ->with(['track.artist'])
                                    ->latest('played_at')
                                    ->take(50)
                                    ->get()
                                    ->filter(fn ($play) => $play->track)
                                    ->unique('track_id')
                                    ->take(5)
                                    ->values();
                                $followedArtists = auth()->user()->following()->latest()->take(5)->get();
                                $recentDownloads = auth()->user()->downloads()->with('track')->latest()->take(5)->get();
                            @endphp

                            @if($recentPlaylists->isEmpty())
                                <div class="bg-black/10 p-5 rounded-lg">
                                    <h2 class="text-white font-semibold text-base">Create your first playlist</h2>
                                    <p class="text-gray-400 text-sm mt-1">Start organizing your favorite tracks.</p>
                                    <a href="{{ route('playlists.create') }}" class="inline-flex mt-3 bg-white text-black px-4 py-2 rounded-full text-sm font-medium">Create Playlist</a>
                                </div>
                            @else
                                <div class="bg-black/10 p-4 rounded-lg">
                                    <div class="flex items-center justify-between mb-3">
                                        <h2 class="text-white font-semibold">Recent Playlists</h2>
                                        <a href="{{ route('library.index') }}" class="text-xs text-white/70 hover:text-white">View all</a>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($recentPlaylists as $playlist)
                                            <a href="{{ route('playlists.show', $playlist) }}" class="flex items-center justify-between p-2 rounded-md hover:bg-white/10">
                                                <span class="text-sm text-white truncate">{{ $playlist->name }}</span>
                                                <span class="text-xs text-white/50">{{ $playlist->tracks_count }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ route('playlists.create') }}" class="inline-flex bg-white text-black px-4 py-2 rounded-full text-sm font-medium">New Playlist</a>
                                        <a href="{{ route('library.index') }}" class="inline-flex bg-white/10 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-white/20">My Library</a>
                                    </div>
                                </div>
                            @endif

                            <div class="bg-black/10 p-4 rounded-lg">
                                <div class="flex items-center justify-between mb-3">
                                    <h2 class="text-white font-semibold">Recently Played</h2>
                                </div>

                                @if($recentPlays->isEmpty())
                                    <p class="text-xs text-white/60">Start playing tracks and your recent listens will appear here.</p>
                                @else
                                    <div class="space-y-2">
                                        @foreach($recentPlays as $play)
                                            @if($play->track)
                                                <a href="{{ route('tracks.show', $play->track) }}" class="block p-2 rounded-md hover:bg-white/10">
                                                    <div class="text-sm text-white truncate">{{ $play->track->title }}</div>
                                                    <div class="text-xs text-white/60 truncate">{{ $play->track->artist->artist_name ?? 'Unknown Artist' }}</div>
                                                    <div class="text-[11px] text-white/45">{{ number_format($play->track->play_count ?? 0) }} plays</div>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="bg-black/10 p-4 rounded-lg">
                                <div class="flex items-center justify-between mb-3">
                                    <h2 class="text-white font-semibold">Followed Artists</h2>
                                    <a href="{{ route('library.index') }}" class="text-xs text-white/70 hover:text-white">View all</a>
                                </div>
                                @if($followedArtists->isEmpty())
                                    <p class="text-xs text-white/60">Artists you follow will appear here.</p>
                                @else
                                    <div class="space-y-2">
                                        @foreach($followedArtists as $artist)
                                            <a href="{{ route('artists.show', $artist) }}" class="block p-2 rounded-md hover:bg-white/10">
                                                <div class="text-sm text-white truncate">{{ $artist->artist_name }}</div>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="bg-black/10 p-4 rounded-lg">
                                <div class="flex items-center justify-between mb-3">
                                    <h2 class="text-white font-semibold">Downloads</h2>
                                    <a href="{{ route('library.index') }}" class="text-xs text-white/70 hover:text-white">View all</a>
                                </div>
                                @if($recentDownloads->isEmpty())
                                    <p class="text-xs text-white/60">Downloaded tracks will appear here.</p>
                                @else
                                    <div class="space-y-2">
                                        @foreach($recentDownloads as $download)
                                            @if($download->track)
                                                <a href="{{ route('tracks.show', $download->track) }}" class="block p-2 rounded-md hover:bg-white/10">
                                                    <div class="text-sm text-white truncate">{{ $download->track->title }}</div>
                                                    <div class="text-xs text-white/60 truncate">{{ ucfirst($download->status ?? 'unknown') }}</div>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="bg-black/10 p-5 rounded-lg">
                                <h2 class="text-white font-semibold text-base">Save tracks to your library</h2>
                                <p class="text-gray-400 text-sm mt-1">Sign in to create playlists and manage your listening history.</p>
                                <a href="{{ route('login') }}" class="inline-flex mt-3 bg-white text-black px-4 py-2 rounded-full text-sm font-medium">Sign In</a>
                            </div>
                        @endauth
                    </div>


                    <div class=" flex flex-col gap-4">
                        <div class="flex flex-wrap items-center gap-3 ">
                            <a href="" class="text-xs text-white/50">Legal</a>
                            <a href="" class="text-xs text-white/50">Safety and Privacy center</a>
                            <a href="" class="text-xs text-white/50">Privacy Policy</a>
                            <a href="" class="text-xs text-white/50">Cookies</a>
                            <a href="" class="text-xs text-white/50">About Us</a>
                            <a href="" class="text-xs text-white/50">Accessibility</a>
                        </div>

                        <!-- Language Selector -->
                        <button class="text-sm gap-3 w-fit border text-white/50 border-white/50 rounded-full px-4 py-2 flex items-center hover:text-white hover:border-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>    
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

    @stack('scripts')
</body>

</html>