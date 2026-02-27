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
<div id="app" class="min-h-screen">

    {{-- ── Navigation ──────────────────────────────────────────────────────── --}}
    <nav class="glass-nav sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-gray-800 hover:text-red-500 transition-colors" wire:navigate>
                    <svg class="w-7 h-7 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 3v10.55A4 4 0 107 17V7h8V3H9z"/></svg>
                    <span>GrinMusic</span>
                </a>

                {{-- Desktop links --}}
                <div class="hidden md:flex items-center gap-7 text-sm">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-red-500 transition" wire:navigate>Browse</a>
                    <a href="{{ route('charts') }}" class="text-gray-700 hover:text-red-500 transition" wire:navigate>Charts</a>
                    <a href="{{ route('new-releases') }}" class="text-gray-700 hover:text-red-500 transition" wire:navigate>New Releases</a>

                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-red-500 transition" wire:navigate>Dashboard</a>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 glass-btn glass-btn-hover py-1.5 px-3 text-sm">
                                <img src="{{ Auth::user()->getAvatarUrl() }}" class="w-6 h-6 rounded-full object-cover" alt="">
                                <span class="max-w-[100px] truncate">{{ Auth::user()->name }}</span>
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 011.06 0L10 11.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 9.28a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="absolute right-0 mt-2 w-44 glass-panel rounded-xl shadow-glass overflow-hidden text-sm" style="display:none">
                                <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-2 px-4 py-2.5 text-gray-700 hover:bg-red-50 hover:text-red-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Profile
                                </a>
                                @if(Auth::user()->isArtist())
                                <a href="{{ route('artist.tracks') }}" wire:navigate class="flex items-center gap-2 px-4 py-2.5 text-gray-700 hover:bg-red-50 hover:text-red-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13"/></svg>
                                    My Music
                                </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-gray-700 hover:bg-red-50 hover:text-red-600 transition text-left">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-red-500 transition" wire:navigate>Login</a>
                        <a href="{{ route('register') }}" class="glass-btn-primary glass-btn-primary-hover px-4 py-2 rounded-xl text-sm font-medium" wire:navigate>Sign Up Free</a>
                    @endauth
                </div>

                {{-- Mobile hamburger --}}
                <button x-data x-on:click="$dispatch('toggle-mobile-nav')" class="md:hidden glass-btn glass-btn-hover p-2 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </nav>

    {{-- ── Mobile Drawer ───────────────────────────────────────────────────── --}}
    <div x-data="{ open: false }" x-on:toggle-mobile-nav.window="open = !open" class="md:hidden">
        <div x-show="open" x-transition @click="open = false" class="fixed inset-0 bg-black/30 z-40 backdrop-blur-sm" style="display:none"></div>
        <div x-show="open" x-transition class="fixed top-0 left-0 h-full w-64 glass-panel z-50 shadow-xl p-6 flex flex-col gap-4 text-sm" style="display:none">
            <span class="font-bold text-lg text-gray-800">GrinMusic</span>
            <a href="{{ route('home') }}" wire:navigate class="text-gray-700 hover:text-red-500">Browse</a>
            <a href="{{ route('charts') }}" wire:navigate class="text-gray-700 hover:text-red-500">Charts</a>
            @auth
                <a href="{{ route('dashboard') }}" wire:navigate class="text-gray-700 hover:text-red-500">Dashboard</a>
                <a href="{{ route('profile') }}" wire:navigate class="text-gray-700 hover:text-red-500">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-left text-gray-700 hover:text-red-500">Log Out</button>
                </form>
            @else
                <a href="{{ route('login') }}" wire:navigate class="text-gray-700 hover:text-red-500">Login</a>
                <a href="{{ route('register') }}" wire:navigate class="text-gray-700 hover:text-red-500">Sign Up</a>
            @endauth
        </div>
    </div>

    {{-- ── Page Content ────────────────────────────────────────────────────── --}}
    <main class="pb-6">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    {{-- ── Site Footer ─────────────────────────────────────────────────────── --}}
    <footer class="bg-gray-900 text-gray-400 pb-32 pt-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Top grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-10 mb-12">

                {{-- Brand --}}
                <div class="col-span-2 sm:col-span-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 text-white font-black text-xl mb-3">
                        <svg class="w-7 h-7 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 3v10.55A4 4 0 107 17V7h8V3H9z"/></svg>
                        GrinMuzik
                    </a>
                    <p class="text-sm leading-relaxed mb-5">
                        Independent music. Real artists.<br>Support the creators you love.
                    </p>
                    {{-- Social icons --}}
                    <div class="flex gap-3">
                        @foreach([
                            ['label' => 'Twitter/X',   'path' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
                            ['label' => 'Instagram',   'path' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z'],
                            ['label' => 'Facebook',    'path' => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'],
                        ] as $soc)
                            <a href="#" class="w-8 h-8 rounded-full bg-white/10 hover:bg-red-500 flex items-center justify-center transition" aria-label="{{ $soc['label'] }}">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $soc['path'] }}"/></svg>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Explore --}}
                <div>
                    <h4 class="text-white font-semibold text-sm mb-4 uppercase tracking-wider">Explore</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-red-400 transition">Home</a></li>
                        <li><a href="{{ route('browse') }}" class="hover:text-red-400 transition">Browse Music</a></li>
                        <li><a href="{{ route('search') }}" class="hover:text-red-400 transition">Search</a></li>
                        <li><a href="{{ route('charts') }}" wire:navigate class="hover:text-red-400 transition">Charts</a></li>
                        <li><a href="{{ route('new-releases') }}" wire:navigate class="hover:text-red-400 transition">New Releases</a></li>
                        @auth
                            <li><a href="{{ route('listener.liked') }}" class="hover:text-red-400 transition">Liked Tracks</a></li>
                            <li><a href="{{ route('listener.playlists') }}" class="hover:text-red-400 transition">My Playlists</a></li>
                        @endauth
                    </ul>
                </div>

                {{-- For Artists --}}
                <div>
                    <h4 class="text-white font-semibold text-sm mb-4 uppercase tracking-wider">For Artists</h4>
                    <ul class="space-y-2.5 text-sm">
                        @auth
                            @if(Auth::user()->isArtist())
                                <li><a href="{{ route('artist.dashboard') }}" class="hover:text-red-400 transition">Dashboard</a></li>
                                <li><a href="{{ route('artist.upload-track') }}" class="hover:text-red-400 transition">Upload Track</a></li>
                                <li><a href="{{ route('artist.albums') }}" class="hover:text-red-400 transition">My Albums</a></li>
                            @endif
                        @else
                            <li><a href="{{ route('register') }}" class="hover:text-red-400 transition">Join as Artist</a></li>
                        @endauth
                        <li><a href="{{ route('artist-guide') }}" wire:navigate class="hover:text-red-400 transition">Artist Guide</a></li>
                        <li><a href="{{ route('pricing') }}" wire:navigate class="hover:text-red-400 transition">Pricing &amp; Donations</a></li>
                    </ul>
                </div>

                {{-- Company --}}
                <div>
                    <h4 class="text-white font-semibold text-sm mb-4 uppercase tracking-wider">Company</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('about') }}" wire:navigate class="hover:text-red-400 transition">About Us</a></li>
                        <li><a href="{{ route('privacy') }}" wire:navigate class="hover:text-red-400 transition">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}" wire:navigate class="hover:text-red-400 transition">Terms of Service</a></li>
                        <li><a href="{{ route('contact') }}" wire:navigate class="hover:text-red-400 transition">Contact</a></li>
                    </ul>
                </div>

            </div>

            {{-- Divider --}}
            <div class="border-t border-white/10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <p>© {{ date('Y') }} GrinMuzik. All rights reserved.</p>
                <p class="flex items-center gap-1.5">
                    Made with <span class="text-red-500">♥</span> for independent artists
                </p>
            </div>

        </div>
    </footer>

    {{-- ── Persistent Mini Player ──────────────────────────────────────────── --}}
    @livewire('mini-player')

</div>

@livewireScripts
@stack('scripts')
</body>
</html>