#!/bin/bash
set -euo pipefail

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*"
}

fatal() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [FATAL] $*" >&2
    exit 1
}

if [ $# -eq 0 ]; then
    fatal "No command provided."
fi

trap "log 'Received termination signal, shutting down...'; exit 0" SIGTERM SIGINT

case "$1" in
    fpm)
        log "Starting php-fpm only"
        php -v
        exec php-fpm -c /var/www/docker/app/php.ini -y /var/www/docker/app/php-fpm.conf
        ;;

    app)
        log "Starting app"
        php -v
        exec /usr/bin/supervisord -n -c /var/www/docker/app/supervisord.conf
        ;;

    *)
        log "Executing custom command: $*"
        exec "$@"
        ;;
esac
