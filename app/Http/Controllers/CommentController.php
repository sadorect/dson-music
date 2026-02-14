<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\ArtistProfile;
use App\Models\Comment;
use App\Models\Track;
use App\Rules\SpamFree;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000', new SpamFree],
            'parent_id' => 'nullable|exists:comments,id',
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
        ]);

        $commentable = $this->getCommentable($validated['commentable_type'], $validated['commentable_id']);

        $comment = $commentable->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'comment' => $comment->load('user'),
                'message' => 'Comment posted successfully',
            ]);
        }

        return back()->with('success', 'Comment posted successfully');
    }

    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000', new SpamFree],
        ]);

        $comment->update($validated);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'comment' => $comment->fresh(),
                'message' => 'Comment updated successfully',
            ]);
        }

        return back()->with('success', 'Comment updated successfully');
    }

    public function destroy(Request $request, Comment $comment)
    {
        try {
            $this->authorize('delete', $comment);

            DB::beginTransaction();

            // Soft delete any replies first
            $comment->replies()->delete();

            // Soft delete the comment
            $comment->delete();

            DB::commit();

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comment deleted successfully',
                ]);
            }

            return back()->with('success', 'Comment deleted successfully');

        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete comment', [
                'comment_id' => $comment->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to delete comment at this time.',
                ], 500);
            }

            return back()->with('error', 'Unable to delete comment at this time.');
        }
    }

    public function pin(Request $request, Comment $comment)
    {
        $this->authorize('pin', $comment);

        $comment->update(['is_pinned' => ! $comment->is_pinned]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'is_pinned' => $comment->is_pinned,
                'message' => $comment->is_pinned ? 'Comment pinned successfully' : 'Comment unpinned successfully',
            ]);
        }

        return back()->with('success', $comment->is_pinned ? 'Comment pinned successfully' : 'Comment unpinned successfully');
    }

    private function getCommentable(string $type, int $id)
    {
        return match ($type) {
            'track', Track::class => Track::findOrFail($id),
            'album', Album::class => Album::findOrFail($id),
            'artist', ArtistProfile::class => ArtistProfile::findOrFail($id),
            default => abort(404)
        };
    }
}
