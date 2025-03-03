<?php
/*******************************************************************************
	Participants Schedule

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Event Schedule';
$jsIncludes[] = 'logistics_management_scripts.js';
$includeTournamentName = false;
$hideEventNav = true;
$jsIncludes[] = 'roster_management_scripts.js';

// This is necessary for the custom table-sortability
$tableJS = "$(document).ready(function() {
				$('#particiantsScheduleSummaryTable').DataTable();
			} );";

include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif (ALLOW['VIEW_SCHEDULE'] == false){
	displayAlert("Event is still upcoming<BR>Schedule not yet released");
} elseif($_SESSION['isMetaEvent'] == true){
	redirect('infoSummary.php');
} else {

	$eventDays = getEventDays($_SESSION['eventID']);

	$eventRoster = getEventRoster();
	if($_SESSION['rosterID'] > 0){
		$schedule = logistics_getParticipantSchedule($_SESSION['rosterID'], $_SESSION['eventID']);
	} else {
		$schedule = null;
	}


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?php

	changeRosterID($eventRoster);

	if(ALLOW['EVENT_MANAGEMENT'] == true || ALLOW['VIEW_SETTINGS'] == true ){
		showStaffingHours($_SESSION['rosterID'], $_SESSION['eventID']);
	}

	if($_SESSION['rosterID'] > 0) {

		displayIndividualSchedule($schedule, $eventDays);

	} else if($_SESSION['rosterID'] == -1) {

		displayFullScheduleDump();

	} else {
		/* Wait for input */
	}

	?>

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

/******************************************************************************/

function displayFullScheduleDump(){

	$dayNames = getEventDays($_SESSION['eventID'], true);
	$roster = getEventRoster();

	foreach($roster as $r){
		$rosterID = $r['rosterID'];
		$rawSchedule = logistics_getParticipantSchedule($rosterID, $_SESSION['eventID']);
		$personalSchedule = [];

		if(isset($rawSchedule['scheduled']) == true){

			$previousEnded = -1;
			$i = 0;
			foreach($rawSchedule['scheduled'] as $s){

				if((int)$s['suppressConflicts'] != 0){
					continue;
				}

				if(	   ($i > 0)
					&& ($s['startTime'] <= $personalSchedule[$i-1]['endTime'])
					&& ($s['dayNum'] == $personalSchedule[$i-1]['dayNum'])
				){
					$personalSchedule[$i-1]['endTime'] = $s['endTime'];
				} else {
					$personalSchedule[$i] = $s;
					$i++;
				}

				$previousEnded = $s['endTime'];

				$previousEnded;
			}

			$participantList[$rosterID] = $personalSchedule;
		} else {
			$participantList[$rosterID] = [];
		}
	}


?>

	<table>
	<?php foreach($participantList as $rosterID => $participant):?>
		<tr>
			<td><?=getFighterName($rosterID)?></td>
			<?php foreach($participant as $s):?>
				<td><?=$dayNames[$s['dayNum']]?>
				<?=min2hr($s['startTime'], false, true)?> - <?=min2hr($s['endTime'], false, true)?></td>
			<?php endforeach ?>
		</tr>
	<?php endforeach?>
	</table>
<?
}

/******************************************************************************/

function displayIndividualSchedule($schedule, $eventDays){

	if($schedule == null){
		echo("<BR><BR><em>No Tournament Entries or Staffing Shifts</em><BR><BR>");
		return;
	}

?>

	<div class='hidden' id='print-schedule-header'>
		Personal Schedule For:
		<h3 class='blue-text no-top no-bottom'><?=getFighterName($_SESSION['rosterID'])?></h3>
		<h4><em><?=$_SESSION['eventName']?></em></h4>
		<HR>
	</div>

	<div class='grid-x grid-margin-x' id='personal-schedule-div'>

	<?php if(isset($schedule['unScheduled']) == true): ?>

		<fieldset class='fieldset large-7 cell'>

			<legend class='no-bottom'>
				<h5 class='no-bottom'>
					Unscheduled Tournaments
					<?=tooltip("Tournaments which have not had a schedule time assigned.")?>
				</h5>
			</legend>


			<?php foreach($schedule['unScheduled'] as $tournamentID): ?>
				<li>
					<?=getTournamentName($tournamentID)?>
				</li>
			<?php endforeach ?>


		</fieldset>
	<?php endif ?>

	<table class='stack cell'>

	<tr class='hide-for-small-only'>
		<th>Type</th>
		<th>Scheduled</th>
		<th>Time</th>
		<th>Location</th>
	</tr>


	<?php if(isset($schedule['scheduled']) == true){
		$dayNum = 0;
		foreach($schedule['scheduled'] as $sItem){
			$dayNum = displayScheduleItem($sItem, $eventDays, $dayNum);
		}
	} ?>

	</table>

	</div>
<?php
}

/******************************************************************************/

function displayScheduleItem($info, $eventDays, $dayNum){


	$name = logistics_getScheduleBlockName($info['blockID']);

	$timeString = '';
	if(count($eventDays) > 1){
		$timeString .= $eventDays[$info['dayNum']].' ';
	}
	$timeString .= min2hr($info['startTime']);
	$timeString .= ' - ';
	$timeString .= min2hr($info['endTime']);
	$roleString = '';
	$locationString = '';

	$class = '';
	if($dayNum != $info['dayNum']){
		$class = 'top-border';
	}

	if(isset($info['shiftID']) == true){

		if($info['blockTypeID'] == SCHEDULE_BLOCK_MISC
			&& $info['logisticsRoleID'] == LOGISTICS_ROLE_PARTICIPANT){

			$typeString = $name;
			$name = '';

		} else {
			$locationString = '';
			$typeString = '<u>Staffing</u>';
			if($info['logisticsRoleID'] != null){
				$roleString = ": <em>".logistics_getRoleName($info['logisticsRoleID']).'</em>';
			} else {
				$roleString = '';
			}
		}

		$locationString = "<u>".logistics_getLocationName($info['locationID'])."</u>";

	} else {
		$typeString = 'Tournament Entry';
	}

	?>

	<tr class='<?=$class?>'>

		<td><?=$typeString?>
		<?=$roleString?></td>

		<td>
		<?php if($name != ''): ?>
			<strong> <?=$name?> </strong>
		<?php endif ?>
		</td>

		<td><?=$timeString?></td>
		<td><?=$locationString?></td>

	</tr>

<?php
	return ($info['dayNum']);
}

/******************************************************************************/

function showStaffingHours($rosterID, $eventID){

	$minutes = logistics_getStaffingMinutes($rosterID, $eventID);
	if($minutes == null){
		return;
	}
	$total = 0;
	?>

	<div class='grid-x grid-margin-x'>
	<fieldset class='large-3 medium-5 fieldset cell'>

		<legend><h5>Staffing Summary</h5></legend>
		<div class='grid-x grid-margin-x'>

		<?php foreach($minutes as $roleID => $minutes):
			$hours = round($minutes/60,1);
			$total += $hours;
			?>

			<div class='small-7 text-right cell'>
				<?=logistics_getRoleName($roleID)?>
			</div>
			<div class='small-5 text-right cell'>
				<?=($hours)?> hours
			</div>

		<?php endforeach ?>


		<div class='small-7 text-right cell'>
			<strong>Total </strong>
		</div>
		<div class='small-5 text-right cell'  style='border-top:1px solid black;'>
			<strong><?=($total)?> hours</strong>
		</div>

		</div>
	</fieldset>
	</div>

	<HR>

<?php
}

/******************************************************************************/

function changeRosterID($eventRoster){
	if(isset($_SESSION['rosterID'])){
		$currentRosterID = $_SESSION['rosterID'];
	} else {
		$currentRosterID =  null;
	}

?>

	<div class='callout cell success text-center'>

	<form method='POST'>
	<input type='hidden' name='formName' value='changeRosterID'>
	<div class='input-group grid-x no-bottom'>
	<span class='input-group-label large-3 medium-4 small-12 text-right'>
		Showing Schedule For:
	</span>
	<select name='rosterID' onchange='this.form.submit()' class='  input-group-field'>

		<?php if($currentRosterID == null): ?>
			<option></option>
		<?php endif ?>

		<?php foreach($eventRoster as $person): ?>
			<option <?=optionValue($person['rosterID'], $currentRosterID)?> >
				<?=getFighterName($person['rosterID'])?>
			</option>
		<?php endforeach ?>

		<?php if(ALLOW['EVENT_MANAGEMENT'] == true || ALLOW['VIEW_SETTINGS'] == true ): ?>
			<option <?=optionValue(-1, $currentRosterID)?>>== Everyone ==</option>
		<?php endif ?>

	</select>

	<?php if($currentRosterID != null): ?>
		<button class='button input-group-button' onclick="popOutSchedule()">
			Print
		</button>
	<?php endif ?>

	</div>
	</form>

	</div>


<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
