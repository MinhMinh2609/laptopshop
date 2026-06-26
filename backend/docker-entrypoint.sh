#!/bin/sh
set -e

# Laravel requires a .env file to exist (can be empty; real config comes from env vars)
touch /var/www/backend/.env

# Fix permissions on mounted storage volume
chown -R www-data:www-data /var/www/backend/storage /var/www/backend/bootstrap/cache
chmod -R 775 /var/www/backend/storage /var/www/backend/bootstrap/cache

# Cache config/routes/views for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run pending migrations (--force skips the production confirmation prompt)
php artisan migrate --force

# Create storage symlink if it doesn't exist
php artisan storage:link --force 2>/dev/null || true

exec "$@"
