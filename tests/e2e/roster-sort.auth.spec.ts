import { test, expect } from './helpers/fixtures';
import type { Page } from '@playwright/test';
import { TEST_EVENT_ID } from './helpers/test-data';

/**
 * Event roster headers sort both ways: clicking the sorted column flips its
 * direction, clicking the other column sorts it ascending.
 */
const header = (page: Page, label: string) => page.locator('thead th[onclick]', { hasText: label });

// The header's onclick handler loads with the page footer, so each click waits
// for the reloaded page to finish loading before the next step
async function sortBy(page: Page, label: string) {
  await Promise.all([page.waitForEvent('load'), header(page, label).click()]);
}

async function rowIndex(page: Page, name: string) {
  const rows = await page.locator('table tbody tr').allTextContents();
  return rows.findIndex((text) => text.includes(name));
}

test('roster Name and School headers sort ascending and descending', async ({ page }) => {
  await page.goto(`/participantsEvent.php?e=${TEST_EVENT_ID}`, { waitUntil: 'load' });

  // Put the roster back on the default sort if an earlier run left it elsewhere
  if (!/\bis-sorted\b/.test((await header(page, 'Name').getAttribute('class')) ?? '')) {
    await sortBy(page, 'Name');
  }
  if (/\bsort-desc\b/.test((await header(page, 'Name').getAttribute('class')) ?? '')) {
    await sortBy(page, 'Name');
  }

  await expect(header(page, 'Name')).toHaveClass(/\bis-sorted\b/);
  await expect(header(page, 'Name')).not.toHaveClass(/\bsort-desc\b/);
  expect(await rowIndex(page, 'Applegate')).toBeLessThan(await rowIndex(page, 'Fischer'));

  await sortBy(page, 'Name');
  await expect(header(page, 'Name')).toHaveClass(/\bsort-desc\b/);
  expect(await rowIndex(page, 'Applegate')).toBeGreaterThan(await rowIndex(page, 'Fischer'));

  await sortBy(page, 'School');
  await expect(header(page, 'School')).toHaveClass(/\bis-sorted\b/);
  await expect(header(page, 'School')).not.toHaveClass(/\bsort-desc\b/);
  await expect(header(page, 'Name')).not.toHaveClass(/\bis-sorted\b/);

  await sortBy(page, 'School');
  await expect(header(page, 'School')).toHaveClass(/\bsort-desc\b/);

  // Leave the shared session on the default sort
  await sortBy(page, 'Name');
  await expect(header(page, 'Name')).toHaveClass(/\bis-sorted\b/);
  await expect(header(page, 'Name')).not.toHaveClass(/\bsort-desc\b/);
});
