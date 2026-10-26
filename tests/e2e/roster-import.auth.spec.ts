import { test, expect } from './helpers/fixtures';
import { createTournament } from './helpers/tournament-actions';
import { IMPORT_LATE_ENTRY, ORGANIZER_PASSWORD } from './helpers/test-data';
import path from 'path';
import fs from 'fs';
import type { Browser, Page } from '@playwright/test';

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
  // The table shows while the footer's scripts still load; ImportReview.init
  // attaches the page's handlers on DOMContentLoaded
  await page.waitForLoadState('domcontentloaded');
}

const rowFor = (page: Page, text: string) => page.locator('#importPreview tr.import-row', { hasText: text });
const importButton = (page: Page) => page.locator("button[name='formName'][value='confirmParticipantsImport']");
async function tick(page: Page, text: string) { await rowFor(page, text).locator('input.import-check').check(); }
async function confirmAndImport(page: Page) {
  await page.locator('#importConfirmed').check();
  await importButton(page).click();
}
const option = (page: Page, row: string, label: string) =>
  rowFor(page, row).locator('label', { hasText: label }).locator('input.import-choice');

const STALE = 'This import changed since the page was loaded. Review it again before importing.';
// Another upload in the same session, as from a second tab
const uploadElsewhere = (page: Page, file: CsvPayload) =>
  page.request.post('/participantsImport.php', { multipart: { formName: 'uploadParticipantsCsv', participantsCsv: file } });
// A browser of its own, for tests that log in or out or switch events. Inside a
// test, browser.newContext() would otherwise share the worker's login.
const newSession = (browser: Browser, baseURL?: string) =>
  browser.newContext({ baseURL, storageState: { cookies: [], origins: [] } });
async function logIn(page: Page, data: Record<string, string>) {
  await page.goto('/infoWelcome.php');
  await page.request.post('/infoWelcome.php', { form: { formName: 'logUserIn', ...data } });
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
    await expect(jarvis).toContainText('School not found: "Nowhere Academy"');
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

  test('clean rows and rows that need a look are listed apart', async ({ page }) => {
    await upload(page, FIXTURE);
    await expect(page.locator('#importReady h5')).toHaveText('Ready to import (3)');
    await expect(page.locator('#importReview h5')).toHaveText('Needs a look (1)');
    await expect(page.locator('#importReview tr.import-row')).toContainText(['Jarvis']);
  });

  test('a section with no rows is left out', async ({ page }) => {
    await upload(page, csv('clean.csv', 'First Name,Last Name,School', 'Abe,Abernathy,Test School'));
    await expect(page.locator('#importReview')).toHaveCount(0);
    await expect(page.locator('#importReady tr.import-row')).toHaveCount(1);
  });

  test('a new fighter named in capitals or lowercase is flagged, a known one is not', async ({ page }) => {
    await upload(page, csv('casing.csv', 'First Name,Last Name,School',
      'XAVIER,YATES,Test School', 'yolanda,zimmer,Test School', 'rowan,fairweather,Other Club'));
    await expect(rowFor(page, 'YATES')).toContainText('Name is in all capitals');
    await expect(rowFor(page, 'zimmer')).toContainText('Name is in all lowercase');
    await expect(page.locator('#importReady tr.import-row', { hasText: 'fairweather' })).toHaveCount(1);
    await expect(rowFor(page, 'fairweather')).not.toContainText('Name is in all');
  });

  test('the same person twice in the file is flagged on each line', async ({ page }) => {
    await upload(page, csv('twice.csv', 'First Name,Last Name,School',
      'Nell,Norgaard,Test School', 'Otto,Ostrander,Test School', 'nell,NORGAARD,Test School'));
    const flagged = page.locator('#importReview tr.import-row');
    await expect(flagged.nth(0)).toContainText('Also on line 4 of the file');
    await expect(flagged.nth(1)).toContainText('Also on line 2 of the file');
    await expect(page.locator('#importReady tr.import-row', { hasText: 'Ostrander' })).toHaveCount(1);
  });

  test('an existing fighter whose school is not recognised keeps their Scorecard school', async ({ page }) => {
    await upload(page, csv('vance.csv', 'First Name,Last Name,School', 'Tobias,Vance,Iron Gat'));
    const row = page.locator('#importReview tr.import-row', { hasText: 'Vance' });
    await expect(row.locator('td.import-school')).toContainText('Test School');
    await expect(row).toContainText('School not found: "Iron Gat"');
    await expect(row.locator('td.import-status')).toHaveText('In Scorecard');
    await tick(page, 'Vance');
    await confirmAndImport(page);
    await expect(page.getByText('1 participants imported.')).toBeVisible();
    await expect(page.getByText('school choice')).toHaveCount(0);
    await expect(page.locator('#eventRosterForm tr.roster-row', { hasText: 'Vance' })).toContainText('Test School');
  });

  test('an existing fighter listed at another school is asked about after importing', async ({ page }) => {
    await upload(page, csv('quintero.csv', 'First Name,Last Name,School', 'Ursula,Quintero,Other Club'));
    await expect(page.locator('#importReview tr.import-row', { hasText: 'Quintero' }))
      .toContainText("Scorecard has them at Test School; you'll be asked which to use after importing");
    await tick(page, 'Quintero');
    await confirmAndImport(page);
    await expect(page.getByText('1 need a school choice before they are added.')).toBeVisible();
    await expect(page.getByText('already exists in the system')).toBeVisible();
  });

  test('a near-identical name offers the existing fighter', async ({ page }) => {
    await upload(page, csv('typo.csv', 'First Name,Last Name,School', 'Rowan,Fairwether,Other Club'));
    const row = rowFor(page, 'Fairwether');
    await expect(row).toContainText('Similar fighter already in Scorecard');
    await expect(option(page, 'Fairwether', 'Add as a new fighter')).toHaveCount(1);
    await expect(row.locator('input.import-check')).toBeDisabled();
    await option(page, 'Fairwether', 'Use Rowan Fairweather (Other Club)').check();
    await expect(row.locator('input.import-check')).toBeEnabled();
    await expect(row.locator('td.import-status')).toHaveText('In Scorecard');
  });

  test('swapped names, punctuation, case and accents still match', async ({ page }) => {
    await upload(page, csv('variants.csv', 'First Name,Last Name,School',
      'Fairweather,Rowan,Other Club', 'Siobhan,ORourke,Test School', 'ZOE,marchetti,Test School'));
    await expect(option(page, 'Fairweather', 'Use Rowan Fairweather')).toHaveCount(1);
    await expect(option(page, 'ORourke', "Use Siobhan O'Rourke")).toHaveCount(1);
    await expect(option(page, 'marchetti', 'Use Zoë Marchetti')).toHaveCount(1);
  });

  test('two typos still match but a different surname does not', async ({ page }) => {
    await upload(page, csv('distance.csv', 'First Name,Last Name,School', 'Rowen,Fairwether,Other Club', 'Rowan,Fairfax,Other Club'));
    await expect(option(page, 'Rowen', 'Use Rowan Fairweather')).toHaveCount(1);
    const fairfax = rowFor(page, 'Fairfax');
    await expect(fairfax).not.toContainText('Similar fighter');
    await expect(fairfax.locator('td.import-status')).toHaveText('New to Scorecard');
  });

  test('a typo in a short name still matches', async ({ page }) => {
    await upload(page, csv('ixo.csv', 'First Name,Last Name,School', 'Ixo,Ibsem,'));
    await expect(option(page, 'Ibsem', 'Use Ivo Ibsen (Test School)')).toHaveCount(1);
    await expect(option(page, 'Ibsem', 'Use Ivo Ibsen (Other Club)')).toHaveCount(1);
  });

  test('any first name close enough matches when the surname is the same', async ({ page }) => {
    await upload(page, csv('bowam.csv', 'First Name,Last Name,School', 'Bowam,Fairweather,Other Club'));
    await expect(option(page, 'Bowam', 'Use Rowan Fairweather')).toHaveCount(1);
  });

  test('the same holds with first and last names swapped', async ({ page }) => {
    await upload(page, csv('bowam-swapped.csv', 'First Name,Last Name,School', 'Fairweather,Bowam,Other Club'));
    await expect(option(page, 'Bowam', 'Use Rowan Fairweather')).toHaveCount(1);
  });

  test('a typo in a two-letter name still matches', async ({ page }) => {
    await upload(page, csv('wu.csv', 'First Name,Last Name,School', 'Ottoline,Wu,Test School'));
    await tick(page, 'Ottoline');
    await confirmAndImport(page);
    await expect(page.getByText('1 participants imported.')).toBeVisible();
    await upload(page, csv('xu.csv', 'First Name,Last Name,School', 'Ottoline,Xu,Test School'));
    await expect(option(page, 'Ottoline', 'Use Ottoline Wu')).toHaveCount(1);
  });

  test('a name two fighters share asks which one, with no new-fighter option', async ({ page }) => {
    await upload(page, csv('ivo.csv', 'First Name,Last Name,School', 'Ivo,Ibsen,'));
    const row = rowFor(page, 'Ibsen');
    await expect(row).toContainText('More than one fighter in Scorecard has this name');
    await expect(option(page, 'Ibsen', 'Use Ivo Ibsen (Test School)')).toHaveCount(1);
    await expect(option(page, 'Ibsen', 'Use Ivo Ibsen (Other Club)')).toHaveCount(1);
    await expect(option(page, 'Ibsen', 'Add as a new fighter')).toHaveCount(0);
    await expect(row.locator('input.import-check')).toBeDisabled();
  });

  // Before the next test enters Rowan Fairweather, after which his radio leaves nothing to tick
  test('a choice that is not one of the offered fighters is ignored', async ({ page }) => {
    // No school, so a forged choice that got through would import her outright
    await upload(page, csv('forged.csv', 'First Name,Last Name,School', 'Rowan,Fairwethr,'));
    const forged = option(page, 'Fairwethr', 'Use Rowan Fairweather');
    // Zoë Marchetti's systemRosterID: not in the event, and no test enters her
    await forged.evaluate((radio: HTMLInputElement) => { radio.value = '13'; });
    await forged.check();
    await tick(page, 'Fairwethr');
    await confirmAndImport(page);
    await expect(page.getByText('0 participants imported.')).toBeVisible();
    await expect(page.locator('#eventRosterForm tr.roster-row', { hasText: 'Marchetti' })).toHaveCount(0);
  });

  test('choosing a similar fighter imports them instead of a new one', async ({ page }) => {
    await upload(page, csv('choose.csv', 'First Name,Last Name,School', 'Rowan,Fairwether,Other Club'));
    await option(page, 'Fairwether', 'Use Rowan Fairweather').check();
    await tick(page, 'Fairwether');
    await confirmAndImport(page);
    await expect(page.getByText('1 participants imported.')).toBeVisible();
    const roster = page.locator('#eventRosterForm');
    await expect(roster.locator('tr.roster-row', { hasText: 'Fairweather' })).toContainText('Other Club');
    await expect(roster.locator('tr.roster-row', { hasText: 'Fairwether' })).toHaveCount(0);
  });

  test('a similar fighter already in the event only gets the new tournaments', async ({ page }) => {
    await createTournament(page, { weapon: 'Dagger' }); // distinct from other specs' weapons
    await upload(page, csv('applegait.csv', 'First Name,Last Name,School,Tournaments', 'Alice,Applegait,Test School,Dagger'));
    await option(page, 'Applegait', 'Use Alice Applegate (Test School) (already in this event)').check();
    await expect(rowFor(page, 'Applegait').locator('td.import-status')).toHaveText('Already in this event, adds tournaments');
    await tick(page, 'Applegait');
    await confirmAndImport(page);
    await expect(page.getByText('Added tournaments for 1 already in the event.')).toBeVisible();
    const roster = page.locator('#eventRosterForm');
    await expect(roster.locator('tr.roster-row', { hasText: 'Applegate' }).locator('.tournament-box', { hasText: 'Dagger' })).toHaveCount(1);
    await expect(roster.locator('tr.roster-row', { hasText: 'Applegait' })).toHaveCount(0);
  });

  // Rowan Fairweather is in the event by now, with no tournaments
  test('a remembered choice with nothing to add can still be changed', async ({ page }) => {
    await upload(page, csv('rechoose.csv', 'First Name,Last Name,School', 'Rowan,Fairwaether,Other Club'));
    await option(page, 'Fairwaether', 'Use Rowan Fairweather').check();
    const box = rowFor(page, 'Fairwaether').locator('input.import-check');
    await expect(box).toBeDisabled();
    await importButton(page).evaluate((b: HTMLButtonElement) => { b.disabled = false; });
    await importButton(page).click(); // refused without the confirmation, so the page comes back
    await expect(page.getByText('before importing')).toBeVisible();
    await expect(option(page, 'Fairwaether', 'Use Rowan Fairweather')).toBeChecked();
    await expect(box).toBeDisabled();
    await option(page, 'Fairwaether', 'Add as a new fighter').check();
    await expect(box).toBeEnabled();
  });

  const count = (page: Page, key: string) => page.locator(`#importSummary [data-count='${key}'] .count`);

  test('the summary counts what will be written as rows are ticked', async ({ page }) => {
    await createTournament(page, { weapon: 'Grappling' }); // distinct from other specs' weapons
    await upload(page, csv('summary.csv', 'First Name,Last Name,School,Tournaments',
      "Siobhan,O'Rourke,Test School,", 'Pia,Pemberton,Test School,', 'Quinn,Quarles,,',
      'Alice,Applegate,Test School,Grappling', 'Vivian,Voss,Other Club,'));
    await expect(page.locator('#importSummaryHeading')).toHaveText("You're importing 0 of 5 rows");
    await expect(count(page, 'leftOut')).toHaveText('5');
    for (const name of ["O'Rourke", 'Pemberton', 'Quarles', 'Applegate', 'Voss']) await tick(page, name);
    await expect(page.locator('#importSummaryHeading')).toHaveText("You're importing 5 of 5 rows");
    for (const [key, n] of [['known', '1'], ['new', '2'], ['entered', '1'], ['noSchool', '1'], ['schoolPrompt', '1'], ['leftOut', '0']]) {
      await expect(count(page, key)).toHaveText(n);
    }
    await expect(page.locator('#importNewNames')).toHaveText('Pia Pemberton, Quinn Quarles');
    await rowFor(page, 'Pemberton').locator('input.import-check').uncheck();
    await expect(page.locator('#importSummaryHeading')).toHaveText("You're importing 4 of 5 rows");
    await expect(count(page, 'new')).toHaveText('1');
    await expect(page.locator('#importNewNames')).toHaveText('Quinn Quarles');
  });

  test('switching between a similar fighter and a new one moves the row in the summary', async ({ page }) => {
    await upload(page, csv('switch.csv', 'First Name,Last Name,School', 'Siobhan,ORourke,Test School'));
    await option(page, 'ORourke', "Use Siobhan O'Rourke").check();
    await tick(page, 'ORourke');
    await expect(count(page, 'known')).toHaveText('1');
    await expect(count(page, 'new')).toHaveText('0');
    await option(page, 'ORourke', 'Add as a new fighter').check();
    await expect(count(page, 'known')).toHaveText('0');
    await expect(count(page, 'new')).toHaveText('1');
    await expect(page.locator('#importNewNames')).toHaveText('Siobhan ORourke');
    await expect(rowFor(page, 'ORourke').locator('td.import-status')).toHaveText('New to Scorecard');
  });

  test('names are shown exactly as written, never as markup', async ({ page }) => {
    await upload(page, csv('markup.csv', 'First Name,Last Name,School', 'Dara,"O\'Neil <b>Bold</b>",Test School'));
    await expect(rowFor(page, 'Dara')).toContainText("O'Neil <b>Bold</b>");
    await expect(page.locator('#importPreview b', { hasText: 'Bold' })).toHaveCount(0);
    await tick(page, 'Dara');
    await expect(page.locator('#importNewNames')).toHaveText("Dara O'Neil <b>Bold</b>");
    // A file with only clean rows: no Needs a look section, and the summary still counts
    await expect(page.locator('#importReview')).toHaveCount(0);
    await expect(page.locator('#importSummaryHeading')).toHaveText("You're importing 1 of 1 rows");
  });

  const editor = (page: Page) => page.locator('tr.roster-editor');

  test('clicking a row opens the editor with its values', async ({ page }) => {
    await upload(page, csv('open.csv', 'First Name,Last Name,School,Tournaments', "Siobhan,O'Rourk,Test School,Longsword"));
    await rowFor(page, "O'Rourk").locator('.import-name-text').click();
    await expect(editor(page).locator('.edit-first-name')).toHaveValue('Siobhan');
    await expect(editor(page).locator('.edit-last-name')).toHaveValue("O'Rourk");
    await expect(editor(page).locator('.edit-school-input')).toHaveValue('Test School');
    await expect(editor(page).locator('.edit-tournaments .tournament-box.is-selected')).toHaveText(['Longsword']);
  });

  test('saving a fixed name re-checks the row in place and ticks it', async ({ page }) => {
    await upload(page, csv('fix.csv', 'First Name,Last Name,School,Tournaments', "Siobhan,O'Rourk,Test School,Longsword"));
    await rowFor(page, "O'Rourk").locator('.import-name-text').click();
    await editor(page).locator('.edit-last-name').fill("O'Rourke");
    await editor(page).locator('.roster-save').click();
    const row = page.locator('#importReview tr#importRow0');
    await expect(row).not.toContainText('Similar fighter');
    await expect(row.locator('td.import-status')).toHaveText('In Scorecard');
    await expect(row.locator('.import-fixed')).toHaveText("Fixed: was Siobhan O'Rourk");
    await expect(row.locator('input.import-check')).toBeChecked();
  });

  test('a row that still needs a choice stays unticked after saving', async ({ page }) => {
    await upload(page, csv('still.csv', 'First Name,Last Name,School', 'Rowan,Fairwether,Other Club'));
    await rowFor(page, 'Fairwether').locator('.import-school-text').click();
    await editor(page).locator('.edit-school-input').fill('Test School');
    await editor(page).locator('.roster-save').click();
    const row = rowFor(page, 'Fairwether');
    await expect(row.locator('.import-fixed')).toHaveText('Fixed: school was Other Club');
    await expect(row.locator('input.import-check')).toBeDisabled();
    await expect(row.locator('input.import-check')).not.toBeChecked();
  });

  test('picking a school fixes an unrecognised one', async ({ page }) => {
    await upload(page, csv('school.csv', 'First Name,Last Name,School', 'Yara,Yeoman,Nowhere Academy'));
    await rowFor(page, 'Yeoman').locator('.import-school-text').click();
    await editor(page).locator('.edit-school-input').fill('Other Club');
    await editor(page).locator('.roster-save').click();
    const row = rowFor(page, 'Yeoman');
    await expect(row.locator('td.import-school')).toContainText('Other Club');
    await expect(row).not.toContainText('School not found');
    await expect(row.locator('.import-fixed')).toHaveText('Fixed: school was *Unknown');
    await expect(row.locator('input.import-check')).toBeChecked();
  });

  test('other rows keep their ticks across a save, but the final confirmation must be ticked again', async ({ page }) => {
    await upload(page, csv('keep.csv', 'First Name,Last Name,School', 'Cleo,Castellan,Test School', 'Yara,Yeoman,Nowhere Academy'));
    await tick(page, 'Castellan');
    await page.locator('#importConfirmed').check();
    await rowFor(page, 'Yeoman').locator('.import-school-text').click();
    await editor(page).locator('.edit-school-input').fill('Other Club');
    await editor(page).locator('.roster-save').click();
    await expect(rowFor(page, 'Castellan').locator('input.import-check')).toBeChecked();
    await expect(page.locator('#importConfirmed')).not.toBeChecked();
  });

  test('ignored tournaments stay flagged until an edit changes the tournaments', async ({ page }) => {
    await upload(page, csv('ignored.csv', 'First Name,Last Name,School,Tournaments', 'Kip,Kowalczyk,Test School,Longswordd'));
    const row = rowFor(page, 'Kowalczyk');
    await row.locator('.import-name-text').click();
    await editor(page).locator('.edit-first-name').fill('Kit');
    await editor(page).locator('.roster-save').click();
    await expect(row.locator('.import-fixed')).toHaveText('Fixed: was Kip Kowalczyk');
    await expect(row).toContainText('Ignored: Longswordd');

    await page.waitForLoadState('domcontentloaded'); // ImportReview.init has run
    await row.locator('.import-name-text').click();
    await editor(page).locator('.edit-tournaments .tournament-box', { hasText: /^\s*Longsword\s*$/ }).click();
    await editor(page).locator('.roster-save').click();
    await expect(row.locator('.import-fixed', { hasText: 'tournaments' })).toHaveText('Fixed: tournaments were none');
    await expect(row).not.toContainText('Ignored:');
    await expect(row.locator('.tournament-box')).toHaveText(['Longsword']);
  });

  test('Import is unavailable while a row is being edited', async ({ page }) => {
    await upload(page, csv('busy.csv', 'First Name,Last Name,School', 'Cleo,Castellan,Test School'));
    await tick(page, 'Castellan');
    await page.locator('#importConfirmed').check();
    await expect(importButton(page)).toBeEnabled();
    await rowFor(page, 'Castellan').locator('.import-name-text').click();
    await expect(importButton(page)).toBeDisabled();
    await editor(page).locator('.roster-cancel').click();
    await expect(editor(page)).toHaveCount(0);
    await expect(importButton(page)).toBeEnabled();
  });

  test('Enter in the editor saves the row and never imports', async ({ page }) => {
    await upload(page, csv('enter.csv', 'First Name,Last Name,School', 'Yara,Yeoman,Nowhere Academy'));
    await rowFor(page, 'Yeoman').locator('.import-name-text').click();
    await editor(page).locator('.edit-last-name').press('Enter');
    await expect(page).toHaveURL(/participantsImport\.php/);
    await expect(rowFor(page, 'Yeoman').locator('input.import-check')).toBeChecked();
  });

  test('edits survive a reload and Cancel discards the import', async ({ page }) => {
    await upload(page, csv('reload.csv', 'First Name,Last Name,School', 'Yara,Yeoman,Nowhere Academy'));
    await rowFor(page, 'Yeoman').locator('.import-school-text').click();
    await editor(page).locator('.edit-school-input').fill('Other Club');
    await editor(page).locator('.roster-save').click();
    await page.reload();
    await expect(rowFor(page, 'Yeoman').locator('.import-fixed')).toHaveText('Fixed: school was *Unknown');
    await page.locator("button[name='formName'][value='cancelParticipantsImport']").click();
    await expect(page.locator("input[name='participantsCsv']")).toBeVisible();
  });

  test('the server refuses invalid edits and keeps the row', async ({ page }) => {
    await upload(page, csv('refuse.csv', 'First Name,Last Name,School', 'Cleo,Castellan,Test School'));
    const importID = await page.locator("#importForm input[name='importID']").inputValue();
    // maxRedirects 0: following the redirect would render, and so use up, the alert
    const save = (edit: Record<string, string>) => page.request.post('/participantsImport.php',
      { maxRedirects: 0, form: { formName: 'saveImportRow', importID, 'importEdit[index]': '0', 'importEdit[firstName]': 'Cleo',
                'importEdit[lastName]': 'Castellan', 'importEdit[schoolID]': '3', ...edit } });
    for (const [edit, message] of [
      [{ 'importEdit[firstName]': ' ', 'importEdit[lastName]': '' }, 'A participant needs a first or last name.'],
      [{ 'importEdit[schoolID]': '999999' }, "That school isn't in Scorecard."],
      [{ 'importEdit[tournamentIDs][999999]': '999999' }, "That tournament isn't in this event."],
    ] as const) {
      await save(edit);
      await page.goto('/participantsImport.php');
      await expect(page.getByText(message)).toBeVisible();
      await expect(rowFor(page, 'Castellan').locator('.import-fixed')).toHaveCount(0);
      await expect(rowFor(page, 'Castellan').locator('td.import-school')).toContainText('Test School');
    }
  });

  test('a page left open across another upload is refused on Import', async ({ page }) => {
    await upload(page, csv('stale-a.csv', 'First Name,Last Name,School', 'Ada,Achterberg,Test School'));
    await tick(page, 'Achterberg');
    await uploadElsewhere(page, csv('stale-b.csv', 'First Name,Last Name,School', 'Bram,Brandvold,Test School'));
    await confirmAndImport(page);
    await expect(page.getByText(STALE)).toBeVisible();
    await expect(rowFor(page, 'Brandvold').locator('input.import-check')).not.toBeChecked();
    await page.goto('/participantsEvent.php');
    for (const name of ['Achterberg', 'Brandvold']) {
      await expect(page.locator('#eventRosterForm tr.roster-row', { hasText: name })).toHaveCount(0);
    }
  });

  test('a page left open across another upload is refused on Save', async ({ page }) => {
    await upload(page, csv('stale-c.csv', 'First Name,Last Name,School', 'Cato,Castiglione,Test School'));
    await uploadElsewhere(page, csv('stale-d.csv', 'First Name,Last Name,School', 'Dov,Dragomir,Test School'));
    await rowFor(page, 'Castiglione').locator('.import-name-text').click();
    await editor(page).locator('.edit-last-name').fill('Castiglioni');
    await editor(page).locator('.roster-save').click();
    await expect(page.getByText(STALE)).toBeVisible();
    await expect(rowFor(page, 'Dov Dragomir').locator('.import-fixed')).toHaveCount(0);
    await expect(rowFor(page, 'Castiglioni')).toHaveCount(0);
  });

  test('an import belongs to the event it was uploaded in', async ({ browser, baseURL }) => {
    // The admin user switches events without being logged out, which an organizer can't
    const context = await newSession(browser, baseURL);
    const page = await context.newPage();
    await logIn(page, { 'logInData[type]': 'logInUser', 'logInData[userName]': 'admin', 'logInData[password]': 'any' });
    await page.goto('/participantsImport.php?e=1');
    await upload(page, csv('event.csv', 'First Name,Last Name,School,Tournaments', 'Edda,Eikeland,Test School,Longsword'));
    await tick(page, 'Eikeland');
    // Another tab switches the session to event 3, then this page imports
    const other = await context.newPage();
    await other.goto('/infoSummary.php?e=3');
    await confirmAndImport(page);
    await expect(page.getByText(STALE)).toBeVisible();
    // The import page drops an import from another event
    await expect(page.locator("input[name='participantsCsv']")).toBeVisible();
    await page.goto('/participantsImport.php?e=1');
    await expect(page.locator("input[name='participantsCsv']")).toBeVisible();
    for (const eventID of [1, 3]) {
      await page.goto(`/participantsEvent.php?e=${eventID}`);
      await expect(page.locator('#eventRosterForm tr.roster-row', { hasText: 'Eikeland' })).toHaveCount(0);
    }
    await context.close();
  });

  test('logging out discards a pending import', async ({ browser, baseURL }) => {
    const context = await newSession(browser, baseURL);
    const page = await context.newPage();
    const organizer = { 'logInData[type]': 'logInOrganizer', 'logInData[eventID]': '1', 'logInData[password]': ORGANIZER_PASSWORD };
    await logIn(page, organizer);
    await upload(page, csv('logout.csv', 'First Name,Last Name,School', 'Fenna,Falkenrath,Test School'));
    await page.request.post('/infoWelcome.php', { form: { formName: 'logUserOut' } });
    await logIn(page, organizer);
    await page.goto('/participantsImport.php');
    await expect(page.locator("input[name='participantsCsv']")).toBeVisible();
    await expect(page.locator('#importPreview')).toHaveCount(0);
    await context.close();
  });

  test('a fighter chosen before the page script runs is applied when it does', async ({ page }) => {
    await page.goto('/participantsImport.php');
    const cancel = page.locator("button[name='formName'][value='cancelParticipantsImport']");
    if (await cancel.count()) await cancel.click();
    // Holds back the footer's CDN jquery-ui, so ImportReview.init runs well after the choice
    await page.route('**/jquery-ui.js', async (route) => {
      await new Promise((r) => setTimeout(r, 2000));
      await route.continue();
    });
    await page.locator("input[name='participantsCsv']").setInputFiles(
      csv('early.csv', 'First Name,Last Name,School', 'Zoe,Marchetti,Test School'));
    await page.locator("button[name='formName'][value='uploadParticipantsCsv']").click();
    await option(page, 'Marchetti', 'Use Zoë Marchetti').check();
    await page.waitForLoadState('domcontentloaded');
    const row = rowFor(page, 'Marchetti');
    await expect(row.locator('input.import-check')).toBeEnabled();
    await expect(row.locator('td.import-status')).toHaveText('In Scorecard');
  });
});
