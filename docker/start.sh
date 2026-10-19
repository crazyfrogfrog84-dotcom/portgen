#!/bin/sh
set -eu

APP_HTTP_PORT="${PORT:-10000}"
case "$APP_HTTP_PORT" in
    ''|*[!0-9]*)
        echo "PORT must be a numeric HTTP port." >&2
        exit 1
        ;;
esac

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY must be configured before the service starts." >&2
    exit 1
fi

sed -i "s/Listen 80/Listen ${APP_HTTP_PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${APP_HTTP_PORT}>/" /etc/apache2/sites-available/000-default.conf

php artisan package:discover --ansi
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
apache2ctl configtest

php artisan schedule:work &
SCHEDULER_PID=$!
apache2-foreground &
APACHE_PID=$!

stop_services() {
    kill -TERM "$APACHE_PID" "$SCHEDULER_PID" 2>/dev/null || true
    wait "$APACHE_PID" 2>/dev/null || true
    wait "$SCHEDULER_PID" 2>/dev/null || true
}

trap stop_services INT TERM

set +e
while kill -0 "$APACHE_PID" 2>/dev/null && kill -0 "$SCHEDULER_PID" 2>/dev/null; do
    sleep 1
done

if kill -0 "$APACHE_PID" 2>/dev/null; then
    wait "$SCHEDULER_PID"
    SERVICE_STATUS=$?
    echo "Laravel scheduler exited while Apache was running." >&2
    kill -TERM "$APACHE_PID" 2>/dev/null || true
    wait "$APACHE_PID" 2>/dev/null || true
else
    wait "$APACHE_PID"
    SERVICE_STATUS=$?
    kill -TERM "$SCHEDULER_PID" 2>/dev/null || true
    wait "$SCHEDULER_PID" 2>/dev/null || true
fi

exit "$SERVICE_STATUS"
