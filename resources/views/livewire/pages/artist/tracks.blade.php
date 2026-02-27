<?php

use App\Models\Track;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component
{
    public string $search = '';
    public string $filter = 'all'; // all | published | drafts | locked
    public ?string $successMessage = null;

    public function mount(): void
    {
        if (!auth()->user()?->isArtist()) {
            $this->redirect(route('dashboard'));
            return;
        }

        if (!auth()->user()->artistProfile) {
            $this->redirect(route('artist.setup'));
            return;
        }
    }

    public function togglePublished(int $trackId): void
    {
        $track = Track::where('artist_profile_id', auth()->user()->artistProfile->id)->findOrFail($trackId);
        $newValue = !$track->is_published;
        $track->update(['is_published' => $newValue]);
        $this->successMessage = $newValue ? "\u201c{$track->title}\u201d is now live." : "\u201c{$track->title}\u201d set to draft.";
    }

    public function deleteTrack(int $trackId): void
    {
        $track = Track::where('artist_profile_id', auth()->user()->artistProfile->id)->findOrFail($trackId);
        $title = $track->title;
        $track->delete();
        $this->successMessage = "\u201c{$title}\u201d deleted.";
    }

    public function with(): array
    {
        $profile = auth()->user()->artistProfile;

        if (!$profile) {
            return ['tracks' => Track::whereNull('id')->paginate(20)];
        }

        $query = $profile->tracks()->with(['genre', 'album'])->latest();

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        match ($this->filter) {
            'published' => $query->where('is_published', true),
            'drafts'    => $query->where('is_published', false),
            'locked'    => $query->where('is_free', false),
            default     => null,
        };

        return [
            'tracks' => $query->paginate(20),
        ];
    }
}; ?>

<div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">My Tracks</h1>
        </div>
        <a href="{{ route('artist.upload-track') }}" wire:navigate
           class="glass-btn-primary glass-btn-primary-hover px-4 py-2 rounded-xl text-sm font-semibold shrink-0">
            + Upload Track
        </a>
    </div>

    @if(session('success') || $successMessage)
    <div class="px-4 py-3 bg-green-100 text-green-700 rounded-xl text-sm font-medium">
        {{ session('success') ?: $successMessage }}
    </div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <x-text-input wire:model.live.debounce.300ms="search" class="flex-1" type="search" placeholder="Search tracks…" />
        <div class="flex gap-2">
            @foreach(['all' => 'All', 'published' => 'Live', 'drafts' => 'Drafts', 'locked' => 'Locked'] as $key => $label)
            <button wire:click="$set('filter', '{{ $key }}')"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition
                           {{ $filter === $key ? 'bg-red-500 text-white' : 'bg-white/60 text-gray-600 hover:bg-white' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Track list --}}
    <div class="glass-card rounded-2xl overflow-hidden">
        @if($tracks->isEmpty())
        <div class="p-12 text-center text-gray-500 text-sm">
            No tracks found.
            <a href="{{ route('artist.upload-track') }}" wire:navigate class="text-red-500 hover:underline">Upload one →</a>
        </div>
        @else
        <ul class="divide-y divide-white/30">
            @foreach($tracks as $track)
            <li class="flex items-center gap-4 px-5 py-3 hover:bg-white/40 transition group">
                {{-- Cover / Play --}}
                <button @click="Livewire.dispatch('play-track', { id: {{ $track->id }} })"
                        class="w-10 h-10 rounded-lg bg-gray-200 overflow-hidden shrink-0 relative group/play">
                    @if($track->getAudioUrl())
                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover/play:opacity-100 transition">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                    </div>
                    @endif
                    @if($track->getCoverUrl())
                        <img src="{{ $track->getCoverUrl() }}" class="w-full h-full object-cover" alt="">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                        </div>
                    @endif
                </button>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $track->title }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $track->genre?->name ?? '—' }}
                        @if($track->album) · {{ $track->album->title }} @endif
                        · {{ $track->formatted_duration }}
                    </p>
                </div>

                <div class="flex items-center gap-3 text-xs text-gray-500 shrink-0">
                    <span>{{ number_format($track->play_count) }} plays</span>

                    @if($track->requires_donation)
                    <span class="text-rose-600 font-medium">${{ $track->donation_amount }} unlock</span>
                    @endif

                    {{-- Toggle live/draft --}}
                    <button wire:click="togglePublished({{ $track->id }})"
                            class="px-2.5 py-1 rounded-full font-medium transition
                                   {{ $track->is_published
                                       ? 'bg-green-100 text-green-700 hover:bg-yellow-100 hover:text-yellow-700'
                                       : 'bg-yellow-100 text-yellow-700 hover:bg-green-100 hover:text-green-700' }}">
                        {{ $track->is_published ? 'Live' : 'Draft' }}
                    </button>

                    {{-- Edit --}}
                    <a href="{{ route('artist.edit-track', $track) }}" wire:navigate
                       class="text-gray-400 hover:text-blue-600 transition"
                       title="Edit track">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>

                    {{-- Delete --}}
                    <button wire:click="deleteTrack({{ $track->id }})"
                            wire:confirm="Delete &lsquo;{{ addslashes($track->title) }}&rsquo;? This cannot be undone."
                            class="text-red-400 hover:text-red-600 transition"
                            title="Delete track">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </li>
            @endforeach
        </ul>
        <div class="px-5 py-3 border-t border-white/30">
            {{ $tracks->links() }}
        </div>
        @endif
    </div>
</div>
