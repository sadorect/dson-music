<div x-data="playerControls()" class="fixed bottom-0 z-40 w-full bg-white border-t border-black/10 shadow-lg dson-player text-black">
    <div class="mx-auto max-w-7xl flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between p-3 sm:p-4">
        <div class="min-w-0 flex items-center gap-3 lg:w-1/3">
            <img :src="currentTrack?.artwork" class="h-10 w-10 sm:h-12 sm:w-12 rounded object-cover" x-show="currentTrack">
            <div class="min-w-0">
                <h4 class="font-semibold truncate" x-text="currentTrack?.title || 'No track selected'"></h4>
                <p class="text-xs sm:text-sm opacity-70 truncate" x-text="currentTrack?.artist || 'Select a track to play'"></p>
            </div>
        </div>

        <div class="flex flex-col gap-2 lg:w-1/3">
            <div class="flex items-center justify-center gap-2">
                <button class="bg-white border border-black/15 text-black p-2 rounded-full hover:bg-orange-50 hover:border-orange-400 transition-colors" @click="previousTrack" aria-label="Previous track">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.971 4.285A2 2 0 0 1 21 6v12a2 2 0 0 1-3.029 1.715l-9.997-5.998a2 2 0 0 1-.003-3.432z"/><path d="M3 20V4"/></svg>
                </button>
                <button class="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-2.5 rounded-full glow-accent" @click="togglePlay" aria-label="Play or pause">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-show="isPlaying"><rect x="14" y="3" width="5" height="18" rx="1"/><rect x="5" y="3" width="5" height="18" rx="1"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-show="!isPlaying"><path d="M5 5a2 2 0 0 1 3.008-1.728l11.997 6.998a2 2 0 0 1 .003 3.458l-12 7A2 2 0 0 1 5 19z"/></svg>
                </button>
                <button class="bg-white border border-black/15 text-black p-2 rounded-full hover:bg-orange-50 hover:border-orange-400 transition-colors" @click="nextTrack" aria-label="Next track">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 4v16"/><path d="M6.029 4.285A2 2 0 0 0 3 6v12a2 2 0 0 0 3.029 1.715l9.997-5.998a2 2 0 0 0 .003-3.432z"/></svg>
                </button>
            </div>

            <div class="flex items-center gap-2 text-xs sm:text-sm">
                <span x-text="formatTime(currentTime)">0:00</span>
                <div class="h-1 bg-black/10 rounded cursor-pointer flex-1" @click="seek($event)">
                    <div class="h-1 bg-gradient-to-r from-orange-500 to-orange-600 rounded transition-all shadow-sm" :style="`width: ${progress}%`"></div>
                </div>
                <span x-text="formatTime(duration)">0:00</span>
            </div>
        </div>

        <div class="flex items-center justify-center lg:justify-end gap-2 sm:gap-3 lg:w-1/3">
            <button @click="toggleRepeat" :class="{ 'text-primary-color glow-accent': repeatMode !== 'none' }" class="text-black/70 hover:text-orange-600 p-2 rounded transition-colors" aria-label="Toggle repeat">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            </button>
            <button @click="toggleShuffle" :class="{ 'text-primary-color glow-accent bg-orange-100 border border-orange-300': isShuffled }" class="text-black/70 hover:text-orange-600 p-2 rounded transition-colors" aria-label="Toggle shuffle" :aria-pressed="isShuffled.toString()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 14 4 4-4 4"/><path d="m18 2 4 4-4 4"/><path d="M2 18h1.973a4 4 0 0 0 3.3-1.7l5.454-8.6a4 4 0 0 1 3.3-1.7H22"/><path d="M2 6h1.972a4 4 0 0 1 3.6 2.2"/><path d="M22 18h-6.041a4 4 0 0 1-3.3-1.8l-.359-.45"/></svg>
            </button>
            <button @click="toggleQueuePanel" :class="{ 'text-primary-color bg-orange-100 border border-orange-300': showQueue }" class="text-black/70 hover:text-orange-600 p-2 rounded transition-colors" aria-label="Toggle queue">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            </button>
            <button @click="toggleMute" class="text-black/70 hover:text-orange-600 p-2 rounded transition-colors" aria-label="Toggle mute">
                <span x-show="!isMuted"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z"/><path d="M16 9a5 5 0 0 1 0 6"/><path d="M19.364 18.364a9 9 0 0 0 0-12.728"/></svg></span>
                <span x-show="isMuted"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 9a5 5 0 0 1 .95 2.293"/><path d="M19.364 5.636a9 9 0 0 1 1.889 9.96"/><path d="m2 2 20 20"/><path d="m7 7-.587.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298V11"/><path d="M9.828 4.172A.686.686 0 0 1 11 4.657v.686"/></svg></span>
            </button>
            <input type="range" min="0" max="100" x-model="volume" @input="updateVolume" class="w-20 sm:w-24 accent-primary-color">
        </div>
    </div>

    <div x-show="showQueue" x-transition class="absolute bottom-full right-4 mb-3 w-[22rem] max-w-[92vw] bg-white border border-black/15 rounded-xl shadow-2xl p-3">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-black">Up Next</h3>
            <button @click="clearQueue" class="text-xs text-red-600 hover:text-red-700">Clear</button>
        </div>

        <div class="max-h-72 overflow-y-auto space-y-2">
            <template x-if="queue.length === 0">
                <p class="text-xs text-black/50">Queue is empty.</p>
            </template>

            <template x-for="(track, index) in queue" :key="`${track.id || index}-${index}`">
                <div class="flex items-center gap-2 p-2 rounded-lg border border-black/10 bg-orange-50/30">
                    <img :src="track.artwork" alt="" class="w-9 h-9 rounded object-cover">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-black truncate" x-text="track.title"></p>
                        <p class="text-xs text-black/60 truncate" x-text="track.artist"></p>
                    </div>
                    <button @click="playFromQueue(index)" class="text-xs px-2 py-1 rounded bg-orange-500 text-white hover:bg-orange-600">Play</button>
                    <button @click="removeFromQueue(index)" class="text-xs text-red-600 hover:text-red-700">Remove</button>
                </div>
            </template>
        </div>
    </div>
</div>

