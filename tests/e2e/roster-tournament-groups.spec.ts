import { test, expect } from '@playwright/test';
import type { Page } from '@playwright/test';
import { GROUPED_EVENT_ID, ORGANIZER_PASSWORD } from './helpers/test-data';

/**
 * Tournament chips on the roster entry card for an event with many
 * tournaments (seeded event 3: ten). Chips are grouped by weapon, Open tiers
 * before URG; above eight tournaments only the row being worked on shows
 * every chip. Logs in to event 3 itself, since the auth fixture is event 1.
 */

async function logInToGroupedEvent(page: Page) {
  await page.goto('/adminLogIn.php');
  await page.locator('#logInType').selectOption('logInOrganizer');
  await page.locator('#logInEventID').selectOption(String(GROUPED_EVENT_ID));
  await page.locator("input[name='logInData[password]']").fill(ORGANIZER_PASSWORD);
  await page.locator('#logInSubmitButton').click();
  await expect(page.getByRole('link', { name: 'Log Out' }).first()).toBeVisible();
  await page.goto('/participantsEvent.php');
}

async function openGroupedEntryCard(page: Page) {
  await logInToGroupedEvent(page);
  const card = page.locator('#addParticipantsCard');
  if (!(await card.isVisible())) {
    await page.locator("button[name='newParticipantsMode'][value='on']").click();
  }
  await expect(card.locator('.new-participant-row').first()).toBeVisible();
  return card;
}

test('tournaments are grouped by weapon with Open tiers together', async ({ page }) => {
  const card = await openGroupedEntryCard(page);
  const row = card.locator('.new-participant-row').first();

  await expect(row.locator('.tournament-group-label')).toHaveText(['Longsword', 'Longsword Cutting', 'Saber']);
  const groups = row.locator('.tournament-group');
  await expect(groups.nth(0).locator('.tournament-box')).toHaveText(
    ['Tier A Open', 'Tier B Open', 'Tier A URG', 'Tier B URG', 'U18'], { useInnerText: true });
  await expect(groups.nth(2).locator('.tournament-box')).toHaveText(['Tier A Open', 'URG'], { useInnerText: true });
  // Tournaments that are just a weapon share one unlabeled line.
  await expect(groups.last().locator('.tournament-box')).toHaveText(['Sidesword', 'Smallsword'], { useInnerText: true });
});

test('above eight tournaments, rows not being worked on show only their picks', async ({ page }) => {
  const card = await openGroupedEntryCard(page);
  const rows = card.locator('.new-participant-row');
  const first = rows.nth(0);
  const pick = first.locator('.tournament-box', { hasText: 'Tier B URG' });

  await first.locator('.first-name-input').fill('Ana'); // focusing the last row spawns row 2
  await pick.click();
  await expect(first.locator('.tournament-box:visible')).toHaveCount(10);

  await rows.nth(1).locator('.first-name-input').click();
  await expect(first.locator('.tournament-box:visible')).toHaveCount(1);
  await expect(first.locator('.tournament-box:visible')).toHaveText('Longsword - Tier B URG', { useInnerText: true });
  await expect(rows.nth(1).locator('.tournament-box:visible')).toHaveCount(10);

  // Clicking a collapsed row's chip reopens the row instead of unpicking it.
  await pick.click();
  await expect(first.locator('.tournament-box:visible')).toHaveCount(10);
  await expect(pick).toHaveClass(/is-selected/);
});

test('the inline editor groups tournaments too, on a line of their own', async ({ page }) => {
  await logInToGroupedEvent(page);
  const roster = page.locator('#eventRosterForm');
  await roster.locator('tr.roster-row', { hasText: 'Ibsen' }).locator('.roster-name').click();

  const editor = roster.locator('tr.roster-editor');
  await expect(editor.locator('.tournament-group-label')).toHaveText(['Longsword', 'Longsword Cutting', 'Saber']);
  await expect(editor.locator('.tournament-group').first().locator('.tournament-box')).toHaveText(
    ['Tier A Open', 'Tier B Open', 'Tier A URG', 'Tier B URG', 'U18'], { useInnerText: true });

  const name = await editor.locator('.edit-first-name').boundingBox();
  const chips = await editor.locator('.edit-tournaments').boundingBox();
  expect(chips!.y).toBeGreaterThan(name!.y + name!.height);
});
