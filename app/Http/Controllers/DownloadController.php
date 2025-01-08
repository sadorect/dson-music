<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function download(Track $track, Request $request)
    {
        $download = $track->downloads()->create([
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        $track->increment('downloads_count');

        return response()->download(storage_path('app/' . $track->file_path));
    }
}
