@props(['tracks'])

<div class="container mx-auto px-4">
    <h2 class="text-2xl md:text-3xl font-bold mb-6">New Releases</h2>
    
    @if($tracks->isEmpty())
        <p class="text-gray-500">No new releases available at the moment.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($tracks as $track)
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition-transform hover:scale-105">
                    <div class="h-48 bg-gray-200">
                        @if($track->cover_path)
                            <img src="{{ Storage::url($track->cover_path) }}" alt="{{ $track->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                <span class="text-gray-500">No Cover</span>
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1">{{ $track->title }}</h3>
                        <p class="text-sm text-gray-600 mb-2">{{ $track->artist->name ?? 'Unknown Artist' }}</p>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">{{ $track->created_at->diffForHumans() }}</span>
                            <button class="text-indigo-600 hover:text-indigo-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>