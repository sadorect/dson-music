<?php

use App\Models\ArtistProfile;
use App\Models\Genre;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Str;

new #[Layout('layouts.glass-app')] class extends Component {

    #[Validate('required|string|min:2|max:80')]
    public string $stageName = '';

    #[Validate('nullable|string|max:500')]
    public string $bio = '';

    #[Validate('nullable|url|max:255')]
    public string $website = '';

    #[Validate('nullable|string|max:100')]
    public string $instagram = '';

    #[Validate('nullable|string|max:100')]
    public string $twitter = '';

    #[Validate('nullable|string|max:100')]
    public string $spotify = '';

    public array $selectedGenres = [];

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user->isArtist(), 403);

        // Already has a profile → skip to dashboard
        if ($user->artistProfile) {
            $this->redirect(route('artist.dashboard'), navigate: true);
        }
    }

    public function with(): array
    {
        return [
            'genres' => Genre::where('is_active', true)->orderBy('sort_order')->get(),
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();

        $slug = Str::slug($this->stageName);
        // Ensure unique slug
        $base  = $slug;
        $count = 1;
        while (ArtistProfile::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $count++;
        }

        $profile = ArtistProfile::create([
            'user_id'     => $user->id,
            'stage_name'  => $this->stageName,
            'slug'        => $slug,
            'bio'         => $this->bio ?: null,
            'website'     => $this->website ?: null,
            'instagram'   => $this->instagram ?: null,
            'twitter'     => $this->twitter ?: null,
            'spotify'     => $this->spotify ?: null,
            'is_approved' => false, // admin must approve
            'is_active'   => true,
        ]);

        if (!empty($this->selectedGenres)) {
            $profile->genres()->sync($this->selectedGenres);
        }

        $this->redirect(route('artist.dashboard'), navigate: true);
    }
};
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-xl">

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-purple-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Set Up Your Artist Profile</h1>
            <p class="text-white/50 text-sm mt-1">Tell listeners who you are. You can update this anytime.</p>
        </div>

        {{-- Card --}}
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-sm">
            <form wire:submit="save" class="space-y-5">

                {{-- Stage name --}}
                <div>
                    <label class="block text-sm font-medium text-white/80 mb-1.5">
                        Artist / Stage Name <span class="text-purple-400">*</span>
                    </label>
                    <input
                        wire:model="stageName"
                        type="text"
                        placeholder="How should fans know you?"
                        class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-white/30 focus:outline-none focus:border-purple-400 transition">
                    @error('stageName')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Bio --}}
                <div>
                    <label class="block text-sm font-medium text-white/80 mb-1.5">Bio</label>
                    <textarea
                        wire:model="bio"
                        rows="3"
                        placeholder="Tell your story in a few sentences…"
                        class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-white/30 focus:outline-none focus:border-purple-400 transition resize-none"></textarea>
                    @error('bio')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Genres --}}
                <div>
                    <label class="block text-sm font-medium text-white/80 mb-2">Genres (pick up to 3)</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($genres as $genre)
                            <label class="cursor-pointer">
                                <input type="checkbox"
                                       wire:model="selectedGenres"
                                       value="{{ $genre->id }}"
                                       class="sr-only peer">
                                <span class="inline-block px-3 py-1. 5 rounded-full text-sm border border-white/20 text-white/60
                                             peer-checked:bg-purple-600 peer-checked:border-purple-500 peer-checked:text-white
                                             hover:bg-white/10 transition">
                                    {{ $genre->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Social links (collapsible) --}}
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                            class="flex items-center gap-2 text-sm text-white/50 hover:text-white transition">
                        <svg class="w-4 h-4 transition" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        Social links (optional)
                    </button>
                    <div x-show="open" x-collapse class="mt-3 space-y-3">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30 text-sm">instagram.com/</span>
                            <input wire:model="instagram" type="text" placeholder="yourusername"
                                   class="w-full bg-white/10 border border-white/20 rounded-xl pl-32 pr-4 py-2.5 text-white placeholder-white/30 text-sm focus:outline-none focus:border-purple-400 transition">
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30 text-sm">twitter.com/</span>
                            <input wire:model="twitter" type="text" placeholder="yourusername"
                                   class="w-full bg-white/10 border border-white/20 rounded-xl pl-28 pr-4 py-2.5 text-white placeholder-white/30 text-sm focus:outline-none focus:border-purple-400 transition">
                        </div>
                        <input wire:model="spotify" type="url" placeholder="Spotify profile URL"
                               class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-white/30 text-sm focus:outline-none focus:border-purple-400 transition">
                        <input wire:model="website" type="url" placeholder="https://yourwebsite.com"
                               class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-white/30 text-sm focus:outline-none focus:border-purple-400 transition">
                    </div>
                </div>

                {{-- Approval notice --}}
                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-3 flex gap-2">
                    <svg class="w-4 h-4 text-yellow-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2Zm1 14h-2v-2h2v2Zm0-4h-2V7h2v5Z"/>
                    </svg>
                    <p class="text-yellow-300 text-xs leading-relaxed">
                        Your profile will be reviewed before going public. You can still upload tracks while waiting for approval.
                    </p>
                </div>

                <button type="submit"
                        class="w-full bg-purple-600 hover:bg-purple-500 text-white py-3 rounded-xl font-semibold transition shadow-lg">
                    <span wire:loading.remove>Create Profile</span>
                    <span wire:loading>Creating…</span>
                </button>

            </form>
        </div>
    </div>
</div>
