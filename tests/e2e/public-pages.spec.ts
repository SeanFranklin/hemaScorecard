import { test, expect } from '@playwright/test';
import { TEST_EVENT_ID, TEST_EVENT_NAME } from './helpers/test-data';

/**
 * Phase 3 — public/read-only pages. No login, no writes.
 */
test('event summary page renders the seeded event and tournament', async ({ page }) => {
  await page.goto(`/infoSummary.php?e=${TEST_EVENT_ID}`);

  // Session-driven redirect selects the event's only tournament.
  await expect(page).toHaveURL(new RegExp(`infoSummary\\.php\\?e=${TEST_EVENT_ID}&t=1`));
  await expect(page.getByText(TEST_EVENT_NAME).first()).toBeVisible();
  await expect(page.getByText('Longsword').first()).toBeVisible();
});
