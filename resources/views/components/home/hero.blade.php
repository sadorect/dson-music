<div class="relative h-96 overflow-hidden">
    @php
        try {
            $settings = app(\App\Settings\GeneralSettings::class);
            $heroSlides = $settings->hero_slides ?? [];
        } catch (\Exception $e) {
            // Fallback if settings are not initialized
            $heroSlides = [
                [
                    'title' => 'Discover New Music',
                    'subtitle' => 'Stream and download tracks from emerging artists worldwide',
                    'button_text' => 'Get Started',
                    'button_url' => route('register'),
                    'active' => true,
                    'image_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'
                ]
            ];
        }
        
        $activeSlides = array_filter($heroSlides, function($slide) {
            return isset($slide['active']) && $slide['active'] && isset($slide['image_url']);
        });
        
        // If no active slides with images, use default slide
        if (empty($activeSlides)) {
            $activeSlides = [[
                'image_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
                'title' => 'Discover New Music',
                'subtitle' => 'Stream and download tracks from emerging artists worldwide',
                'button_text' => 'Get Started',
                'button_url' => route('register')
            ]];
        }
    @endphp

    <!-- Rest of the component remains the same -->


    <!-- Hero Slideshow -->
    <div x-data="{ activeSlide: 0 }" class="relative h-full">
        @foreach($activeSlides as $index => $slide)
            <div x-show="activeSlide === {{ $loop->index }}" 
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 transform scale-105"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-500"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="absolute inset-0 w-full h-full bg-cover bg-center"
                 style="background-image: url('{{ $slide['image_url'] }}');">
                <div class="absolute inset-0 bg-gradient-to-r from-purple-900/70 to-blue-900/70"></div>
            </div>
        @endforeach
        
        <!-- Content -->
        <div class="container mx-auto px-4 h-full flex items-center relative z-10">
            <div class="text-white max-w-2xl">
                @foreach($activeSlides as $index => $slide)
                    <div x-show="activeSlide === {{ $loop->index }}"
                         x-transition:enter="transition ease-out duration-300 delay-300"
                         x-transition:enter-start="opacity-0 transform translate-y-4"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform translate-y-4">
                        <h1 class="text-5xl font-bold mb-4">{{ $slide['title'] }}</h1>
                        <p class="text-xl mb-8">{{ $slide['subtitle'] }}</p>
                        <a href="{{ $slide['button_url'] }}" class="bg-white text-purple-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition">
                            {{ $slide['button_text'] }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        
        <!-- Slide Controls -->
        @if(count($activeSlides) > 1)
            <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
                @foreach($activeSlides as $index => $slide)
                    <button @click="activeSlide = {{ $loop->index }}" 
                            :class="{'bg-white': activeSlide === {{ $loop->index }}, 'bg-white/50': activeSlide !== {{ $loop->index }}}" 
                            class="w-3 h-3 rounded-full focus:outline-none"></button>
                @endforeach
            </div>
            
            <!-- Auto-advance slides -->
            <div x-init="setInterval(() => { activeSlide = (activeSlide + 1) % {{ count($activeSlides) }} }, 5000)"></div>
        @endif
    </div>
</div>
