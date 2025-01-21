<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Track;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\ArtistProfile;

class CommentController extends Controller
{
    public function store(Request $request)
{
    $validated = $request->validate([
        'content' => 'required|string|max:1000',
        'parent_id' => 'nullable|exists:comments,id',
        'commentable_type' => 'required|string',
        'commentable_id' => 'required|integer'
    ]);

    $commentable = $this->getCommentable($validated['commentable_type'], $validated['commentable_id']);

    $comment = $commentable->comments()->create([
        'user_id' => auth()->id(),
        'content' => $validated['content'],
        'parent_id' => $validated['parent_id'] ?? null
    ]);

    return response()->json([
        'comment' => $comment->load('user'),
        'message' => 'Comment posted successfully'
    ]);
}


    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment->update($validated);

        return response()->json([
            'comment' => $comment->fresh(),
            'message' => 'Comment updated successfully'
        ]);
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        
        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully'
        ]);
    }

    public function pin(Comment $comment)
    {
        $this->authorize('pin', $comment);
        
        $comment->update(['is_pinned' => !$comment->is_pinned]);

        return response()->json([
            'is_pinned' => $comment->is_pinned,
            'message' => $comment->is_pinned ? 'Comment pinned successfully' : 'Comment unpinned successfully'
        ]);
    }

    private function getCommentable(string $type, int $id)
    {
        return match ($type) {
            'track' => Track::findOrFail($id),
            'album' => Album::findOrFail($id),
            'artist' => ArtistProfile::findOrFail($id),
            default => abort(404)
        };
    }
}
