<?php

use App\Models\ArtistProfile;
use App\Models\Track;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

new #[Layout('layouts.glass-app')] class extends Component {

    #[Url]
    public string $q = '';

    public string $tab = 'tracks'; // tracks | artists

    public function with(): array
    {
        $tracks  = collect();
        $artists = collect();

        if (strlen($this->q) >= 2) {
            if ($this->tab === 'tracks') {
                $tracks = Track::search($this->q)
                    ->query(fn($query) => $query->with(['artistProfile.user', 'genre'])->where('is_published', true))
                    ->get()
                    ->take(30);
            } else {
                $artists = ArtistProfile::search($this->q)
                    ->query(fn($query) => $query->with('user')->where('is_approved', true))
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

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        {{-- Search input --}}
        <div class="relative mb-8">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input
                wire:model.live.debounce.300ms="q"
                type="search"
                placeholder="Search tracks, artists…"
                autofocus
                class="w-full bg-white/10 border border-white/20 rounded-2xl py-4 pl-12 pr-4 text-white placeholder-white/40 focus:outline-none focus:border-purple-400 text-lg">
        </div>

        {{-- Tabs --}}
        <div class="flex gap-4 mb-6 border-b border-white/10 pb-1">
            <button wire:click="switchTab('tracks')"
                    class="text-sm font-semibold pb-2 border-b-2 transition {{ $tab === 'tracks' ? 'border-purple-400 text-white' : 'border-transparent text-white/50 hover:text-white' }}">
                Tracks
            </button>
            <button wire:click="switchTab('artists')"
                    class="text-sm font-semibold pb-2 border-b-2 transition {{ $tab === 'artists' ? 'border-purple-400 text-white' : 'border-transparent text-white/50 hover:text-white' }}">
                Artists
            </button>
        </div>

        {{-- Empty / prompt state --}}
        @if(strlen($q) < 2)
            <div class="py-20 text-center text-white/40">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                <p>Start typing to search…</p>
            </div>

        {{-- Tracks results --}}
        @elseif($tab === 'tracks')
            <div wire:loading.class="opacity-50" class="space-y-2 transition-opacity">
                @forelse($tracks as $track)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 hover:bg-white/10 cursor-pointer transition"
                         @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })">
                        <div class="w-12 h-12 shrink-0 rounded-lg overflow-hidden bg-white/5">
                            @if($track->getFirstMediaUrl('cover'))
                                <img src="{{ $track->getFirstMediaUrl('cover', 'thumb') }}" alt="{{ $track->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-purple-800 to-indigo-900 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white/30" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-medium truncate">{{ $track->title }}</p>
                            <p class="text-white/50 text-sm truncate">{{ $track->artistProfile->stage_name ?? $track->artistProfile->user->name }}</p>
                        </div>
                        @if($track->requires_donation)
                            <span class="shrink-0 text-xs bg-yellow-500/20 text-yellow-300 px-2 py-0.5 rounded-full">
                                ${{ number_format($track->donation_amount, 2) }}
                            </span>
                        @endif
                        @livewire('like-button', ['trackId' => $track->id], key('like-'.$track->id))
                    </div>
                @empty
                    <p class="py-10 text-center text-white/40">No tracks found for "{{ $q }}"</p>
                @endforelse
            </div>

        {{-- Artist results --}}
        @else
            <div wire:loading.class="opacity-50" class="grid grid-cols-2 sm:grid-cols-3 gap-4 transition-opacity">
                @forelse($artists as $artist)
                    <a href="{{ route('artist.page', $artist) }}"
                       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-white/5 hover:bg-white/10 transition text-center">
                        <div class="w-20 h-20 rounded-full overflow-hidden bg-gradient-to-br from-purple-800 to-indigo-900">
                            @if($artist->getFirstMediaUrl('avatar'))
                                <img src="{{ $artist->getFirstMediaUrl('avatar', 'thumb') }}" alt="{{ $artist->stage_name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-2xl font-bold text-white/40">
                                    {{ strtoupper(substr($artist->stage_name ?? $artist->user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-white font-medium">{{ $artist->stage_name ?? $artist->user->name }}</p>
                            @if($artist->is_verified)
                                <span class="text-xs text-purple-400">✓ Verified</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <p class="col-span-full py-10 text-center text-white/40">No artists found for "{{ $q }}"</p>
                @endforelse
            </div>
        @endif
    </div>
</div>
