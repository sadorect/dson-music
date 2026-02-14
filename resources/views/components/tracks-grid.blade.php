<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4" x-data="tracksGrid()">
    <template x-for="track in tracks">
        <div class="track-card p-2 sm:p-3 hover:bg-black/10 rounded-lg transition">
            <img :src="track.artwork" :alt="track.title" class="w-full aspect-square object-cover rounded-lg mb-2">
            <h4 x-text="track.title" class="font-semibold text-white truncate"></h4>
            <p x-text="track.artist" class="text-xs sm:text-sm text-white/60 truncate"></p>
            <button @click="playTrack(track)" class="dson-btn mt-2 w-full text-sm">Play</button>
        </div>
    </template>
</div>
