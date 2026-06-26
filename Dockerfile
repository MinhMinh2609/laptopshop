# =============================================================
#  LAPTOP SHOP DATN - Production Dockerfile
#  Multi-stage: Node (build Vue) → Composer → PHP-FPM
# =============================================================

# ─── Stage 1: Build Vue.js Frontend ──────────────────────
FROM node:20-alpine AS frontend-builder

WORKDIR /app

COPY frontend/package*.json ./
RUN npm ci --prefer-offline

COPY frontend/ ./
RUN npm run build
# Output: /app/dist

# ─── Stage 2: Install PHP Composer Dependencies ──────────
FROM composer:2.7 AS composer-builder

WORKDIR /app

COPY backend/composer.json backend/composer.lock ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts \
    --prefer-dist

# ─── Stage 3: Production PHP-FPM Image ───────────────────
FROM php:8.4-fpm-alpine

LABEL maintainer="LaptopShop DATN"

# System dependencies
RUN apk add --no-cache \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    curl \
    unzip \
    bash

# PHP extensions
RUN docker-php-ext-configure gd \
        --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install \
        pdo_mysql mbstring exif pcntl bcmath gd zip xml opcache

# PHP production config
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

# Copy custom PHP + OPcache config
COPY docker/php/php.prod.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/backend

# Copy vendor từ composer stage
COPY --from=composer-builder /app/vendor ./vendor

# Copy toàn bộ backend
COPY backend/ ./

# Copy built frontend để nginx serve
COPY --from=frontend-builder /app/dist /var/www/frontend/dist

# Tạo symlink storage
RUN php artisan storage:link --no-interaction 2>/dev/null || true

# Permissions
RUN chown -R www-data:www-data \
        /var/www/backend/storage \
        /var/www/backend/bootstrap/cache && \
    chmod -R 775 \
        /var/www/backend/storage \
        /var/www/backend/bootstrap/cache

# Startup script
COPY docker/php/entrypoint.prod.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
