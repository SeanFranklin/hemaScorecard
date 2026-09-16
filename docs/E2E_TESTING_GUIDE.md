# End-to-End Testing Guide

HEMA Scorecard has an automated end-to-end (E2E) test suite built on
[Playwright](https://playwright.dev). No prior Playwright/TypeScript/GitHub Actions experience needed.

## What and why

An E2E test starts a real copy of the app (PHP + MySQL + a real browser),
loads pages, fills forms, and checks the result — the same way a user would.
It catches regressions at the pull request instead of at a tournament, hopefully.

## The pieces

| Piece | What it is |
|---|---|
| `docker-compose.test.yml` | Test specific Docker Compose with it's own configuration for running tests. |
| `tests/fixtures/seed.sql` | Fixed test data for the database. |
| `tests/fixtures/credentials.md` | Cheat sheet of seeded logins, IDs, and names. |
| `tests/reset-db.sh` | Helper script that will reset the test DB to seed values. |
| `healthcheck.php` | Returns 200 when app + DB are ready; used to wait for startup inside of test suite. |
| `playwright.config.ts` | Playwright settings. Ideally shouldn't get messed with much. |
| `tests/e2e/*.spec.ts` | Test files. We have two smoke tests for the test harness itself and 1 sample test. |
| `.github/workflows/e2e.yml` | Runs the suite on GitHub for every PR. |

## Running locally

One-time setup (needs Docker Desktop and Node.js):

```bash
npm install
npx playwright install chromium
```

Every time:

```bash
npm run test:stack:up      # start the test app (~30-60s first boot)
npm run test:e2e           # run the suite (waits + resets DB automatically)
npm run test:stack:down    # stop it
```

The test stack uses port 8000, same as the dev stack — only one at a time.

Other commands:

```bash
npm run test:e2e:ui        # visual UI; best way to debug a failing test
npm run test:db:reset      # manual DB reset
npx playwright test tests/e2e/smoke.spec.ts   # one file only
npx playwright show-report # screenshots/traces from the last failing run
```

## Anatomy of a test

From `tests/e2e/public-pages.spec.ts`:

```ts
import { test, expect } from '@playwright/test';
import { TEST_EVENT_ID, TEST_EVENT_NAME } from './helpers/test-data';

test('event summary page renders the seeded event and tournament', async ({ page }) => {
  await page.goto(`/infoSummary.php?e=${TEST_EVENT_ID}`);

  await expect(page.getByText(TEST_EVENT_NAME).first()).toBeVisible();
  await expect(page.getByText('Longsword').first()).toBeVisible();
});
```

- `page` is a real browser tab Playwright controls; `await` means "wait for
  this step to finish".
- `page.goto(...)` navigates (relative to `http://localhost:8000`).
- `expect(...).toBeVisible()` is the check — it waits and retries for a few
  seconds, then fails with a screenshot.
- Seeded IDs/names come from `tests/e2e/helpers/test-data.ts` — always use
  those constants instead of retyping values.

**Login is handled by file name:**

- `*.spec.ts` — runs logged out (public pages).
- `*.auth.spec.ts` — runs already logged in as the event organizer
  (`auth.setup.ts` logs in once and shares the session cookie). Never write
  login code in a test.

**Writing a new test:** pick the file name by login need, copy an existing
test, change the page and checks, run that file. Find elements like a user
would — `getByText`, `getByLabel`, `getByRole` — or add a `data-testid`
attribute to the PHP template if a page has nothing to grab.

Two rules:

- **Tests must not depend on each other.** The DB resets per run, not per
  test — a test that mutates data must create what it needs itself.
- **No hard-coded waits** (`waitForTimeout`). `expect(...)` already retries.

## CI (GitHub Actions)

`.github/workflows/e2e.yml` makes GitHub run the full suite on a fresh machine
for every PR and push to master. Each PR gets a ✅/❌ automatically; on
failure, click **Details** for the log and download the `playwright-report`
artifact for screenshots and traces. To reproduce, run the suite locally —
it's the same tests against the same seed.

Once stable, make `e2e` a required status check (repo Settings → Branches) so
red blocks merging.

## Future work policy

1. **Every bug fix gets a test** — write it failing first, fix, watch it pass.
2. **New features ship with at least one test** covering the main path.
3. **Highest-value tests to add next:** login/permission checks, then the full
   tournament journey (create → add fighters → pools → score → verify
   standings), then brackets.

Notes:

- Tests run serially (`workers: 1`) because they share one session-based app
  and one DB. Parallelization is future work we aren't bothering with now.
- If the seed grows, update `seed.sql` + `test-data.ts` + `credentials.md`
  together. Keep the seed minimal — create pools/matches through the UI in
  tests instead.

## Troubleshooting

| Symptom | Fix |
|---|---|
| `App not ready ... did not return 200` | Start the stack (`npm run test:stack:up`); make sure the dev stack isn't on port 8000. First boot takes ~30–60s. |
| Connection errors mid-run | Docker stopped or stack went down. |
| All `.auth` tests fail at setup | Seed didn't load: `npm run test:db:reset`; check `seed.sql` for syntax errors. |
| Weird failures after editing `seed.sql` | `npm run test:db:reset`, or restart the stack. |
