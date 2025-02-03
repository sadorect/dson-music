<div x-show="showQueue" class="fixed right-0 top-0 h-full w-80 bg-white shadow-lg p-4">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold">Queue</h3>
        <button @click="clearQueue" class="text-red-600">Clear All</button>
    </div>
    
    <div class="queue-list space-y-2">
        <template x-for="(track, index) in queue" :key="index">
            <div class="flex items-center gap-2 p-2 bg-gray-50 rounded" draggable="true" 
                 @dragstart="dragStart($event, index)" 
                 @dragover.prevent 
                 @drop="drop($event, index)">
                <img :src="track.artwork" class="w-10 h-10 rounded">
                <div class="flex-1">
                    <p x-text="track.title" class="font-medium"></p>
                    <p x-text="track.artist" class="text-sm text-gray-600"></p>
                </div>
                <button @click="removeFromQueue(index)" class="text-gray-400 hover:text-red-600">×</button>
            </div>
        </template>
    </div>
</div>
