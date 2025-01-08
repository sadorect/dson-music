<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/dson-theme.css'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <div class="flex">
            <!-- Sidebar -->
            <div class="w-64 bg-gray-900 min-h-screen">
                <div class="flex items-center justify-center h-16 bg-gray-800">
                    <span class="text-white font-bold text-xl">DSON Admin</span>
                </div>
                
                <nav class="mt-4">
                    <x-admin.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        Dashboard
                    </x-admin.nav-link>
                    
                    <x-admin.nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users')">
                        Users
                    </x-admin.nav-link>
                    
                    <x-admin.nav-link :href="route('admin.tracks.index')" :active="request()->routeIs('admin.tracks')">
                        Tracks
                    </x-admin.nav-link>
                    
                    <x-admin.nav-link :href="route('admin.artists.index')" :active="request()->routeIs('admin.artists')">
                        Artists
                    </x-admin.nav-link>
                    
                    <x-admin.nav-link :href="route('admin.reports')" :active="request()->routeIs('admin.reports')">
                        Reports
                    </x-admin.nav-link>
                    
                    <x-admin.nav-link :href="route('admin.settings')" :active="request()->routeIs('admin.settings')">
                        Settings
                    </x-admin.nav-link>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Top Navigation -->
                <div class="bg-white shadow">
                    <div class="flex justify-between items-center px-6 py-4">
                        <h2 class="text-xl font-semibold">@yield('title')</h2>
                        
                        <div class="flex items-center">
                            @if(session('success'))
                                <div class="mr-4 text-green-600">{{ session('success') }}</div>
                            @endif
                            
                            <x-dropdown>
                                <x-slot name="trigger">
                                    {{ Auth::user()->name }}
                                </x-slot>
                                
                                <x-slot name="content">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                            Logout
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </div>

                <!-- Page Content -->
                <main class="p-6">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>
</body>
</html>
