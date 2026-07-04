import { test, expect } from '@playwright/test';
import { FIGHTERS, TEST_EVENT_ID } from './helpers/test-data';

/**
 * Harness smoke tests — public pages, no login. Prove the dockerized app
 * serves pages and the seed data is present.
 */
test('landing page loads', async ({ page }) => {
  await page.goto('/index.php');
  await expect(page).toHaveTitle(/HEMA Scorecard/);
});

test('login page shows the login form', async ({ page }) => {
  await page.goto('/adminLogIn.php');
  await expect(page.locator('#logInType')).toBeVisible();
  await expect(page.locator("input[name='logInData[password]']")).toBeVisible();
  await expect(page.locator('#logInSubmitButton')).toBeVisible();
});

test('public roster shows the seeded fighters', async ({ page }) => {
  await page.goto(`/participantsEvent.php?e=${TEST_EVENT_ID}`);
  for (const fighter of FIGHTERS) {
    await expect(page.getByText(fighter.lastName).first()).toBeVisible();
  }
});
