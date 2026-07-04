#!/usr/bin/env bash
# Reset the test database to its seeded state without restarting containers.
#
# Drops and recreates the schema inside the running test-stack MySQL
# container, then replays the same init files docker-entrypoint-initdb.d
# used on first boot (mounted read-only into the container by
# docker-compose.test.yml).
#
# Usage: tests/reset-db.sh
set -euo pipefail

COMPOSE=(docker compose -f docker-compose.test.yml -p hemascorecard-test)
DB_NAME="${PRIMARY_DATABASE:-ScorecardV5}"

"${COMPOSE[@]}" exec -T db sh -c '
  set -e
  DB_NAME="$1"
  MYSQL="mysql -uroot -p$MYSQL_ROOT_PASSWORD"
  $MYSQL -e "DROP DATABASE IF EXISTS \`$DB_NAME\`; CREATE DATABASE \`$DB_NAME\`;"
  for f in /docker-entrypoint-initdb.d/01-schema.sql \
           /docker-entrypoint-initdb.d/02-users.sql \
           /docker-entrypoint-initdb.d/03-seed.sql; do
    $MYSQL "$DB_NAME" < "$f"
  done
' sh "$DB_NAME"

echo "Database '$DB_NAME' reset to seed state."
