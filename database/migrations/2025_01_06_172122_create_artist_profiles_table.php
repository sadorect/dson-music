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
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('artist_name');
        $table->text('bio')->nullable();
        $table->string('profile_image')->nullable();
        $table->string('cover_image')->nullable();
        $table->string('genre');
        $table->string('location')->nullable();
        $table->string('website')->nullable();
        $table->boolean('is_verified')->default(false);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artist_profiles');
    }
};
