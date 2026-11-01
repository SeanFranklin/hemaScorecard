import { test, expect } from '@playwright/test';
import { TEST_EVENT_ID } from './helpers/test-data';

test('public roster lists a Tournaments column with no edit controls', async ({ page }) => {
  await page.goto(`/participantsEvent.php?e=${TEST_EVENT_ID}`);
  const roster = page.locator('#eventRosterForm');
  await expect(roster.locator('th', { hasText: 'Tournaments' })).toBeVisible();
  await expect(roster.locator('tr.roster-row').first()).toBeVisible();
  await expect(roster.locator("input[type='checkbox']")).toHaveCount(0);
});
