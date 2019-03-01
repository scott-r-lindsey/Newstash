#!/bin/bash

#------------------------------------------------------------------------------
set -o errexit
set -o nounset
set -o pipefail

#------------------------------------------------------------------------------
__dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd $__dir;

. ./colors.sh

#------------------------------------------------------------------------------
while [ ! -n "$(docker ps | grep newstash-php-container)" ]
do
  sleep 1 &
  wait $!
done

#------------------------------------------------------------------------------
# phpdebug installation

if [ ! -x "`docker exec -u www-data -i newstash-php-container sh -c 'which gcc'`" ]; then

    green "-----> Installing xdebug and a ton of build tools...";
    docker exec -u root -i newstash-php-container sh -c '\
        apk add alpine-sdk autoconf mysql-client python2 nodejs-current yarn &&
        pecl install xdebug-beta &&
        docker-php-ext-enable xdebug &&
        curl https://getcomposer.org/installer >composer-setup.php &&
        php composer-setup.php &&
        mv composer.phar /usr/local/bin/composer &&
        rm composer-setup.php
    '
fi

#------------------------------------------------------------------------------
green "-----> PHP Devification complete!"

