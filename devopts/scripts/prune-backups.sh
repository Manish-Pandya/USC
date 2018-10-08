#!/bin/bash
#
# Script to prune backups from directory

DIR=/var/rsms/backup
NUM=7
CMD="rm"

cd $DIR

# 1. list files in reverse (r) cronological (t) order
# 2. trim list to the last NUM files
# 3. execute CMD, unless there are fewer than NUM
ls -tr "$DIR/*.tar.gz" | head -n -$NUM | xargs --no-run-if-empty $CMD
