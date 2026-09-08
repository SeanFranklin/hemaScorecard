import { test, expect } from './helpers/fixtures';
import { TEST_EVENT_ID, TEST_EVENT_NAME, TEST_TOURNAMENT_ID } from './helpers/test-data';

/**
 * Authenticated smoke test — proves the per-worker storageState produced by
 * helpers/fixtures.ts carries a working organizer session.
 */
test('organizer session reaches tournament administration', async ({ page }) => {
  // Explicit ?e=&t= pins the session to the seeded tournament regardless of
  // how many tournaments other parallel specs have since added to this
  // event — config.php only auto-selects "the" tournament when exactly one
  // exists, which isn't guaranteed once tests run concurrently.
  await page.goto(`/adminTournaments.php?e=${TEST_EVENT_ID}&t=${TEST_TOURNAMENT_ID}`);

  await expect(page).toHaveURL(/adminTournaments\.php\?e=1&t=1/);
  await expect(page.getByText(TEST_EVENT_NAME).first()).toBeVisible();
  await expect(page.getByRole('link', { name: 'Log Out' }).first()).toBeVisible();
});
