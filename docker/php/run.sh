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
# in the developer context, permissions may need fixing
# but you can't do that if the script has simply exited
# so just sleep zzzzzzz...
#if [ 'local' == $APP_ENV ]; then
#    until touch var/cache/dev/write-test; do
#        echo "Can't write to var/cache/dev, sleeping...";
#        sleep 5;
#    done
#    rm var/cache/dev/write-test
#fi
#
#------------------------------------------------------------------------------
# wait for the db to come up            
#/var/run/wait.sh                        
    
#------------------------------------------------------------------------------
# update the symfony schema             
#./bin/console mheextras:data:schemaupdater --force

#------------------------------------------------------------------------------

# start the command                     
exec php-fpm

