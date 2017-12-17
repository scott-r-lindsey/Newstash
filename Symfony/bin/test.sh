#!/bin/sh

export APP_ENV=test

#------------------------------------------------------------------------------
SCRIPTSRC=`readlink -f "$0" || echo "$0"`
__here=`dirname "${SCRIPTSRC}" || echo .`
#------------------------------------------------------------------------------

$__here/phpunit --verbose --debug $*
