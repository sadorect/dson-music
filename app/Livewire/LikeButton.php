<?php

namespace App\Livewire;

use App\Models\Like;
use App\Models\Track;
use Livewire\Component;

class LikeButton extends Component
{
    public int  $trackId;
    public bool $liked = false;
    public int  $count = 0;

    public function mount(int $trackId): void
    {
        $this->trackId = $trackId;
        $this->refresh();
    }

    public function toggle(): void
    {
        if (!auth()->check()) {
            $this->dispatch('open-modal', 'login-required');
            return;
        }

        $exists = Like::where('user_id', auth()->id())
            ->where('track_id', $this->trackId)
            ->exists();

        if ($exists) {
            Like::where('user_id', auth()->id())->where('track_id', $this->trackId)->delete();
        } else {
            Like::create(['user_id' => auth()->id(), 'track_id' => $this->trackId]);
        }

        $this->refresh();
    }

    protected function refresh(): void
    {
        $this->liked = auth()->check()
            && Like::where('user_id', auth()->id())->where('track_id', $this->trackId)->exists();
        $this->count = Like::where('track_id', $this->trackId)->count();
    }

    public function render()
    {
        return view('livewire.like-button');
    }
}
