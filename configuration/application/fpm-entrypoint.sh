#!/usr/bin/env sh
set -e

FPM_CONFIG_PATH="${FPM_CONFIG_PATH:-/usr/local/etc/php-fpm.conf}";

FPM_PORT="${FPM_PORT:-9000}";
FPM_USER="${FPM_USER:-root}";
FPM_GROUP="${FPM_GROUP:-root}";

sed -i "s#%FPM_PORT%#${FPM_PORT}#g" "$FPM_CONFIG_PATH";
sed -i "s#%FPM_USER%#${FPM_USER}#g" "$FPM_CONFIG_PATH";
sed -i "s#%FPM_GROUP%#${FPM_GROUP}#g" "$FPM_CONFIG_PATH";

exec "$@";
