<?php
/*******************************************************************************
	Logistics Schedule

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////



$pageName = 'Event Entry Overlaps';
$jsIncludes[] = 'logistics_management_scripts.js';
$includeTournamentName = false;
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false) {
	pageError('user');
} elseif($_SESSION['isMetaEvent'] == true){
	redirect('infoSummary.php');
} else {

	$list = (array)@$_SESSION['checkTournamentOverlapIDs'];


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?=showTournamentOverlaps($list)?>

	<hr>

	<?=enterTournamentIdsToCheck($list)?>

	<hr>

	<p>
		This tool is meant to help check how many fighters are entered overlapping tournament divisions.
		<BR>Simply check two or more tournaments and it will show you the overlap of competitors between the two.
	</p>


<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

function showTournamentOverlaps($tournamentsToCheck){

	$overlaps = getTournamentOverlaps($tournamentsToCheck);

?>

	<div class='grid-x grid-margin-x'>

	<?php foreach($overlaps as $combo): ?>

		<div class='cell large-3 medium-4' style='border-top:solid black 2px'>

			<div style='background-color: #EAF3FB;'>
				<b >
					<?=getTournamentName($combo['t1ID'])?>
					<BR>
					<?=getTournamentName($combo['t2ID'])?>
				</b>
			</div>

			<ol>
				<?php foreach($combo['rosterIDs'] as $rosterID): ?>
					<li><?=getFighterName($rosterID)?></li>
				<?php endforeach ?>
			</ol>

		</div>

	<?php endforeach ?>

	</div>

<?php
}

/******************************************************************************/

function enterTournamentIdsToCheck($alreadyChecked){

	$tournamentIDs = (array)getEventTournaments($_SESSION['eventID']);

?>
	<form method="POST">

		<div class='grid-x grid-margin-x'>

			<?php foreach($tournamentIDs as $tournamentID): ?>

				<div class='large-4 medium-6 cell'>
					<input type='checkbox' class='no-bottom'
						name='checkTournamentOverlaps[tournamentIDs][<?=$tournamentID?>]' value='<?=$tournamentID?>' <?=chk(isset($alreadyChecked[$tournamentID]))?>>
					<?=getTournamentName($tournamentID)?>
				</div>
			<?php endforeach ?>

			<button class='button' name='formName' value='checkTournamentOverlaps'>
				Update List
			</button>

		</div>

	</form>


<?php

}

/******************************************************************************/
// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
