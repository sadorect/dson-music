<?php

use App\Models\ArtistProfile;
use App\Models\Track;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component {
    #[Url]
    public string $q = '';

    public string $tab = 'tracks';

    public function with(): array
    {
        $tracks = collect();
        $artists = collect();

        if (strlen($this->q) >= 2) {
            if ($this->tab === 'tracks') {
                $tracks = Track::search($this->q)
                    ->query(fn ($query) => $query->with(['artistProfile.user', 'genre'])->where('is_published', true))
                    ->get()
                    ->take(30);
            } else {
                $artists = ArtistProfile::search($this->q)
                    ->query(fn ($query) => $query->with('user')->where('is_approved', true))
                    ->get()
                    ->take(30);
            }
        }

        return compact('tracks', 'artists');
    }

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
    }
};
?>

<div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl space-y-6">
        <section class="glass-card rounded-[2rem] p-6 sm:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Search Library</p>
                    <h1 class="text-3xl font-black tracking-tight text-gray-900 sm:text-4xl">Find tracks and artists fast</h1>
                    <p class="mt-2 text-sm text-gray-500 sm:text-base">Search the catalog, jump into playback, or open an artist profile directly from the results.</p>
                </div>

                <div class="flex items-center gap-2 rounded-full border border-white/60 bg-white/70 p-1 shadow-sm">
                    <button
                        wire:click="switchTab('tracks')"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $tab === 'tracks' ? 'bg-primary text-white shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                        Tracks
                    </button>
                    <button
                        wire:click="switchTab('artists')"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $tab === 'artists' ? 'bg-primary text-white shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                        Artists
                    </button>
                </div>
            </div>

            <div class="relative mt-6">
                <svg class="pointer-events-none absolute left-5 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                <input
                    wire:model.live.debounce.300ms="q"
                    type="search"
                    placeholder="Search tracks, artists..."
                    autofocus
                    class="w-full rounded-[1.6rem] border border-white/60 bg-white/85 py-4 pl-14 pr-5 text-base text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-primary/40 focus:ring-4 focus:ring-primary/10 sm:text-lg">
            </div>

            @if(strlen($q) >= 2)
                <div class="mt-4 flex items-center gap-2 text-sm text-gray-500">
                    <span class="inline-flex h-2 w-2 rounded-full bg-primary"></span>
                    <span>Showing {{ $tab }} results for</span>
                    <span class="font-semibold text-gray-800">"{{ $q }}"</span>
                </div>
            @endif
        </section>

        @if(strlen($q) < 2)
            <section class="glass-card rounded-[2rem] px-6 py-16 text-center sm:px-8">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                </div>
                <h2 class="mt-5 text-xl font-bold text-gray-900">Start typing to search</h2>
                <p class="mt-2 text-sm text-gray-500">Use at least two characters to search the catalog.</p>
            </section>
        @elseif($tab === 'tracks')
            <section wire:loading.class="opacity-60" class="space-y-3 transition-opacity">
                @forelse($tracks as $track)
                    <div class="glass-card group flex items-center gap-4 rounded-[1.6rem] p-4 transition hover:-translate-y-0.5 hover:bg-white/85 hover:shadow-lg"
                         @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })">
                        <div class="relative h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200">
                            @if($track->getFirstMediaUrl('cover'))
                                <img src="{{ $track->getFirstMediaUrl('cover', 'thumb') }}" alt="{{ $track->title }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <svg class="h-6 w-6 text-primary-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/></svg>
                                </div>
                            @endif
                            <div class="absolute inset-0 flex items-center justify-center bg-black/35 opacity-0 transition group-hover:opacity-100">
                                <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-base font-semibold text-gray-900">{{ $track->title }}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                                <span class="truncate">{{ $track->artistProfile->stage_name ?? $track->artistProfile->user->name }}</span>
                                @if($track->genre)
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ $track->genre->name }}</span>
                                @endif
                                <x-track-duration :track="$track" class="text-gray-400" />
                            </div>
                        </div>

                        <div class="hidden shrink-0 items-center gap-3 sm:flex">
                            @if($track->requires_donation)
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                    ${{ number_format($track->donation_amount, 2) }}
                                </span>
                            @endif
                            @livewire('like-button', ['trackId' => $track->id], key('search-like-'.$track->id))
                        </div>
                    </div>
                @empty
                    <div class="glass-card rounded-[2rem] px-6 py-14 text-center">
                        <p class="text-lg font-semibold text-gray-800">No tracks found</p>
                        <p class="mt-2 text-sm text-gray-500">Nothing matched "{{ $q }}". Try another title, artist, or spelling.</p>
                    </div>
                @endforelse
            </section>
        @else
            <section wire:loading.class="opacity-60" class="grid grid-cols-2 gap-4 transition-opacity sm:grid-cols-3 lg:grid-cols-4">
                @forelse($artists as $artist)
                    <a href="{{ route('artist.page', $artist) }}"
                       class="glass-card flex flex-col items-center gap-3 rounded-[1.6rem] p-5 text-center transition hover:-translate-y-0.5 hover:bg-white/85 hover:shadow-lg">
                        <div class="h-24 w-24 overflow-hidden rounded-full bg-gradient-to-br from-primary-100 to-primary-200 ring-2 ring-white/80 shadow-sm">
                            @if($artist->getFirstMediaUrl('avatar'))
                                <img src="{{ $artist->getFirstMediaUrl('avatar', 'thumb') }}" alt="{{ $artist->stage_name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-2xl font-black text-primary-400">
                                    {{ strtoupper(substr($artist->stage_name ?? $artist->user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $artist->stage_name ?? $artist->user->name }}</p>
                            <p class="mt-1 text-xs {{ $artist->is_verified ? 'text-primary' : 'text-gray-400' }}">
                                {{ $artist->is_verified ? 'Verified Artist' : 'Artist Profile' }}
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="glass-card col-span-full rounded-[2rem] px-6 py-14 text-center">
                        <p class="text-lg font-semibold text-gray-800">No artists found</p>
                        <p class="mt-2 text-sm text-gray-500">Nothing matched "{{ $q }}". Try another artist name or spelling.</p>
                    </div>
                @endforelse
            </section>
        @endif
    </div>
</div>
