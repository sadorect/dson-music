window.tracksGrid = function() {
    return {
        tracks: [],
        player: null,
        
        init() {
            this.loadTracks();
            this.initPlayer();
        },
        
        async loadTracks() {
            this.tracks = [
                {
                    id: 1,
                    title: "Classical Symphony",
                    artist: "Mozart Orchestra",
                    artwork: "https://images.pexels.com/photos/3944091/pexels-photo-3944091.jpeg",
                    audioUrl: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3"
                },
                {
                    id: 2,
                    title: "Jazz Fusion",
                    artist: "The Cool Quartet",
                    artwork: "https://images.pexels.com/photos/4087991/pexels-photo-4087991.jpeg",
                    audioUrl: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3"
                },
                {
                    id: 3,
                    title: "Electronic Dreams",
                    artist: "Digital Waves",
                    artwork: "https://images.pexels.com/photos/1763075/pexels-photo-1763075.jpeg",
                    audioUrl: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3"
                },
                {
                    id: 4,
                    title: "Rock Anthem",
                    artist: "The Amplifiers",
                    artwork: "https://images.pexels.com/photos/1389429/pexels-photo-1389429.jpeg",
                    audioUrl: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3"
                },
                {
                    id: 5,
                    title: "Urban Beat",
                    artist: "Street Rhythm",
                    artwork: "https://images.pexels.com/photos/1626481/pexels-photo-1626481.jpeg",
                    audioUrl: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3"
                },
                {
                    id: 6,
                    title: "Acoustic Session",
                    artist: "Wood & Strings",
                    artwork: "https://images.pexels.com/photos/1407322/pexels-photo-1407322.jpeg",
                    audioUrl: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-6.mp3"
                }
            ];
        },
        
        initPlayer() {
            this.player = new Howl({
                src: [],
                html5: true
            });
        },
        
        playTrack(track) {
            if (this.player.playing()) {
                this.player.stop();
            }
            this.player = new Howl({
                src: [track.audioUrl],
                html5: true
            });
            this.player.play();
        }
    }
}
