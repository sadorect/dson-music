<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function index()
    {
        $tracks = Track::with(['artist.user', 'album'])
            ->withCount('plays')
            ->latest()
            ->paginate(20);
            
        return view('admin.tracks.index', compact('tracks'));
    }

    public function show(Track $track)
    {
        $track->load(['artist.user', 'album', 'plays', 'likes', 'comments']);
        return view('admin.tracks.show', compact('track'));
    }


    public function edit(Track $track)
    {
        return view('admin.tracks.edit', compact('track'));
    }

    public function update(Request $request, Track $track)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:published,draft,private',
            'is_featured' => 'boolean'
        ]);

        $track->update($validated);
        
        return redirect()->route('admin.tracks.index')
            ->with('success', 'Track updated successfully');
    }
}
