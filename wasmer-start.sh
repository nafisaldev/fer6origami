#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/app}"
if [ ! -d "$APP_DIR" ]; then
  APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
fi
cd "$APP_DIR"

php artisan migrate --force --no-interaction

if [ "${RUN_SEEDERS:-true}" = "true" ]; then
  php artisan db:seed --force --no-interaction
fi

if [ "${IMPORT_SNAPSHOTS:-true}" = "true" ]; then
  php artisan scraper:import-snapshots --no-interaction
fi

exec php -S 0.0.0.0:8080 -t public
