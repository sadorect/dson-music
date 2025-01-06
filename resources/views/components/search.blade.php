<div x-data="search()" class="relative">
    <input 
        type="text" 
        x-model="query"
        @input.debounce.300ms="searchTracks"
        class="w-full md:w-80 search-input"
        placeholder="Search tracks, artists..."
    >
    
    <div x-show="results.length" 
         x-cloak
         class="absolute top-full mt-2 w-full bg-white shadow-xl rounded-lg">
        <template x-for="result in results">
            <div class="p-3 hover:bg-gray-50 cursor-pointer">
                <h5 x-text="result.title" class="font-medium"></h5>
                <p x-text="result.artist" class="text-sm text-gray-600"></p>
            </div>
        </template>
    </div>
</div>
