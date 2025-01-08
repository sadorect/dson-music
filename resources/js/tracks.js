window.tracksGrid = function() {
    return {
        tracks: [],
        currentTrackIndex: 0,
        
        init() {
            this.loadTracks();
            window.player = null;
            this.setupEventListeners();
        },
        
        async loadTracks() {
            try {
                const response = await fetch('/tracks/public');
                this.tracks = await response.json();
            } catch (error) {
                console.log('Error loading tracks:', error);
            }
        },
        
        setupEventListeners() {
            window.addEventListener('player:toggle', () => this.togglePlay());
            window.addEventListener('player:previous', () => this.previousTrack());
            window.addEventListener('player:next', () => this.nextTrack());
            window.addEventListener('player:seek', (e) => this.seek(e.detail.percent));
        },
        
        playTrack(track) {
            if (window.player) {
                window.player.unload();
            }
            
            this.currentTrackIndex = this.tracks.findIndex(t => t.id === track.id);
            
            // Record play
            fetch(`/tracks/${track.id}/play`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            window.player = new Howl({
                src: [track.audioUrl],
                html5: true,
                autoplay: true,
                volume: 1.0,
                onload: () => {
                    window.dispatchEvent(new CustomEvent('track:duration', { 
                        detail: { duration: window.player.duration() } 
                    }));
                },
                onplay: () => {
                    this.updateProgress();
                },
                onend: () => this.nextTrack()
            });
            
            window.dispatchEvent(new CustomEvent('track:play', { detail: track }));
        },
        
        updateProgress() {
            if (window.player && window.player.playing()) {
                const seek = window.player.seek() || 0;
                window.dispatchEvent(new CustomEvent('track:progress', { 
                    detail: { currentTime: seek } 
                }));
                requestAnimationFrame(() => this.updateProgress());
            }
        },

        togglePlay() {
            if (window.player) {
                if (window.player.playing()) {
                    // Store current position before pausing
                    this.currentPosition = window.player.seek();
                    window.player.pause();
                } else {
                    // Resume from stored position
                    window.player.seek(this.currentPosition);
                    window.player.play();
                }
            }
        },
        
        previousTrack() {
            if (this.currentTrackIndex > 0) {
                this.playTrack(this.tracks[this.currentTrackIndex - 1]);
            }
        },
        
        nextTrack() {
            if (this.currentTrackIndex < this.tracks.length - 1) {
                this.playTrack(this.tracks[this.currentTrackIndex + 1]);
            }
        },
        
        seek(percent) {
                if (window.player) {
                const duration = window.player.duration();
                const seekTime = duration * percent;
                window.player.seek(seekTime);
                 // Emit progress event with new position
                window.dispatchEvent(new CustomEvent('track:progress', { 
                    detail: { currentTime: seekTime,
                        duration: duration,
                        percent: percent * 100 } 
                }));
            }
        }
    }
}
