# Nudge - Semantic Version Management API

A Symfony-based REST API for managing semantic versioning of projects with build tracking capabilities. Nudge allows you
to create projects, manage their versions following semantic versioning principles (major.minor.patch), and track builds
for specific versions.

## Features

- **Project Management**: Create and manage projects with unique identifiers
- **Semantic Versioning**: Support for major, minor, and patch version bumps
- **Build Tracking**: Track builds for specific project versions with contextual metadata
- **RESTful API**: Clean REST endpoints with JSON responses
- **Validation**: Input validation with proper error handling

## API Endpoints

### Create Project

```http
POST /api/projects
Content-Type: application/json
```

```json
{
    "name": "My Project",
    "initial_version": "1.0.0"
}
```

**Note:** The `initial_version` field is optional. If not provided, the initial version will be set to `0.0.1`.

**Response:**

```json
{
    "success": true,
    "data": {
        "uuid": "a9fa1c7d-4226-4b3c-8201-50a6e6da94c9",
        "name": "My Project"
    }
}
```

### Get Current Version

```http
GET /api/{projectUuid}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "project": {
            "uuid": "a9fa1c7d-4226-4b3c-8201-50a6e6da94c9",
            "name": "My Project"
        },
        "version": "1.0.0",
        "context": null,
        "created_at": "2025-09-25T20:57:06+00:00"
    }
}
```

### Bump Patch Version

```http
POST /api/{projectUuid}/patch
Content-Type: application/json
```

```json
{
    "context": {
        "release_notes": "Bug fixes",
        "actor": "jon"
    }
}
```

**Note:** The `context` field is optional. If included, it must contain valid JSON. If omitted, its value will default
to `null`.

**Response:**

```json
{
    "success": true,
    "data": {
        "version": "1.0.1",
        "context": {
            "release_notes": "Bug fixes",
            "actor": "jon"
        }
    }
}
```

### Bump Minor Version

```http
POST /api/{projectUuid}/minor
Content-Type: application/json
```

```json
{
    "context": {
        "release_notes": "New features"
    }
}
```

**Note:** The `context` field is optional. If included, it must contain valid JSON. If omitted, its value will default
to `null`.

**Response:**

```json
{
    "success": true,
    "data": {
        "version": "1.1.0",
        "context": {
            "release_notes": "New features"
        }
    }
}
```

### Bump Major Version

```http
POST /api/{projectUuid}/major
Content-Type: application/json
```

```json
{
    "context": {
        "release_notes": "Breaking changes"
    }
}
```

**Note:** The `context` field is optional. If included, it must contain valid JSON. If omitted, its value will default
to `null`.

**Response:**

```json
{
    "success": true,
    "data": {
        "version": "2.0.0",
        "context": {
            "release_notes": "Breaking changes"
        }
    }
}
```

### Create Build

```http
POST /api/{projectUuid}/{version}/builds
Content-Type: application/json
```

```json
{
    "tag": "beta",
    "context": {
        "build_number": "123",
        "commit_hash": "abc123def",
        "branch": "beta"
    }
}
```

**Note:** The `context` field is optional. If included, it must contain valid JSON. If omitted, its value will default
to `null`.

**Response:**

```json
{
    "success": true,
    "data": {
        "version": "1.2.3-beta.1",
        "context": {
            "build_number": "123",
            "commit_hash": "abc123def",
            "branch": "beta"
        }
    }
}
```

## Docker

Example compose.yaml configuration:

```yaml
services:
    nudge:
        image: ghcr.io/belovai/nudge:latest
        ports:
            - "8000:80"
        volumes:
            - ./.env:/var/www/.env
            - nudge_sqlite:/var/www/var/database/
volumes:
    nudge_sqlite:
        driver: local
```

Setup commands:
```bash
docker compose up -d
docker compose exec -u www-data nudge php bin/console doctrine:database:create
docker compose exec -u www-data nudge php bin/console doctrine:migrations:migrate
```

## Local installation & Setup

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Set up the database:**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

3. **Start the development server:**
   ```bash
   symfony serve
   ```

## Testing

### Database Setup for Tests

```bash
php bin/console --env=test doctrine:database:drop --force
php bin/console --env=test doctrine:database:create
php bin/console --env=test doctrine:schema:create
```

### Run Tests

```bash
php bin/phpunit
```

## Code Quality

### Code Style Fixes

```bash
./vendor/bin/php-cs-fixer fix src
```
