#!/bin/bash

# Fail on any non-zero exit
set -e

# Fail on any unset var
set -u

../devilbox.sh up -d httpd php mysql
