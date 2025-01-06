<div class="dson-player fixed bottom-0 w-full bg-black text-white">
    <div class="container mx-auto flex items-center justify-between p-4">
        <div class="track-info flex items-center">
            <img id="track-artwork" class="w-16 h-16 rounded" src="" alt="Track Artwork">
            <div class="ml-4">
                <h4 id="track-title" class="text-lg font-bold"></h4>
                <p id="track-artist" class="text-sm text-gray-400"></p>
            </div>
        </div>
        
        <div class="player-controls flex items-center">
            <button class="dson-btn-player mx-2">Previous</button>
            <button class="dson-btn-player mx-2">Play/Pause</button>
            <button class="dson-btn-player mx-2">Next</button>
        </div>
        
        <div class="volume-control flex items-center">
            <input type="range" min="0" max="100" value="100" class="w-32">
        </div>
    </div>
</div>
