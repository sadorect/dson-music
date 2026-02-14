<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrackUploadRequest extends FormRequest
{
    /**
     * Allowed genres whitelist
     */
    protected const ALLOWED_GENRES = [
        'hip-hop', 'rap', 'pop', 'rock', 'r&b', 'soul',
        'jazz', 'blues', 'country', 'electronic', 'edm',
        'house', 'techno', 'dubstep', 'reggae', 'afrobeat',
        'gospel', 'classical', 'indie', 'alternative', 'metal',
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only artists can upload tracks
        return $this->user() && $this->user()->isArtist() && $this->user()->artistProfile;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Track metadata
            'title' => 'required|string|max:255|min:1',
            'album_id' => 'nullable|exists:albums,id',
            'genre' => ['required', 'string', Rule::in(self::ALLOWED_GENRES)],
            'release_date' => 'required|date',
            'status' => 'required|in:draft,published,private',
            'download_type' => 'required|in:free,donate',
            'minimum_donation' => 'required_if:download_type,donate|nullable|numeric|min:0.01',

            // Audio file validation
            'track_file' => [
                'required',
                'file',
                'mimes:mp3,wav,flac,m4a,aac',
                'max:51200', // 50MB max
                'mimetypes:audio/mpeg,audio/x-wav,audio/flac,audio/mp4,audio/aac',
            ],

            // Cover image validation
            'cover_art' => [
                'nullable',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120', // 5MB max
                'mimetypes:image/jpeg,image/png,image/webp',
                'dimensions:min_width=300,min_height=300,max_width=5000,max_height=5000',
            ],

            // Optional metadata
            'lyrics' => 'nullable|string|max:10000',
            'description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'track_file.mimes' => 'The track must be an MP3, WAV, FLAC, M4A, or AAC file.',
            'track_file.mimetypes' => 'Invalid audio file type detected.',
            'track_file.max' => 'The track file may not be larger than 50MB.',
            'cover_art.dimensions' => 'Cover image must be at least 300x300 pixels and no larger than 5000x5000 pixels.',
            'cover_art.max' => 'The cover image may not be larger than 5MB.',
            'genre.in' => 'The selected genre is not valid. Please choose from the approved genres list.',
            'minimum_donation.required_if' => 'A minimum donation is required when download type is donate.',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'You must be an artist with a complete profile to upload tracks.'
        );
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional MIME type verification using getid3
            if ($this->hasFile('track_file')) {
                try {
                    $file = $this->file('track_file');
                    $getId3 = new \getID3;
                    $fileInfo = $getId3->analyze($file->getPathname());

                    // Verify it's actually an audio file
                    if (! isset($fileInfo['fileformat']) || $fileInfo['fileformat'] !== 'mp3') {
                        if (! in_array($fileInfo['fileformat'] ?? '', ['mp3', 'flac', 'aac', 'wav', 'm4a'])) {
                            $validator->errors()->add('track_file', 'The uploaded file is not a valid audio file.');
                        }
                    }
                } catch (\Exception $e) {
                    $validator->errors()->add('track_file', 'Unable to verify audio file format.');
                }
            }
        });
    }
}
