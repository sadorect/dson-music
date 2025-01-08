<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('artist_profiles', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('artist_profiles', function (Blueprint $table) {
            $table->dropColumn('verified_at');
        });
    }
};
