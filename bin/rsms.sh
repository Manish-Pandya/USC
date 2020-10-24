#!/bin/sh
set -e
set -u

# TODO: Find path to this script so this isn't hard-coded
ROOT=/home/mmart/projects/rsms/erasmus/bin
CMD="$ROOT"

# TODO: Loop arguments & build directory command tree?
#  instead of having a flat set of scripts?
#echo "Evaluating: $@"
while [ "$#" -gt 0 ]
do
    # Pop off the first parameter
    CMD="$CMD/$1"
    shift

    # Check for command as a directory
    if [ -d "$CMD" ]; then
        # Move on to the next parameter
        continue

    # Check for command as a file
    elif [ -f "$CMD" ]; then
        break

    # Check for command as file with .sh extension
    elif [ -f "$CMD.sh" ]; then
        CMD="$CMD.sh"
        break
    fi
done

# Call command, if valid
if [ -f "$CMD" ]; then
    #echo "calling: $CMD $@"
    sh $CMD $@
elif [ ! -f "$CMD" ]; then
    echo "Invalid command: $CMD $@"
    exit 1
fi
