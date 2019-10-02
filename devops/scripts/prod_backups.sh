#!/bin/bash
# Create new backup
/var/rsms/scripts/backup.sh >> /var/rsms/rsms-backups.log

# Prune backups
/var/rsms/scripts/prune-backups.sh >> /var/rsms/rsms-backups.log

# Sync prod backups off-server
/var/rsms/scripts/sync_backups.sh >> /var/rsms/rsms-backups.log
