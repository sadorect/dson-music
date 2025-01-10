<?php

namespace App\Http\Controllers\Admin;

use App\Models\Track;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\TrackApproved;
use App\Notifications\TrackRejected;

class TrackApprovalController extends Controller
{
    public function approve(Track $track)
    {
        $track->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id()
        ]);
    
        // Notify artist
        $track->artist->user->notify(new TrackApproved($track));
    
        return back()->with('success', 'Track approved successfully');
    }
    
    public function reject(Request $request, Track $track)
    {
        $track->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $request->reason
        ]);
    
        // Notify artist
        $track->artist->user->notify(new TrackRejected($track));
    
        return back()->with('success', 'Track rejected');
    }
    
}
