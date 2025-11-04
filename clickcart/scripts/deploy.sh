#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR=/var/www/clickcart

sudo mkdir -p "$PROJECT_DIR"
sudo rsync -a --delete ./ "$PROJECT_DIR" --exclude .env --exclude vendor

cd "$PROJECT_DIR"

if ! command -v composer >/dev/null 2>&1; then
  EXPECTED_SIGNATURE="$(wget -q -O - https://composer.github.io/installer.sig)"
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
  if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then
    >&2 echo 'ERROR: Invalid composer installer signature'
    rm composer-setup.php
    exit 1
  fi
  php composer-setup.php --install-dir=/usr/local/bin --filename=composer
  rm composer-setup.php
fi

composer install --no-interaction --prefer-dist --optimize-autoloader

if [ ! -f .env ]; then
  cp .env.example .env
fi

php -r "require 'vendor/autoload.php'; Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();"

if command -v mysql >/dev/null 2>&1; then
  mysql -u "${DB_USERNAME:-root}" -p"${DB_PASSWORD:-}" < db_init.sql || true
fi

sudo chown -R www-data:www-data "$PROJECT_DIR"/public/uploads
sudo chmod -R 775 "$PROJECT_DIR"/public/uploads

echo "Deployment complete." 
