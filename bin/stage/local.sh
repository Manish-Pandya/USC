#!/bin/bash
# Uploads a deployment package to a server by environment name
set -e
set -u

PROJ_ROOT=~/projects/rsms/erasmus
REPO=$PROJ_ROOT/wip
STAGE=$PROJ_ROOT/stage/rsms

rsync -vhca --delete \
    --exclude /config/rsms-config.php \
    --exclude /biosafety-committees/protocol-documents/* \
    $REPO/rsms/src/* $STAGE