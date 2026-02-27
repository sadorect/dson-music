<?php

use App\Models\Track;
use App\Models\Comment;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.glass-app')] class extends Component {

    public Track $track;
    public string $commentBody = '';

    // Resolved via route-model binding: /track/{track:slug}
    public function mount(Track $track): void
    {
        abort_if(!$track->is_published, 404);
        $this->track = $track->load(['artistProfile.user', 'genre', 'albums',
                                    'comments' => fn($q) => $q->whereNull('parent_id')->with('user', 'replies.user')->latest()]);
    }

    public function with(): array
    {
        return ['track' => $this->track];
    }

    public function play(): void
    {
        $this->dispatch('play-track', id: $this->track->id);
    }

    public function submitComment(): void
    {
        $this->validate(['commentBody' => ['required', 'string', 'min:2', 'max:500']]);

        $user = auth()->user();
        abort_unless($user, 403);

        Comment::create([
            'user_id'    => $user->id,
            'track_id'   => $this->track->id,
            'body'       => $this->commentBody,
            'parent_id'  => null,
        ]);

        $this->commentBody = '';
        $this->track->load(['comments' => fn($q) => $q->whereNull('parent_id')->with('user', 'replies.user')->latest()]);
    }
};
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">

        {{-- Hero section --}}
        <div class="flex flex-col sm:flex-row gap-8 mb-10">
            {{-- Cover art --}}
            <div class="w-full sm:w-56 h-56 shrink-0 rounded-2xl overflow-hidden shadow-2xl">
                @if($track->getFirstMediaUrl('cover'))
                    <img src="{{ $track->getFirstMediaUrl('cover', 'large') }}" alt="{{ $track->title }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-purple-800 to-indigo-900 flex items-center justify-center">
                        <svg class="w-20 h-20 text-white/20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/></svg>
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="flex-1 flex flex-col justify-between">
                <div>
                    <div class="flex flex-wrap gap-2 mb-2">
                        @if($track->genre)
                            <a href="{{ route('browse', ['genre' => $track->genre->slug]) }}"
                               class="text-xs bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full hover:bg-purple-500/30 transition">
                                {{ $track->genre->name }}
                            </a>
                        @endif
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-white mb-1">{{ $track->title }}</h1>
                    <a href="{{ route('artist.page', $track->artistProfile) }}"
                       class="text-lg text-purple-300 hover:text-purple-200 transition">
                        {{ $track->artistProfile->stage_name ?? $track->artistProfile->user->name }}
                    </a>
                    @if($track->artistProfile->is_verified)
                        <span class="ml-1 text-sm text-purple-400">✓</span>
                    @endif
                    <p class="text-white/50 text-sm mt-2">
                        {{ number_format($track->play_count) }} plays
                        @if($track->formatted_duration)
                            · {{ $track->formatted_duration }}
                        @endif
                    </p>
                    @if($track->description)
                        <p class="text-white/70 mt-3 text-sm leading-relaxed">{{ $track->description }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-3 mt-4">
                    @if($track->requires_donation)
                        {{-- Unlock / donation gate --}}
                        @livewire('unlock-track', ['track' => $track], key('unlock-'.$track->id))
                    @else
                        <button wire:click="play"
                                class="flex items-center gap-2 bg-purple-600 hover:bg-purple-500 text-white px-6 py-3 rounded-full font-semibold transition shadow-lg">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            Play
                        </button>
                    @endif
                    @livewire('like-button', ['trackId' => $track->id], key('like-'.$track->id))
                </div>
            </div>
        </div>

        {{-- Comments section --}}
        <div class="bg-white/5 rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">
                Comments ({{ $track->comments->count() }})
            </h2>

            @auth
                <form wire:submit="submitComment" class="flex gap-3 mb-6">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-700 to-indigo-800 flex items-center justify-center shrink-0 text-sm font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 flex gap-2">
                        <input
                            wire:model="commentBody"
                            type="text"
                            placeholder="Add a comment…"
                            class="flex-1 bg-white/10 border border-white/20 rounded-xl px-4 py-2 text-white placeholder-white/40 text-sm focus:outline-none focus:border-purple-400">
                        <button type="submit"
                                class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
                            Post
                        </button>
                    </div>
                </form>
            @else
                <p class="text-white/50 text-sm mb-6">
                    <a href="{{ route('login') }}" class="text-purple-400 hover:underline">Sign in</a> to leave a comment.
                </p>
            @endauth

            <div class="space-y-4">
                @forelse($track->comments->whereNull('parent_id') as $comment)
                    <div class="flex gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-700 to-indigo-800 flex items-center justify-center shrink-0 text-sm font-bold text-white">
                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-white font-medium text-sm">{{ $comment->user->name }}</span>
                                <span class="text-white/40 text-xs">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-white/70 text-sm mt-0.5">{{ $comment->body }}</p>
                            {{-- Replies --}}
                            @foreach($comment->replies as $reply)
                                <div class="flex gap-3 mt-3 pl-4 border-l border-white/10">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-purple-700 to-indigo-800 flex items-center justify-center shrink-0 text-xs font-bold text-white">
                                        {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-white font-medium text-xs">{{ $reply->user->name }}</span>
                                            <span class="text-white/40 text-xs">{{ $reply->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-white/70 text-xs mt-0.5">{{ $reply->body }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-white/40 text-sm">No comments yet. Be the first!</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
