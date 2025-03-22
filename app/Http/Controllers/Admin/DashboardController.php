<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Track;
use App\Models\ArtistProfile;

class DashboardController extends Controller
{
    public function index()
    {
        \Log::info('User type: ' . auth()->user()->user_type);
        \Log::info('Is super admin: ' . (auth()->user()->is_super_admin ? 'Yes' : 'No'));
        \Log::info('Admin permissions: ' . json_encode(auth()->user()->admin_permissions));
        
        $stats = [
            'users_count' => User::count(),
            'tracks_count' => Track::count(),
            'artists_count' => ArtistProfile::count(),
            'recent_tracks' => Track::latest()->take(5)->get(),
            'recent_users' => User::latest()->take(5)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
