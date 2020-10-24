#!/bin/bash
set -u
set -e

# TODO: Verify that backup has been made
echo 'Checking backup...'
if [ ! -d ./backup ]; then
    echo 'Backup for this deployment has not yet been performed'
    ./backup.sh
fi

echo 'Backup directory exists'

# Check Database Migrations
if [ -d ./migrations ]; then
    echo 'Migrations are present in deployment files';
    ./migrate.sh
fi

DOCROOT='/var/www/html'
DEPLOY_CONTEXT='rsms'

# TODO: What about db migration scripts?

# Deploy staged files using Rsync
echo 'Deploying staged files...'

# -v --verbose
# -h --human-readable
# -c --checksum
# -a --archive
#    -r --recursive
#    -l --links
#    -p --perms
#    -t --times
#    -g --group
#    -o --owner
#
# Exclude uploaded contents (but not the dirs themselves)
#    /config/rsms-config.php
#    /biosafety-committees/protocol-documents/*
rsync -vhca --delete --exclude /config/rsms-config.php --exclude /biosafety-committees/protocol-documents ./stage/* $DOCROOT/$DEPLOY_CONTEXT > deploy.log

echo 'Deployment complete; see deploy.log for details'
