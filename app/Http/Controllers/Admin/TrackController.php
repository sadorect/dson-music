<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function index(Request $request)
    {
        $tracks = Track::with(['artist.user', 'album'])
            ->withCount('plays')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhereHas('artist', fn ($a) => $a->where('artist_name', 'like', '%' . $request->search . '%'));
            })
            ->when($request->filled('genre'), fn ($q) => $q->where('genre', $request->genre))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

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
            'is_featured' => 'boolean',
        ]);

        $track->update($validated);

        return redirect()->route('admin.tracks.index')
            ->with('success', 'Track updated successfully');
    }
}
