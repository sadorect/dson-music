<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaylistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of playlists.
     */
    public function index()
    {
        $playlists = Playlist::query()
            ->where('is_public', true)
            ->with('user')
            ->withCount('tracks')
            ->latest()
            ->paginate(12);

        return view('playlists.index', compact('playlists'));
    }

    /**
     * Display user's playlists.
     */
    public function myPlaylists()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $playlists = $user
            ->playlists()
            ->withCount('tracks')
            ->latest()
            ->get();

        return view('playlists.my-playlists', compact('playlists'));
    }

    /**
     * Show the form for creating a new playlist.
     */
    public function create()
    {
        return view('playlists.create');
    }

    /**
     * Store a newly created playlist in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
        ]);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $playlist = $user->playlists()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_public' => $validated['is_public'] ?? true,
        ]);

        return redirect()
            ->route('playlists.show', $playlist)
            ->with('success', 'Playlist created successfully!');
    }

    /**
     * Display the specified playlist.
     */
    public function show(Playlist $playlist)
    {
        // Check if playlist is accessible
        if (! $playlist->is_public && (! Auth::check() || Auth::id() !== $playlist->user_id)) {
            abort(403, 'This playlist is private.');
        }

        $playlist->load(['user', 'tracks.artist', 'tracks.album']);

        return view('playlists.show', compact('playlist'));
    }

    /**
     * Show the form for editing the specified playlist.
     */
    public function edit(Playlist $playlist)
    {
        $this->authorize('update', $playlist);

        return view('playlists.edit', compact('playlist'));
    }

    /**
     * Update the specified playlist in storage.
     */
    public function update(Request $request, Playlist $playlist)
    {
        $this->authorize('update', $playlist);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
        ]);

        $playlist->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_public' => $validated['is_public'] ?? $playlist->is_public,
        ]);

        return redirect()
            ->route('playlists.show', $playlist)
            ->with('success', 'Playlist updated successfully!');
    }

    /**
     * Remove the specified playlist from storage.
     */
    public function destroy(Playlist $playlist)
    {
        $this->authorize('delete', $playlist);

        $playlist->delete();

        return redirect()
            ->route('playlists.my-playlists')
            ->with('success', 'Playlist deleted successfully!');
    }

    /**
     * Add a track to the playlist.
     */
    public function addTrack(Request $request, Playlist $playlist)
    {
        $this->authorize('update', $playlist);

        $request->validate([
            'track_id' => 'required|exists:tracks,id',
        ]);

        // Check if track already exists in playlist
        if ($playlist->tracks()->where('track_id', $request->track_id)->exists()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Track already in playlist.',
                ], 409);
            }

            return back()->with('error', 'Track already in playlist.');
        }

        // Get the highest position and add 1
        $position = $playlist->tracks()->max('position') + 1;

        $playlist->tracks()->attach($request->track_id, [
            'position' => $position,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Track added to playlist!',
                'playlist' => [
                    'id' => $playlist->id,
                    'name' => $playlist->name,
                ],
            ]);
        }

        return back()->with('success', 'Track added to playlist!');
    }

    /**
     * Remove a track from the playlist.
     */
    public function removeTrack(Playlist $playlist, Track $track)
    {
        $this->authorize('update', $playlist);

        $playlist->tracks()->detach($track->id);

        // Reorder remaining tracks
        $tracks = $playlist->tracks()->orderBy('position')->get();
        foreach ($tracks as $index => $t) {
            $playlist->tracks()->updateExistingPivot($t->id, ['position' => $index]);
        }

        return back()->with('success', 'Track removed from playlist!');
    }

    /**
     * Reorder tracks in the playlist.
     */
    public function reorderTracks(Request $request, Playlist $playlist)
    {
        $this->authorize('update', $playlist);

        $request->validate([
            'track_ids' => 'required|array',
            'track_ids.*' => 'exists:tracks,id',
        ]);

        foreach ($request->track_ids as $position => $trackId) {
            $playlist->tracks()->updateExistingPivot($trackId, ['position' => $position]);
        }

        return response()->json(['success' => true]);
    }
}
