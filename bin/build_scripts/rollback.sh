#!/bin/bash
set -u
set -e

if [ ! -d ./backup ]; then
    echo 'No backup to rollback to...'
    exit 1
fi

# Avoid literal '*' if directory is empty
shopt -s nullglob

DB_BACKUP=''
DOCROOT_BACKUP=''

for f in ./backup/*.gz; do
    if [[ $f == *.tar.gz ]]; then
        DOCROOT_BACKUP=$f
    elif [[ $f == *.sql.gz ]]; then
        DB_BACKUP=$f
    fi
done

echo "Found database backup $DB_BACKUP"
echo "Found docroot backup $DOCROOT_BACKUP"

if [ -z $DB_BACKUP ] || [ -z $DOCROOT_BACKUP ]; then
    echo "Could not find all backup components"
    exit 1
fi

echo "TODO Restore database..."
#mysql -u $DB_USER -p $DB_NAME --single-transaction < $DB_BACKUP > rollback.db.log

echo "TODO Restore docroot..."
