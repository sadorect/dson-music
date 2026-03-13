<?php

use App\Models\Genre;
use App\Models\Track;
use App\Rules\ValidAudioUpload;
use App\StagesLivewireUploads;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.glass-app')] class extends Component
{
    use StagesLivewireUploads;
    use WithFileUploads;

    public string $title = '';
    public string $description = '';
    public ?int $genre_id = null;
    public ?int $album_id = null;
    public ?string $mood = null;
    public int $track_number = 1;
    public bool $is_published = true;
    public bool $requires_donation = false;
    public string $donation_amount = '';

    public $audioFile = null;
    public $coverFile = null;

    public array $genres = [];
    public array $albums = [];
    public array $moods = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->isArtist(), 403);

        $profile = auth()->user()->artistProfile;

        if (! $profile) {
            $this->redirect(route('artist.setup'), navigate: true);

            return;
        }

        $this->genres = Genre::active()->orderBy('name')->get(['id', 'name', 'slug'])->toArray();
        $this->albums = $profile->albums()->orderByDesc('created_at')->get(['id', 'title'])->toArray();
        $this->moods = collect(Track::moodOptions())
            ->map(fn (string $label, string $slug) => ['slug' => $slug, 'label' => $label])
            ->values()
            ->all();
    }

    public function suggestedMoodLabel(): ?string
    {
        if (! $this->genre_id) {
            return null;
        }

        $genre = collect($this->genres)->firstWhere('id', $this->genre_id);
        $suggestedMood = Track::suggestedMoodSlugForGenre($genre['slug'] ?? null);

        return $suggestedMood ? (Track::moodOptions()[$suggestedMood] ?? null) : null;
    }

    public function save(): void
    {
        $rules = [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'genre_id' => ['nullable', 'exists:genres,id'],
            'album_id' => ['nullable', 'exists:albums,id'],
            'mood' => ['nullable', 'string', 'max:50'],
            'track_number' => ['nullable', 'integer', 'min:1'],
            'is_published' => ['boolean'],
            'requires_donation' => ['boolean'],
            'donation_amount' => ['nullable', 'numeric', 'min:0.5', 'max:999'],
            'audioFile' => ['required', 'file', new ValidAudioUpload(), 'max:102400'],
            'coverFile' => ['nullable', 'image', 'max:4096'],
        ];

        if ($this->requires_donation) {
            $rules['donation_amount'][] = 'required';
        }

        $this->validate($rules);

        $profile = auth()->user()->artistProfile;

        $track = Track::create([
            'user_id' => auth()->id(),
            'artist_profile_id' => $profile->id,
            'album_id' => $this->album_id,
            'genre_id' => $this->genre_id,
            'mood' => $this->mood,
            'title' => $this->title,
            'description' => $this->description,
            'track_number' => $this->track_number ?: 1,
            'is_published' => $this->is_published,
            'requires_donation' => $this->requires_donation,
            'donation_amount' => $this->requires_donation ? (float) $this->donation_amount : 1.00,
        ]);

        $audioExt = strtolower($this->audioFile->getClientOriginalExtension());
        $audioFileName = (Str::slug($this->title) ?: 'track') . '.' . $audioExt;

        $this->addStagedMedia($track, $this->audioFile, 'audio', $audioFileName);

        if ($this->coverFile) {
            $coverExt = strtolower($this->coverFile->getClientOriginalExtension());

            $this->addStagedMedia($track, $this->coverFile, 'cover', 'cover.' . $coverExt);
        }

        session()->flash('success', "Track \"{$track->title}\" uploaded successfully!");
        $this->redirect(route('artist.tracks'));
    }
};
?>

<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="flex items-center gap-3 mb-8">
        <a href="{{ route('artist.tracks') }}" wire:navigate class="text-gray-500 hover:text-gray-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Upload Track</h1>
    </div>

    @if(session('success'))
        <div class="mb-6 px-4 py-3 bg-green-100 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <form wire:submit="save" class="space-y-6" enctype="multipart/form-data">
        <div
            class="glass-card rounded-2xl p-6 space-y-4"
            x-data="{ drag: false, uploading: false, progress: 0 }"
            x-on:livewire-upload-start="if ($event.detail.property !== 'audioFile') return; uploading = true; progress = 0"
            x-on:livewire-upload-progress="if ($event.detail.property !== 'audioFile') return; progress = $event.detail.progress"
            x-on:livewire-upload-finish="if ($event.detail.property !== 'audioFile') return; uploading = false; progress = 100"
            x-on:livewire-upload-error="if ($event.detail.property !== 'audioFile') return; uploading = false"
            x-on:livewire-upload-cancel="if ($event.detail.property !== 'audioFile') return; uploading = false; progress = 0"
        >
            <h2 class="font-semibold text-gray-700">Audio File <span class="text-red-500">*</span></h2>
            <div
                x-on:dragover.prevent="drag = true"
                x-on:dragleave.prevent="drag = false"
                x-on:drop.prevent="drag = false"
                :class="drag ? 'border-red-400 bg-red-50' : 'border-gray-300'"
                class="border-2 border-dashed rounded-xl p-8 text-center transition cursor-pointer"
                onclick="document.getElementById('audioInput').click()"
            >
                <svg class="mx-auto w-10 h-10 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                @if($audioFile)
                    <p class="text-sm font-medium text-green-600">Selected: {{ $audioFile->getClientOriginalName() }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ round($audioFile->getSize() / 1048576, 1) }} MB</p>
                @else
                    <p class="text-sm font-medium text-gray-600">Drop your audio file here or <span class="text-red-500">browse</span></p>
                    <p class="text-xs text-gray-400 mt-1">MP3, WAV, FLAC, OGG, AAC, M4A - Max 100 MB</p>
                @endif
                <input id="audioInput" wire:model="audioFile" type="file" accept=".mp3,.wav,.flac,.ogg,.aac,.m4a,audio/*" class="sr-only">
            </div>
            <div x-show="uploading" class="space-y-2">
                <div class="flex items-center justify-between text-xs font-medium text-gray-500">
                    <span>Uploading audio file...</span>
                    <span x-text="`${progress}%`"></span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full bg-red-500 transition-all duration-200" :style="`width: ${progress}%`"></div>
                </div>
            </div>
            <x-input-error :messages="$errors->get('audioFile')" />
        </div>

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

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <x-input-label for="genre_id" value="Genre" />
                    <select wire:model="genre_id" id="genre_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-red-400 focus:ring-red-400 text-sm">
                        <option value="">No genre</option>
                        @foreach($genres as $g)
                            <option value="{{ $g['id'] }}">{{ $g['name'] }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('genre_id')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="album_id" value="Album" />
                    <select wire:model="album_id" id="album_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-red-400 focus:ring-red-400 text-sm">
                        <option value="">Standalone single</option>
                        @foreach($albums as $a)
                            <option value="{{ $a['id'] }}">{{ $a['title'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="mood" value="Mood" />
                    <select wire:model="mood" id="mood" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-red-400 focus:ring-red-400 text-sm">
                        <option value="">No mood</option>
                        @foreach($moods as $m)
                            <option value="{{ $m['slug'] }}">{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                    @if(!$mood && $this->suggestedMoodLabel())
                        <p class="mt-1 text-xs text-gray-400">Suggested from genre: {{ $this->suggestedMoodLabel() }}</p>
                    @else
                        <p class="mt-1 text-xs text-gray-400">Helps browse and discovery rails place the track correctly.</p>
                    @endif
                    <x-input-error :messages="$errors->get('mood')" class="mt-1" />
                </div>
            </div>

            <div class="w-32">
                <x-input-label for="track_number" value="Track #" />
                <x-text-input wire:model="track_number" id="track_number" class="block mt-1 w-full" type="number" min="1" />
            </div>
        </div>

        <div
            class="glass-card rounded-2xl p-6 space-y-3"
            x-data="{ uploading: false, progress: 0 }"
            x-on:livewire-upload-start="if ($event.detail.property !== 'coverFile') return; uploading = true; progress = 0"
            x-on:livewire-upload-progress="if ($event.detail.property !== 'coverFile') return; progress = $event.detail.progress"
            x-on:livewire-upload-finish="if ($event.detail.property !== 'coverFile') return; uploading = false; progress = 100"
            x-on:livewire-upload-error="if ($event.detail.property !== 'coverFile') return; uploading = false"
            x-on:livewire-upload-cancel="if ($event.detail.property !== 'coverFile') return; uploading = false; progress = 0"
        >
            <h2 class="font-semibold text-gray-700">Cover Art <span class="text-xs text-gray-400 font-normal">(optional)</span></h2>
            <div class="flex items-center gap-5">
                @if($coverFile)
                    <img src="{{ $coverFile->temporaryUrl() }}" class="w-20 h-20 rounded-xl object-cover ring-2 ring-red-400" alt="">
                @else
                    <div class="w-20 h-20 rounded-xl bg-gray-200 flex items-center justify-center text-gray-400 shrink-0">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                @endif
                <div>
                    <input wire:model="coverFile" type="file" accept="image/jpeg,image/png,image/webp"
                           class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-600 hover:file:bg-red-100">
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP - recommended 1:1</p>
                </div>
            </div>
            <div x-show="uploading" class="space-y-2">
                <div class="flex items-center justify-between text-xs font-medium text-gray-500">
                    <span>Uploading cover art...</span>
                    <span x-text="`${progress}%`"></span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full bg-red-500 transition-all duration-200" :style="`width: ${progress}%`"></div>
                </div>
            </div>
            <x-input-error :messages="$errors->get('coverFile')" />
        </div>

        <div class="glass-card rounded-2xl p-6 space-y-4">
            <h2 class="font-semibold text-gray-700">Visibility & Monetization</h2>

            <label class="flex items-center gap-3 cursor-pointer">
                <input wire:model.live="is_published" type="checkbox" class="rounded border-gray-300 text-red-500 focus:ring-red-400">
                <div>
                    <p class="text-sm font-medium text-gray-700">Publish immediately</p>
                    <p class="text-xs text-gray-400">Uncheck to save as a draft</p>
                </div>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
                <input wire:model.live="requires_donation" type="checkbox" class="rounded border-gray-300 text-red-500 focus:ring-red-400">
                <div>
                    <p class="text-sm font-medium text-gray-700">Require donation to unlock full track</p>
                    <p class="text-xs text-gray-400">Listeners pay once to stream and download</p>
                </div>
            </label>

            @if($requires_donation)
                <div class="ml-7 w-44">
                    <x-input-label for="donation_amount" value="Unlock Price (USD) *" />
                    <div class="relative mt-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">$</span>
                        <x-text-input wire:model="donation_amount" id="donation_amount" class="block w-full pl-7" type="number" min="0.5" step="0.5" placeholder="2.00" />
                    </div>
                    <x-input-error :messages="$errors->get('donation_amount')" class="mt-1" />
                </div>
            @endif
        </div>

        <div class="flex justify-end gap-3 pb-4">
            <a href="{{ route('artist.tracks') }}" wire:navigate
               class="glass-btn glass-btn-hover px-5 py-2.5 rounded-xl text-sm font-medium">
                Cancel
            </a>
            <button type="submit"
                    class="glass-btn-primary glass-btn-primary-hover px-6 py-2.5 rounded-xl text-sm font-semibold"
                    wire:loading.attr="disabled"
                    wire:target="audioFile,coverFile,save">
                <span wire:loading.remove wire:target="audioFile,coverFile,save">Upload Track</span>
                <span wire:loading wire:target="audioFile,coverFile">Uploading files...</span>
                <span wire:loading wire:target="save">Saving track...</span>
            </button>
        </div>
    </form>
</div>
