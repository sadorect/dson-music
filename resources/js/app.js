import "./bootstrap";
import "./tracks.js";
import Alpine from "alpinejs";
import "./search";
import playerControls from "./components/player";
import { submitComment } from "./comments";

window.submitComment = submitComment;

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
