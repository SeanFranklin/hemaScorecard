<?php
/*******************************************************************************
	Add New Tournaments
	
	Adds new tournaments to the event
	LOGIN:
		- ADMIN and above can view the page and add tournaments
		
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Manage Tournaments';
$jsIncludes[] = 'tournament_management_scripts.js';
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false) {
	pageError('user');
} else {
	
	$tournamentIDs = getEventTournaments();
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>
	
	<fieldset>
	<form method='POST'>
		
	<input type='hidden' name='formName' value='updateTournamentInfo'>
	<input type='hidden' name='modifyTournamentID' value='0'>

	<h3>Add New Tournament</u></h3>

	<div class='grid-x grid-margin-x grid-padding-x'>
	<div class='large-7 cell'>

	<table class='options-table stack'>
	
		<!-- Tournament Info --------------------------------------------->
			<?php 

			// Tournament Info --------------------------
				edit_tournamentOptionsRow("General Configuration");
				edit_tournamentName(0);
				edit_tournamentFormatType(0);
				edit_tournamentRankingType(0);
				edit_tournamentBasePoints(0); 

			// Sparring Tournaments Info --------------------------
				edit_tournamentOptionsRow("Sparring Info","option-sparring");
				edit_tournamentDoubleType(0);
				edit_tournamentNetScore(0);
				edit_tournamentOverrideDoubles(0);
				edit_tournamentTies(0);
				edit_tournamentReverseScore(0);

			// Match Display --------------------------
				edit_tournamentOptionsRow("Match Display","option-match-display");
				edit_tournamentColors(0, 1);
				edit_tournamentColors(0, 2);
				edit_tournamentTimerCountdown(0);

			// Pools & Standings --------------------------
				edit_tournamentOptionsRow("Pool Configuration","option-pools");
				edit_tournamentMaxPoolSize(0);
				edit_tournamentPoolWinners(0);

			// Match Conclusion --------------------------
				edit_tournamentOptionsRow("Match Auto-Conclude","option-auto-conclude",
					"Optional settings for the software to automatically end a match when these conditions are met. 
					Scorekeepers can always conclude (or re-open) matches regardless of what is set here.");
				edit_tournamentMaxDoubles(0);
				edit_tournamentTimeLimit(0);
				edit_tournamentMaxPoints(0);
				edit_tournamentMaxPointSpread(0);
				edit_tournamentMaxExchanges(0);

			// Sub Matches --------------------------
				edit_tournamentOptionsRow("Sub-Match Info","option-sub-match",
					"Sub-matches will create multiple 'sub-matches' for each match.
							<BR><u>Example</u>: A multi-weapon tournament where competitors
							face off with each weapon set one after another.");
				edit_tournamentSubMatches(0);

			// Teams --------------------------
				edit_tournamentOptionsRow("Team Tournaments","option-teams");
				edit_tournamentTeams(0);

			// Logistics
				edit_tournamentOptionsRow("Other Miscelanious Options","option-misc");
				edit_tournamentCuttingQual(0);
				edit_tournamentDoublesCarryForward(0);
				edit_tournamentHideFinalResults(0);
				edit_tournamentKeepPrivate(0);
				edit_tournamentRequireSignOff(0);
				edit_tournamentStaffCheckin(0);
				edit_tournamentNormalization(0);
				edit_tournamentLimitPoolMatches(0);

			?>
	</table>
	</div>
	</div>

	<div id='tournamentWarnings_0'>
		<BR>
	</div>

<!-- Submit Form Options -------------------------------------------------->
	<div>
		<button class='button success expanded'
				name='updateType' value='add' disabled id='editTournamentButton0'>
				Add New Tournament
		</button>
	</div>
	</form>

	</fieldset>

	
<!-- List of existing tournaments -->
	<fieldset class='fieldset'>
	<legend><h4>Current Tournaments</h4></legend>
	
	<?php foreach((array)$tournamentIDs as $tournamentID):
		$name = getTournamentName($tournamentID); ?>
		<li><?=$name?></li>
	<?php endforeach ?>
	</fieldset>

<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
