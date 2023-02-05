<?php
/*******************************************************************************
	
Fighter Penalties
	Display a summary of all the penatlies that have been handed out in the 
	tournament, grouped by fighter.

*******************************************************************************/

// PAGE SETUP //////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Fighter Penalties';

$tournamentPage 		 = false;
$lockedTournamentWarning = false;

$jsIncludes = null;

include('includes/header.php');

if(ALLOW['EVENT_SCOREKEEP'] == false && ALLOW['VIEW_SETTINGS'] == false){
	pageError('user');
} else if($_SESSION['eventID'] == null){
	pageError('event');
} else {

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

	$fightersWithPenalties = getEventPenalties($_SESSION['eventID']);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>	

	<div class='grid-x grid-margin-x'>
	<div class='large-6 cell'>

	<?php 

	foreach($fightersWithPenalties as $fighter):

			echo "<HR><h5>".getFighterName($fighter['fighterID']);
			echo " [".$fighter['numPenalties']." Penalties]</h5>";
		
		foreach($fighter['list'] as $penalty){
			displayPenalty($penalty);
		}
		
	endforeach
	?>

	</div>
	</div>

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
