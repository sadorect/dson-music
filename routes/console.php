<?php

use App\Jobs\GenerateAnalyticsReport;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Generate daily analytics report at 2:00 AM
Schedule::job(new GenerateAnalyticsReport())->dailyAt('02:00');
