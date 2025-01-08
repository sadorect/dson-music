<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Track;
use Illuminate\Http\Request;
use App\Models\ArtistProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SearchController extends Controller
{
    public function index(Request $request)
{
    $query = $request->get('q');
    
    $tracks = Track::where('status', 'published')
        ->where('title', 'like', "%{$query}%")
        ->with('artist')
        ->get();
        
    $artists = ArtistProfile::where('artist_name', 'like', "%{$query}%")
        ->get();

    return view('search.index', compact('tracks', 'artists', 'query'));
}


    public function quickSearch(Request $request)
    { 
        dd('Quick search request', ['query' => $request->get('q')]);
        Log::info('Quick search request', ['query' => $request->get('q')]);
        $query = $request->get('q');
       
        $tracks = Track::where('status', 'published')
            ->where('title', 'like', "%{$query}%")
            ->with('artist')
            ->take(3)
            ->get()
            ->map(function($track) {
                return [
                    'id' => $track->id,
                    'title' => $track->title,
                    'subtitle' => $track->artist->artist_name,
                    'image' => Storage::url($track->cover_art),
                    'url' => "/tracks/{$track->id}"
                ];
            });
            Log::info('Search results', ['tracks' => $tracks]);
        $artists = ArtistProfile::where('artist_name', 'like', "%{$query}%")
            ->take(3)
            ->get()
            ->map(function($artist) {
                return [
                    'id' => $artist->id,
                    'title' => $artist->artist_name,
                    'subtitle' => 'Artist',
                    'image' => Storage::url($artist->profile_image),
                    'url' => "/artists/{$artist->id}"
                ];
            });
    
        $results = $tracks->merge($artists);
        
        return response()->json($results->values());
    }
    
}