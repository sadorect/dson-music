<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name');
            $table->boolean('maintenance_mode')->default(false);
            $table->integer('max_upload_size')->default(10);
            $table->json('hero_slides')->nullable();
            $table->text('site_description')->nullable();
            $table->string('contact_email')->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('enable_registration')->default(true);
            $table->text('footer_text')->nullable();
            $table->string('logo_desktop_path')->nullable();
            $table->string('logo_desktop_url')->nullable();
            $table->string('logo_mobile_path')->nullable();
            $table->string('logo_mobile_url')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('favicon_url')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('general_settings');
    }
};
