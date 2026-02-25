<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtistProfile;
use App\Models\Track;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users_count'          => User::count(),
            'tracks_count'         => Track::count(),
            'artists_count'        => ArtistProfile::count(),
            'pending_tracks_count' => Track::whereIn('status', ['pending', 'draft'])
                                           ->orWhere('approval_status', 'pending')
                                           ->count(),
            'pending_tracks' => Track::with(['artist'])
                ->where(function ($q) {
                    $q->whereIn('status', ['pending', 'draft'])
                      ->orWhere('approval_status', 'pending');
                })
                ->latest()
                ->take(8)
                ->get(),
            'recent_tracks' => Track::with(['artist.user', 'album'])
                ->latest()
                ->take(5)
                ->get(),
            'recent_users' => User::with('artistProfile')
                ->latest()
                ->take(5)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
