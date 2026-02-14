<?php

namespace App\Policies;

use App\Models\Playlist;
use App\Models\User;

class PlaylistPolicy
{
    /**
     * Determine if the user can view the playlist.
     */
    public function view(User $user, Playlist $playlist): bool
    {
        return $playlist->is_public || $user->id === $playlist->user_id;
    }

    /**
     * Determine if the user can create playlists.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the playlist.
     */
    public function update(User $user, Playlist $playlist): bool
    {
        return $user->id === $playlist->user_id;
    }

    /**
     * Determine if the user can delete the playlist.
     */
    public function delete(User $user, Playlist $playlist): bool
    {
        return $user->id === $playlist->user_id;
    }
}
