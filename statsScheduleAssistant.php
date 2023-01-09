<?php
/*******************************************************************************

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Match Length';
$hideEventNav = true;
$hidePageTitle = true;
$jsIncludes[] = 'stats_scripts.js';

include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} else {

	$numDataSeries = countNumDataSeries();

	$eventID = $_SESSION['matchID'];

	$seedData = [];
	$matchLengthIntegral = 0;
	$matchLengthCount = 0;
	$changeLengthIntegral = 0;
	$changeLengthCount = 0;

	for($i = 0; $i < $numDataSeries; $i++){
		$seedData = getTournamentExchangeTimeData($_SESSION['activeStatsItems']['tournamentIDs'][$i]);

		foreach($seedData['match']['length']['data'] as $time){
			$matchLengthIntegral += $time;
			$matchLengthCount++;
		}

		foreach($seedData['match']['change']['data'] as $time){
			$changeLengthIntegral += $time;
			$changeLengthCount++;
		}
	}

	if($matchLengthCount != 0){
		$matchLength = round($matchLengthIntegral/$matchLengthCount);
	} else {
		$matchLength = 0;
	}

	if($changeLengthCount != 0){
		$changeLength = round($changeLengthIntegral/$changeLengthCount);
	} else {
		$changeLength = 0;
	}
	$totalMatchLength = $matchLength + $changeLength;

	if(ALLOW['EVENT_SCOREKEEP'] == true){
		$tournamentIDs = (array)getEventTournaments($eventID);
	} else {
		$tournamentIDs = [];
	}
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Warning Message ------------------------------------------------------------------------>

	<div class='callout cell primary' data-closable>
		Welcome to an exciting new feature that is <b>under construction</b>.<ol>
		<li>This calculator will fit to your desired pool size, and create pools of +1 over the size to make up people who don't fit. If that isn't possible for the parameters you enter the timer can't be calculated. <i>eg: You can't make pools of 4-5 people for an 11 person tournament.</i></li>
		<li>The calculator will take into account ring stacking. <i>eg: If you have 3 pools in 2 rings it takes as long as 4 pools.</i> </li>
		<li>Bracket calculations not implemented yet. If you chose a pool size of 3, and change the number of fighters to the brackets size, it is a decent estimation.</li>
		<li>I think I did all the math right, but it hasn't been thoroughly tested.</li>

		<button class='close-button' aria-label='Dismiss alert' type='button' data-close>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>

	<div class='cell'>

		<div class='input-group no-bottom'>
			<span class='input-group-label no-bottom'>Average Match Length & Changeover [sec]: </span>
			<input class='input-group-field no-bottom' type=number id='time-per-match' 
				value='<?=$totalMatchLength?>' placeholder='<?=$totalMatchLength?>'
				onchange="statsUpdateTournamentTimeCalc()">
		</div>
		
	</div>

	<a onclick="$('#time-source-div').toggle()"><i>Use Historical Data</i></a>
	<div class="callout hidden" id='time-source-div'>
		<?=selectDataSeriesTournaments()?>
		<i>Note: This uses an average of whatever is in the database for these events, whether the data is good or bad. You have been warned.
		<BR> (If you want to look at the data you can use <a href="statsMatchLength.php">Match Timings</a>.</i>
	</div>

	<div class='cell'>

		<div class='input-group no-bottom'>
			<span class='input-group-label no-bottom'>
				Delay between pools [sec]: 
				<?=tooltip("There is no historical data import for this. You are on your own to estimate.")?>
			</span>
			<input class='input-group-field no-bottom' type=number id='time-between-pools' 
				value='0' placeholder='???'
				onchange="statsUpdateTournamentTimeCalc()">
		</div>
		
	</div>

	<p id='time-calculation-error' class='red-text'>
	</p>

	<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
		<div class='grid-x grid-margin-x'>
			<div class='large-12'><u>Entries for <?=getEventName($_SESSION['eventID'])?></u></div>
		<?php foreach($tournamentIDs as $tournamentID): ?>

			<div class='large-4 medium-6 cell'>
				<b><?=getNumTournamentEntries($tournamentID)?></b> <?=getTournamentName($tournamentID)?>
			</div>
		<?php endforeach ?>
		</div>
	<?php endif ?>

	<table>

		<tr>
			<th># Fighters</th>
			<th>Pool Size</th>
			<th>Pools</th>
			<th>Fights</th>
			<th>Rings</th>
			<th>Total Time [hrs]</th>
		</tr>

		
		<tr>

			<td>
				<input class='no-bottom' type=number id='t-time-calc-num-fighters'
				value=""
				placeholder=""
				onchange="statsUpdateTournamentTimeCalc()">
			</td>

			<td>
				<input class='no-bottom' type=number id='t-time-calc-pool-size'
				onchange="statsUpdateTournamentTimeCalc()">
			</td>

			<td id='t-time-calc-num-pools'>-</td>
			<td id='t-time-calc-num-fights'>-</td>

			<td>
				<input class='no-bottom' type=number id='t-time-calc-num-rings'
				onchange="statsUpdateTournamentTimeCalc()">
			</td>

			<td id='t-time-calc-total-time'>-</td>

		</tr>
		


	</table>



	
<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
