<div x-data="playerControls()" class="fixed bottom-0 z-40 w-full border-t border-white/10 bg-bg/95 backdrop-blur p-3 sm:p-4 dson-player">
    <div class="mx-auto max-w-7xl flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0 flex items-center gap-3 lg:w-1/3">
            <img :src="currentTrack?.artwork" class="h-10 w-10 sm:h-12 sm:w-12 rounded object-cover" x-show="currentTrack">
            <div class="min-w-0">
                <h4 class="font-semibold truncate" x-text="currentTrack?.title || 'No track selected'"></h4>
                <p class="text-xs sm:text-sm opacity-70 truncate" x-text="currentTrack?.artist || 'Select a track to play'"></p>
            </div>
        </div>

        <div class="flex flex-col gap-2 lg:w-1/3">
            <div class="flex items-center justify-center gap-2">
                <button class="bg-white/10 hover:bg-white/20 text-white p-2 rounded-full" @click="previousTrack" aria-label="Previous track">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.971 4.285A2 2 0 0 1 21 6v12a2 2 0 0 1-3.029 1.715l-9.997-5.998a2 2 0 0 1-.003-3.432z"/><path d="M3 20V4"/></svg>
                </button>
                <button class="bg-primary-color text-black p-2.5 rounded-full" @click="togglePlay" aria-label="Play or pause">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-show="isPlaying"><rect x="14" y="3" width="5" height="18" rx="1"/><rect x="5" y="3" width="5" height="18" rx="1"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-show="!isPlaying"><path d="M5 5a2 2 0 0 1 3.008-1.728l11.997 6.998a2 2 0 0 1 .003 3.458l-12 7A2 2 0 0 1 5 19z"/></svg>
                </button>
                <button class="bg-white/10 hover:bg-white/20 text-white p-2 rounded-full" @click="nextTrack" aria-label="Next track">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 4v16"/><path d="M6.029 4.285A2 2 0 0 0 3 6v12a2 2 0 0 0 3.029 1.715l9.997-5.998a2 2 0 0 0 .003-3.432z"/></svg>
                </button>
            </div>

            <div class="flex items-center gap-2 text-xs sm:text-sm">
                <span x-text="formatTime(currentTime)">0:00</span>
                <div class="h-1 bg-white/20 rounded cursor-pointer flex-1" @click="seek($event)">
                    <div class="h-1 bg-primary-color rounded transition-all" :style="`width: ${progress}%`"></div>
                </div>
                <span x-text="formatTime(duration)">0:00</span>
            </div>
        </div>

        <div class="flex items-center justify-center lg:justify-end gap-2 sm:gap-3 lg:w-1/3">
            <button @click="toggleRepeat" :class="{ 'text-primary-color': repeatMode !== 'none' }" class="text-white/80" aria-label="Toggle repeat">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            </button>
            <button @click="toggleShuffle" :class="{ 'text-primary-color': isShuffled }" class="text-white/80" aria-label="Toggle shuffle">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 14 4 4-4 4"/><path d="m18 2 4 4-4 4"/><path d="M2 18h1.973a4 4 0 0 0 3.3-1.7l5.454-8.6a4 4 0 0 1 3.3-1.7H22"/><path d="M2 6h1.972a4 4 0 0 1 3.6 2.2"/><path d="M22 18h-6.041a4 4 0 0 1-3.3-1.8l-.359-.45"/></svg>
            </button>
            <button @click="toggleMute" class="text-white/80" aria-label="Toggle mute">
                <span x-show="!isMuted"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z"/><path d="M16 9a5 5 0 0 1 0 6"/><path d="M19.364 18.364a9 9 0 0 0 0-12.728"/></svg></span>
                <span x-show="isMuted"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 9a5 5 0 0 1 .95 2.293"/><path d="M19.364 5.636a9 9 0 0 1 1.889 9.96"/><path d="m2 2 20 20"/><path d="m7 7-.587.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298V11"/><path d="M9.828 4.172A.686.686 0 0 1 11 4.657v.686"/></svg></span>
            </button>
            <input type="range" min="0" max="100" x-model="volume" @input="updateVolume" class="w-20 sm:w-24 accent-primary-color">
        </div>
    </div>
</div>

