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
                    'artwork' => $track->cover_art ? Storage::url($track->cover_art) : null,
                    'audioUrl' => Storage::url($track->file_path)
                ];
            });

        return response()->json($tracks);
    }
}
