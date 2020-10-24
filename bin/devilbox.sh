#!/bin/bash

# Fail on any non-zero exit
set -e

# Fail on any unset var
set -u

# proxy parameters to devilbox
cd /home/mmart/projects/rsms/erasmus/devilbox
docker-compose $@
