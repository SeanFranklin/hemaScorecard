#!/usr/bin/env bash
set -u
source "$(dirname "$0")/_lib.sh"

echo "== Group 3: Schedules + Workshops =="

# Workshops
expect 200 "/events/9001/workshops"                      "workshops list"
expect 200 "/events/9001/workshops/4402"                 "workshop detail"
expect 404 "/events/9001/workshops/99999"                "missing workshop"

# Schedules (all variants)
expect 200 "/events/9001/schedules/main"                 "schedules main (day-grouped)"
expect 200 "/events/9001/schedules/main/day/1"           "schedules main day 1"
expect 200 "/events/9001/schedules/workshops"            "schedules workshops"
expect 200 "/events/9001/schedules/workshops/day/2"      "schedules workshops day 2"
expect 200 "/events/9001/schedules/school/7001"          "schedules school"
expect 200 "/events/9001/schedules/school/7001/day/1"    "schedules school day 1"
expect 200 "/events/9001/schedules/personal/9201"        "schedules personal"
expect 200 "/events/9001/schedules/personal/9201/day/1"  "schedules personal day 1"
expect 404 "/events/9001/schedules/school/99999"         "schedules school missing"
expect 404 "/events/9001/schedules/personal/99999"       "schedules personal missing"

# Hidden event, schedules list endpoints (200-empty)
expect 200 "/events/9008/schedules/main"                 "schedules main on hidden event"

summary "Group 3"
exit $FAILED
