<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ValidAudioUpload implements ValidationRule
{
    /**
     * @var array<int, string>
     */
    protected array $allowedExtensions = ['mp3', 'wav', 'flac', 'ogg', 'aac', 'm4a'];

    /**
     * @var array<int, string>
     */
    protected array $allowedNonAudioMimes = ['application/octet-stream', 'application/ogg', 'video/mp4'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile && !$value instanceof TemporaryUploadedFile) {
            $fail('The '.$attribute.' must be a valid uploaded file.');

            return;
        }

        $extensions = collect([
            $value->getClientOriginalExtension(),
            $value->clientExtension(),
            $value->guessExtension(),
            $value->extension(),
        ])
            ->filter()
            ->map(fn ($extension) => strtolower((string) $extension))
            ->unique()
            ->values();

        if ($extensions->intersect($this->allowedExtensions)->isEmpty()) {
            $fail('The '.$attribute.' must be an MP3, WAV, FLAC, OGG, AAC, or M4A audio file.');

            return;
        }

        $mimes = collect([
            $value->getMimeType(),
            $value->getClientMimeType(),
        ])
            ->filter()
            ->map(fn ($mime) => strtolower((string) $mime))
            ->unique()
            ->values();

        if ($mimes->isEmpty()) {
            return;
        }

        $hasAllowedMime = $mimes->contains(function (string $mime): bool {
            return str_starts_with($mime, 'audio/')
                || in_array($mime, $this->allowedNonAudioMimes, true);
        });

        if (!$hasAllowedMime) {
            $fail('The '.$attribute.' must be an MP3, WAV, FLAC, OGG, AAC, or M4A audio file.');
        }
    }
}
