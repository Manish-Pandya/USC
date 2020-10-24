#!/bin/bash

# Fail on any non-zero exit
set -e

# Fail on any unset var
set -u

# TODO: Configure/parameterize
PROJ_ROOT=/home/mmart/projects/rsms/erasmus
DEPLOY_SCRIPTS=$PROJ_ROOT/bin/build_scripts
REPO_BRANCH='develop'

# Usage:
#  build [branchname]

if [ $# -gt 0 ]; then
    REPO_BRANCH="$1"
fi

echo "Prepare to stage deployment of branch '$REPO_BRANCH'"

WORKING_DIR=$PROJ_ROOT/stage/builds
REPO='/tmp/rsms-stage-repo'

# clone the HEAD of specified branch
# NOTE: Not a shallow clone so that we can use git-describe as a pseudo artifact name
echo "Cloning branch '$REPO_BRANCH' of repository"
git clone --single-branch -b $REPO_BRANCH git@bitbucket.org:mitch_ehs/erasmus.git $REPO

# Gather information about code state
cd $REPO
# Get description state
GIT_DESC=$(git describe)

# TODO: Describe changelog
cd $WORKING_DIR

# Prepare staging dirs
#  *Clean repo-branch in case it includes slashes
DOCS=$(echo "build-$REPO_BRANCH-$GIT_DESC" | sed -e 's/\//-/g')
mkdir $DOCS

STAGE="$DOCS/stage"
mkdir $STAGE

# Copy deploy tools into docs
cp $DEPLOY_SCRIPTS/* ./$DOCS/

BUNDLE_NAME="$DOCS"
BUNDLE_TAR="$BUNDLE_NAME.tar.gz"

# FIXME: rsync would be a better tool than cp
# Stage all source files
echo "Stage source files for $BUNDLE_NAME in $STAGE"
cp -r $REPO/rsms/src/* ./$STAGE/

# Write state description to a file
echo "Write version file"
echo $BUNDLE_NAME > ./$STAGE/version

# What about db migration scripts? Just copy them all for now
if [ -d "$REPO/db/migrations" ]; then
    echo "Copy migrations"
    cp -r $REPO/db/migrations ./$DOCS
fi

# TODO: What about 'task' migrations? Just copy them all for now
if [ -d "$REPO/rsms/tasks" ]; then
    echo "Copy tasks"
    cp -r $REPO/rsms/tasks ./$DOCS
fi

# Copy devops scripts
if [ -d "$REPO/devops/scripts" ]; then
    echo "Copy devops/scripts"
    cp -r $REPO/devops/scripts ./$DOCS/devops-scripts
fi

# Package it all up
echo "Bundling $DOCS as $BUNDLE_TAR"
echo "    tar czf ./$BUNDLE_TAR ./$DOCS"
tar czf ./$BUNDLE_TAR ./$DOCS

# Clean up
echo "Remove working dir $REPO"
rm -rf $REPO

echo "Remove staging dir ./$DOCS"
rm -r ./$DOCS

echo "$BUNDLE_TAR"
