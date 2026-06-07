# =============================================================
#  PHP 8.2 FPM Dockerfile - Laravel Backend
#  Laptop Shop DATN
# =============================================================

FROM php:8.4-fpm

# Labels
LABEL maintainer="Dao Duy Minh - DATN 2024"
LABEL description="PHP 8.2 FPM for Laravel Laptop Shop"

# ─── System Dependencies ───────────────────────────────────
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    zip \
    unzip \
    nano \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ─── PHP Extensions ────────────────────────────────────────
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    xml \
    opcache

# ─── Composer ──────────────────────────────────────────────
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# ─── Working Directory ─────────────────────────────────────
WORKDIR /var/www/backend

# ─── Permissions ───────────────────────────────────────────
RUN groupmod -g 1000 www-data \
    && usermod -u 1000 -g www-data www-data

# ─── Expose PHP-FPM port ───────────────────────────────────
EXPOSE 9000

CMD ["php-fpm"]