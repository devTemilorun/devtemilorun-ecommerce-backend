FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# Install runtime and build dependencies needed by Laravel and common PHP packages.
RUN apk add --no-cache \
    bash \
    curl \
    git \
    nginx \
    gettext \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    libxml2-dev \
    libpq \
    postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /tmp/* /var/tmp/* /var/cache/apk/*

# Copy application source.
COPY . /var/www/html

# Install PHP dependencies for production.
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Fix permissions for Laravel directories.
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && find /var/www/html/storage /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \; \
    && find /var/www/html/storage /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \; \
    && chmod +x /var/www/html/artisan

# Configure Nginx to serve the Laravel public directory.
RUN mkdir -p /etc/nginx/http.d /run/nginx \
    && cat <<'EOF' > /etc/nginx/http.d/default.conf.template
server {
    listen ${PORT};
    server_name _;
    root /var/www/html/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.(?:css|js|png|jpg|jpeg|gif|ico|svg|woff2?|ttf|eot)$ {
        expires 1y;
        access_log off;
        add_header Cache-Control "public, immutable";
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location ~ /\. {
        deny all;
    }
}
EOF

# Entrypoint script: cache config/routes, run migrations, and start services.
RUN cat <<'EOF' > /usr/local/bin/start.sh
#!/usr/bin/env bash
set -euo pipefail

export PORT="${PORT:-8080}"

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY:-}" = "base64:" ]; then
    php artisan key:generate --force --no-interaction || true
fi

php artisan config:cache
php artisan route:cache
php artisan migrate --force --no-interaction

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

php-fpm -D
nginx -g 'daemon off;'
EOF

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]