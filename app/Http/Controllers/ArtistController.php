<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArtistProfile;
use Illuminate\Support\Facades\Auth;

class ArtistController extends Controller
{
    public function showRegistrationForm()
    {
        return view('artist.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'artist_name' => 'required|unique:artist_profiles',
            'genre' => 'required',
            'bio' => 'nullable|string',
            'profile_image' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:2048'
        ]);

        $artistProfile = new ArtistProfile($validated);
        
        if ($request->hasFile('profile_image')) {
            $artistProfile->profile_image = $request->file('profile_image')->store('artist-profiles', 'public');
        }
        
        if ($request->hasFile('cover_image')) {
            $artistProfile->cover_image = $request->file('cover_image')->store('artist-covers', 'public');
        }

        $request->user()->artistProfile()->save($artistProfile);

        return redirect()->route('artist.dashboard')->with('success', 'Artist profile created successfully!');
    }

    public function dashboard()
    {
        if (!auth()->user()->artistProfile) {
            return redirect()->route('artist.register.form')
                ->with('message', 'Please complete your artist profile first');
        }
    
        $artist = auth()->user()->artistProfile;
        return view('artist.dashboard', compact('artist'));
    }

    // For authenticated artist viewing their own profile
// For public viewing of artist profiles
public function show(ArtistProfile $artist)
{
    $artist->loadCount(['tracks', 'followers'])
          ->load(['tracks' => function($query) {
              $query->withCount(['plays', 'likes', 'downloads']);
          }]);

    return view('artists.public.show', compact('artist'));
}


}

