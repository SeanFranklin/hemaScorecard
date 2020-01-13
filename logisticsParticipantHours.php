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
$createSortableDataTable[] = 'particiantsScheduleSummaryTable';
$createSortableDataTable[] = 'particiantsScheduleMatchesTable';

include('includes/header.php');

if($_SESSION['isMetaEvent'] == true){
	redirect('infoSummary.php');
} 

$tournamentID = $_SESSION['tournamentID'];

if($_SESSION['eventID'] == null){
	pageError('event');
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
				Show by Confirmed Matches
			</a>
		</li>

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

	</div>

	

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

function displayStaffingMatchesSummary(){

	$matchList = logistics_getEventStaffingMatches($_SESSION['eventID']);

	if($matchList == null){
		displayAlert("No Data Recorded.");
		return;
	}

	reset($matchList);
	$firstIndex = key($matchList);

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
					Total Matches
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
					<?=$staffData['totalMatches']?>
				</td>
			</tr>
		<?php endforeach ?>

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
