<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    protected function isCommentableOwner(User $user, Comment $comment): bool
    {
        $commentable = $comment->commentable;
        if (! $commentable) {
            return false;
        }

        if ($commentable instanceof \App\Models\Track) {
            return (int) $commentable->artist?->user_id === (int) $user->id;
        }

        if ($commentable instanceof \App\Models\Album) {
            return (int) $commentable->artist?->user_id === (int) $user->id;
        }

        if ($commentable instanceof \App\Models\ArtistProfile) {
            return (int) $commentable->user_id === (int) $user->id;
        }

        return false;
    }

    public function update(User $user, Comment $comment)
    {
        return $user->id === $comment->user_id;
    }

    public function delete(User $user, Comment $comment)
    {
        return $user->id === $comment->user_id ||
               $this->isCommentableOwner($user, $comment) ||
               $user->user_type === 'admin';
    }

    public function pin(User $user, Comment $comment)
    {
        return $this->isCommentableOwner($user, $comment) ||
               $user->user_type === 'admin';
    }
}
