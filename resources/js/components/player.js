function playerControls() {
    return {
        player: null,
        currentTrack: null,
        queue: [],
        playHistory: [],
        isPlaying: false,
        isMuted: false,
        isShuffled: false,
        repeatMode: "none",
        currentTime: 0,
        duration: 0,
        progress: 0,
        volume: 100,
        progressTimer: null,
        storageKey: "dson_player_preferences",
        playerType: null,

        init() {
            this.loadPreferences();
            this.setupEventListeners();
            this.startProgressTimer();
        },

        loadPreferences() {
            try {
                const raw = localStorage.getItem(this.storageKey);
                if (!raw) {
                    return;
                }

                const preferences = JSON.parse(raw);
                this.volume =
                    typeof preferences.volume === "number"
                        ? preferences.volume
                        : 100;
                this.isMuted = Boolean(preferences.isMuted);
                this.isShuffled = Boolean(preferences.isShuffled);
                this.repeatMode = ["none", "single", "all"].includes(
                    preferences.repeatMode,
                )
                    ? preferences.repeatMode
                    : "none";
            } catch (_) {}
        },

        savePreferences() {
            try {
                localStorage.setItem(
                    this.storageKey,
                    JSON.stringify({
                        volume: this.volume,
                        isMuted: this.isMuted,
                        isShuffled: this.isShuffled,
                        repeatMode: this.repeatMode,
                    }),
                );
            } catch (_) {}
        },

        setupEventListeners() {
            window.addEventListener("queue:add", (e) => {
                this.addToQueue(e.detail);
            });

            window.addEventListener("track:play", (e) => {
                this.playTrack(e.detail);
            });

            window.addEventListener("player:toggle", () => {
                this.togglePlay();
            });

            window.addEventListener("player:previous", () => {
                this.previousTrack();
            });

            window.addEventListener("player:next", () => {
                this.nextTrack();
            });

            window.addEventListener("player:seek", (e) => {
                this.seekByPercent(e.detail.percent);
            });
        },

        playTrack(track, addToHistory = true) {
            if (!track || !track.audioUrl) {
                return;
            }

            if (this.currentTrack && addToHistory) {
                this.playHistory.push(this.currentTrack);
            }

            this.stopAndUnloadPlayer();

            this.currentTrack = track;
            this.currentTime = 0;
            this.progress = 0;

            if (typeof window.Howl === "function") {
                this.playerType = "howl";
                this.player = new window.Howl({
                    src: [track.audioUrl],
                    format: this.resolveAudioFormats(track),
                    html5: true,
                    volume: this.volume / 100,
                    mute: this.isMuted,
                    onload: () => {
                        this.duration = this.getPlayerDuration();
                        this.playCurrentPlayer();
                    },
                    onplay: () => {
                        this.isPlaying = true;
                        this.recordPlay();
                    },
                    onpause: () => {
                        this.isPlaying = false;
                    },
                    onstop: () => {
                        this.isPlaying = false;
                    },
                    onend: () => {
                        this.handleTrackEnd();
                    },
                    onloaderror: () => {
                        this.isPlaying = false;
                        this.fallbackToNative(track, "load");
                    },
                    onplayerror: () => {
                        this.isPlaying = false;
                        this.fallbackToNative(track, "play");
                    },
                });

                return;
            }

            this.playerType = "native";
            this.player = new Audio(track.audioUrl);
            this.player.preload = "metadata";
            this.player.volume = this.volume / 100;
            this.player.muted = this.isMuted;

            this.player.addEventListener("loadedmetadata", () => {
                this.duration = this.getPlayerDuration();
            });

            this.player.addEventListener("play", () => {
                this.isPlaying = true;
                this.recordPlay();
            });

            this.player.addEventListener("pause", () => {
                this.isPlaying = false;
            });

            this.player.addEventListener("ended", () => {
                this.handleTrackEnd();
            });

            this.playCurrentPlayer();
        },

        resolveAudioFormats(track) {
            if (Array.isArray(track?.formats) && track.formats.length > 0) {
                return track.formats;
            }

            if (typeof track?.format === "string" && track.format.trim()) {
                return [track.format.trim().toLowerCase()];
            }

            const source = String(track?.audioUrl || track?.filePath || "");
            const cleanSource = source.split("?")[0].toLowerCase();
            const extensionMatch = cleanSource.match(/\.([a-z0-9]+)$/);

            if (extensionMatch?.[1]) {
                return [extensionMatch[1]];
            }

            return ["mp3", "wav", "ogg", "aac", "m4a", "flac"];
        },

        fallbackToNative(track, reason = "unknown") {
            if (!track?.audioUrl || this.playerType !== "howl") {
                return;
            }

            this.stopAndUnloadPlayer();
            this.playerType = "native";
            this.player = new Audio(track.audioUrl);
            this.player.preload = "metadata";
            this.player.volume = this.volume / 100;
            this.player.muted = this.isMuted;

            this.player.addEventListener("loadedmetadata", () => {
                this.duration = this.getPlayerDuration();
            });

            this.player.addEventListener("play", () => {
                this.isPlaying = true;
                this.recordPlay();
            });

            this.player.addEventListener("pause", () => {
                this.isPlaying = false;
            });

            this.player.addEventListener("ended", () => {
                this.handleTrackEnd();
            });

            this.player.addEventListener("error", () => {
                this.isPlaying = false;
                console.error("Playback failed", {
                    reason,
                    url: track.audioUrl,
                });
            });

            this.playCurrentPlayer();
        },

        stopAndUnloadPlayer() {
            if (!this.player) {
                return;
            }

            if (this.playerType === "howl") {
                this.player.stop();
                this.player.unload();
            } else if (this.playerType === "native") {
                this.player.pause();
                this.player.currentTime = 0;
                this.player.src = "";
            }

            this.player = null;
            this.playerType = null;
        },

        playCurrentPlayer() {
            if (!this.player) {
                return;
            }

            if (this.playerType === "howl") {
                this.player.play();
                return;
            }

            if (this.playerType === "native") {
                this.player.play().catch(() => {
                    this.isPlaying = false;
                });
            }
        },

        pauseCurrentPlayer() {
            if (!this.player) {
                return;
            }

            if (this.playerType === "howl") {
                this.player.pause();
                return;
            }

            if (this.playerType === "native") {
                this.player.pause();
            }
        },

        isPlayerPlaying() {
            if (!this.player) {
                return false;
            }

            if (this.playerType === "howl") {
                return this.player.playing();
            }

            if (this.playerType === "native") {
                return !this.player.paused;
            }

            return false;
        },

        getPlayerSeek() {
            if (!this.player) {
                return 0;
            }

            if (this.playerType === "howl") {
                return this.player.seek() || 0;
            }

            if (this.playerType === "native") {
                return this.player.currentTime || 0;
            }

            return 0;
        },

        setPlayerSeek(seconds) {
            if (!this.player) {
                return;
            }

            if (this.playerType === "howl") {
                this.player.seek(seconds);
                return;
            }

            if (this.playerType === "native") {
                this.player.currentTime = seconds;
            }
        },

        getPlayerDuration() {
            if (!this.player) {
                return 0;
            }

            if (this.playerType === "howl") {
                return this.player.duration() || 0;
            }

            if (this.playerType === "native") {
                return this.player.duration || 0;
            }

            return 0;
        },

        recordPlay() {
            if (!this.currentTrack?.id) {
                return;
            }

            const csrfToken = document.querySelector(
                'meta[name="csrf-token"]',
            )?.content;
            if (!csrfToken) {
                return;
            }

            fetch(`/tracks/${this.currentTrack.id}/play`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
            }).catch(() => {});
        },

        handleTrackEnd() {
            if (this.repeatMode === "single" && this.currentTrack) {
                this.playTrack(this.currentTrack, false);
                return;
            }

            this.nextTrack();
        },

        startProgressTimer() {
            if (this.progressTimer) {
                clearInterval(this.progressTimer);
            }

            this.progressTimer = setInterval(() => {
                if (this.player && this.isPlayerPlaying()) {
                    this.currentTime = this.getPlayerSeek();
                    this.progress =
                        this.duration > 0
                            ? (this.currentTime / this.duration) * 100
                            : 0;
                }
            }, 300);
        },

        stop() {
            this.stopAndUnloadPlayer();
            this.isPlaying = false;
            this.currentTime = 0;
            this.progress = 0;
        },

        togglePlay() {
            if (!this.player) {
                return;
            }

            if (this.isPlayerPlaying()) {
                this.pauseCurrentPlayer();
                this.isPlaying = false;
            } else {
                this.playCurrentPlayer();
                this.isPlaying = true;
            }
        },

        previousTrack() {
            if (this.playHistory && this.playHistory.length > 0) {
                const previousTrack = this.playHistory.pop();
                this.playTrack(previousTrack, false);
            }
        },

        nextTrack() {
            if (this.queue.length > 0) {
                const nextTrack = this.isShuffled
                    ? this.queue.splice(
                          Math.floor(Math.random() * this.queue.length),
                          1,
                      )[0]
                    : this.queue.shift();
                this.playTrack(nextTrack, false);
                return;
            }

            if (this.repeatMode === "all" && this.playHistory.length > 0) {
                const nextFromHistory = this.playHistory.shift();
                this.playTrack(nextFromHistory, false);
            }
        },

        seek(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const percent = x / rect.width;

            this.seekByPercent(percent);
        },

        seekByPercent(percent) {
            if (!this.player || !this.duration) {
                return;
            }

            const safePercent = Math.min(Math.max(percent, 0), 1);
            const seekTime = this.duration * safePercent;

            this.setPlayerSeek(seekTime);
            this.currentTime = seekTime;
            this.progress = safePercent * 100;
        },

        toggleMute() {
            if (this.player) {
                this.isMuted = !this.isMuted;
                if (this.playerType === "howl") {
                    this.player.mute(this.isMuted);
                } else if (this.playerType === "native") {
                    this.player.muted = this.isMuted;
                }
            }
            this.savePreferences();
        },

        updateVolume() {
            if (this.player) {
                if (this.playerType === "howl") {
                    this.player.volume(this.volume / 100);
                } else if (this.playerType === "native") {
                    this.player.volume = this.volume / 100;
                }
            }
            this.savePreferences();
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, "0")}`;
        },

        toggleRepeat() {
            const modes = ["none", "single", "all"];
            const currentIndex = modes.indexOf(this.repeatMode);
            this.repeatMode = modes[(currentIndex + 1) % modes.length];
            this.savePreferences();
        },

        toggleShuffle() {
            this.isShuffled = !this.isShuffled;
            this.savePreferences();
        },

        showQueueNotification(message) {
            const notification = document.createElement("div");
            notification.className =
                "fixed bottom-24 right-4 z-50 bg-black/80 text-white px-4 py-2 rounded-lg text-sm";
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 2000);
        },
        addToQueue(track) {
            this.queue.push(track);
            this.showQueueNotification("Track added to queue");
        },

        removeFromQueue(index) {
            this.queue.splice(index, 1);
        },

        clearQueue() {
            this.queue = [];
        },

        destroy() {
            if (this.progressTimer) {
                clearInterval(this.progressTimer);
            }
        },
    };
}

export default playerControls;
