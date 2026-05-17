#!/usr/bin/env bash
# Shared helpers for smoke scripts. Source this from each group script.
# Not meant to be run directly.

: "${KEY:=hsc_hFlb8lWRgzWavWtJ3DkH50wstvZ65H5DXRZ9PYcn3VM}"
: "${BASE:=http://localhost:8000/api/v1}"

# Global failure counter. Group scripts increment this on mismatches.
FAILED=${FAILED:-0}

# expect EXPECTED_CODE URL [LABEL]
#   Makes an authenticated GET to $BASE$URL and checks the HTTP status.
#   If the status != EXPECTED_CODE, prints a red line and increments $FAILED.
expect() {
    local expected="$1" url="$2"
    local label="${3:-GET $url}"
    local code
    code=$(curl -s -o /dev/null -w "%{http_code}" -H "X-API-Key: $KEY" "$BASE$url")
    if [ "$code" = "$expected" ]; then
        printf "  \033[32m%s\033[0m  %s\n" "$code" "$label"
    else
        printf "  \033[31m%s\033[0m  %s (expected %s)\n" "$code" "$label" "$expected"
        FAILED=$((FAILED + 1))
    fi
}

# expect_unauth EXPECTED_CODE URL [LABEL]
#   Like expect(), but skips the X-API-Key header.
expect_unauth() {
    local expected="$1" url="$2"
    local label="${3:-GET $url (no auth)}"
    local code
    code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE$url")
    if [ "$code" = "$expected" ]; then
        printf "  \033[32m%s\033[0m  %s\n" "$code" "$label"
    else
        printf "  \033[31m%s\033[0m  %s (expected %s)\n" "$code" "$label" "$expected"
        FAILED=$((FAILED + 1))
    fi
}

# summary LABEL
#   Call at the end of a group script.
summary() {
    local label="$1"
    if [ "$FAILED" -eq 0 ]; then
        printf "\n  \033[32m== %s: all checks passed ==\033[0m\n\n" "$label"
    else
        printf "\n  \033[31m== %s: %d failure(s) ==\033[0m\n\n" "$label" "$FAILED"
    fi
}
