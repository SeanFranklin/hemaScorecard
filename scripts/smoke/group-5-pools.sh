#!/usr/bin/env bash
set -u
source "$(dirname "$0")/_lib.sh"

echo "== Group 5: Tournament Pools =="

# Pools
expect 200 "/events/9001/tournaments/9101/pools"                          "pools list"
expect 200 "/events/9001/tournaments/9102/pools"                          "pools list (tournament with no pools) 200-empty"
expect 200 "/events/9001/tournaments/9101/pools/9501"                     "pool detail"
expect 200 "/events/9001/tournaments/9101/pools/9503"                     "pool detail (un-ranked finals)"
expect 404 "/events/9001/tournaments/9101/pools/99999"                    "pool missing"
expect 404 "/events/9001/tournaments/9102/pools/9501"                     "pool cross-tournament"
expect 200 "/events/9001/tournaments/9101/pools/9501/roster"              "pool roster"

# Matches + standings
expect 200 "/events/9001/tournaments/9101/pools/9501/matches"             "pool matches list"
expect 200 "/events/9001/tournaments/9101/pools/9501/matches/9601"        "pool match detail"
expect 404 "/events/9001/tournaments/9101/pools/9501/matches/9620"        "placeholder match filtered"
expect 404 "/events/9001/tournaments/9101/pools/9502/matches/9601"        "cross-pool match"
expect 200 "/events/9001/tournaments/9101/pools/9501/standings"           "pool standings"

# Hidden event
expect 200 "/events/9008/tournaments/9103/pools"                          "hidden pools list (200-empty)"
expect 404 "/events/9008/tournaments/9103/pools/9510"                     "hidden pool detail (404)"

# Archived event
expect 200 "/events/9004/tournaments/9104/pools"                          "archived pools list"
expect 200 "/events/9004/tournaments/9104/pools/9511"                     "archived pool detail (override)"

# Auth
expect_unauth 401 "/events/9001/tournaments/9101/pools"                   "pools unauth"

summary "Group 5"
exit $FAILED
