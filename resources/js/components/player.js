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
        runtimeStateKey: "dson_player_runtime_state",
        playerType: null,
        lastRecordedTrackId: null,
        lastRecordedAt: 0,

        init() {
            this.loadPreferences();
            this.restoreRuntimeState();
            this.setupEventListeners();
            this.setupRuntimePersistence();
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
                const detail = e.detail || {};
                const track = detail.track || detail;
                this.addToQueue(track, Boolean(detail.silent));
            });

            window.addEventListener("queue:add-next", (e) => {
                const detail = e.detail || {};
                const track = detail.track || detail;
                this.addNextToQueue(track, Boolean(detail.silent));
            });

            window.addEventListener("queue:add-many", (e) => {
                const detail = e.detail || {};
                this.addManyToQueue(
                    Array.isArray(detail.tracks) ? detail.tracks : [],
                    detail.notifyMessage || null,
                );
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

        setupRuntimePersistence() {
            const persist = () => this.persistRuntimeState();

            window.addEventListener("beforeunload", persist);
            window.addEventListener("pagehide", persist);
            document.addEventListener("visibilitychange", () => {
                if (document.visibilityState === "hidden") {
                    persist();
                }
            });
        },

        persistRuntimeState() {
            if (!this.currentTrack) {
                return;
            }

            const state = {
                currentTrack: this.currentTrack,
                queue: Array.isArray(this.queue) ? this.queue : [],
                playHistory: Array.isArray(this.playHistory)
                    ? this.playHistory
                    : [],
                isPlaying: this.isPlayerPlaying(),
                currentTime: this.getPlayerSeek(),
                savedAt: Date.now(),
            };

            try {
                sessionStorage.setItem(
                    this.runtimeStateKey,
                    JSON.stringify(state),
                );
            } catch (_) {}
        },

        restoreRuntimeState() {
            try {
                const raw = sessionStorage.getItem(this.runtimeStateKey);
                if (!raw) {
                    return;
                }

                const state = JSON.parse(raw);
                if (!state?.currentTrack?.audioUrl) {
                    return;
                }

                this.queue = Array.isArray(state.queue) ? state.queue : [];
                this.playHistory = Array.isArray(state.playHistory)
                    ? state.playHistory
                    : [];

                this.playTrack(
                    state.currentTrack,
                    false,
                    Boolean(state.isPlaying),
                );

                const resumeFrom = Number(state.currentTime || 0);
                if (resumeFrom > 0) {
                    this.restoreSeekAfterLoad(
                        resumeFrom,
                        Boolean(state.isPlaying),
                    );
                }
            } catch (_) {}
        },

        restoreSeekAfterLoad(seconds, shouldPlay) {
            let attempts = 0;
            const maxAttempts = 40;
            const timer = setInterval(() => {
                attempts += 1;

                if (!this.player) {
                    if (attempts >= maxAttempts) {
                        clearInterval(timer);
                    }
                    return;
                }

                const duration = this.getPlayerDuration();
                if (!duration || !Number.isFinite(duration)) {
                    if (attempts >= maxAttempts) {
                        clearInterval(timer);
                    }
                    return;
                }

                const safeSeek = Math.min(Math.max(seconds, 0), duration);
                this.setPlayerSeek(safeSeek);
                this.currentTime = safeSeek;
                this.duration = duration;
                this.progress = duration > 0 ? (safeSeek / duration) * 100 : 0;

                if (shouldPlay) {
                    this.playCurrentPlayer();
                    this.isPlaying = true;
                } else {
                    this.pauseCurrentPlayer();
                    this.isPlaying = false;
                }

                clearInterval(timer);
            }, 100);
        },

        playTrack(track, addToHistory = true, shouldAutoplay = true) {
            if (!track || !track.audioUrl) {
                return;
            }

            if (
                this.currentTrack?.id === track.id &&
                this.player &&
                this.duration > 0
            ) {
                if (shouldAutoplay) {
                    this.playCurrentPlayer();
                }
                return;
            }

            if (this.currentTrack && addToHistory) {
                this.playHistory.push(this.currentTrack);
            }

            this.stopAndUnloadPlayer();

            this.currentTrack = track;
            this.currentTime = 0;
            this.progress = 0;
            this.persistRuntimeState();

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
                        if (shouldAutoplay) {
                            this.playCurrentPlayer();
                        }
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

            if (shouldAutoplay) {
                this.playCurrentPlayer();
            }
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

            const now = Date.now();
            if (
                this.lastRecordedTrackId === this.currentTrack.id &&
                now - this.lastRecordedAt < 30000
            ) {
                return;
            }

            const csrfToken = document.querySelector(
                'meta[name="csrf-token"]',
            )?.content;
            if (!csrfToken) {
                return;
            }

            this.lastRecordedTrackId = this.currentTrack.id;
            this.lastRecordedAt = now;

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
                    this.persistRuntimeState();
                }
            }, 300);
        },

        stop() {
            this.stopAndUnloadPlayer();
            this.isPlaying = false;
            this.currentTime = 0;
            this.progress = 0;
            this.currentTrack = null;
            this.queue = [];
            this.playHistory = [];
            try {
                sessionStorage.removeItem(this.runtimeStateKey);
            } catch (_) {}
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

            this.persistRuntimeState();
        },

        previousTrack() {
            if (this.playHistory && this.playHistory.length > 0) {
                const previousTrack = this.playHistory.pop();
                this.playTrack(previousTrack, false);
                this.persistRuntimeState();
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
                this.playTrack(nextTrack, true);
                this.persistRuntimeState();
                return;
            }

            if (this.repeatMode === "all" && this.playHistory.length > 0) {
                const nextFromHistory = this.isShuffled
                    ? this.playHistory.splice(
                          Math.floor(Math.random() * this.playHistory.length),
                          1,
                      )[0]
                    : this.playHistory.shift();
                this.playTrack(nextFromHistory, true);
                this.persistRuntimeState();
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
            this.persistRuntimeState();
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
            this.persistRuntimeState();
        },

        toggleShuffle() {
            this.isShuffled = !this.isShuffled;
            this.savePreferences();
            this.persistRuntimeState();
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
        addToQueue(track, silent = false) {
            if (!track?.audioUrl) {
                return;
            }

            this.queue.push(track);
            this.persistRuntimeState();
            if (!silent) {
                this.showQueueNotification("Track added to queue");
            }
        },

        addNextToQueue(track, silent = false) {
            if (!track?.audioUrl) {
                return;
            }

            this.queue.unshift(track);
            this.persistRuntimeState();
            if (!silent) {
                this.showQueueNotification("Track will play next");
            }
        },

        addManyToQueue(tracks, notifyMessage = null) {
            if (!Array.isArray(tracks) || tracks.length === 0) {
                return;
            }

            tracks.forEach((track) => this.addToQueue(track, true));

            const message =
                notifyMessage ||
                `${tracks.length} ${tracks.length === 1 ? "track" : "tracks"} added to queue`;
            this.showQueueNotification(message);
        },

        removeFromQueue(index) {
            this.queue.splice(index, 1);
            this.persistRuntimeState();
        },

        clearQueue() {
            this.queue = [];
            this.persistRuntimeState();
        },

        destroy() {
            if (this.progressTimer) {
                clearInterval(this.progressTimer);
            }
        },
    };
}

export default playerControls;
