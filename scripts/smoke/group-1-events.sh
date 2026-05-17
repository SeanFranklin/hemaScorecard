#!/usr/bin/env bash
set -u
# shellcheck source=./_lib.sh
source "$(dirname "$0")/_lib.sh"

echo "== Group 1: Events skeleton =="

# Core endpoints
expect 200 "/health"                                     "health"
expect 200 "/events"                                     "events list"
expect 200 "/events/today"                               "events today"
expect 200 "/events/upcoming"                            "events upcoming"
expect 200 "/events/recent"                              "events recent"
expect 200 "/events/9001"                                "event detail"
expect 404 "/events/99999"                               "missing event -> 404"

# Auth
expect_unauth 401 "/events"                              "events list no auth"

# Filters (Task 5 feature)
expect 200 "/events?year=2026"                           "filter year single"
expect 200 "/events?year=2026,2025"                      "filter year list"
expect 200 "/events?country=us"                          "filter country lowercase"
expect 200 "/events?country=US,CA,GB"                    "filter country list"
expect 200 "/events?isMeta=false"                        "filter isMeta false"
expect 200 "/events?isMeta=true"                         "filter isMeta true"
expect 200 "/events?year=2026&country=US&isMeta=false"   "filter combined"
expect 400 "/events?year=abc"                            "invalid year -> 400"
expect 400 "/events?year=2026,abc"                       "invalid year-list entry -> 400"
expect 400 "/events?country=USA"                         "country 3-letter -> 400"
expect 400 "/events?country=U"                           "country 1-letter -> 400"
expect 400 "/events?isMeta=maybe"                        "invalid isMeta -> 400"

# Roster pagination (pre-existing, verified here)
expect 200 "/events/9001/roster"                         "roster default page"
expect 200 "/events/9001/roster?per_page=5"              "roster small page"
expect 200 "/events/9001/roster?page=99"                 "roster page beyond data"
expect 400 "/events/9001/roster?per_page=abc"            "invalid per_page -> 400"
expect 400 "/events/9001/roster?per_page=99999"          "per_page over max -> 400"

summary "Group 1"
exit $FAILED
