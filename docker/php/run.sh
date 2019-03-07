#!/bin/sh

#------------------------------------------------------------------------------
# startup for php
#
# executed as "www-data"
#------------------------------------------------------------------------------

set -o pipefail
set -e

#------------------------------------------------------------------------------
# make sure mongo session has indexes
php MongoWarmup.php

# start the command
exec php-fpm

