#!/bin/bash
PERL_WITH_LINK='{ while(/(RSMS-\d+)/g){ print "$&: https://uscehs.atlassian.net/browse/$&\n" }}'
PERL_TICKET_ONLY='{ while(/(RSMS-\d+)/g){ print "$&\n" } }'

CURRENT_TAG=$(git describe --tags --exact-match 2> /dev/null || git symbolic-ref -q --short HEAD || git rev-parse --short HEAD)
PREVIOUS_TAG=$(git describe --abbrev=0 HEAD~1)

# TODO: This doesn't work if the last tag is the current ref!
#CURRENT_BRANCH=$(git branch | grep \* | cut -d ' ' -f2)
#LAST_TAG=$(git describe --abbrev=0)

echo "Tickets between $PREVIOUS_TAG and $CURRENT_TAG"
git log $PREVIOUS_TAG..$CURRENT_TAG --pretty=oneline --no-merges | perl -ne "$PERL_WITH_LINK" | sort | uniq