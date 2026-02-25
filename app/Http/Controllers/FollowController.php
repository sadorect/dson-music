<?php

namespace App\Http\Controllers;

use App\Jobs\SendNotificationEmail;
use App\Models\ArtistProfile;

class FollowController extends Controller
{
    public function follow(ArtistProfile $artist)
    {
        auth()->user()->follows()->create([
            'artist_profile_id' => $artist->id,
        ]);

        // Notify the artist about their new follower
        if ($artist->user) {
            SendNotificationEmail::dispatch(
                $artist->user,
                'You have a new follower!',
                auth()->user()->name.' is now following you on DSON Music.'
            );
        }

        return back()->with('success', 'You are now following '.$artist->artist_name);
    }

    public function unfollow(ArtistProfile $artist)
    {
        auth()->user()->follows()
            ->where('artist_profile_id', $artist->id)
            ->delete();

        return back()->with('success', 'You have unfollowed '.$artist->artist_name);
    }
}
