import "./bootstrap";

/**
 * GrinPlayer — persistent audio singleton.
 * Lives outside the DOM so it survives Livewire wire:navigate page swaps.
 * Alpine's miniPlayer() binds to this object; on each re-init it re-attaches
 * event listeners without interrupting playback.
 */
if (!window.GrinPlayer) {
    window.GrinPlayer = {
        audio: new Audio(),
        track: null, // current track object
        queue: [], // array of track IDs (for dispatch)
        queueTracks: [], // array of full track objects (for display)
        queueIndex: -1,
        volume: 80,
        muted: false,
    };
    window.GrinPlayer.audio.preload = "metadata";
}
