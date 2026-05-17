# API smoke tests

Per-group cURL sweeps that verify the read-only API matches the documented
status codes and shapes. No dependencies beyond `bash`, `curl`, `python3`
(for JSON assertions), and `docker-compose` (for seeding).

## Prerequisites

- `docker-compose up` running with `web` + `db` containers healthy.
- `data/api_keys.json` contains the dev key `hsc_hFlb8lWRgzWavWtJ3DkH50wstvZ65H5DXRZ9PYcn3VM` (seeded by default). Override via `KEY=... scripts/smoke/all.sh` if your local key differs.
- `BASE` defaults to `http://localhost:8000/api/v1`. Override for staging: `BASE=https://api.example.com/v1 scripts/smoke/all.sh`.

## Usage

From repo root:

```bash
scripts/smoke/seed.sh           # reset fixtures (idempotent)
scripts/smoke/all.sh            # run every group sweep in order
scripts/smoke/group-5-pools.sh  # run just one group
```

Each script prints one line per endpoint (`200  GET /events`) and exits
non-zero if any expected status doesn't match. Green = pass, red = fail.

## Files

- `seed.sh` — pipes each `sql/group-N.sql` into `docker-compose exec db mysql`.
- `all.sh` — sources each `group-N-*.sh` in order; accumulates failures.
- `group-N-*.sh` — self-contained sweep per design group. Safe to run individually.
- `_lib.sh` — shared `expect`/`expect_unauth`/`summary` helpers. Sourced by each group script, not run directly.
- `sql/_teardown.sql` — drains all smoke-managed rows in reverse-FK order before the group SQL files reseed. Needed because each group's own DELETE block assumes it runs from a state where later groups haven't yet inserted FK-referencing rows.
- `sql/group-N.sql` — idempotent seed-extension blocks extracted from plan preambles. Groups 1+2 have no seed file (they use schema-shipped data).
