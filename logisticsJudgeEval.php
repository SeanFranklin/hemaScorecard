<?php
/*******************************************************************************
	Logistics Staff Roster

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Judge Overview';
$includeTournamentName = false;
$hideEventNav = true;
$jsIncludes[] = 'stats_scripts.js';
include('includes/header.php');


if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false) {
	pageError('user');
} elseif($_SESSION['isMetaEvent'] == true){
	redirect('infoSummary.php');
} else {


	$tournamentIDs = $_SESSION['tournamentID'];
	$eventID = $_SESSION['eventID'];

	$roleList = logistics_getRoles();
	$tournamentList = getEventTournaments($_SESSION['eventID']);


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>
	<div class='callout primary'>
		This is a stats feature that is under development. If you have not logged staff into matches then you will only be able to get a lumped summary of everything by using the 'Any' role.
	</div>

	<div class='input-group'>
		<select class='no-bottom input-group-field' id='judge-eval-tournamentID'>
			<option value='0'>-- All Tournaments --</option>
			<?php foreach($tournamentList as $t): ?>
				<option value='<?=$t?>'><?=getTournamentName($t)?></option>
			<?php endforeach ?>

		</select>


		<select class='no-bottom input-group-field' id='judge-eval-roleID'>
			<option value='0'>-- All Roles --</option>
			<?php foreach($roleList as $r): ?>
				<option value='<?=$r['logisticsRoleID']?>'><?=$r['roleName']?></option>
			<?php endforeach ?>

		</select>


		<select class='no-bottom input-group-field' id='judge-eval-dataType'>
			<option value='numExchanges'># Exchanges</option>
			<option value='numMatches'># Matches</option>
			<option value='numPenalties'># Penalties</option>
			<option value='penaltiesPerExch'>Penalties / Exch</option>
			<option value='timeOffTotal'>Time Off Total</option>
			<option value='timeOffAvg'>Time Off / Exch</option>
			<option value='timeOnTotal'>Time On Total</option>
			<option value='timeOnAvg'>Time On / Exch</option>
			<option value='timeAllTotal'>Time Total</option>
			<option value='timeAllAvg'>Time / Exch</option>
			<option value='timeRatio'>Judge Ratio</option>
		</select>

		<select class='no-bottom input-group-field' id='judge-eval-sortByValue'>
			<option value='1'>Sort Descending</option>
			<option value='0'>Sort Name</option>

		</select>

		<a class='button input-group-button' onclick="updateJudgeEval()">Update Plot</a>
	</div>

	<div id='judge-eval-notes'>
	</div>

	 <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

	 <HR>
	 <div id="judge-eval-chart" style='height:500px;'></div>



<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

/******************************************************************************/


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
