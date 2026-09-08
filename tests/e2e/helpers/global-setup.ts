import { execSync } from 'child_process';
import fs from 'fs';
import path from 'path';
import type { FullConfig } from '@playwright/test';

const HEALTHCHECK_TIMEOUT_MS = 120_000;
const POLL_INTERVAL_MS = 3_000;

/**
 * Runs once before the whole suite:
 *  1. Waits for the dockerized app + seeded DB to be ready (healthcheck.php).
 *  2. Resets the database to the seed state so every run is deterministic.
 *  3. Drops the per-worker login cache (helpers/fixtures.ts): the PHP
 *     sessions it points at live in the web container and vanish whenever
 *     the stack is recreated, which would leave every test logged out.
 */
export default async function globalSetup(config: FullConfig) {
  const baseURL = config.projects[0].use.baseURL || 'http://localhost:8000';
  const healthURL = `${baseURL}/healthcheck.php`;

  const deadline = Date.now() + HEALTHCHECK_TIMEOUT_MS;
  for (;;) {
    try {
      const res = await fetch(healthURL);
      if (res.status === 200) break;
    } catch {
      // stack still starting
    }
    if (Date.now() > deadline) {
      throw new Error(
        `App not ready: ${healthURL} did not return 200 within ${HEALTHCHECK_TIMEOUT_MS / 1000}s.\n` +
          'Start the test stack first:\n' +
          '  docker compose -f docker-compose.test.yml -p hemascorecard-test up -d',
      );
    }
    await new Promise((r) => setTimeout(r, POLL_INTERVAL_MS));
  }

  execSync('bash tests/reset-db.sh', { stdio: 'inherit' });

  fs.rmSync(path.resolve(__dirname, '../.auth'), { recursive: true, force: true });
}
