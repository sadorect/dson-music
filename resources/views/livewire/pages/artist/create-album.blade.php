<?php

use App\Models\Album;
use App\Models\Genre;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.glass-app')] class extends Component
{
    use WithFileUploads;

    public string $title       = '';
    public string $description = '';
    public string $type        = 'album'; // album | ep | single
    public ?int   $genre_id    = null;
    public string $release_date = '';
    public bool   $is_published = false;
    public $coverFile = null;
    public array  $genres = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->isArtist(), 403);

        if (!auth()->user()->artistProfile) {
            $this->redirect(route('artist.setup'), navigate: true);
            return;
        }

        $this->genres = Genre::active()->orderBy('name')->get(['id','name'])->toArray();
        $this->release_date = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $this->validate([
            'title'        => ['required', 'string', 'max:200'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'type'         => ['required', 'in:album,ep,single'],
            'genre_id'     => ['nullable', 'exists:genres,id'],
            'release_date' => ['nullable', 'date'],
            'is_published' => ['boolean'],
            'coverFile'    => ['nullable', 'image', 'max:4096'],
        ]);

        $user    = auth()->user();
        $profile = $user->artistProfile;

        $album = Album::create([
            'user_id'           => $user->id,
            'artist_profile_id' => $profile->id,
            'title'             => $this->title,
            'description'       => $this->description,
            'type'              => $this->type,
            'genre_id'          => $this->genre_id,
            'release_date'      => $this->release_date ?: null,
            'is_published'      => $this->is_published,
        ]);

        if ($this->coverFile) {
            $album->addMedia($this->coverFile->getRealPath())
                ->usingFileName('cover.' . $this->coverFile->getClientOriginalExtension())
                ->toMediaCollection('cover');
        }

        session()->flash('success', "Album \"{$album->title}\" created!");
        $this->redirect(route('artist.albums'), navigate: true);
    }
}; ?>

<div class="max-w-2xl mx-auto px-4 py-8">

    <div class="flex items-center gap-3 mb-8">
        <a href="{{ route('artist.albums') }}" wire:navigate class="text-gray-500 hover:text-gray-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Create Album</h1>
    </div>

    <form wire:submit="save" class="space-y-6" enctype="multipart/form-data">

        <div class="glass-card rounded-2xl p-6 space-y-5">

            <div>
                <x-input-label for="title" value="Album Title *" />
                <x-text-input wire:model="title" id="title" class="block mt-1 w-full" type="text" />
                <x-input-error :messages="$errors->get('title')" class="mt-1" />
            </div>

            <div>
                <x-input-label value="Type" />
                <div class="flex gap-3 mt-2">
                    @foreach(['album' => 'Album', 'ep' => 'EP', 'single' => 'Single'] as $val => $label)
                    <label class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl border-2 cursor-pointer text-sm font-medium transition
                                  {{ $type === $val ? 'border-red-500 bg-red-50 text-red-600' : 'border-gray-200 text-gray-600 hover:border-red-300' }}">
                        <input type="radio" wire:model.live="type" value="{{ $val }}" class="sr-only">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <x-input-label for="description" value="Description" />
                <textarea wire:model="description" id="description" rows="3"
                    class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-red-400 focus:ring-red-400 text-sm resize-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="genre_id" value="Genre" />
                    <select wire:model="genre_id" id="genre_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-red-400 focus:ring-red-400 text-sm">
                        <option value="">— None —</option>
                        @foreach($genres as $g)
                            <option value="{{ $g['id'] }}">{{ $g['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="release_date" value="Release Date" />
                    <x-text-input wire:model="release_date" id="release_date" class="block mt-1 w-full" type="date" />
                </div>
            </div>

            {{-- Cover --}}
            <div>
                <x-input-label value="Cover Art" />
                <div class="flex items-center gap-4 mt-2">
                    @if($coverFile)
                        <img src="{{ $coverFile->temporaryUrl() }}" class="w-20 h-20 rounded-xl object-cover ring-2 ring-red-400">
                    @else
                        <div class="w-20 h-20 rounded-xl bg-gray-200 flex items-center justify-center text-gray-400 shrink-0">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                        </div>
                    @endif
                    <input wire:model="coverFile" type="file" accept="image/jpeg,image/png,image/webp"
                           class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-600 hover:file:bg-red-100">
                </div>
                <x-input-error :messages="$errors->get('coverFile')" class="mt-1" />
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input wire:model="is_published" type="checkbox" class="rounded border-gray-300 text-red-500 focus:ring-red-400">
                <div>
                    <p class="text-sm font-medium text-gray-700">Publish immediately</p>
                    <p class="text-xs text-gray-400">Uncheck to keep as draft</p>
                </div>
            </label>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('artist.albums') }}" wire:navigate class="glass-btn glass-btn-hover px-5 py-2.5 rounded-xl text-sm font-medium">Cancel</a>
            <button type="submit" class="glass-btn-primary glass-btn-primary-hover px-6 py-2.5 rounded-xl text-sm font-semibold" wire:loading.attr="disabled">
                <span wire:loading.remove>Create Album</span>
                <span wire:loading>Saving…</span>
            </button>
        </div>
    </form>
</div>
