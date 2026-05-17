#!/usr/bin/env bash
set -u

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
TOTAL_FAILED=0

for script in \
    "$SCRIPT_DIR/group-1-events.sh" \
    "$SCRIPT_DIR/group-2-nested.sh" \
    "$SCRIPT_DIR/group-3-schedules.sh" \
    "$SCRIPT_DIR/group-4-tournaments.sh" \
    "$SCRIPT_DIR/group-5-pools.sh" \
    "$SCRIPT_DIR/group-6-brackets.sh"
do
    bash "$script"
    TOTAL_FAILED=$((TOTAL_FAILED + $?))
done

echo
if [ "$TOTAL_FAILED" -eq 0 ]; then
    printf "\033[32m==== ALL GROUPS: PASS ====\033[0m\n"
else
    printf "\033[31m==== ALL GROUPS: %d TOTAL FAILURE(S) ====\033[0m\n" "$TOTAL_FAILED"
fi
exit "$TOTAL_FAILED"
