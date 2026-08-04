#!/usr/bin/env bash
set -euo pipefail

ME=$(basename "$0")

echo "[$ME] Generating SSL certificates..."
if [ -x /opt/generate-ssl-certs.sh ]; then
    /opt/generate-ssl-certs.sh
fi

echo "[$ME] Setting permissions..."
chown -R www-data:www-data storage
mkdir -p storage/framework/views storage/logs

echo "[$ME] Setting environment variables from Docker secrets..."
if [ -d /run/secrets ]; then
    for f in $(find /run/secrets/toenv -type f); do
        key=$(basename "$f")
        value=$(cat "$f")
        export "$key=$value"
    done
fi

echo "[$ME] Running artisan optimize..."
php artisan optimize

exec "$@"
