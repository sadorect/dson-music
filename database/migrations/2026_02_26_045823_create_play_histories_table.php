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
        Schema::create('play_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->unsignedSmallInteger('seconds_played')->default(0);
            $table->boolean('completed')->default(false);
            $table->string('source')->nullable(); // 'playlist', 'album', 'radio', 'direct'
            $table->timestamps();

            $table->index(['track_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('play_histories');
    }
};
