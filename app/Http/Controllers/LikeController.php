<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggleLike(Request $request, Track $track)
    {
        $user = auth()->user();

        if ($track->likes()->where('user_id', $user->id)->exists()) {
            $track->likes()->where('user_id', $user->id)->delete();
            $message = 'Like removed';
        } else {
            $track->likes()->create(['user_id' => $user->id]);
            $message = 'Track liked';
        }

        $payload = [
            'message' => $message,
            'likes_count' => $track->likes()->count(),
        ];

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($payload);
        }

        return back()->with('success', $message);
    }
}
