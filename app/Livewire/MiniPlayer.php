<?php

namespace App\Livewire;

use App\Models\PlayHistory;
use App\Models\Track;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class MiniPlayer extends Component
{
    public ?int $trackId = null;
    public ?array $track = null;

    public bool $playing    = false;
    public int  $currentTime = 0;
    public int  $duration   = 0;
    public int  $volume     = 80;

    // Queue in client-side; server just provides track data
    #[On('play-track')]
    public function loadTrack(int $id): void
    {
        $model = Track::with(['artistProfile', 'album'])->find($id);
        if (!$model || !$model->is_published) {
            return;
        }

        $audioUrl = $model->getAudioUrl();

        // No audio file attached yet — silently bail
        if (!$audioUrl) {
            return;
        }

        $this->trackId = $id;
        $this->playing = true;
        $this->track   = [
            'id'          => $model->id,
            'title'       => $model->title,
            'artist'      => $model->artistProfile?->stage_name ?? 'Unknown',
            'cover'       => $model->getCoverUrl(),
            'audio_url'   => $audioUrl,
            'duration'    => $model->duration ?? 0,
            'requires_donation' => $model->requires_donation,
            'donation_amount'   => $model->donation_amount,
        ];

        $this->duration = $model->duration ?? 0;

        // Record play history (fire & forget — only for logged-in users)
        if (Auth::check()) {
            PlayHistory::create([
                'user_id'    => Auth::id(),
                'track_id'   => $id,
                'ip_address' => request()->ip(),
                'source'     => 'web',
            ]);
        }

        $model->incrementPlayCount();

        $this->dispatch('player-track-loaded', track: $this->track);
    }

    #[On('queue-track')]
    public function queueTrack(int $id): void
    {
        $model = Track::with(['artistProfile'])->find($id);
        if (!$model || !$model->is_published) {
            return;
        }

        $audioUrl = $model->getAudioUrl();
        if (!$audioUrl) {
            return;
        }

        $trackData = [
            'id'        => $model->id,
            'title'     => $model->title,
            'artist'    => $model->artistProfile?->stage_name ?? 'Unknown',
            'cover'     => $model->getCoverUrl(),
            'audio_url' => $audioUrl,
            'duration'  => $model->duration ?? 0,
        ];

        $this->dispatch('player-queue-add', track: $trackData);
    }

    #[On('play-playlist')]
    public function playPlaylist(array $ids): void
    {
        if (empty($ids)) return;

        $tracks = Track::with(['artistProfile'])
            ->whereIn('id', $ids)
            ->where('is_published', true)
            ->get()
            ->sortBy(fn($t) => array_search($t->id, $ids))
            ->values();

        $first = true;
        foreach ($tracks as $model) {
            $audioUrl = $model->getAudioUrl();
            if (!$audioUrl) continue;

            $trackData = [
                'id'        => $model->id,
                'title'     => $model->title,
                'artist'    => $model->artistProfile?->stage_name ?? 'Unknown',
                'cover'     => $model->getCoverUrl(),
                'audio_url' => $audioUrl,
                'duration'  => $model->duration ?? 0,
            ];

            if ($first) {
                if (Auth::check()) {
                    PlayHistory::create([
                        'user_id'    => Auth::id(),
                        'track_id'   => $model->id,
                        'ip_address' => request()->ip(),
                        'source'     => 'web',
                    ]);
                }
                $model->incrementPlayCount();
                $this->dispatch('player-track-loaded', track: $trackData);
                $first = false;
            } else {
                $this->dispatch('player-queue-add', track: $trackData);
            }
        }
    }

    #[On('queue-playlist')]
    public function queuePlaylist(array $ids): void
    {
        if (empty($ids)) return;

        $tracks = Track::with(['artistProfile'])
            ->whereIn('id', $ids)
            ->where('is_published', true)
            ->get()
            ->sortBy(fn($t) => array_search($t->id, $ids))
            ->values();

        foreach ($tracks as $model) {
            $audioUrl = $model->getAudioUrl();
            if (!$audioUrl) continue;

            $trackData = [
                'id'        => $model->id,
                'title'     => $model->title,
                'artist'    => $model->artistProfile?->stage_name ?? 'Unknown',
                'cover'     => $model->getCoverUrl(),
                'audio_url' => $audioUrl,
                'duration'  => $model->duration ?? 0,
            ];

            $this->dispatch('player-queue-add', track: $trackData);
        }
    }

    #[On('player-paused')]
    public function onPaused(): void
    {
        $this->playing = false;
    }

    #[On('player-resumed')]
    public function onResumed(): void
    {
        $this->playing = true;
    }

    public function render()
    {
        return view('livewire.mini-player');
    }
}
