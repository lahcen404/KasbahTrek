FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    zip \
    postgresql-dev \
    nodejs \
    npm \
    build-base \
    autoconf \
    re2c \
    libtool \
    make \
    pkgconfig \
    zlib-dev \
    oniguruma-dev \
    libxml2-dev

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    xml \
    bcmath \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN printf "upload_max_filesize=20M\npost_max_size=20M\nmax_file_uploads=20\n" > /usr/local/etc/php/conf.d/uploads.ini

RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

USER www-data
