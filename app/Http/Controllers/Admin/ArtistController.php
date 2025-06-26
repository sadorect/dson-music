<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ArtistProfile;
use App\Http\Controllers\Controller;
use App\Notifications\ArtistVerified;

class ArtistController extends Controller
{
    public function index(Request $request)
    {
        $query = ArtistProfile::with('user')
            ->withCount('tracks');
            
             // Check if the admin has permission to manage users
    if (!can_admin('users')) {
        return redirect()->route('admin.dashboard')
            ->with('error', 'You do not have permission to manage users.');
    }
    
      if ($request->filled('type')) {
          $query->where('user_type', $request->type);
      }
      
      if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('name', 'like', "%{$request->search}%")
              ->orWhere('email', 'like', "%{$request->search}%");
        });
    }
        if ($request->filled('search')) {
            $query->where('artist_name', 'like', "%{$request->search}%");
        }
        
        if ($request->filled('status')) {
            $query->where('is_verified', $request->status === 'verified');
        }
        
        $artists = $query->latest()->paginate(20);
        
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
            'genre' => 'required|string',
            'bio' => 'nullable|string',
            'is_verified' => 'boolean',
            'profile_image' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')->store('artist-profiles', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('artist-covers', 'public');
        }

        $artist->update($validated);

        return redirect()->route('admin.artists.index')
            ->with('success', 'Artist updated successfully');
    }

    public function show(ArtistProfile $artist)
    {
        $artist->load(['tracks', 'albums']);
        return view('admin.artists.show', compact('artist'));
    }

    public function verify(ArtistProfile $artist)
{
    $artist->update([
        'is_verified' => true,
        'verified_at' => now()
    ]);

    // Send notification to artist
    $artist->user->notify(new ArtistVerified($artist));

    return redirect()->back()->with('success', 'Artist verified successfully');
}

public function unverify(ArtistProfile $artist)
{
    $artist->update([
        'is_verified' => false,
        'verified_at' => null
    ]);

    return redirect()->back()->with('success', 'Artist verification removed');
}

}
