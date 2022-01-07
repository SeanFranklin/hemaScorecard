<?php
/*******************************************************************************
	Finals Bracket 1
	
	Displays the main/winners bracket, and bracket management
	LOGIN:
		- ADMIN and above can create/delete brackets
		- STAFF and above can add/remove fighters from matches
		- STAFF and above can enable/disable the bracket helper
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Finals Bracket';
$includeTournamentName = true;
$lockedTournamentWarning = true;
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if($tournamentID == null){
	pageError('tournament');
} elseif($_SESSION['formatID'] != FORMAT_MATCH){
	displayAlert("There are no brackets for this tournament format");
} elseif (ALLOW['VIEW_MATCHES'] == false){
	displayAlert("Event is still upcoming<BR>Bracket not yet released");
} else {
	
// Bracket Information
	
	$allBracketInfo = getBracketInformation($tournamentID);
	
	if(isPools($tournamentID)){
		$finalists = getTournamentStandings($tournamentID, null, 'pool', 'advancements');
	} else {
		if(isEntriesByTeam($tournamentID) == false){
			$finalists = getTournamentFighters($tournamentID);
		} else {
			$finalists = getTournamentTeams($tournamentID);
		}

	}

	bracketHelperToggleButton($allBracketInfo, $finalists);


// Bracket Display
	if($allBracketInfo == null){
		displayAlert("No Brackets Created", 'CENTER');
	} else {
		$bracketAdvancements = getBracketAdvancements($allBracketInfo, $finalists);

		// Where the magic happens. Located in displayFunctions.php
		bracket_display($allBracketInfo['winner'],$finalists,'winners',$bracketAdvancements);
	}
	
	
// Bracket Management
	bracket_management($tournamentID,$allBracketInfo, $finalists);
	

// Auto-refresh
	if($allBracketInfo != null){
		$bracketMatches = getBracketMatchesByPosition($allBracketInfo['winner']['groupID']);
		foreach((array)$bracketMatches as $level => $matchesInLevel){
			if($level == 1){ break; }
			foreach((array)$matchesInLevel as $match){
				if($match['winnerID'] == null){$incompletes = true;
				} else { $completes = true; }
				
			}
		}
	
		$time = autoRefreshTime(isInProgress($tournamentID, 'bracket'));
		echo "<script>window.onload = function(){autoRefresh({$time});}</script>";
	}

}

include('includes/footer.php');
 
 
// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
