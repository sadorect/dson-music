<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Support\Facades\Storage;

class PublicTrackController extends Controller
{
    public function index()
    {
        $tracks = Track::where('status', 'published')
            ->with('artist:id,artist_name')
            ->select(['id', 'title', 'file_path', 'cover_art', 'artist_id'])
            ->latest()
            ->get()
            ->map(function ($track) {
                return [
                    'id' => $track->id,
                    'title' => $track->title,
                    'artist' => $track->artist->artist_name,
                    'artwork' => $track->cover_art ? Storage::disk('s3')->url($track->cover_art) : null,
                    'audioUrl' => Storage::disk('s3')->url($track->file_path)
                ];
            });
        return response()->json($tracks);
    }

    public function show(Track $track)
    {
        $relatedTracks = Track::where('genre', $track->genre)
            ->where('id', '!=', $track->id)
            ->take(4)
            ->get();

        return view('tracks.show', compact('track', 'relatedTracks'));
    }

}
