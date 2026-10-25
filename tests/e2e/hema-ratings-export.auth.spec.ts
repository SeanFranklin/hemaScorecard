import fs from 'fs';
import { test, expect } from './helpers/fixtures';
import { FIGHTERS } from './helpers/test-data';
import {
  createTournament,
  addFightersToTournamentRoster,
  createPoolAndAssignFighters,
  scoreAllPoolMatches,
} from './helpers/tournament-actions';
import { MatchScript, pairKey } from './helpers/standings-calc';
import { readZip } from './helpers/zip';

/**
 * HEMA Ratings "Export All" on statsResultsDump.php: one zip holding
 * clubs.csv, fighters.csv and a results file per tournament.
 *
 * Two tournaments share a name (same weapon, default settings) and only the
 * second gets a scored match, so the test also proves each keeps its own
 * file in the zip rather than one overwriting the other.
 */

const WEAPON = 'Backsword'; // distinct from other specs' weapons

// Two seeded fighters -> a single pool match.
const EXPORT_FIGHTERS = FIGHTERS.slice(0, 2);

const MATCH_SCRIPT: MatchScript = new Map([
  [pairKey('Bowman', 'Applegate'), {
    exchanges: [{ scorer: 'Bowman', points: 3 }],
    winner: 'Bowman',
  }],
]);

test('Export All zips clubs, fighters and a results file per tournament', async ({ page }) => {
  test.setTimeout(120_000);

  await test.step('create two same-named tournaments, scoring a match in the second', async () => {
    await createTournament(page, { weapon: WEAPON });
    await createTournament(page, { weapon: WEAPON });
    await addFightersToTournamentRoster(page, EXPORT_FIGHTERS);
    await createPoolAndAssignFighters(page, EXPORT_FIGHTERS);
    await scoreAllPoolMatches(page, MATCH_SCRIPT, EXPORT_FIGHTERS);
  });

  await page.goto('/statsResultsDump.php');

  // The per-tournament export buttons show the generated tournament name.
  const tournamentName = (await page
    .locator("button[name='HemaRatingsExport']")
    .filter({ hasText: WEAPON })
    .first()
    .innerText()).trim();

  const files = await test.step('download the Export All zip', async () => {
    const downloadPromise = page.waitForEvent('download');
    await page.getByRole('button', { name: /Export All/ }).click();
    const download = await downloadPromise;
    expect(download.suggestedFilename()).toBe('export.zip');
    return readZip(fs.readFileSync(await download.path()));
  });

  expect([...files.keys()]).toEqual(expect.arrayContaining([
    'clubs.csv',
    'fighters.csv',
    `${tournamentName}.csv`,
    `${tournamentName} (2).csv`,
  ]));

  await test.step('clubs.csv has its header and fighters.csv lists the event roster', async () => {
    expect(files.get('clubs.csv')?.split('\n')[0])
      .toBe('"Club Name",Country,State,City,"Website URL","Facebook URL","Parent club"');

    // Name, Club, Nationality, Gender, HEMA Ratings ID; no header row. The
    // roster query has no ORDER BY, so compare sorted.
    expect(files.get('fighters.csv')?.trim().split('\n').sort()).toEqual(
      FIGHTERS.map((f) => `${f.firstName} ${f.lastName},Test Fencing School,US,,`).sort(),
    );
  });

  await test.step('each same-named tournament has its own results file', async () => {
    const results = [files.get(`${tournamentName}.csv`), files.get(`${tournamentName} (2).csv`)];

    // Only the second tournament played a match; the first's file is empty.
    expect(results.filter((csv) => csv?.startsWith('Brett Bowman,Alice Applegate,Win,Loss,'))).toHaveLength(1);
    expect(results.filter((csv) => csv === '')).toHaveLength(1);
  });
});
