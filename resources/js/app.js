import "./bootstrap";
import "./tracks.js";
import Alpine from "alpinejs";
import "./search";
import playerControls from "./components/player";
import { submitComment } from "./comments";

window.submitComment = submitComment;

window.libraryActions = {
    notify(message, type = "success") {
        const notification = document.createElement("div");
        notification.className =
            "fixed bottom-24 right-4 z-50 px-4 py-2 rounded-lg text-sm shadow-lg " +
            (type === "error"
                ? "bg-red-600 text-white"
                : "bg-black/85 text-white");
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => notification.remove(), 2200);
    },

    async addTrackToPlaylist(trackId, playlistId) {
        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]',
        )?.content;

        if (!csrfToken) {
            this.notify("Unable to add track right now.", "error");
            return;
        }

        try {
            const response = await fetch(`/playlists/${playlistId}/tracks`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ track_id: trackId }),
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                this.notify(
                    payload.message || "Could not add track to playlist.",
                    "error",
                );
                return;
            }

            this.notify(payload.message || "Track added to playlist!");
        } catch (_) {
            this.notify("Could not add track to playlist.", "error");
        }
    },

    async share(url, title = "") {
        try {
            if (navigator.share) {
                await navigator.share({
                    title: title || "GRIN Music",
                    url,
                });
                return;
            }

            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(url);
                this.notify("Link copied to clipboard.");
                return;
            }

            const textarea = document.createElement("textarea");
            textarea.value = url;
            textarea.setAttribute("readonly", "");
            textarea.style.position = "fixed";
            textarea.style.opacity = "0";
            document.body.appendChild(textarea);
            textarea.select();
            const copied = document.execCommand("copy");
            textarea.remove();

            if (copied) {
                this.notify("Link copied to clipboard.");
                return;
            }

            this.notify("Sharing is not supported on this device.", "error");
        } catch (_) {
            this.notify("Could not share this item.", "error");
        }
    },

    async toggleTrackLike(trackId) {
        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]',
        )?.content;

        if (!csrfToken) {
            this.notify("Unable to update like right now.", "error");
            return { success: false };
        }

        try {
            const response = await fetch(`/tracks/${trackId}/like`, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                this.notify(
                    payload.message || "Could not update like.",
                    "error",
                );
                return { success: false };
            }

            this.notify(payload.message || "Like updated.");
            return {
                success: true,
                likesCount: payload.likes_count,
                message: payload.message,
            };
        } catch (_) {
            this.notify("Could not update like.", "error");
            return { success: false };
        }
    },

    queueTracks(tracks, notifyMessage = null) {
        if (!Array.isArray(tracks) || tracks.length === 0) {
            this.notify("No tracks available.", "error");
            return;
        }

        window.dispatchEvent(
            new CustomEvent("queue:add-many", {
                detail: {
                    tracks,
                    notifyMessage,
                },
            }),
        );
    },

    playPlaylist(tracks) {
        if (!Array.isArray(tracks) || tracks.length === 0) {
            this.notify("No tracks available.", "error");
            return;
        }

        const [firstTrack, ...remainingTracks] = tracks;
        window.dispatchEvent(
            new CustomEvent("track:play", {
                detail: firstTrack,
            }),
        );

        if (remainingTracks.length > 0) {
            this.queueTracks(
                remainingTracks,
                `${remainingTracks.length} ${remainingTracks.length === 1 ? "track" : "tracks"} queued`,
            );
        }
    },
};

window.playlistReorder = (initialTracks, reorderUrl) => ({
    tracks: Array.isArray(initialTracks) ? initialTracks : [],
    draggingIndex: null,
    isSaving: false,
    async saveOrder() {
        if (this.isSaving) {
            return;
        }

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]',
        )?.content;
        if (!csrfToken) {
            window.libraryActions.notify("Unable to save order.", "error");
            return;
        }

        this.isSaving = true;
        try {
            const response = await fetch(reorderUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    track_ids: this.tracks.map((track) => track.id),
                }),
            });

            if (!response.ok) {
                window.libraryActions.notify(
                    "Could not save track order.",
                    "error",
                );
                return;
            }

            window.libraryActions.notify("Playlist order saved.");
        } catch (_) {
            window.libraryActions.notify(
                "Could not save track order.",
                "error",
            );
        } finally {
            this.isSaving = false;
        }
    },
    moveTrack(fromIndex, toIndex) {
        if (fromIndex === toIndex || fromIndex === null || toIndex === null) {
            return;
        }

        const [moved] = this.tracks.splice(fromIndex, 1);
        this.tracks.splice(toIndex, 0, moved);
        this.draggingIndex = null;
    },
});

// Register playerControls as an Alpine data component
document.addEventListener("alpine:init", () => {
    Alpine.data("playerControls", playerControls);
    Alpine.data("searchBar", () => ({
        query: "",
        results: [],
        async search() {
            const trimmed = this.query.trim();
            if (trimmed.length < 2) {
                this.results = [];
                return;
            }

            try {
                const response = await fetch(
                    `/search/quick?q=${encodeURIComponent(trimmed)}`,
                    {
                        headers: {
                            Accept: "application/json",
                        },
                    },
                );

                if (!response.ok) {
                    this.results = [];
                    return;
                }

                const data = await response.json();
                const tracks = Array.isArray(data.tracks)
                    ? data.tracks.map((track) => ({ ...track, type: "track" }))
                    : [];
                const artists = Array.isArray(data.artists)
                    ? data.artists.map((artist) => ({
                          ...artist,
                          type: "artist",
                      }))
                    : [];

                this.results = [...tracks, ...artists].slice(0, 5);
            } catch (error) {
                console.error("Search failed:", error);
                this.results = [];
            }
        },
    }));
});

window.Alpine = Alpine;
Alpine.start();
