<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\ArtistProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
    try {
            $data = [
            'featuredArtists' => ArtistProfile::where('is_verified', true)
                            ->withCount(['tracks', 'followers'])
                            ->take(4)
                            ->get(),
                            
            'trendingTracks' => Track::withCount('plays')
                        ->orderBy('plays_count', 'desc')
                        ->take(8)
                        ->get(),
                        
            'newReleases' => Track::with('artist')
                         ->latest()
                         ->take(8)
                         ->get(),
                         
            'genres' => Track::select('genre')
                    ->distinct()
                    ->pluck('genre'),
                    
            'genreCounts' => Track::select('genre', DB::raw('count(*) as count'))
                         ->groupBy('genre')
                         ->pluck('count', 'genre')
            ];

            return view('home', $data);
        } catch (\Exception $e) {
            Log::error('Failed to render home page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->view('errors.general', [
                'message' => 'An unexpected error occurred while loading the homepage. Please try again later.'
            ], 500);
        }
    }
}
