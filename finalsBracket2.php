<?php
/*******************************************************************************
	Finals Bracket 2
	
	Displays the consolation bracket
	LOGIN:
		- STAFF and above can add/remove fighters from matches
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Finals Bracket';
$includeTournamentName = true;
$lockedTournamentWarning = true;
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if($tournamentID == null){
	displayAnyErrors('No Tournament Selected', 'CENTER');
} elseif(!isBrackets($tournamentID)){
	displayAnyErrors("There are no brackets for this tournament");
} elseif ((getEventStatus() == 'upcoming' || getEventStatus() == 'hidden') && USER_TYPE < USER_STAFF){
	displayAnyErrors("Event is still upcoming<BR>Bracket not yet released");
} else {
	
	$allBracketInfo = getBracketInformation($tournamentID);
	
	// Redirect if there is no loser bracket
	if($allBracketInfo['loser'] == null){
		if(USER_TYPE < USER_SUPER_ADMIN){
			redirect('finalsBracket1.php');
		}
		displayAnyErrors("No Consolation Bracket created");		
	}
	
	$finalists = getTournamentStandings($tournamentID, null, 'pool', 'advancements');
	
// Bracket Helper
	$bracketAdvancements = getLoserBracketAdvancements($allBracketInfo, $finalists);
	bracketHelperToggleButton($allBracketInfo, $finalists);


// Display the bracket - display_functions.php
	bracket_display($allBracketInfo['loser'],$finalists,'losers',$bracketAdvancements);
	
	echo "<BR><BR>";// This exists because the layout is unreliable and doesn't always
					// give enough space to to not overlap the footer. :(
	
// Auto refresh
	$time = autoRefreshTime(isInProgress($tournamentID, 'bracket'));
	echo "<script>window.onload = function(){autoRefresh({$time});}</script>";
}

include('includes/footer.php');
 
// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
