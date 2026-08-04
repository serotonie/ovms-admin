#!/usr/bin/env bash
set -euo pipefail

ME=$(basename "$0")

echo "[$ME] Generating SSL certificates..."
if [ -x /opt/generate-ssl-certs.sh ]; then
    /opt/generate-ssl-certs.sh
fi

echo "[$ME] Setting permissions..."
chown -R www-data:www-data storage
su -s /bin/bash -c 'mkdir -p storage/framework/views storage/logs' www-data

echo "[$ME] Setting environment variables from Docker secrets..."
if [ -d /run/secrets ]; then
    for f in $(find /run/secrets -type f); do
        key=$(basename "$f")
        value=$(cat "$f")
        export "$key=$value"
    done
fi

echo "[$ME] Running artisan optimize..."
su -s /bin/bash -c 'php artisan optimize' www-data

exec "$@"
