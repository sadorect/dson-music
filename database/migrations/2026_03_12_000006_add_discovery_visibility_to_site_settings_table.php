<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->boolean('show_home_personalized')->default(true)->after('favicon');
            $table->boolean('show_home_editor_picks')->default(true)->after('show_home_personalized');
            $table->boolean('show_browse_mood_filters')->default(true)->after('show_home_editor_picks');
            $table->boolean('show_browse_editor_picks')->default(true)->after('show_browse_mood_filters');
            $table->boolean('show_browse_personalized')->default(true)->after('show_browse_editor_picks');
            $table->boolean('show_browse_fresh_this_week')->default(true)->after('show_browse_personalized');
            $table->boolean('show_browse_artists_to_watch')->default(true)->after('show_browse_fresh_this_week');
            $table->boolean('show_browse_support_direct')->default(true)->after('show_browse_artists_to_watch');
            $table->boolean('show_search_trending_tracks')->default(true)->after('show_browse_support_direct');
            $table->boolean('show_search_popular_artists')->default(true)->after('show_search_trending_tracks');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'show_home_personalized',
                'show_home_editor_picks',
                'show_browse_mood_filters',
                'show_browse_editor_picks',
                'show_browse_personalized',
                'show_browse_fresh_this_week',
                'show_browse_artists_to_watch',
                'show_browse_support_direct',
                'show_search_trending_tracks',
                'show_search_popular_artists',
            ]);
        });
    }
};
