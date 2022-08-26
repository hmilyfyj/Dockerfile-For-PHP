#!/bin/sh
set -e

/usr/sbin/nginx

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
        set -- php-fpm "$@"
fi

# 启动 supervisor
supervisord -c /etc/supervisor/supervisord.conf

exec "$@"