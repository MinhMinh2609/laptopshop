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
php artisan view:cache || true

# Bỏ qua migrate vì init.sql đã tạo toàn bộ schema + seed data
# Chỉ chạy migrate nếu có migration mới (thêm bảng sau này)
php artisan migrate --force 2>/dev/null || true

# Create storage symlink if it doesn't exist
php artisan storage:link --force 2>/dev/null || true

exec "$@"
