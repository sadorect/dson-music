<?php

namespace App\Http\Requests\Track;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\AudioFileRule;
use App\Rules\ImageFileRule;

class UploadTrackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'track_file' => ['required', 'file', new AudioFileRule(), 'max:10240'],
            'cover_image' => ['nullable', 'image', new ImageFileRule(), 'max:2048'],
            'genre' => 'required|string|in:pop,rock,jazz,classical,electronic,hip-hop,rap,country,folk,blues,reggae',
            'album_id' => 'nullable|exists:albums,id',
            'description' => 'nullable|string|max:1000',
            'lyrics' => 'nullable|string|max:5000',
            'release_date' => 'nullable|date|after_or_equal:today',
            'is_explicit' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a track title.',
            'title.max' => 'Track title cannot exceed 255 characters.',
            'track_file.required' => 'Please select an audio file to upload.',
            'track_file.max' => 'Audio file cannot exceed 10MB.',
            'cover_image.max' => 'Cover image cannot exceed 2MB.',
            'genre.required' => 'Please select a genre.',
            'genre.in' => 'Please select a valid genre.',
            'album_id.exists' => 'Selected album does not exist.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'lyrics.max' => 'Lyrics cannot exceed 5000 characters.',
            'release_date.after_or_equal' => 'Release date cannot be in the past.',
        ];
    }

    public function validated(): array
    {
        $data = parent::validated();
        
        // Sanitize string inputs
        if (isset($data['title'])) {
            $data['title'] = trim($data['title']);
        }
        if (isset($data['description'])) {
            $data['description'] = trim($data['description']);
        }
        if (isset($data['lyrics'])) {
            $data['lyrics'] = trim($data['lyrics']);
        }
        
        // Set default values
        $data['is_explicit'] = $data['is_explicit'] ?? false;
        
        return $data;
    }
}