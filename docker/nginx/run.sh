#! /bin/sh
set -ex

if [ "$RUNTIME" = "fargate" ]; then
    sed -i "s/php:9000/localhost:9000/" /etc/nginx/conf.d/default.conf
fi

nginx -g 'daemon off;'
