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

# Entrypoint script: create .env from environment variables, cache config/routes, run migrations, and start services.
RUN cat <<'EOF' > /usr/local/bin/start.sh
#!/usr/bin/env bash
set -euo pipefail

export PORT="${PORT:-8080}"

cd /var/www/html

# ================================================================
# CREATE .env FILE FROM ENVIRONMENT VARIABLES
# ================================================================
# This creates .env from Railway/Docker environment variables
# instead of trying to read a pre-existing .env file

# Use Railway DATABASE_URL when available, and derive the classic DB_* values from it.
if [ -n "${DATABASE_URL:-}" ]; then
    DB_URL="${DATABASE_URL}"
    DB_HOST="$(php -r '$u = parse_url(getenv("DATABASE_URL")); echo $u["host"] ?? "";' )"
    DB_PORT="$(php -r '$u = parse_url(getenv("DATABASE_URL")); echo $u["port"] ?? "5432";' )"
    DB_DATABASE="$(php -r '$u = parse_url(getenv("DATABASE_URL")); echo ltrim($u["path"] ?? "/", "/");' )"
    DB_USERNAME="$(php -r '$u = parse_url(getenv("DATABASE_URL")); echo $u["user"] ?? "";' )"
    DB_PASSWORD="$(php -r '$u = parse_url(getenv("DATABASE_URL")); echo $u["pass"] ?? "";' )"
else
    DB_URL="${DB_URL:-}"
    DB_HOST="${DB_HOST:-}"
    DB_PORT="${DB_PORT:-5432}"
    DB_DATABASE="${DB_DATABASE:-}"
    DB_USERNAME="${DB_USERNAME:-}"
    DB_PASSWORD="${DB_PASSWORD:-}"
fi

cat > .env <<ENVEOF
APP_NAME="${APP_NAME:-ModernStore}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY:-}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-}"
FRONTEND_URL="${FRONTEND_URL:-}"
SANCTUM_STATEFUL_DOMAINS="${SANCTUM_STATEFUL_DOMAINS:-}"
SESSION_DOMAIN="${SESSION_DOMAIN:-}"
SESSION_DRIVER="${SESSION_DRIVER:-database}"
SESSION_LIFETIME="${SESSION_LIFETIME:-120}"
SESSION_ENCRYPT="${SESSION_ENCRYPT:-false}"
SESSION_PATH="${SESSION_PATH:-/}"
APP_LOCALE="${APP_LOCALE:-en}"
APP_FALLBACK_LOCALE="${APP_FALLBACK_LOCALE:-en}"
APP_FAKER_LOCALE="${APP_FAKER_LOCALE:-en_US}"
BCRYPT_ROUNDS="${BCRYPT_ROUNDS:-12}"
LOG_CHANNEL="${LOG_CHANNEL:-stack}"
LOG_STACK="${LOG_STACK:-single}"
LOG_DEPRECATIONS_CHANNEL="${LOG_DEPRECATIONS_CHANNEL:-null}"
LOG_LEVEL="${LOG_LEVEL:-debug}"
DATABASE_URL="${DATABASE_URL:-${DB_URL:-}}"
DB_URL="${DB_URL:-${DATABASE_URL:-}}"
DB_CONNECTION="${DB_CONNECTION:-pgsql}"
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-5432}"
DB_DATABASE="${DB_DATABASE:-laravel}"
DB_USERNAME="${DB_USERNAME:-postgres}"
DB_PASSWORD="${DB_PASSWORD:-}"
DB_SSLMODE="${DB_SSLMODE:-require}"
BROADCAST_CONNECTION="${BROADCAST_CONNECTION:-log}"
FILESYSTEM_DISK="${FILESYSTEM_DISK:-local}"
QUEUE_CONNECTION="${QUEUE_CONNECTION:-database}"
CACHE_STORE="${CACHE_STORE:-database}"
MAIL_MAILER="${MAIL_MAILER:-smtp}"
MAIL_HOST="${MAIL_HOST:-}"
MAIL_PORT="${MAIL_PORT:-465}"
MAIL_USERNAME="${MAIL_USERNAME:-}"
MAIL_PASSWORD="${MAIL_PASSWORD:-}"
MAIL_ENCRYPTION="${MAIL_ENCRYPTION:-ssl}"
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS:-}"
MAIL_FROM_NAME="${MAIL_FROM_NAME:-}"
PAYSTACK_PUBLIC_KEY="${PAYSTACK_PUBLIC_KEY:-}"
PAYSTACK_SECRET_KEY="${PAYSTACK_SECRET_KEY:-}"
PAYSTACK_CALLBACK_URL="${PAYSTACK_CALLBACK_URL:-}"
PORT="${PORT:-8080}"
APP_MAINTENANCE_DRIVER="${APP_MAINTENANCE_DRIVER:-file}"
MAIL_SCHEME="${MAIL_SCHEME:-null}"
VITE_APP_NAME="${VITE_APP_NAME:-ModernStore}"
ENVEOF

echo "✅ .env file created from environment variables"

# ================================================================
# GENERATE APP KEY IF NOT SET
# ================================================================
if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY:-}" = "base64:" ]; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force --no-interaction || true
fi

# ================================================================
# CLEAR CACHES
# ================================================================
echo "🧹 Clearing caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true

# ================================================================
# CACHE CONFIG & ROUTES FOR PERFORMANCE
# ================================================================
echo "⚙️ Caching configuration and routes..."
php artisan config:cache || true
php artisan route:cache || true

# ================================================================
# RUN DATABASE MIGRATIONS
# ================================================================
echo "🗄️ Running database migrations..."
php artisan migrate --force --no-interaction || true

# ================================================================
# SEED DATABASE (optional - comment out if you don't want auto-seeding)
# ================================================================
echo "🌱 Seeding database..."
php artisan db:seed --force --no-interaction || true

# ================================================================
# FIX PERMISSIONS FOR LARAVEL STORAGE
# ================================================================
echo "🔐 Fixing permissions..."
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# ================================================================
# GENERATE NGINX CONFIG WITH PORT
# ================================================================
echo "🌐 Configuring Nginx for port ${PORT}..."
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

# ================================================================
# START PHP-FPM AND NGINX
# ================================================================
echo "🚀 Starting PHP-FPM and Nginx..."
php-fpm -D
nginx -g 'daemon off;'
EOF

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]
