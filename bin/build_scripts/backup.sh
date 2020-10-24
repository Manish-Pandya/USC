#!/bin/bash
set -u
set -e

TIMESTAMP=$(date +%Y%m%d_%H%M)
CWD=$(pwd)

# TODO: Configure/parameterize
BACKUP_STAGE="./backup"
DOCROOT='/var/www/html'
DEPLOY_CONTEXT='rsms'
DB_NAME='usc_ehs_rsms'
DB_USER='erasmus'

##########
# Backup #
##########
mkdir backup
BACKUP_DOCROOT="$BACKUP_STAGE/rsms_docroot.tar.gz"
BACKUP_DATABASE="$BACKUP_STAGE/rsms_db.sql.gz"

# Backup whole docroot
echo "Backup $DOCROOT/$DEPLOY_CONTEXT to $CWD/$BACKUP_DOCROOT"
tar csf $BACKUP_DOCROOT -C $DOCROOT $DEPLOY_CONTEXT

# Backup database
echo "Backup RSMS database '$DB_NAME' to $CWD/$BACKUP_DATABASE"
echo "$DB_USER"
mysqldump -u $DB_USER -p $DB_NAME --single-transaction --quick --lock-tables=false | gzip > $BACKUP_DATABASE

# Bundle backup files together
tar csf "$BACKUP_STAGE/rsms-$TIMESTAMP-$DEPLOY_CONTEXT.tar.gz" $BACKUP_DATABASE $BACKUP_DOCROOT
