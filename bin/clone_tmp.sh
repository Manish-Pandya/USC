#!/bin/bash

# Fail on any non-zero exit
set -e

# Fail on any unset var
set -u

# TODO: Configure/parameterize
REPO_BRANCH='develop'

# Usage:
#  build [branchname]

if [ $# -gt 0 ]; then
    REPO_BRANCH="$1"
fi

WORKING_DIR=$PROJ_ROOT/stage/builds
REPO='/tmp/rsms-stage-repo'

# clone the HEAD of specified branch
# NOTE: Not a shallow clone so that we can use git-describe as a pseudo artifact name
echo "Cloning branch '$REPO_BRANCH' of repository"
git clone --single-branch -b $REPO_BRANCH git@bitbucket.org:mitch_ehs/erasmus.git $REPO
