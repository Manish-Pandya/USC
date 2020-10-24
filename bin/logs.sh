#!/bin/bash
ROOT=/home/mmart/projects/rsms/erasmus/local-docroot/rsms.graysail.com/htdocs
LOGS_PATH='rsms/src/logs/'

cd $ROOT/$LOGS_PATH
if [ -z "$1" ]; then
    # Default to main log name
    /usr/bin/tail -f *.log
else
    # Forward all params as log names
    /usr/bin/tail -f $@
fi
