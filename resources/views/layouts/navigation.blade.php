<nav x-data="{ open: false }" class="brand-gradient-shell sticky top-0 z-50 border-b">
    <div class="w-full px-4 sm:px-6 lg:px-8 ">
        <div class="flex justify-between p-4 items-center">
            <div class="flex items-center gap-4">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="brand-text text-2xl font-bold">
                        @if(setting('logo_desktop_url'))
                            <img src="{{ setting('logo_desktop_url') }}" alt="{{ setting('site_name', 'GRIN MUSIC') }}" class="h-10 hidden sm:block">
                        @endif
                        
                        @if(setting('logo_mobile_url'))
                            <img src="{{ setting('logo_mobile_url') }}" alt="{{ setting('site_name', 'GRIN MUSIC') }}" class="h-8 sm:hidden">
                        @elseif(setting('logo_desktop_url'))
                            <img src="{{ setting('logo_desktop_url') }}" alt="{{ setting('site_name', 'GRIN MUSIC') }}" class="h-8 sm:hidden">
                        @else
                            <span class="text-2xl font-bold">{{ setting('site_name', 'GRIN MUSIC') }}</span>
                        @endif
                    </a>
                </div>

                <div class="p-3 rounded-full bg-black/25 border border-orange-400/40">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-icon lucide-house text-orange-300"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                </div>

                <!-- Main Navigation Links -->
                <!-- Impersonation Banner -->
                @if(session()->has('impersonated_by'))
                    <div class="bg-yellow-400 text-black px-4 py-2 rounded-full text-xs font-semibold flex items-center gap-2">
                        <span>Impersonating: {{ Auth::user()?->name ?? 'Unknown User' }}</span>
                        <form action="{{ route('admin.stop-impersonating') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="underline hover:text-red-600 ml-2">Stop Impersonating</button>
                        </form>
                    </div>
                @endif
                <!-- Mobile Search (Shows in hamburger menu) -->
                <div class="sm:hidden" x-data="searchBar" @click.outside="results = []">
                    <div class="px-2 pt-2 pb-3 space-y-1">
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="query"
                                @input.debounce.300ms="search"
                                class="w-full bg-white text-black rounded-full py-2 px-4 pl-10 border border-black/15"
                                placeholder="Search tracks, artists...">
                            <span class="absolute left-3 top-2.5 text-black/50">🔍</span>

                            <div class="absolute left-0 right-0 mt-2 bg-white text-black rounded-lg shadow-xl z-20"
                                 x-show="results.length"
                                 x-cloak>
                                <template x-for="result in results" :key="result.type + '-' + result.id">
                                    <a :href="result.url"
                                       class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100">
                                        <div class="w-10 h-10 rounded overflow-hidden bg-gray-200 flex items-center justify-center">
                                            <img x-show="result.image" :src="result.image" alt="" class="w-full h-full object-cover">
                                            <span x-show="!result.image" class="text-xs font-semibold uppercase" x-text="result.type"></span>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900" x-text="result.title"></p>
                                            <p class="text-sm text-gray-500" x-text="result.subtitle"></p>
                                        </div>
                                        <span class="text-xs text-gray-400 uppercase" x-text="result.type"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Search Bar -->
                <div class="hidden sm:flex sm:items-center" x-data="searchBar" @click.outside="results = []">
                    <form action="{{ route('search') }}" method="GET" class="w-full">
                        <div class="relative w-full max-w-xl">
                            <input 
                                type="text" 
                                name="q"
                                x-model="query"
                                @input.debounce.300ms="search"
                                class="bg-white border border-black/15 text-black placeholder-black/40 rounded-full p-3 w-full text-sm focus:border-orange-400 focus:ring-orange-300"
                                placeholder="What do you want to play?">
                            
                            <button type="submit" class="absolute right-3 top-0 bottom-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide text-black/70 hover:text-orange-600 transition-colors lucide-search-icon lucide-search"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg></button>

                            <div class="absolute left-0 right-0 mt-2 bg-white text-gray-900 rounded-lg shadow-2xl z-20"
                                 x-show="results.length"
                                 x-cloak>
                                <template x-for="result in results" :key="result.type + '-' + result.id">
                                    <a :href="result.url"
                                       class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100">
                                        <div class="w-10 h-10 rounded overflow-hidden bg-gray-200 flex items-center justify-center">
                                            <img x-show="result.image" :src="result.image" alt="" class="w-full h-full object-cover">
                                            <span x-show="!result.image" class="text-xs font-semibold uppercase" x-text="result.type"></span>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-medium" x-text="result.title"></p>
                                            <p class="text-sm text-gray-500" x-text="result.subtitle"></p>
                                        </div>
                                        <span class="text-xs text-gray-400 uppercase" x-text="result.type"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- User Menu -->
            @auth
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center brand-text hover:text-orange-300 transition-colors">
                                <div>{{ Auth::user()?->name ?? 'User' }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('artist.dashboard')">
                                {{ __('Dashboard') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('library.index')">
                                {{ __('Library') }}
                            </x-dropdown-link>
                            @if(optional(Auth::user())->artistProfile)
                                <x-dropdown-link :href="route('artist.profile.edit')">
                                    {{ __('Edit Profile') }}
                                </x-dropdown-link>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            @else
                <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                    <a href="{{ route('login') }}" class="brand-text hover:text-orange-200 transition-colors">Login</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-4 py-3 bg-primary-color text-white border border-transparent rounded-full font-semibold text-xs tracking-widest hover:scale-105 hover:shadow-md hover:shadow-orange-300 transition ease-in-out duration-150">Sign Up</a>
                </div>
            @endauth
            

            <!-- Mobile menu button -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md brand-text hover:text-orange-200 transition-colors">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-black/35 border-t border-orange-700/30">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')" class="brand-text-muted">
                {{ __('Home') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="#" class="brand-text-muted">
                {{ __('Browse') }}
            </x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('library.index')" :active="request()->routeIs('library.*')" class="brand-text-muted">
                    {{ __('Library') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('login')" class="brand-text-muted">
                    {{ __('Library') }}
                </x-responsive-nav-link>
            @endauth
            <x-responsive-nav-link href="#" class="brand-text-muted">
                {{ __('Radio') }}
            </x-responsive-nav-link>
        </div>
    </div>
</nav>


