<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SpamFreeRule;

class CreateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && !$this->user()->isBlocked();
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:1000', new SpamFreeRule()],
            'parent_id' => 'nullable|exists:comments,id',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Please enter a comment.',
            'content.min' => 'Comment cannot be empty.',
            'content.max' => 'Comment cannot exceed 1000 characters.',
            'parent_id.exists' => 'Parent comment does not exist.',
        ];
    }

    public function validated(): array
    {
        $data = parent::validated();
        
        // Sanitize content
        if (isset($data['content'])) {
            $data['content'] = trim($data['content']);
        }
        
        return $data;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            
            // Rate limiting: max 5 comments per minute
            if ($user) {
                $recentComments = $user->comments()
                    ->where('created_at', '>', now()->subMinute())
                    ->count();
                    
                if ($recentComments >= 5) {
                    $validator->errors()->add('content', 'Please wait before posting another comment.');
                }
            }
            
            // Check if replying to own comment or comment on own track (prevent spam)
            if ($this->input('parent_id')) {
                $parentComment = \App\Models\Comment::find($this->input('parent_id'));
                if ($parentComment && $parentComment->user_id === $user->id) {
                    $validator->errors()->add('parent_id', 'You cannot reply to your own comment.');
                }
            }
        });
    }
}