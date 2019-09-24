#!/usr/bin/env sh
set -e

SERVER_CONFIG_PATH="${SERVER_CONFIG_PATH:-/etc/nginx/nginx.conf}";

FPM_PORT="${FPM_PORT:-9000}";
FPM_HOST="${FPM_HOST:-php-fpm}";
FPM_UPSTREAM_PARAMS="${FPM_UPSTREAM_PARAMS:-max_fails=3 fail_timeout=30s}";
ROOT_DIR="${ROOT_DIR:-/usr/share/nginx/html}";
APP_BASE_URL="${APP_BASE_URL:-https://domain.name/}";
ADDITIONAL_FPM_HOSTS="${ADDITIONAL_FPM_HOSTS:-# Additional fpm hosts not passed}";

sed -i "s#%FPM_PORT%#${FPM_PORT}#g" "$SERVER_CONFIG_PATH";
sed -i "s#%FPM_HOST%#${FPM_HOST}#g" "$SERVER_CONFIG_PATH";
sed -i "s#%FPM_UPSTREAM_PARAMS%#${FPM_UPSTREAM_PARAMS}#g" "$SERVER_CONFIG_PATH";
sed -i "s#%ROOT_DIR%#${ROOT_DIR}#g" "$SERVER_CONFIG_PATH";
sed -i "s#%APP_BASE_URL%#${APP_BASE_URL}#g" "$SERVER_CONFIG_PATH";
sed -i "s^%ADDITIONAL_FPM_HOSTS%^${ADDITIONAL_FPM_HOSTS}^g" "$SERVER_CONFIG_PATH";

exec "$@";
