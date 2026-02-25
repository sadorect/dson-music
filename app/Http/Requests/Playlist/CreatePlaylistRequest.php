<?php

namespace App\Http\Requests\Playlist;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ImageFileRule;

class CreatePlaylistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasArtistProfile();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'cover_image' => ['nullable', 'image', new ImageFileRule(), 'max:2048'],
            'is_public' => 'boolean',
            'is_collaborative' => 'boolean',
            'tags' => 'nullable|array|max:10',
            'tags.*' => 'string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a playlist name.',
            'name.max' => 'Playlist name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'cover_image.max' => 'Cover image cannot exceed 2MB.',
            'tags.max' => 'Cannot add more than 10 tags.',
            'tags.*.max' => 'Each tag cannot exceed 50 characters.',
        ];
    }

    public function validated(): array
    {
        $data = parent::validated();
        
        // Sanitize string inputs
        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);
        }
        if (isset($data['description'])) {
            $data['description'] = trim($data['description']);
        }
        
        // Set default values
        $data['is_public'] = $data['is_public'] ?? false;
        $data['is_collaborative'] = $data['is_collaborative'] ?? false;
        
        // Process tags
        if (isset($data['tags'])) {
            $data['tags'] = array_unique(array_map('trim', $data['tags']));
            $data['tags'] = array_filter($data['tags'], 'strlen');
        }
        
        return $data;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if user has reached playlist limit (basic spam protection)
            $user = $this->user();
            if ($user && $user->playlists()->count() >= 50) {
                $validator->errors()->add('name', 'You have reached the maximum number of playlists allowed.');
            }
            
            // Validate collaborative settings
            if ($this->input('is_collaborative') && !$this->input('is_public')) {
                $validator->errors()->add('is_collaborative', 'Collaborative playlists must be public.');
            }
        });
    }
}