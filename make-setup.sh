#!/usr/bin/env bash

set -euo pipefail

WEB_CONTAINER=${1:-web}
ENV_NAME=${2:-local}

export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}

# Copy environment variables
if ! [ -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    fi
fi

# Copy docker-compose override configuration (for convenience in 'docker compose up')
if ! [ -f docker-compose.override.yml ] && [ -f docker-compose.dev.yml ]; then
    cp docker-compose.dev.yml docker-compose.override.yml
fi

# Build docker image
docker compose build

# Install PHP dependencies
docker compose run --rm ${WEB_CONTAINER} bash -lc "composer install --no-interaction --prefer-dist"

# Start services
docker compose up -d

# Install Node dependencies
docker compose exec -T ${WEB_CONTAINER} bash -lc "if command -v yarn >/dev/null 2>&1; then yarn install --frozen-lockfile || true; fi; npm install"

# Generate application key
docker compose exec -T ${WEB_CONTAINER} php artisan key:generate --ansi || true

# Prepare database (wait for MySQL then migrate)
docker compose exec -T ${WEB_CONTAINER} bash -lc "until mysqladmin ping -hmysql -p\"$DB_PASSWORD\" --silent; do echo waiting for mysql; sleep 2; done && php artisan migrate --force"

# Build assets
docker compose exec -T ${WEB_CONTAINER} npm run build

# Link storage
docker compose exec -T ${WEB_CONTAINER} php artisan storage:link || true

echo "\nSetup complete. Services are running."
echo "- App:        http://localhost:${APP_PORT:-8080}"
echo "- Mailpit:    http://localhost:${FORWARD_MAILPIT_DASHBOARD_PORT:-8025}"
echo "- phpMyAdmin: http://localhost:${FORWARD_PHPMYADMIN_PORT:-8081}"

