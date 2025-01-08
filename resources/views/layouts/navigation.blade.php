<nav x-data="{ open: false }" class="dson-nav">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="text-white text-2xl font-bold">
                        DSON MUSIC
                    </a>
                </div>

                <!-- Main Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')" class="text-white">
                        {{ __('Home') }}
                    </x-nav-link>
                    <x-nav-link href="#" class="text-white">
                        {{ __('Browse') }}
                    </x-nav-link>
                    <x-nav-link href="#" class="text-white">
                        {{ __('Library') }}
                    </x-nav-link>
                    <x-nav-link href="#" class="text-white">
                        {{ __('Radio') }}
                    </x-nav-link>
                </div>
            </div>
<!-- Mobile Search (Shows in hamburger menu) -->
<div class="sm:hidden" x-data="{ mobileQuery: '', results: [] }">
    <div class="px-2 pt-2 pb-3 space-y-1">
        <div class="relative">
            <input 
                type="text" 
                x-model="mobileQuery"
                @input.debounce.300ms="performSearch"
                class="w-full bg-gray-800 text-white rounded-full py-2 px-4 pl-10"
                placeholder="Search tracks, artists...">
            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
        </div>
    </div>
</div>
            <!-- Search Bar -->
            <div class="hidden sm:flex sm:items-center" x-data="{ 
                query: '', 
                results: [],
                async search() {
                    if (this.query.length < 2) {
                        this.results = [];
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/search/quick?q=${this.query}`);
                        this.results = await response.json();
                    } catch (error) {
                        console.log('Search failed:', error);
                    }
                }
            }">
                <div class="relative">
                    <input 
                        type="text" 
                        x-model="query"
                        @input.debounce.300ms="search"
                        class="bg-gray-800 text-white rounded-full py-2 px-4 pl-10 w-64 focus:w-96 transition-all duration-300"
                        placeholder="Search tracks, artists...">
                    <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
            
                    <!-- Results Dropdown -->
                    <div 
                        x-show="query.length > 1 && results.length > 0"
                        @click.away="results = []"
                        class="absolute mt-2 w-full bg-gray-900 rounded-lg shadow-lg z-50">
                        <div class="max-h-96 overflow-y-auto py-2">
                            <template x-for="result in results" :key="result.id">
                                <a :href="result.url" class="flex items-center px-4 py-2 hover:bg-gray-800">
                                    <img :src="result.image" class="w-10 h-10 rounded">
                                    <div class="ml-3">
                                        <p x-text="result.title" class="text-white"></p>
                                        <p x-text="result.subtitle" class="text-sm text-gray-400"></p>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            @auth
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center text-white hover:opacity-75">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
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
                    <a href="{{ route('login') }}" class="text-white hover:opacity-75">Login</a>
                    <a href="{{ route('register') }}" class="dson-btn">Sign Up</a>
                </div>
            @endauth

            <!-- Mobile menu button -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:opacity-75">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-black">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')" class="text-white">
                {{ __('Home') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="#" class="text-white">
                {{ __('Browse') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="#" class="text-white">
                {{ __('Library') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="#" class="text-white">
                {{ __('Radio') }}
            </x-responsive-nav-link>
        </div>
    </div>
</nav>



@push('scripts')
<script>
function searchBar() {
    return {
        query: '',
        results: [],
        async search() {
            if (this.query.length < 2) {
                this.results = [];
                return;
            }
            
            try {
                const response = await fetch(`/search?q=${this.query}`);
                const data = await response.json();
                this.results = [...data.tracks, ...data.artists].slice(0, 5);
            } catch (error) {
                console.error('Search failed:', error);
            }
        }
    }
}
</script>
@endpush