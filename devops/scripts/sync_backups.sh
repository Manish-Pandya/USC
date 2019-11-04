#!/bin/bash
echo "Sync backups off-server"
rsync -avz --delete -e "ssh -p 555" /var/rsms/backup safety-compliance-test-web1:/var/rsms/prod-backup >> /var/rsms/rsms-backups.log