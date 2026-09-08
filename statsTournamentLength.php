<?php
/*******************************************************************************

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Tournament Schedule';
$hideEventNav = true;
$hidePageTitle = true;
$jsIncludes[] = 'stats_scripts.js';

include('includes/header.php');


if($_SESSION['eventID'] == 0){

	displayAlert("This page displays information for the currently selected tournament. <BR>
				<b>No event is currently selected.</b> Please navigate to an event and use the upper left hand navigation to select a tournament.", 'warning');

} else if($_SESSION['tournamentID'] == 0){

	displayAlert("This page displays information for the currently selected tournament. <BR>
				<b>No tournament is currently selected.</b> Please use the upper left hand navigation to select the tournament to display.", 'warning');

} else {

	$tournamentID = (int)$_SESSION['tournamentID'];

	if(isset($_SESSION['timezone']) == false){
		$_SESSION['timezone'] = DATABASE_TIMEZONE;
	}

	$timeCorrectionHr = $_SESSION['timezone'] - DATABASE_TIMEZONE;
	$timeCorrectionSec = $timeCorrectionHr * 3600;


	$timeLowest = PHP_INT_MAX;
	$timeHighest = 0;


	// __ Schedule Data _____________________________________________

	$eventID = (int)$_SESSION['eventID'];
	$sql = "SELECT eventStartDate
			FROM systemEvents
			WHERE eventID = {$eventID}";
	$eventStartDate = mysqlQuery($sql, SINGLE, 'eventStartDate');
	$eventStartTime = strtotime($eventStartDate .'UTC');


	$blocksRaw = logistics_getScheduleByTournament($tournamentID);

	$firstDay = 0;
	$scheduleData = [];

	foreach($blocksRaw as $b){
		if($firstDay == 0){
			$firstDay = $b['dayNum'];
		}

		if($b['dayNum'] != $firstDay){
			displayAlert("This feature does not work for tournaments across multiple days (yet). Only the first day of the tournament is displayed.", "alert");
			break;
		}

		$dayTimeOffset = (($firstDay - 1) * 24 * 3600); // Convert to sec
		$timeOffset = $eventStartTime + $dayTimeOffset;


		$temp['timeStart'] = ($b['startTime'] * 60) + $timeOffset;
		$temp['timeEnd'] = ($b['endTime'] * 60) + $timeOffset;
		$temp['name'] = $b['blockSubtitle'];

		if($temp['timeStart'] < $timeLowest){
			$timeLowest = $temp['timeStart'];
		}

		if($temp['timeEnd'] > $timeHighest){
			$timeHighest = $temp['timeEnd'];
		}

		$scheduleData[] = $temp;

	}


	// __ Tournament Time Data _____________________________________________________________

	$sql = "SELECT groupID, matchID, groupSet, locationID, groupName, numFighters, groupType, bracketLevel,
				(SELECT timestamp
					FROM eventExchanges AS eE2
					WHERE eE2.matchID = eM.matchID
					ORDER BY timestamp ASC
					LIMIT 1) AS firstTimestamp,
				(SELECT timestamp
					FROM eventExchanges AS eE3
					WHERE eE3.matchID = eM.matchID
					ORDER BY timestamp DESC
					LIMIT 1) AS lastTimestamp
			FROM eventMatches AS eM
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			ORDER BY groupNumber ASC, firstTimestamp ASC";
	$exch = (array)mysqlQuery($sql, ASSOC);

	$groupData = [];

	foreach($exch as $e){
		$lID = (int)$e['locationID'];
		$gID = (int)$e['groupID'];
		$t1 = strtotime($e['firstTimestamp']);
		$t2 = strtotime($e['lastTimestamp']);


		// __ Condition checks for valid data __________________
		// Protection if there is no time data.
		if($t1 == false || $t2 == false){
			continue;
		}

		// The correction happens after the check against FALSE, because it
		// would convert false to int and make the check non-functional.
		$t1 += $timeCorrectionSec;
		$t2 += $timeCorrectionSec;


		// Don't include the finals matches, as they often happen at a different time.
		if($e['bracketLevel'] == 1){
			continue;
		}

		if($e['groupType'] == 'elim' && $e['bracketLevel'] == 0){
			continue;
		}

		// If the match is more than 12 hours from the first timestamp, ignore it.
		$twelveHr = (12 * 60 * 60);
		if(($t1 - $timeLowest) > $twelveHr || ($t2 - $timeLowest) > $twelveHr){
			continue;
		}


		// __ Record group data ____________________________________________

		// The brackets don't have informative group names by default
		if($e['groupName'] == 'winner'){
			$lID = -1;
			$e['groupName'] = 'Primary Bracket';
		}
		if($e['groupName'] == 'loser'){
			$lID = -2;
			$e['groupName'] = 'Secondary Bracket';
		}

		// Initialize group data
		if(isset($groupData[$lID][$gID]['timeStart']) == false){
			$groupData[$lID][$gID]['groupName'] = $e['groupName']." <i>({$e['numFighters']})</i>";
			$groupData[$lID][$gID]['groupType'] = $e['groupType'];
			$groupData[$lID][$gID]['timeStart'] = $t1;
			$groupData[$lID][$gID]['timeEnd'] = $t2;
			$groupData[$lID][$gID]['location'] = logistics_getLocationName($e['locationID']);
		}

		if($t1 < $groupData[$lID][$gID]['timeStart']){
			$groupData[$lID][$gID]['timeStart'] = $t1;
		}

		if($t2 > $groupData[$lID][$gID]['timeEnd']){
			$groupData[$lID][$gID]['timeEnd'] = $t2;
		}

		$groupData[$lID][$gID]['timeElapsed'] = ($groupData[$lID][$gID]['timeEnd'] - $groupData[$lID][$gID]['timeStart']);


		// __ Set limits to calculate display boundary ________________
		if($t1 < $timeLowest){
			$timeLowest = $t1;
		}

		if($t2 > $timeHighest){
			$timeHighest = $t2;
		}

	}


	// __ Format Data For Display _____________________________________________________________

	// Have the plot start and end at the nearest hour to fully encompass the data range.
	//3600 rounds seconds to the nearest hour
	$display['start'] = $timeLowest - ($timeLowest % 3600);
	$display['end'] = ceil($timeHighest / 3600) * 3600;
	$display['range'] = $display['end'] - $display['start'];

	$columnHeaders = makeColumnHeaderData($display);
	$scheduleRows = makeScheduleRowData($scheduleData, $display);
	$tournamentRows = makeTournamentRowData($groupData, $display);



// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Warning Message ------------------------------------------------------------------------>

	<div class='grid-x grid-margin-x'>

		<div class='callout cell primary large-9' data-closable>
			Welcome to an exciting new feature that is <u>under construction</u>. Let us know if you see weirdness.
			<BR><b>You can mouse-over the blocks for more detailed information.</b> <a onclick="$('#tournament-length-help').toggle()"><i>Tell me more ↓</i>
			</a>

			<div id='tournament-length-help' class='hidden no-bottom'>

				<BR>
				<p>Make sure to adjust the timezone in the upper right to the correct value for the event. The schedule (should) display correctly regardless of the timezone, because the schedule is relative to the start of the event. The match data uses and absolute timestamp.</p>

				<p>Schedule block: blue, Bracket: orange, Pools: red. (The number in the bracket beside the pool name is the number of competitors in the pool.)</p>

				<p><u>Finals are not included</u>. Because the finals matches often take place separately it makes the bracket look incredibly long if you include them. And if they are on another day it completely breaks the display.</p>

				<p>This data is generated using the timestamps saved when the table enters an exchange. The start of the pool is the first exchange recorded, and the end of the pool is the last. This could lead to weirdness if an organizer were to go back and re-open a match to fix things after the tournament is done, as that would add new timestamps to the match.</p>

				<p>Pools that have run in the same ring are in the same row. If no rings have been specified by the organizer each pool will appear in it's own row. If something weird happens like splitting pool matches across rings (or just not fighting it in the ring that is entered in Scorecard) it can make the display look weird with pools overlapping. </p>

			</div>

			<button class='close-button' aria-label='Dismiss alert' type='button' data-close>
				<span aria-hidden='true'>&times;</span>
			</button>
		</div>

		<div class='cell large-3'>
			<?=eventTimezoneInput()?>
		</div>
	</div>


<!-- Content ------------------------------------------------------------------------>


	<font style='font-size:1.7em'><?=getTournamentName($tournamentID)?>, </font>
	<i>(<?=getEventName($eventID)?>)</i>

	<?=displayTimeBlockRow($columnHeaders, 'border-top: 1px solid black')?>


	<?php foreach($scheduleRows as $row):?>
		<?=displayTimeBlockRow($row)?>
	<?php endforeach ?>

	<?php foreach($tournamentRows as $location):?>
		<?=displayTimeBlockRow($location)?>
	<?php endforeach ?>



<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayTimeBlockRow($row, $style = null){

?>

	<div class='psudo-gantt-row ' style='<?=$style?>'>
		<?php foreach($row as $block):?>


			<span title="<?=@$block['hover']?>">
				<div class="psudo-gantt-block" style="--start: <?=$block['pctStart']?>%;
											--end: <?=$block['pctEnd']?>%;
											--color: <?=$block['color']?>;
											white-space: nowrap;
											<?=@$block['style']?>">


						<?=$block['name']?>


				</div>
			</span>

		<?php endforeach ?>

	</div>

<?php
}

/******************************************************************************/

function makeScheduleRowData($scheduleData, $display){

	$retData = [];
	foreach($scheduleData as $bID => $data){

		$start = $data['timeStart'] - $display['start'];
		$end = $data['timeEnd'] - $display['start'];

		$startSec = $data['timeStart'] % (3600 * 24);
		$startHr = min2hr($startSec/60, false, true);

		$endSec= $data['timeEnd'] % (3600 * 24);
		$endHr = min2hr($endSec/60, false, true);

		$durationSec = $endSec - $startSec;
		$duration = round($durationSec/60);


		$retData[$bID][0]['pctStart'] = round(100*$start/$display['range']);
		$retData[$bID][0]['pctEnd'] = round(100*$end/$display['range']);
		$retData[$bID][0]['name'] = $data['name'];
		$retData[$bID][0]['color'] = "#82C8E5";
		$retData[$bID][0]['style'] = "box-shadow: 4px 4px 5px 1px #666; border-left:1px solid grey";
		$retData[$bID][0]['hover'] = "{$startHr} - {$endHr} ({$duration} min)";

	}


	return ($retData);
}

/******************************************************************************/

function makeTournamentRowData($groupData, $display){

	$tournamentRows = [];
	foreach($groupData as $lID => $location){

		foreach($location as $gID => $group){

			$start = $group['timeStart'] - $display['start'];
			$end = $group['timeEnd'] - $display['start'];

			if($lID == 0 && $group['groupType'] != 'elim'){
				$i = $gID;
			} else {
				$i = $lID;
			}

			$startSec = $group['timeStart'] % (3600 * 24);
			$startHr = min2hr($startSec/60, false, true);

			$endSec= $group['timeEnd'] % (3600 * 24);
			$endHr = min2hr($endSec/60, false, true);

			$duration = round($group['timeElapsed']/60);

			$tournamentRows[$i][$gID]['pctStart'] = round(100*$start/$display['range']);
			$tournamentRows[$i][$gID]['pctEnd'] = round(100*$end/$display['range']);
			$tournamentRows[$i][$gID]['name'] = $group['groupName'];

			$tournamentRows[$i][$gID]['style'] = "box-shadow: 4px 4px 5px 1px #666; border-left:1px solid grey";
			$tournamentRows[$i][$gID]['hover'] = "{$startHr} - {$endHr} ({$duration} min, {$group['location']})";


			if($group['groupType'] == 'elim'){
				$tournamentRows[$i][$gID]['color'] = "#FFB830";
			} else {
				$tournamentRows[$i][$gID]['color'] = "#FF746C";
			}


		}
	}

	return ($tournamentRows);
}

/******************************************************************************/

function makeColumnHeaderData($display){

	$columnHeaders = [];
	$dt = new DateTime();

	for($i = $display['start']; $i <= $display['end']; $i += 3600){

		$start = $i - $display['start'];
		$end = ($i+3600) - $display['start'];
		$tmp = [];
		$tmp['pctStart'] = round(100*$start/$display['range']);
		$tmp['pctEnd'] = round(100*$end /$display['range']);

		$dt->setTimestamp($i);
		$tmp['name'] = $dt->format('H:i');
		$tmp['color'] = "#CFDFFF";
		$tmp['style'] = "border-left: 1px solid black;   margin-right: 0px;";

		$columnHeaders[] = $tmp;

	}

	return ($columnHeaders);
}

/******************************************************************************/
// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
