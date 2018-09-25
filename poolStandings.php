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

	$teamRoster = getTournamentTeams($tournamentID);
	$fighterRoster = getTournamentFighters($tournamentID,'rosterID','full');

	if(isTeams($tournamentID) == false){
		$showFighters = true;
		$showTeams = false;
	} elseif(isMatchesByTeam($tournamentID) == true){
		$showFighters = false;
		$showTeams = true;
	} else {
		$showFighters = true;
		$showTeams = true;
	}

	
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


	<?php if($showFighters & $showTeams): ?>
		<ul class="tabs" data-tabs id="example-tabs">
			<li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Fighters</a></li>
			<li class="tabs-title"><a data-tabs-target="panel2" href="#panel2">Teams</a></li>
		</ul>

		<div class="tabs-content" data-tabs-content="example-tabs">
			<div class="tabs-panel is-active" id="panel1">
				<?=pool_DisplayResults($tournamentID, $_SESSION['groupSet'], false);?>
			</div>
			<div class="tabs-panel" id="panel2">
				<?=pool_DisplayResults($tournamentID, $_SESSION['groupSet'], true);?>
			</div>
		</div>
	<?php elseif($showTeams): ?>
		<?=pool_DisplayResults($tournamentID, $_SESSION['groupSet'], true);?>
	<?php else: ?>
		<?=pool_DisplayResults($tournamentID, $_SESSION['groupSet'], false);?>
	<?php endif ?>
 
<?php 		
}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
