<?php

use App\Models\Genre;
use App\Models\Track;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

new #[Layout('layouts.glass-app')] class extends Component {
    use WithPagination;

    #[Url]
    public ?string $genre = null;

    #[Url]
    public string $sort = 'latest'; // latest | popular

    public function with(): array
    {
        $query = Track::with(['artistProfile.user', 'genre'])
            ->where('is_published', true)
            ->when($this->genre, function ($q) {
                $q->whereHas('genre', fn($g) => $g->where('slug', $this->genre));
            });

        if ($this->sort === 'popular') {
            $query->orderByDesc('play_count');
        } else {
            $query->orderByDesc('created_at');
        }

        return [
            'genres' => Genre::where('is_active', true)->orderBy('sort_order')->get(),
            'tracks' => $query->paginate(24),
        ];
    }

    public function setGenre(?string $slug): void
    {
        $this->genre = $slug;
        $this->resetPage();
    }

    public function setSort(string $value): void
    {
        $this->sort = $value;
        $this->resetPage();
    }
};
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    {{-- Page header --}}
    <div class="max-w-7xl mx-auto mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Browse Music</h1>
        <p class="text-gray-500 mt-1">Discover tracks from independent artists</p>
    </div>

    {{-- Genre filter chips --}}
    <div class="max-w-7xl mx-auto mb-6 flex flex-wrap gap-2">
        <button
            wire:click="setGenre(null)"
            class="px-4 py-1.5 rounded-full text-sm font-medium transition
                {{ is_null($genre) ? 'bg-red-500 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:border-red-300 hover:text-red-500' }}">
            All
        </button>
        @foreach($genres as $g)
            <button
                wire:click="setGenre('{{ $g->slug }}')"
                class="px-4 py-1.5 rounded-full text-sm font-medium transition
                    {{ $genre === $g->slug ? 'bg-red-500 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:border-red-300 hover:text-red-500' }}">
                {{ $g->name }}
            </button>
        @endforeach
    </div>

    {{-- Sort toggle --}}
    <div class="max-w-7xl mx-auto mb-6 flex gap-3 items-center">
        <span class="text-gray-500 text-sm">Sort by:</span>
        <button wire:click="setSort('latest')"
                class="text-sm {{ $sort === 'latest' ? 'text-red-500 font-semibold' : 'text-gray-500 hover:text-gray-800' }}">
            Latest
        </button>
        <button wire:click="setSort('popular')"
                class="text-sm {{ $sort === 'popular' ? 'text-red-500 font-semibold' : 'text-gray-500 hover:text-gray-800' }}">
            Popular
        </button>
    </div>

    {{-- Track grid --}}
    <div class="max-w-7xl mx-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @forelse($tracks as $track)
            <div class="group glass-card rounded-xl overflow-hidden transition cursor-pointer hover:shadow-md"
                 @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })">
                <div class="relative aspect-square">
                    @if($track->getFirstMediaUrl('cover'))
                        <img src="{{ $track->getFirstMediaUrl('cover', 'thumb') }}"
                             alt="{{ $track->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-red-100 to-rose-200 flex items-center justify-center">
                            <svg class="w-10 h-10 text-red-300" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/>
                            </svg>
                        </div>
                    @endif
                    {{-- Play overlay --}}
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                        <svg class="w-10 h-10 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                    {{-- Add to Queue button (top-left, visible on hover) --}}
                    <button
                        @click.stop="Livewire.dispatch('queue-track', { id: {{ $track->id }} })"
                        title="Add to queue"
                        class="absolute top-1.5 left-1.5 opacity-0 group-hover:opacity-100 transition
                               bg-black/60 hover:bg-black/80 text-white rounded-full w-6 h-6 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                    @if($track->requires_donation)
                        <span class="absolute top-1.5 right-1.5 bg-yellow-500 text-black text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                            ${{ number_format($track->donation_amount, 2) }}
                        </span>
                    @endif
                </div>
                <div class="p-2">
                    <p class="text-gray-800 text-xs font-semibold truncate">{{ $track->title }}</p>
                    <a href="{{ route('artist.page', $track->artistProfile) }}"
                       @click.stop
                       class="text-gray-500 text-xs hover:text-red-500 truncate block">
                        {{ $track->artistProfile->stage_name ?? $track->artistProfile->user->name }}
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-gray-400">
                <p class="text-lg">No tracks found.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="max-w-7xl mx-auto mt-8">
        {{ $tracks->links() }}
    </div>
</div>
