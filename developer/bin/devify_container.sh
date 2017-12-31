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
        apk add alpine-sdk autoconf mysql-client &&
        pecl install xdebug &&
        docker-php-ext-enable xdebug &&
        curl https://getcomposer.org/installer >composer-setup.php &&
        php composer-setup.php &&
        mv composer.phar /usr/local/bin/composer &&
        rm composer-setup.php
    '
fi

#------------------------------------------------------------------------------
# setup testing db access

green "-----> Setting up testing dba...";

docker exec -u www-data -i newstash-php-container sh -c '\
    MYSQL_PWD=bookstash mysql -h mysql -u root -e "CREATE DATABASE IF NOT EXISTS bookstash_test"'
docker exec -u www-data -i newstash-php-container sh -c '\
    MYSQL_PWD=bookstash mysql -h mysql -u root -e "GRANT ALL PRIVILEGES ON bookstash_test.* TO bookstash@\"%\""'

#------------------------------------------------------------------------------
green "-----> Devification complete!"

