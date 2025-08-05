<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->string('gradient_start_color')->nullable();
            $table->string('gradient_end_color')->nullable();
        });

        Schema::table('artist_profiles', function (Blueprint $table) {
            $table->string('gradient_start_color')->nullable();
            $table->string('gradient_end_color')->nullable();
        });
    }

    public function down()
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn(['gradient_start_color', 'gradient_end_color']);
        });

        Schema::table('artist_profiles', function (Blueprint $table) {
            $table->dropColumn(['gradient_start_color', 'gradient_end_color']);
        });
    }
};
