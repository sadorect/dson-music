<div class="relative h-96 overflow-hidden">
    <!-- Hero Slideshow -->
    <div x-data="{ activeSlide: 0 }" class="relative h-full">
        <!-- Slide 1 -->
        <div x-show="activeSlide === 0" 
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 transform scale-105"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="absolute inset-0 w-full h-full bg-cover bg-center"
             style="background-image: url('https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');">
            <div class="absolute inset-0 bg-gradient-to-r from-purple-900/70 to-blue-900/70"></div>
        </div>
        
        <!-- Slide 2 -->
        <div x-show="activeSlide === 1" 
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 transform scale-105"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="absolute inset-0 w-full h-full bg-cover bg-center"
             style="background-image: url('https://images.unsplash.com/photo-1514525253161-7a46d19cd819?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');">
            <div class="absolute inset-0 bg-gradient-to-r from-purple-900/70 to-blue-900/70"></div>
        </div>
        
        <!-- Slide 3 -->
        <div x-show="activeSlide === 2" 
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 transform scale-105"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="absolute inset-0 w-full h-full bg-cover bg-center"
             style="background-image: url('https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');">
            <div class="absolute inset-0 bg-gradient-to-r from-purple-900/70 to-blue-900/70"></div>
        </div>
        
        <!-- Content -->
        <div class="container mx-auto px-4 h-full flex items-center relative z-10">
            <div class="text-white max-w-2xl">
                <h1 class="text-5xl font-bold mb-4">Discover New Music</h1>
                <p class="text-xl mb-8">Stream and download tracks from emerging artists worldwide</p>
                <a href="{{ route('register') }}" class="bg-white text-purple-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition">
                    Get Started
                </a>
            </div>
        </div>
        
        <!-- Slide Controls -->
        <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
            <button @click="activeSlide = 0" :class="{'bg-white': activeSlide === 0, 'bg-white/50': activeSlide !== 0}" class="w-3 h-3 rounded-full focus:outline-none"></button>
            <button @click="activeSlide = 1" :class="{'bg-white': activeSlide === 1, 'bg-white/50': activeSlide !== 1}" class="w-3 h-3 rounded-full focus:outline-none"></button>
            <button @click="activeSlide = 2" :class="{'bg-white': activeSlide === 2, 'bg-white/50': activeSlide !== 2}" class="w-3 h-3 rounded-full focus:outline-none"></button>
        </div>
        
        <!-- Auto-advance slides -->
        <div x-init="setInterval(() => { activeSlide = (activeSlide + 1) % 3 }, 5000)"></div>
    </div>
</div>
