<?php

use App\Support\UploadLimits;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->unsignedInteger('audio_upload_limit_kb')->default(UploadLimits::DEFAULT_AUDIO_KB)->after('favicon');
            $table->unsignedInteger('image_upload_limit_kb')->default(UploadLimits::DEFAULT_IMAGE_KB)->after('audio_upload_limit_kb');
            $table->unsignedInteger('site_logo_upload_limit_kb')->default(UploadLimits::DEFAULT_SITE_LOGO_KB)->after('image_upload_limit_kb');
            $table->unsignedInteger('favicon_upload_limit_kb')->default(UploadLimits::DEFAULT_FAVICON_KB)->after('site_logo_upload_limit_kb');
            $table->unsignedInteger('hero_image_upload_limit_kb')->default(UploadLimits::DEFAULT_HERO_IMAGE_KB)->after('favicon_upload_limit_kb');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'audio_upload_limit_kb',
                'image_upload_limit_kb',
                'site_logo_upload_limit_kb',
                'favicon_upload_limit_kb',
                'hero_image_upload_limit_kb',
            ]);
        });
    }
};
