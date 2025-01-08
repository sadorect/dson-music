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
        $table->string('title');
        $table->foreignId('artist_id')->constrained('artist_profiles')->onDelete('cascade');
        $table->foreignId('album_id')->nullable()->constrained()->onDelete('set null');
        $table->string('genre');
        $table->integer('duration');
        $table->string('file_path');
        $table->string('cover_art')->nullable();
        $table->timestamp('release_date');
        $table->boolean('is_featured')->default(false);
        $table->integer('play_count')->default(0);
        $table->enum('status', ['draft', 'published', 'private'])->default('draft');
        $table->timestamps();
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
