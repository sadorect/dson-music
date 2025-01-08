<?php

namespace App\Http\Controllers;

use Log;
use getID3;
use App\Models\Track;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Storage;

class TrackController extends Controller
{
    public function apiIndex()
    {
        $tracks = auth()->user()->artistProfile->tracks()
            ->select(['id', 'title', 'file_path', 'cover_art'])
            ->with('artist:id,artist_name')
            ->get()
            ->map(function ($track) {
                return [
                    'id' => $track->id,
                    'title' => $track->title,
                    'artist' => $track->artist->artist_name,
                    'artwork' => Storage::url($track->cover_art),
                    'audioUrl' => Storage::url($track->file_path)
                ];
            });

        return response()->json($tracks);
    }

    public function index()
    {
        $tracks = auth()->user()->artistProfile->tracks()->latest()->paginate(10);
        return view('artist.tracks.index', compact('tracks'));
    }

    public function create()
    {
        $albums = auth()->user()->artistProfile->albums()->pluck('title', 'id');
        return view('artist.tracks.create', compact('albums'));
    }

    public function store(Request $request)
    {
        // Check for POST size limit exceeded
        if (empty($request->all())) {
            return redirect()->back()
                ->with('error', 'Upload failed. File size exceeds the maximum allowed size of ' . ini_get('post_max_size'));
        }
    
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'genre' => 'required|string',
                'track_file' => 'required|file|mimes:mp3,wav,MP3,WAV|max:' . (int)(ini_get('upload_max_filesize')) * 1024,
                'cover_art' => 'nullable|image|max:2048',
                'release_date' => 'required|date',
                'album_id' => 'nullable|exists:albums,id',
                'status' => 'required|in:draft,published,private'
            ]);
    
            $track = new Track($validated);
            $track->artist_id = auth()->user()->artistProfile->id;
    
            if ($request->hasFile('track_file')) {
                $track->file_path = $request->file('track_file')->store('tracks', 'public');
                
                $getID3 = new getID3;
                $fileInfo = $getID3->analyze(storage_path('app/public/' . $track->file_path));
                $track->duration = ceil($fileInfo['playtime_seconds']);
            }
    
            if ($request->hasFile('cover_art')) {
                $track->cover_art = $request->file('cover_art')->store('covers', 'public');
            }
    
            $track->save();
    
            ActivityLogger::log(
                auth()->id(),
                'track_upload',
                "Uploaded new track: {$track->title}"
            );
    
            return redirect()->route('artist.tracks.index')
                ->with('success', 'Track "' . $track->title . '" uploaded successfully');
    
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Upload failed. Please check file size limits and try again.')
                ->withInput();
        }
    }
    

    public function show(Track $track)
    {
        return view('artist.tracks.show', compact('track'));
    }

    public function edit(Track $track)
    {
        $albums = auth()->user()->artistProfile->albums()->pluck('title', 'id');
        return view('artist.tracks.edit', compact('track', 'albums'));
    }

    public function update(Request $request, Track $track)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'genre' => 'required|string',
            'track_file' => 'nullable|file|mimes:mp3,wav|max:10240',
            'cover_art' => 'nullable|image|max:2048',
            'release_date' => 'required|date',
            'album_id' => 'nullable|exists:albums,id',
            'status' => 'required|in:draft,published,private'
        ]);

        if ($request->hasFile('track_file')) {
            // Delete old file
            if ($track->file_path) {
                Storage::delete('public/' . $track->file_path);
            }
            
            $validated['file_path'] = $request->file('track_file')->store('tracks', 'public');
            
            // Update duration
            $getID3 = new getID3;
            $fileInfo = $getID3->analyze(storage_path('app/public/' . $validated['file_path']));
            $validated['duration'] = ceil($fileInfo['playtime_seconds']);
        }

        if ($request->hasFile('cover_art')) {
            if ($track->cover_art) {
                Storage::delete('public/' . $track->cover_art);
            }
            $validated['cover_art'] = $request->file('cover_art')->store('covers', 'public');
        }

        $track->update($validated);

        ActivityLogger::log(
            auth()->id(),
            'track_updated',
            "Updated track: {$track->title}"
        );

        return redirect()->route('artist.tracks.show', $track)
            ->with('success', 'Track updated successfully');
    }

    public function destroy(Track $track)
    {
        // Delete associated files
        if ($track->file_path) {
            Storage::delete('public/' . $track->file_path);
        }
        if ($track->cover_art) {
            Storage::delete('public/' . $track->cover_art);
        }

        $track->delete();

        ActivityLogger::log(
            auth()->id(),
            'track_deleted',
            "Deleted track: {$track->title}"
        );

        return redirect()->route('artist.tracks.index')
            ->with('success', 'Track deleted successfully');
    }

    public function recordPlay(Track $track)
{
    $track->incrementPlayCount();
    return response()->json(['success' => true]);
}

}
