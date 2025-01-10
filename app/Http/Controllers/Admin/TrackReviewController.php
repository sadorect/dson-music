<?php

namespace App\Http\Controllers\Admin;

use App\Models\Track;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\TrackApproved;
use App\Notifications\TrackRejected;

class TrackReviewController extends Controller
{
    public function index()
    {
        $pendingTracks = Track::where('approval_status', 'pending')
            ->with('artist')
            ->latest()
            ->paginate(10);
            
        return view('admin.tracks.review.index', compact('pendingTracks'));
    }

    public function show(Track $track)
    {
        return view('admin.tracks.review.show', compact('track'));
    }

    public function approve(Track $track)
    {
        $track->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'status' => 'published'
        ]);

        $track->artist->user->notify(new TrackApproved($track));

        return redirect()->route('admin.tracks.review.index')
            ->with('success', 'Track approved and published successfully');
    }

    public function reject(Request $request, Track $track)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10'
        ]);

        $track->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'status' => 'draft'
        ]);

        $track->artist->user->notify(new TrackRejected($track));

        return redirect()->route('admin.tracks.review.index')
            ->with('success', 'Track rejected with feedback');
    }
}
