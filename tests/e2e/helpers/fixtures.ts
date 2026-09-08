import { test as base, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';
import { ORGANIZER_PASSWORD, TEST_EVENT_ID } from './test-data';

/**
 * Per-worker organizer login for *.auth.spec.ts files.
 *
 * Playwright workers run in parallel against the one shared app/DB (see
 * docker-compose.test.yml's PHP_CLI_SERVER_WORKERS). If every worker reused
 * the same PHPSESSID, they'd stomp each other's server-side
 * $_SESSION['tournamentID'] pointer. Each worker instead logs in once (its
 * own session, same DB) and every authenticated test in that worker reuses
 * the cached storageState — mirrors Playwright's documented worker-scoped
 * auth pattern.
 */
const test = base.extend<object, { workerStorageState: string }>({
  storageState: async ({ workerStorageState }, use) => {
    await use(workerStorageState);
  },

  workerStorageState: [
    async ({ browser }, use, workerInfo) => {
      const fileName = path.resolve(
        __dirname,
        `../.auth/organizer-${workerInfo.parallelIndex}.json`,
      );

      if (fs.existsSync(fileName)) {
        await use(fileName);
        return;
      }

      // browser.newPage() doesn't inherit the project's `use.baseURL` the
      // way the built-in `page` fixture does, so it's passed explicitly.
      const page = await browser.newPage({
        storageState: undefined,
        baseURL: workerInfo.project.use.baseURL,
      });

      await page.goto('/adminLogIn.php');
      await page.locator('#logInType').selectOption('logInOrganizer');
      await page.locator('#logInEventID').selectOption(String(TEST_EVENT_ID));
      await page.locator("input[name='logInData[password]']").fill(ORGANIZER_PASSWORD);
      await page.locator('#logInSubmitButton').click();

      // The header only renders "Log Out" for a logged-in session.
      await expect(page.getByRole('link', { name: 'Log Out' }).first()).toBeVisible();

      await page.context().storageState({ path: fileName });
      await page.close();

      await use(fileName);
    },
    { scope: 'worker' },
  ],
});

export { test, expect };
