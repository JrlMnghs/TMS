# Translation Management Service (API v1)

A high-performance Laravel API for managing translation keys, values, tags, and locales with versioned endpoints, repository pattern, and sub-500ms export performance.

## Features
- API versioning: `/api/v1/*`
- Authentication (Laravel Sanctum)
- Translations CRUD with search and filtering (tags, key prefix, text)
- Ultra-fast export by locale (supports tags; streaming)
- Repository pattern, PSR-12, SOLID
- Comprehensive tests (unit, feature, performance)
- OpenAPI spec and Postman collection

## Tech Stack
- PHP 8.1, Laravel 10
- MySQL 8
- Docker + docker-compose
- Redis (optional)

## Requirements
- Docker and docker-compose installed
- Git, Make (optional)

## Quick Start (Docker)
1. Clone and enter the project
```bash
git clone git@github.com:JrlMnghs/TMS.git
cd translation-management-service
```
2. Start containers
```bash
docker compose up -d
```
3. Install dependencies and generate app key
```bash
docker exec -it translation-management-service-web-1 composer install
docker exec -it translation-management-service-web-1 php artisan key:generate
```
4. Environment
- Copy `.env.example` to `.env` and set DB/REDIS/APP configs to match docker compose (MySQL on port 3306, host `translation-management-service-mysql-1`).
- For tests, `.env.testing` is included.

5. Migrate database (fresh)
```bash
docker exec -it translation-management-service-web-1 php artisan migrate:fresh
```

## Seeding Data
- Base sample data (users, locales, tags, small translations):
```bash
docker exec -it translation-management-service-web-1 php artisan db:seed
```
- Memory-efficient large dataset (200k translations target):
```bash
docker exec -it translation-management-service-web-1 php artisan migrate:fresh --seed --seeder=Database\\Seeders\\MemoryEfficientSeeder
```
- Full performance dataset (heavy; adjust memory if needed):
```bash
# Optional alternative
php -d memory_limit=1G artisan db:seed --class=PerformanceTestSeeder
```

Default login user (after base seed):
- email: `test@example.com`
- password: `password`

## API
- Base URL (local): `http://localhost:8080`
- Versioned prefix: `/api/v1`

OpenAPI spec and Postman collection:
- `openapi.yaml`
- `postman/TranslationManagementService.postman_collection.json`

Common endpoints:
- Auth: `POST /api/v1/login`, `POST /api/v1/logout`
- Translations: `GET/POST/PUT/DELETE /api/v1/translations`
- Export: `GET /api/v1/export/{locale}`, `GET /api/v1/export/{locale}/stream`
- Users: `GET /api/v1/users`, `GET /api/v1/users/{id}`

## Running Tests
```bash
docker exec -it translation-management-service-web-1 php artisan test
```
- Tests include unit, feature, and performance suites.

## Performance Notes
- Raw SQL for export with EXISTS for tag filtering
- DB indexes included in migrations
- Streaming responses for large exports

## Useful Commands
```bash
# Shell into app container
docker exec -it translation-management-service-web-1 bash

# Run seeds (base)
docker exec -it translation-management-service-web-1 php artisan db:seed

# Refresh DB and seed 200k translations
docker exec -it translation-management-service-web-1 php artisan migrate:fresh --seed --seeder=Database\\Seeders\\MemoryEfficientSeeder

# Run a single test file
docker exec -it translation-management-service-web-1 php artisan test tests/Feature/Api/V1/ExportControllerTest.php
```

## Contribution & License
- Follow PSR-12 and SOLID principles.
- License: MIT.
