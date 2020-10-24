#!/bin/bash

BUILDS_DIR=/home/mmart/projects/rsms/erasmus/stage/builds
echo "Cleaning staged RSMS Builds: $BUILDS_DIR"
echo "Delete the following files/directories?"
ls -l $BUILDS_DIR

rm -r $BUILDS_DIR/*