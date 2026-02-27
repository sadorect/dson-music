<?php

use App\Models\Album;
use App\Models\Genre;
use App\Models\Track;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.glass-app')] class extends Component
{
    use WithFileUploads;

    // Track being edited
    public Track $track;

    // Editable fields
    public string $title           = '';
    public string $description     = '';
    public string $lyrics          = '';
    public ?int   $genre_id        = null;
    public ?int   $album_id        = null;
    public int    $track_number    = 1;
    public bool   $is_published    = true;
    public bool   $requires_donation = false;
    public string $donation_amount = '';

    // Replacement files (optional)
    public $audioFile = null;
    public $coverFile = null;

    // Select options
    public array $genres = [];
    public array $albums = [];

    public function mount(Track $track): void
    {
        abort_unless(auth()->user()->isArtist(), 403);

        $profile = auth()->user()->artistProfile;
        abort_unless($profile && $track->artist_profile_id === $profile->id, 403);

        $this->track = $track;

        // Pre-populate form fields
        $this->title             = $track->title;
        $this->description       = $track->description ?? '';
        $this->lyrics            = $track->lyrics ?? '';
        $this->genre_id          = $track->genre_id;
        $this->album_id          = $track->album_id;
        $this->track_number      = $track->track_number ?? 1;
        $this->is_published      = (bool) $track->is_published;
        $this->requires_donation = !($track->is_free ?? true);
        $this->donation_amount   = $track->donation_amount ? (string)$track->donation_amount : '';

        $this->genres = Genre::active()->orderBy('name')->get(['id', 'name'])->toArray();
        $this->albums = $profile->albums()->orderByDesc('created_at')->get(['id', 'title'])->toArray();
    }

    public function save(): void
    {
        $rules = [
            'title'              => ['required', 'string', 'max:200'],
            'description'        => ['nullable', 'string', 'max:2000'],
            'lyrics'             => ['nullable', 'string', 'max:10000'],
            'genre_id'           => ['nullable', 'exists:genres,id'],
            'album_id'           => ['nullable', 'exists:albums,id'],
            'track_number'       => ['nullable', 'integer', 'min:1'],
            'is_published'       => ['boolean'],
            'requires_donation'  => ['boolean'],
            'donation_amount'    => ['nullable', 'numeric', 'min:0.5', 'max:999'],
            'audioFile'          => ['nullable', 'file', 'mimes:mp3,wav,flac,ogg,aac', 'max:102400'],
            'coverFile'          => ['nullable', 'image', 'max:4096'],
        ];

        if ($this->requires_donation) {
            $rules['donation_amount'][] = 'required';
        }

        $this->validate($rules);

        $this->track->update([
            'title'          => $this->title,
            'description'    => $this->description ?: null,
            'lyrics'         => $this->lyrics ?: null,
            'genre_id'       => $this->genre_id,
            'album_id'       => $this->album_id,
            'track_number'   => $this->track_number ?: 1,
            'is_published'   => $this->is_published,
            'is_free'        => !$this->requires_donation,
            'donation_amount'=> $this->requires_donation ? (float)$this->donation_amount : 1.00,
        ]);

        // Replace audio if a new file was provided
        if ($this->audioFile) {
            $this->track->clearMediaCollection('audio');
            $this->track->addMedia($this->audioFile->getRealPath())
                ->usingFileName(\Str::slug($this->title) . '.' . $this->audioFile->getClientOriginalExtension())
                ->toMediaCollection('audio');
        }

        // Replace cover if a new file was provided
        if ($this->coverFile) {
            $this->track->clearMediaCollection('cover');
            $this->track->addMedia($this->coverFile->getRealPath())
                ->usingFileName('cover.' . $this->coverFile->getClientOriginalExtension())
                ->toMediaCollection('cover');
        }

        session()->flash('success', "\"{$this->track->title}\" updated successfully!");
        $this->redirect(route('artist.tracks'));
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-8">
        <a href="{{ route('artist.tracks') }}" wire:navigate class="text-gray-500 hover:text-gray-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Track</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $track->title }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 px-4 py-3 bg-green-100 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <form wire:submit="save" class="space-y-6" enctype="multipart/form-data">

        {{-- Track Info --}}
        <div class="glass-card rounded-2xl p-6 space-y-5">
            <h2 class="font-semibold text-gray-700">Track Details</h2>

            <div>
                <x-input-label for="title" value="Title *" />
                <x-text-input wire:model="title" id="title" class="block mt-1 w-full" type="text" placeholder="Track title" />
                <x-input-error :messages="$errors->get('title')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="description" value="Description" />
                <textarea wire:model="description" id="description" rows="3"
                    class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-red-400 focus:ring-red-400 text-sm resize-none"
                    placeholder="What's this track about?"></textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-1" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="genre_id" value="Genre" />
                    <select wire:model="genre_id" id="genre_id"
                        class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-red-400 focus:ring-red-400 text-sm">
                        <option value="">— No genre —</option>
                        @foreach($genres as $g)
                            <option value="{{ $g['id'] }}" @selected($genre_id == $g['id'])>{{ $g['name'] }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('genre_id')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="album_id" value="Album" />
                    <select wire:model="album_id" id="album_id"
                        class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-red-400 focus:ring-red-400 text-sm">
                        <option value="">— Standalone single —</option>
                        @foreach($albums as $a)
                            <option value="{{ $a['id'] }}" @selected($album_id == $a['id'])>{{ $a['title'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="w-32">
                <x-input-label for="track_number" value="Track #" />
                <x-text-input wire:model="track_number" id="track_number" class="block mt-1 w-full" type="number" min="1" />
            </div>
        </div>

        {{-- Lyrics --}}
        <div class="glass-card rounded-2xl p-6 space-y-3">
            <h2 class="font-semibold text-gray-700">Lyrics <span class="text-xs text-gray-400 font-normal">(optional)</span></h2>
            <textarea wire:model="lyrics" rows="8"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-400 focus:ring-red-400 text-sm resize-y font-mono"
                placeholder="Paste your lyrics here…"></textarea>
            <x-input-error :messages="$errors->get('lyrics')" class="mt-1" />
        </div>

        {{-- Cover Art --}}
        <div class="glass-card rounded-2xl p-6 space-y-3">
            <h2 class="font-semibold text-gray-700">Cover Art</h2>
            <div class="flex items-center gap-5">
                @if($coverFile)
                    <img src="{{ $coverFile->temporaryUrl() }}" class="w-20 h-20 rounded-xl object-cover ring-2 ring-red-400" alt="">
                @elseif($track->getCoverUrl())
                    <img src="{{ $track->getCoverUrl() }}" class="w-20 h-20 rounded-xl object-cover" alt="">
                @else
                    <div class="w-20 h-20 rounded-xl bg-gray-200 flex items-center justify-center text-gray-400 shrink-0">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                @endif
                <div>
                    <input wire:model="coverFile" type="file" accept="image/jpeg,image/png,image/webp"
                        class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-600 hover:file:bg-red-100">
                    <p class="text-xs text-gray-400 mt-1">Upload a new image to replace · JPG, PNG, WebP</p>
                    <x-input-error :messages="$errors->get('coverFile')" class="mt-1" />
                </div>
            </div>
        </div>

        {{-- Replace Audio --}}
        <div class="glass-card rounded-2xl p-6 space-y-3">
            <h2 class="font-semibold text-gray-700">Replace Audio <span class="text-xs text-gray-400 font-normal">(optional — leave blank to keep current)</span></h2>

            @php $currentAudio = $track->getFirstMedia('audio'); @endphp
            @if($currentAudio)
            <div class="flex items-center gap-3 px-3 py-2 bg-gray-50 rounded-lg text-sm text-gray-600">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                <span class="truncate">{{ $currentAudio->file_name }}</span>
                <span class="text-xs text-gray-400 shrink-0">{{ round($currentAudio->size / 1048576, 1) }} MB</span>
            </div>
            @elseif($track->audio_file_path)
            <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm text-gray-600">
                {{ basename($track->audio_file_path) }}
            </div>
            @else
            <p class="text-sm text-amber-600">⚠ No audio file uploaded yet.</p>
            @endif

            <div
                x-data="{ drag: false }"
                x-on:dragover.prevent="drag = true"
                x-on:dragleave.prevent="drag = false"
                x-on:drop.prevent="drag = false"
                :class="drag ? 'border-red-400 bg-red-50' : 'border-gray-300'"
                class="border-2 border-dashed rounded-xl p-6 text-center transition cursor-pointer"
                onclick="document.getElementById('audioReplaceInput').click()"
            >
                @if($audioFile)
                    <p class="text-sm font-medium text-green-600">✓ {{ $audioFile->getClientOriginalName() }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ round($audioFile->getSize() / 1048576, 1) }} MB — will replace existing</p>
                @else
                    <p class="text-sm text-gray-500">Drop new audio file here or <span class="text-red-500">browse</span></p>
                    <p class="text-xs text-gray-400 mt-1">MP3, WAV, FLAC, OGG, AAC · Max 100 MB</p>
                @endif
                <input id="audioReplaceInput" wire:model="audioFile" type="file" accept=".mp3,.wav,.flac,.ogg,.aac" class="sr-only">
            </div>
            <x-input-error :messages="$errors->get('audioFile')" class="mt-1" />
        </div>

        {{-- Publish & Monetise --}}
        <div class="glass-card rounded-2xl p-6 space-y-4">
            <h2 class="font-semibold text-gray-700">Visibility & Monetisation</h2>

            <div class="flex items-center gap-3">
                <input wire:model="is_published" id="is_published" type="checkbox"
                    class="rounded border-gray-300 text-red-500 focus:ring-red-400">
                <x-input-label for="is_published" value="Published (visible to listeners)" class="!mb-0" />
            </div>

            <div class="flex items-center gap-3">
                <input wire:model.live="requires_donation" id="requires_donation" type="checkbox"
                    class="rounded border-gray-300 text-red-500 focus:ring-red-400">
                <x-input-label for="requires_donation" value="Requires donation to unlock" class="!mb-0" />
            </div>

            @if($requires_donation)
            <div class="w-48">
                <x-input-label for="donation_amount" value="Minimum Amount (USD) *" />
                <div class="relative mt-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                    <x-text-input wire:model="donation_amount" id="donation_amount"
                        class="block w-full pl-6" type="number" step="0.50" min="0.50" placeholder="1.00" />
                </div>
                <x-input-error :messages="$errors->get('donation_amount')" class="mt-1" />
            </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('artist.tracks') }}" wire:navigate
               class="text-sm text-gray-500 hover:text-gray-700 transition">
                ← Cancel
            </a>
            <x-primary-button type="submit">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save Changes
            </x-primary-button>
        </div>

    </form>
</div>
