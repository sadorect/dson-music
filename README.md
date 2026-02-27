# GrinMuzik

A music streaming and artist monetisation platform built with Laravel 12, Livewire 3 / Volt, Alpine.js, and Tailwind CSS.

Artists upload tracks at **zero commission**. Listeners stream for free and support artists directly via donations powered by Stripe.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Frontend | Livewire 3 (Volt), Alpine.js, Tailwind CSS 3 |
| Admin panel | Filament 3 |
| Auth | Laravel Breeze (custom glass-morphism theme) |
| Roles & Permissions | Spatie Laravel Permission |
| Media storage | Spatie Laravel MediaLibrary (local / AWS S3) |
| Payments | Stripe (donations) |
| Build tool | Vite |
| Database | MySQL |

---

## Features

### Listeners
- Browse, search, and stream tracks via a persistent mini player (survives SPA navigation)
- Play queue with slide-up tray — add tracks individually or play/queue entire playlists
- Like tracks and view play history on a personal dashboard
- Create public or private playlists, share them via a public URL
- Donate directly to artists via Stripe Checkout

### Artists
- Artist profile with avatar and banner (media library)
- Upload audio tracks with cover art — publish/unpublish at will
- Zero-commission model — all donation revenue goes to the artist

### General
- Charts page (top 50 by play count)
- New Releases page
- Public playlist pages (`/playlist/{slug}`)
- Math captcha on all auth forms (no third-party captcha service required)
- Contact form with SMTP email delivery and reply-to set to sender
- Footer pages: About, Artist Guide, Pricing, Privacy Policy, Terms of Service
- Custom 404 page

### Admin
- Filament 3 admin panel at `/admin`
- Super-admin seeder included

---

## Local Development Setup

### Requirements
- PHP 8.2+
- Composer
- Node.js 18+ & npm
- MySQL 8+

### Steps

```bash
# 1. Clone
git clone git@github.com:sadorect/dson-music.git grinmuzik
cd grinmuzik

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Environment
cp .env.example .env
php artisan key:generate

# 5. Configure .env (see section below), then:
php artisan migrate --seed

# 6. Create storage symlink
php artisan storage:link

# 7. Build assets
npm run build

# 8. Start the dev server
php artisan serve
```

---

## Environment Variables

Key variables to configure in `.env`:

```ini
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_DATABASE=grinmuzik
DB_USERNAME=root
DB_PASSWORD=

# Storage: "local" or "s3"
FILESYSTEM_DISK=local
# MEDIA_DISK=s3       # uncomment when using S3

# S3 (optional)
# AWS_ACCESS_KEY_ID=
# AWS_SECRET_ACCESS_KEY=
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=
# AWS_URL=

# Mail (SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=yourpassword
MAIL_FROM_ADDRESS=hello@grinmuzik.com

# Stripe
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

---

## Seeding

```bash
# Creates a super-admin user (edit the seeder for credentials)
php artisan db:seed --class=SuperAdminSeeder
```

---

## Production Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Set real `APP_URL`
- [ ] Set real Stripe live keys
- [ ] Configure SMTP mail credentials
- [ ] Switch `FILESYSTEM_DISK=s3` and fill AWS vars (`league/flysystem-aws-s3-v3` already installed)
- [ ] Run `php artisan optimize`
- [ ] Run `npm run build`
- [ ] Set up queue worker (`php artisan queue:work`) if using queued mail/jobs

---

## Roles

| Role | Access |
|---|---|
| `super_admin` | Full Filament admin panel |
| `artist` | Artist dashboard — upload & manage tracks, view earnings |
| `listener` | Listener dashboard — liked tracks, history, playlists |

---

## License

Proprietary — all rights reserved.
