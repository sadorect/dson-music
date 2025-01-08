<?php

namespace App\Routes;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TrackController;
use App\Http\Controllers\Admin\ArtistController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('tracks', TrackController::class);
    Route::resource('artists', ArtistController::class);
    Route::post('artists/{artist}/verify', [ArtistController::class, 'verify'])->name('artists.verify');
Route::post('artists/{artist}/unverify', [ArtistController::class, 'unverify'])->name('artists.unverify');


    Route::get('reports', [ReportController::class, 'index'])->name('reports');
    Route::get('settings', [SettingController::class, 'index'])->name('settings');
});
