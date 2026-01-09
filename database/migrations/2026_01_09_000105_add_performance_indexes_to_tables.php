<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Helper to add index safely
        $addIndexSafely = function ($table, $column, $indexName = null) {
            try {
                if ($indexName) {
                    Schema::table($table, function (Blueprint $t) use ($column, $indexName) {
                        $t->index($column, $indexName);
                    });
                } else {
                    Schema::table($table, function (Blueprint $t) use ($column) {
                        $t->index($column);
                    });
                }
            } catch (\Exception $e) {
                // Index already exists, skip
            }
        };

        // Add indexes to tracks table
        $addIndexSafely('tracks', 'status');
        $addIndexSafely('tracks', 'genre');
        $addIndexSafely('tracks', 'artist_id');
        $addIndexSafely('tracks', 'album_id');
        $addIndexSafely('tracks', ['status', 'created_at'], 'idx_tracks_status_created');
        $addIndexSafely('tracks', ['status', 'play_count'], 'idx_tracks_status_plays');
        $addIndexSafely('tracks', 'approval_status');

        // Add indexes to play_histories table
        $addIndexSafely('play_histories', 'track_id');
        $addIndexSafely('play_histories', 'user_id');
        $addIndexSafely('play_histories', 'created_at');
        $addIndexSafely('play_histories', ['track_id', 'created_at'], 'idx_play_histories_track_date');

        // Add indexes to likes table
        $addIndexSafely('likes', ['likeable_type', 'likeable_id'], 'idx_likes_likeable');
        $addIndexSafely('likes', 'user_id');

        // Add indexes to follows table
        $addIndexSafely('follows', 'user_id');
        $addIndexSafely('follows', 'artist_profile_id');

        // Add indexes to comments table
        $addIndexSafely('comments', ['commentable_type', 'commentable_id'], 'idx_comments_commentable');
        $addIndexSafely('comments', 'user_id');
        $addIndexSafely('comments', 'created_at');

        // Add indexes to downloads table
        $addIndexSafely('downloads', 'track_id');
        $addIndexSafely('downloads', 'user_id');
        $addIndexSafely('downloads', 'created_at');

        // Add indexes to playlists table
        $addIndexSafely('playlists', 'user_id');
        $addIndexSafely('playlists', 'is_public');

        // Add indexes to playlist_track pivot table
        $addIndexSafely('playlist_track', 'playlist_id');
        $addIndexSafely('playlist_track', 'track_id');
        $addIndexSafely('playlist_track', 'position');

        // Add indexes to artist_profiles table
        $addIndexSafely('artist_profiles', 'user_id');
        $addIndexSafely('artist_profiles', 'is_verified');
        $addIndexSafely('artist_profiles', 'custom_url');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from tracks table
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['genre']);
            $table->dropIndex(['artist_id']);
            $table->dropIndex(['album_id']);
            $table->dropIndex('idx_tracks_status_created');
            $table->dropIndex('idx_tracks_status_plays');
            $table->dropIndex(['approval_status']);
        });

        // Drop indexes from play_histories table
        Schema::table('play_histories', function (Blueprint $table) {
            $table->dropIndex(['track_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex('idx_play_histories_track_date');
        });

        // Drop indexes from likes table
        Schema::table('likes', function (Blueprint $table) {
            $table->dropIndex('idx_likes_likeable');
            $table->dropIndex(['user_id']);
        });

        // Drop indexes from follows table
        Schema::table('follows', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['artist_profile_id']);
        });

        // Drop indexes from comments table
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('idx_comments_commentable');
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        // Drop indexes from downloads table
        Schema::table('downloads', function (Blueprint $table) {
            $table->dropIndex(['track_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        // Drop indexes from playlists table
        Schema::table('playlists', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['is_public']);
        });

        // Drop indexes from playlist_track pivot table
        Schema::table('playlist_track', function (Blueprint $table) {
            $table->dropIndex(['playlist_id']);
            $table->dropIndex(['track_id']);
            $table->dropIndex(['position']);
        });

        // Drop indexes from artist_profiles table
        Schema::table('artist_profiles', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['is_verified']);
            $table->dropIndex(['custom_url']);
        });
    }
};
