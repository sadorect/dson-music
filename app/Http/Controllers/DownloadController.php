<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function download(Track $track, Request $request)
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        $fileExists = $disk->exists($track->file_path);

        $download = $track->downloads()->create([
            'user_id' => $request->user()->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => $fileExists ? 'completed' : 'failed',
        ]);

        if (! $fileExists) {
            return back()->with('error', 'Track file is temporarily unavailable. Please try again later.');
        }

        if ($download->status === 'completed') {
            $track->incrementDownloadCount();
        }

        return redirect($disk->temporaryUrl(
            $track->file_path,
            now()->addMinutes(5),
            ['ResponseContentDisposition' => 'attachment']
        ));
    }
}
