<?php

use App\Models\ArtistProfile;
use App\Models\Follow;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.glass-app')] class extends Component {

    public ArtistProfile $profile;
    public bool $isFollowing = false;

    // Resolved via route-model binding: /artist/{profile:slug}
    public function mount(ArtistProfile $profile): void
    {
        abort_if(!$profile->is_approved, 404);
        $this->profile = $profile->load(['user', 'genres',
            'tracks' => fn($q) => $q->where('is_published', true)->orderByDesc('play_count')->take(20),
            'albums'  => fn($q) => $q->where('is_published', true)->orderByDesc('release_date'),
        ]);

        if (auth()->check()) {
            $this->isFollowing = Follow::where('follower_id', auth()->id())
                ->where('artist_profile_id', $profile->id)
                ->exists();
        }
    }

    public function with(): array
    {
        return ['profile' => $this->profile];
    }

    public function toggleFollow(): void
    {
        if (!auth()->check()) {
            $this->dispatch('open-modal', id: 'login-required');
            return;
        }

        $userId = auth()->id();
        $existing = Follow::where('follower_id', $userId)
            ->where('artist_profile_id', $this->profile->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $this->profile->decrement('followers_count');
            $this->isFollowing = false;
        } else {
            Follow::create([
                'follower_id'       => $userId,
                'artist_profile_id' => $this->profile->id,
            ]);
            $this->profile->increment('followers_count');
            $this->isFollowing = true;
        }
    }
};
?>

<div class="min-h-screen">
    {{-- Banner --}}
    <div class="relative h-52 sm:h-72 overflow-hidden">
        @if($profile->getFirstMediaUrl('banner'))
            <img src="{{ $profile->getFirstMediaUrl('banner', 'large') }}" alt="" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
    </div>

    {{-- Profile row --}}
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end gap-5 -mt-16 sm:-mt-20 mb-6">
            {{-- Avatar --}}
            <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-2xl ring-4 ring-black overflow-hidden shrink-0 bg-gradient-to-br from-purple-800 to-indigo-900">
                @if($profile->getFirstMediaUrl('avatar'))
                    <img src="{{ $profile->getFirstMediaUrl('avatar', 'thumb') }}" alt="{{ $profile->stage_name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-4xl font-bold text-white/30">
                        {{ strtoupper(substr($profile->stage_name ?? $profile->user->name, 0, 1)) }}
                    </div>
                @endif
            </div>
            {{-- Name + follow --}}
            <div class="flex-1 pb-2">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white leading-tight">
                        {{ $profile->stage_name ?? $profile->user->name }}
                    </h1>
                    @if($profile->is_verified)
                        <span class="text-purple-400 text-sm font-medium">✓ Verified</span>
                    @endif
                </div>
                <div class="flex items-center gap-4 mt-1">
                    <span class="text-white/50 text-sm">{{ number_format($profile->followers_count) }} followers</span>
                    <span class="text-white/50 text-sm">{{ $profile->tracks->count() }} tracks</span>
                </div>
            </div>
            <div class="pb-2 shrink-0">
                <button wire:click="toggleFollow"
                        class="px-6 py-2 rounded-full font-semibold text-sm transition {{ $isFollowing
                            ? 'bg-white/10 text-white hover:bg-white/20'
                            : 'bg-purple-600 text-white hover:bg-purple-500' }}">
                    {{ $isFollowing ? 'Following' : 'Follow' }}
                </button>
            </div>
        </div>

        {{-- Bio --}}
        @if($profile->bio)
            <p class="text-white/60 text-sm leading-relaxed mb-8 max-w-2xl">{{ $profile->bio }}</p>
        @endif

        {{-- Genres --}}
        @if($profile->genres->count())
            <div class="flex flex-wrap gap-2 mb-8">
                @foreach($profile->genres as $genre)
                    <a href="{{ route('browse', ['genre' => $genre->slug]) }}"
                       class="text-xs bg-white/10 text-white/70 px-3 py-1 rounded-full hover:bg-white/20 transition">
                        {{ $genre->name }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Tracks --}}
        @if($profile->tracks->count())
            <section class="mb-10">
                <h2 class="text-xl font-bold text-white mb-4">Tracks</h2>
                <div class="space-y-2">
                    @foreach($profile->tracks as $i => $track)
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 hover:bg-white/10 cursor-pointer transition group"
                             @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })">
                            <span class="w-6 text-center text-white/30 text-sm group-hover:hidden">{{ $i + 1 }}</span>
                            <svg class="w-6 h-6 text-white hidden group-hover:block shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                            <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0">
                                @if($track->getFirstMediaUrl('cover'))
                                    <img src="{{ $track->getFirstMediaUrl('cover', 'thumb') }}" alt="{{ $track->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-purple-800 to-indigo-900"></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-white text-sm font-medium truncate">{{ $track->title }}</p>
                                <p class="text-white/40 text-xs">{{ number_format($track->play_count) }} plays</p>
                            </div>
                            @if($track->requires_donation)
                                <span class="text-xs bg-yellow-500/20 text-yellow-300 px-2 py-0.5 rounded-full shrink-0">
                                    ${{ number_format($track->donation_amount, 2) }}
                                </span>
                            @endif
                            @livewire('like-button', ['trackId' => $track->id], key('like-'.$track->id))
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Albums --}}
        @if($profile->albums->count())
            <section class="mb-10">
                <h2 class="text-xl font-bold text-white mb-4">Albums & EPs</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($profile->albums as $album)
                        <div class="bg-white/5 rounded-xl overflow-hidden hover:bg-white/10 transition">
                            <div class="aspect-square">
                                @if($album->getFirstMediaUrl('cover'))
                                    <img src="{{ $album->getFirstMediaUrl('cover', 'large') }}" alt="{{ $album->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-purple-800 to-indigo-900 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-white/20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-2">
                                <p class="text-white text-sm font-semibold truncate">{{ $album->title }}</p>
                                <p class="text-white/50 text-xs">{{ ucfirst($album->type) }} · {{ $album->release_date?->year }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Social links --}}
        @php
            $socialLinks = array_filter([
                'spotify'   => $profile->spotify_url ?? null,
                'instagram' => $profile->instagram_url ?? null,
                'twitter'   => $profile->twitter_url ?? null,
                'website'   => $profile->website_url ?? null,
            ]);
        @endphp
        @if(count($socialLinks))
            <div class="flex flex-wrap gap-3 mb-10">
                @foreach($socialLinks as $platform => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener"
                       class="flex items-center gap-1.5 text-sm text-white/60 hover:text-white transition bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-full">
                        {{ ucfirst($platform) }}
                        <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
