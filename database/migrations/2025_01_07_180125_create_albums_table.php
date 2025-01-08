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
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
        $table->string('title');
        $table->foreignId('artist_id')->constrained('artist_profiles')->onDelete('cascade');
        $table->string('cover_art');
        $table->timestamp('release_date');
        $table->text('description')->nullable();
        $table->enum('type', ['album', 'EP', 'single'])->default('album');
        $table->enum('status', ['draft', 'published', 'private'])->default('draft');
        $table->integer('play_count')->default(0);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
