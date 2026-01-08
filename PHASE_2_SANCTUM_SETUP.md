# Phase 2 Task 2.4: Laravel Sanctum Installation

## Installation Steps

Run these commands in order:

```bash
# Install Laravel Sanctum
composer require laravel/sanctum

# Publish Sanctum configuration and migrations
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Run migrations to create personal_access_tokens table
php artisan migrate
```

## Configuration Complete

The following files have been created as part of this task:
- `routes/api.php` - API routes with authentication
- `app/Http/Controllers/Api/AuthController.php` - API authentication
- `app/Http/Controllers/Api/TrackController.php` - API track endpoints

## Usage

### API Authentication

**Login:**
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "token": "1|abcdef123456...",
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com"
  }
}
```

**Using the token:**
```http
GET /api/tracks
Authorization: Bearer 1|abcdef123456...
```

### API Endpoints

- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login and get token
- `POST /api/auth/logout` - Logout (revoke token)
- `GET /api/user` - Get authenticated user
- `GET /api/tracks` - List all tracks
- `GET /api/tracks/{track}` - Get single track
- `POST /api/tracks` - Create track (authenticated)
- `PUT /api/tracks/{track}` - Update track (authenticated, owner only)
- `DELETE /api/tracks/{track}` - Delete track (authenticated, owner only)

## Security Features

- Token-based authentication (Sanctum)
- Rate limiting applied to all API endpoints
- CORS restrictions in place
- Token abilities/scopes support
