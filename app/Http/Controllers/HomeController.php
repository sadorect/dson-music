<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\ArtistProfile;

class HomeController extends Controller
{
    public function index()
    {
        $data = [
            'featuredArtists' => ArtistProfile::where('is_verified', true)
                                            ->withCount(['tracks', 'followers'])
                                            ->take(4)
                                            ->get(),
                                            
            'trendingTracks' => Track::withCount('plays')
                                    ->orderBy('plays_count', 'desc')
                                    ->take(8)
                                    ->get(),
                                    
            'newReleases' => Track::latest()
                                 ->take(8)
                                 ->get(),
        ];

        return view('home', $data);
    }
}
