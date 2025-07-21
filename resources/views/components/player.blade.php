<div x-data="playerControls()"
x-init="console.log('Player component mounted')" class="fixed bottom-0 w-full bg-bg text-white p-4 dson-player" x-data="playerControls()">
    <div class="container mx-auto flex justify-between items-center">
        <div class="track-info flex items-center space-x-4">
            <img :src="currentTrack?.artwork" class="h-12 w-12 rounded object-cover" x-show="currentTrack">
            <div>
                <h4 class="font-bold" x-text="currentTrack?.title || 'Track'"></h4>
                <p class="text-sm opacity-75" x-text="currentTrack?.artist || ''"></p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <button class="bg-primary-color text-black p-2 rounded-full" @click="previousTrack">
            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-skip-back-icon lucide-skip-back"><path d="M17.971 4.285A2 2 0 0 1 21 6v12a2 2 0 0 1-3.029 1.715l-9.997-5.998a2 2 0 0 1-.003-3.432z"/><path d="M3 20V4"/></svg>
            </button>
            <button class="bg-primary-color text-black p-2 rounded-full" @click="togglePlay">
    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pause-icon lucide-pause" x-show="isPlaying">
        <rect x="14" y="3" width="5" height="18" rx="1"/>
        <rect x="5" y="3" width="5" height="18" rx="1"/>
    </svg>
    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-play-icon lucide-play" x-show="!isPlaying">
        <path d="M5 5a2 2 0 0 1 3.008-1.728l11.997 6.998a2 2 0 0 1 .003 3.458l-12 7A2 2 0 0 1 5 19z"/>
    </svg>
</button>
            <button class="bg-primary-color text-black p-2 rounded-full" @click="stop()"><svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-icon lucide-square"><rect width="18" height="18" x="3" y="3" rx="2"/></svg></button>
            <button class="bg-primary-color text-black p-2 rounded-full" @click="nextTrack"><svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-skip-forward-icon lucide-skip-forward"><path d="M21 4v16"/><path d="M6.029 4.285A2 2 0 0 0 3 6v12a2 2 0 0 0 3.029 1.715l9.997-5.998a2 2 0 0 0 .003-3.432z"/></svg></button>
        </div>
        
        <div class="flex items-center space-x-4 w-1/3">
            <div class="flex-grow">
                <div class="flex items-center gap-2 text-sm mb-1">
                    <span x-text="formatTime(currentTime)">0:00</span>
                    <span>/</span>
                    <span x-text="formatTime(duration)">0:00</span>
                    <button @click="toggleRepeat" :class="{ 'text-red-600': repeatMode !== 'none' }"><svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-rotate-ccw-icon lucide-rotate-ccw"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg></button>
                    <button @click="toggleShuffle" :class="{ 'text-red-600': isShuffled }"><svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shuffle-icon lucide-shuffle"><path d="m18 14 4 4-4 4"/><path d="m18 2 4 4-4 4"/><path d="M2 18h1.973a4 4 0 0 0 3.3-1.7l5.454-8.6a4 4 0 0 1 3.3-1.7H22"/><path d="M2 6h1.972a4 4 0 0 1 3.6 2.2"/><path d="M22 18h-6.041a4 4 0 0 1-3.3-1.8l-.359-.45"/></svg></button>
                
                </div>
                <div class="flex items-center gap-2">
                    </div>
                <div class="h-1 bg-gray-700 rounded cursor-pointer" @click="seek($event)">
                    <div class="h-1 bg-red-600 rounded transition-all" :style="`width: ${progress}%`"></div>
                </div>
                
            </div>
            
            <div class="flex items-center gap-2">
                <button @click="toggleMute" class="text-white">
                    <span x-show="!isMuted"><svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-volume2-icon lucide-volume-2"><path d="M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z"/><path d="M16 9a5 5 0 0 1 0 6"/><path d="M19.364 18.364a9 9 0 0 0 0-12.728"/></svg></span>
                    <span x-show="isMuted"><svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-volume-off-icon lucide-volume-off"><path d="M16 9a5 5 0 0 1 .95 2.293"/><path d="M19.364 5.636a9 9 0 0 1 1.889 9.96"/><path d="m2 2 20 20"/><path d="m7 7-.587.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298V11"/><path d="M9.828 4.172A.686.686 0 0 1 11 4.657v.686"/></svg></span>
                </button>
                <input type="range" 
                       min="0" 
                       max="100" 
                       x-model="volume" 
                       @input="updateVolume" 
                       class="w-20 ">
            </div>
        </div>
    </div>
</div>

