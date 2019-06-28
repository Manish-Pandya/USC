#!/bin/bash
set -u
set -e

DOCROOT='/var/www/html'
DEPLOY_CONTEXT='rsms'
APP_CONFIG_FILE='config/rsms-config.php'
EXCLUDE_CONFIG=''
DB_NAME="usc_ehs_rsms"
PRE_CONFIRM=false
EMAIL_TO="mmartin@graysail.com"
DB_ONLY=0
PROTECTED_TABLES=()

while getopts "hcYd:Dp:a:e:" opt; do
    case $opt in
        h )
            echo "Usage:"
            echo ""
            echo "    restore.sh [OPTION...] [FILE]"
            echo ""
            echo "    -h                      Display this help message."
            echo "    -c                      Exclude the RSMS configuration file from the restoration."
            echo "    -D                      Restore the Database only; the docroot remains untouched."
            echo "    -Y                      Confirm restoration; do not prompt."
            echo "    -d [database_name]      Specify the database name to restore onto (default '$DB_NAME')."
            echo "    -p [table]              Specify one table name to protect (by backing up and reimporting). Multiple tables are supporrted by specifying -p multiple times"
            echo "    -a [context name]       Specify the webapp context name to resore onto (default '$DEPLOY_CONTEXT'"
            echo "    -e [email address(es)]  Specify one or more email addresses to notify upon completion"
            exit 0
            ;;
        d )
            DB_NAME="$OPTARG"
            echo "Use database '$DB_NAME'"
            ;;
        a )
            DEPLOY_CONTEXT="$OPTARG"
            echo "Use application context '$DEPLOY_CONTEXT'"
            ;;
        c )
            echo "Exclude configuration from backup"
            EXCLUDE_CONFIG="--exclude /$APP_CONFIG_FILE"
            ;;
        D )
            DB_ONLY=1
            ;;
        p )
            PROTECTED_TABLES+=("$OPTARG")
            ;;
        e )
            EMAIL_TO="$OPTARG"
            ;;
        Y )
            echo "Accepting task; will not prompt"
            PRE_CONFIRM=true
            ;;
        \?)
            echo "Invalid arg $OPTARG" 1>&2
            exit 1
            ;;
    esac

done

# Timestamp down to second
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Read in param for backup file
#   Read param after optargs
BACKUP_TARGZ=${@:$OPTIND:1}

if [ ! -f $BACKUP_TARGZ ]; then
    echo "Backup archive $BACKUP_TARGZ does not exist"
    exit 1;
fi

# Create staging directory
BACKUP_RESTORE_STAGE="/tmp/rsmsbackup_restore_$TIMESTAMP"
mkdir $BACKUP_RESTORE_STAGE

# Untar backup into stage
tar xf $BACKUP_TARGZ -C $BACKUP_RESTORE_STAGE

echo "Staging restoration in $BACKUP_RESTORE_STAGE"
cd $BACKUP_RESTORE_STAGE

# Find the name of the internal backup dir and step into it
for entry in ./rsmsbackup_*
do
    BACKUP_ROOT=$entry
    break;
done

if [ ! -d $BACKUP_ROOT ]; then
    echo "Could not find extracted archive files in $BACKUP_RESTORE_STAGE"
    exit 1;
fi

cd $BACKUP_ROOT

# Validate staged archive (should contain 2 files)
DOCROOT_BACKUP="./rsms_docroot.tar.gz"
DB_BACKUP="./rsms_db.sql.gz"

if [ ! -f "$DOCROOT_BACKUP" ]; then
    echo "Docroot backup file $BACKUP_RESTORE_STAGE/$DOCROOT_BACKUP does not exist"
    exit 1;
fi

if [ ! -f "$DB_BACKUP" ]; then
    echo "Database backup file $BACKUP_RESTORE_STAGE/$DB_BACKUP does not exist"
    exit 1;
fi

#####################################
# Confirm that this should continue
echo ''
echo "Restore backup '$BACKUP_TARGZ'"
echo "Backup is extracted and staged for restoration on this server. This will perform the following DESTRUCTIVE tasks:"
echo ''
if [ $DB_ONLY -eq 1 ]; then
    echo "  Retain RSMS application document root:    $DOCROOT/$DEPLOY_CONTEXT"
else
    echo "  Overwrite RSMS application document root: $DOCROOT/$DEPLOY_CONTEXT"
fi

if [ -z "$EXCLUDE_CONFIG" ]; then
  echo "  Overwrite configuration file:             $DOCROOT/$DEPLOY_CONTEXT/$APP_CONFIG_FILE"
else
  echo "  Retain existing configuration file:       $DOCROOT/$DEPLOY_CONTEXT/$APP_CONFIG_FILE"
fi
echo "  Overwrite RSMS database:                  $DB_NAME"

if [ ${#PROTECTED_TABLES[@]} -gt 0 ]; then
    for val in "${PROTECTED_TABLES[@]}"; do
        echo "                                              * Retain $val"
    done
fi
echo ''

if [ $PRE_CONFIRM == false ]
then
    read -p "Are you sure you want to continue? " -n 1 -r
    echo ''
    if [[ ! $REPLY =~ ^[Yy]$ ]]
    then
        echo "Backup restoration operation cancelled."
        exit 0;
    fi
else
    echo "Skipping confirmation as -Y flag was supplied"
fi
#####################################

# Backup protected tables

if [ ${#PROTECTED_TABLES[@]} -gt 0 ]; then
    BACKUP_PROTECTED_TABLES="./protected-tables.sql.gz"
    echo "Backup protected tables: ${PROTECTED_TABLES[@]}..."
    mysqldump --defaults-extra-file=/var/rsms/conf/.rsms.erasmus.my.cnf $DB_NAME --single-transaction --quick --lock-tables=false ${PROTECTED_TABLES[@]} | gzip > $BACKUP_PROTECTED_TABLES
else
    BACKUP_PROTECTED_TABLES=''
fi

# Restore the docroot
echo "Restoring application backup..."

# Stage the docroot restoration
if [ $DB_ONLY -eq 0 ]; then
    # TODO: CHOWN/CHMOD to ensure www-deploy is group and group has rw
    mkdir docroot
    tar -xf $DOCROOT_BACKUP -C ./docroot
    cd ./docroot
    echo "Restoring docroot..."
    rsync -av --delete $EXCLUDE_CONFIG ./rsms/ $DOCROOT/$DEPLOY_CONTEXT > docroot_restore.log
    cd ..
else
    echo "Skip docroot Restoration (-D flag)"
fi

# Restore the db schema
echo "Restoring database '$DB_NAME'..."
zcat $DB_BACKUP | mysql --defaults-extra-file=/var/rsms/conf/.rsms.erasmus.my.cnf $DB_NAME

if [ ! -z "$BACKUP_PROTECTED_TABLES" ]; then
    echo 'Restore protected tables...'
    zcat $BACKUP_PROTECTED_TABLES | mysql --defaults-extra-file=/var/rsms/conf/.rsms.erasmus.my.cnf $DB_NAME
fi

# Output a file into the docroot to specify that a restoration has taken place
echo "$BACKUP_TARGZ restored on $TIMESTAMP" > $DOCROOT/$DEPLOY_CONTEXT/backup_restored

# Done!
COMPLETED_TIMESTAMP=$(date)
echo "Restoration of backup $BACKUP_TARGZ complete at $COMPLETED_TIMESTAMP"

# Send email
if [ ! -z "$EMAIL_TO" ]; then
    HOSTNAME=$(hostname)
    SENDER="backup-restore@$HOSTNAME"
    echo "Sending summary email to $EMAIL_TO..."

    # Headers
    echo "Subject: Refresh of $HOSTNAME - $COMPLETED_TIMESTAMP" > _mail.txt
    echo "From: $HOSTNAME RSMS <$SENDER>" >> _mail.txt
    echo "To: $EMAIL_TO" >> _mail.txt
    echo "Content-Type: text/html" >> _mail.txt
    echo "MIME-Version: 1.0" >> _mail.txt

    # Separator
    echo "" >> _mail.txt

    # Body
    echo "<div>The RSMS instance on $HOSTNAME has been refreshed from a backup archive. The details of the restoration can be found below.</div>" >> _mail.txt

    echo "<h3>Summary of backup restoration</h3>" >> _mail.txt

    echo "<dl style='font-family:monospace; padding-left:20px;'>" >> _mail.txt
    echo "<dt>Restored Backup File</dt> <dd>$BACKUP_TARGZ</dd>" >> _mail.txt
    echo "<dt>Application</dt> <dd>$HOSTNAME/$DEPLOY_CONTEXT</dd>" >> _mail.txt
    echo "<dt>Completed timestamp</dt> <dd>$COMPLETED_TIMESTAMP</dd>" >> _mail.txt
    echo "" >> _mail.txt
    echo "<dt>Application document root</dt>" >> _mail.txt

    if [ $DB_ONLY -eq 1 ]; then
        echo "<dd>Retained</dd>" >> _mail.txt
    else
        echo "<dd>$DOCROOT/$DEPLOY_CONTEXT</dd>" >> _mail.txt
    fi

    if [ -z "$EXCLUDE_CONFIG" ]; then
        echo "<dt>Overwrote configuration file</dt>" >> _mail.txt
    else
        echo "<dt>Retained existing configuration file</dt>" >> _mail.txt
    fi
    echo "<dd>$DOCROOT/$DEPLOY_CONTEXT/$APP_CONFIG_FILE</dd>" >> _mail.txt
    echo "<dt>Overwrote RSMS database</dt> <dd>$DB_NAME</dd>" >> _mail.txt
    if [ ${#PROTECTED_TABLES[@]} -gt 0 ]; then
        echo "<dt>Protected Tables</dt>" >> _mail.txt
        for val in "${PROTECTED_TABLES[@]}"; do
            echo "<dd>$val<dd>" >> _mail.txt
        done
    fi
    echo "</dl>" >> _mail.txt

    echo "<h4>Do not reply to this email. Instead, notify your systems administrator of any issues.</h4>" >> _mail.txt

    /usr/sbin/sendmail -f $SENDER $EMAIL_TO < _mail.txt
fi

# TODO: cleanup?