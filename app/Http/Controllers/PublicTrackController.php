<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PublicTrackController extends Controller
{
    public function index()
    {
        $tracks = Track::where('status', 'published')
            ->with('artist:id,artist_name')
            ->select(['id', 'title', 'file_path', 'cover_art', 'artist_id'])
            ->latest()
            ->get()
            ->map(function ($track) {
                return [
                    'id' => $track->id,
                    'title' => $track->title,
                    'artist' => $track->artist->artist_name,
                    'artwork' => $track->cover_art ? Storage::disk('s3')->url($track->cover_art) : null,
                    'audioUrl' => route('tracks.stream', $track),
                    'format' => $this->resolveAudioFormat($track->file_path),
                ];
            });

        return response()->json($tracks);
    }

    public function stream(Track $track)
    {
        $path = $track->file_path;

        if (! $path) {
            abort(404);
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return redirect()->away($path);
        }

        if (Storage::disk('public')->exists($path)) {
            $localPath = Storage::disk('public')->path($path);

            if (File::exists($localPath)) {
                return response()->file($localPath, [
                    'Accept-Ranges' => 'bytes',
                    'Cache-Control' => 'public, max-age=3600',
                    'Content-Type' => $this->resolveMimeType($path),
                ]);
            }
        }

        $disk = Storage::disk('s3');
        try {
            $url = $disk->temporaryUrl($path, now()->addMinutes(20));

            return redirect()->away($url);
        } catch (\Throwable $e) {
            try {
                $url = $disk->url($path);

                return redirect()->away($url);
            } catch (\Throwable $inner) {
                abort(404);
            }
        }
    }

    public function show(Track $track)
    {
        $relatedTracks = Track::where('genre', $track->genre)
            ->where('id', '!=', $track->id)
            ->take(4)
            ->get();

        return view('tracks.show', compact('track', 'relatedTracks'));
    }

    private function resolveAudioFormat(?string $path): string
    {
        if (! $path) {
            return 'mp3';
        }

        $extension = strtolower(pathinfo(parse_url($path, PHP_URL_PATH) ?? $path, PATHINFO_EXTENSION));

        return match ($extension) {
            'wav', 'ogg', 'aac', 'm4a', 'flac' => $extension,
            default => 'mp3',
        };
    }

    private function resolveMimeType(string $path): string
    {
        return match ($this->resolveAudioFormat($path)) {
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'aac' => 'audio/aac',
            'm4a' => 'audio/mp4',
            'flac' => 'audio/flac',
            default => 'audio/mpeg',
        };
    }
}
