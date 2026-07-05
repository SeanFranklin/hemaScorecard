import { test, expect } from '@playwright/test';
import { FIGHTERS } from './helpers/test-data';
import {
  createTournament,
  addFightersToTournamentRoster,
  createPoolAndAssignFighters,
  scoreAllPoolMatches,
  readStandings,
} from './helpers/tournament-actions';
import {
  MatchScript,
  pairKey,
  computeExpectedStandings,
  FRANKLIN_2014,
} from './helpers/standings-calc';

/**
 * Full tournament lifecycle journey: create a tournament, build its roster,
 * pool the fighters, score every auto-generated pool match, then verify the
 * standings match the expected Franklin 2014 ranking.
 *
 * The scripted results put the fighters in REVERSE roster order (Dukas >
 * Chandler > Bowman > Applegate) so the test proves sorting, and include one
 * double hit and one deductive afterblow so those formula terms are exercised.
 *
 * To test a different ranking algorithm: create the tournament with its
 * rankingID, write a MatchScript, and pass a matching RankingRule to
 * computeExpectedStandings (see helpers/standings-calc.ts).
 */

const WEAPON = 'Saber'; // distinct from tournament-setup spec's 'Rapier (Single)'

// First 4 seeded fighters -> 6 round-robin matches in one pool.
const LIFECYCLE_FIGHTERS = FIGHTERS.slice(0, 4);

const MATCH_SCRIPT: MatchScript = new Map([
  [pairKey('Dukas', 'Applegate'), {
    exchanges: [{ scorer: 'Dukas', points: 3 }, { scorer: 'Dukas', points: 2 }],
    winner: 'Dukas',
  }],
  [pairKey('Dukas', 'Bowman'), {
    exchanges: [{ scorer: 'Dukas', points: 3, afterblow: 1 }, { scorer: 'Dukas', points: 3 }],
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

// Hand-computed from MATCH_SCRIPT — keeps the calculator honest.
const HAND_COMPUTED = [
  { lastName: 'Dukas',     wins: 3, pointsFor: 15, pointsAgainst: 3,  doubles: 1, hitsAgainst: 1, score: 26 },
  { lastName: 'Chandler',  wins: 2, pointsFor: 10, pointsAgainst: 7,  doubles: 1, hitsAgainst: 2, score: 12 },
  { lastName: 'Bowman',    wins: 1, pointsFor: 5,  pointsAgainst: 9,  doubles: 0, hitsAgainst: 4, score: 1 },
  { lastName: 'Applegate', wins: 0, pointsFor: 1,  pointsAgainst: 12, doubles: 0, hitsAgainst: 4, score: -11 },
];

test('full tournament lifecycle: create, roster, pool, score, standings', async ({ page }) => {
  test.setTimeout(180_000);

  const expected = computeExpectedStandings(MATCH_SCRIPT, FRANKLIN_2014);
  expect(expected).toEqual(HAND_COMPUTED);

  await test.step('create Saber tournament', async () => {
    await createTournament(page, { weapon: WEAPON });
  });

  await test.step('add 4 fighters to the tournament roster', async () => {
    await addFightersToTournamentRoster(page, LIFECYCLE_FIGHTERS);
  });

  await test.step('create a pool and assign fighters (auto-generates matches)', async () => {
    await createPoolAndAssignFighters(page, LIFECYCLE_FIGHTERS);
  });

  await test.step('score all 6 pool matches', async () => {
    await scoreAllPoolMatches(page, MATCH_SCRIPT, LIFECYCLE_FIGHTERS);
  });

  await test.step('standings match the computed Franklin 2014 ranking', async () => {
    const displayed = await readStandings(page);
    expect(displayed).toHaveLength(expected.length);

    for (let i = 0; i < expected.length; i++) {
      const want = expected[i];
      const got = displayed[i];
      expect(got.rank, `rank of ${want.lastName}`).toBe(i + 1);
      expect(got.name, `row ${i + 1} fighter`).toContain(want.lastName);
      expect(got.wins).toBe(want.wins);
      expect(got.pointsFor).toBe(want.pointsFor);
      expect(got.pointsAgainst).toBe(want.pointsAgainst);
      expect(got.doubles).toBe(want.doubles);
      expect(got.score).toBe(want.score);
    }
  });
});
