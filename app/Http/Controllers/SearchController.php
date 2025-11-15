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
        $query = trim($request->get('q', ''));

        if ($query === '') {
            return response()->json([
                'tracks' => [],
                'artists' => []
            ]);
        }

        $tracks = Track::where('status', 'published')
            ->where('title', 'like', "%{$query}%")
            ->with('artist')
            ->take(5)
            ->get()
            ->map(function ($track) {
                return [
                    'id' => $track->id,
                    'title' => $track->title,
                    'subtitle' => $track->artist->artist_name,
                    'image' => $track->cover_art ? Storage::url($track->cover_art) : null,
                    'url' => route('tracks.show', $track)
                ];
            });

        $artists = ArtistProfile::where('artist_name', 'like', "%{$query}%")
            ->take(5)
            ->get()
            ->map(function ($artist) {
                return [
                    'id' => $artist->id,
                    'title' => $artist->artist_name,
                    'subtitle' => 'Artist',
                    'image' => $artist->profile_image ? Storage::url($artist->profile_image) : null,
                    'url' => route('artists.show', $artist)
                ];
            });

        Log::info('Quick search results', [
            'query' => $query,
            'tracks_count' => $tracks->count(),
            'artists_count' => $artists->count()
        ]);
        
        return response()->json([
            'tracks' => $tracks->values(),
            'artists' => $artists->values()
        ]);
    }
    
}