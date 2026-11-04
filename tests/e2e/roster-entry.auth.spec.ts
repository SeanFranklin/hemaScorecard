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

  test('typing into the last row spawns a fresh one below it', async ({ page }) => {
    const card = await openEntryCard(page);
    const rows = card.locator('.new-participant-row');
    await expect(rows).toHaveCount(1);

    // Focus alone doesn't, so Tab can move on past a blank last row.
    await rows.nth(0).locator('.first-name-input').click();
    await expect(rows).toHaveCount(1);
    await page.keyboard.type('A');
    await expect(rows).toHaveCount(2);

    // Only the last row spawns; typing in an earlier row does nothing.
    await rows.nth(0).locator('.last-name-input').fill('B');
    await expect(rows).toHaveCount(2);

    await rows.nth(1).locator('.school-input').fill('Test');
    await expect(rows).toHaveCount(3);
  });

  test('rows stop at 15, with a note, until one is removed', async ({ page }) => {
    const card = await openEntryCard(page);
    const rows = card.locator('.new-participant-row');
    const add = card.locator('#addParticipantRow');
    const note = card.locator('#newParticipantLimit');
    await expect(note).toBeHidden();

    for (let i = 0; i < 14; i++) {
      await add.click();
    }
    await expect(rows).toHaveCount(15);
    await expect(note).toBeVisible();
    await expect(add).toHaveAttribute('aria-disabled', 'true');

    // Typing in the last row normally spawns another below it.
    await rows.last().locator('.first-name-input').fill('Pat');
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
    // Other specs add tournaments to event 1 as they run; past eight, rows collapse.
    test.skip(await card.evaluate((c) => c.classList.contains('tournaments-collapse')),
      'event 1 has more than eight tournaments in this run');
    const rows = card.locator('.new-participant-row');
    await rows.nth(0).locator('.first-name-input').fill('Quinn'); // spawns row 2
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
    await expect(options.nth(0)).toHaveText('*Unknown');
    await expect(options.nth(1)).toHaveText('*Unaffiliated');
    await expect(options.filter({ hasText: SCHOOLS[0].shortName })).toHaveCount(1);
    await expect(options.filter({ hasText: SCHOOLS[1].shortName })).toHaveCount(1);
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

test.describe('event roster entry from the keyboard', () => {
  test('Tab runs through names, school, chips and remove, then into the next row', async ({ page }) => {
    const card = await openEntryCard(page);
    const rows = card.locator('.new-participant-row');
    const row = rows.nth(0);
    const names = row.locator('.name-inputs input');
    const chips = row.locator('.tournament-box');

    await names.nth(0).focus();
    await page.keyboard.type('Kay');
    await page.keyboard.press('Tab');
    await expect(names.nth(1)).toBeFocused();
    await page.keyboard.type('Keys');
    await page.keyboard.press('Tab');
    await expect(row.locator('.school-input')).toBeFocused();

    await page.keyboard.press('Tab');
    await expect(chips.first()).toBeFocused();
    const box = chips.first().locator('input');
    const wasChecked = await box.isChecked();
    await page.keyboard.press('Space');
    expect(await box.isChecked()).toBe(!wasChecked);
    await expect(chips.first()).toHaveAttribute('aria-checked', String(!wasChecked));

    const count = await chips.count();
    for (let i = 1; i < count; i++) {
      await page.keyboard.press('Tab');
      await expect(chips.nth(i)).toBeFocused();
    }
    await page.keyboard.press('Tab');
    await expect(row.locator('.remove-row')).toBeFocused();
    await page.keyboard.press('Tab');
    await expect(rows.nth(1).locator('.name-inputs input').first()).toBeFocused();
  });

  test('Tab past a blank last row reaches the buttons below the rows', async ({ page }) => {
    const card = await openEntryCard(page);
    const rows = card.locator('.new-participant-row');
    await rows.nth(0).locator('.name-inputs input').first().fill('Lee');
    await expect(rows).toHaveCount(2);

    await rows.nth(1).locator('.name-inputs input').first().focus();
    for (let i = 0; i < 40 && await rows.nth(1).evaluate((r) => r.contains(document.activeElement)); i++) {
      await page.keyboard.press('Tab');
    }
    await expect(card.locator('#addParticipantRow')).toBeFocused();
    await expect(rows).toHaveCount(2);

    // Add Row puts the cursor in the row it adds.
    await page.keyboard.press('Enter');
    await expect(rows).toHaveCount(3);
    await expect(rows.nth(2).locator('.name-inputs input').first()).toBeFocused();
  });

  test('Enter on a chip moves to the next row', async ({ page }) => {
    const card = await openEntryCard(page);
    const rows = card.locator('.new-participant-row');
    await rows.nth(0).locator('.name-inputs input').first().fill('Mo');
    const chip = rows.nth(0).locator('.tournament-box').first();
    await chip.focus();
    await expect(chip).toBeFocused();
    await page.keyboard.press('Enter');
    await expect(rows.nth(1).locator('.name-inputs input').first()).toBeFocused();
  });

  test('typing part of a school and pressing Tab picks the highlighted match', async ({ page }) => {
    const card = await openEntryCard(page);
    const row = card.locator('.new-participant-row').first();
    const school = row.locator('.school-input');

    // "te" is also inside "*Unaffiliated", but a school starting with it leads.
    await school.focus();
    await page.keyboard.type('te');
    await expect(row.locator('.school-combobox .autocomplete-item.is-active')).toHaveText(SCHOOLS[0].shortName);
    await page.keyboard.press('Tab');
    await expect(school).toHaveValue(SCHOOLS[0].shortName);
    await expect(row.locator('.school-id')).toHaveValue(String(SCHOOLS[0].schoolID));

    // Tabbing back through a picked school keeps it.
    await school.focus();
    await page.keyboard.press('Tab');
    await expect(row.locator('.school-id')).toHaveValue(String(SCHOOLS[0].schoolID));
  });

  test('Down in an empty school box lists every school, as the caret does', async ({ page }) => {
    const card = await openEntryCard(page);
    const row = card.locator('.new-participant-row').first();
    const options = row.locator('.school-combobox [role=option]');
    await row.locator('.school-toggle').click();
    await expect(options.first()).toBeVisible();
    const every = await options.count();
    await page.keyboard.press('Escape');
    await expect(options).toHaveCount(0);

    await page.keyboard.press('ArrowDown');
    await expect(options).toHaveCount(every);
    await expect(options.nth(0)).toHaveClass(/is-active/);
    await page.keyboard.press('ArrowDown');
    await page.keyboard.press('Enter');
    await expect(row.locator('.school-input')).toHaveValue('*Unaffiliated');
    await expect(row.locator('.school-id')).toHaveValue('2');
  });

  test('Tab takes an arrowed-to fighter but never guesses one', async ({ page }) => {
    const card = await openEntryCard(page);
    const rows = card.locator('.new-participant-row');
    const ivo = SAME_NAME_FIGHTERS[0]; // never entered, unlike Greta in the add test

    await rows.nth(0).locator('.first-name-input').fill(ivo.firstName);
    await expect(rows.nth(0).locator('.name-inputs [role=option]').first()).toBeVisible();
    await page.keyboard.press('Tab');
    await expect(rows.nth(0).locator('.system-roster-id')).toHaveValue('0');
    await expect(rows.nth(0).locator('.last-name-input')).toHaveValue('');

    await rows.nth(1).locator('.first-name-input').fill(ivo.firstName);
    await expect(rows.nth(1).locator('.name-inputs [role=option]').first()).toBeVisible();
    await page.keyboard.press('ArrowDown');
    await page.keyboard.press('Tab');
    await expect(rows.nth(1).locator('.system-roster-id')).not.toHaveValue('0');
    await expect(rows.nth(1).locator('.last-name-input')).toHaveValue(ivo.lastName);
  });

  test('a fighter search that lands after Tab leaves no list behind', async ({ page }) => {
    const card = await openEntryCard(page);
    const row = card.locator('.new-participant-row').first();
    await row.locator('.first-name-input').focus();

    const search = page.waitForResponse(/mode=fighterSearch/);
    await page.keyboard.type('Gre');
    await page.keyboard.press('Tab');
    await search;
    // Give the page's handler a moment to run after the response arrives.
    await page.waitForTimeout(300);
    await expect(card.locator('.autocomplete-list:visible')).toHaveCount(0);
  });

  test('removing a row from the keyboard keeps focus in the rows', async ({ page }) => {
    const card = await openEntryCard(page);
    const rows = card.locator('.new-participant-row');
    await rows.nth(0).locator('.first-name-input').fill('Ned');
    await rows.nth(1).locator('.first-name-input').fill('Ola');
    await expect(rows).toHaveCount(3);

    await rows.nth(0).locator('.remove-row').focus();
    await page.keyboard.press('Enter');
    await expect(rows).toHaveCount(2);
    await expect(rows.nth(0).locator('.first-name-input')).toHaveValue('Ola');
    await expect(rows.nth(0).locator('.remove-row')).toBeFocused();
  });
});
