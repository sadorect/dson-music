<?php

namespace App\Routes;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TrackController;
use App\Http\Controllers\Admin\ArtistController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImpersonationController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('tracks', TrackController::class);
    Route::resource('artists', ArtistController::class);
    Route::post('artists/{artist}/verify', [ArtistController::class, 'verify'])->name('artists.verify');
Route::post('artists/{artist}/unverify', [ArtistController::class, 'unverify'])->name('artists.unverify');

Route::post('impersonate/{user}', [ImpersonationController::class, 'impersonate'])->name('impersonate');
    Route::post('stop-impersonating', [ImpersonationController::class, 'stopImpersonating'])->name('stop-impersonating');


    Route::get('reports', [ReportController::class, 'index'])->name('reports');
    Route::get('settings', [SettingController::class, 'index'])->name('settings');

    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/export', [AnalyticsController::class, 'export'])->name('export');
        Route::get('/artist/{artist}', [AnalyticsController::class, 'artist'])->name('artist');
        Route::get('/artist-comparison', [AnalyticsController::class, 'getArtistComparison'])->name('artist-comparison');
    Route::get('/geographic', [AnalyticsController::class, 'getGeographicStats'])->name('geographic');
    Route::get('/daily-report', [AnalyticsController::class, 'getDailyReport'])->name('daily-report');
    

    
    
});
    





});
