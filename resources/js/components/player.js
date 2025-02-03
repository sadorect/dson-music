function playerControls() {
    console.log('Player controls initialized');
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
                console.log('Audio URL:', e.detail.audioUrl); // Add this line
                // Initialize Howler with the track
                window.player = new Howl({
                    src: [e.detail.audioUrl],
                    html5: true,
                    format: ['mp3','MP3','wav','ogg','WAV','flac','FLAC','aac','AAC','m4a','M4A','m4p','M4P','mp4','MP4','webm','WEBM','weba','WEBA','webm','WEBM'],
                    volume: this.volume / 100,
                    xhr: {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'audio/mpeg'
                        }
                    },
                    onload: () => {
                        console.log('Track loaded:', e.detail.audioUrl);
                        this.duration = window.player.duration();
                        window.player.play();
                    },
                    onplayerror: (id, error) => {
                        console.log('Play error:', error);
                    },
                    onplay: () => {
                        console.log('Track playing');
                        fetch(`/tracks/${this.currentTrack.id}/play`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        this.isPlaying = true;
                    },
                    onloaderror: (id, err) => {
                        console.log('Loading error:', err);
                    }
                });

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
