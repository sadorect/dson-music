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


export default playerControls;
