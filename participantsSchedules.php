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
} elseif (   (getEventStatus() == 'hidden' || getEventStatus() == 'upcoming') 
		   && (ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false)){
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
		
	}
	?>

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/


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

	<?php if(isset($schedule['scheduled']) == true){
		foreach($schedule['scheduled'] as $sItem){
			displayScheduleItem($sItem, $eventDays);
		}
	} ?>

	</div>
<?php
}

/******************************************************************************/

function displayScheduleItem($info, $eventDays){


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


	if(isset($info['shiftID']) == true){

		if($info['blockTypeID'] == SCHEDULE_BLOCK_MISC
			&& $info['logisticsRoleID'] == LOGISTICS_ROLE_PARTICIPANT){

			$typeString = $name;
			$name = '';
			
		} else {
			$locationString = '';
			$typeString = 'Event Staffing';
			if($info['logisticsRoleID'] != null){
				$roleString = "- <em>".logistics_getRoleName($info['logisticsRoleID']).'</em>';
			} else {
				$roleString = '';
			}
		}

		$locationString = "<u>".logistics_getLocationName($info['locationID'])."</u>";

	} else {
		$typeString = 'Tournament Entry';
	}
		
	?>

	<fieldset class='fieldset large-7 medium-9 cell'>

		<legend class='no-bottom'>
			<h5 class='no-bottom'> 
				<?=$typeString?> 
				<?=$roleString?>
			</h5>
		</legend>

		<?php if($name != ''): ?>
			<strong> <?=$name?> </strong>
			
			<BR>
		<?php endif ?>

		<?=$timeString?><BR>
		<?=$locationString?>

	</fieldset>

<?php
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
