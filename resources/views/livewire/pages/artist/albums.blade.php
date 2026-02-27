<?php

use App\Models\Album;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->isArtist(), 403);

        if (!auth()->user()->artistProfile) {
            $this->redirect(route("artist.setup"), navigate: true);
        }
    }

    public function deleteAlbum(int $id): void
    {
        $album = Album::where('artist_profile_id', auth()->user()->artistProfile->id)->findOrFail($id);
        $album->delete();
    }

    public function with(): array
    {
        $profile = auth()->user()->artistProfile;
        return [
            'albums' => $profile->albums()->with('genre')->withCount('tracks')->orderByDesc('release_date')->get(),
        ];
    }
}; ?>

<div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">My Albums</h1>
        <a href="{{ route('artist.create-album') }}" wire:navigate
           class="glass-btn-primary glass-btn-primary-hover px-4 py-2 rounded-xl text-sm font-semibold">
            + New Album
        </a>
    </div>

    @if($albums->isEmpty())
    <div class="glass-card rounded-2xl p-12 text-center text-gray-500 text-sm">
        No albums yet. <a href="{{ route('artist.create-album') }}" wire:navigate class="text-red-500 hover:underline">Create your first →</a>
    </div>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
        @foreach($albums as $album)
        <div class="glass-card rounded-2xl overflow-hidden group">
            <div class="aspect-square bg-gray-200 relative overflow-hidden">
                @if($album->getCoverUrl())
                    <img src="{{ $album->getCoverUrl('large') }}" class="w-full h-full object-cover transition group-hover:scale-105" alt="">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-red-100 to-pink-100">
                        <svg class="w-10 h-10 text-red-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v10.55A4 4 0 1010 17V7h8V3h-6z"/></svg>
                    </div>
                @endif
                <div class="absolute top-2 left-2">
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full backdrop-blur-sm
                          {{ $album->type === 'single' ? 'bg-blue-500/80 text-white' : ($album->type === 'ep' ? 'bg-purple-500/80 text-white' : 'bg-red-500/80 text-white') }}">
                        {{ strtoupper($album->type) }}
                    </span>
                </div>
            </div>
            <div class="p-3">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $album->title }}</p>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $album->tracks_count }} track{{ $album->tracks_count !== 1 ? 's' : '' }}
                    @if($album->release_date) · {{ $album->release_date->format('Y') }} @endif
                </p>
                <div class="flex items-center justify-between mt-3">
                    <span class="text-xs {{ $album->is_published ? 'text-green-600' : 'text-yellow-600' }} font-medium">
                        {{ $album->is_published ? 'Published' : 'Draft' }}
                    </span>
                    <button wire:click="deleteAlbum({{ $album->id }})"
                            wire:confirm="Delete album '{{ addslashes($album->title) }}'?"
                            class="text-red-400 hover:text-red-600 transition opacity-0 group-hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
