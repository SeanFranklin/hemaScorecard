import type { Page } from '@playwright/test';
import { test, expect } from './helpers/fixtures';
import { COUNTRY_FIGHTERS } from './helpers/test-data';
import {
  Fighter,
  createTournament,
  setMaxPoolSize,
  submit,
  addFightersToTournamentRoster,
} from './helpers/tournament-actions';

/**
 * "Generate Pools > Separate Countries" spreads fighters whose schools share
 * a country across pools. Each test seeds 4 fighters into two pools of 2,
 * so every pool shows up as one match on poolMatches.php.
 *
 * Seed List order is fixed by the ratings, and with two pools the rating
 * balance alone sends 1st and 4th seeds to one pool, 2nd and 3rd to the
 * other. The ratings are chosen so that split would put countrymates
 * together.
 */

const { morgan, nash, ortiz, paquet, quill, reyes } = COUNTRY_FIGHTERS;

type Seed = { fighter: Fighter; rating: number };

/** Enter each fighter's Seed List rating on participantsRatings.php. */
async function setRatings(page: Page, seeds: Seed[]) {
  await page.goto('/participantsRatings.php');
  for (const { fighter, rating } of seeds) {
    const row = page.locator('#fighterRatingTable tbody tr').filter({ hasText: fighter.lastName });
    // The input saves over AJAX on change and echoes into the next cell.
    await row.locator("input[id^='rating-input-']").fill(String(rating));
    await row.locator("input[id^='rating-input-']").blur();
    await expect(row.locator("td[id^='rating-output-']")).toHaveText(String(rating));
  }
}

/** Foundation switch: the checkbox is hidden behind its paddle label. */
async function turnOn(page: Page, switchID: string) {
  await page.locator(`label[for='${switchID}']`).click();
  await expect(page.locator(`#${switchID}`)).toBeChecked();
}

/** Create the pools and generate them off the seed list with Separate Countries on. */
async function generateSeedListPools(page: Page, numPools: number, options: { debug?: boolean } = {}) {
  await page.goto('/poolRosters.php');
  await page.locator("[data-open='addPoolsBox']").click();
  await page.locator("select[name='numPoolsToAdd']").selectOption(String(numPools));
  await submit(page, page.locator("button[name='formName'][value='createNewPools']"));
  await expect(page.locator("select[name^='groupAdditions[']")).toHaveCount(numPools * 2);

  await page.locator("[data-open='autoPopulateBox']").click();
  await page.locator("select[name='generatePools[seedMethod]']").selectOption('seedList');
  await turnOn(page, 'avoidCountryFights');
  if (options.debug) {
    await turnOn(page, 'debug');
  }
  await submit(page, page.locator("button[name='formName'][value='generatePools']"));
}

/** Commit the generated seeds, which come back pre-selected in the pool slots. */
async function acceptGeneratedPools(page: Page, slotsLeft: number) {
  const slots = page.locator("select[name^='groupAdditions[']");
  await expect(slots.first()).not.toHaveValue('');
  await submit(page, page.locator("button[name='formName'][value='addFightersToPool']"));
  await expect(slots).toHaveCount(slotsLeft);
}

/** Last names in each 2 person pool, read off its one match on poolMatches.php. */
async function readPools(page: Page, fighters: Fighter[]): Promise<string[][]> {
  await page.goto('/poolMatches.php');
  const items = await page.locator('.match-item').allInnerTexts();
  return items.map((text) => {
    const names = fighters.filter((f) => text.includes(f.lastName)).map((f) => f.lastName);
    expect(names, `two known fighters in match: ${text}`).toHaveLength(2);
    return names.sort();
  });
}

/**
 * The "Show Generation Data" scoring table printed when the fighter was
 * placed: one row per pool, keyed by column header.
 */
async function readGenerationScores(page: Page, fighter: Fighter): Promise<Record<string, number>[]> {
  const heading = page.locator('h4').filter({ hasText: `Adding ${fighter.firstName} ${fighter.lastName}` });
  const rows = heading.locator('xpath=following-sibling::table[1]').locator('tr');
  const headers = await rows.first().locator('th').allInnerTexts();

  const pools: Record<string, number>[] = [];
  for (const row of (await rows.all()).slice(1)) {
    const cells = await row.locator('th, td').allInnerTexts();
    const pool: Record<string, number> = {};
    headers.forEach((header, i) => {
      if (header.trim() !== '') pool[header.trim()] = Number(cells[i]);
    });
    pools.push(pool);
  }
  return pools;
}

async function setUpTournament(page: Page, weapon: string, seeds: Seed[]) {
  await createTournament(page, { weapon });
  await setMaxPoolSize(page, '2');
  await addFightersToTournamentRoster(page, seeds.map((s) => s.fighter));
  await setRatings(page, seeds);
}

// Rating balance alone would pool Morgan with Nash (US) and Ortiz with Paquet (CA).
const US_CA_SEEDS = [
  { fighter: morgan, rating: 400 },
  { fighter: ortiz, rating: 300 },
  { fighter: paquet, rating: 200 },
  { fighter: nash, rating: 100 },
];

test('separate countries splits up fighters whose schools share a country', async ({ page }) => {
  test.setTimeout(120_000);

  const seeds = US_CA_SEEDS;
  const country: Record<string, string> = { Morgan: 'US', Nash: 'US', Ortiz: 'CA', Paquet: 'CA' };

  await setUpTournament(page, 'Backsword', seeds);
  await generateSeedListPools(page, 2);
  await acceptGeneratedPools(page, 0);

  const pools = await readPools(page, seeds.map((s) => s.fighter));
  expect(pools).toHaveLength(2);
  for (const [a, b] of pools) {
    expect(country[a], `${a} and ${b} share a pool`).not.toBe(country[b]);
  }
});

test('separate countries does not treat fighters without a country as countrymates', async ({ page }) => {
  test.setTimeout(120_000);

  // Quill and Reyes are 2nd and 3rd seeds, so rating balance pools them
  // together unless a missing country is counted as a shared one.
  const seeds = [
    { fighter: morgan, rating: 400 },
    { fighter: quill, rating: 300 },
    { fighter: reyes, rating: 200 },
    { fighter: nash, rating: 100 },
  ];

  await setUpTournament(page, 'Rotella', seeds);
  await generateSeedListPools(page, 2);
  await acceptGeneratedPools(page, 0);

  const pools = await readPools(page, seeds.map((s) => s.fighter));
  expect(pools).toContainEqual(['Quill', 'Reyes']);
});

test('generation data shows the same-country score in each pool total', async ({ page }) => {
  test.setTimeout(120_000);

  await setUpTournament(page, 'Glima', US_CA_SEEDS);
  await generateSeedListPools(page, 2, { debug: true });

  // Paquet (CA) is placed after Morgan went to pool 0 and Ortiz (CA) to pool 1.
  const scores = await readGenerationScores(page, paquet);
  expect(scores.map((pool) => pool['Same-Country Score'])).toEqual([0, -1]);
  for (const { 'TOTAL SCORE': total, ...parts } of scores) {
    const shown = Object.values(parts).reduce((sum, score) => sum + score, 0);
    expect(shown, 'columns add up to the total').toBeCloseTo(total);
  }
});
