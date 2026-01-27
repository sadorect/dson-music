<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AlbumController extends Controller
{
    public function index()
    {
        $albums = auth()->user()->artistProfile->albums()->latest()->paginate(10);

        return view('artist.albums.index', compact('albums'));
    }

    public function create()
    {
        return view('artist.albums.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'cover_art' => 'required|image|max:2048',
            'release_date' => 'required|date',
            'description' => 'nullable|string',
            'type' => 'required|in:album,EP,single',
            'status' => 'required|in:draft,published,private',
        ]);

        $album = new Album($validated);
        $album->artist_id = auth()->user()->artistProfile->id;
        $album->cover_art = $request->file('cover_art')->store('albums', 'public');
        $album->save();

        ActivityLogger::log(
            auth()->id(),
            'album_created',
            "Created new {$album->type}: {$album->title}"
        );

        return redirect()->route('artist.albums.index')
            ->with('success', 'Album created successfully');
    }

    public function show(Album $album)
    {
        $tracks = $album->tracks()->orderBy('created_at', 'desc')->get();

        return view('artist.albums.show', compact('album', 'tracks'));
    }

    public function edit(Album $album)
    {
        return view('artist.albums.edit', compact('album'));
    }

    public function update(Request $request, Album $album)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'cover_art' => 'nullable|image|max:2048',
            'release_date' => 'required|date',
            'description' => 'nullable|string',
            'type' => 'required|in:album,EP,single',
            'status' => 'required|in:draft,published,private',
        ]);

        if ($request->hasFile('cover_art')) {
            $validated['cover_art'] = $request->file('cover_art')->store('albums', 'public');
        }

        $album->update($validated);

        ActivityLogger::log(
            auth()->id(),
            'album_updated',
            "Updated {$album->type}: {$album->title}"
        );

        return redirect()->route('artist.albums.index')
            ->with('success', 'Album updated successfully');
    }

    public function destroy(Album $album)
    {
        // Delete the album cover art from storage
        if ($album->cover_art) {
            Storage::delete($album->cover_art);
        }

        $album->delete();

        return redirect()->route('artist.albums.index')
            ->with('success', 'Album deleted successfully');
    }
}
