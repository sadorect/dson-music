<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->string('home_editor_picks_position', 32)
                ->default('after-personalized')
                ->after('show_search_popular_artists');
            $table->string('browse_editor_picks_position', 32)
                ->default('before-personalized')
                ->after('home_editor_picks_position');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'home_editor_picks_position',
                'browse_editor_picks_position',
            ]);
        });
    }
};
