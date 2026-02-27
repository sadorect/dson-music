<?php

namespace App\Livewire;

use App\Models\Donation;
use App\Models\Track;
use Livewire\Component;

class UnlockTrack extends Component
{
    public Track $track;

    /** Has the current user already unlocked this track? */
    public bool $unlocked = false;

    public function mount(Track $track): void
    {
        $this->track = $track;

        if (auth()->check()) {
            $this->unlocked = Donation::where('user_id', auth()->id())
                ->where('track_id', $track->id)
                ->where('type', 'unlock')
                ->where('status', 'completed')
                ->exists();
        }
    }

    /**
     * Called from Alpine/JS after a successful Stripe payment.
     * Confirms the unlock state without page reload.
     */
    public function confirmUnlocked(): void
    {
        $this->unlocked = Donation::where('user_id', auth()->id())
            ->where('track_id', $this->track->id)
            ->where('type', 'unlock')
            ->where('status', 'completed')
            ->exists();

        if ($this->unlocked) {
            $this->dispatch('play-track', id: $this->track->id);
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.unlock-track');
    }
}
