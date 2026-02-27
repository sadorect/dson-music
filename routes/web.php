<?php

use App\Http\Controllers\DonationController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'pages.home')->name('home');

// ── Public browse & search ────────────────────────────────────────────────
Volt::route('/browse', 'pages.browse')->name('browse');
Volt::route('/search', 'pages.search')->name('search');
Volt::route('/charts', 'pages.charts')->name('charts');
Volt::route('/new-releases', 'pages.new-releases')->name('new-releases');
Volt::route('/track/{track:slug}', 'pages.track-show')->name('track.show');
Volt::route('/playlist/{playlist:slug}', 'pages.playlist-show')->name('playlist.show');

// ── Static / info pages ───────────────────────────────────────────────────
Volt::route('/about', 'pages.about')->name('about');
Volt::route('/artist-guide', 'pages.artist-guide')->name('artist-guide');
Volt::route('/pricing', 'pages.pricing')->name('pricing');
Volt::route('/privacy', 'pages.privacy')->name('privacy');
Volt::route('/terms', 'pages.terms')->name('terms');
Volt::route('/contact', 'pages.contact')->name('contact');

// ── Donation / unlock ──────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/stripe/intent/{track}', [DonationController::class, 'createIntent'])->name('donation.intent');
});
Route::post('/stripe/webhook', [DonationController::class, 'webhook'])->name('donation.webhook');

// Shared authenticated dashboard — redirects by role
Route::get('dashboard', function () {
    $user = auth()->user();
    if ($user->isArtist()) {
        return redirect()->route('artist.dashboard');
    }
    return redirect()->route('listener.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::view('profile', 'profile')->middleware(['auth'])->name('profile');

// ── Artist area ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('artist')->name('artist.')->group(function () {
    Volt::route('/setup', 'pages.artist.setup')->name('setup');
    Volt::route('/', 'pages.artist.dashboard')->name('dashboard');
    Volt::route('/tracks', 'pages.artist.tracks')->name('tracks');
    Volt::route('/tracks/upload', 'pages.artist.upload-track')->name('upload-track');
    Volt::route('/tracks/{track}/edit', 'pages.artist.edit-track')->name('edit-track');
    Volt::route('/albums', 'pages.artist.albums')->name('albums');
    Volt::route('/albums/create', 'pages.artist.create-album')->name('create-album');
});

// ── Public artist profile — registered AFTER the /artist/* group so static paths win
Volt::route('/artist/{profile:slug}', 'pages.artist-page')->name('artist.page');

// ── Listener area ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('me')->name('listener.')->group(function () {
    Volt::route('/', 'pages.listener.dashboard')->name('dashboard');
    Volt::route('/playlists', 'pages.listener.playlists')->name('playlists');
    Volt::route('/liked', 'pages.listener.liked')->name('liked');
    Volt::route('/history', 'pages.listener.history')->name('history');
});

Route::post('logout', function () {
    auth()->logout();
    return redirect()->route('home');
})->name('logout');
// Admin panel is served by Filament at /musicdirector
require __DIR__.'/auth.php';
