<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrendingController;
use App\Http\Controllers\PublicTrackController;
use App\Http\Controllers\Auth\RegisteredUserController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/artist/tracks/api', [TrackController::class, 'apiIndex'])
->name('artist.tracks.api')
->middleware(['auth']);


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('register', [RegisteredUserController::class, 'store'])
        ->name('register');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/artist/register', [ArtistController::class, 'showRegistrationForm'])->name('artist.register.form');
    Route::post('/artist/register', [ArtistController::class, 'register'])->name('artist.register');
});

Route::middleware('auth')->group(function () {
    Route::get('/artist/register', [ArtistController::class, 'showRegistrationForm'])->name('artist.register.form');
    Route::post('/artist/register', [ArtistController::class, 'register'])->name('artist.register');
    Route::get('/artist/dashboard', [ArtistController::class, 'dashboard'])->name('artist.dashboard');
    Route::resource('artist/albums', AlbumController::class, ['as' => 'artist']);
     // Track routes
     Route::resource('artist/tracks', TrackController::class, ['as' => 'artist']);
});

Route::get('/artist/profile/create', [ArtistController::class, 'create'])->name('artist.profile.create');
Route::post('/artist/profile', [ArtistController::class, 'store'])->name('artist.profile.store');
Route::get('/artist/profile/edit', [ArtistController::class, 'edit'])->name('artist.profile.edit');


require __DIR__.'/auth.php';

Route::get('/tracks/public', [PublicTrackController::class, 'index'])->name('tracks.public');

Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/{query}', [SearchController::class, 'index'])->name('search.query');
Route::get('/search/quick', [SearchController::class, 'quickSearch'])->name('search.quick');

Route::get('/trending', [TrendingController::class, 'index'])->name('trending');
Route::post('/tracks/{track}/play', [TrackController::class, 'recordPlay'])->name('tracks.play');
