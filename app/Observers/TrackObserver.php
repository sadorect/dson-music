<?php

namespace App\Observers;

use App\Models\Track;
use App\Services\CacheService;

class TrackObserver
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the Track "created" event.
     */
    public function created(Track $track): void
    {
        $this->clearRelevantCache($track);
    }

    /**
     * Handle the Track "updated" event.
     */
    public function updated(Track $track): void
    {
        $this->clearRelevantCache($track);
    }

    /**
     * Handle the Track "deleted" event.
     */
    public function deleted(Track $track): void
    {
        $this->clearRelevantCache($track);
    }

    /**
     * Clear all relevant cache when track changes
     */
    protected function clearRelevantCache(Track $track): void
    {
        $this->cacheService->clearTrending();
        $this->cacheService->clearGenreCounts();
        $this->cacheService->clearNewReleases();
        $this->cacheService->clearHomeStats();

        if ($track->artist_id) {
            $this->cacheService->clearArtistCache($track->artist_id);
        }
    }
}
