<?php

namespace App\Traits;

use App\Models\Comment;

trait HasComments
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function pinnedComments()
    {
        return $this->comments()->where('is_pinned', true);
    }

    public function addComment($content, $userId, $parentId = null)
    {
        return $this->comments()->create([
            'content' => $content,
            'user_id' => $userId,
            'parent_id' => $parentId,
        ]);
    }
}
