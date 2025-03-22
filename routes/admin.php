<?php

namespace App\Routes;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TrackController;
use App\Http\Controllers\Admin\ArtistController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TrackReviewController;
use App\Http\Controllers\Admin\ImpersonationController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Check if user is admin
    Route::middleware(['auth'], function ($request, $next) {
        if (!$request->user() || $request->user()->user_type !== 'admin') {
            return redirect()->route('home')->with('error', 'You do not have permission to access the admin area.');
        }
        return $next($request);
    })->group(function () {
        // Existing admin routes...

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('tracks', TrackController::class);
    Route::get('/tracks/{track}', [TrackController::class, 'show'])->name('tracks.show');

    Route::resource('artists', ArtistController::class);
    Route::post('artists/{artist}/verify', [ArtistController::class, 'verify'])->name('artists.verify');
Route::post('artists/{artist}/unverify', [ArtistController::class, 'unverify'])->name('artists.unverify');

Route::post('impersonate/{user}', [ImpersonationController::class, 'impersonate'])->name('impersonate');
    Route::post('stop-impersonating', [ImpersonationController::class, 'stopImpersonating'])->name('stop-impersonating');


    Route::get('reports', [ReportController::class, 'index'])->name('reports');
       // Settings routes
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/social', [SettingController::class, 'updateSocial'])->name('settings.update-social');
    Route::get('/settings/hero-slides', [SettingController::class, 'heroSlides'])->name('settings.hero-slides');
    Route::post('/settings/hero-slides', [SettingController::class, 'updateHeroSlides'])->name('settings.hero-slides.update');
// Inside your admin routes group
Route::post('/settings/update-logo', [SettingController::class, 'updateLogo'])->name('settings.update-logo');
Route::post('/settings/delete-logo', [SettingController::class, 'deleteLogo'])->name('settings.delete-logo');

    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/export', [AnalyticsController::class, 'export'])->name('export');
        Route::get('/artist/{artist}', [AnalyticsController::class, 'artist'])->name('artist');
        Route::get('/artist-comparison', [AnalyticsController::class, 'getArtistComparison'])->name('artist-comparison');
    Route::get('/geographic', [AnalyticsController::class, 'getGeographicStats'])->name('geographic');
    Route::get('/daily-report', [AnalyticsController::class, 'getDailyReport'])->name('daily-report');
   
});
    


Route::get('/tracks/review', [TrackReviewController::class, 'index'])->name('tracks.review.index');
        Route::get('/tracks/review/{track}', [TrackReviewController::class, 'show'])->name('tracks.review.show');
        Route::post('/tracks/review/{track}/approve', [TrackReviewController::class, 'approve'])->name('tracks.review.approve');
        Route::post('/tracks/review/{track}/reject', [TrackReviewController::class, 'reject'])->name('tracks.review.reject');



// Admin user management (super admin only)
Route::resource('admins', AdminUserController::class);

});

});
