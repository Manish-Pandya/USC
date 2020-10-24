#!/bin/bash
# Uploads a deployment package to a server by environment name
set -e
set -u

USER="martina3"
BUILDS_DIR=/home/mmart/projects/rsms/erasmus/stage/builds
BUNDLE_TAR="$BUILDS_DIR/$2"
SERVER_ENV="$1"
SERVER="safety-compliance-$SERVER_ENV-web1.sc.edu"

echo "Uploading $BUNDLE_TAR to $SERVER"
rsync -avz -e "ssh -p 555" $BUNDLE_TAR $USER@$SERVER:/var/rsms/deployments
echo "Done"
