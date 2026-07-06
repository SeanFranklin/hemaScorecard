# Database Migrations

A minimal migration system for applying incremental schema/data changes to the MySQL database.

## How it works

`run.php` scans this directory for `*.sql` files, sorts them alphabetically, and executes any that have not been applied yet. Applied migrations are recorded in a `migrations` table (created automatically) keyed by filename, so each file runs exactly once.

The runner executes automatically on container startup (see `docker-compose.yml`) — after the mysqli extension is installed and before the PHP dev server starts. If any migration fails, the runner exits non-zero and the web server does not start.

Connection settings come from `includes/database.php` (same constants the application uses).

## Writing a migration

1. Create a new `.sql` file in this directory. Files run in alphabetical order, so use an incrementing prefix:

   ```
   V1_eventRankings.sql
   V2_yourChangeHere.sql
   ```

2. Write plain SQL. Multiple statements per file are fine (executed via `multi_query`).

3. Restart the web container (or run manually, below) to apply.

**Rules:**

- **Never edit a migration that has already been applied** — it will not re-run. Create a new file instead.
- Migrations are not transactional; MySQL DDL auto-commits. A file that fails partway may leave partial changes, and since it isn't recorded as applied, it will re-run in full on the next start. Write statements to be safe on re-run where practical (`CREATE TABLE IF NOT EXISTS`, `INSERT ... ON DUPLICATE KEY UPDATE`, etc.).
- There are no down/rollback migrations. To undo a change, write a new migration.

## Running manually

```bash
docker-compose exec web php /hemaScorecard/migrations/run.php
```

Output is prefixed with `[migrations]`; already-applied files are reported as skipped.

## Running without Docker

The runner is plain PHP with no Docker dependency. On any host with PHP (with mysqli) that can reach the database:

```bash
php migrations/run.php
```

Connection settings are read from environment variables (`includes/database.php`), with defaults suited to the Docker setup. Override them for your environment:

```bash
DATABASE_HOST=localhost DATABASE_USER=myuser DATABASE_PASSWORD=mypass PRIMARY_DATABASE=ScorecardV5 php migrations/run.php
```

On Windows (PowerShell):

```powershell
$env:DATABASE_HOST='localhost'; $env:DATABASE_USER='myuser'; $env:DATABASE_PASSWORD='mypass'; php migrations/run.php
```

## Applying via phpMyAdmin (or any SQL client)

If you can't run PHP against the database (e.g. shared hosting with only phpMyAdmin access), apply migrations by hand. The runner is just a bookkeeper — the migrations are plain SQL.

1. Check which migrations have already been applied:

   ```sql
   SELECT filename FROM migrations;
   ```

   If the `migrations` table doesn't exist yet, create it first:

   ```sql
   CREATE TABLE IF NOT EXISTS migrations (
       filename VARCHAR(255) NOT NULL PRIMARY KEY,
       ran_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
   );
   ```

2. For each `.sql` file not in that list, **in alphabetical order**, paste its contents into the SQL tab (or use Import) and run it.

3. After each file succeeds, record it so the runner (and other people) know it's been applied:

   ```sql
   INSERT INTO migrations (filename) VALUES ('V1_eventRankings.sql');
   ```

Skipping step 3 means the runner will try to re-apply the file the next time it executes against this database.

## Resetting migration state

To force a migration to re-run (e.g. in local development), delete its row:

```sql
DELETE FROM migrations WHERE filename = 'V1_eventRankings.sql';
```
