<?php

namespace App\Http\Requests\Track;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ImageFileRule;

class UpdateTrackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->track);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'cover_image' => ['nullable', 'image', new ImageFileRule(), 'max:2048'],
            'genre' => 'required|string|in:pop,rock,jazz,classical,electronic,hip-hop,rap,country,folk,blues,reggae',
            'album_id' => 'nullable|exists:albums,id',
            'description' => 'nullable|string|max:1000',
            'lyrics' => 'nullable|string|max:5000',
            'release_date' => 'nullable|date|after_or_equal:today',
            'is_explicit' => 'boolean',
            'status' => 'nullable|in:draft,pending,published,rejected',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a track title.',
            'title.max' => 'Track title cannot exceed 255 characters.',
            'cover_image.max' => 'Cover image cannot exceed 2MB.',
            'genre.required' => 'Please select a genre.',
            'genre.in' => 'Please select a valid genre.',
            'album_id.exists' => 'Selected album does not exist.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'lyrics.max' => 'Lyrics cannot exceed 5000 characters.',
            'release_date.after_or_equal' => 'Release date cannot be in the past.',
            'status.in' => 'Invalid status selected.',
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
        
        // Only allow status changes for admins or track owners with approval
        if (isset($data['status'])) {
            $user = $this->user();
            if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                unset($data['status']);
            }
        }
        
        return $data;
    }
}