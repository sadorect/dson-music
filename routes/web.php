<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicTrackController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\TrendingController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/artist/tracks/api', [TrackController::class, 'apiIndex'])
    ->name('artist.tracks.api')
    ->middleware(['auth']);
Route::get('/artists/public/{slug}', [ArtistController::class, 'showPublicBySlug'])
    ->name('artists.showPublic');
Route::get('/artists/{artist}', [ArtistController::class, 'showPublicProfile'])->name('artists.show');
Route::get('/artists', [ArtistController::class, 'index'])->name('artists.index');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Two-Factor Authentication routes
Route::middleware('auth')->prefix('two-factor')->name('2fa.')->group(function () {
    Route::get('/challenge',       [TwoFactorController::class, 'showChallenge'])->name('challenge');
    Route::post('/verify',         [TwoFactorController::class, 'verify'])->name('verify');
    Route::get('/setup',           [TwoFactorController::class, 'showSetup'])->name('setup');
    Route::post('/enable',         [TwoFactorController::class, 'enable'])->name('enable');
    Route::post('/disable',        [TwoFactorController::class, 'disable'])->name('disable');
    Route::get('/recovery-codes',  [TwoFactorController::class, 'showRecoveryCodes'])->name('recovery-codes');
    Route::post('/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('regenerate-codes');
});

Route::post('register', [RegisteredUserController::class, 'store'])
    ->name('register');

Route::middleware('auth')->group(function () {
    // User profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/overview', [ProfileController::class, 'show'])->name('profile.show');

    // Artist onboarding and dashboard
    Route::get('/artist/register', [ArtistController::class, 'showRegistrationForm'])->name('artist.register.form');
    Route::post('/artist/register', [ArtistController::class, 'register'])->name('artist.register');
    Route::get('/artist/dashboard', [ArtistController::class, 'dashboard'])->name('artist.dashboard');
    Route::get('/artist/profile/create', [ArtistController::class, 'create'])->name('artist.profile.create');
    Route::post('/artist/profile', [ArtistController::class, 'store'])->name('artist.profile.store');
    Route::get('/artist/profile/edit', [ArtistController::class, 'edit'])->name('artist.profile.edit');
    Route::get('/artist/profile', [ArtistController::class, 'show'])->name('artist.profile.show');

    Route::resource('artist/albums', AlbumController::class, ['as' => 'artist']);
    Route::resource('artist/tracks', TrackController::class, ['as' => 'artist'])->except(['store']);
    Route::post('artist/tracks', [TrackController::class, 'store'])
        ->middleware('throttle:uploads')
        ->name('artist.tracks.store');

    // Social interactions
    Route::post('artists/{artist}/follow', [FollowController::class, 'follow'])->name('artists.follow');
    Route::delete('artists/{artist}/unfollow', [FollowController::class, 'unfollow'])->name('artists.unfollow');
    Route::post('tracks/{track}/like', [LikeController::class, 'toggleLike'])->name('tracks.like');

    // Comment routes
    Route::post('tracks/{track}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:comment-actions')
        ->name('tracks.comments.store');
    Route::delete('/delete/comments/{comment}', [CommentController::class, 'destroy'])
        ->middleware('throttle:comment-actions')
        ->name('comments.destroy');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])
        ->middleware('throttle:comment-actions')
        ->name('comments.update');
    Route::post('/comments/{comment}/pin', [CommentController::class, 'pin'])
        ->middleware('throttle:comment-actions')
        ->name('comments.pin');

    // Downloads
    Route::get('tracks/{track}/download', [DownloadController::class, 'download'])
        ->middleware('throttle:downloads')
        ->name('tracks.download');

    // Playlists
    Route::get('/my-playlists', [PlaylistController::class, 'myPlaylists'])->name('playlists.my-playlists');
    Route::resource('playlists', PlaylistController::class);
    Route::post('/playlists/{playlist}/tracks', [PlaylistController::class, 'addTrack'])->name('playlists.add-track');
    Route::delete('/playlists/{playlist}/tracks/{track}', [PlaylistController::class, 'removeTrack'])->name('playlists.remove-track');
    Route::post('/playlists/{playlist}/reorder', [PlaylistController::class, 'reorderTracks'])->name('playlists.reorder');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

// Public Playlist Routes (outside auth middleware)
Route::get('/playlists', [PlaylistController::class, 'index'])->name('playlists.index');
Route::get('/playlists/{playlist}', [PlaylistController::class, 'show'])->name('playlists.show');

Route::get('/tracks/public', [PublicTrackController::class, 'index'])->name('tracks.public');
Route::get('/tracks/{track}/stream', [PublicTrackController::class, 'stream'])->name('tracks.stream');
Route::get('/tracks/{track}', [PublicTrackController::class, 'show'])->name('tracks.show');

Route::get('/search', [SearchController::class, 'index'])
    ->middleware('throttle:search')
    ->name('search');
Route::get('/search/quick', [SearchController::class, 'quickSearch'])
    ->middleware('throttle:search')
    ->name('search.quick');

Route::get('/trending', [TrendingController::class, 'index'])->name('trending');
Route::post('/tracks/{track}/play', [TrackController::class, 'recordPlay'])->name('tracks.play');
Route::post('/toggle-theme', [App\Http\Controllers\ThemeController::class, 'toggle'])->name('toggle-theme');
