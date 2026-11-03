import { test, expect } from './helpers/fixtures';
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
 * Custom ranking formula tiers. The indicator pointsFor / doubles (fallback
 * 9001) ranks 0-double fighters first, so standings must follow the compiled
 * formula rather than wins.
 */

const WEAPON = 'Dane Axe'; // distinct from other specs' weapons
const REJECT_WEAPON = 'Dagger';

const FORMULA_CRITERIA: CustomCriterion[] = [
  // Indicator: hit ratio with divide-by-zero fallback
  { formula: 'pointsFor / doubles', fallback: '9001', sort: 'DESC' },
  // Tiebreaker 1: formula with no division (fallback defaults to 0)
  { formula: 'pointsFor - pointsAgainst', sort: 'DESC' },
  // Tiebreaker 2: plain field pick alongside formula tiers
  { field: 'wins', sort: 'DESC' },
];

// First 4 seeded fighters -> 6 round-robin matches in one pool.
const FORMULA_FIGHTERS = FIGHTERS.slice(0, 4);

// Dukas leads on wins but has a double, so the 0-double fighters (Chandler,
// Applegate) rank above him on the 9001 fallback.
const MATCH_SCRIPT: MatchScript = new Map([
  [pairKey('Dukas', 'Applegate'), {
    exchanges: [{ scorer: 'Dukas', points: 5 }],
    winner: 'Dukas',
  }],
  [pairKey('Dukas', 'Bowman'), {
    exchanges: [{ double: true }, { scorer: 'Dukas', points: 3 }],
    winner: 'Dukas',
  }],
  [pairKey('Dukas', 'Chandler'), {
    exchanges: [{ scorer: 'Chandler', points: 2 }, { scorer: 'Dukas', points: 3 }],
    winner: 'Dukas',
  }],
  [pairKey('Chandler', 'Applegate'), {
    exchanges: [{ scorer: 'Chandler', points: 3 }],
    winner: 'Chandler',
  }],
  [pairKey('Chandler', 'Bowman'), {
    exchanges: [{ scorer: 'Chandler', points: 2 }, { scorer: 'Bowman', points: 1 }],
    winner: 'Chandler',
  }],
  [pairKey('Bowman', 'Applegate'), {
    exchanges: [{ scorer: 'Bowman', points: 3 }, { scorer: 'Applegate', points: 1 }],
    winner: 'Bowman',
  }],
]);

/** JS mirror of the indicator formula, fallback rule included. */
const indicator = (s: FighterStats) => (s.doubles > 0 ? s.pointsFor / s.doubles : 9001);
const pointDiff = (s: FighterStats) => s.pointsFor - s.pointsAgainst;

/** Expected order under FORMULA_CRITERIA, best fighter first. */
function expectedFormulaStandings(script: MatchScript): FighterStats[] {
  return [...accumulateStats(script).values()].sort(
    (a, b) =>
      indicator(b) - indicator(a) ||   // formula indicator DESC
      pointDiff(b) - pointDiff(a) ||   // formula tiebreaker DESC
      b.wins - a.wins,                 // wins DESC
  );
}

test('formula ranking: criteria persist and standings follow the compiled formulas', async ({ page }) => {
  test.setTimeout(180_000);

  await test.step('create a tournament with formula criteria', async () => {
    await createTournament(page, {
      weapon: WEAPON,
      rankingID: '-1',
      customCriteria: FORMULA_CRITERIA,
    });
  });

  await test.step('settings page restores formulas and fallbacks', async () => {
    await page.goto('/adminTournaments.php');

    const rankingSelect = page.locator("select[name='updateTournament[tournamentRankingID]']");
    await expect(rankingSelect.locator('option:checked')).toHaveText(/Custom/);

    // Tiers 1-2 are formula tiers with a formula/fallback row
    await expect(
      page.locator("select[name='updateTournament[customCriteria][1][field]']"),
    ).toHaveValue('__formula__');
    await expect(
      page.locator("input[name='updateTournament[customCriteria][1][formula]']"),
    ).toHaveValue('pointsFor / doubles');
    await expect(
      page.locator("input[name='updateTournament[customCriteria][1][fallback]']"),
    ).toHaveValue('9001');

    await expect(
      page.locator("select[name='updateTournament[customCriteria][2][field]']"),
    ).toHaveValue('__formula__');
    await expect(
      page.locator("input[name='updateTournament[customCriteria][2][formula]']"),
    ).toHaveValue('pointsFor - pointsAgainst');
    await expect(
      page.locator("input[name='updateTournament[customCriteria][2][fallback]']"),
    ).toHaveValue('0');

    // Tier 3 is a field tier: no formula row.
    await expect(
      page.locator("select[name='updateTournament[customCriteria][3][field]']"),
    ).toHaveValue('wins');
    await expect(
      page.locator("input[name='updateTournament[customCriteria][3][formula]']"),
    ).toHaveCount(0);

    for (let i = 1; i <= 3; i++) {
      await expect(
        page.locator(`select[name='updateTournament[customCriteria][${i}][sort]']`),
      ).toHaveValue('DESC');
    }
  });

  await test.step('roster, pool, and score all matches (no 500 on divide-by-zero)', async () => {
    await addFightersToTournamentRoster(page, FORMULA_FIGHTERS);
    await createPoolAndAssignFighters(page, FORMULA_FIGHTERS);
    // Every score update hits the fallback for fighters without doubles
    await scoreAllPoolMatches(page, MATCH_SCRIPT, FORMULA_FIGHTERS);
  });

  await test.step('standings follow the formula order and show formula columns', async () => {
    const expected = expectedFormulaStandings(MATCH_SCRIPT);
    const displayed = await readStandingsByHeader(page);
    expect(displayed).toHaveLength(expected.length);

    for (let i = 0; i < expected.length; i++) {
      const want = expected[i];
      const got = displayed[i];
      expect(parseInt(got['Rank'], 10), `rank of ${want.lastName}`).toBe(i + 1);
      expect(got['Name'], `row ${i + 1} fighter`).toContain(want.lastName);
      // Formula columns are headed by their source; Score mirrors the indicator
      expect(parseFloat(got['pointsFor / doubles'])).toBeCloseTo(indicator(want), 1);
      expect(parseFloat(got['pointsFor - pointsAgainst'])).toBe(pointDiff(want));
      expect(parseFloat(got['Wins'])).toBe(want.wins);
      expect(parseFloat(got['Score'])).toBeCloseTo(indicator(want), 1);
    }
  });
});

test('formula ranking: invalid formula is rejected inline and on save', async ({ page }) => {
  await test.step('create a tournament with a valid formula indicator', async () => {
    await createTournament(page, {
      weapon: REJECT_WEAPON,
      rankingID: '-1',
      customCriteria: [{ formula: 'wins * 5 + pointsFor', sort: 'DESC' }],
    });
  });

  const formulaInput = page.locator("input[name='updateTournament[customCriteria][1][formula]']");

  await test.step('inline validation flags an injection attempt', async () => {
    await page.goto('/adminTournaments.php');
    await expect(formulaInput).toHaveValue('wins * 5 + pointsFor');

    await formulaInput.fill('wins) OR (1=1');
    // fill() only fires an input event; htmx listens for change/keyup.
    await formulaInput.dispatchEvent('change');
    // Inline validation is a hint only; the server has final say on save.
    await expect(page.locator("[id^='customCriteria1FormulaMsg'] .form-error")).toBeVisible();
  });

  await test.step('saving the invalid formula is refused', async () => {
    await page.locator("button[id^='editTournamentButton']").click();
    await expect(
      page.locator('.callout.alert').filter({ hasText: 'Tournament not updated' }),
    ).toBeVisible();
  });

  await test.step('the saved configuration is unchanged', async () => {
    await page.goto('/adminTournaments.php');
    await expect(formulaInput).toHaveValue('wins * 5 + pointsFor');
  });
});
