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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artist_profile_id')->constrained('artist_profiles')->cascadeOnDelete();
            $table->foreignId('album_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('genre_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('lyrics')->nullable(); // plain text or .lrc
            $table->string('mood')->nullable(); // happy, sad, energetic, chill, etc.
            $table->unsignedSmallInteger('duration')->default(0); // seconds
            $table->unsignedInteger('track_number')->nullable();
            $table->boolean('is_free')->default(true); // false = 30s preview + donation
            $table->decimal('donation_amount', 8, 2)->default(1.00); // min donation to unlock
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('play_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('downloads_count')->default(0);
            $table->string('audio_file_path')->nullable(); // relative storage path
            $table->string('preview_file_path')->nullable(); // 30-second preview
            $table->string('waveform_data')->nullable(); // JSON waveform peaks
            $table->string('credits')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['artist_profile_id', 'is_published']);
            $table->index(['genre_id', 'is_published']);
            $table->index('play_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
