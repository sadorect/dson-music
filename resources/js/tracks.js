window.tracksGrid = function () {
    return {
        tracks: [],

        init() {
            this.loadTracks();
        },

        async loadTracks() {
            try {
                const response = await fetch("/tracks/public");
                this.tracks = await response.json();
            } catch (error) {
                console.log("Error loading tracks:", error);
            }
        },

        playTrack(track) {
            window.dispatchEvent(
                new CustomEvent("track:play", { detail: track }),
            );
        },
    };
};

// Global playTrack function for track-card click handler
window.playTrack = async (trackId) => {
    // First find the tracks grid component
    const tracksGridElement = document.querySelector('[x-data="tracksGrid()"]');

    if (!tracksGridElement || !tracksGridElement.__x) {
        console.log("Tracks grid component not initialized");
        return;
    }

    const tracksComponent = tracksGridElement.__x.$data;
    const track = tracksComponent.tracks.find((t) => t.id === trackId);

    if (track) {
        tracksComponent.playTrack(track);
    }
};
