<?php
/*******************************************************************************
	Participants Schedule

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Total Staffing Hours';
$jsIncludes[] = 'logistics_management_scripts.js';
$includeTournamentName = false;
$hideEventNav = true;
$jsIncludes[] = 'roster_management_scripts.js';

// This is necessary for the custom table-sortability
$createSortableDataTable[] = ['particiantsScheduleSummaryTable',100];
$createSortableDataTable[] = ['particiantsScheduleMatchesTable',100];
$createSortableDataTable[] = ['particiantsTournamentsMatchesTable',100];
$createSortableDataTable[] = ['table-staff-shifts-full',100];

include('includes/header.php');

if($_SESSION['isMetaEvent'] == true){
	redirect('infoSummary.php');
}

$tournamentID = $_SESSION['tournamentID'];

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif (ALLOW['VIEW_SCHEDULE'] == false){
	displayAlert("Event is still upcoming<BR>Schedule not yet released");
} else {

	if(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false){
		$matchesActive = 'is-active';
	} else {
		$matchesActive = '';
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Tabs -->
	<ul class="tabs" data-tabs id="staffHours-tab">

		<?php if(ALLOW['EVENT_MANAGEMENT'] == true || ALLOW['VIEW_SETTINGS'] == true):?>
			<li class="tabs-title">
				<a data-tabs-target="panel-byHour" href="#panel-byHour">
					Show by Scheduled Hours
				</a>
			</li>
		<?php endif ?>

		<li class="tabs-title <?=$matchesActive?>">
			<a data-tabs-target="panel-byMatch" href="#panel-byMatch">
				Confirmed Matches by Role
			</a>
		</li>

		<li class="tabs-title">
			<a data-tabs-target="panel-byTournament" href="#panel-byTournament">
				Confirmed Matches by Tournament
			</a>
		</li>

		<?php if(ALLOW['EVENT_MANAGEMENT'] == true || ALLOW['VIEW_SETTINGS'] == true):?>
			<li class="tabs-title">
				<a data-tabs-target="panel-list-all" href="#panel-list-all">
					Full List Of (Scheduled) Staff Shifts
				</a>
			</li>
		<?php endif ?>

	</ul>

<!-- Tab Content -->
	<div class="tabs-content" data-tabs-content="staffHours-tab">

		<?php if(ALLOW['EVENT_MANAGEMENT'] == true || ALLOW['VIEW_SETTINGS'] == true):?>
			<div class="tabs-panel" id="panel-byHour">
				<?php displayStaffingHoursSummary();?>
			</div>
		<?php endif ?>

		<div class="tabs-panel <?=$matchesActive?>" id="panel-byMatch">
			<?php displayStaffingMatchesSummary();?>
		</div>

		<div class="tabs-panel" id="panel-byTournament">
			<?php displayStaffingMatchTournamentSummary();?>
		</div>

		<?php if(ALLOW['EVENT_MANAGEMENT'] == true || ALLOW['VIEW_SETTINGS'] == true):?>
			<div class="tabs-panel" id="panel-list-all">
				<?php displayStaffingFullList();?>
			</div>
		<?php endif ?>

	</div>

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

function displayStaffingFullList(){

	$shiftList = logistics_getEventFullShiftList($_SESSION['eventID']);

?>

	<table id='table-staff-shifts-full'>
		<thead>
			<tr>
				<th>Last</th>
				<th>First</th>
				<th>School</th>
				<th>Day</th>
				<th>Location</th>
				<th>Role</th>
				<th>Minutes</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($shiftList as $s): ?>
				<tr>
					<td><?=$s['lastName']?></td>
					<td><?=$s['firstName']?></td>
					<td><?=$s['schoolFullName']?></td>
					<td>Day #<?=$s['dayNum']?></td>
					<td><?=$s['locationName']?></td>
					<td><?=$s['roleName']?></td>
					<td><?=$s['length']?></td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>


<?php
}

/******************************************************************************/

function displayStaffingMatchesSummary(){

	$matchList = logistics_getEventStaffingMatches($_SESSION['eventID']);
	$multipliers = logistics_getMatchMultipliers($_SESSION['eventID']);

	if($matchList == null){
		displayAlert("No Data Recorded.");
		return;
	}

	reset($matchList);
	$firstIndex = key($matchList);

	$hasMultipliers = false;
	$str = "The following roles have multipliers:
		<ul>";
		foreach($multipliers as $roleID => $multiplier){
			if($multiplier == 1){continue;}
			$hasMultipliers = true;
			$str .= "<li>".logistics_getRoleName($roleID). " (x{$multiplier})</li>";
		}
	$str .= "</ul>";

	?>


	<table id="particiantsScheduleMatchesTable" class="display" >
		<thead>
		<tr>
				<th>
					Name
				</th>
			<?php foreach($matchList[$firstIndex]['roleMatches'] as $roleID => $dummy): ?>
				<th>
					<?=logistics_getRoleName($roleID)?>
				</th>
			<?php endforeach ?>

				<th>
					<?php if($hasMultipliers == false):?>
						Total
					<?php else: ?>
						Total*
						<?=tooltip($str)?>
					<?php endif ?>

				</th>
		</tr>
		</thead>


		<?php foreach($matchList as $rosterID => $staffData):?>
			<tr>
				<td>
					<?=getFighterName($rosterID)?>
				</td>

				<?php foreach($staffData['roleMatches'] as $matches):
					if($matches == 0){
						$matches = '';
					}
					?>
					<td >
						<?=$matches?>
					</td>
				<?php endforeach ?>

				<td>
					<?=$staffData['scaledMatches']?>
				</td>
			</tr>
		<?php endforeach ?>

	</table>

<?php
}

/******************************************************************************/

function displayStaffingMatchTournamentSummary(){

	$tournamentIDs = getEventTournaments($_SESSION['eventID']);


	$staffList = [];
	$tournamentMatches = [];
	$tournamentNotEmpty = [];
	$headings = [];


	foreach($tournamentIDs as $id){
		$headings[$id] = getTournamentName($id);
	}
	$headings[0] = "TOTAL";


	$matches = logistics_getEventStaffingMatchesByTournament($tournamentIDs);

	foreach($matches as $m){
		$rosterID = $m['rosterID'];
		if(isset($staffList[$rosterID]['name']) == false){
			$staffList[$rosterID]['name'] = getFighterName($rosterID);
		}

		$tournamentID = $m['tournamentID'];
		if(isset($tournamentMatches[$rosterID][$tournamentID]) == false){
			$tournamentMatches[$rosterID][$tournamentID] = 1;
		} else {
			$tournamentMatches[$rosterID][$tournamentID]++;
		}
		@$tournamentMatches[$rosterID][0]++;

		$tournamentNotEmpty[$tournamentID] = true;

	}


	// Don't display anything if there is no staff data entered.
	if($tournamentNotEmpty == []){
		displayAlert("No Data Recorded.");
		return;
	}


	// Don't display tournaments that didn't have any staff registered.
	foreach($headings as $tournamentID => $data){

		if(isset($tournamentNotEmpty[$tournamentID]) == false && $tournamentID != 0){
			unset($headings[$tournamentID]);
		}

	}

?>

	<i>This table displays data from all roles equally (table, judge, director, etc.)</i>

	<table id="particiantsTournamentsMatchesTable" class="display" >
		<thead>
			<tr>
				<th style='font-size:0.7em'>Staff Name</th>
				<?php foreach($headings as $tName):?>
					<th style='font-size:0.7em'><?=$tName?></th>
				<?php endforeach ?>
			</tr>
		</thead>

		<tbody>
			<?php foreach($staffList as $rosterID => $s):?>
				<tr>
					<td><?=$s['name']?></td>
					<?php foreach($headings as $tID => $tName){
						echo "<td>";
						if(isset($tournamentMatches[$rosterID][$tID])){
							echo $tournamentMatches[$rosterID][$tID];
						}
						echo "</td>";
					}?>

				</tr>
			<?php endforeach ?>
		</tbody>

	</table>


<?php
}

/******************************************************************************/


function displayStaffingHoursSummary(){
	$shiftList = logistics_getEventStaffingMinutes($_SESSION['eventID']);

	if($shiftList == null){
		displayAlert("No Data Recorded.");
		return;
	}

	reset($shiftList);
	$firstIndex = key($shiftList);

	?>

<!-- Code to show/hide certain parts of the schedule -->
	Showing:

	<!-- Time in each role -->
	<a class='button tiny role-hours-on-button secondary hollow'
		onclick="toggleWithButton('role-hours',true)">
		Role Hours
	</a>
	<a class='button tiny role-hours secondary' style='display:none'
		onclick="toggleWithButton('role-hours',false)">
		Role Hours
	</a>

	<!-- Confirmed hours button -->

	<a class='button tiny confirmed-hours-on-button success hollow'
		onclick="toggleWithButton('confirmed-hours',true)">
		Confirmed Hours
	</a>
		<a class='button tiny confirmed-hours success' style='display:none'
		onclick="toggleWithButton('confirmed-hours',false)">
		Confirmed Hours
	</a>

	<!-- Scheduled hours button -->
	<a class='button tiny scheduled-hours-on-button secondary hollow ' style='display:none'
		onclick="toggleWithButton('scheduled-hours',true)">
		Scheduled Hours
	</a>
	<a class='button tiny scheduled-hours secondary'
		onclick="toggleWithButton('scheduled-hours',false)">
		Scheduled Hours
	</a>

	<!-- Target hours button -->
	<a class='button tiny target-hours-on-button hollow' style='display:none'
		onclick="toggleWithButton('target-hours',true)">
		Target Hours
	</a>
	<a class='button tiny target-hours white-text'
		onclick="toggleWithButton('target-hours',false)">
		Target Hours
	</a>

	<!-- Unscheduled hours button -->
	<a class='button tiny unscheduled-hours-on-button warning hollow'  style='display:none'
		onclick="toggleWithButton('unscheduled-hours',true)">
		Unscheduled Hours
	</a>
	<a class='button tiny unscheduled-hours warning'
		onclick="toggleWithButton('unscheduled-hours',false)">
		Unscheduled Hours
	</a>

	<!-- Target hours button -->
	<a class='button tiny unconfirmed-hours-on-button alert hollow'
		onclick="toggleWithButton('unconfirmed-hours',true)">
		Unconfirmed Hours
	</a>
	<a class='button tiny unconfirmed-hours alert' style='display:none'
		onclick="toggleWithButton('unconfirmed-hours',false)">
		Unconfirmed Hours
	</a>


	<table id="particiantsScheduleSummaryTable" class="display" >
		<thead>
		<tr>
				<th>
					Name
				</th>
			<?php foreach($shiftList[$firstIndex]['staffingMinutes'] as $roleID => $dummy): ?>
				<th class='role-hours hidden'>
					<?=logistics_getRoleName($roleID)?>
				</th>
			<?php endforeach ?>

				<th class='confirmed-hours hidden'>
					Confirmed Hours
				</th>
				<th class='scheduled-hours'>
					Scheduled Hours
				</th>
				<th class='target-hours'>
					Target Hours
				</th>
				<th class='unscheduled-hours'>
					Hours-To-Target <?=tooltip("Hours that need to be scheduled to meet the target.")?>
				</th>
				<th class='unconfirmed-hours hidden'>
					Unconfirmed Hours <?=tooltip("Staffing shifts that have not been checked in.")?>
				</th>
		</tr>
		</thead>


		<?php foreach($shiftList as $staffData):?>
			<tr>
				<td>
					<a onclick="goToPersonalSchedule('<?=$staffData['rosterID']?>')">
						<?=getFighterName($staffData['rosterID'])?>
					</a>
				</td>

				<?php foreach($staffData['staffingMinutes'] as $length): ?>
					<td class='role-hours hidden'>
						<?php if($length != 0): ?>
							<?=round($length/60,1)?>
						<?php endif ?>

					</td>
				<?php endforeach ?>

				<td class='confirmed-hours hidden'>
					<?=round($staffData['confirmedTotalMinutes']/60,1)?>
				</td>
				<td class='scheduled-hours'>
					<?=round($staffData['totalMinutes']/60,1)?>
				</td>
				<td class='target-hours'>
					 <?=$staffData['staffHoursTarget']?>
				</td>
				<td class='unscheduled-hours'>
					 <?=round($staffData['staffMinutesUnscheduled']/60,1)?>
				</td>
				<td class='unconfirmed-hours hidden'>
					<?=round($staffData['staffMinutesUnconfirmed']/60,1)?>
				</td>
			</tr>
		<?php endforeach ?>

	</table>

<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
