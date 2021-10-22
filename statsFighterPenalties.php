<?php
/*******************************************************************************
	Fighter Management
	
	Withdraw fighters if they are injured and can no longer compete
	LOGIN:
		- ADMIN or higher required to access
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Fighter Penalties';
include('includes/header.php');

$eventID = $_SESSION['eventID'];

if(ALLOW['EVENT_SCOREKEEP'] == false && ALLOW['VIEW_SETTINGS'] == false){
	pageError('user');
} else if($eventID == null){
	pageError('event');
} else {


$penalties = getEventPenalties($eventID);


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>	


<div class='grid-x grid-margin-x'>
<div class='large-6 cell'>

<?php 

$rosterID = 0;
foreach($penalties as $penalty):


	if($rosterID != $penalty['scoringID'])
	{
		$rosterID = $penalty['scoringID'];
		echo "<HR><h5>".getFighterName($rosterID);
		echo " [".$penalty['numPenalties']." Penalties]</h5>";
	}

	displayPenalty($penalty);

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
