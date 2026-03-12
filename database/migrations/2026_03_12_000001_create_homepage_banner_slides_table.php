<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_banner_slides', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('badge_text')->nullable();
            $table->string('heading')->nullable();
            $table->text('body')->nullable();
            $table->string('primary_button_label')->nullable();
            $table->string('primary_button_url')->nullable();
            $table->string('secondary_button_label')->nullable();
            $table->string('secondary_button_url')->nullable();
            $table->string('background_image')->nullable();
            $table->string('background_image_alt')->nullable();
            $table->boolean('show_overlay_content')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_banner_slides');
    }
};
