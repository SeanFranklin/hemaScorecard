#!/usr/bin/env bash
set -u
source "$(dirname "$0")/_lib.sh"

echo "== Group 2: Event-nested resources =="

# Announcements
expect 200 "/events/9001/announcements"                  "announcements"
expect 404 "/events/99999/announcements"                 "announcements, missing event"

# Roster (primary list -- covered more in group-1; one probe here)
expect 200 "/events/9001/roster"                         "roster"

# Rules
expect 200 "/events/9001/rules"                          "rules list"
expect 200 "/events/9001/rules/9301"                     "rules detail"
expect 404 "/events/9001/rules/99999"                    "rules missing"

# Hidden event behavior
expect 200 "/events/9008/roster"                         "roster on partial-publish event (200-empty)"
expect 404 "/events/9008/rules/9310"                     "rules on hidden event"

summary "Group 2"
exit $FAILED
