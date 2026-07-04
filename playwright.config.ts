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

  // The app is session-based and all tests share one database — parallel
  // workers would collide. Keep serial until per-worker DB isolation exists.
  fullyParallel: false,
  workers: 1,

  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [['list'], ['html', { open: 'never' }]] : 'list',

  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:8000',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: [
    // Logs in as event organizer and saves the session cookie for the
    // 'authenticated' project.
    {
      name: 'setup',
      testMatch: /.*\.setup\.ts/,
      use: { ...devices['Desktop Chrome'] },
    },
    // Public pages — no login. Any tests/e2e/*.spec.ts not named *.auth.spec.ts.
    {
      name: 'public',
      testMatch: /.*\.spec\.ts/,
      testIgnore: /.*\.auth\.spec\.ts/,
      use: { ...devices['Desktop Chrome'] },
    },
    // Organizer-authenticated pages — tests named *.auth.spec.ts.
    {
      name: 'authenticated',
      testMatch: /.*\.auth\.spec\.ts/,
      dependencies: ['setup'],
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'tests/e2e/.auth/organizer.json',
      },
    },
  ],
});
