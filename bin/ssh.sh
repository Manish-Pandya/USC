#!/bin/bash
set -e
set -u

USER="martina3"
SERVER_ENV="$1"
SERVER="safety-compliance-$SERVER_ENV-web1.sc.edu"
ssh -p 555 $USER@$SERVER