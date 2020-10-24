#!/bin/sh

# Downloads a backup package from a server by environment name
set -e
set -u

USER="martina3"
REMOTE_BACKUP_DIR=/var/rsms/backup
LOACL_BACKUP_DIR=/home/mmart/projects/rsms/backups
SERVER_ENV="$1"
BACKUP_NAME="$2"
SERVER="safety-compliance-$SERVER_ENV-web1.sc.edu"

LOCAL_TARGET_FILE="$LOACL_BACKUP_DIR/$SERVER_ENV"
LOCAL_TARGET_FILE+="_$BACKUP_NAME"

echo "Downloading backup '$BACKUP_NAME' from $SERVER to $LOCAL_TARGET_FILE"
rsync -avz --progress -e "ssh -p 555" $USER@$SERVER:/var/rsms/backup/$BACKUP_NAME $LOCAL_TARGET_FILE
