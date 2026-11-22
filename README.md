# AnimeStream

Laravel anime streaming app with scraper-backed catalog, database-backed snapshot fallback, and cloud-ready Docker deployment.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
# Scrape catalog and anime pages (supports multi-page & all genres)
php artisan scraper:refresh-snapshots --pages=5 --all-genres
php artisan serve
```

## Maintenance Commands

- **Purge all users except a specific account**
  ```bash
  php artisan users:purge --keep="carmela@example.com"
  ```
  This command deletes every user record except the one with the provided email, cleaning up related tables (`watchlists`, `comments`, `reports`, etc.). Use with caution; the operation is irreversible.


```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
# Scrape catalog and anime pages (supports multi-page & all genres)
php artisan scraper:refresh-snapshots --pages=5 --all-genres
php artisan serve
```

## Scraper & REST APIs

### Artisan Scraper Command
```bash
# Scrape multiple pages across completed & ongoing anime
php artisan scraper:refresh-snapshots --pages=5

# Scrape all 36+ genres
php artisan scraper:refresh-snapshots --all-genres

# Deep scrape including detailed anime metadata
php artisan scraper:refresh-snapshots --pages=5 --all-genres --deep --anime-limit=50
```

### Scrape REST API Endpoints
- `GET /api/home` - Home page feed (ongoing, complete, popular, latest episodes)
- `GET /api/ongoing-anime?page=1` - Paginated ongoing anime
- `GET /api/complete-anime?page=1` - Paginated completed anime
- `GET /api/anime-list` - Full anime title list
- `GET /api/genres` - Available anime genres
- `GET /api/genres/{genre}?page=1` - Anime by genre with pagination
- `GET /api/search?q={query}` - Search anime by keyword
- `GET /api/anime/{animeId}` - Detailed anime information & episode list
- `GET /api/episode/{episodeId}` - Episode streaming link and server mirrors
- `POST /api/scrape/refresh` - On-demand trigger to refresh/warm snapshots (supports `pages`, `all_genres`, `deep`, `limit`)

## Deploy to Koyeb for free testing

This repository includes a Docker-based runtime that is compatible with Koyeb's Git deployment flow.

### Recommended Koyeb setup

- 1 web service
- 1 PostgreSQL database
- builder: `Dockerfile`

### Required environment variables

- `APP_NAME=Fer6origami`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY=<generate locally with php artisan key:generate --show>`
- `APP_URL=https://<your-service>.koyeb.app`
- `ASSET_URL=https://<your-service>.koyeb.app`
- `LOG_CHANNEL=stderr`
- `CACHE_DRIVER=file`
- `SESSION_DRIVER=cookie`
- `QUEUE_CONNECTION=sync`
- `FILESYSTEM_DISK=local`
- `DB_CONNECTION=pgsql`
- `DATABASE_URL=<provided by Koyeb PostgreSQL>`
- `OTAKUDESU_SOURCE_URL=https://otakudesu.best`
- `OTAKUDESU_DISABLE_PROXY=true`
- `OTAKUDESU_PROXY_IMAGES=false`
- `OTAKUDESU_WARM_PAGES=1`
- `OTAKUDESU_WARM_GENRES=action,fantasy`

### First deploy only

Set these temporarily on the web service for the first successful boot:

- `RUN_MIGRATIONS=true`
- `WARM_SNAPSHOTS=true`

After the app is live, change both back to `false` to keep restarts fast.

### Port and startup

The container now follows the platform `PORT` automatically, so it works on Koyeb without hardcoding Render's port assumptions.

## Deploy to Render

Render deployment files are still included:

- [render.yaml](/c:/laragon/www/animestream/render.yaml)
- [Dockerfile](/c:/laragon/www/animestream/Dockerfile)

For Render, use a shared `APP_KEY` for both web and cron services and set `APP_URL` / `ASSET_URL` to the live `.onrender.com` URL after the first deploy.

## Important notes

- Snapshot storage is database-backed via `scraper_snapshots`, so data survives restarts and redeploys.
- Koyeb free tier is suitable for testing, not heavy production traffic.
- Render remains the better fit for long-term production because it supports the multi-service setup more naturally.
