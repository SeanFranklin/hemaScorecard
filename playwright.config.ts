import { defineConfig, devices } from '@playwright/test';

/**
 * E2E test config. Expects the dockerized test stack to be running:
 *   docker compose -f docker-compose.test.yml -p hemascorecard-test up -d
 *
 * Global setup waits for healthcheck.php and resets the DB to the seed
 * state (tests/reset-db.sh) before every run.
 */
export default defineConfig({
  testDir: './tests/e2e',
  globalSetup: './tests/e2e/helpers/global-setup',

  // File-level parallelism: each worker runs whole spec files serially
  // within itself (preserving intra-file step order) while multiple files
  // run concurrently across workers. All workers share the one app/DB
  // (see docker-compose.test.yml's PHP_CLI_SERVER_WORKERS for concurrent
  // request handling); *.auth.spec.ts files get their own per-worker login
  // via tests/e2e/helpers/fixtures.ts so sessions don't collide.
  fullyParallel: false,
  workers: process.env.PW_WORKERS ? Number(process.env.PW_WORKERS) : 4,

  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [['list'], ['html', { open: 'never' }]] : 'list',

  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:8000',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: [
    // Public pages — no login. Any tests/e2e/*.spec.ts not named *.auth.spec.ts.
    {
      name: 'public',
      testMatch: /.*\.spec\.ts/,
      testIgnore: /.*\.auth\.spec\.ts/,
      use: { ...devices['Desktop Chrome'] },
    },
    // Organizer-authenticated pages — tests named *.auth.spec.ts. Each
    // worker logs in lazily on first use (helpers/fixtures.ts) rather than
    // via a shared 'setup' project, so login stays per-worker.
    {
      name: 'authenticated',
      testMatch: /.*\.auth\.spec\.ts/,
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
