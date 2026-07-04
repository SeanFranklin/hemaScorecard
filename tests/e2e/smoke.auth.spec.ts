import { test, expect } from '@playwright/test';
import { TEST_EVENT_NAME } from './helpers/test-data';

/**
 * Authenticated smoke test — proves the storageState produced by
 * auth.setup.ts carries a working organizer session.
 */
test('organizer session reaches tournament administration', async ({ page }) => {
  await page.goto('/adminTournaments.php');

  // Session-driven redirect appends the current event/tournament.
  await expect(page).toHaveURL(/adminTournaments\.php\?e=1&t=1/);
  await expect(page.getByText(TEST_EVENT_NAME).first()).toBeVisible();
  await expect(page.getByRole('link', { name: 'Log Out' }).first()).toBeVisible();
});
