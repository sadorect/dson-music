<div class="fixed bottom-0 w-full bg-black text-white p-4 dson-player" x-data="playerControls()">
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
            <button class="dson-btn-player" @click="nextTrack">⏭</button>
        </div>
        
        <div class="flex items-center space-x-4 w-1/3">
            <div class="flex-grow">
                <div class="flex items-center gap-2 text-sm mb-1">
                    <span x-text="formatTime(currentTime)">0:00</span>
                    <span>/</span>
                    <span x-text="formatTime(duration)">0:00</span>
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

@push('scripts')
<script>
function playerControls() {
    return {
        currentTrack: null,
        isPlaying: false,
        isMuted: false,
        currentTime: 0,
        duration: 0,
        progress: 0,
        volume: 100,
        
        init() {
            this.setupEventListeners();
            this.startProgressTimer();
        },
        
        setupEventListeners() {
            window.addEventListener('track:play', (e) => {
                this.currentTrack = e.detail;
                this.isPlaying = true;
                this.updateDuration();
            });
                    window.addEventListener('track:progress', (e) => {
                this.currentTime = e.detail.currentTime;
                this.progress = (this.currentTime / this.duration) * 100;
            });
            
            window.addEventListener('track:duration', (e) => {
                this.duration = e.detail.duration;
            });
        },
        
        startProgressTimer() {
            setInterval(() => {
                if (window.player && window.player.playing()) {
                    this.currentTime = window.player.seek();
                    this.progress = (this.currentTime / this.duration) * 100;
                }
            }, 1000);
        },
        
        updateDuration() {
            if (window.player) {
                this.duration = window.player.duration();
            }
        },
        
        togglePlay() {
            if (window.player) {
                window.dispatchEvent(new CustomEvent('player:toggle'));
                this.isPlaying = !this.isPlaying;
            }
        },
        
        previousTrack() {
            window.dispatchEvent(new CustomEvent('player:previous'));
        },
        
        nextTrack() {
            window.dispatchEvent(new CustomEvent('player:next'));
        },
        
        seek(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const percent = x / rect.width;
            
            window.dispatchEvent(new CustomEvent('player:seek', { 
                detail: { percent } 
            }));
            
            // Update local state immediately
            this.progress = percent * 100;
            this.currentTime = this.duration * percent;
        },
        
        toggleMute() {
            if (window.player) {
                this.isMuted = !this.isMuted;
                window.player.mute(this.isMuted);
            }
        },
        
        updateVolume() {
            if (window.player) {
                window.player.volume(this.volume / 100);
            }
        },
        
        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }
    }
}
</script>
@endpush
