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
        Schema::create('artist_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stage_name');
            $table->string('slug')->unique();
            $table->text('bio')->nullable();
            $table->string('website')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('spotify')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_approved')->default(true); // false = pending review
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('total_plays')->default(0);
            $table->unsignedInteger('followers_count')->default(0);
            $table->decimal('total_donations', 10, 2)->default(0);
            $table->timestamps();
        });

        // Genre pivot for artist
        Schema::create('artist_genre', function (Blueprint $table) {
            $table->foreignId('artist_profile_id')->constrained('artist_profiles')->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->primary(['artist_profile_id', 'genre_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artist_genre');
        Schema::dropIfExists('artist_profiles');
    }
};
