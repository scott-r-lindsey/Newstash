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
while [ ! -n "$(docker ps | grep newstash-mysql-container)" ]
do
  sleep 1 &
  wait $!
done

#------------------------------------------------------------------------------
# setup testing db access

green "-----> Fixing user auth";
docker exec -i \
    --env MYSQL_PWD=bookstash \
    newstash-mysql-container \
    mysql \
        --user root bookstash \
        --execute "ALTER USER 'bookstash'@'%' IDENTIFIED WITH mysql_native_password BY 'bookstash'";

green "-----> Creatin testing dba...";
docker exec -i \
    --env MYSQL_PWD=bookstash \
    newstash-mysql-container \
    mysql \
        --user root bookstash \
        --execute "CREATE DATABASE IF NOT EXISTS bookstash_test"

green "-----> Granting access to testing db...";
docker exec -i \
    --env MYSQL_PWD=bookstash \
    newstash-mysql-container \
    mysql \
        --user root bookstash \
        --execute "GRANT ALL PRIVILEGES ON bookstash_test.* TO bookstash@\"%\""

#------------------------------------------------------------------------------
green "-----> MySQL Devification complete!"

