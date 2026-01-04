<?php
/*******************************************************************************


*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Filter Matches By School';
$hidePageTitle = true;
$jsIncludes[] = 'stats_scripts.js';
include('includes/header.php');
$createSortableDataTable[] = ['matchesBySystemRosterID',25];

$eventID = (int)$_SESSION['eventID'];
{



	$HemaRatingsID = (int)hemaRatings_getFighterID($_SESSION['filterForSystemRosterID']);
	if($HemaRatingsID == 0){
		$HemaRatingsID = "<i>not mapped</i>";
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?php if($_SESSION['filterForSystemRosterID'] != 0): ?>
		<form method='POST'>
		<h3>Showing: <b><?=getFighterNameSystem($_SESSION['filterForSystemRosterID'])?></b>
			<?=tooltip("???")?>
			<button class='button' name='formName' value='filterForSystemRosterID'>Clear</button>
			<input type='hidden' name='systemRosterID' value='0'>
		</h3>
		</form>
		[School: <b><?=getFighterSchoolNameSystem($_SESSION['filterForSystemRosterID'])?></b>]
		[ScorecardID: <?=$_SESSION['filterForSystemRosterID']?>]
		[HEMA Ratings ID: <?=$HemaRatingsID?>]
	<?php endif ?>

	<?=changeRosterFilterDropdown()?>


	<ul class="tabs" data-tabs id="exchange-type-tabs">

		<li class="tabs-title ">
			<a data-tabs-target="panel-solo-attendance" >Match History</a>
		</li>
		<li class="tabs-title is-active">

			<a data-tabs-target="panel-solo-stats">Exchange Stats</a>
		</li>

	</ul>


	<div class="tabs-content" data-tabs-content="exchange-type-tabs">

		<div class="tabs-panel " id="panel-solo-attendance">
			<?=showSoloAttendanceData($_SESSION['filterForSystemRosterID'])?>
		</div>

		<div class="tabs-panel is-active" id="panel-solo-stats">
			<?=showSoloMatchData($_SESSION['filterForSystemRosterID'])?>
		</div>

	</div>




<?php

}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/******************************************************************************/

function showSoloMatchData($systemRosterID){




	// Show last year for the first 30 days of the new year.
	$defaultYear = (int)date("Y", strtotime("-30 day"));
	$currentYear = (int)date("Y");


	if(isset($_SESSION['stats']['year']) == true){
		$yearSelected = (int)$_SESSION['stats']['year'];
	} else {
		$yearSelected = $defaultYear;
	}

	if($yearSelected != 0){
		$years = [$yearSelected];
	} else {
		$years = [];
	}

	$weaponIdList = getSoloTournamentWeaponsForYears($systemRosterID, $years);


	if(isset($_SESSION['stats']['weaponID']) == true){


		if(in_array((int)$_SESSION['stats']['weaponID'], $weaponIdList) == false){
			$_SESSION['stats']['weaponID'] = 0;
		}

		$weaponID = (int)$_SESSION['stats']['weaponID'];

	} else {
		$weaponID = 0;
	}


	$placings = getSoloPlacingsForYears($systemRosterID, $years);
	$exchStats = getSoloExchStatsForYears($systemRosterID, $years, $weaponID);

?>


<!-- Pull all the data into a format google charts can use ------------------------------------>
<!--------------------------------------------------------------------------------------------->
	<script>

        var options = {
        	is3D: true,
			chartArea: {
				left: 1,
				bottom: 1,
				right: 1,
				top: 1,
				height: 400,
			},

		};

		var data = [];
		<?php foreach($exchStats['target'] as $i => $t):?>
			data[<?=$i?>] = [];
			data[<?=$i?>]['name'] = '<?=$t['name']?>';
			data[<?=$i?>]['value'] = <?=$t['value']?>;
		<?php endforeach ?>

		google.charts.setOnLoadCallback(function (){drawMultSeries(data, 'solo-target', 'pie', options)});


		var data2 = [];
		<?php foreach($exchStats['type'] as $i => $t):?>
			data2[<?=$i?>] = [];
			data2[<?=$i?>]['name'] = '<?=$t['name']?>';
			data2[<?=$i?>]['value'] = <?=$t['value']?>;
		<?php endforeach ?>

		google.charts.setOnLoadCallback(function (){drawMultSeries(data2, 'solo-type', 'pie', options)});

		var data3 = [];
		<?php foreach($exchStats['exchType'] as $i => $t):?>
			data3[<?=$i?>] = [];
			data3[<?=$i?>]['name'] = '<?=$t['name']?>';
			data3[<?=$i?>]['value'] = <?=$t['value']?>;
		<?php endforeach ?>

		google.charts.setOnLoadCallback(function (){drawMultSeries(data3, 'solo-exchType', 'pie', options)});

	</script>

<!-- Actual layout ---------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------------->




	<div class='grid-x grid-margin-x'>

<!-- Header/Input ---------------------------------------------------------------------------->

		<div class='cell medium-7 callout warning'>
			This reflects what the table at the tournament entered. Different events keep track of things to different levels of detail. For better or for worse.
		</div>
		<div class='cell medium-5 callout'>
			<form method="POST">
				<div class='input-group'>
					<input class='hidden' name='formName' value='statsYear'>
					<span class='input-group-label'>Viewing Year:</span>
					<select class='input-group-field' name='stats[year]'>
						<?php for($i = $currentYear; $i >=  FIRST_YEAR; $i--):?>
							<option <?=optionValue($i, $yearSelected)?>><?=$i?></option>
						<?php endfor?>
						<option <?=optionValue(0, $yearSelected)?>>Everything</option>
					</select>
					<div class="input-group-button">
						<input type="submit" class="button" value="Change Year">
					</div>
				</div>
			</form>
			<form method="POST">
				<div class='input-group  no-bottom'>
					<input class='hidden' name='formName' value='statsWeaponID'>
					<span class='input-group-label no-bottom'>Weapon:</span>
					<select class='input-group-field no-bottom' name='stats[weaponID]'>
						<?php foreach($weaponIdList as $wID):?>
							<option <?=optionValue($wID, $weaponID)?>><?=getTournamentAttributeName($wID)?></option>
						<?php endforeach?>
						<option <?=optionValue(0, $weaponID)?>>Everything</option>
					</select>
					<div class="input-group-button no-bottom">
						<input type="submit" class="button" value="Change Weapon">
					</div>
				</div>
			</form>
		</div>

<!-- Data display ---------------------------------------------------------------------------->
		<div class='cell medium-6 callout'>

			<span style="font-size: 2em">Placings</span>
			<?php foreach($placings as $p): ?>

				<BR><b><?=$p['placing']?><?=numSuffix($p['placing'])?></b><span style='font-size:0.8em'>/<?=$p['numParticipants']?></span>,
				<i><?=getEventName($p['eventID'])?></i>, <?=getTournamentName($p['tournamentID'])?>
			<?php endforeach ?>

			<BR><i style='font-size: 0.8em;'>(If some of your tournament results are missing, it is possible that the event organizer did not finalize the tournament placings.)</i>

		</div>


		<div class='cell medium-6 callout'>
			<span style="font-size: 1.5em">Outcomes</span>
			<div id='solo-exchType'></div>

			<BR><i style='font-size: 0.8em;'>Afterblow for/neutral/against indicates if the fighter got or lost points on an exchange where points were added for both competitors.</i>
			<BR><i style='font-size: 0.8em;'>This breakdown may be incorrect if the table did not properly enter exchanges as doubles/afterblows when they happened.</i>
		</div>

		<div class='cell medium-6 callout'>
			<span style="font-size: 1.5em">Attacks</span>
			<div id='solo-type'></div>

			<BR><i style='font-size: 0.8em;'>Includes data from tournaments where event organizers defined specific actions for the table to use. Generic score data does not include the attack type.</i>
		</div>

		<div class='cell medium-6 callout'>
			<span style="font-size: 1.5em">Targets</span>
			<div id='solo-target'></div>

			<BR><i style='font-size: 0.8em;'>Includes data from tournaments where event organizers defined specific targets for the table to use. Generic score data does not include the attack type.</i>
		</div>


	</div>




<?php
}

/******************************************************************************/

function getSoloTournamentWeaponsForYears($systemRosterID, $years){

	$systemRosterID = (int)$systemRosterID;

	if($years != NULL){
		$yearStr = implode2int($years);
		$yearClause = "AND eventYear IN ({$yearStr})";
	} else {
		$yearClause = "";
	}


	$sql = "SELECT DISTINCT(tournamentWeaponID)
			FROM eventTournamentRoster
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN eventRoster USING(rosterID)
				INNER JOIN systemEvents ON eventTournaments.eventID = systemEvents.eventID
			WHERE systemRosterID = {$systemRosterID}
				{$yearClause}";
	$weaponIDs = (array)mysqlQuery($sql, SINGLES, 'tournamentWeaponID');

	return($weaponIDs);
}

/******************************************************************************/

function getSoloExchStatsForYears($systemRosterID, $years, $weaponID = 0){


	$systemRosterID = (int)$systemRosterID;

	if($years != NULL){
		$yearStr = implode2int($years);
		$yearClause = "AND eventYear IN ({$yearStr})";
	} else {
		$yearClause = "";
	}

	$weaponID = (int)$weaponID;
	if($weaponID != 0){
		$weaponClause = "AND tournamentWeaponID = {$weaponID}";
	} else {
		$weaponClause = "";
	}

	$sql = "SELECT attackText AS name, count(*) AS value
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
				INNER JOIN systemAttacks ON refTarget = attackID
				INNER JOIN eventRoster ON scoringID = rosterID
			WHERE refTarget IS NOT NULL
				AND systemRosterID = {$systemRosterID}
				AND exchangeType IN ('clean','afterblow')
				{$yearClause}
				{$weaponClause}
			GROUP BY refTarget
			ORDER BY value DESC";

	$exchStats['target'] = mySqlQuery($sql, ASSOC);

	$sql = "SELECT attackText AS name, count(*) AS value
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
				INNER JOIN systemAttacks ON refType = attackID
				INNER JOIN eventRoster ON scoringID = rosterID
			WHERE refType IS NOT NULL
				AND systemRosterID = {$systemRosterID}
				AND exchangeType IN ('clean','afterblow')
				{$yearClause}
				{$weaponClause}
			GROUP BY refType
			ORDER BY value DESC";

	$exchStats['type'] = mysqlQuery($sql, ASSOC);

	$sql = "SELECT tournamentWeaponID, exchangeType, eR1.systemRosterID AS scoringSysID, (scoreValue - scoreDeduction) AS netScore
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
				INNER JOIN eventRoster AS eR1 ON scoringID = eR1.rosterID
				INNER JOIN eventRoster AS eR2 ON receivingID = eR2.rosterID
			WHERE (eR1.systemRosterID = {$systemRosterID}
				OR eR2.systemRosterID = {$systemRosterID})
				{$yearClause}
				{$weaponClause}";

	$allExch = (array)mysqlQuery($sql, ASSOC);

	define("EXCH_TYPE_CLEAN_FOR",0);
	define("EXCH_TYPE_CLEAN_AGAINST",1);
	define("EXCH_TYPE_AB_FOR",2);
	define("EXCH_TYPE_AB_NULL",3);
	define("EXCH_TYPE_AB_AGAINST",4);
	define("EXCH_TYPE_DOUBLE",5);

	$exchStats['exchType'][EXCH_TYPE_CLEAN_FOR]['name'] = 'Clean For';
	$exchStats['exchType'][EXCH_TYPE_CLEAN_FOR]['value'] = 0;
	$exchStats['exchType'][EXCH_TYPE_CLEAN_AGAINST]['name'] = 'Clean Against';
	$exchStats['exchType'][EXCH_TYPE_CLEAN_AGAINST]['value'] = 0;
	$exchStats['exchType'][EXCH_TYPE_AB_FOR]['name'] = 'Afterblow For';
	$exchStats['exchType'][EXCH_TYPE_AB_FOR]['value'] = 0;
	$exchStats['exchType'][EXCH_TYPE_AB_NULL]['name'] = 'Afterblow Neutral';
	$exchStats['exchType'][EXCH_TYPE_AB_NULL]['value'] = 0;
	$exchStats['exchType'][EXCH_TYPE_AB_AGAINST]['name'] = 'Afterblow Against';
	$exchStats['exchType'][EXCH_TYPE_AB_AGAINST]['value'] = 0;
	$exchStats['exchType'][EXCH_TYPE_DOUBLE]['name'] = 'Double';
	$exchStats['exchType'][EXCH_TYPE_DOUBLE]['value'] = 0;

	foreach($allExch as $e){

		switch($e['exchangeType']){
			case 'clean':
				if($e['scoringSysID'] == $systemRosterID){
					$exchStats['exchType'][EXCH_TYPE_CLEAN_FOR]['value']++;
				} else {
					$exchStats['exchType'][EXCH_TYPE_CLEAN_AGAINST]['value']++;
				}
				break;
			case 'double':
				$exchStats['exchType'][EXCH_TYPE_DOUBLE]['value']++;
				break;
			case 'afterblow':
				if($e['netScore'] == 0){
					$exchStats['exchType'][EXCH_TYPE_AB_NULL]['value']++;
				} elseif($e['scoringSysID'] == $systemRosterID){
					$exchStats['exchType'][EXCH_TYPE_AB_FOR]['value']++;
				} else {
					$exchStats['exchType'][EXCH_TYPE_AB_AGAINST]['value']++;
				}
				break;
		}

	}

	return ($exchStats);

}

/******************************************************************************/

function getSoloPlacingsForYears($systemRosterID, $years){


	$systemRosterID = (int)$systemRosterID;

	if($years != NULL){
		$yearStr = implode2int($years);
		$yearClause = "AND eventYear IN ({$yearStr})";
	} else {
		$yearClause = "";
	}


	$sql = "SELECT placing, eventTournaments.eventID, tournamentID, numParticipants
			FROM eventPlacings
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
				INNER JOIN eventRoster USING(rosterID)
			WHERE systemRosterID = {$systemRosterID}
				{$yearClause}
			ORDER BY (placing/numParticipants) ASC, numParticipants DESC";

	$placings = (array)mysqlQuery($sql, ASSOC);

	return ($placings);
}

/******************************************************************************/

function showSoloAttendanceData($systemRosterID){

	$attendanceList = (array)getAttendanceBySystemRosterID($systemRosterID);

?>

	<table  id="matchesBySystemRosterID" class="display">
		<thead>
				<th>Year</th>
				<th>Event</th>
				<th>Tournament</th>
				<th>Match</th>
				<th>Fighter1</th>
				<th></th>
				<th></th>
				<th>Fighter2</th>
		</thead>
		<tbody>
		<?php foreach($attendanceList as $event): ?>

			<?php if($event['matches'] == []): ?>
				<tr>
					<td><?=$event['year']?></td>
					<td><?=$event['name']?></td>
					<td>Did not compete</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			<?php endif ?>

			<?php foreach($event['matches'] as $m):

				$name = getGroupName($m['groupID']);

				if($m['groupType'] == 'pool'){
					$name .= ", Match ".$m['matchNumber'];
					$page = 'scoreMatch.php';

				} elseif($m['groupType'] == 'round') {
					$page = 'scorePiece.php';
					$m['fighter2ID'] = 0;
					$m['fighter2Score'] = "";
				} else {
					$name .= ", ".getMatchStageName($m['matchID']);
					$page = 'scoreMatch.php';
				}

				$params = "?e=".$m['eventID'];
				$params .= "&t=".$m['tournamentID'];
				$params .= "&m=".$m['matchID'];
				$nameTag = "<a href='{$page}".$params."'>".$name."</a>";

				$f1class = "text-right";
				$f2class = "text-left";
				if($m['winnerID'] == $m['fighter1ID']){
					$f1class .= " bold";
				}
				if($m['winnerID'] == $m['fighter2ID']){
					$f2class .= " bold";
				}

				?>
				<tr>
					<td><?=$event['year']?></td>
					<td><?=$event['name']?></td>
					<td><?=getTournamentName($m['tournamentID'])?></td>
					<td><?=$nameTag?></td>
					<td class='<?=$f1class?>'><?=getFighterName($m['fighter1ID'])?></td>
					<td class='<?=$f1class?>'><?=$m['fighter1Score']?></td>
					<td class='<?=$f2class?>'><?=$m['fighter2Score']?></td>
					<td class='<?=$f2class?>'><?=getFighterName($m['fighter2ID'])?></td>
				</tr>
			<?php endforeach ?>

		<?php endforeach ?>
		</tbody>
	</table>

<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
