import { test, expect } from '@playwright/test';
import { FIGHTERS } from './helpers/test-data';
import {
  createTournament,
  getCurrentTournamentID,
  switchToTournament,
  importTournamentSettingsFrom,
  readCustomCriteria,
  addFightersToTournamentRoster,
  createPoolAndAssignFighters,
  scoreAllPoolMatches,
  readStandingsByHeader,
  CustomCriterion,
} from './helpers/tournament-actions';
import { MatchScript, pairKey, accumulateStats, FighterStats } from './helpers/standings-calc';

/**
 * Import Tournament Settings (adminTournaments.php's "Import/Copy" form)
 * against a custom-ranked source or target.
 *
 * importTournamentSettings() copies eventTournaments.* wholesale, which for a
 * custom source means tournamentRankingID = NULL (custom criteria live in
 * eventRankings, a separate table import never touches). Left unhandled,
 * that NULL drives updateEventTournaments() into the built-in branch,
 * getRankingInfo(0) finds nothing, and it aborts with "Ranking Type "NULL"
 * is not valid" -- after the "Settings updated" success alert already fired,
 * so the user sees a green banner and a red one with nothing imported.
 *
 * copyCustomRankingToTournament() is what prevents that: the importer flags
 * the copy, updateEventTournaments() skips the criteria validator, and the
 * source's eventRankings row is cloned onto the target directly.
 */

const SOURCE_CUSTOM_WEAPON = 'Dussack'; // distinct from other specs' weapons
const TARGET_TEMPLATE_WEAPON = 'Broadsword';
const CUSTOM_TARGET_WEAPON = 'Sidesword';

const CUSTOM_CRITERIA: CustomCriterion[] = [
  { field: 'doubles', sort: 'ASC' },       // Indicator: Doubles [Lowest]
  { field: 'pointsAgainst', sort: 'ASC' }, // Tiebreaker 1: Points Against [Lowest]
  { field: 'wins', sort: 'DESC' },         // Tiebreaker 2: Wins [Highest]
];

// First 3 seeded fighters -> 3 round-robin matches in one pool.
const POOL_FIGHTERS = FIGHTERS.slice(0, 3);

// Chandler wins on record but carries a double, so the custom ranking
// (Doubles ASC primary) drops her below Applegate.
const MATCH_SCRIPT: MatchScript = new Map([
  [pairKey('Applegate', 'Bowman'), {
    exchanges: [{ scorer: 'Applegate', points: 3 }],
    winner: 'Applegate',
  }],
  [pairKey('Applegate', 'Chandler'), {
    exchanges: [{ double: true }, { scorer: 'Chandler', points: 5 }],
    winner: 'Chandler',
  }],
  [pairKey('Bowman', 'Chandler'), {
    exchanges: [{ scorer: 'Chandler', points: 4 }],
    winner: 'Chandler',
  }],
]);

/** Expected order under CUSTOM_CRITERIA, best fighter first. */
function expectedCustomStandings(script: MatchScript): FighterStats[] {
  return [...accumulateStats(script).values()].sort(
    (a, b) =>
      a.doubles - b.doubles ||             // doubles ASC
      a.pointsAgainst - b.pointsAgainst || // pointsAgainst ASC
      b.wins - a.wins,                     // wins DESC
  );
}

test('import from a custom-ranked source carries criteria onto a template target, and the copied ranking drives standings', async ({ page }) => {
  test.setTimeout(120_000);

  let sourceID = '';
  let targetID = '';

  await test.step('create the custom-ranked source', async () => {
    await createTournament(page, {
      weapon: SOURCE_CUSTOM_WEAPON,
      rankingID: '-1',
      customCriteria: CUSTOM_CRITERIA,
    });
    sourceID = await getCurrentTournamentID(page);
  });

  await test.step('create a template-ranked target', async () => {
    await createTournament(page, {
      weapon: TARGET_TEMPLATE_WEAPON,
      rankingID: '1',
    });
    targetID = await getCurrentTournamentID(page);
  });

  await test.step('import the source settings onto the target', async () => {
    await switchToTournament(page, targetID);
    await importTournamentSettingsFrom(page, targetID, sourceID);

    // The whole import aborts on a bad ranking type before any settings are
    // written, so an error alert here means nothing came through.
    await expect(page.getByText(/^Error:/)).toHaveCount(0);
    await expect(page.getByText(/Settings updated to match/)).toBeVisible();
  });

  await test.step('target now shows Custom with the source criteria', async () => {
    const rankingSelect = page.locator(`#rankingID_select${targetID}`);
    await expect(rankingSelect.locator('option:checked')).toHaveText(/Custom/);
    expect(await readCustomCriteria(page, targetID)).toEqual(CUSTOM_CRITERIA);
  });

  await test.step('roster, pool, and score all matches on the target', async () => {
    await addFightersToTournamentRoster(page, POOL_FIGHTERS);
    await createPoolAndAssignFighters(page, POOL_FIGHTERS);
    await scoreAllPoolMatches(page, MATCH_SCRIPT, POOL_FIGHTERS);
  });

  await test.step('standings follow the imported custom criteria, proving the eventRankings row was copied', async () => {
    // A missing eventRankings row would leave the standings query's
    // INNER JOIN eventRankings empty -- no rows at all, not just a
    // different order -- so a populated, correctly ordered table is what
    // proves the copy actually landed rather than just the form round-trip.
    const expected = expectedCustomStandings(MATCH_SCRIPT);
    const displayed = await readStandingsByHeader(page);
    expect(displayed).toHaveLength(expected.length);

    for (let i = 0; i < expected.length; i++) {
      const want = expected[i];
      const got = displayed[i];
      expect(got['Name'], `row ${i + 1} fighter`).toContain(want.lastName);
      expect(parseFloat(got['Doubles'])).toBe(want.doubles);
      expect(parseFloat(got['Points Against'])).toBe(want.pointsAgainst);
      expect(parseFloat(got['Wins'])).toBe(want.wins);
    }
  });
});

test('import from a template source overwrites a custom target', async ({ page }) => {
  test.setTimeout(60_000);

  let sourceID = '';
  let targetID = '';

  await test.step('create a template-ranked source', async () => {
    await createTournament(page, {
      weapon: 'Spear',
      rankingID: '1',
    });
    sourceID = await getCurrentTournamentID(page);
  });

  await test.step('create a custom-ranked target', async () => {
    await createTournament(page, {
      weapon: CUSTOM_TARGET_WEAPON,
      rankingID: '-1',
      customCriteria: CUSTOM_CRITERIA,
    });
    targetID = await getCurrentTournamentID(page);
  });

  await test.step('import the template source onto the custom target', async () => {
    await switchToTournament(page, targetID);
    await importTournamentSettingsFrom(page, targetID, sourceID);

    await expect(page.getByText(/^Error:/)).toHaveCount(0);
    await expect(page.getByText(/Settings updated to match/)).toBeVisible();
  });

  await test.step('target now shows the template ranking, criteria selects gone', async () => {
    const rankingSelect = page.locator(`#rankingID_select${targetID}`);
    await expect(rankingSelect.locator('option:checked')).not.toHaveText(/Custom/);
    await expect(
      page.locator(`#customCriteria1Field_select${targetID}`),
    ).not.toBeAttached();
  });
});
