import { test, expect } from './helpers/fixtures';
import type { Locator, Page } from '@playwright/test';
import { UNENTERED_FIGHTER, SAME_NAME_FIGHTERS, SCHOOLS } from './helpers/test-data';

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

  test('rows stop at 15, with a note, until one is removed', async ({ page }) => {
    const card = await openEntryCard(page);
    const rows = card.locator('.new-participant-row');
    const add = card.locator('#addParticipantRow');
    const note = card.locator('#newParticipantLimit');
    await expect(note).toBeHidden();

    for (let i = 0; i < 16; i++) {
      await add.click();
    }
    await expect(rows).toHaveCount(15);
    await expect(note).toBeVisible();

    // Focusing the last row normally spawns another below it.
    await rows.last().locator('.first-name-input').click();
    await expect(rows).toHaveCount(15);

    await rows.last().locator('.remove-row').click();
    await expect(rows).toHaveCount(14);
    await expect(note).toBeHidden();
    await add.click();
    await expect(rows).toHaveCount(15);
  });

  test('leaving with unsaved names asks first', async ({ page }) => {
    const card = await openEntryCard(page);
    await card.locator('.new-participant-row').first().locator('.first-name-input').fill('Unsaved');

    const dialog = page.waitForEvent('dialog');
    await page.locator("button[name='newParticipantsMode'][value='off']").click({ noWaitAfter: true });
    const prompt = await dialog;
    expect(prompt.type()).toBe('beforeunload');
    await prompt.dismiss();
    await expect(card).toBeVisible();
  });

  test('with few tournaments, rows not being worked on keep every chip', async ({ page }) => {
    const card = await openEntryCard(page);
    const rows = card.locator('.new-participant-row');
    await rows.nth(0).locator('.first-name-input').click(); // spawns row 2
    const chip = rows.nth(0).locator('.tournament-box').first();
    if (await chip.locator('input').isChecked()) {
      await chip.click(); // a lone tournament starts picked
    }
    await rows.nth(1).locator('.first-name-input').click();
    await expect(chip).toBeVisible();
    await expect(chip).not.toHaveClass(/is-selected/);
  });

  test('school picker lists the placeholders first and hides schools named Unaffiliated', async ({ page }) => {
    const card = await openEntryCard(page);
    const row = card.locator('.new-participant-row').first();
    await row.locator('.school-toggle').click();

    const options = row.locator('.school-combobox [role=option]');
    await expect(options).toHaveCount(4);
    await expect(options.nth(0)).toHaveText('*Unknown');
    await expect(options.nth(1)).toHaveText('*Unaffiliated');
    await expect(options.filter({ hasText: /^Unaffiliated$/ })).toHaveCount(0);
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

  test('fighter suggestions show each school so same-name fighters can be told apart', async ({ page }) => {
    const card = await openEntryCard(page);
    const row = card.locator('.new-participant-row').first();
    await row.locator('.last-name-input').fill(SAME_NAME_FIGHTERS[0].lastName);

    const options = row.locator('.name-inputs [role=option]');
    await expect(options).toHaveCount(2);
    await expect(options.filter({ hasText: SCHOOLS[0].shortName })).toHaveCount(1);
    await options.filter({ hasText: SCHOOLS[1].shortName }).click();

    await expect(row.locator('.system-roster-id')).toHaveValue(String(SAME_NAME_FIGHTERS[1].systemRosterID));
    await expect(row.locator('.school-input')).toHaveValue(SCHOOLS[1].shortName);
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
    const gable = roster.locator('tr.roster-row', { hasText: 'Gable' });
    const hart = roster.locator('tr.roster-row', { hasText: 'Hart' });
    await expect(gable).toContainText(SCHOOLS[0].shortName);
    await expect(hart).toContainText(SCHOOLS[1].shortName);

    // Both were entered in the tournament, not just the event.
    await expect(gable.locator('.roster-tournaments .tournament-box')).toHaveCount(1);
    await expect(hart.locator('.roster-tournaments .tournament-box')).toHaveCount(1);
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

/**
 * Roster table inline editing. Clicking a row swaps it for an editor that
 * POSTs formName=editEventParticipant with editParticipantData[...] exactly
 * as the old modal did. Uses the fighters added above (serial within file)
 * so it never touches fighters other spec files may have put into matches.
 */
test.describe('event roster inline edit', () => {
  test('roster rows show tournament chips and no edit button', async ({ page }) => {
    await page.goto('/participantsEvent.php');
    const roster = page.locator('#eventRosterForm');
    await expect(roster.locator('th', { hasText: 'Tournaments' })).toBeVisible();
    await expect(roster.getByText('Edit', { exact: true })).toHaveCount(0);
    const hart = roster.locator('tr.roster-row', { hasText: 'Hart' });
    await expect(hart.locator('.roster-tournaments .tournament-box')).toHaveCount(1);
  });

  test('cancel restores the row unchanged', async ({ page }) => {
    await page.goto('/participantsEvent.php');
    const roster = page.locator('#eventRosterForm');
    const gable = roster.locator('tr.roster-row', { hasText: 'Gable' });
    await gable.locator('.roster-name').click();

    const editor = roster.locator('tr.roster-editor');
    await expect(editor).toHaveCount(1);
    await expect(editor.locator('.edit-first-name')).toHaveValue('Greta');
    await editor.locator('.edit-last-name').fill('Nope');
    await editor.locator('.roster-cancel').click();

    await expect(roster.locator('tr.roster-editor')).toHaveCount(0);
    await expect(gable).toBeVisible();
    await expect(gable).toContainText('Gable');
  });

  test('saving an inline edit changes school and tournament entries', async ({ page }) => {
    await page.goto('/participantsEvent.php');
    const roster = page.locator('#eventRosterForm');
    const hart = roster.locator('tr.roster-row', { hasText: 'Hart' });
    const chipName = (await hart.locator('.roster-tournaments .tournament-box').first().innerText()).trim();
    await hart.locator('.roster-school').click();

    const editor = roster.locator('tr.roster-editor');
    await editor.locator('.edit-school-input').fill('Test');
    await page.getByRole('option', { name: SCHOOLS[0].shortName }).click();
    await expect(editor.locator('.edit-school-id')).toHaveValue(String(SCHOOLS[0].schoolID));

    const chip = editor.locator('.tournament-box', { hasText: chipName });
    await expect(chip.locator("input[type='checkbox']")).toBeChecked();
    await chip.click();
    await expect(chip.locator("input[type='checkbox']")).not.toBeChecked();

    await editor.locator('.roster-save').click();

    const saved = roster.locator('tr.roster-row', { hasText: 'Hart' });
    await expect(saved).toContainText(SCHOOLS[0].shortName);
    await expect(saved.locator('.roster-tournaments .tournament-box', { hasText: chipName })).toHaveCount(0);
  });

  test('save is refused while the school box is empty', async ({ page }) => {
    await page.goto('/participantsEvent.php');
    const roster = page.locator('#eventRosterForm');
    await roster.locator('tr.roster-row', { hasText: 'Gable' }).locator('.roster-name').click();
    const editor = roster.locator('tr.roster-editor');
    await editor.locator('.edit-school-input').fill('');
    await editor.locator('.roster-save').click();
    await expect(editor).toHaveCount(1);
    await expect(editor.locator('.edit-school-input')).toHaveClass(/is-invalid-input/);
  });

  test('opening a second row closes the first editor', async ({ page }) => {
    await page.goto('/participantsEvent.php');
    const roster = page.locator('#eventRosterForm');
    await roster.locator('tr.roster-row', { hasText: 'Gable' }).locator('.roster-name').click();
    await roster.locator('tr.roster-row', { hasText: 'Hart' }).locator('.roster-name').click();
    const editor = roster.locator('tr.roster-editor');
    await expect(editor).toHaveCount(1);
    await expect(editor.locator('.edit-last-name')).toHaveValue('Hart');
  });
});
