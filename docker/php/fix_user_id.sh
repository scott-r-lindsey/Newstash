#!/bin/sh
                                        
set -o errexit                          
set -o nounset                          
set -o pipefail

# -----------------------------------------------------------------------------

if [ -s /www-data-user-id ]; then       

    TARGET_USER_ID=1000

    sed -i "s/82:82/$TARGET_USER_ID:$TARGET_USER_ID/g" /etc/passwd
    sed -i "s/:82:/:$TARGET_USER_ID:/g" /etc/group
  
    chown -R www-data:www-data /var/www/html/phpapp/Symfony
fi  
