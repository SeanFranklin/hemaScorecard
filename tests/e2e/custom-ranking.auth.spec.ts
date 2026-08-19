import { test, expect } from '@playwright/test';
import { FIGHTERS } from './helpers/test-data';
import {
  createTournament,
  addFightersToTournamentRoster,
  createPoolAndAssignFighters,
  scoreAllPoolMatches,
  readStandingsByHeader,
  CustomCriterion,
} from './helpers/tournament-actions';
import {
  MatchScript,
  pairKey,
  accumulateStats,
  FighterStats,
} from './helpers/standings-calc';

/**
 * Custom tournament ranking: instead of a systemRankings template, the
 * organizer picks 'Custom' as the Ranking Type and orders fighters by up to
 * 4 whitelisted eventStandings fields (indicator + 3 tiebreakers), each
 * with its own sort direction.
 * Stored as the tournament's own eventRankings row (systemRankingID NULL).
 *
 * The criteria deliberately invert the natural result: primary Doubles
 * [Lowest] ranks the 0-double, low-win fighters first, proving the standings
 * follow the custom order-by chain (including an ASC primary) rather than
 * any template.
 */

const WEAPON = 'Sword and Buckler'; // distinct from other specs' weapons

const CUSTOM_CRITERIA: CustomCriterion[] = [
  { field: 'doubles', sort: 'ASC' },       // Indicator: Doubles [Lowest]
  { field: 'pointsAgainst', sort: 'ASC' }, // Tiebreaker 1: Points Against [Lowest]
  { field: 'wins', sort: 'DESC' },         // Tiebreaker 2: Wins [Highest]
  { field: 'pointsFor', sort: 'DESC' },    // Tiebreaker 3: Points For [Highest]
];

// First 4 seeded fighters -> 6 round-robin matches in one pool.
const CUSTOM_FIGHTERS = FIGHTERS.slice(0, 4);

// Same shape as the lifecycle spec's script: Dukas dominates on wins but
// carries a double, so the custom ranking flips the order.
const MATCH_SCRIPT: MatchScript = new Map([
  [pairKey('Dukas', 'Applegate'), {
    exchanges: [{ scorer: 'Dukas', points: 3 }, { scorer: 'Dukas', points: 2 }],
    winner: 'Dukas',
  }],
  [pairKey('Dukas', 'Bowman'), {
    exchanges: [{ scorer: 'Dukas', points: 3 }, { scorer: 'Dukas', points: 3 }],
    winner: 'Dukas',
  }],
  [pairKey('Dukas', 'Chandler'), {
    exchanges: [{ double: true }, { scorer: 'Chandler', points: 3 }, { scorer: 'Dukas', points: 5 }],
    winner: 'Dukas',
  }],
  [pairKey('Chandler', 'Applegate'), {
    exchanges: [{ scorer: 'Chandler', points: 4 }],
    winner: 'Chandler',
  }],
  [pairKey('Chandler', 'Bowman'), {
    exchanges: [{ scorer: 'Chandler', points: 3 }, { scorer: 'Bowman', points: 2 }],
    winner: 'Chandler',
  }],
  [pairKey('Bowman', 'Applegate'), {
    exchanges: [{ scorer: 'Bowman', points: 3 }, { scorer: 'Applegate', points: 1 }],
    winner: 'Bowman',
  }],
]);

/** Expected order under CUSTOM_CRITERIA, best fighter first. */
function expectedCustomStandings(script: MatchScript): FighterStats[] {
  return [...accumulateStats(script).values()].sort(
    (a, b) =>
      a.doubles - b.doubles ||               // doubles ASC
      a.pointsAgainst - b.pointsAgainst ||   // pointsAgainst ASC
      b.wins - a.wins ||                     // wins DESC
      b.pointsFor - a.pointsFor,             // pointsFor DESC
  );
}

test('custom ranking: criteria persist and standings follow the custom order', async ({ page }) => {
  test.setTimeout(180_000);

  await test.step('create a tournament with a Custom ranking', async () => {
    await createTournament(page, {
      weapon: WEAPON,
      rankingID: '-1',
      customCriteria: CUSTOM_CRITERIA,
    });
  });

  await test.step('settings page restores Custom selection and criteria', async () => {
    await page.goto('/adminTournaments.php');

    const rankingSelect = page.locator("select[name='updateTournament[tournamentRankingID]']");
    await expect(rankingSelect.locator('option:checked')).toHaveText(/Custom/);

    for (let i = 0; i < CUSTOM_CRITERIA.length; i++) {
      await expect(
        page.locator(`select[name='updateTournament[customCriteria][${i + 1}][field]']`),
      ).toHaveValue(CUSTOM_CRITERIA[i].field);
      await expect(
        page.locator(`select[name='updateTournament[customCriteria][${i + 1}][sort]']`),
      ).toHaveValue(CUSTOM_CRITERIA[i].sort);
    }
  });

  await test.step('roster, pool, and score all matches', async () => {
    await addFightersToTournamentRoster(page, CUSTOM_FIGHTERS);
    await createPoolAndAssignFighters(page, CUSTOM_FIGHTERS);
    await scoreAllPoolMatches(page, MATCH_SCRIPT, CUSTOM_FIGHTERS);
  });

  await test.step('standings follow the custom criteria order', async () => {
    const expected = expectedCustomStandings(MATCH_SCRIPT);
    const displayed = await readStandingsByHeader(page);
    expect(displayed).toHaveLength(expected.length);

    for (let i = 0; i < expected.length; i++) {
      const want = expected[i];
      const got = displayed[i];
      expect(parseInt(got['Rank'], 10), `rank of ${want.lastName}`).toBe(i + 1);
      expect(got['Name'], `row ${i + 1} fighter`).toContain(want.lastName);
      // Display columns are the criteria themselves, plus Score mirroring
      // the indicator (doubles).
      expect(parseFloat(got['Doubles'])).toBe(want.doubles);
      expect(parseFloat(got['Points Against'])).toBe(want.pointsAgainst);
      expect(parseFloat(got['Wins'])).toBe(want.wins);
      expect(parseFloat(got['Points For'])).toBe(want.pointsFor);
      expect(parseFloat(got['Score'])).toBe(want.doubles);
    }
  });
});

test('switching a template tournament to Custom reveals criteria via htmx', async ({ page }) => {
  // Seeded tournament 1 uses a systemRankings template; selecting Custom in
  // its settings must swap the criteria selects in without a page load.
  await page.goto('/adminTournaments.php?t=1');

  const rankingSelect = page.locator("select[name='updateTournament[tournamentRankingID]']");
  await expect(rankingSelect.locator("option[value='-1']")).toBeAttached();

  // No criteria selects while a template is chosen.
  await expect(
    page.locator("select[name='updateTournament[customCriteria][1][field]']"),
  ).not.toBeAttached();

  await rankingSelect.selectOption('-1');
  await expect(
    page.locator("select[name='updateTournament[customCriteria][1][field]']"),
  ).toBeAttached();

  // Selecting a template again removes them (empty fragment swap).
  await rankingSelect.selectOption('1');
  await expect(
    page.locator("select[name='updateTournament[customCriteria][1][field]']"),
  ).not.toBeAttached();
});
