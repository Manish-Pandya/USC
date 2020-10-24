#!/bin/bash
set -u
set -e

# Verify that backup has been made
echo 'Checking backup...'
if [ ! -d ./backup ]; then
    echo 'Warning: No backup has been created; exiting.'
    exit 1
fi

echo 'Backup directory exists'

# Check Database Migrations
if [ -d ./migrations ]; then
    cd ./migrations
    php /var/rsms/scripts/run_migrations.php
else
    echo 'No migrations found.';
    exit 1
fi

