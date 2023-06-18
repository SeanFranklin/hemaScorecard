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

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

