#!/bin/bash

set -e

# -----------------------------------------------------------------------------
# add target to known hosts if missing
if grep -q "$TUNNEL_TARGET"  ~/.ssh/known_hosts; then
    echo "$TUNNEL_TARGET is in known hosts"
else
    echo "running ssh-keyscan $TUNNEL_TARGET"
    ssh-keyscan  $TUNNEL_TARGET >> ~/.ssh/known_hosts
fi

# -----------------------------------------------------------------------------
# stand up the tunnel
createTunnel() {
    /usr/bin/ssh \
        -f \
        -N \
        -L *:27017:127.0.0.1:27017 \
        -L *:19922:localhost:22 \
        "$TUNNEL_USER@$TUNNEL_TARGET"

    if [[ $? -eq 0 ]]; then
        echo Tunnel to $TUNNEL_USER@$TUNNEL_TARGET created successfully
    else
        echo An error occurred creating a tunnel to hostb RC was $?
    fi
}

createTunnel

# -----------------------------------------------------------------------------
# add localhost:19922 to known hosts if missing

if grep -q "localhost"  ~/.ssh/known_hosts; then
    echo "localhost:19922 is in known hosts"
else
    echo "running ssh-keyscan localhost:19922"
    ssh-keyscan -p 19922 localhost >> ~/.ssh/known_hosts
fi

# -----------------------------------------------------------------------------
## Run the 'ls' command remotely.  If it returns non-zero, then restart the tunnel
while true; do
    sleep 5
    /usr/bin/ssh -p 19922 $TUNNEL_USER@localhost ls >/dev/null
    if [[ $? -ne 0 ]]; then
        echo Creating new tunnel connection
        createTunnel
    fi
done

