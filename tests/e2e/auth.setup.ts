import { test as setup, expect } from '@playwright/test';
import {
  ORGANIZER_PASSWORD,
  ORGANIZER_STORAGE_STATE,
  TEST_EVENT_ID,
} from './helpers/test-data';

/**
 * Logs in as the event organizer for the seeded test event and saves the
 * PHP session cookie. The 'authenticated' project loads this storageState
 * so tests don't repeat the login flow.
 */
setup('authenticate as event organizer', async ({ page }) => {
  await page.goto('/adminLogIn.php');

  await page.locator('#logInType').selectOption('logInOrganizer');
  await page.locator('#logInEventID').selectOption(String(TEST_EVENT_ID));
  await page.locator("input[name='logInData[password]']").fill(ORGANIZER_PASSWORD);
  await page.locator('#logInSubmitButton').click();

  // The header only renders "Log Out" for a logged-in session.
  await expect(page.getByRole('link', { name: 'Log Out' }).first()).toBeVisible();

  await page.context().storageState({ path: ORGANIZER_STORAGE_STATE });
});
