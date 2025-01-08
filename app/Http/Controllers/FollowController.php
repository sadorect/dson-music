<?php

namespace App\Http\Controllers;

use App\Models\ArtistProfile;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow(ArtistProfile $artist)
    {
        auth()->user()->follows()->create([
            'artist_profile_id' => $artist->id
        ]);

        return back()->with('success', 'You are now following ' . $artist->artist_name);
    }

    public function unfollow(ArtistProfile $artist)
    {
        auth()->user()->follows()
            ->where('artist_profile_id', $artist->id)
            ->delete();

        return back()->with('success', 'You have unfollowed ' . $artist->artist_name);
    }
}
