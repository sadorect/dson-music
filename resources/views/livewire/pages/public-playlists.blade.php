<?php

use App\Models\Playlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.glass-app')] class extends Component
{
    use WithPagination;

    public function with(): array
    {
        /** @var LengthAwarePaginator $playlists */
        $playlists = Playlist::query()
            ->with(['user', 'media'])
            ->withCount('tracks')
            ->where('is_public', true)
            ->latest('updated_at')
            ->paginate(18);

        $featured = collect($playlists->items())->take(3);

        return [
            'playlists' => $playlists,
            'featured' => $featured,
            'stats' => [
                'playlists' => Playlist::where('is_public', true)->count(),
                'tracks' => (int) Playlist::where('is_public', true)->sum('tracks_count'),
                'updatedToday' => Playlist::where('is_public', true)->whereDate('updated_at', today())->count(),
            ],
        ];
    }
};
?>

<div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="glass-card relative overflow-hidden rounded-[2rem] p-6 sm:p-8">
            <div class="absolute inset-y-0 right-0 w-1/3 bg-gradient-to-l from-primary/10 to-transparent"></div>
            <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Public Library</p>
                    <h1 class="text-3xl font-black tracking-tight text-gray-900 sm:text-5xl">Public playlists from the community</h1>
                    <p class="mt-3 max-w-2xl text-sm text-gray-500 sm:text-base">Explore listener-made collections, open any playlist page, and jump straight into curated moods, moments, and discoveries.</p>
                </div>

                <div class="grid grid-cols-3 gap-3 sm:gap-4">
                    @foreach([
                        ['label' => 'Playlists', 'value' => number_format($stats['playlists'])],
                        ['label' => 'Tracks', 'value' => number_format($stats['tracks'])],
                        ['label' => 'Updated Today', 'value' => number_format($stats['updatedToday'])],
                    ] as $metric)
                        <div class="rounded-2xl border border-white/60 bg-white/80 px-4 py-3 text-center shadow-sm backdrop-blur-sm">
                            <p class="text-lg font-black text-gray-900 sm:text-2xl">{{ $metric['value'] }}</p>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-400">{{ $metric['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        @if($featured->isNotEmpty())
            <section class="grid gap-4 lg:grid-cols-3">
                @foreach($featured as $playlist)
                    <article class="glass-card overflow-hidden rounded-[2rem] transition hover:-translate-y-0.5 hover:bg-white/90 hover:shadow-lg">
                        <a href="{{ route('playlist.show', $playlist->slug) }}" wire:navigate class="block">
                            <div class="aspect-[1.15/1] overflow-hidden bg-gradient-to-br from-primary-100 to-primary-200">
                                @if($playlist->getCoverUrl())
                                    <img src="{{ $playlist->getCoverUrl() }}" alt="{{ $playlist->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <svg class="h-14 w-14 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h10"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-5">
                                <div class="flex items-center gap-2">
                                    <h2 class="truncate text-lg font-black tracking-tight text-gray-900">{{ $playlist->name }}</h2>
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Public</span>
                                </div>
                                <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ $playlist->description ?: 'Open playlist by '.$playlist->user?->name.' with curated tracks ready to explore.' }}</p>
                                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-gray-400">
                                    <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">{{ $playlist->tracks_count }} tracks</span>
                                    <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">By {{ $playlist->user?->name ?? 'Unknown' }}</span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </section>
        @endif

        <section class="glass-card overflow-hidden rounded-[2rem]">
            @if($playlists->isEmpty())
                <div class="px-6 py-20 text-center">
                    <p class="text-lg font-semibold text-gray-800">No public playlists yet</p>
                    <p class="mt-2 text-sm text-gray-500">Once listeners publish their playlists, they will appear here.</p>
                </div>
            @else
                <div class="grid gap-4 p-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($playlists as $playlist)
                        <article class="glass-card rounded-[1.7rem] p-4 transition hover:-translate-y-0.5 hover:bg-white/90 hover:shadow-lg">
                            <a href="{{ route('playlist.show', $playlist->slug) }}" wire:navigate class="block">
                                <div class="flex gap-4">
                                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                                        @if($playlist->getCoverUrl())
                                            <img src="{{ $playlist->getCoverUrl() }}" alt="{{ $playlist->name }}" class="h-full w-full object-cover">
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="truncate text-base font-semibold text-gray-900">{{ $playlist->name }}</h3>
                                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Public</span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">By {{ $playlist->user?->name ?? 'Unknown' }}</p>
                                        <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ $playlist->description ?: 'Curated tracks collected and shared with the community.' }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-gray-400">
                                    <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">{{ $playlist->tracks_count }} tracks</span>
                                    <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">Updated {{ $playlist->updated_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>

                @if($playlists->hasPages())
                    <div class="border-t border-white/40 px-6 py-4">{{ $playlists->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</div>
