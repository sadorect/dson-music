<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? $siteTitle }}</title>
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
    @stack('styles')
</head>
<body class="glass-gradient-light min-h-screen antialiased">
<div id="app" class="min-h-screen">

    {{-- ── Navigation ──────────────────────────────────────────────────────── --}}
    <nav class="glass-nav sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-gray-800 hover:text-primary-500 transition-colors" wire:navigate>
                    <x-application-logo class="h-7 w-auto object-contain" />
                    <span>{{ $siteName }}</span>
                </a>

                {{-- Desktop links --}}
                <div class="hidden md:flex items-center gap-7 text-sm">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-primary-500 transition" wire:navigate>Browse</a>
                    <a href="{{ route('charts') }}" class="text-gray-700 hover:text-primary-500 transition" wire:navigate>Charts</a>
                    <a href="{{ route('new-releases') }}" class="text-gray-700 hover:text-primary-500 transition" wire:navigate>New Releases</a>

                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-primary-500 transition" wire:navigate>Dashboard</a>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 glass-btn glass-btn-hover py-1.5 px-3 text-sm">
                                <img src="{{ Auth::user()->getAvatarUrl() }}" class="w-6 h-6 rounded-full object-cover" alt="">
                                <span class="max-w-[100px] truncate">{{ Auth::user()->name }}</span>
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 011.06 0L10 11.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 9.28a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="absolute right-0 mt-2 w-44 glass-panel rounded-xl shadow-glass overflow-hidden text-sm" style="display:none">
                                <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-2 px-4 py-2.5 text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Profile
                                </a>
                                @if(Auth::user()->isArtist())
                                <a href="{{ route('artist.tracks') }}" wire:navigate class="flex items-center gap-2 px-4 py-2.5 text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13"/></svg>
                                    My Music
                                </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition text-left">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-primary-500 transition" wire:navigate>Login</a>
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
            <span class="font-bold text-lg text-gray-800">{{ $siteName }}</span>
            <a href="{{ route('home') }}" wire:navigate class="text-gray-700 hover:text-primary-500">Browse</a>
            <a href="{{ route('charts') }}" wire:navigate class="text-gray-700 hover:text-primary-500">Charts</a>
            @auth
                <a href="{{ route('dashboard') }}" wire:navigate class="text-gray-700 hover:text-primary-500">Dashboard</a>
                <a href="{{ route('profile') }}" wire:navigate class="text-gray-700 hover:text-primary-500">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-left text-gray-700 hover:text-primary-500">Log Out</button>
                </form>
            @else
                <a href="{{ route('login') }}" wire:navigate class="text-gray-700 hover:text-primary-500">Login</a>
                <a href="{{ route('register') }}" wire:navigate class="text-gray-700 hover:text-primary-500">Sign Up</a>
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
                        <x-application-logo class="h-7 w-auto object-contain" />
                        {{ $siteName }}
                    </a>
                    <p class="text-sm leading-relaxed mb-5">
                        Independent music. Real artists.<br>Support the creators you love.
                    </p>
                    @if(count($socialLinks))
                        <div class="flex gap-3">
                            @foreach($socialLinks as $social)
                                <a href="{{ $social['url'] }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="w-8 h-8 rounded-full bg-white/10 hover:bg-primary-500 flex items-center justify-center transition"
                                   aria-label="{{ $social['label'] }}">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $social['path'] }}"/></svg>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Explore --}}
                <div>
                    <h4 class="text-white font-semibold text-sm mb-4 uppercase tracking-wider">Explore</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-primary-400 transition">Home</a></li>
                        <li><a href="{{ route('browse') }}" class="hover:text-primary-400 transition">Browse Music</a></li>
                        <li><a href="{{ route('search') }}" class="hover:text-primary-400 transition">Search</a></li>
                        <li><a href="{{ route('charts') }}" wire:navigate class="hover:text-primary-400 transition">Charts</a></li>
                        <li><a href="{{ route('new-releases') }}" wire:navigate class="hover:text-primary-400 transition">New Releases</a></li>
                        @auth
                            <li><a href="{{ route('listener.liked') }}" class="hover:text-primary-400 transition">Liked Tracks</a></li>
                            <li><a href="{{ route('listener.playlists') }}" class="hover:text-primary-400 transition">My Playlists</a></li>
                        @endauth
                    </ul>
                </div>

                {{-- For Artists --}}
                <div>
                    <h4 class="text-white font-semibold text-sm mb-4 uppercase tracking-wider">For Artists</h4>
                    <ul class="space-y-2.5 text-sm">
                        @auth
                            @if(Auth::user()->isArtist())
                                <li><a href="{{ route('artist.dashboard') }}" class="hover:text-primary-400 transition">Dashboard</a></li>
                                <li><a href="{{ route('artist.upload-track') }}" class="hover:text-primary-400 transition">Upload Track</a></li>
                                <li><a href="{{ route('artist.albums') }}" class="hover:text-primary-400 transition">My Albums</a></li>
                            @endif
                        @else
                            <li><a href="{{ route('register') }}" class="hover:text-primary-400 transition">Join as Artist</a></li>
                        @endauth
                        <li><a href="{{ route('artist-guide') }}" wire:navigate class="hover:text-primary-400 transition">Artist Guide</a></li>
                        <li><a href="{{ route('pricing') }}" wire:navigate class="hover:text-primary-400 transition">Pricing &amp; Donations</a></li>
                    </ul>
                </div>

                {{-- Company --}}
                <div>
                    <h4 class="text-white font-semibold text-sm mb-4 uppercase tracking-wider">Company</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('about') }}" wire:navigate class="hover:text-primary-400 transition">About Us</a></li>
                        <li><a href="{{ route('privacy') }}" wire:navigate class="hover:text-primary-400 transition">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}" wire:navigate class="hover:text-primary-400 transition">Terms of Service</a></li>
                        <li><a href="{{ route('contact') }}" wire:navigate class="hover:text-primary-400 transition">Contact</a></li>
                    </ul>
                </div>

            </div>

            {{-- Divider --}}
            <div class="border-t border-white/10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <p>© {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>

                {{-- Site visit counter --}}
                @php
                    $footerViews = cache()->remember('footer_page_views', 300, fn () =>
                        \App\Models\PageView::count()
                    );
                    $footerToday = cache()->remember('footer_page_views_today', 60, fn () =>
                        \App\Models\PageView::today()->count()
                    );
                @endphp
                <p class="flex items-center gap-1.5">
                    <svg class="w-3 h-3 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span class="text-gray-500">{{ number_format($footerViews) }} visits &middot; {{ number_format($footerToday) }} today</span>
                </p>

                <p class="flex items-center gap-1.5">
                    Made with <span class="text-primary-500">♥</span> for independent artists
                </p>
            </div>

        </div>
    </footer>

    {{-- ── Persistent Mini Player ──────────────────────────────────────────── --}}
    @livewire('mini-player')

    {{-- ── Login-Required Modal ───────────────────────────────────────────── --}}
    <div
        x-data="{ show: false }"
        @open-modal.window="if ($event.detail === 'login-required' || $event.detail?.id === 'login-required') show = true"
        x-show="show"
        x-transition.opacity
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        style="display:none"
    >
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="show = false"></div>
        <div class="relative glass-panel max-w-sm w-full p-8 text-center rounded-2xl shadow-2xl z-10">
            <div class="w-14 h-14 rounded-2xl bg-primary-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Sign in to continue</h2>
            <p class="text-gray-500 text-sm mb-6">You need an account to like tracks, download music, and more.</p>
            <div class="flex flex-col gap-3">
                <a href="{{ route('login') }}" class="glass-btn-primary glass-btn-primary-hover w-full py-2.5 rounded-xl text-sm font-semibold text-center">Sign In</a>
                <a href="{{ route('register') }}" class="glass-btn glass-btn-hover w-full py-2.5 rounded-xl text-sm font-semibold text-center">Create Free Account</a>
            </div>
            <button @click="show = false" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

</div>

@livewireScripts
@stack('scripts')
</body>
</html>
