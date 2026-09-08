import { Page } from '@playwright/test';
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
 * Custom ranking under reverse scoring (Golf / Injury).
 *
 * In reverse modes scorekeepers enter each hit in the box of the fighter who
 * GOT HIT, and the app swaps scoringID back to the true hitter at write time
 * (doPOST.php). eventStandings therefore keeps NORMAL semantics (pointsFor =
 * points landed, wins = matches won) — these tests prove custom criteria
 * sort correctly on those stats when matches are entered reverse-style, and
 * that the reverse-scoring warning splash appears where expected.
 *
 * Both tournaments use No Afterblow (reverse + afterblow modes get forcibly
 * rewritten by the server) and scripts avoid afterblows accordingly.
 */

const CUSTOM_CRITERIA: CustomCriterion[] = [
  { field: 'doubles', sort: 'ASC' },       // Indicator: Doubles [Lowest]
  { field: 'pointsAgainst', sort: 'ASC' }, // Tiebreaker 1: Points Against [Lowest]
  { field: 'wins', sort: 'DESC' },         // Tiebreaker 2: Wins [Highest]
  { field: 'pointsFor', sort: 'DESC' },    // Tiebreaker 3: Points For [Highest]
];

const REVERSE_FIGHTERS = FIGHTERS.slice(0, 4);

// Same shape as the custom-ranking spec: Dukas dominates on wins but carries
// a double, so the doubles-first custom ranking flips the order. Per-fighter
// totals stay well under the injury base (10) so nothing auto-concludes.
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

const REVERSE_WARNING_TEXT = /Golf\/Injury scoring/;

async function runReverseLifecycle(
  page: Page,
  options: { weapon: string; reverseScore: '1' | '2'; basePointValue?: string },
) {
  await test.step('create reverse-scoring tournament with Custom ranking', async () => {
    await createTournament(page, {
      weapon: options.weapon,
      rankingID: '-1',
      customCriteria: CUSTOM_CRITERIA,
      doubleTypeLabel: 'No Afterblow',
      reverseScore: options.reverseScore,
      basePointValue: options.basePointValue,
    });
    // Save-time splash: custom ranking + reverse scoring warns on the
    // POST-redirect page.
    await expect(page.locator('.callout.warning', { hasText: REVERSE_WARNING_TEXT })).toBeVisible();
  });

  await test.step('roster, pool, and score all matches (reverse entry)', async () => {
    await addFightersToTournamentRoster(page, REVERSE_FIGHTERS);
    await createPoolAndAssignFighters(page, REVERSE_FIGHTERS);
    await scoreAllPoolMatches(page, MATCH_SCRIPT, REVERSE_FIGHTERS, { reversed: true });
  });

  await test.step('standings follow the custom criteria on landed statistics', async () => {
    const expected = expectedCustomStandings(MATCH_SCRIPT);
    const displayed = await readStandingsByHeader(page);
    expect(displayed).toHaveLength(expected.length);

    for (let i = 0; i < expected.length; i++) {
      const want = expected[i];
      const got = displayed[i];
      expect(parseInt(got['Rank'], 10), `rank of ${want.lastName}`).toBe(i + 1);
      expect(got['Name'], `row ${i + 1} fighter`).toContain(want.lastName);
      expect(parseFloat(got['Doubles'])).toBe(want.doubles);
      expect(parseFloat(got['Points Against'])).toBe(want.pointsAgainst);
      expect(parseFloat(got['Wins'])).toBe(want.wins);
      expect(parseFloat(got['Points For'])).toBe(want.pointsFor);
      expect(parseFloat(got['Score'])).toBe(want.doubles);
    }
  });
}

test('golf scoring: custom ranking sorts landed statistics correctly', async ({ page }) => {
  test.setTimeout(180_000);
  await runReverseLifecycle(page, { weapon: 'Singlestick', reverseScore: '1' });
});

test('injury scoring: custom ranking sorts landed statistics correctly', async ({ page }) => {
  test.setTimeout(180_000);
  await runReverseLifecycle(page, {
    weapon: 'Smallsword',
    reverseScore: '2',
    basePointValue: '10',
  });
});

test('reverse-scoring warning appears and disappears with the form state', async ({ page }) => {
  // Seeded tournament 1: normal scoring, template ranking. UI-only — no save.
  await page.goto('/adminTournaments.php?t=1');

  const rankingSelect = page.locator("select[name='updateTournament[tournamentRankingID]']");
  const reverseSelect = page.locator("select[name='updateTournament[isReverseScore]']");
  const criteriaSelect = page.locator("select[name='updateTournament[customCriteria][1][field]']");
  const warning = page.locator("div[id^='customRankingReverseWarning']");

  // Custom on a normal-scoring tournament: criteria, no warning.
  await rankingSelect.selectOption('-1');
  await expect(criteriaSelect).toBeAttached();
  await expect(warning).not.toBeAttached();

  // Flip to Golf: the fragment refreshes via htmx and the warning appears.
  await reverseSelect.selectOption('1');
  await expect(warning).toBeVisible();
  await expect(warning).toHaveText(REVERSE_WARNING_TEXT);
  await expect(criteriaSelect).toBeAttached();

  // Back to normal scoring: warning goes away, criteria stay.
  await reverseSelect.selectOption('0');
  await expect(warning).not.toBeAttached();
  await expect(criteriaSelect).toBeAttached();

  // Selecting a template ranking empties the fragment entirely.
  await rankingSelect.selectOption('1');
  await expect(criteriaSelect).not.toBeAttached();
});
