#!/bin/bash

set -o pipefail
set -e

#------------------------------------------------------------------------------
# this is run from on dozer
#------------------------------------------------------------------------------

__here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
__root="$__here/../"

. $__here/credentials.sh

docker exec -u www-data -it newstash-php-container ./bin/console newstash:generate-sitemap

aws s3 cp ${__root}Symfony/var/sitemap/sitemap.xml.gz s3://newstash-prod-assets/
aws s3 sync ${__root}Symfony/var/sitemap/ s3://newstash-prod-assets/sitemaps/ --exclude=".gitkeep"

