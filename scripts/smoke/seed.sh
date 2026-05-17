#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
SQL_DIR="$SCRIPT_DIR/sql"

if ! command -v docker-compose >/dev/null 2>&1; then
    echo "docker-compose not found on PATH. Exiting." >&2
    exit 1
fi

# First drain all smoke-managed rows in reverse-FK order (_teardown.sql), then
# reseed in dependency order. Each group-*.sql repeats its own DELETEs, but
# those fail on re-run when upstream groups try to wipe eventTournaments while
# downstream placings/standings/matches still reference them. _teardown.sql is
# an idempotent prefix that drains all children first.
for sql_file in \
    "$SQL_DIR"/_teardown.sql \
    "$SQL_DIR"/group-3.sql \
    "$SQL_DIR"/group-4.sql \
    "$SQL_DIR"/group-5.sql \
    "$SQL_DIR"/group-6.sql; do
    if [ ! -f "$sql_file" ]; then
        echo "Missing: $sql_file" >&2
        exit 1
    fi
    echo "-- seeding $(basename "$sql_file") --"
    docker-compose exec -T db mysql -u root -ppassw0rd ScorecardV5 < "$sql_file"
done

echo
echo "== Seed complete =="
