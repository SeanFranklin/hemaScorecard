import { test, expect } from './helpers/fixtures';
import type { Locator, Page } from '@playwright/test';
import { UNENTERED_FIGHTER, SCHOOLS } from './helpers/test-data';

/**
 * Event roster entry (participantsEvent.php) — the "Add Event Participants"
 * card. Rows are added/removed client-side; each row has first/last name
 * inputs with an autocomplete over fighters already in the system, a school
 * combobox, and tournament chips. Submitting POSTs formName=addEventParticipants
 * with newParticipants[k][...] exactly as the legacy form did.
 */

/** Chips pre-select when the event has one tournament, so toggle only if needed. */
async function ensureChipSelected(row: Locator) {
  const chip = row.locator('.tournament-box').first();
  const checkbox = chip.locator("input[type='checkbox']");
  if (!(await checkbox.isChecked())) {
    await chip.click();
  }
  await expect(checkbox).toBeChecked();
}

async function openEntryCard(page: Page) {
  await page.goto('/participantsEvent.php');
  const card = page.locator('#addParticipantsCard');
  if (!(await card.isVisible())) {
    await page.locator("button[name='newParticipantsMode'][value='on']").click();
  }
  await expect(card).toBeVisible();
  // Rows are built on DOMContentLoaded.
  await expect(card.locator('.new-participant-row').first()).toBeVisible();
  return card;
}

test.describe('event roster entry', () => {
  test('starts with one row; rows can be added and removed', async ({ page }) => {
    const card = await openEntryCard(page);
    const rows = card.locator('.new-participant-row');
    await expect(rows).toHaveCount(1);

    await card.locator('#addParticipantRow').click();
    await expect(rows).toHaveCount(2);

    await rows.last().locator('.remove-row').click();
    await expect(rows).toHaveCount(1);
  });

  test('clicking into the last row spawns a fresh one below it', async ({ page }) => {
    const card = await openEntryCard(page);
    const rows = card.locator('.new-participant-row');
    await expect(rows).toHaveCount(1);

    await rows.nth(0).locator('.first-name-input').click();
    await expect(rows).toHaveCount(2);

    // Only the last row spawns; revisiting an earlier row does nothing.
    await rows.nth(0).locator('.last-name-input').click();
    await expect(rows).toHaveCount(2);

    await rows.nth(1).locator('.school-input').click();
    await expect(rows).toHaveCount(3);
  });

  test('picking a known fighter fills the name, locks the school, and sets the ID', async ({ page }) => {
    const card = await openEntryCard(page);
    const row = card.locator('.new-participant-row').first();

    await row.locator('.first-name-input').fill('Gre');
    await page.getByRole('option', { name: `${UNENTERED_FIGHTER.firstName} ${UNENTERED_FIGHTER.lastName}` }).click();

    await expect(row.locator('.last-name-input')).toHaveValue(UNENTERED_FIGHTER.lastName);
    await expect(row.locator('.system-roster-id')).toHaveValue(String(UNENTERED_FIGHTER.systemRosterID));
    await expect(row.locator('.school-input')).toHaveValue(SCHOOLS[0].shortName);
    await expect(row.locator('.school-input')).toHaveAttribute('readonly', '');
    await expect(row.locator('.school-id')).toHaveValue(String(SCHOOLS[0].schoolID));

    // Editing the name after a pick turns the row back into a new fighter.
    await row.locator('.last-name-input').fill('Gables');
    await expect(row.locator('.system-roster-id')).toHaveValue('0');
    await expect(row.locator('.school-input')).not.toHaveAttribute('readonly', '');
  });

  test('a known and a new fighter are added to the event with tournaments', async ({ page }) => {
    const card = await openEntryCard(page);
    const rows = card.locator('.new-participant-row');

    // Row 1: known fighter through the autocomplete.
    await rows.nth(0).locator('.first-name-input').fill('Gre');
    await page.getByRole('option', { name: 'Greta Gable' }).click();
    await ensureChipSelected(rows.nth(0));

    // Row 2: brand-new fighter with a school chosen from the combobox.
    await rows.nth(1).locator('.first-name-input').fill('Hana');
    await rows.nth(1).locator('.last-name-input').fill('Hart');
    await rows.nth(1).locator('.school-input').fill('Other');
    await page.getByRole('option', { name: SCHOOLS[1].shortName }).click();
    await expect(rows.nth(1).locator('.school-id')).toHaveValue(String(SCHOOLS[1].schoolID));
    await ensureChipSelected(rows.nth(1));

    // Row 3 stays blank and must not block submission.
    await card.locator("button[name='formName'][value='addEventParticipants']").click();

    const roster = page.locator('#eventRosterForm');
    await expect(roster.locator('tr.pointer', { hasText: 'Gable' })).toContainText(SCHOOLS[0].shortName);
    await expect(roster.locator('tr.pointer', { hasText: 'Hart' })).toContainText(SCHOOLS[1].shortName);

    // Both were entered in the tournament, not just the event.
    await expect(roster.getByText('Tournament Entries for Greta')).toBeAttached();
    await expect(roster.getByText('Tournament Entries for Hana')).toBeAttached();
  });

  test('adminSchools return button reopens the entry card', async ({ page }) => {
    await page.goto('/adminSchools.php');
    await page.getByRole('button', { name: /Return To Add Participants/ }).click();
    await expect(page).toHaveURL(/participantsEvent\.php/);
    await expect(page.locator('#addParticipantsCard')).toBeVisible();
  });

  test('done button closes the entry card', async ({ page }) => {
    await openEntryCard(page);
    const done = page.locator("button[name='newParticipantsMode'][value='off']");
    await expect(done).toHaveText(/Done Adding Participants/);
    await done.click();
    await expect(page.locator('#addParticipantsCard')).toHaveCount(0);
    await expect(page.locator("button[name='newParticipantsMode'][value='on']")).toBeVisible();
  });
});

