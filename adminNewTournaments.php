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
	displayAnyErrors('No Event Selected');
} elseif(USER_TYPE < USER_ADMIN) {
	displayAnyErrors("Please Log In to Edit");
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
			edit_tournamentElimType();
			edit_tournamentDoubleType();
			edit_tournamentRankingType();
			edit_tournamentBasePoints();
			?>
		</div>
		
	<!-- Submit button -->
		<div class='grid-x grid-padding-x text-center'>
			<div class=' cell'>	
			<BR>
			<button class='button success expanded'
				name='updateType' value='add' disabled id='editTournamentButtonnew'>
				Add New Tournament
			</button>
			</div>
		</div>
		
	<!-- Optional fields -->
		<div id='optionalFields_new' class='grid-x grid-padding-x text-center'>
			<?php
			edit_tournamentTimer();
			edit_tournamentColors('new', 1);
			edit_tournamentColors('new', 2);
			edit_tournamentMaxDoubles();
			edit_tournamentMaxPoolSize();
			edit_tournamentNormalization();
			edit_tournamentControlPoints();
			edit_tournamentTies();
			edit_tournamentCuttingQual();
			edit_tournamentMaxExchanges();
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
