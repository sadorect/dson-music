<?php

namespace App\Observers;

use App\Models\ArtistProfile;
use App\Services\CacheService;

class ArtistProfileObserver
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the ArtistProfile "created" event.
     */
    public function created(ArtistProfile $artistProfile): void
    {
        $this->clearRelevantCache($artistProfile);
    }

    /**
     * Handle the ArtistProfile "updated" event.
     */
    public function updated(ArtistProfile $artistProfile): void
    {
        $this->clearRelevantCache($artistProfile);
    }

    /**
     * Handle the ArtistProfile "deleted" event.
     */
    public function deleted(ArtistProfile $artistProfile): void
    {
        $this->clearRelevantCache($artistProfile);
    }

    /**
     * Clear all relevant cache when artist profile changes
     */
    protected function clearRelevantCache(ArtistProfile $artistProfile): void
    {
        $this->cacheService->clearFeaturedArtists();
        $this->cacheService->clearHomeStats();
        $this->cacheService->clearArtistCache($artistProfile->id);
    }
}
