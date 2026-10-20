import { test, expect } from './helpers/fixtures';
import { FIGHTERS } from './helpers/test-data';
import {
  createTournament,
  addFightersToTournamentRoster,
  createPoolAndAssignFighters,
  scoreAllPoolMatches,
} from './helpers/tournament-actions';
import { MatchScript, pairKey } from './helpers/standings-calc';

/**
 * The scoreMatch.php header and exchange history: the tournament name sits
 * centered beside the back button, and a deductive afterblow's breakdown
 * moves into a tooltip on the net score instead of a second line that
 * stretches its column.
 */

const WEAPON = 'Bowie Knife'; // distinct from other specs' weapons
const [APPLEGATE, BOWMAN] = FIGHTERS;

// A clean hit, then a 10 point hit with a 1 point afterblow (nets 9).
// "[10-1]" is too wide for a 40px column, like the "[1-0.5]" of a half point.
const SCRIPT: MatchScript = new Map([
  [
    pairKey(APPLEGATE.lastName, BOWMAN.lastName),
    {
      exchanges: [
        { scorer: APPLEGATE.lastName, points: 3 },
        { scorer: APPLEGATE.lastName, points: 10, afterblow: 1 },
      ],
      winner: APPLEGATE.lastName,
    },
  ],
]);

test('match page header and exchange history', async ({ page }) => {
  test.setTimeout(120_000);

  await test.step('score a match with a clean hit and an afterblow', async () => {
    // Form default is deductive afterblow.
    await createTournament(page, { weapon: WEAPON });
    await addFightersToTournamentRoster(page, [APPLEGATE, BOWMAN]);
    await createPoolAndAssignFighters(page, [APPLEGATE, BOWMAN]);
    await scoreAllPoolMatches(page, SCRIPT, [APPLEGATE, BOWMAN]);

    await page.locator('.match-item a', { hasText: /Match \d+/ }).first().click();
    await expect(page).toHaveURL(/scoreMatch\.php/);
  });

  await test.step('the tournament name is centered on the back button', async () => {
    const button = await page.getByRole('link', { name: 'Back To Match List' }).boundingBox();
    const name = await page.locator('.match-heading-name').boundingBox();
    expect(button && name, 'button and name are rendered').toBeTruthy();
    const middle = (box: { y: number; height: number }) => box.y + box.height / 2;
    expect(Math.abs(middle(name!) - middle(button!))).toBeLessThanOrEqual(1);
  });

  const columns = page.locator('.match-history-column');

  await test.step("the afterblow's fighter cells line up with the clean hit's", async () => {
    // Clean hit, afterblow, then the "Win" that concluding the match records.
    await expect(columns).toHaveCount(3);
    for (const fighterCell of ['.f1-BG', '.f2-BG']) {
      const clean = await columns.nth(0).locator(fighterCell).boundingBox();
      const afterblow = await columns.nth(1).locator(fighterCell).boundingBox();
      expect(afterblow!.y, `${fighterCell} top`).toBe(clean!.y);
      expect(afterblow!.height, `${fighterCell} height`).toBe(clean!.height);
    }
  });

  await test.step("hovering the afterblow's net score shows the hit and afterblow", async () => {
    const net = columns.nth(1).locator('[data-tooltip]');
    await expect(net).toHaveText('9');
    // Foundation links the tooltip once it initialises; hovering before that shows nothing.
    await expect(net).toHaveAttribute('aria-describedby', /.+/);
    // The generated id can start with a digit, which a #id selector can't.
    const tooltip = page.locator(`[id='${await net.getAttribute('aria-describedby')}']`);
    await net.hover();
    await expect(tooltip).toBeVisible();
    await expect(tooltip).toHaveText(/10\D+1/);
  });
});
