#!/bin/bash
set -u
set -e

# Syncs devops scripts to /var/rsms/scripts

STAGE="./devops-scripts"
TARGET="/var/rsms/scripts"
DRY_OPT=''

if [ ! -d "$STAGE" ]; then
    echo 'No devops scripts to sync'
    exit 1;
fi

while getopts "hDdt:" opt; do
    case $opt in
        h )
            echo "Usage:"
            echo ""
            echo "    sync_devops.sh [OPTION...]"
            echo ""
            echo "    -h                      Display this help message."
            echo "    -D                      Dry-run sync"
            echo "    -d                      Diff"
            echo "    -t                      Change sync destination (default: $TARGET)"
            exit 0
            ;;
        D )
            echo "Dry run..."
            DRY_OPT='--dry-run'
            ;;
        t )
            TARGET="$OPTARG"
            echo "Override target to $TARGET"
            ;;
        d )
            diff $TARGET $STAGE
            exit 0
            ;;
        \?)
            echo "Invalid arg $OPTARG" 1>&2
            exit 1
            ;;
    esac
done

echo "rsync -vhca $DRY_OPT $STAGE/* $TARGET"
