<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AudioFileRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof \Illuminate\Http\UploadedFile) {
            $fail('The :attribute must be a file.');
            return;
        }

        $allowedMimes = [
            'audio/mpeg',      // MP3
            'audio/mp3',       // MP3 (alternative)
            'audio/wav',       // WAV
            'audio/wave',      // WAV (alternative)
            'audio/ogg',       // OGG
            'audio/vorbis',    // OGG Vorbis
            'audio/flac',      // FLAC
            'audio/x-flac',    // FLAC (alternative)
        ];

        $allowedExtensions = ['mp3', 'wav', 'ogg', 'flac'];

        // Check MIME type
        if (!in_array($value->getMimeType(), $allowedMimes)) {
            $fail('The :attribute must be a valid audio file (MP3, WAV, OGG, or FLAC).');
            return;
        }

        // Check file extension
        $extension = strtolower($value->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions)) {
            $fail('The :attribute must have a valid audio file extension (mp3, wav, ogg, or flac).');
            return;
        }

        // Additional security: check if file content matches extension
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $value->getPathname());
        finfo_close($finfo);

        if (!in_array($detectedMime, $allowedMimes)) {
            $fail('The :attribute file content does not match its extension.');
            return;
        }

        // Check minimum file size (100KB to prevent empty files)
        if ($value->getSize() < 100 * 1024) {
            $fail('The :attribute must be at least 100KB.');
            return;
        }

        // Check if file is actually readable
        if (!$value->isReadable()) {
            $fail('The :attribute could not be read. Please upload a valid file.');
            return;
        }
    }
}