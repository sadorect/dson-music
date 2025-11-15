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
        Schema::table('artist_profiles', function (Blueprint $table) {
            $table->json('social_links')->nullable()->after('website');
            $table->string('custom_url')->nullable()->unique()->after('social_links');
            $table->unsignedTinyInteger('completion_percentage')->default(0)->after('custom_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artist_profiles', function (Blueprint $table) {
            $table->dropColumn(['social_links', 'custom_url', 'completion_percentage']);
        });
    }
};
