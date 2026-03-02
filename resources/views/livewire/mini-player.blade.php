{{--
    Mini Player — persistent bottom bar
    Audio lives in window.GrinPlayer (JS singleton) — survives wire:navigate DOM swaps.
    Alpine init()/destroy() re-bind/unbind event listeners on each navigation cycle.
--}}
<div
    x-data="miniPlayer()"
    x-init="init()"
    @destroyed="destroy()"
    x-on:player-track-loaded.window="onTrackLoaded($event.detail)"
    x-on:player-queue-add.window="onQueueAdd($event.detail)"
    @start-download.window="
        var a = document.createElement('a');
        a.href = $event.detail.url;
        a.download = $event.detail.filename || 'track.mp3';
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
    "
    class="fixed bottom-0 left-0 right-0 z-50"
>
    {{-- ── Queue Panel (slides up above the bar) ───────────────────────────── --}}
    <div
        x-show="showQueue"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        @click.outside="showQueue = false"
        class="absolute bottom-full w-full sm:w-96 sm:right-4 sm:left-auto left-0 mb-1"
        style="display:none"
    >
        <div class="glass-panel rounded-t-2xl sm:rounded-2xl shadow-2xl border border-white/40 overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-white/30">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10"/>
                    </svg>
                    <span class="text-sm font-semibold text-gray-800">Up Next</span>
                    <span class="text-xs text-gray-400 tabular-nums" x-text="`${queueTracks.length} track${queueTracks.length !== 1 ? 's' : ''}`"></span>
                </div>
                <button @click="clearQueue()"
                        x-show="queueTracks.length > 0"
                        class="text-xs text-gray-400 hover:text-primary-500 transition">
                    Clear all
                </button>
            </div>

            {{-- Track list --}}
            <div class="max-h-72 overflow-y-auto overscroll-contain">
                <template x-if="queueTracks.length === 0">
                    <div class="px-4 py-8 text-center text-gray-400 text-sm">
                        Queue is empty.<br>
                        <span class="text-xs">Click a track to play, or use + to add.</span>
                    </div>
                </template>
                <template x-for="(t, idx) in queueTracks" :key="t.id">
                    <div
                        @click="jumpTo(idx)"
                        class="flex items-center gap-3 px-4 py-2.5 cursor-pointer transition hover:bg-white/50"
                        :class="idx === queueIndex ? 'bg-primary-50/70' : ''"
                    >
                        <div class="w-5 text-center shrink-0">
                            <template x-if="idx === queueIndex">
                                <span class="inline-flex gap-0.5 items-end h-4">
                                    <span class="w-0.5 bg-primary-500 rounded-full animate-bounce" style="height:60%;animation-delay:0ms"></span>
                                    <span class="w-0.5 bg-primary-500 rounded-full animate-bounce" style="height:100%;animation-delay:150ms"></span>
                                    <span class="w-0.5 bg-primary-500 rounded-full animate-bounce" style="height:40%;animation-delay:75ms"></span>
                                </span>
                            </template>
                            <template x-if="idx !== queueIndex">
                                <span class="text-xs text-gray-400 tabular-nums" x-text="idx + 1"></span>
                            </template>
                        </div>
                        <img
                            :src="t.cover || '/images/placeholder-track.svg'"
                            class="w-9 h-9 rounded-lg object-cover shrink-0 ring-1 ring-white/30"
                            :alt="t.title"
                        >
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold truncate"
                               :class="idx === queueIndex ? 'text-primary-600' : 'text-gray-800'"
                               x-text="t.title"></p>
                            <p class="text-xs text-gray-400 truncate" x-text="t.artist"></p>
                        </div>
                        <button @click.stop="removeFromQueue(idx)"
                                class="text-gray-300 hover:text-primary-500 transition shrink-0"
                                title="Remove">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ── Queue-added toast ────────────────────────────────────────────────── --}}
    <div
        x-show="queueToast"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute bottom-full mb-2 right-16 bg-gray-800 text-white text-xs px-3 py-1.5 rounded-full shadow-lg pointer-events-none"
        style="display:none"
    >
        Added to queue
    </div>

    <div
        x-show="track"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        class="glass-mini-player"
        :class="{ 'glass-mini-player-playing': playing }"
        style="display: none;"
    >
        <div class="max-w-7xl mx-auto flex items-center gap-4 flex-wrap sm:flex-nowrap">

            {{-- ── Cover + Info ──────────────────────────────────────────────── --}}
            <div class="flex items-center gap-3 min-w-0 w-44 shrink-0">
                <img
                    :src="track?.cover || '/images/placeholder-track.svg'"
                    :alt="track?.title"
                    class="h-11 w-11 rounded-lg object-cover shadow-sm ring-1 ring-white/30 shrink-0"
                >
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate" x-text="track?.title"></p>
                    <p class="text-xs text-gray-500 truncate" x-text="track?.artist"></p>
                </div>
            </div>

            {{-- ── Controls ──────────────────────────────────────────────────── --}}
            <div class="flex flex-col items-center gap-1 flex-1">
                <div class="flex items-center gap-5">
                    {{-- Prev --}}
                    <button @click="playPrev()"
                            :class="queueIndex > 0 ? 'text-gray-600 hover:text-gray-900' : 'text-gray-300 cursor-default'"
                            class="transition" title="Previous">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M8.445 14.832A1 1 0 0010 14v-2.798l5.445 3.63A1 1 0 0017 14V6a1 1 0 00-1.555-.832L10 8.798V6a1 1 0 00-1.555-.832l-6 4a1 1 0 000 1.664l6 4z"/></svg>
                    </button>

                    {{-- Play / Pause --}}
                    <button
                        @click="togglePlay"
                        class="w-10 h-10 rounded-full bg-primary-500 text-white shadow-lg flex items-center justify-center hover:bg-primary-600 transition active:scale-95"
                        :title="playing ? 'Pause' : 'Play'"
                    >
                        <template x-if="!playing">
                            <svg class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                        </template>
                        <template x-if="playing">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M5.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75A.75.75 0 007.25 3h-1.5zM12.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75a.75.75 0 00-.75-.75h-1.5z"/></svg>
                        </template>
                    </button>

                    {{-- Next --}}
                    <button @click="playNext()"
                            :class="queueIndex < queue.length - 1 ? 'text-gray-600 hover:text-gray-900' : 'text-gray-300 cursor-default'"
                            class="transition" title="Next">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 6a1 1 0 011.555-.832L10 9.202V6a1 1 0 011.555-.832l6 4a1 1 0 010 1.664l-6 4A1 1 0 0110 14v-2.798l-5.445 3.63A1 1 0 013 14V6z"/></svg>
                    </button>
                </div>

                {{-- Progress bar --}}
                <div class="flex items-center gap-2 w-full max-w-md">
                    <span class="text-xs tabular-nums text-gray-500 w-8 text-right" x-text="formatTime(currentTime)"></span>
                    <div class="flex-1 h-1.5 bg-gray-200 rounded-full cursor-pointer relative group" @click="seek($event)">
                        <div class="h-full bg-primary-500 rounded-full transition-all" :style="`width: ${progress}%`"></div>
                        <div class="absolute top-1/2 -translate-y-1/2 w-3 h-3 bg-white border-2 border-primary-500 rounded-full shadow opacity-0 group-hover:opacity-100 transition" :style="`left: calc(${progress}% - 6px)`"></div>
                    </div>
                    <span class="text-xs tabular-nums text-gray-500 w-8" x-text="formatTime(duration)"></span>
                </div>
            </div>

            {{-- ── Like / Share / Download + Volume + Queue toggle ──────────── --}}
            <div class="hidden sm:flex items-center gap-3 shrink-0">

                {{-- Like --}}
                @if($track)
                <button
                    wire:click="likeToggle"
                    class="flex items-center gap-1 transition group"
                    title="{{ $liked ? 'Unlike' : 'Like' }}"
                >
                    <svg class="w-4 h-4 transition {{ $liked ? 'text-primary fill-current' : 'text-gray-400 group-hover:text-primary' }}"
                         viewBox="0 0 24 24" fill="{{ $liked ? 'currentColor' : 'none' }}" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span class="text-xs tabular-nums {{ $liked ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                          wire:key="like-count-{{ $trackId }}">{{ $likesCount > 0 ? $likesCount : '' }}</span>
                </button>

                {{-- Share --}}
                <button
                    x-data="{ copied: false }"
                    @click="
                        if (track?.url) {
                            navigator.clipboard.writeText(window.location.origin + track.url).then(() => {
                                copied = true; setTimeout(() => copied = false, 2000);
                            });
                        }
                    "
                    class="relative text-gray-400 hover:text-primary transition"
                    :title="copied ? 'Link copied!' : 'Share track'"
                >
                    <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                    <svg x-show="copied" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>

                {{-- Download --}}
                <button
                    wire:click="downloadTrack"
                    class="text-gray-400 hover:text-primary transition"
                    title="Download"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </button>

                <div class="w-px h-4 bg-gray-200 mx-1"></div>
                @endif
                <div class="flex items-center gap-2 w-28">
                    <button @click="toggleMute()" class="text-gray-500 hover:text-gray-800 transition">
                        <template x-if="muted || volume === 0">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.547 3.062A.75.75 0 0110 3.75v12.5a.75.75 0 01-1.264.546L4.703 13H3.167a.75.75 0 01-.7-.48A6.985 6.985 0 012 10c0-.887.165-1.737.468-2.52a.75.75 0 01.7-.48h1.535l4.033-3.796a.75.75 0 01.811-.142zM14.78 7.22a.75.75 0 00-1.06 1.06L14.94 9.5l-1.22 1.22a.75.75 0 001.06 1.06l1.22-1.22 1.22 1.22a.75.75 0 101.06-1.06L17.06 9.5l1.22-1.22a.75.75 0 10-1.06-1.06L16 8.44l-1.22-1.22z"/></svg>
                        </template>
                        <template x-if="!muted && volume > 0">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.547 3.062A.75.75 0 0110 3.75v12.5a.75.75 0 01-1.264.546L4.703 13H3.167a.75.75 0 01-.7-.48A6.985 6.985 0 012 10c0-.887.165-1.737.468-2.52a.75.75 0 01.7-.48h1.535l4.033-3.796a.75.75 0 01.811-.142zM13.28 6.22a.75.75 0 10-1.06 1.06A3.5 3.5 0 0113.5 10a3.5 3.5 0 01-1.28 2.72.75.75 0 101.06 1.06A5 5 0 0015 10a5 5 0 00-1.72-3.78zM16.72 4.22a.75.75 0 00-1.06 1.06A6.5 6.5 0 0117.5 10a6.5 6.5 0 01-1.84 4.72.75.75 0 101.06 1.06A8 8 0 0019 10a8 8 0 00-2.28-5.78z"/></svg>
                        </template>
                    </button>
                    <input
                        type="range" min="0" max="100"
                        x-model="volume"
                        @input="setVolume()"
                        class="w-full accent-primary-500 h-1 cursor-pointer"
                    >
                </div>

                {{-- Queue toggle button --}}
                <button
                    @click="showQueue = !showQueue"
                    :class="showQueue ? 'text-primary-500 bg-primary-50' : 'text-gray-500 hover:text-gray-800'"
                    class="relative p-1.5 rounded-lg transition"
                    title="Queue"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10"/>
                    </svg>
                    <span x-show="queue.length > 1"
                          class="absolute -top-1 -right-1 bg-primary-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center leading-none"
                          x-text="queue.length"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function miniPlayer() {
    const P = window.GrinPlayer; // singleton — survives navigate

    return {
        // ── Reactive state (synced from singleton on init) ───────────────────────
        track:       P.track,
        playing:     false,
        currentTime: Math.floor(P.audio.currentTime) || 0,
        duration:    0,
        volume:      P.volume,
        muted:       P.muted,
        queue:       P.queue,
        queueTracks: P.queueTracks,
        queueIndex:  P.queueIndex,
        showQueue:   false,
        queueToast:  false,
        _toastTimer: null,
        _handlers:   {},

        get progress() {
            if (!this.duration) return 0;
            return (this.currentTime / this.duration) * 100;
        },

        // ── Lifecycle ──────────────────────────────────────────────────
        init() {
            this.playing  = !P.audio.paused && !!P.audio.src && !P.audio.ended;
            this.duration = Math.floor(P.audio.duration) || 0;
            P.audio.volume = this.volume / 100;
            P.audio.muted  = this.muted;

            this._handlers = {
                timeupdate:     () => { this.currentTime = Math.floor(P.audio.currentTime); },
                loadedmetadata: () => { this.duration = Math.floor(P.audio.duration) || 0; },
                play:           () => { this.playing = true; },
                pause:          () => { this.playing = false; },
                ended:          () => {
                    this.playing = false;
                    this.currentTime = 0;
                    const nextIdx = this.queueIndex + 1;
                    if (nextIdx < this.queue.length) {
                        Livewire.dispatch('play-track', { id: this.queue[nextIdx] });
                    }
                },
            };
            for (const [evt, fn] of Object.entries(this._handlers)) {
                P.audio.addEventListener(evt, fn);
            }
        },

        destroy() {
            for (const [evt, fn] of Object.entries(this._handlers)) {
                P.audio.removeEventListener(evt, fn);
            }
        },

        // ── Track loading ───────────────────────────────────────────────
        onTrackLoaded(detail) {
            const t = detail?.track ?? (Array.isArray(detail) ? detail[0] : detail);
            if (!t || !t.audio_url) {
                console.warn('GrinMusic: track has no audio URL, skipping playback.');
                return;
            }
            const existingIdx = this.queue.indexOf(t.id);
            if (existingIdx !== -1) {
                this.queueIndex = existingIdx;
            } else {
                this.queue.push(t.id);
                this.queueTracks.push(t);
                this.queueIndex = this.queue.length - 1;
            }
            P.queue       = this.queue;
            P.queueTracks = this.queueTracks;
            P.queueIndex  = this.queueIndex;
            P.track       = t;
            this.track    = t;

            P.audio.src           = t.audio_url;
            P.audio.volume        = this.volume / 100;
            P.audio.currentTime   = 0;
            P.audio.play()
                .then(() => { this.playing = true; })
                .catch(err => {
                    console.error('Audio play failed:', err.message);
                    this.playing = false;
                });
        },

        // ── Queue operations ──────────────────────────────────────────────
        onQueueAdd(detail) {
            const t = detail?.track ?? (Array.isArray(detail) ? detail[0] : detail);
            if (!t || !t.id) return;
            if (!this.queue.includes(t.id)) {
                this.queue.push(t.id);
                this.queueTracks.push(t);
                P.queue       = this.queue;
                P.queueTracks = this.queueTracks;
                this.queueToast = true;
                clearTimeout(this._toastTimer);
                this._toastTimer = setTimeout(() => { this.queueToast = false; }, 2000);
            }
        },

        jumpTo(idx) {
            if (idx < 0 || idx >= this.queue.length) return;
            Livewire.dispatch('play-track', { id: this.queue[idx] });
        },

        playNext() {
            const nextIdx = this.queueIndex + 1;
            if (nextIdx < this.queue.length) {
                Livewire.dispatch('play-track', { id: this.queue[nextIdx] });
            }
        },

        playPrev() {
            if (this.currentTime > 3) { P.audio.currentTime = 0; return; }
            const prevIdx = this.queueIndex - 1;
            if (prevIdx >= 0) {
                Livewire.dispatch('play-track', { id: this.queue[prevIdx] });
            } else {
                P.audio.currentTime = 0;
            }
        },

        removeFromQueue(idx) {
            const nextId = this.queue[idx + 1];
            const wasPlaying = idx === this.queueIndex;
            this.queue.splice(idx, 1);
            this.queueTracks.splice(idx, 1);
            if (wasPlaying) {
                if (nextId) {
                    this.queueIndex = Math.min(idx, this.queue.length - 1);
                    Livewire.dispatch('play-track', { id: nextId });
                } else {
                    this.queueIndex = Math.max(0, idx - 1);
                    P.audio.pause();
                }
            } else if (idx < this.queueIndex) {
                this.queueIndex--;
            }
            P.queue       = this.queue;
            P.queueTracks = this.queueTracks;
            P.queueIndex  = this.queueIndex;
        },

        clearQueue() {
            P.audio.pause();
            this.playing = false; this.track = null;
            this.queue = []; this.queueTracks = []; this.queueIndex = -1;
            P.track = null; P.queue = []; P.queueTracks = []; P.queueIndex = -1;
            this.showQueue = false;
        },

        // ── Playback controls ─────────────────────────────────────────────
        togglePlay() {
            if (!this.track) return;
            if (this.playing) {
                P.audio.pause();
            } else {
                P.audio.play()
                    .then(() => { this.playing = true; })
                    .catch(err => { console.error('Play failed:', err.message); });
            }
        },

        toggleMute() {
            this.muted = !this.muted;
            P.audio.muted = this.muted; P.muted = this.muted;
        },

        setVolume() {
            P.audio.volume = this.volume / 100; P.volume = this.volume;
            if (this.volume > 0) { this.muted = false; P.audio.muted = false; P.muted = false; }
        },

        seek(ev) {
            const rect  = ev.currentTarget.getBoundingClientRect();
            const ratio = (ev.clientX - rect.left) / rect.width;
            P.audio.currentTime = ratio * this.duration;
        },

        formatTime(seconds) {
            if (!seconds || isNaN(seconds)) return '0:00';
            const m = Math.floor(seconds / 60);
            const s = Math.floor(seconds % 60);
            return `${m}:${s.toString().padStart(2, '0')}`;
        },
    };
}
</script>
