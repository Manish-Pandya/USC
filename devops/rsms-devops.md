## Source Control

Core branches and their purpose:

| Branch | Purpose |
| :---- | :---- |
| `master` | Main source branch, deployed to Prod server (and any other) |
| `develop` | Cutting-edge Development branch, deployed to any non-production server |
| `stage/*` | 'Staging' branches intended for test builds |

Feature development should be peformed on a specific branch before being merged into a staging branch. Once a feature is tested and approved, it should be merged into `develop`.

Generally, only `develop` should be merged into `master`

## Server operations

DevOps controls will be placed in `/var/rsms`

```
$ ls -al /var/rsms/*
/var/rsms/backup:
total 0
drwxrwsr-x 2 mitchm www-deploy     4096 Oct  8 15:54 .
drwxrwsr-x 5 root   www-deploy     4096 Oct  8 14:55 ..

/var/rsms/conf:
total 12
drwxrwsr-x 2 mitchm www-deploy 4096 Oct  8 15:14 .
drwxrwsr-x 5 root   www-deploy 4096 Oct  8 14:55 ..
-rw------- 1 mitchm www-deploy   47 Oct  8 15:14 .rsms.erasmus.my.cnf

/var/rsms/scripts:
total 16
drwxrwsr-x 2 mitchm www-deploy 4096 Oct  8 15:58 .
drwxrwsr-x 5 root   www-deploy 4096 Oct  8 14:55 ..
-rwxrwxr-x 1 mitchm www-deploy  761 Oct  8 15:44 backup.sh
-rwxrwxr-x 1 mitchm www-deploy  368 Oct  8 15:58 prune-backups.sh
```

`/var/rsms/conf/.rsms.erasmus.my.cnf` is a MySQL defaults-extra-file which is used to securely configure the credentials with which to perform RSMS database backups.

## Initial setup of RSMS DevOps

#### Create DevOps directory
On target server, create the directory and apply permissions to the www-deploy group
```
sudo mkdir /var/rsms
sudo chown root:www-deploy /var/rsms
sudo chmod 2775 /var/rsms
mkdir /var/rsms/backup
mkdir /var/rsms/deployments
```

#### Upload controls
Upload the controls from the RSMS repository
```
scp -Pr 555 devops/* mitchm@safety-compliance-prod-web1.sc.edu:/var/rsms
```

#### Secure and Configure the connection information
On the target server, update the credentials in the MySQL conf file
```
vi /var/rsms/conf/.rsms.erasmus.my.cnf
```

## Backups of Deployed RSMS
```
chmod 600 /var/rsms/conf/.rsms.erasmus.my.cnf
```

Crontab
```
# Backup RSMS every night at midnight; dump output to log file
0 0 * * * /var/rsms/scripts/backup.sh > /var/rsms/rsms-backups.log

# Prune RSMS backups directory every night following backup
30 0 * * * /var/rsms/scripts/prune-backups.sh > /var/rsms/rsms-backups.log
```

## RSMS Scheduled Tasks
RSMS `Scheduler` module manages tasks which should be run regularly. Because the RSMS server is scripted, we use Cron to ensure that these tasks are regularly executed:

```
# Execute RSMS Scheduler (Messaging module only) every 5 minutes
*/5 * * * * php /var/rsms/scripts/run_scheduler.php Messaging >/dev/null 2>&1

# Execute RSMS Scheduler (All modules) daily at 1am
0 1 * * * php /var/rsms/scripts/run_scheduler.php >/dev/null 2>&1
```

## Backup Restoration

The `restore.sh` script can be used to unpack a backup archive and restore it on the local instance.

```
#
# The '-c' flag indicates that the configuration file should be excluded from the restoration,
#   allowing for easier restorations of a backup from another server (such as restoring a Prod backup on the Test server)
# ./restore.sh -h will list available options
#
$ ./restore.sh -c /var/rsms/prod-backup/backup/rsms_backup_20190405_000001.tar.gz

Exclude configuration from backup
Staging restoration in /tmp/rsmsbackup_restore_20190405_104916

Restore backup '/var/rsms/prod-backup/backup/rsms_backup_20190405_000001.tar.gz'
Backup is extracted and staged for restoration on this server. This will perform the following DESTRUCTIVE tasks:

  Overwrite RSMS application document root: /var/www/html/rsms
  Retain existing configuration file:       /var/www/html/rsms/config/rsms-config.php
  Overwrite RSMS database:                  usc_ehs_rsms

Are you sure you want to continue? y
Restoring application backup...
Restoring docroot...
Restoring database 'usc_ehs_rsms'...
Restoration of backup /var/rsms/prod-backup/backup/rsms_backup_20190405_000001.tar.gz complete
```

### Automated Restoration

The Test server is configured to restore a Prod backup onto the Test server monthly

Crontab:
```
# Pass the latest Prod backup to the restore script, excluding config and pre-accepting confirmation, email results
0 1 1 * * /var/rsms/scripts/restore.sh -cY -e 'mmartin@graysail.com,MROBBINS@mailbox.sc.edu,JLOCKE@mailbox.sc.edu' $(find /var/rsms/prod-backup/backup/ -printf '%p\n' | sort -r | head -n 1)
```

## SMTP Configuration
USC SMTP relay server is `smtp.sc.edu`. Postfix must be configured by updating the `relayhost` property in `/etc/postfix/main.cf` to `[smtp.sc.edu]`

## Document Uploads
Ensure that the document uploads directory has group write permissions

```
chmod g+rwx /var/www/html/rsms/biosafety-committees/protocol-documents/
```

## Application Deployment

