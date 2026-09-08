import { test, expect } from './helpers/fixtures';
import { createTournament } from './helpers/tournament-actions';

/**
 * Tournament setup — the "Add New Tournament" form (adminNewTournaments.php).
 *
 * Form mechanics (see includes/scripts/tournament_management_scripts.js):
 *  - Submit button #editTournamentButton0 starts disabled.
 *  - Picking a format fires an AJAX call that fills #rankingID_select0.
 *  - For Sparring Matches, a ranking type must be chosen before the
 *    button enables. POSTs formName=updateTournamentInfo, updateType=add.
 *
 * The happy path lives in helpers/tournament-actions.ts (createTournament);
 * the validation test below stays bespoke because it asserts the
 * intermediate disabled states.
 */

const NEW_TOURNAMENT_WEAPON = 'Rapier (Single)';

test.describe('adding a new tournament', () => {
  test('form blocks submission until required fields are chosen', async ({ page }) => {
    await page.goto('/adminNewTournaments.php');

    const addButton = page.locator('#editTournamentButton0');
    await expect(addButton).toBeDisabled();

    // Format alone is not enough for a sparring tournament — a ranking
    // type is still missing, and the JS says so.
    await page.locator('#formatID_select0').selectOption({ label: 'Sparring Matches' });
    await expect(page.locator('#tournamentWarnings_0')).toContainText('Please select Ranking Type');
    await expect(addButton).toBeDisabled();
  });

  test('organizer creates a sparring tournament', async ({ page }) => {
    await createTournament(page, { weapon: NEW_TOURNAMENT_WEAPON });
  });
});
