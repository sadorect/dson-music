# Agent Report: DSON Music

## Latest Changes (15 Nov 2025)

-   Removed blocking `dd()` calls in `HomeController`, `SearchController`, and `CommentController` and replaced them with proper logging and user-friendly error responses.
-   Consolidated duplicate route declarations in `routes/web.php`, ensuring unique artist/profile/comment endpoints and cleaner auth grouping.
-   `TrackController::recordPlay` now persists detailed `PlayHistory` entries (user, IP, agent, timestamp) so analytics dashboards have real data instead of just incremented counters.
-   Navigation search (desktop + mobile) now consumes the `/search/quick` JSON endpoint with Alpine-driven suggestions, matching the new contract and preventing 500s from the Blade view response.

## Overview

-   **Mission**: Laravel 11 + Vite/Tailwind stack delivering a streaming & artist-management hub with uploading, moderation, analytics, and a Howler.js-powered player.
-   **Data & Storage**: Tracks and metadata live in MySQL via Eloquent (`Track`, `ArtistProfile`, `Playlist`, `PlayHistory`, `Download`, `Follow`, etc.), while audio/cover assets are pushed to S3 (`Storage::disk('s3')`). Settings leverage `spatie/laravel-settings` (`App\Settings\GeneralSettings`).
-   **Client Surface**: Public home/track/search views (`resources/views/home.blade.php`, `tracks/show.blade.php`, `search/index.blade.php`) plus dedicated artist and admin dashboards with Alpine components (`resources/views/layouts/app.blade.php`, `layouts/artist.blade.php`, `layouts/admin.blade.php`).
-   **Playback UX**: Alpine + Howler queues (`resources/js/components/player.js`, `resources/views/components/player.blade.php`) pull JSON feeds from `PublicTrackController::index()` and emit `track:play` events consumed globally.

## Implemented Capabilities

1. **Artist lifecycle**

    - Registration + profile creation (`ArtistController`, `ArtistProfile` model) with verification workflow and admin approval toggles (`Admin\ArtistController@verify`).
    - Track/album CRUD with S3 upload, metadata extraction via `getID3`, and donation-ready attributes (`TrackController`, `AlbumController`).
    - Activity logging via `App\Services\ActivityLogger` to `Activity` model.

2. **Moderation & Admin tooling**

    - Track approval/review (`Admin\TrackApprovalController`, `Admin\TrackReviewController`) with mail/database notifications (`TrackApproved`, `TrackRejected`, `NewTrackPendingApproval`).
    - Admin dashboard + analytics (counts, charts, artist detail reports) using `PlayHistory` aggregations and Chart.js (`Admin\DashboardController`, `Admin\AnalyticsController`, `resources/views/admin/analytics/index.blade.php`).
    - Configurable site settings, hero slides, and branding managed through Spatie settings + S3 uploads (`Admin\SettingController`, `resources/views/admin/settings/*.blade.php`).

3. **Social & engagement features**

    - Likes, comments (with pin/edit/delete), follows, and downloads wired to their models and controllers.
    - Trending and genre sections on home page fed by `Track::trending()` and aggregated queries in `HomeController`.

4. **Playback & discovery**
    - Public `tracks/public` API returns artist, artwork, and audio URLs for the Alpine grid (`resources/views/components/tracks-grid.blade.php`).
    - Player component exposes queue, shuffle, repeat, and seeks while reporting plays back via `TrackController::recordPlay`.

## Gaps & Risks

| Area                  | Issue                                                                                                                                                                               | Impact                                       |
| --------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------- |
| Download metrics      | `DownloadController` increments `downloads_count` on `Track`, but the column isn’t defined on the model fillables/migrations; metrics likely never persist.                         | Medium.                                      |
| Comments & moderation | No throttling, rate limiting, or abuse protections around comment create/update endpoints; moderation queue remains manual-only.                                                    | Medium.                                      |
| Playlist ecosystem    | `Playlist` model exists but no controllers, policies, or UI ("Your Library" sidebar is static).                                                                                     | Medium – core streaming expectation missing. |
| Controller stubs      | `MusicController` and `SongController` are placeholders; `PublicTrackController::show` only loads Blade view without shareable metadata; `ThemeController` toggle isn’t consumed.   | Low/Medium depending on scope.               |
| Admin/Report views    | `Admin\ReportController@index` references `admin.reports.index` view that doesn't exist. Also admin middleware nesting in `routes/admin.php` is non-standard, may skip guard logic. | Medium.                                      |
| Front-end polish      | `components/player.blade.php` duplicates `x-data`, queue UI absent, mobile responsiveness limited. Navigation references `Auth::user()->artist` (non-existent relation).            | Low/Medium.                                  |
| Testing & CI          | No feature/unit tests around upload, approvals, or playback; PHPUnit scaffolding only.                                                                                              | Medium – regressions likely.                 |

## Enhancement Recommendations

### Immediate Fixes (Stability)

1. **Remove debug `dd()` calls** and replace with logging + graceful error messages across `HomeController`, `SearchController`, `CommentController`, etc.
2. **Normalize routing**: deduplicate artist/comment routes, ensure named routes map uniquely, and move admin guard logic into proper middleware classes.
3. **Persist analytics data**: update `TrackController::recordPlay` (and front-end) to create `PlayHistory` rows with user/IP/device info; ensure downloads store counts either on `tracks` table or via aggregate queries.
4. **Search API contract**: expose a dedicated JSON endpoint (reuse `quickSearch` minus `dd()`) and update `resources/js/search.js` / navigation search to hit it.
5. **Fix comment deletion**: remove dumps, wrap deletes in policy-backed service, and add cascading soft-delete handling.

### Near-Term Feature Parity

1. **Playlist management**: controller + UI for create/add/remove, tie into player queue. Extend `Your Library` panel to show saved playlists.
2. **Artist monetization/donations**: enforce `download_type` & `minimum_donation` before S3 downloads (e.g., Stripe/Paystack integration).
3. **Content moderation tooling**: add bulk actions, filters, and audit logs in admin track review; integrate waveform/FFmpeg linting for uploads.
4. **Search & discovery**: implement multi-entity search service (Scout/Algolia/Meilisearch) for faster queries, add genre/keyword filters, trending keywords.
5. **Notifications center**: surface approval/rejection events in-app (database notifications UI) for artists, not just email.

### Long-Term / Scale

1. **Streaming optimization**: adopt chunked streaming with signed URLs or CloudFront; pre-generate multiple bitrates and store metadata beyond duration (loudness, waveform) via queued jobs.
2. **Real-time analytics**: move play/download writes to queue/jobs, feed aggregated metrics to Redis for dashboards.
3. **Mobile-ready API**: expose authenticated REST/GraphQL endpoints for mobile clients; include OAuth/token hardening.
4. **Quality gates**: implement PHPUnit/Pest suites for uploads, approvals, and API JSON responses; configure CI (GitHub Actions) to run phpunit, Pint, Pint/Tailwind builds, and npm tests.
5. **Access control hardening**: add policies for tracks/albums/comments, rate limiting for likes/follows/downloads, and impersonation audit logs.

## Suggested Next Steps

1. **Stabilize core flows** (remove `dd()`, fix routes, ensure analytics writes) before any new feature work.
2. **Formalize playlists + library UI** to meet baseline streaming expectations.
3. **Instrument observability**: add logging for uploads/plays, set up Telescope/Sentry/Logtail for production insight.
4. **Design product roadmap** focusing on: monetization, personalized recommendations, collaborative playlists, and offline/mobile support.
5. **Draft contributor guide** (setup, env requirements, S3 config, npm scripts) to onboard future engineers faster.

## Reference Hotspots

-   Controllers: `app/Http/Controllers/TrackController.php`, `PublicTrackController.php`, `Admin/*`
-   Models: `app/Models/Track.php`, `ArtistProfile.php`, `Playlist.php`, `PlayHistory.php`
-   Frontend: `resources/views/home.blade.php`, `components/player.blade.php`, `resources/js/components/player.js`
-   Settings & Services: `app/Settings/GeneralSettings.php`, `app/Services/Analytics/AnalyticsService.php`
