#!/usr/bin/env bash
set -u
source "$(dirname "$0")/_lib.sh"

echo "== Group 4: Tournaments + Location Schedule =="

# Tournaments
expect 200 "/events/9001/tournaments"                    "tournaments list"
expect 200 "/events/9001/tournaments/9101"               "tournament detail"
expect 200 "/events/9001/tournaments/9102"               "tournament detail 9102"
expect 404 "/events/9001/tournaments/99999"              "tournament missing"
expect 404 "/events/9008/tournaments/9101"               "tournament cross-event"

# Location schedule
expect 200 "/events/9001/schedules/location/6001"        "location schedule"
expect 200 "/events/9001/schedules/location/6001/day/1"  "location schedule day 1"
expect 404 "/events/9001/schedules/location/99999"       "location missing"

summary "Group 4"
exit $FAILED
