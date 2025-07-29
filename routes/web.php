<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\TrendingController;
use App\Http\Controllers\PublicTrackController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SongController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/artist/tracks/api', [TrackController::class, 'apiIndex'])
->name('artist.tracks.api')
->middleware(['auth']);
Route::get('/artists/{artist}', [ArtistController::class, 'show'])->name('artists.show');
Route::get('/artists', [ArtistController::class, 'index'])->name('artists.index');



Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('register', [RegisteredUserController::class, 'store'])
        ->name('register');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/artist/register', [ArtistController::class, 'showRegistrationForm'])->name('artist.register.form');
    Route::post('/artist/register', [ArtistController::class, 'register'])->name('artist.register');
});

Route::middleware('auth')->group(function () {
    Route::get('/artist/register', [ArtistController::class, 'showRegistrationForm'])->name('artist.register.form');
    Route::post('/artist/register', [ArtistController::class, 'register'])->name('artist.register');
    Route::get('/artist/dashboard', [ArtistController::class, 'dashboard'])->name('artist.dashboard');
    Route::get('/artist/profile/create', [ArtistController::class, 'create'])->name('artist.profile.create');
Route::post('/artist/profile', [ArtistController::class, 'store'])->name('artist.profile.store');
Route::get('/artist/profile/edit', [ArtistController::class, 'edit'])->name('artist.profile.edit');
Route::get('/artist/profile', [ArtistController::class, 'showPublic'])->name('artist.profile.show');
    
    Route::resource('artist/albums', AlbumController::class, ['as' => 'artist']);
     // Track routes
     Route::resource('artist/tracks', TrackController::class, ['as' => 'artist']);

     // Follow routes
    Route::post('artists/{artist}/follow', [FollowController::class, 'follow'])->name('artists.follow');
    Route::delete('artists/{artist}/unfollow', [FollowController::class, 'unfollow'])->name('artists.unfollow');

    // Like routes
    Route::post('tracks/{track}/like', [LikeController::class, 'toggleLike'])->name('tracks.like');

    // Comment routes
    Route::post('tracks/{track}/comments', [CommentController::class, 'store'])->name('tracks.comments.store');
    Route::delete('/delete/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/tracks/{track}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
   
    Route::post('/comments/{comment}/pin', [CommentController::class, 'pin'])->name('comments.pin');
    
    // Download routes
    Route::get('tracks/{track}/download', [DownloadController::class, 'download'])->name('tracks.download');

});




require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

Route::get('/tracks/public', [PublicTrackController::class, 'index'])->name('tracks.public');
Route::get('/tracks/{track}', [PublicTrackController::class, 'show'])->name('tracks.show');

Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/{query}', [SearchController::class, 'index'])->name('search.query');
Route::get('/search/quick', [SearchController::class, 'quickSearch'])->name('search.quick');

Route::get('/trending', [TrendingController::class, 'index'])->name('trending');
Route::post('/tracks/{track}/play', [TrackController::class, 'recordPlay'])->name('tracks.play');
Route::get('songs/{song}', [SongController::class, 'show'])->name('songs.show');

Route::post('/toggle-theme', [App\Http\Controllers\ThemeController::class, 'toggle'])->name('toggle-theme');