<?php 
/*******************************************************************************

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Tournament Attendance";
include('includes/header.php');

//if(ALLOW['VIEW_STATS'] == false){
	//pageError('user');
//} else {
{


if(isset($_SESSION['statsAttendanceFilters']) == true){

	$filters = $_SESSION['statsAttendanceFilters'];

	if($filters['countryIso2'] != null){
		$countryClause = "AND countryIso2 = '{$filters['countryIso2']}'";
	} else {
		$countryClause = '';
	}

	$sql = "SELECT tournamentID, eventID, numParticipants
			FROM eventTournaments
			INNER JOIN systemEvents USING(eventID)
			WHERE DATE(eventEndDate) > '{$filters['startDate']}'
			AND DATE(eventStartDate) < '{$filters['endDate']}'
			{$countryClause}
			ORDER BY eventStartDate ASC";
	$list = (array)mysqlQuery($sql, ASSOC);

	$tIDs = [];
	foreach($list as $l){
		$tIDs[] = $l['tournamentID'];
		$placings[$l['tournamentID']][1] = null;
		$placings[$l['tournamentID']][2] = null;
		$placings[$l['tournamentID']][3] = null;
	}
	$tIDs = implode2int($tIDs);
//1077



	$sql = "SELECT tournamentID, firstName, lastName, placing
			FROM eventPlacings
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN systemRoster USING(systemRosterID)
			WHERE tournamentID IN({$tIDs})
			AND placing IN(1,2,3)
			AND highBound IS NULL
			AND lowBound IS NULL
			ORDER BY tournamentID ASC, placing ASC";
	$placingList = (array)mysqlQuery($sql, ASSOC);

	$tID = 0;
	$errorIn = [];
	$placeNext = 4;

	foreach($placingList as $p){

		if($p['tournamentID'] != $tID){

			if($placeNext != 4){
				$errorIn[$tID] = $tID;
			}

			$tID = $p['tournamentID'];
			$placeNext = 1;
		}

		if($p['placing'] == $placeNext){
			$placings[$tID][$placeNext] = $p['lastName'].", ".$p['firstName'];
			$placeNext++;
		} else {
			$errorIn[$tID] = $tID;
		}


	}

	foreach($errorIn as $tID){
		$placings[$tID][1] = null;
		$placings[$tID][2] = null;
		$placings[$tID][3] = null;
	}
	

	
} else {

	$filters['countryIso2'] = '';
	$filters['startDate'] = '';
	$filters['endDate'] = '';
	$list = [];
}



// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>

<form method="POST">
<div class='input-group'>
	<span class="input-group-label">Date Range:</span>
	<input type='date' class='input-group-field' name='statsAttendanceFilters[startDate]' value='<?=$filters['startDate']?>'>
	<input type='date' class='input-group-field' name='statsAttendanceFilters[endDate]' value='<?=$filters['endDate']?>'>
	<span class="input-group-label">Country:</span>
	<?=selectCountry('statsAttendanceFilters[countryIso2]',$filters['countryIso2'],null,'input-group-field',false)?>
	<div class='input-group-button'>
		<button class='button' name='formName' value='statsAttendanceFilters'>Submit</button>
	</div>
	
</div>
</form>

<table>
	<tr>
		<th>Event</th>
		<th>Tournament</th>
		<th>#</th>
		<th>1st</th>
		<th>2nd</th>
		<th>3rd</th>
	</tr>
<?php foreach($list as $t):?>
	<tr>
		<td><?=getEventName($t['eventID'])?></td>
		<td><?=getTournamentName($t['tournamentID'])?></td>
		<td><?=$t['numParticipants']?></td>
		<td><?=$placings[$t['tournamentID']][1]?></td>
		<td><?=$placings[$t['tournamentID']][2]?></td>
		<td><?=$placings[$t['tournamentID']][3]?></td>
	</tr>
<?php endforeach ?>
</table>


<?php }

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

/******************************************************************************/

function showEventRosterGrid($eventID){

	$eventID = (int)$eventID;
	if($eventID == 0){ return; }


	$sql = "SELECT rosterID, firstName, lastName
			FROM eventRoster
			INNER JOIN systemRoster USING(systemRosterID)
			WHERE eventID = {$eventID}
			ORDER BY lastName ASC";
	$roster = mysqlQuery($sql, KEY, 'rosterID');


	$sql = "SELECT tournamentID,
				(SELECT count(*)
				FROM eventTournamentRoster as eTR2
				WHERE eTR2.tournamentID = eT.tournamentID) as numParticipants
			FROM eventTournaments AS eT
			WHERE eventID = {$eventID}
			ORDER BY numParticipants DESC";
	$tournaments = mysqlQuery($sql, KEY_SINGLES, 'tournamentID', 'numParticipants');

	$sql = "SELECT rosterID, tournamentID
			FROM eventTournamentRoster
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE eventID = {$eventID}";
	$allEntries = mysqlQuery($sql, ASSOC);

	$entries = [];
	foreach($allEntries as $entry){
		$entries[$entry['rosterID']][$entry['tournamentID']] = true;
	}

?>

	<table>
		<tr>
			<th>Name</th>
			<?php foreach($tournaments as $tournamentID => $numParticipants): ?>
				<th><?=getTournamentName($tournamentID)?></th>
			<?php endforeach ?>
		</tr>
		<tr>
			<th><?=count($roster)?></th>
			<?php foreach($tournaments as $tournamentID => $numParticipants): ?>
				<th ><?=$numParticipants?></th>
			<?php endforeach ?>
		</tr>
		<?php foreach($roster as $rosterID => $r):?>
			<tr>
				<td style="white-space: nowrap;"><?=$r['lastName']?>, <?=$r['firstName']?></td>
			
				<?php foreach($tournaments as $tournamentID => $numParticipants): ?>
					
					<?php if(isset($entries[$rosterID][$tournamentID])):?>
						<td style='background: black;'>
							1
						</td>
					<?php else: ?>
						<td></td>
					<?php endif ?>
					
				<?php endforeach ?>
			</tr>
		<?php endforeach ?>
	</table>

<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

