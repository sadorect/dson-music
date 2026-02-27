<?php

use App\Models\ArtistProfile;
use App\Models\Track;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->isArtist(), 403);

        // Redirect new artists to set up their profile first
        if (!auth()->user()->artistProfile) {
            $this->redirect(route('artist.setup'), navigate: true);
        }
    }

    public function with(): array
    {
        $profile = auth()->user()->artistProfile;
        if (!$profile) {
            return [
                'profile'       => null,
                'totalTracks'   => 0,
                'totalPlays'    => 0,
                'totalDonations'=> 0,
                'followers'     => 0,
                'recentTracks'  => collect(),
            ];
        }

        return [
            'profile'       => $profile,
            'totalTracks'   => $profile->tracks()->where('is_published', true)->count(),
            'totalPlays'    => $profile->tracks()->sum('play_count'),
            'totalDonations'=> $profile->donations()->sum('amount'),
            'followers'     => $profile->followers()->count(),
            'recentTracks'  => $profile->tracks()->with('genre')->latest()->take(5)->get(),
        ];
    }
}; ?>

<div class="max-w-6xl mx-auto px-4 py-8 space-y-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Artist Dashboard</h1>
            <p class="text-sm text-gray-500 mt-0.5">Welcome back, {{ $profile?->stage_name ?? auth()->user()->name }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('artist.tracks') }}" wire:navigate
               class="glass-btn-primary glass-btn-primary-hover px-4 py-2 rounded-xl text-sm font-semibold">
                Manage Tracks
            </a>
            <a href="{{ route('artist.upload-track') }}" wire:navigate
               class="glass-btn-primary glass-btn-primary-hover px-4 py-2 rounded-xl text-sm font-semibold">
                + Upload Track
            </a>
            <a href="{{ route('artist.create-album') }}" wire:navigate
               class="glass-btn glass-btn-hover px-4 py-2 rounded-xl text-sm font-semibold">
                + New Album
            </a>
        </div>
    </div>

    @if(!$profile)
    <div class="glass-card rounded-2xl p-8 text-center">
        <p class="text-gray-600">Your artist profile is being set up. Please check back shortly.</p>
    </div>
    @else

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Published Tracks', 'value' => number_format($totalTracks),   'icon' => 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3'],
            ['label' => 'Total Plays',      'value' => number_format($totalPlays),     'icon' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Followers',        'value' => number_format($followers),      'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['label' => 'Total Donations',  'value' => '$' . number_format($totalDonations, 2), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ] as $stat)
        <div class="glass-card rounded-2xl p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">{{ $stat['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stat['value'] }}</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $stat['icon'] }}"/>
                    </svg>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Artist Profile Card --}}
    @if(!$profile->is_approved)
    <div class="glass-card rounded-2xl p-4 border border-yellow-200 bg-yellow-50/60">
        <p class="text-sm text-yellow-700 font-medium">⏳ Your artist profile is pending admin approval. You can still upload tracks, but they won't be publicly visible until approved.</p>
    </div>
    @endif

    {{-- Recent Tracks --}}
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/40 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Recent Tracks</h2>
            <a href="{{ route('artist.tracks') }}" wire:navigate class="text-sm text-red-500 hover:underline">View all →</a>
        </div>
        @if($recentTracks->isEmpty())
            <div class="px-6 py-10 text-center text-gray-500 text-sm">
                No tracks yet. <a href="{{ route('artist.upload-track') }}" wire:navigate class="text-red-500 hover:underline">Upload your first track →</a>
            </div>
        @else
        <ul class="divide-y divide-white/30">
            @foreach($recentTracks as $track)
            <li class="flex items-center gap-4 px-6 py-3 hover:bg-white/40 transition">
                <div class="w-10 h-10 rounded-lg bg-gray-200 overflow-hidden shrink-0">
                    @if($track->getCoverUrl())
                        <img src="{{ $track->getCoverUrl() }}" class="w-full h-full object-cover" alt="">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $track->title }}</p>
                    <p class="text-xs text-gray-500">{{ $track->genre?->name ?? '—' }} · {{ $track->formatted_duration }}</p>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-500 shrink-0">
                    <span>{{ number_format($track->play_count) }} plays</span>
                    <span class="{{ $track->is_published ? 'text-green-600' : 'text-yellow-600' }} font-medium">
                        {{ $track->is_published ? 'Live' : 'Draft' }}
                    </span>
                </div>
            </li>
            @endforeach
        </ul>
        @endif
    </div>
    @endif
</div>
