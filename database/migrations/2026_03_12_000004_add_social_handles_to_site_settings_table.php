<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('x_handle')->nullable()->after('site_title');
            $table->string('instagram_handle')->nullable()->after('x_handle');
            $table->string('facebook_handle')->nullable()->after('instagram_handle');
            $table->string('tiktok_handle')->nullable()->after('facebook_handle');
            $table->string('youtube_handle')->nullable()->after('tiktok_handle');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'x_handle',
                'instagram_handle',
                'facebook_handle',
                'tiktok_handle',
                'youtube_handle',
            ]);
        });
    }
};
