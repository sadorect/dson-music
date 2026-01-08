<?php

namespace App\Http\Controllers\Api;

use App\Models\Track;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class TrackController extends Controller
{
    /**
     * List all approved tracks
     */
    public function index()
    {
        $tracks = Track::with('artist')
            ->where('approval_status', 'approved')
            ->latest()
            ->paginate(20);

        return response()->json($tracks);
    }

    /**
     * Get a single track
     */
    public function show(Track $track)
    {
        if ($track->approval_status !== 'approved') {
            return response()->json([
                'message' => 'Track not found or not available'
            ], 404);
        }

        $track->load('artist');

        return response()->json($track);
    }

    /**
     * Create a new track
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'album_id' => 'nullable|exists:albums,id',
            'genre' => 'required|string|max:100',
            'duration' => 'required|integer',
            'file_path' => 'required|string',
            'cover_image' => 'nullable|string',
        ]);

        // Ensure user is an artist
        if (!$request->user()->isArtist()) {
            return response()->json([
                'message' => 'Only artists can upload tracks'
            ], 403);
        }

        $track = Track::create([
            'artist_id' => $request->user()->artistProfile->id,
            'album_id' => $request->album_id,
            'title' => $request->title,
            'genre' => $request->genre,
            'duration' => $request->duration,
            'file_path' => $request->file_path,
            'cover_image' => $request->cover_image,
            'approval_status' => 'pending',
        ]);

        return response()->json($track, 201);
    }

    /**
     * Update a track
     */
    public function update(Request $request, Track $track)
    {
        // Check authorization
        if (!Gate::allows('update', $track)) {
            return response()->json([
                'message' => 'You are not authorized to update this track'
            ], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'genre' => 'sometimes|required|string|max:100',
            'cover_image' => 'nullable|string',
        ]);

        $track->update($request->only(['title', 'genre', 'cover_image']));

        return response()->json($track);
    }

    /**
     * Delete a track
     */
    public function destroy(Track $track)
    {
        // Check authorization
        if (!Gate::allows('delete', $track)) {
            return response()->json([
                'message' => 'You are not authorized to delete this track'
            ], 403);
        }

        $track->delete();

        return response()->json([
            'message' => 'Track deleted successfully'
        ], 200);
    }
}
