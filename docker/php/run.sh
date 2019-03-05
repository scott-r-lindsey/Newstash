#!/bin/sh

#------------------------------------------------------------------------------
# startup for php
#
# executed as "www-data"
#------------------------------------------------------------------------------

set -o pipefail
set -e

#------------------------------------------------------------------------------

if [ -f release.txt ]; then
    echo -n "run.sh executed, release "
    echo `cat release.txt`
else

#------------------------------------------------------------------------------
# make sure mongo session has indexes
php MongoWarmup.php

# start the command
exec php-fpm

