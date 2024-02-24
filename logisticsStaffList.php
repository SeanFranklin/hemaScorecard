<?php
/*******************************************************************************


*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Staff List Full';
$createSortableDataTable[] = ['fullStaffList',100];
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_SCOREKEEP'] == false && ALLOW['VIEW_MATCHES'] == false){
	pageError('user');
} else {

	$eventID = (int)$_SESSION['eventID'];
	$sql =" SELECT rosterID, logisticsRoleID, locationID, dayNum, lSS.startTime, lSS.endTime,
				tournamentID, blockSubtitle
			FROM logisticsStaffShifts
			INNER JOIN logisticsScheduleShifts AS lSS USING(shiftID)
			INNER JOIN logisticsScheduleBlocks USING(blockID)
			WHERE eventID = {$eventID}
			ORDER BY dayNum ASC, lSS.startTime ASC";
	$allStaff = (array)mysqlQuery($sql, ASSOC);

	$tableData = [];
	foreach($allStaff as $s){
		$tmp = [];
		$tmp['day'] = "D".$s['dayNum'];
		$tmp['time'] = min2hr($s['startTime'], false)."-".min2hr($s['endTime']);
		$tmp['name'] = getFighterName($s['rosterID']);
		$tmp['role'] = logistics_getRoleName($s['logisticsRoleID']);
		$tmp['location'] = logistics_getLocationName($s['locationID'], true);
		if((int)$s['tournamentID'] != 0){
			$tmp['tournament'] = getTournamentName($s['tournamentID']);
		} else {
			$tmp['tournament'] = "";
		}
		$tmp['misc'] = $s['blockSubtitle'];
		$tableData[] = $tmp;

	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<table id="fullStaffList" class="display">
		<thead>
			<tr>
				<th>Day</th>
				<th>Time</th>
				<th>Name</th>
				<th>Role</th>
				<th>Location</th>
				<th>Tournament</th>
				<th>Misc</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($tableData as $row): ?>
			<tr>
				<td><?=$row['day']?></td>
				<td><?=$row['time']?></td>
				<td><?=$row['name']?></td>
				<td><?=$row['role']?></td>
				<td><?=$row['location']?></td>
				<td><?=$row['tournament']?></td>
				<td><i><?=$row['misc']?></i></td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>

<?php
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
