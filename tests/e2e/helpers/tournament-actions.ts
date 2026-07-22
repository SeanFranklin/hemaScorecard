import { expect, Page } from '@playwright/test';
import { MatchScript, pairKey } from './standings-calc';

/**
 * UI drivers for the tournament lifecycle (organizer session required).
 *
 * The app is session-driven: creating a tournament points the server-side
 * session at it, so every helper here just goto()s its page with no ?t=
 * (tournament auto-increment IDs are unpredictable).
 */

export type Fighter = { rosterID: number; firstName: string; lastName: string };

/** One custom ranking criterion; sorts map to Highest/Lowest First in the UI. */
export type CustomCriterion = { field: string; sort: 'DESC' | 'ASC' };

export type CreateTournamentOptions = {
  weapon: string;
  formatLabel?: string;
  /** systemRankings ID; '1' = Franklin 2014, '-1' = Custom (RANKING_CUSTOM). */
  rankingID?: string;
  /** Required when rankingID is '-1': up to 4 criteria, indicator first. */
  customCriteria?: CustomCriterion[];
  /** Double/Afterblow Type label; use 'No Afterblow' for reverse-score
   *  tournaments (the server otherwise forces afterblow settings). */
  doubleTypeLabel?: string;
  /** isReverseScore: '1' = Golf, '2' = Injury. */
  reverseScore?: '1' | '2';
  /** Required for Injury scoring — base 0 double-outs matches instantly. */
  basePointValue?: string;
};

/**
 * Fill the custom ranking criteria selects (htmx fragment) for the given
 * tournament's settings form. Assumes 'Custom' is already the selected
 * Ranking Type, which makes the selects appear.
 */
export async function fillCustomCriteria(
  page: Page,
  tournamentID: string,
  criteria: CustomCriterion[],
) {
  // The criteria selects arrive via an htmx swap after picking 'Custom'.
  await expect(page.locator(`#customCriteria1Field_select${tournamentID}`)).toBeAttached();
  for (let i = 0; i < criteria.length; i++) {
    await page
      .locator(`#customCriteria${i + 1}Field_select${tournamentID}`)
      .selectOption(criteria[i].field);
    await page
      .locator(`#customCriteria${i + 1}Sort_select${tournamentID}`)
      .selectOption(criteria[i].sort);
  }
}

/**
 * Create a tournament via adminNewTournaments.php, leaving every other
 * setting at its form default (deductive afterblow, maxPoolSize 5, ...).
 * The session tracks the new tournament afterwards.
 */
export async function createTournament(page: Page, options: CreateTournamentOptions) {
  const {
    weapon,
    formatLabel = 'Sparring Matches',
    rankingID = '1',
    customCriteria,
    doubleTypeLabel,
    reverseScore,
    basePointValue,
  } = options;

  await page.goto('/adminNewTournaments.php');
  await page.locator('#formatID_select0').selectOption({ label: formatLabel });

  // Scoring-mode fields FIRST: changing the reverse-score select refreshes
  // the custom-criteria fragment via htmx, so it must settle before any
  // criteria are chosen.
  if (doubleTypeLabel) {
    await page.locator('#doubleID_select0').selectOption({ label: doubleTypeLabel });
  }
  if (reverseScore) {
    await page.locator('#reverseScore_select0').selectOption(reverseScore);
  }
  if (basePointValue) {
    await page.locator('#baseValue_select0').fill(basePointValue);
  }

  // Ranking options arrive via AJAX after the format is picked.
  const rankingSelect = page.locator('#rankingID_select0');
  await expect(rankingSelect.locator(`option[value="${rankingID}"]`).first()).toBeAttached();
  await rankingSelect.selectOption(rankingID);

  if (customCriteria) {
    await fillCustomCriteria(page, '0', customCriteria);
  }

  await page.locator('#weaponID_div0').selectOption({ label: weapon });

  const addButton = page.locator('#editTournamentButton0');
  await expect(addButton).toBeEnabled();
  await addButton.click();

  // POST-redirect-GET lands back on the page listing current tournaments.
  await expect(page.getByRole('listitem').filter({ hasText: weapon })).toBeVisible();
}

/** Add event-roster fighters to the current tournament's roster. */
export async function addFightersToTournamentRoster(page: Page, fighters: Fighter[]) {
  await page.goto('/participantsTournament.php');

  // The add form exposes 5 slots per submit.
  for (let start = 0; start < fighters.length; start += 5) {
    const chunk = fighters.slice(start, start + 5);
    // Toggle <a> has no href, so it exposes no link role — target its onclick.
    await page.locator("a[onclick*='add-fighters']").click();
    for (let i = 0; i < chunk.length; i++) {
      await page
        .locator(`select[name='addToTournament[${i + 1}]']`)
        .selectOption(String(chunk[i].rosterID));
    }
    await page.locator("button[name='formName'][value='addToTournamentRoster']").click();
  }

  for (const fighter of fighters) {
    await expect(page.locator('#tournamentCheckInTable').getByText(fighter.lastName)).toBeVisible();
  }
}

/**
 * Create one pool and assign the fighters to it. Committing the pool roster
 * auto-generates the round-robin, so this also asserts the expected number
 * of matches exists on poolMatches.php.
 */
export async function createPoolAndAssignFighters(page: Page, fighters: Fighter[]) {
  await page.goto('/poolRosters.php');
  await page.locator("[data-open='addPoolsBox']").click();
  await page.locator("select[name='numPoolsToAdd']").selectOption('1');
  await page.locator("button[name='formName'][value='createNewPools']").click();

  // Empty pool slots; group ID is unpredictable so match on the name prefix.
  const slots = page.locator("select[name^='groupAdditions[']");
  await expect(slots.nth(fighters.length - 1)).toBeVisible();
  for (let i = 0; i < fighters.length; i++) {
    await slots.nth(i).selectOption(String(fighters[i].rosterID));
  }
  await page.locator("button[name='formName'][value='addFightersToPool']").click();

  const matchCount = (fighters.length * (fighters.length - 1)) / 2;
  await page.goto('/poolMatches.php');
  await expect(page.locator('.match-item')).toHaveCount(matchCount);
}

type Side = { pre: 'fighter1' | 'fighter2'; fighterID: string };

/** Map each fighter's lastName to its side prefix and app-side fighter ID. */
async function readSides(page: Page, fighters: Fighter[]): Promise<Record<string, Side>> {
  const sides: Record<string, Side> = {};
  for (const pre of ['fighter1', 'fighter2'] as const) {
    const dropdown = page.locator(`#${pre}_score_dropdown`);
    const box = page.locator('.fighter-score-box').filter({ has: dropdown });
    const text = await box.innerText();
    const fighter = fighters.find((f) => text.includes(f.lastName));
    if (!fighter) throw new Error(`No known fighter in ${pre} box: ${text}`);

    // Hit select is named score[<fighterID>][hit]; the same ID is the
    // winner button's value (the buttons are labeled by color, not name).
    const name = (await dropdown.getAttribute('name')) ?? '';
    const fighterID = name.match(/^score\[(\d+)\]/)?.[1];
    if (!fighterID) throw new Error(`Cannot parse fighter ID from '${name}'`);
    sides[fighter.lastName] = { pre, fighterID };
  }
  return sides;
}

export type ScoringOptions = {
  /** Reverse-score (Golf/Injury) tournaments: hits are entered in the box of
   *  the fighter who GOT HIT; the app swaps scoringID back to the true hitter
   *  at write time, so scripts keep scorer = the fighter who landed the hit. */
  reversed?: boolean;
};

/** On scoreMatch.php: play the scripted exchanges for this pairing and conclude. */
async function playMatch(
  page: Page,
  script: MatchScript,
  fighters: Fighter[],
  opts: ScoringOptions = {},
) {
  const sides = await readSides(page, fighters);
  const [nameA, nameB] = Object.keys(sides);
  const plan = script.get(pairKey(nameA, nameB));
  if (!plan) throw new Error(`No script for pairing ${nameA} vs ${nameB}`);

  for (const ex of plan.exchanges) {
    // Each submit reloads the page; the dropdowns' inline onchange calls
    // scoreDropdownChange() from score_scripts.js — interacting before that
    // script loads silently leaves the submit button as 'noExchange'.
    await page.waitForFunction(() => typeof (window as any).scoreDropdownChange === 'function');
    const submit = page.locator('#New_Exchange_Button');
    if (ex.double) {
      // Foundation switch: the radio input is hidden behind its paddle label.
      await page.click("label[for='Double_Hit_Radio']");
      await expect(page.locator('#Double_Hit_Radio')).toBeChecked();
      await expect(submit).toHaveAttribute('value', 'doubleHit');
    } else {
      const scorerName = ex.scorer!;
      // Reverse modes: enter the hit in the opposite fighter's box.
      const entryName = opts.reversed
        ? Object.keys(sides).find((n) => n !== scorerName)!
        : scorerName;
      const { pre } = sides[entryName];
      await page.locator(`#${pre}_score_dropdown`).selectOption(String(ex.points));
      if (ex.afterblow) {
        const afterblow = page.locator(`#${pre}_afterblow_input`);
        await expect(afterblow).toBeEnabled(); // JS enables it once a hit is picked
        await afterblow.selectOption(String(ex.afterblow));
      }
      // The JS rewrites the submit button's value from the inputs; clicking
      // before that lands records a pointless 'noExchange'.
      await expect(submit).toHaveAttribute('value', 'scoringHit');
    }
    await submit.click();
    await expect(page.locator('#New_Exchange_Button')).toBeVisible();
  }

  await page
    .locator(`button.conclude-match-button[value='${sides[plan.winner].fighterID}']`)
    .click();
}

/** Score every incomplete pool match according to the script. */
export async function scoreAllPoolMatches(
  page: Page,
  script: MatchScript,
  fighters: Fighter[],
  opts: ScoringOptions = {},
) {
  for (let i = 0; i < script.size; i++) {
    await page.goto('/poolMatches.php');
    // Match links live inside per-match POST forms (goToMatch<ID>).
    await page.locator('.match-incomplete a', { hasText: /Match \d+/ }).first().click();
    await expect(page).toHaveURL(/scoreMatch\.php/);
    await playMatch(page, script, fighters, opts);
  }
  await page.goto('/poolMatches.php');
  await expect(page.locator('.match-incomplete')).toHaveCount(0);
}

export type DisplayedStanding = {
  rank: number;
  name: string;
  wins: number;
  pointsFor: number;
  pointsAgainst: number;
  doubles: number;
  score: number;
};

/**
 * Read the poolStandings.php table generically: one Record per fighter row
 * keyed by trimmed header label, top rank first. For rankings whose display
 * columns differ from the default readStandings() shape.
 */
export async function readStandingsByHeader(page: Page): Promise<Record<string, string>[]> {
  await page.goto('/poolStandings.php');

  const table = page.locator('table').filter({ has: page.getByText('Rank') }).first();
  const headers = (await table.locator('tr').first().locator('th').allInnerTexts()).map((h) =>
    h.trim(),
  );

  const standings: Record<string, string>[] = [];
  const rows = table.locator('tr');
  const rowCount = await rows.count();
  for (let i = 1; i < rowCount; i++) {
    const cells = await rows.nth(i).locator('td').allInnerTexts();
    const row: Record<string, string> = {};
    headers.forEach((h, idx) => (row[h] = (cells[idx] ?? '').trim()));
    standings.push(row);
  }
  return standings;
}

/** Read the poolStandings.php table, top rank first. */
export async function readStandings(page: Page): Promise<DisplayedStanding[]> {
  await page.goto('/poolStandings.php');

  const table = page.locator('table').filter({ has: page.getByText('Points For') }).first();
  const headers = await table.locator('tr').first().locator('th').allInnerTexts();
  const col = (label: string) => {
    const idx = headers.findIndex((h) => h.trim() === label);
    if (idx === -1) throw new Error(`No standings column '${label}' in [${headers}]`);
    return idx;
  };

  const standings: DisplayedStanding[] = [];
  const rows = table.locator('tr');
  const rowCount = await rows.count();
  for (let i = 1; i < rowCount; i++) {
    const cells = await rows.nth(i).locator('td').allInnerTexts();
    standings.push({
      rank: parseInt(cells[col('Rank')], 10),
      name: cells[col('Name')].trim(),
      wins: parseFloat(cells[col('Wins')]),
      pointsFor: parseFloat(cells[col('Points For')]),
      pointsAgainst: parseFloat(cells[col('Points Against')]),
      doubles: parseFloat(cells[col('Doubles')]),
      score: parseFloat(cells[col('Score')]),
    });
  }
  return standings;
}
