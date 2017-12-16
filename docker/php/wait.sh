#!/bin/sh
                                        
# This is invoked from run.sh for the WEB client's run.sh and runs as www-data
echo -e "Executing wait.sh script... "  

###############################################################################
### database can be slow on a new build, so wait for it here
counter=0

echo "${0} Waiting for RDBMS to come online...";
RET=`./bin/console doctrine:query:sql 'select NOW()' 2>/dev/null`
echo $RET

while ! echo "$RET" | grep 'now'; do
  
    if [[ "$counter" -gt 1000 ]]; then
       echo "Counter: $counter times reached; Exiting as something is wrong..."
       exit 1                           
    fi
      
    sleep 1
    echo "(${counter}) ${0} Zzzzz..."   
    RET=`./bin/console doctrine:query:sql 'select NOW()' 2>/dev/null`
          
    counter=$((counter+1))              
done

echo "Waiting complete, RDBMS seems active";

