<?php

use App\Models\ArtistProfile;
use App\Models\Genre;
use App\Models\User;
use App\Rules\CaptchaAnswer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.guest')] class extends Component
{
    use WithFileUploads;

    public string $name        = '';
    public string $email       = '';
    public string $password    = '';
    public string $password_confirmation = '';
    public string $role        = 'listener'; // 'listener' | 'artist'
    public string $captcha     = '';

    // Artist-only fields
    public string $stage_name = '';
    public string $bio        = '';
    public array  $selectedGenres = [];
    public $avatar = null;

    public function mount(): void
    {
        $this->genres = Genre::active()->orderBy('name')->get()->toArray();
    }

    public array $genres = [];

    public function register(): void
    {
        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'in:listener,artist'],
            'captcha'  => ['required', 'integer', new CaptchaAnswer],
        ];

        if ($this->role === 'artist') {
            $rules['stage_name']     = ['required', 'string', 'max:100'];
            $rules['bio']            = ['nullable', 'string', 'max:500'];
            $rules['selectedGenres'] = ['nullable', 'array', 'max:5'];
            $rules['avatar']         = ['nullable', 'image', 'max:2048'];
        }

        $validated = $this->validate($rules);

        $user = User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $user->assignRole($this->role);

        if ($this->role === 'artist') {
            $profile = ArtistProfile::create([
                'user_id'    => $user->id,
                'stage_name' => $this->stage_name,
                'bio'        => $this->bio,
                'is_active'  => true,
                'is_approved' => false,
            ]);

            if (!empty($this->selectedGenres)) {
                $profile->genres()->sync($this->selectedGenres);
            }

            if ($this->avatar) {
                $profile->addMedia($this->avatar->getRealPath())
                    ->usingFileName('avatar.' . $this->avatar->getClientOriginalExtension())
                    ->toMediaCollection('avatar');
            }
        }

        event(new Registered($user));
        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-800">Create your account</h1>
        <p class="text-sm text-gray-500 mt-1">Join GrinMusic today — it's free</p>
    </div>

    <form wire:submit="register" enctype="multipart/form-data" class="space-y-4">

        {{-- ── Role Toggle ──────────────────────────────────────────────────── --}}
        <div class="mb-6">
            <p class="text-sm font-medium text-gray-700 mb-2">{{ __('I want to…') }}</p>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 cursor-pointer transition
                              {{ $role === 'listener' ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-red-300' }}">
                    <input type="radio" wire:model.live="role" value="listener" class="sr-only">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ $role === 'listener' ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                    <span class="text-sm font-semibold {{ $role === 'listener' ? 'text-red-600' : 'text-gray-600' }}">{{ __('Listen to music') }}</span>
                </label>

                <label class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 cursor-pointer transition
                              {{ $role === 'artist' ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-red-300' }}">
                    <input type="radio" wire:model.live="role" value="artist" class="sr-only">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ $role === 'artist' ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    <span class="text-sm font-semibold {{ $role === 'artist' ? 'text-red-600' : 'text-gray-600' }}">{{ __('Release my music') }}</span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        {{-- ── Name ──────────────────────────────────────────────────────────── --}}
        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        {{-- ── Email ─────────────────────────────────────────────────────────── --}}
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- ── Password ──────────────────────────────────────────────────────── --}}
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- ── Artist Fields ─────────────────────────────────────────────────── --}}
        @if($role === 'artist')
        <div class="mt-6 space-y-4 rounded-xl border border-red-200 bg-red-50/60 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-500">{{ __('Artist Details') }}</p>

            {{-- Stage Name --}}
            <div>
                <x-input-label for="stage_name" :value="__('Stage / Artist Name')" />
                <x-text-input wire:model="stage_name" id="stage_name" class="block mt-1 w-full" type="text" name="stage_name" placeholder="e.g. DJ Phoenix" />
                <x-input-error :messages="$errors->get('stage_name')" class="mt-2" />
            </div>

            {{-- Bio --}}
            <div>
                <x-input-label for="bio" :value="__('Bio') . ' (' . mb_strlen($bio) . '/500)'" />
                <textarea wire:model.live="bio" id="bio" name="bio" rows="3" maxlength="500"
                          class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-red-400 focus:ring-red-400 text-sm resize-none"
                          placeholder="{{ __('Tell the world about your music…') }}"></textarea>
                <x-input-error :messages="$errors->get('bio')" class="mt-2" />
            </div>

            {{-- Genres --}}
            @if(!empty($genres))
            <div>
                <x-input-label :value="__('Genres (up to 5)')" />
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($genres as $genre)
                    <label class="flex items-center gap-1 px-3 py-1 rounded-full border text-xs font-medium cursor-pointer transition
                                  {{ in_array($genre['id'], $selectedGenres) ? 'bg-red-500 text-white border-red-500' : 'bg-white text-gray-600 border-gray-300 hover:border-red-400' }}">
                        <input type="checkbox" wire:model.live="selectedGenres" value="{{ $genre['id'] }}" class="sr-only">
                        {{ $genre['name'] }}
                    </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('selectedGenres')" class="mt-2" />
            </div>
            @endif

            {{-- Avatar --}}
            <div>
                <x-input-label for="avatar" :value="__('Profile Photo (optional)')" />
                <div class="mt-1 flex items-center gap-4">
                    @if($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}" class="h-16 w-16 rounded-full object-cover ring-2 ring-red-400" alt="Preview">
                    @else
                        <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                    @endif
                    <input wire:model="avatar" id="avatar" type="file" accept="image/jpeg,image/png,image/webp"
                           class="text-sm text-gray-600 file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-600 hover:file:bg-red-100">
                </div>
                <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
            </div>
        </div>
        @endif

        {{-- ── Captcha + Submit ─────────────────────────────────────── --}}
        <x-math-captcha wire:model="captcha" error-bag="captcha" />

        <div class="pt-2">
            <x-primary-button class="w-full justify-center">
                {{ __('Create Account') }}
            </x-primary-button>
        </div>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Already have an account?
        <a href="{{ route('login') }}" wire:navigate class="text-red-500 font-medium hover:underline">Sign in</a>
    </p>
</div>
