<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Support\Facades\Auth;

class LibraryController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $playlists = $user->playlists()
            ->withCount('tracks')
            ->latest()
            ->get();

        $recentPlays = $user->plays()
            ->with(['track.artist'])
            ->latest('played_at')
            ->take(100)
            ->get()
            ->filter(fn ($play) => $play->track)
            ->unique('track_id')
            ->take(30)
            ->values();

        $likedTracks = Track::query()
            ->whereHas('likes', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('likeable_type', Track::class);
            })
            ->with('artist')
            ->latest()
            ->take(50)
            ->get();

        $followedArtists = $user->following()
            ->withCount(['tracks', 'followers'])
            ->latest()
            ->take(30)
            ->get();

        $downloads = $user->downloads()
            ->with(['track.artist'])
            ->latest()
            ->take(50)
            ->get();

        return view('library.index', compact('playlists', 'recentPlays', 'likedTracks', 'followedArtists', 'downloads'));
    }
}
