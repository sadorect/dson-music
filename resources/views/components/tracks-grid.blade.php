<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4" x-data="tracksGrid()">
    <template x-for="track in tracks">
        <div class="track-card p-3 hover:bg-gray-50 rounded-lg transition">
            <img :src="track.artwork" :alt="track.title" class="w-full aspect-square object-cover rounded-lg mb-2">
            <h4 x-text="track.title" class="font-semibold truncate"></h4>
            <p x-text="track.artist" class="text-sm text-gray-600"></p>
            <button @click="playTrack(track)" class="dson-btn mt-2 w-full">Play</button>
        </div>
    </template>
</div>
