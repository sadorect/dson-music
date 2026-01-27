<?php

namespace App\Observers;

use App\Models\PlayHistory;
use App\Services\CacheService;

class PlayHistoryObserver
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the PlayHistory "created" event.
     */
    public function created(PlayHistory $playHistory): void
    {
        // Clear trending tracks as play counts changed
        $this->cacheService->clearTrending();
        $this->cacheService->clearHomeStats();
        $this->cacheService->clearGenreCounts();

        // Clear artist stats if available
        if ($playHistory->track && $playHistory->track->artist_id) {
            $this->cacheService->clearArtistCache($playHistory->track->artist_id);
        }
    }
}
