import type { Page, Locator } from '@playwright/test';
import { test, expect } from './helpers/fixtures';
import { FIGHTERS } from './helpers/test-data';
import {
  Fighter,
  createTournament,
  getCurrentTournamentID,
  addFightersToTournamentRoster,
  scoreAllPoolMatches,
  readStandings,
} from './helpers/tournament-actions';
import { MatchScript, pairKey } from './helpers/standings-calc';

/**
 * Two rounds of swiss pairing with an odd field: create a tournament with
 * 2 person pools and two cumulative pool sets, fill each set with
 * "Generate Pools > Swiss Pairing", score the matches, and check that the
 * bye rotates, nobody gets a rematch, and the bye is scored as a win.
 *
 * Fighters are unrated so the first set's order is whatever the DB returns;
 * the assertions are structural rather than naming specific pairings.
 */

const WEAPON = 'Messer'; // distinct from other specs' weapons

// 5 fighters -> 2 matches and a bye per set.
const SWISS_FIGHTERS = FIGHTERS.slice(0, 5);

/** Script the set's drawn pairings: the fighter earlier in the roster wins 3-0. */
function scriptFor(pairs: [string, string][]): MatchScript {
  const script: MatchScript = new Map();
  for (const [a, b] of pairs) {
    const order = (name: string) => SWISS_FIGHTERS.findIndex((f) => f.lastName === name);
    const winner = order(a) < order(b) ? a : b;
    script.set(pairKey(a, b), { exchanges: [{ scorer: winner, points: 3 }], winner });
  }
  return script;
}

/**
 * Click a submit button and wait for its POST-redirect-GET to land, so a
 * following page.goto() can't cancel the form submission mid-flight.
 */
async function submit(page: Page, button: Locator) {
  const posted = page.waitForResponse((r) => r.request().method() === 'POST');
  await button.click();
  await posted;
  await page.waitForLoadState();
}

/** Set the tournament's maximum pool size on adminTournaments.php. */
async function setMaxPoolSize(page: Page, size: string) {
  await page.goto('/adminTournaments.php');
  const tournamentID = await getCurrentTournamentID(page);
  await page.locator(`#maxPoolSize_select${tournamentID}`).selectOption(size);
  await submit(page, page.locator(`#editTournamentButton${tournamentID}`));
  await page.reload();
  await expect(page.locator(`#maxPoolSize_select${tournamentID}`)).toHaveValue(size);
}

/** Two pool sets, with the second one cumulative. */
async function createCumulativePoolSets(page: Page) {
  await page.goto('/poolRosters.php');
  await page.locator("[data-open='poolSetBox']").click();
  await page.locator("select[name='numPoolSets']").selectOption('2');
  await submit(page, page.locator("button[name='formName'][value='updatePoolSets']"));
  await expect(page.locator("button[name='groupSet'][value='2']")).toBeVisible();

  // New sets default to cumulative; make sure rather than assume.
  await page.locator("[data-open='poolSetBox']").click();
  const cumulative = page.locator('#cumulativeSet-2');
  if (!(await cumulative.isChecked())) {
    await page.locator("label[for='cumulativeSet-2']").click();
  }
  await expect(cumulative).toBeChecked();
  await submit(page, page.locator("button[name='formName'][value='updatePoolSets']"));
}

async function switchToPoolSet(page: Page, set: string) {
  await page.goto('/poolRosters.php');
  await submit(page, page.locator(`button[name='groupSet'][value='${set}']`));
}

/** Create the pools, run swiss pairing, and accept the generated rosters. */
async function generateSwissPools(page: Page, numPools: number, numFighters: number) {
  await page.goto('/poolRosters.php');
  await page.locator("[data-open='addPoolsBox']").click();
  await page.locator("select[name='numPoolsToAdd']").selectOption(String(numPools));
  await submit(page, page.locator("button[name='formName'][value='createNewPools']"));

  // Empty pool slots, 2 per pool.
  const slots = page.locator("select[name^='groupAdditions[']");
  await expect(slots).toHaveCount(numPools * 2);

  await page.locator("[data-open='autoPopulateBox']").click();
  await page.locator("select[name='generatePools[seedMethod]']").selectOption('swiss');
  await submit(page, page.locator("button[name='formName'][value='generatePools']"));

  // Generated seeds come back pre-selected in the slots.
  await expect(slots.first()).not.toHaveValue('');
  await submit(page, page.locator("button[name='formName'][value='addFightersToPool']"));
  await expect(slots).toHaveCount(numPools * 2 - numFighters);
}

type SetPairings = { pairs: [string, string][]; bye: string };

/** Read who fought whom on poolMatches.php; whoever is missing had the bye. */
async function readPairings(page: Page, fighters: Fighter[]): Promise<SetPairings> {
  await page.goto('/poolMatches.php');
  const items = await page.locator('.match-item').allInnerTexts();

  const pairs: [string, string][] = [];
  const seen = new Set<string>();
  for (const text of items) {
    const names = fighters.filter((f) => text.includes(f.lastName)).map((f) => f.lastName);
    expect(names, `two known fighters in match: ${text}`).toHaveLength(2);
    pairs.push([names[0], names[1]]);
    names.forEach((n) => seen.add(n));
  }

  const byes = fighters.filter((f) => !seen.has(f.lastName)).map((f) => f.lastName);
  expect(byes, 'exactly one fighter sat out').toHaveLength(1);
  return { pairs, bye: byes[0] };
}

test('swiss pairing rotates the bye, avoids rematches, and scores the bye as a win', async ({ page }) => {
  test.setTimeout(240_000);

  await test.step('create a Messer tournament with 2 person pools', async () => {
    await createTournament(page, { weapon: WEAPON });
    await setMaxPoolSize(page, '2');
  });

  await test.step('add 5 fighters and two cumulative pool sets', async () => {
    await addFightersToTournamentRoster(page, SWISS_FIGHTERS);
    await createCumulativePoolSets(page);
  });

  let setOne: SetPairings;
  await test.step('set 1: swiss pairing makes 2 matches and a bye', async () => {
    await switchToPoolSet(page, '1');
    await generateSwissPools(page, 3, SWISS_FIGHTERS.length);
    setOne = await readPairings(page, SWISS_FIGHTERS);
    expect(setOne.pairs).toHaveLength(2);
  });

  await test.step('set 1: the bye is scored as a win', async () => {
    await scoreAllPoolMatches(page, scriptFor(setOne.pairs), SWISS_FIGHTERS);
    const standings = await readStandings(page);
    expect(standings).toHaveLength(SWISS_FIGHTERS.length);
    const byeRow = standings.find((s) => s.name.includes(setOne.bye));
    expect(byeRow?.wins, `wins for bye fighter ${setOne.bye}`).toBe(1);
  });

  let setTwo: SetPairings;
  await test.step('set 2: the bye rotates and nobody gets a rematch', async () => {
    await switchToPoolSet(page, '2');
    await generateSwissPools(page, 3, SWISS_FIGHTERS.length);
    setTwo = await readPairings(page, SWISS_FIGHTERS);
    expect(setTwo.pairs).toHaveLength(2);
    expect(setTwo.bye).not.toBe(setOne.bye);
    const setOneKeys = setOne.pairs.map(([a, b]) => pairKey(a, b));
    for (const [a, b] of setTwo.pairs) {
      expect(setOneKeys, `rematch ${a} v ${b}`).not.toContain(pairKey(a, b));
    }
  });

  await test.step('set 2: cumulative standings carry both rounds', async () => {
    await scoreAllPoolMatches(page, scriptFor(setTwo.pairs), SWISS_FIGHTERS);
    const standings = await readStandings(page);
    expect(standings).toHaveLength(SWISS_FIGHTERS.length);
    // 2 match wins and 1 bye per set, over 2 sets.
    const totalWins = standings.reduce((sum, s) => sum + s.wins, 0);
    expect(totalWins).toBe(6);
  });
});
