#!/bin/bash

set -o pipefail
set -e

#------------------------------------------------------------------------------
# this is run from on db server
#------------------------------------------------------------------------------

docker exec -u www-data -it newstash-php-container ./bin/console newstash:mongo:export

