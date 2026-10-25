import { test, expect } from './helpers/fixtures';
import { createTournament } from './helpers/tournament-actions';
import { IMPORT_LATE_ENTRY } from './helpers/test-data';
import path from 'path';
import fs from 'fs';
import type { Page } from '@playwright/test';

/**
 * CSV import of event participants (participantsImport.php). Upload parks the
 * parsed rows in the session and re-renders as a confirmation table; Import
 * feeds them through addEventParticipants() and lands on the roster.
 */
const FIXTURE = path.resolve(__dirname, '../fixtures/participants-import.csv');
const NO_HEADER = path.resolve(__dirname, '../fixtures/participants-import-noheader.csv');

type CsvPayload = { name: string; mimeType: string; buffer: Buffer };

function csv(name: string, ...lines: string[]): CsvPayload {
  return { name, mimeType: 'text/csv', buffer: Buffer.from(lines.join('\n') + '\n') };
}

async function upload(page: Page, file: string | CsvPayload) {
  await page.goto('/participantsImport.php');
  // A pending confirmation persists in the session; clear it so the upload form shows.
  const cancel = page.locator("button[name='formName'][value='cancelParticipantsImport']");
  if (await cancel.count()) await cancel.click();
  await page.locator("input[name='participantsCsv']").setInputFiles(file);
  await page.locator("button[name='formName'][value='uploadParticipantsCsv']").click();
  await expect(page.locator('#importPreview')).toBeVisible();
}

const rowFor = (page: Page, text: string) => page.locator('#importPreview tr.import-row', { hasText: text });
const importButton = (page: Page) => page.locator("button[name='formName'][value='confirmParticipantsImport']");
async function tick(page: Page, text: string) { await rowFor(page, text).locator('input.import-check').check(); }
async function confirmAndImport(page: Page) {
  await page.locator('#importConfirmed').check();
  await importButton(page).click();
}

test.describe('participant CSV import', () => {
  test('roster page links to the import page', async ({ page }) => {
    await page.goto('/participantsEvent.php');
    await expect(page.getByRole('link', { name: 'Import CSV' })).toHaveAttribute('href', 'participantsImport.php');
  });

  test('confirmation shows each row with its match status and warnings', async ({ page }) => {
    await upload(page, FIXTURE);
    const rows = page.locator('#importPreview tr.import-row');
    await expect(rows).toHaveCount(4);

    const ingram = rows.filter({ hasText: 'Ingram' });
    await expect(ingram).toContainText('New to Scorecard');
    await expect(ingram).toContainText('Other Club');
    await expect(ingram.locator('.tournament-box')).toHaveText(['Longsword']);

    const haldane = rows.filter({ hasText: 'Haldane' });
    await expect(haldane).toContainText('In Scorecard');
    await expect(haldane).not.toContainText('School not found'); // matched school, whatever its ID
    // Already entered, but not in Longsword yet, so the row still counts
    const applegate = rows.filter({ hasText: 'Applegate' });
    await expect(applegate).toContainText('Already in this event, adds tournaments');
    await expect(applegate.locator('.tournament-box')).toHaveText(['Longsword']);

    const jarvis = rows.filter({ hasText: 'Jarvis' });
    await expect(jarvis).toContainText('School not found');
    await expect(jarvis).toContainText('Ignored: Longswordd');

    await expect(importButton(page)).toHaveText(/Import 0 participants/);
    await expect(importButton(page)).toBeDisabled();
  });

  // Uses the fixture's rows before 'import adds the fighters to the roster' enters them
  test('every row starts unticked and Import waits for the final confirmation', async ({ page }) => {
    await upload(page, FIXTURE);
    const boxes = page.locator('input.import-check');
    await expect(boxes).toHaveCount(4);
    for (const box of await boxes.all()) await expect(box).not.toBeChecked();
    await expect(importButton(page)).toBeDisabled();
    await tick(page, 'Ingram');
    await expect(importButton(page)).toHaveText(/Import 1 participants/);
    await expect(importButton(page)).toBeDisabled();
    await page.locator('#importConfirmed').check();
    await expect(importButton(page)).toBeEnabled();
    await rowFor(page, 'Ingram').locator('input.import-check').uncheck();
    await expect(importButton(page)).toBeDisabled();
  });

  test('tournaments match by name in either display format', async ({ page }) => {
    await createTournament(page, { weapon: 'Glima', prefix: 'Novice' }); // distinct from other specs' weapons
    await upload(page, csv('formats.csv',
      'First Name,Last Name,Tournaments',
      'Lena,Lindqvist,Novice Glima',
      'Milo,Moreau,glima - novice',
    ));

    const rows = page.locator('#importPreview tr.import-row');
    for (const lastName of ['Lindqvist', 'Moreau']) {
      const row = rows.filter({ hasText: lastName });
      await expect(row.locator('.tournament-box')).toHaveCount(1);
      await expect(row).not.toContainText('Ignored');
    }
  });

  test('a name shared by two tournaments is ignored as ambiguous', async ({ page }) => {
    await createTournament(page, { weapon: 'Rotella' }); // distinct from other specs' weapons
    await createTournament(page, { weapon: 'Rotella' });
    await upload(page, csv('ambiguous.csv',
      'First Name,Last Name,Tournaments',
      'Nora,Nakamura,Rotella',
    ));

    const row = page.locator('#importPreview tr.import-row', { hasText: 'Nakamura' });
    await expect(row.locator('.tournament-box')).toHaveCount(0);
    await expect(row).toContainText('Ignored: Rotella (matches 2 tournaments)');
  });

  test('a file without a header row is read in fixed column order', async ({ page }) => {
    await upload(page, NO_HEADER);
    const rows = page.locator('#importPreview tr.import-row');
    await expect(rows).toHaveCount(1);
    await expect(rows.first()).toContainText('Kimura');
    await expect(rows.first()).toContainText('Other Club');
    await expect(page.locator('#importReadAs')).toContainText(
      'No header row, so columns were read in order: First Name, Last Name, School, Tournaments');
    await expect(page.locator('#importFirstLine')).toHaveText(fs.readFileSync(NO_HEADER, 'utf8').split(/\r?\n/)[0]);

    await page.locator("button[name='formName'][value='cancelParticipantsImport']").click();
    await expect(page.locator("input[name='participantsCsv']")).toBeVisible();
    await expect(page.locator('#importPreview')).toHaveCount(0);
  });

  test('a header row is shown mapped to the fields it filled', async ({ page }) => {
    await upload(page, csv('mapped.csv', 'Surname,First,Club,Events', 'Quill,Petra,Test School,'));
    const readAs = page.locator('#importReadAs');
    for (const pair of ['First Name ← First', 'Last Name ← Surname', 'School ← Club', 'Tournaments ← Events']) {
      await expect(readAs).toContainText(pair);
    }
    await expect(page.locator('#importFirstLine')).toHaveText('Quill,Petra,Test School,');
  });

  test('a field with no column in the file says so', async ({ page }) => {
    await upload(page, csv('names-only.csv', 'First Name,Last Name', 'Rhea,Rasmussen'));
    await expect(page.locator('#importReadAs')).toContainText('School ← not in file');
    await expect(page.locator('#importReadAs')).toContainText('Tournaments ← not in file');
  });

  test('import adds the fighters to the roster', async ({ page }) => {
    await upload(page, FIXTURE);
    for (const name of ['Ingram', 'Haldane', 'Applegate', 'Jarvis']) await tick(page, name);
    await confirmAndImport(page);
    await expect(page).toHaveURL(/participantsEvent\.php/);
    await expect(page.getByText('3 participants imported. Added tournaments for 1 already in the event.')).toBeVisible();

    const roster = page.locator('#eventRosterForm');
    const ingram = roster.locator('tr.roster-row', { hasText: 'Ingram' });
    await expect(ingram).toContainText('Other Club');
    await expect(ingram.locator('.roster-tournaments .tournament-box')).toHaveText(['Longsword']);
    await expect(roster.locator('tr.roster-row', { hasText: 'Haldane' })).toContainText('Test School');
    await expect(roster.locator('tr.roster-row', { hasText: 'Jarvis' })).toHaveCount(1);
    const applegate = roster.locator('tr.roster-row', { hasText: 'Applegate' });
    await expect(applegate).toHaveCount(1);
    // Other specs enter her in their own tournaments, so only look for Longsword
    await expect(applegate.locator('.roster-tournaments .tournament-box', { hasText: /^\s*Longsword\s*$/ })).toHaveCount(1);
  });

  test('only ticked rows are imported', async ({ page }) => {
    await upload(page, csv('partial.csv', 'First Name,Last Name,School', 'Una,Underhill,Test School', 'Vera,Vantongeren,Test School'));
    await tick(page, 'Underhill');
    await confirmAndImport(page);
    await expect(page.getByText('1 participants imported.')).toBeVisible();
    const roster = page.locator('#eventRosterForm');
    await expect(roster.locator('tr.roster-row', { hasText: 'Underhill' })).toHaveCount(1);
    await expect(roster.locator('tr.roster-row', { hasText: 'Vantongeren' })).toHaveCount(0);
  });

  test('the server refuses an import without the final confirmation', async ({ page }) => {
    await upload(page, csv('unconfirmed.csv', 'First Name,Last Name,School', 'Wes,Whitlock,Test School'));
    await tick(page, 'Whitlock');
    await importButton(page).evaluate((b: HTMLButtonElement) => { b.disabled = false; });
    await importButton(page).click();
    await expect(page).toHaveURL(/participantsImport\.php/);
    await expect(page.getByText('before importing')).toBeVisible();
    await expect(rowFor(page, 'Whitlock').locator('input.import-check')).toBeChecked();
    await page.goto('/participantsEvent.php');
    await expect(page.locator('#eventRosterForm tr.roster-row', { hasText: 'Whitlock' })).toHaveCount(0);
  });

  test('a fighter already in the event with nothing new is greyed out with no tick box', async ({ page }) => {
    await upload(page, csv('nothing.csv', 'First Name,Last Name,School', 'Alice,Applegate,Test School'));
    const row = rowFor(page, 'Applegate');
    await expect(row).toContainText('Already in this event, nothing to add');
    await expect(row).toHaveClass(/grey-text/);
    await expect(row.locator('input.import-check')).toHaveCount(0);

    // A forged tick on that row writes nothing
    await page.locator('#importForm').evaluate((form) => {
      const forged = document.createElement('input');
      Object.assign(forged, { type: 'hidden', name: 'importRows[0][checked]', value: '1' });
      form.appendChild(forged);
    });
    await page.locator('#importConfirmed').check();
    await importButton(page).evaluate((b: HTMLButtonElement) => { b.disabled = false; });
    await importButton(page).click();
    await expect(page.getByText('0 participants imported.')).toBeVisible();
  });

  test('a fighter entered by someone else after upload only gets the tournaments', async ({ page }) => {
    await upload(page, csv('late.csv', 'First Name,Last Name,School,Tournaments', 'Wendell,Ashby,Test School,Longsword'));
    await expect(rowFor(page, 'Ashby')).toContainText('In Scorecard');
    await page.request.post('/participantsEvent.php', { form: {
      formName: 'addEventParticipants',
      'newParticipants[1][systemRosterID]': String(IMPORT_LATE_ENTRY.systemRosterID),
      'newParticipants[1][schoolID]': String(IMPORT_LATE_ENTRY.schoolID),
    } });
    await tick(page, 'Ashby');
    await confirmAndImport(page);
    await expect(page.getByText('Added tournaments for 1 already in the event.')).toBeVisible();
    const ashby = page.locator('#eventRosterForm tr.roster-row', { hasText: 'Ashby' });
    await expect(ashby).toHaveCount(1);
    await expect(ashby.locator('.roster-tournaments .tournament-box', { hasText: /^\s*Longsword\s*$/ })).toHaveCount(1);
  });
});
