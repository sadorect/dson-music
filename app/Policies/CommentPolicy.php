<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Comment;

class CommentPolicy
{
    public function update(User $user, Comment $comment)
    {
        return $user->id === $comment->user_id;
    }

    public function delete(User $user, Comment $comment)
    {
        return $user->id === $comment->user_id || 
               $user->id === $comment->commentable->user_id ||
               $user->user_type === 'admin';
    }

    public function pin(User $user, Comment $comment)
    {
        return $user->id === $comment->commentable->user_id || 
               $user->user_type === 'admin';
    }
}
