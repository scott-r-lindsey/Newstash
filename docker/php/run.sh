#!/bin/sh

#------------------------------------------------------------------------------
# startup for php
#
# executed as "www-data"
#------------------------------------------------------------------------------

set -o pipefail
set -e

#------------------------------------------------------------------------------

echo -n "run.sh executed, release "
echo `cat release.txt`

#------------------------------------------------------------------------------
# make sure mongo session has indexes
php MongoWarmup.php

# start the command
exec php-fpm

