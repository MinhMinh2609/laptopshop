#!/bin/bash
set -e

echo "=== LaptopShop Production Startup ==="

cd /var/www/backend

# Xóa cache cũ trước khi rebuild
php artisan config:clear   2>/dev/null || true
php artisan cache:clear    2>/dev/null || true
php artisan route:clear    2>/dev/null || true
php artisan view:clear     2>/dev/null || true

# Chờ MySQL sẵn sàng (tối đa 60 giây)
echo "Waiting for database..."
for i in $(seq 1 30); do
    php artisan db:show --no-interaction > /dev/null 2>&1 && break
    echo "  DB not ready, retry $i/30..."
    sleep 2
done

# Chạy migration tự động
echo "Running migrations..."
php artisan migrate --force --no-interaction

# Cache cho production (tăng hiệu năng)
echo "Caching config, routes, views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "=== Startup complete. Starting PHP-FPM ==="

exec "$@"
