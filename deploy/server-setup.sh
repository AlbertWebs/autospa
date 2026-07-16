#!/usr/bin/env bash
# One-time server setup for AutoSpa at /var/www/expresscarwash (expresscarwash.co.ke).
# Usage: bash server-setup.sh <db_password>
set -euo pipefail

DB_PASS="${1:?Usage: bash server-setup.sh <db_password>}"
APP_DIR=/var/www/expresscarwash
DB_NAME=expresscarwash
DB_USER=expresscarwash

cd "$APP_DIR"

echo "== MySQL database =="
sudo mysql <<SQL
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "== .env =="
if [ ! -f .env ]; then
    cp .env.example .env
fi
sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|^APP_URL=.*|APP_URL=http://expresscarwash.co.ke|" .env
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env
sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env
sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS}|" .env

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

echo "== Laravel setup =="
php artisan storage:link || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "== Permissions =="
sudo chown -R ubuntu:www-data "$APP_DIR"
sudo chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

echo "== Nginx vhost =="
sudo tee /etc/nginx/sites-available/expresscarwash >/dev/null <<'NGINX'
server {
    listen 80;
    listen [::]:80;
    server_name expresscarwash.co.ke www.expresscarwash.co.ke;

    root /var/www/expresscarwash/public;
    index index.php index.html;
    client_max_body_size 50m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

sudo ln -sf /etc/nginx/sites-available/expresscarwash /etc/nginx/sites-enabled/expresscarwash
sudo nginx -t
sudo systemctl reload nginx

echo "== Done =="
