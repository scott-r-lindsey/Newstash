#!/bin/bash

#------------------------------------------------------------------------------
set -o pipefail
set -e
#------------------------------------------------------------------------------

#------------------------------------------------------------------------------
__here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
__root="$__here/../"

. $__here/bin/colors.sh
. $__here/config.sh

# make sure we're not running as root
if (( `/usr/bin/id -u` == 0 )); then
    echo -e `tput setaf 1`"Must NOT be run as root, but you must be a user in the 'docker' group."`tput sgr0`
    exit
fi

MYSQL_PORT=33306

#------------------------------------------------------------------------------
export APP_PATH=$__root
export MYSQL_PORT APP_PORT

cd $__root/docker

green "-------------------------------------------------------------------------------"
echo ""
start_teal
echo ""
echo " To shell into the running php instance:"
echo ""
teal "  docker exec -u www-data -it newstash-php-container sh"
echo ""
end_color
green "-------------------------------------------------------------------------------"


docker-compose \
    --file docker-compose-db-master.yml \
    up
