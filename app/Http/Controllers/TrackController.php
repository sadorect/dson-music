<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackUploadRequest;
use App\Models\PlayHistory;
use App\Models\Track;
use App\Models\User;
use App\Notifications\NewTrackPendingApproval;
use App\Services\ActivityLogger;
use getID3;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class TrackController extends Controller
{
    public function apiIndex()
    {
        $tracks = Auth::user()->artistProfile->tracks()
            ->select(['id', 'title', 'file_path', 'cover_art'])
            ->with('artist:id,artist_name')
            ->get()
            ->map(function ($track) {
                return [
                    'id' => $track->id,
                    'title' => $track->title,
                    'artist' => $track->artist->artist_name,
                    'artwork' => Storage::url($track->cover_art),
                    'audioUrl' => Storage::url($track->file_path),
                ];
            });

        return response()->json($tracks);
    }

    public function index()
    {
        $tracks = Auth::user()->artistProfile->tracks()->latest()->paginate(10);

        return view('artist.tracks.index', compact('tracks'));
    }

    public function create()
    {
        $albums = Auth::user()->artistProfile ?
    Auth::user()->artistProfile->albums()->pluck('title', 'id') :
    collect();

        return view('artist.tracks.create', compact('albums'));
    }

    public function store(TrackUploadRequest $request)
    {
        try {
            $validated = $request->validated();

            $track = new Track($validated);
            $track->artist_id = Auth::user()->artistProfile->id;
            $track->approval_status = 'pending';

            if ($request->hasFile('track_file')) {
                $track->file_path = $request->file('track_file')->store('grinmuzik/tracks', 's3');

                $getID3 = new getID3;
                $fileInfo = $getID3->analyze($request->file('track_file'));

                $track->duration = isset($fileInfo['playtime_seconds']) ?
                    ceil($fileInfo['playtime_seconds']) :
                    0;
            }

            if ($request->hasFile('cover_art')) {
                $track->cover_art = $request->file('cover_art')->store('grinmuzik/covers', 's3');
            }

            $track->save();

            // Log the activity
            ActivityLogger::log(
                Auth::id(),
                'track_upload',
                "Uploaded new track: {$track->title}"
            );

            // Notify admins about new track pending approval
            Notification::send(
                User::where('user_type', 'admin')->get(),
                new NewTrackPendingApproval($track)
            );

            return redirect()->route('artist.tracks.index')
                ->with('success', 'Track "'.$track->title.'" uploaded successfully and pending approval');

        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back()
                ->with('error', 'Upload failed: '.$e->getMessage())
                ->withInput();
        }
    }

    public function show(Track $track)
    {
        return view('artist.tracks.show', compact('track'));
    }

    public function edit(Track $track)
    {
        $albums = Auth::user()->artistProfile->albums()->pluck('title', 'id');

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
            'status' => 'required|in:draft,published,private',
        ]);

        if ($request->hasFile('track_file')) {
            // Delete old file
            if ($track->file_path) {
                // Storage::delete('public/' . $track->file_path);
                Storage::disk('s3')->delete($track->file_path);
            }

            $validated['file_path'] = $request->file('track_file')->store('tracks', 'public');

            // Update duration
            $getID3 = new getID3;
            // $fileInfo = $getID3->analyze(storage_path('app/public/' . $validated['file_path']));
            $fileInfo = $getID3->analyze(Storage::disk('s3')->path($validated['file_path']));
            $validated['duration'] = ceil($fileInfo['playtime_seconds']);
        }

        /*if ($request->hasFile('cover_art')) {
            if ($track->cover_art) {
                Storage::delete('public/' . $track->cover_art);
            }
            $validated['cover_art'] = $request->file('cover_art')->store('covers', 'public');
        }*/
        if ($request->hasFile('cover_art')) {
            if ($track->cover_art) {
                Storage::disk('s3')->delete($track->cover_art);
            }
            $validated['cover_art'] = $request->file('cover_art')->store('grinmuzik/covers', 's3');
        }

        $track->update($validated);

        ActivityLogger::log(
            Auth::id(),
            'track_updated',
            "Updated track: {$track->title}"
        );

        return redirect()->route('artist.tracks.show', $track)
            ->with('success', 'Track updated successfully');
    }

    public function destroy(Track $track)
    {
        // Delete associated files
        /*if ($track->file_path) {
            Storage::delete('public/' . $track->file_path);
        }
        if ($track->cover_art) {
            Storage::delete('public/' . $track->cover_art);
        }
            */
        // Delete associated files
        if ($track->file_path) {
            Storage::disk('s3')->delete($track->file_path);
        }
        if ($track->cover_art) {
            Storage::disk('s3')->delete($track->cover_art);
        }

        $track->delete();

        ActivityLogger::log(
            Auth::id(),
            'track_deleted',
            "Deleted track: {$track->title}"
        );

        return redirect()->route('artist.tracks.index')
            ->with('success', 'Track deleted successfully');
    }

    public function recordPlay(Request $request, Track $track)
    {
        try {
            $track->incrementPlayCount();

            PlayHistory::create([
                'user_id' => Auth::id(),
                'track_id' => $track->id,
                'played_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'location' => $request->header('CF-IPCountry'),
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to record track play', [
                'track_id' => $track->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to record play right now.',
            ], 500);
        }
    }
}
