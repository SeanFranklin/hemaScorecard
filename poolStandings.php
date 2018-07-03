<?php
/*******************************************************************************
	Pool Standings
	
	Displays the pool standings. The data table is generated in 
	scoringFunctions.php, as each ruleset will have different 
	items to display in the table.
	
	Login
		- STAFF or higher will have the option to have all incomplete 
		matches shown 
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Pool Standings';
$includeTournamentName = true;
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if($tournamentID == null){
	pageError('tournament');
} elseif(!isPools($tournamentID)){
	if(isRounds($tournamentID) && USER_TYPE < USER_SUPER_ADMIN){
		// redirects to the rounds if they happen to go to the pools
		// page while in a rounds tournament
		redirect('roundStandings.php');
	}
	displayAlert("There are no pools for this tournament");
} elseif ((getEventStatus() == 'upcoming' || getEventStatus() == 'hidden') && USER_TYPE < USER_STAFF){
	displayAlert("Event is still upcoming<BR>Pools not yet released");
} else {
	poolSetNavigation();
	
	$incompleteMatches = getTournamentIncompletes($tournamentID,'pool', $_SESSION['groupSet']);
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>
	<?php if($incompleteMatches != null): ?>
		<div class='large-12 callout secondary text-center'>
		<p>All pool matches not yet concluded. <BR>
		Results may be extrapolated based on matches concluded so far.</p>
		<?php if(USER_TYPE >= USER_STAFF): ?>
			<button class='button hollow' onclick="toggle('incompleteMatchesDiv')">
				Show Matches
			</button>
			<div id='incompleteMatchesDiv' class='callout hidden'>
				<?php displayIncompleteMatches($incompleteMatches); // display_functions.php?>
			</div>
		<?php endif ?>	
		</div>			
	<?php endif ?>

<?php 	
// creates the table to display the results 
		pool_DisplayResults($tournamentID, $_SESSION['groupSet']); // 
		
}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
