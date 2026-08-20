import { test, expect, devices, type Page } from '@playwright/test';
import { TEST_EVENT_ID } from './helpers/test-data';

/**
 * Mobile hamburger navigation. The toggle must be tappable on the whole
 * "Menu" control, not just the 20x16px hamburger icon.
 */
test.use({ ...devices['Pixel 5'] });

const menu = (page: Page) => page.locator('#tourney-animated-menu');

test('tapping the "Menu" text opens the mobile navigation', async ({ page }) => {
  await page.goto(`/infoSummary.php?e=${TEST_EVENT_ID}`);

  await expect(menu(page)).toBeHidden();
  await page.tap('.title-bar .title-bar-title');
  await expect(menu(page)).toBeVisible();
});

test('mobile menu toggle has an adequate touch target', async ({ page }) => {
  await page.goto(`/infoSummary.php?e=${TEST_EVENT_ID}`);

  const box = await page.locator('.title-bar [data-toggle]').boundingBox();
  expect(box).not.toBeNull();
  expect(box!.height).toBeGreaterThanOrEqual(44);
  expect(box!.width).toBeGreaterThanOrEqual(44);
});
