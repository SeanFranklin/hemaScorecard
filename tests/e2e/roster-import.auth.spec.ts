import { test, expect } from './helpers/fixtures';
import { createTournament } from './helpers/tournament-actions';
import path from 'path';
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
    await expect(ingram).toContainText('New fighter');
    await expect(ingram).toContainText('Other Club');
    await expect(ingram.locator('.tournament-box')).toHaveText(['Longsword']);

    const haldane = rows.filter({ hasText: 'Haldane' });
    await expect(haldane).toContainText('Known fighter');
    await expect(haldane).not.toContainText('School not found'); // matched school, whatever its ID
    // Already entered, but not in Longsword yet, so the row still counts
    const applegate = rows.filter({ hasText: 'Applegate' });
    await expect(applegate).toContainText('Already in this event');
    await expect(applegate).toContainText('Adds tournaments');
    await expect(applegate.locator('.tournament-box')).toHaveText(['Longsword']);

    const jarvis = rows.filter({ hasText: 'Jarvis' });
    await expect(jarvis).toContainText('School not found');
    await expect(jarvis).toContainText('Ignored: Longswordd');

    await expect(page.locator("button[name='formName'][value='confirmParticipantsImport']")).toHaveText(/Import 4 participants/);
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

    await page.locator("button[name='formName'][value='cancelParticipantsImport']").click();
    await expect(page.locator("input[name='participantsCsv']")).toBeVisible();
    await expect(page.locator('#importPreview')).toHaveCount(0);
  });

  test('import adds the fighters to the roster', async ({ page }) => {
    await upload(page, FIXTURE);
    await page.locator("button[name='formName'][value='confirmParticipantsImport']").click();
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
});
