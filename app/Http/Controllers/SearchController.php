<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Track;
use Illuminate\Http\Request;
use App\Models\ArtistProfile;
use Illuminate\Support\Facades\Storage;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q');
        
        $tracks = Track::where('status', 'published')
            ->where('title', 'like', "%{$query}%")
            ->with('artist')
            ->take(5)
            ->get();
            
        $albums = Album::where('title', 'like', "%{$query}%")
            ->with('artist')
            ->take(5)
            ->get();
            
        $artists = ArtistProfile::where('artist_name', 'like', "%{$query}%")
            ->take(5)
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'tracks' => $tracks,
                'albums' => $albums,
                'artists' => $artists
            ]);
        }

        return view('search.index', compact('tracks', 'albums', 'artists', 'query'));
    }


public function quickSearch(Request $request)
{
    $query = $request->get('q');
    
    $results = collect([])
        ->merge(Track::where('title', 'like', "%{$query}%")->take(3)->get())
        ->merge(ArtistProfile::where('artist_name', 'like', "%{$query}%")->take(3)->get())
        ->map(function($item) {
            return [
                'id' => $item->id,
                'title' => $item instanceof Track ? $item->title : $item->artist_name,
                'subtitle' => $item instanceof Track ? $item->artist->artist_name : 'Artist',
                'image' => $item instanceof Track ? Storage::url($item->cover_art) : Storage::url($item->profile_image),
                'url' => $item instanceof Track ? route('tracks.show', $item) : route('artists.show', $item)
            ];
        });
    
    return response()->json($results);
}
}