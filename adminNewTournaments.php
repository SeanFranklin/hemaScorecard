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
	
	
	<fieldset class='fieldset grid-x grid-margin-x cell'>
		<legend><h4>Add New Tournament</h4></legend>
		<form method='POST'>
		<input type='hidden' name='formName' value='updateTournamentInfo'>

	<!-- Mandatory fields -->
		<?php edit_tournamentName(); ?>
		<div id='requiredFields_new' class='grid-x grid-padding-x text-center'>
			<?php
			edit_tournamentFormatType();
			edit_tournamentDoubleType();
			edit_tournamentRankingType();
			edit_tournamentNetScore();
			edit_tournamentBasePoints();			
			?>
		</div>
		
	<!-- Submit button -->
		<div class='grid-x grid-padding-x text-center'>
			<div class=' cell'>	
			<div id='tournamentWarnings_new'>
				<BR>
			</div>
			<button class='button success expanded'
				name='updateType' value='add' disabled id='editTournamentButtonnew'>
				Add New Tournament
			</button>
			</div>
		</div>
		
	<!-- Optional fields -->
		<div id='optionalFields_new' class='grid-x grid-padding-x text-center'>
			<?php
			edit_tournamentTies();
			edit_tournamentColors('new', 1);
			edit_tournamentColors('new', 2);
			edit_tournamentMaxDoubles();
			edit_tournamentMaxPoolSize();
			edit_tournamentNormalization();
			edit_tournamentPoolWinners();

			edit_tournamentTimeLimit();
			edit_tournamentTimerCountdown();
			edit_tournamentMaxExchanges();
			edit_tournamentMaxPoints();
			edit_tournamentMaxPointSpread();
			edit_tournamentReverseScore();
			edit_tournamentLimitPoolMatches();
			edit_tournamentOverrideDoubles();
			edit_tournamentCuttingQual();
			edit_tournamentNumSubMatches();
			edit_tournamentSubMatchMode();
			edit_tournamentTeams();
			edit_tournamentKeepPrivate();
			edit_tournamentHideFinalResults();
			edit_tournamentRequireSignOff();
			edit_tournamentStaffCheckin();
			?>
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
