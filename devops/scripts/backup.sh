#!/bin/bash
set -u
set -e

# Timestamp down to second
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

BACKUP_STAGE="/tmp/rsmsbackup_$TIMESTAMP"
mkdir $BACKUP_STAGE

echo "Backup RSMS..."

DATABASE_BACKUP="$BACKUP_STAGE/rsms_db.sql.gz"
DOCROOT_BACKUP="$BACKUP_STAGE/rsms_docroot.tar.gz"

# Backup database
mysqldump --defaults-extra-file=/var/rsms/conf/.rsms.erasmus.my.cnf usc_ehs_rsms --single-transaction --quick --lock-tables=false | gzip -c > "$DATABASE_BACKUP"

# Backup docroot
tar csf "$DOCROOT_BACKUP" -C /var/www/html rsms

# Package together contents
tar csf "/var/rsms/backup/rsms_backup_$TIMESTAMP.tar.gz" -C /tmp "rsmsbackup_$TIMESTAMP"
echo "Backed up RSMS: /var/rsms/backup/rsms_backup_$TIMESTAMP.tar.gz"

# Clean up tmp files
rm -r $BACKUP_STAGE
