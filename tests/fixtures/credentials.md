# Test Fixture Reference

Seeded by `tests/fixtures/seed.sql` (applied automatically by
`docker-compose.test.yml` on first boot, or by `tests/reset-db.sh`).

## Credentials

| Login type | Password | Where checked |
|---|---|---|
| Event organizer (event 1) | `organizer-test-pw` | `eventSettings.organizerPassword` (bcrypt) |
| Event staff (event 1) | `staff-test-pw` | `eventSettings.staffPassword` (bcrypt) |
| System user `admin` | *(any — password is NULL)* | `systemUsers.password`, seeded by `Tables - Setup_Docker.sql` |

Wrong passwords for organizer/staff **are rejected** (real bcrypt hashes are
seeded), so negative auth tests work. The `admin` system user accepts any
password — a quirk of `checkPassword()` treating NULL as "no password set".

## Seeded data

- **Event 1** — "Playwright Test Event" (PTE), 2026, active, all publication
  flags on (roster/schedule/matches/rules publicly visible).
- **Tournament 1** — FORMAT_MATCH (Sparring Matches), Longsword,
  Franklin 2014 ranking, deductive afterblow, max pool size 5.
- **School 1** — "Test Fencing School" (TFS).
- **Fighters** (rosterID = systemRosterID, all checked in, waivers signed):

| rosterID | Name |
|---|---|
| 1 | Alice Applegate |
| 2 | Brett Bowman |
| 3 | Carol Chandler |
| 4 | Dmitri Dukas |
| 5 | Erin Eastwood |
| 6 | Frank Fischer |

No pools, groups, or matches are seeded — journey tests create those through
the UI.

## Environment commands

```bash
# start isolated test stack (fresh DB every up; dev ./data untouched)
docker compose -f docker-compose.test.yml -p hemascorecard-test up -d

# wait for readiness
curl http://localhost:8000/healthcheck.php   # 200 "OK" when ready

# reset DB to seed state between runs (no container restart)
tests/reset-db.sh

# tear down
docker compose -f docker-compose.test.yml -p hemascorecard-test down
```
