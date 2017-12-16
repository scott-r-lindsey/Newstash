#!/bin/bash

#------------------------------------------------------------------------------
set -o pipefail
set -e
#------------------------------------------------------------------------------

#------------------------------------------------------------------------------
__here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
__root="$__here/../"

. ./bin/colors.sh

# make sure we're not running as root
if (( `/usr/bin/id -u` == 0 )); then
    echo -e `tput setaf 1`"Must NOT be run as root, but you must be a user in the 'docker' group."`tput sgr0`
    exit
fi

MYSQL_PORT=33306
APP_PORT=1337

#------------------------------------------------------------------------------
export APP_PATH=$__root

$__here/bin/devify_container.sh &

cd $__root/docker

echo "-------------------------------------------------------------------------------"
start_blue
echo ""
teal "  This app should be available at http://localhost:$APP_PORT/."
echo ""
blue "  mysql -h 127.0.0.1 -P $MYSQL_PORT -u bookstash --password=bookstash bookstash"
echo ""
echo " To shell into the running php instance:"
echo ""
teal "  docker exec -u www-data -it bookstash-php-container sh"
echo ""
end_color
echo "-------------------------------------------------------------------------------"


docker-compose up

