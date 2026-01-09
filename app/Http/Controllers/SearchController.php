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
        
        if (empty($query)) {
            return view('search.index', [
                'tracks' => collect(),
                'artists' => collect(),
                'query' => $query
            ]);
        }
        
        // Use Scout search for better results
        $tracks = Track::search($query)
            ->where('status', 'published')
            ->query(fn ($builder) => $builder->with('artist'))
            ->get();
            
        $artists = ArtistProfile::search($query)->get();

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

        // Use Scout search for better relevance
        $tracks = Track::search($query)
            ->where('status', 'published')
            ->query(fn ($builder) => $builder->with('artist'))
            ->take(5)
            ->get()
            ->map(function ($track) {
                return [
                    'id' => $track->id,
                    'title' => $track->title,
                    'subtitle' => $track->artist ? $track->artist->artist_name : 'Unknown Artist',
                    'image' => $track->cover_art ? Storage::url($track->cover_art) : null,
                    'url' => route('tracks.show', $track)
                ];
            });

        $artists = ArtistProfile::search($query)
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