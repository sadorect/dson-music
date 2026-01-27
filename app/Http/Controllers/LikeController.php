<?php

namespace App\Http\Controllers;

use App\Models\Track;

class LikeController extends Controller
{
    public function toggleLike(Track $track)
    {
        $user = auth()->user();

        if ($track->likes()->where('user_id', $user->id)->exists()) {
            $track->likes()->where('user_id', $user->id)->delete();
            $message = 'Like removed';
        } else {
            $track->likes()->create(['user_id' => $user->id]);
            $message = 'Track liked';
        }

        return response()->json([
            'message' => $message,
            'likes_count' => $track->likes()->count(),
        ]);
    }
}
