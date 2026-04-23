#!/usr/bin/env bash
set -u
source "$(dirname "$0")/_lib.sh"

echo "== Group 6: Tournament Brackets + Placings =="

# Brackets
expect 200 "/events/9001/tournaments/9101/brackets"                         "brackets list"
expect 200 "/events/9001/tournaments/9102/brackets"                         "brackets list (no brackets) 200-empty"
expect 200 "/events/9001/tournaments/9101/brackets/9701"                    "bracket detail"
expect 404 "/events/9001/tournaments/9101/brackets/99999"                   "bracket missing"
expect 404 "/events/9001/tournaments/9102/brackets/9701"                    "bracket cross-tournament"
expect 200 "/events/9001/tournaments/9101/brackets/9701/roster"             "bracket roster"

# Matches + detail
expect 200 "/events/9001/tournaments/9101/brackets/9701/matches"            "bracket matches list"
expect 200 "/events/9001/tournaments/9101/brackets/9701/matches/12101"      "bracket match detail"
expect 404 "/events/9001/tournaments/9101/brackets/9701/matches/12108"      "placeholder filtered"
expect 404 "/events/9001/tournaments/9101/brackets/9702/matches/12101"      "cross-bracket"

# elimType derivation probes
expect 200 "/events/9001/tournaments/9106/brackets"                         "true_double tournament brackets"
expect 200 "/events/9001/tournaments/9105/brackets"                         "single-bracket tournament"

# Placings
expect 200 "/events/9001/tournaments/9101/placings"                         "placings"
expect 200 "/events/9001/tournaments/9102/placings"                         "placings (none) 200-empty"
expect 404 "/events/9001/tournaments/99999/placings"                        "placings, missing tournament"

# Hidden / archived
expect 200 "/events/9008/tournaments/9103/brackets"                         "hidden brackets list (200-empty)"
expect 404 "/events/9008/tournaments/9103/brackets/9710"                    "hidden bracket detail"
expect 200 "/events/9008/tournaments/9103/placings"                         "hidden placings (200-empty)"
expect 200 "/events/9004/tournaments/9104/brackets/9711"                    "archived bracket (override)"
expect 200 "/events/9004/tournaments/9104/placings"                         "archived placings (override)"

summary "Group 6"
exit $FAILED
