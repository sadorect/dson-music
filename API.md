# DSON Music API Documentation

Base URL: `https://your-domain.com/api`

## Authentication

The API uses Laravel Sanctum for authentication. Most endpoints require an API token.

### Register

Create a new user account.

**Endpoint:** `POST /api/auth/register`

**Request Body:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePassword123",
    "password_confirmation": "SecurePassword123",
    "user_type": "listener"
}
```

**Response (201 Created):**

```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "user_type": "listener",
        "created_at": "2024-01-01T00:00:00.000000Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz123456789"
}
```

### Login

Authenticate and receive an API token.

**Endpoint:** `POST /api/auth/login`

**Request Body:**

```json
{
    "email": "john@example.com",
    "password": "SecurePassword123"
}
```

**Response (200 OK):**

```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "2|abcdefghijklmnopqrstuvwxyz123456789"
}
```

### Logout

Revoke current API token.

**Endpoint:** `POST /api/auth/logout`

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200 OK):**

```json
{
    "message": "Logged out successfully"
}
```

### Get Current User

Get authenticated user information.

**Endpoint:** `GET /api/user`

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200 OK):**

```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "user_type": "artist",
    "created_at": "2024-01-01T00:00:00.000000Z"
}
```

## Tracks

### List Tracks

Get a paginated list of all approved tracks.

**Endpoint:** `GET /api/tracks`

**Query Parameters:**

- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 15, max: 100)
- `genre` (string, optional): Filter by genre
- `search` (string, optional): Search by title or artist name

**Example:** `GET /api/tracks?page=1&per_page=20&genre=Hip-Hop`

**Response (200 OK):**

```json
{
    "data": [
        {
            "id": 1,
            "title": "Amazing Track",
            "artist": {
                "id": 5,
                "name": "Artist Name"
            },
            "album": {
                "id": 2,
                "title": "Album Title"
            },
            "genre": "Hip-Hop",
            "duration": 235,
            "play_count": 1234,
            "likes_count": 56,
            "download_type": "free",
            "cover_url": "https://example.com/covers/track.jpg",
            "audio_url": "https://example.com/audio/track.mp3",
            "created_at": "2024-01-01T00:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 100,
        "last_page": 5
    }
}
```

### Get Track

Get details of a specific track.

**Endpoint:** `GET /api/tracks/{id}`

**Response (200 OK):**

```json
{
    "id": 1,
    "title": "Amazing Track",
    "description": "Track description here",
    "artist": {
        "id": 5,
        "name": "Artist Name",
        "verified": true
    },
    "album": {
        "id": 2,
        "title": "Album Title",
        "release_date": "2024-01-01"
    },
    "genre": "Hip-Hop",
    "duration": 235,
    "play_count": 1234,
    "downloads_count": 78,
    "likes_count": 56,
    "comments_count": 12,
    "download_type": "donate",
    "minimum_donation": 5.0,
    "cover_url": "https://example.com/covers/track.jpg",
    "audio_url": "https://example.com/audio/track.mp3",
    "release_date": "2024-01-01",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

### Create Track (Protected)

Create a new track record. Requires authentication and artist profile.

Note: the current API implementation expects metadata + stored file paths (JSON). Multipart file upload for API is not yet implemented in this endpoint.

**Endpoint:** `POST /api/tracks`

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body (application/json):**

```json
{
    "title": "My New Track",
    "album_id": 2,
    "genre": "hip-hop",
    "duration": 235,
    "file_path": "grinmuzik/tracks/my-new-track.mp3",
    "cover_image": "grinmuzik/covers/my-new-track.jpg"
}
```

**Response (201 Created):**

```json
{
    "id": 123,
    "title": "My New Track",
    "artist_id": 5,
    "approval_status": "pending",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

### Update Track (Protected)

Update an existing track. Only the track owner can update.

**Endpoint:** `PUT /api/tracks/{id}`

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "title": "Updated Track Title",
    "description": "Updated description",
    "genre": "R&B",
    "status": "published"
}
```

**Response (200 OK):**

```json
{
    "id": 123,
    "title": "Updated Track Title",
    "message": "Track updated successfully"
}
```

### Delete Track (Protected)

Delete a track. Only the track owner can delete.

**Endpoint:** `DELETE /api/tracks/{id}`

**Headers:**

```
Authorization: Bearer {token}
```

**Response (200 OK):**

```json
{
    "message": "Track deleted successfully"
}
```

## Health Check

Check the application health status.

**Endpoint:** `GET /api/health`

**Response (200 OK):**

```json
{
    "status": "healthy",
    "timestamp": "2024-01-01T12:00:00+00:00",
    "checks": {
        "database": {
            "status": true,
            "message": "Database connection successful"
        },
        "cache": {
            "status": true,
            "message": "Cache system operational"
        },
        "storage": {
            "status": true,
            "message": "Storage is writable"
        }
    },
    "version": "1.0.0"
}
```

**Response (503 Service Unavailable):** When one or more checks fail

```json
{
    "status": "unhealthy",
    "timestamp": "2024-01-01T12:00:00+00:00",
    "checks": {
        "database": {
            "status": false,
            "message": "Database connection failed: Connection refused"
        },
        "cache": {
            "status": true,
            "message": "Cache system operational"
        },
        "storage": {
            "status": true,
            "message": "Storage is writable"
        }
    },
    "version": "1.0.0"
}
```

## Error Responses

All error responses follow this format:

### 400 Bad Request

```json
{
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### 401 Unauthorized

```json
{
    "message": "Unauthenticated."
}
```

### 403 Forbidden

```json
{
    "message": "This action is unauthorized."
}
```

### 404 Not Found

```json
{
    "message": "Resource not found"
}
```

### 422 Unprocessable Entity

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

### 429 Too Many Requests

```json
{
    "message": "Too many requests. Please try again later."
}
```

### 500 Internal Server Error

```json
{
    "message": "Server error. Please try again later."
}
```

## Rate Limiting

API endpoints are rate limited:

- **Authenticated requests:** 60 requests per minute
- **Guest requests:** 30 requests per minute
- **Authentication endpoints:** 5 requests per minute

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640000000
```

## Pagination

List endpoints support pagination with these parameters:

- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

Pagination metadata is included in responses under the `meta` key.

## Best Practices

1. **Always use HTTPS** in production
2. **Store tokens securely** - never expose them in client-side code
3. **Handle rate limits** - implement exponential backoff
4. **Validate input** on the client side before sending requests
5. **Handle errors gracefully** - check response status codes
6. **Use appropriate HTTP methods** (GET, POST, PUT, DELETE)
7. **Include proper headers** (Authorization, Content-Type)
8. **Cache responses** when appropriate to reduce API calls

## Support

For API support, please:

- Check this documentation first
- Review the [main README](README.md)
- Open an issue on GitHub
- Contact support at support@dson-music.com
