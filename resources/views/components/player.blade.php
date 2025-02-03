<div x-data="playerControls()"
x-init="console.log('Player component mounted')" class="fixed bottom-0 w-full bg-black text-white p-4 dson-player" x-data="playerControls()">
    <div class="container mx-auto flex justify-between items-center">
        <div class="track-info flex items-center space-x-4">
            <img :src="currentTrack?.artwork" class="h-12 w-12 rounded object-cover" x-show="currentTrack">
            <div>
                <h4 class="font-bold" x-text="currentTrack?.title || 'Select a track'"></h4>
                <p class="text-sm opacity-75" x-text="currentTrack?.artist || ''"></p>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <button class="dson-btn-player" @click="previousTrack">⏮</button>
            <button class="dson-btn-player" @click="togglePlay" x-text="isPlaying ? '⏸' : '▶'"></button>
            <button class="dson-btn-player" @click="stop()">⏹</button>
            <button class="dson-btn-player" @click="nextTrack">⏭</button>
        </div>
        
        <div class="flex items-center space-x-4 w-1/3">
            <div class="flex-grow">
                <div class="flex items-center gap-2 text-sm mb-1">
                    <span x-text="formatTime(currentTime)">0:00</span>
                    <span>/</span>
                    <span x-text="formatTime(duration)">0:00</span>
                    <button @click="toggleRepeat" :class="{ 'text-red-600': repeatMode !== 'none' }">🔁</button>
                    <button @click="toggleShuffle" :class="{ 'text-red-600': isShuffled }">🔀</button>
                
                </div>
                <div class="flex items-center gap-2">
                    </div>
                <div class="h-1 bg-gray-700 rounded cursor-pointer" @click="seek($event)">
                    <div class="h-1 bg-red-600 rounded transition-all" :style="`width: ${progress}%`"></div>
                </div>
                
            </div>
            
            <div class="flex items-center gap-2">
                <button @click="toggleMute" class="text-white">
                    <span x-show="!isMuted">🔊</span>
                    <span x-show="isMuted">🔇</span>
                </button>
                <input type="range" 
                       min="0" 
                       max="100" 
                       x-model="volume" 
                       @input="updateVolume" 
                       class="w-20">
            </div>
        </div>
    </div>
</div>

