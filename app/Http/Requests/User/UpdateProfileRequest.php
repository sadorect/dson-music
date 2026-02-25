<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ImageFileRule;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'bio' => 'nullable|string|max:1000',
            'profile_image' => ['nullable', 'image', new ImageFileRule(), 'max:2048'],
            'current_password' => 'required_with:password|current_password',
            'password' => 'nullable|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'email.unique' => 'This email address is already taken.',
            'bio.max' => 'Bio cannot exceed 1000 characters.',
            'profile_image.max' => 'Profile image cannot exceed 2MB.',
            'current_password.required_with' => 'Please enter your current password to change your password.',
            'current_password.current_password' => 'Current password is incorrect.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }

    public function validated(): array
    {
        $data = parent::validated();
        
        // Sanitize string inputs
        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);
        }
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }
        if (isset($data['bio'])) {
            $data['bio'] = trim($data['bio']);
        }
        
        // Remove password fields if not changing password
        if (!isset($data['password'])) {
            unset($data['current_password'], $data['password'], $data['password_confirmation']);
        } else {
            // Only keep the new password, not the current one or confirmation
            unset($data['current_password'], $data['password_confirmation']);
        }
        
        return $data;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            
            // Check if email change is allowed (rate limiting)
            if ($this->has('email') && $this->input('email') !== $user->email) {
                $lastEmailChange = $user->email_verified_at && $user->email_verified_at->gt(now()->subDays(7));
                if ($lastEmailChange && !$user->isAdmin()) {
                    $validator->errors()->add('email', 'You can only change your email once every 7 days.');
                }
            }
            
            // Validate profile image dimensions if uploaded
            if ($this->hasFile('profile_image')) {
                $image = $this->file('profile_image');
                if ($image->getClientOriginalExtension() === 'gif') {
                    $validator->errors()->add('profile_image', 'GIF images are not allowed for profile pictures.');
                }
            }
        });
    }
}