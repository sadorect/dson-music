<?php

namespace App\Providers;

use App\Models\ArtistProfile;
use App\Models\Comment;
use App\Models\Track;
use App\Models\User;
use App\Policies\CommentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Comment::class => CommentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // ── Admin RBAC ──────────────────────────────────────────────
        Gate::define('manage-admins', function (User $user) {
            return $user->isSuperAdmin();
        });

        Gate::define('access-admin', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-tracks', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-artists', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-analytics', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-settings', function (User $user) {
            return $user->isSuperAdmin();
        });

        // ── Artist RBAC ─────────────────────────────────────────────
        Gate::define('access-artist-panel', function (User $user) {
            return $user->isArtist() && $user->artistProfile !== null;
        });

        // Owner check: only the artist who owns the track may edit/delete
        Gate::define('manage-own-track', function (User $user, Track $track) {
            return $user->isArtist()
                && $user->artistProfile
                && $track->artist_id === $user->artistProfile->id;
        });

        Gate::define('manage-own-profile', function (User $user, ArtistProfile $profile) {
            return $user->artistProfile && $user->artistProfile->id === $profile->id;
        });

        // ── Impersonation ────────────────────────────────────────────
        Gate::define('impersonate-users', function (User $user) {
            return $user->isSuperAdmin();
        });
    }
}
