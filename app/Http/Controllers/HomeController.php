<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function index()
    {
        try {
            $data = [
                'featuredArtists' => $this->cacheService->getFeaturedArtists(4),
                'trendingTracks' => $this->cacheService->getTrendingTracks(8),
                'newReleases' => $this->cacheService->getNewReleases(8),
                'genres' => Track::select('genre')->distinct()->pluck('genre'),
                'genreCounts' => $this->cacheService->getGenreCounts(),
                'stats' => $this->cacheService->getHomeStats(),
            ];

            return view('home', $data);
        } catch (\Exception $e) {
            Log::error('Failed to render home page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->view('errors.general', [
                'message' => 'An unexpected error occurred while loading the homepage. Please try again later.',
            ], 500);
        }
    }
}
