<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function download(Track $track, Request $request)
{
    // Record the download attempt
    $download = $track->downloads()->create([
        'user_id' => auth()->id(),
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'status' => Storage::exists($track->file_path) ? 'completed' : 'attempted'
    ]);

    $track->increment('downloads_count');

    // Return response based on file existence
    if (Storage::exists($track->file_path)) {
        return response()->download(storage_path('app/' . $track->file_path));
    }

    return back()->with('info', 'Track download recorded for metrics testing');
}

}
