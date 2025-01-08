<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtistProfile;
use Illuminate\Http\Request;

class ArtistController extends Controller
{
    public function index()
    {
        $artists = ArtistProfile::withCount(['tracks', 'albums'])
            ->latest()
            ->paginate(20);
            
        return view('admin.artists.index', compact('artists'));
    }

    public function edit(ArtistProfile $artist)
    {
        return view('admin.artists.edit', compact('artist'));
    }

    public function update(Request $request, ArtistProfile $artist)
    {
        $validated = $request->validate([
            'artist_name' => 'required|string|max:255',
            'is_verified' => 'boolean',
            'status' => 'required|in:active,suspended'
        ]);

        $artist->update($validated);
        
        return redirect()->route('admin.artists.index')
            ->with('success', 'Artist updated successfully');
    }
}
