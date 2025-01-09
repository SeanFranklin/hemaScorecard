<?php
/*******************************************************************************
	Logistics Schedule

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

define("SCHEDULE_TIME_INTERVAL",15); // 15 minutes

$pageName = 'Event Schedule';
$jsIncludes[] = 'logistics_management_scripts.js';
$includeTournamentName = false;
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif (ALLOW['VIEW_SCHEDULE'] == false){
	displayAlert("Event is still upcoming<BR>Schedule not yet released");
} elseif($_SESSION['isMetaEvent'] == true){
	redirect('infoSummary.php');
} else {

	// If they are just viewing the page without management permisions all the forms are locked.
	if(ALLOW['EVENT_MANAGEMENT'] == true){
		$formLock = '';
	} else {
		$formLock = 'disabled';
	}

	$eventLocations = (array)logistics_getEventLocations($_SESSION['eventID']);
	$tournamentIDs = getEventTournaments($_SESSION['eventID']);
	$eventDays = getEventDays($_SESSION['eventID']);
	$conflicts = (array)logistics_getEventScheduleConflicts($_SESSION['eventID']);

	$conflictList = [];
	foreach($conflicts as $conflict){
		$dayNum = $conflict['item1']['dayNum'];
		$conflictList[$dayNum][$conflict['item1']['blockID']] = true;
		$conflictList[$dayNum][$conflict['item2']['blockID']] = true;
	}

	$schedule = logistics_getEventSchedule($_SESSION['eventID']);
	$scheduleData_table = convertScheduleToTableDisplayFormat($schedule, $eventDays, $eventLocations);


	editScheduleBlockBox($schedule,$tournamentIDs,$eventDays,$eventLocations);
	displayBlockDescriptionModal();
	displayScheduleConflicts($conflicts);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<fieldset <?=$formLock?> >
	<form method='POST'>

	<?php if($eventLocations == null): ?>

		<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
			<div class='callout warning'>
				<strong>No Locations Created</strong>
				<BR>You must first create your event <em>Locations</em> (rings, classrooms, etc) to begin creating a schedule.
				<BR><a href='logisticsLocations.php'>Manage Event Locations</a>
			</div>
		<?php else: ?>
			<?=displayAlert("No Schedule Created.")?>
		<?php endif ?>

	<?php else: ?>


		<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
			<a class='button' onclick="logistics_manageScheduleBlock(0)">
				Add Schedule Block
			</a>
		<?php endif ?>

		<?php showHideScheduleBlocks() ?>

		<ul class="tabs" data-tabs id="dayNum-tab">
			<?php foreach($eventDays as $dayNum => $dayName):
				if($dayNum == $_SESSION['dayNum']){
					$class = 'is-active';
				} else {
					$class = '';
				}
				?>
				<li class="tabs-title <?=$class?>">
					<a data-tabs-target="panel<?=$dayNum?>" href="#panel<?=$dayNum?>"
						onclick="updateSession('dayNum',<?=$dayNum?>)">
						Day <?=$dayNum?> (<?=$dayName?>)
					</a>
				</li>
			<?php endforeach ?>
		</ul>


		<div class="tabs-content" data-tabs-content="dayNum-tab">
			<?php foreach($eventDays as $dayNum => $dayName):
				if($dayNum == $_SESSION['dayNum']){
					$class = 'is-active';
				} else {
					$class = '';
				}
				?>
				<div class="tabs-panel <?=$class?>" id="panel<?=$dayNum?>">
					<?=displayScheduleDay_asTable(@$scheduleData_table[$dayNum],  	//Index may not exist,
													$eventLocations, 				// then treat as empty
													@$conflictList[$dayNum])?>
				</div>
			<?php endforeach ?>
		</div>

		</form>
		</fieldset>
	<?php endif ?>


	<?php if(logistics_areMatchStaffUsed($_SESSION['eventID']) == true): ?>
		<a href='logisticsParticipantHours.php'>View Number of Matches Judged</a>
	<?php endif ?>

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

function showHideScheduleBlocks(){
?>

<!-- Code to show/hide certain parts of the schedule -->
<div>
	Showing:

	<?=displayFloorMapButton()?>

	<a class='button tiny' id='sdt-tournament-toggle-button'
		onclick="logistics_sdtToggle('sdt-tournament')">
		Tournament Rings
	</a>

	<a class='button tiny success' id='sdt-workshop-toggle-button'
		onclick="logistics_sdtToggle('sdt-workshop')">
		Classrooms
	</a>

	<a class='button tiny warning hollow' id='sdt-staff-toggle-button'
		onclick="logistics_sdtToggle('sdt-staff')">
		Staffing Locations
	</a>



</div>


<?php
}

/******************************************************************************/

function displayBlockDescriptionModal(){

	if(ALLOW['EVENT_MANAGEMENT'] == true){
		return;
	}

?>
	<div class='reveal medium' id='sbd-modal' data-reveal>

		<?=displayBlockDescription()?>

		<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>

<?
}

/******************************************************************************/

function displayBlockDescription(){

?>
	<h4 id='sbd-title' class='no-bottom'></h4>

	<div>
		<em><span id='sbd-subtitle'></span></em>
	</div>
	<div id='sbd-instructors'></div>
	<div id='sbd-time'></div>
	<div id='sbd-location'></div>
	<HR>
	<div id='sbd-experience'></div>
	<div id='sbd-equipment'></div>
	<div id='sbd-rules'></div>
	<div id='sbd-attribute-hr'></div>
	<div id='sbd-description' style='white-space: pre-wrap'></div>
	<div id='sbd-instructor-bio'></div>
	<div>
		<a id='sbd-link' target="_blank"><span id='sbd-linkDescription'></span></a>
	</div>

	<div id='sbd-staffing'></div>

<?
}

/******************************************************************************/

function editScheduleBlockBox($schedule,$tournamentIDs,$eventDays, $eventLocations){

	if(ALLOW['EVENT_MANAGEMENT'] != true){
		return;
	}
?>

	<div class='reveal large' id='scheduleBlockModal' data-reveal>

<!-- Mode -->
	<div class='callout large-12 cell success'>
	<div class='grid-x grid-margin-x'>
		<div class='large-3 cell'>
		</div>

		<div class='large-6 cell'>

			<select id='blockID' style='text-align-last:center;'
				onchange="logistics_changeScheduleBlock()">
				<option value=0>* Add New Block *</option>

				<?php foreach($schedule as $dayNum => $daySchedule): ?>
					<option disabled>
						Day <?=$dayNum?>
						<?foreach($daySchedule as $block):
							$blockID = $block['blockID'];?>
							<option <?=optionValue($blockID, null)?> >
								<?=logistics_getScheduleBlockName($blockID,'before')?>
							</option>
						<?php endforeach ?>
					</option>
				<?php endforeach ?>

			</select>
		</div>

		<div class='large-3 cell'>
		</div>
	</div>
	</div>

	<ul class="tabs" data-tabs id="schedule-block-tabs">

		<li class="tabs-title is-active">
			<a data-tabs-target="sbd-tab">
				View
			</a>
		</li>


		<li class="tabs-title">
			<a data-tabs-target="sbd-edit">
				Edit
			</a>
		</li>

	</ul>

	<div class="tabs-content" data-tabs-content="schedule-block-tabs">
		<div class="tabs-panel is-active" id="sbd-tab">
			<?=displayBlockDescription()?>
		</div>

		<div class="tabs-panel" id="sbd-edit">
			<?=editScheduleBlockForm($tournamentIDs,$eventDays, $eventLocations)?>

		</div>
	</div>


	<!-- Close button -->
	<button class='close-button' data-close aria-label='Close modal' type='button'>
		<span aria-hidden='true'>&times;</span>
	</button>


	</div>

<?php

}

/******************************************************************************/

function editScheduleBlockForm($tournamentIDs,$eventDays, $eventLocations = null){

	if(ALLOW['EVENT_MANAGEMENT'] != true){
		return;
	}
	$blockTypes = logistics_getBlockTypes();

?>

	<?php if($eventLocations == null): ?>

		Looks like there are no Locations created to hold anything.
		<BR>Please create Locations using <a href='logisticsLocations.php'>Manage Locations</a> first.

	<?php else: ?>

	<form method='POST' id='esb-form'>

	<input type='hidden' name='editScheduleBlock[blockID]' value='0' id='esb-blockID' >
	<input type='hidden' name='formName' value='editScheduleBlock' >
	<input type='hidden' id='esb-numLocationsLoaded' value='0' >
	<input type='hidden' name='editScheduleBlock[eventID]' value='<?=$_SESSION['eventID']?>' >

	<div class='grid-x grid-margin-x'>


	<!-- Type -->
		<div class='input-group large-4 cell'>
			<span class='input-group-label'>
				Block Type:
			</span>
			<select class='input-group-field' id='esb-blockTypeID'
				name='editScheduleBlock[blockTypeID]' required
				onclick="logistics_esbBlockTypeCheck(this)">

				<?php foreach($blockTypes as $blockTypeID => $typeName): ?>
					<option <?=optionValue($blockTypeID, null)?> >
						<?=$typeName?>
					</option>
				<?php endforeach ?>
			</select>
		</div>


	<!-- Tournament -->
		<div class='input-group large-8 cell' id='esb-tournamentID-div'>
			<span class='input-group-label'>
				Tournament:
			</span>
			<select class='input-group-field' id='esb-tournamentID'
				name='editScheduleBlock[tournamentID]'
				onclick="logistics_esbTournamentCheck(this)">

				<option></option>
				<?php foreach($tournamentIDs as $tournamentID): ?>
					<option <?=optionValue($tournamentID, null)?> >
						<?=getTournamentName($tournamentID)?>
					</option>
				<?php endforeach ?>
			</select>
		</div>

	<!-- Block Name -->
		<div class='input-group large-8 cell' id='esb-blockTitle-div' style='display:none'>
			<span class='input-group-label'>
				Block Name:
			</span>
			<input type='text' class='input-group-field' id='esb-blockTitle'
				name='editScheduleBlock[blockTitle]'
				placeholder="eg: 'Not Getting Hit in the Head For Dummies, 'Event Check In'">
		</div>

	<!-- Day -->
		<div class='input-group large-6 cell'>
			<span class='input-group-label'>
				Day:
			</span>
			<select class='input-group-field' id='esb-dayNum'
				name='editScheduleBlock[dayNum]'>
				<?php foreach($eventDays as $dayNum => $dayName): ?>
					<option <?=optionValue($dayNum, null)?> >
						<?=$dayName?>
					</option>
				<?php endforeach ?>
			</select>
		</div>

	<!-- Subtitle -->
		<div class='input-group large-6 cell'>
			<span class='input-group-label'>
				Subtitle:
			</span>
			<input type='text' class='input-group-field' id='esb-blockSubtitle'
				name='editScheduleBlock[blockSubtitle]'
				placeholder='eg: Pools, Eliminations, Bracket 1, etc..'>
		</div>

	<!-- Start Time -->
		<div class='input-group large-6 cell'>
			<span class='input-group-label'>
				Start Time (24hr):
			</span>
			<input type='number' class='input-group-field'
				id='esb-startTimeHour' min=0 max=24
				name='editScheduleBlock[startTimeHour]'>
			<span class='input-group-label'>
				:
			</span>
			<input type='number' class='input-group-field'
				id='esb-startTimeMinute' min=0 max=59
				name='editScheduleBlock[startTimeMinute]' >
		</div>

	<!-- End Time -->
		<div class='input-group large-6 cell'>
			<span class='input-group-label'>
				End Time (24hr):
			</span>
			<input type='number' class='input-group-field'
					id='esb-endTimeHour' min=0 max=23
					name='editScheduleBlock[endTimeHour]'>
			<span class='input-group-label'>
				:
			</span>
			<input type='number' class='input-group-field'
					id='esb-endTimeMinute' min=0 max=59
					name='editScheduleBlock[endTimeMinute]'>
		</div>

	<!-- Desctiption -->

		<div class='input-group large-8 cell'>
			<span class='input-group-label'>
				Description:
			</span>
			<textarea class='input-group-field' id='esb-blockDescription'
				name='editScheduleBlock[blockDescription]' style='overflow: auto;'
				placeholder='Class Description, etc...'></textarea>
		</div>

	<!-- Staffing Shifts -->
		<div class='large-4 cell'>


		<div class='input-group cell' id='esb-numShifts-div'>
			<span class='input-group-label'>
				# Staffing Shifts:
				<?=tooltip("A Staffing Shift is the unit of time you can assign staff to. <BR>
				<u>Example</u><BR>
				If you take a 3 hour tournament block and assign 2 staffing shifts, you will have the option
				to set staff for the first and last 1.5 hour shifts. If you chose 1 shift you can set one group of
				staff for the whole thing.")?>
			</span>
			<input type='number' class='input-group-field' id='esb-numShifts'
				name='editScheduleBlock[numShifts]' min=0 max=12 >
		</div>

		<div class='input-group cell' id='esb-suppressConflicts-div'>
			<span class='input-group-label'>
				Suppress Conflicts:
				<?=tooltip("This will tell the software not to include this block in any sort
							of conflict checks. Each participant in this block will be considered
							to be 'free'.")?>
			</span>
			<div class='switch input-group-field'>
				<input type='hidden' value='0'
						name='editScheduleBlock[suppressConflicts]' >
				<input class='switch-input' type='checkbox'
						value='1'
						id='esb-suppressConflicts'
						name='editScheduleBlock[suppressConflicts]'>
				<label class='switch-paddle' for='esb-suppressConflicts'>
				</label>
			</div>
		</div>

		</div>

	<!-- Block Link -->
		<div class='input-group large-7 cell'>
			<span class='input-group-label'>
				Link:
				<?=tooltip("Use if you want the description to contain a link to an external
							document, such as the tournament rules.")?>
			</span>
			<input type='text' class='input-group-field' id='esb-blockLink'
				name='editScheduleBlock[blockLink]'
				placeholder="You MUST include the 'https://' or this link will not work!">
		</div>

		<div class='input-group large-5 cell'>
			<span class='input-group-label'>
				Link Description:
			</span>
			<input type='text' class='input-group-field' id='esb-blockLinkDescription'
				name='editScheduleBlock[blockLinkDescription]'
				placeholder="eg: 'Rules Document'">
		</div>

	<!-- Block Link -->
		<div class='input-group large-6 cell'>
			<span class='input-group-label'>
				Equipment:
			</span>
			<input type='text' class='input-group-field' id='esb-blockAttributeEquipment'
				name='editScheduleBlock[blockEquipment]'
				placeholder="Optional">
		</div>

		<div class='input-group large-6 cell'>
			<span class='input-group-label'>
				Experience Levels:
			</span>
			<input type='text' class='input-group-field' id='esb-blockAttributeExperience'
				name='editScheduleBlock[blockExperience]'
				placeholder="Optional">
		</div>




	<!-- Locations -->
		<div class='large-12 cell text-center'>
			<HR>
			<h5>You <u>MUST</u> assign at least one location.</h5>
		</div>
		<div class='large-12 cell text-center esb-locationIDs'>
		<div class='cell grid-x grid-margin-x'>

			<?php foreach($eventLocations as $location):
				$locationID = $location['locationID'];
				$classes = 'esb-location-checkbox';
				if($location['hasMatches'] == 0){
					$classes .= ' esb-matches-no hidden';
				} else {
					$classes .= ' esb-matches-yes';
				}
				if($location['hasClasses'] == 0){
					$classes .= ' esb-classes-no';
				} else {
					$classes .= ' esb-classes-yes';
				}
				?>

				<div class='cell large-2 medium-4 small-6 <?=$classes?>'>
				<div class='cell grid-x'>

				<div class='large-12 small-12'>
					<strong><?=$location['locationName']?></strong>
				</div>
				<div class='switch text-center esb-ring-checkbox'>
					<input type='hidden' value='0'
							name='editScheduleBlock[locationIDs][<?=$locationID?>]' >
					<input class='switch-input esb-locationID' type='checkbox'
							value='<?=$locationID?>'
							id='esb-location-<?=$locationID?>'
							name='editScheduleBlock[locationIDs][<?=$locationID?>]'
							onclick="logistics_esbRingCheck(this)" >
					<label class='switch-paddle'
						for='esb-location-<?=$locationID?>'>
					</label>
				</div>

				</div>
				</div>

			<?php endforeach ?>
		</div>
		</div>


		<div class='large-12 cell text-center red-text' id='esb-errorLog'></div>
		<div class='large-12 cell text-center red-text' id='esb-warningLog'></div>

	</div>

	<!-- Submit buttons -->
		<div class='grid-x grid-margin-x'>
			<div class='large-1 medium-0 cell'>
				<!-- Spacing Div -->
			</div>
			<div class='large-10 medium-12 cell'>
			<div class='grid-x grid-margin-x'>


				<a class='button success large-4 medium-4 small-12 cell'
					type='button' onclick="logistics_esbSubmit()" id='esb-submitButton'>
					Add/Update
				</a>
				<a class='button alert medium-4 small-12 cell'
					type='button' data-open="confirmBlockDeleteModal" id='esb-deleteButton'>
					Delete Item
				</a>
				<a class='button secondary medium-4 small-12 cell'  type='button' data-close aria-label='Close modal'>
					Cancel
				</a>
			</div>
			</div>
		</div>

	</form>

	<?php endif ?>

	<div class='reveal small' id='confirmBlockDeleteModal' data-reveal>
		<h3>You are sure?</h3>
		<div class='callout warning'>
			This will delete the schedule block and <strong>all</strong> associated staffing information.
		</div>
		If you just want to keep the block, but remove it from a location, use the checkboxes on the
		previous form.
		<div class='grid-x grid-margin-x'>

			<a class='button alert large-6 small-12 cell'
				type='button' onclick="logistics_esbDeleteSubmit()" id='esb-realDeleteButton'>
				Yes, I want to Delete
			</a>
			<a class='button secondary large-6 small-12 cell'  type='button' data-close aria-label='Close modal'>
				No, Cancel.
			</a>
		</div>
	</div>


<?php
}

/******************************************************************************/

function displayScheduleConflicts($conflicts){

	if($conflicts == null){
		return;
	}

?>

	<?php if(ALLOW['EVENT_MANAGEMENT'] == false): ?>
		<div class='callout alert text-center'>
			<h4>Warning</h4>
			This schedule has been created with ring conflicts (two things in the same ring at the same time.)
			<p class='red-text'>This will completely break the schedule display. Don't trust that things are shown in the correct ring.</p>
			<em>Please bring this (politely) to the attention of the event organizer.</em>
		</div>
	<?php else: ?>
		<?php foreach($conflicts as $conflict): ?>

			<div class='callout alert'>
				<strong>Schedule Conflict Detected</strong>:
				<?=logistics_getLocationName($conflict['locationID'])?>
				,
				Day <?=$conflict['item1']['dayNum']?>
				<BR>
				<li>
					[<?=min2hr($conflict['item1']['startTime'])?>
					-
					<?=min2hr($conflict['item1']['endTime'])?>]
					<?=logistics_getScheduleBlockName($conflict['item1']['blockID'])?>

				</li>

				<li>
					[<?=min2hr($conflict['item2']['startTime'])?>
					-
					<?=min2hr($conflict['item2']['endTime'])?>]
					<?=logistics_getScheduleBlockName($conflict['item2']['blockID'])?>

				</li>

			</div>
		<?php endforeach ?>

		<div class='red-text text-center'>
			<h4>Schedule Conficts will <u>completely</u> break the schedule display and things will show up in the wrong rings!!!</h4>
		</div>

		<div class='text-center'>
			Conflicting items are displayed in <strong class='red-text'>RED</strong> (and may be displayed in incorrect rings.)
		</div>
	<?php endif ?>


<?php
}

/******************************************************************************/

function convertScheduleToTableDisplayFormat($schedule, $eventDays, $eventPlaces){

	$avaliablePlaces = [];
	foreach($eventPlaces as $place){
		$avaliablePlaces[$place['locationID']] = 0;
	}

	$timeLimits = logistics_getEventMaxTimes($_SESSION['eventID']);
	$startTime = $timeLimits['startTime'];
	$endTime = $timeLimits['endTime'];

	if($startTime == null || $endTime == null || $startTime == $endTime){
		return [];
	}

	// Round to fit scheduled time blocks
	$startTime = floor($startTime/SCHEDULE_TIME_INTERVAL)*SCHEDULE_TIME_INTERVAL;
	$endTime = ceil($endTime/SCHEDULE_TIME_INTERVAL)*SCHEDULE_TIME_INTERVAL;


	for($time = $startTime; $time < $endTime; $time += SCHEDULE_TIME_INTERVAL){
		$emptyTimeLine[$time] = $avaliablePlaces;
	}

	foreach($eventDays as $dayNum => $dayName){
		$scheduleData_table[$dayNum] = $emptyTimeLine;

		if(isset($schedule[$dayNum]) == false){
			// Don't try to populate empty days.
			continue;
		}

		$tournamentIDs = logistics_getTournamentsOnDay($_SESSION['eventID'], $dayNum);;

		foreach($schedule[$dayNum] as $block){

			$startTime = floor($block['startTime']/SCHEDULE_TIME_INTERVAL)*SCHEDULE_TIME_INTERVAL;
			$endTime = floor($block['endTime']/SCHEDULE_TIME_INTERVAL)*SCHEDULE_TIME_INTERVAL;
			// End time uses floor because we don't want the block to show up in the time slot it ends just before.

			$length = $endTime- $startTime;
			$itemInfo['numBlocks'] = round($length / SCHEDULE_TIME_INTERVAL,0);
			$itemInfo['startTime'] = $block['startTime'];
			$itemInfo['endTime'] = $block['endTime'];
			$itemInfo['blockID'] = $block['blockID'];
			$itemInfo['blockTypeID'] = $block['blockTypeID'];
			$itemInfo['instructors'] = logistics_getBlockInstructors($block['blockID']);

			if($block['blockTypeID'] == SCHEDULE_BLOCK_TOURNAMENT){
				$itemInfo['name'] = getTournamentName($block['tournamentID']);
				$itemInfo['color'] = getTournamentColor($block['tournamentID'],$tournamentIDs);
			} else {
				$itemInfo['name'] = $block['blockTitle'];
			}
			$itemInfo['subtitle'] = $block['blockSubtitle'];

			foreach($block['locationIDs'] as $locationID){
				$itemInfo['itemType'] = 'new';
				$scheduleData_table[$dayNum][$startTime][$locationID] = $itemInfo;

				$itemInfo['itemType'] = 'skip';
				for($time = $startTime +  SCHEDULE_TIME_INTERVAL;
					$time < $endTime;
					$time +=  SCHEDULE_TIME_INTERVAL){

					$scheduleData_table[$dayNum][$time][$locationID] = $itemInfo;
				}
			}
		}
	}
	return $scheduleData_table;

}

/******************************************************************************/

function displayScheduleDay_asTable($timeLine, $eventPlaces,$conflictList){

	if($timeLine == null){
		return;
	}
	$lIndex = 0;

?>

	<table>
		<tr>
			<th style='border-right: 1px solid black;'>
				Time
			</th>
			<?php foreach($eventPlaces as $place):

				// Code to assign classes to cells to toggle their visibity.
				$lIndex++;
				$class = '';
				if($place['hasMatches'] == 0 && $place['hasClasses'] == 0){
					$stdClasses[$lIndex] = 'sdt-staff';
					$class='hidden ';
				} elseif($place['hasMatches'] == 1 && $place['hasClasses'] == 0){
					$stdClasses[$lIndex] = 'sdt-tournament';
				} elseif($place['hasMatches'] == 0 && $place['hasClasses'] == 1){
					$stdClasses[$lIndex] = 'sdt-workshop';
				} else {
					$stdClasses[$lIndex] = 'sdt-multi';
				}
				$class .= $stdClasses[$lIndex];

				?>
				<th style='border-bottom: 1px solid black;' class='<?=$class?>'>
					<?php if($conflictList == null): ?>
						<?=logistics_getLocationName($place['locationID'])?>
					<?php else: ?>
						????
						<?=tooltip("Schedule conflicts break the table and
									things show up in the wrong places.")?>
					<?php endif ?>

				</th>
			<?php endforeach ?>
		</tr>

		<?php foreach($timeLine as $time => $line):
			$lIndex = 0;
			if(($time % 60) == 0){
				$showTime = min2hr($time);
				$timeClass = "table-top-border";
			} else {
				$showTime = '';
				$timeClass = "";
			}
			?>
			<tr>
				<th style='border-right: 1px solid black' class="<?=$timeClass?>">
					<?=$showTime?>
				</th>

				<?php foreach($line as $locationID => $itemInfo):

					$lIndex++;

					if(isset($itemInfo['itemType']) == false){
						$rowspan = 1;
						$style = '';
						$onclick = '';

						if($stdClasses[$lIndex] == 'sdt-staff'){
							$class = 'sdt-staff hidden';
						} else {
							$class = $stdClasses[$lIndex];
						}
					} elseif($itemInfo['itemType'] == 'skip'){
						continue;
					} else {

						if($stdClasses[$lIndex] == 'sdt-staff'){
							$class = 'sdt-staff hidden';
						} else {
							$class = $stdClasses[$lIndex];
						}

						$rowspan = $itemInfo['numBlocks'];
						$style = "border: solid black 1px; cursor: pointer;";

						if(ALLOW['EVENT_MANAGEMENT'] == true){
							$onclick = "onclick='logistics_manageScheduleBlock({$itemInfo['blockID']})'";
						} else {
							$onclick = "onclick='logistics_displayBlockDescription({$itemInfo['blockID']})'";
						}

						if(isset($conflictList[$itemInfo['blockID']])){
							// There is a schedule conflict with another item highlight the cell
							$color = SCHEDULE_COLOR_CONFLICT;
						} else {
							switch($itemInfo['blockTypeID']){
								case SCHEDULE_BLOCK_TOURNAMENT:
									$color = $itemInfo['color'];
									break;
								case SCHEDULE_BLOCK_WORKSHOP:
									$color = SCHEDULE_COLOR_WORKSHOP;
									break;
								case SCHEDULE_BLOCK_MISC:
									$color = SCHEDULE_COLOR_MISC;
									break;
								default:
									$color = SCHEDULE_COLOR_STAFFING;
							}
						}

						$style .= " background: {$color};";


					}

						?>
					<td rowspan=<?=$rowspan?> style="<?=$style?>" <?=$onclick?> class='<?=$class?>'>
						<?php if(isset($itemInfo['itemType']) != false):?>

							<strong><?=$itemInfo['name']?></strong>

							<?php if($itemInfo['subtitle'] != null):?>
								<BR><em><?=$itemInfo['subtitle']?></em>
							<?php endif ?>

							<?php foreach($itemInfo['instructors'] as $instructor): ?>
								<BR>&#8226;<u><?=$instructor['name']?></u>
							<?php endforeach ?>

							<?php if($itemInfo['startTime'] % SCHEDULE_TIME_INTERVAL != 0): ?>
								<BR>Start: <?=min2hr($itemInfo['startTime'])?>
							<?php endif ?>

							<?php if($itemInfo['endTime'] % SCHEDULE_TIME_INTERVAL != 0): ?>
								<BR>End: <?=min2hr($itemInfo['endTime'])?>
							<?php endif ?>

						<?php endif ?>
					</td>
				<?php endforeach ?>
			</tr>
		<?php endforeach ?>

	</table>

	<?php

}

/******************************************************************************/

/******************************************************************************/

function getTournamentColor($tournamentID, $tournamentIDs){

	$numTournaments = count($tournamentIDs);

	if($numTournaments <= 1){
		return SCHEDULE_COLOR_TOURNAMENT;
	}

	$index = -1;
	foreach($tournamentIDs as $i => $tID){

		if((int)$tID == (int)$tournamentID){
			$index = $i;
			break;
		}
	}

	if($index == -1){
		return SCHEDULE_COLOR_TOURNAMENT;
	}

	$range = 0.2;


	$scale = $range * $index / $numTournaments;
	$scale *= pow(-1,$index);

	$r = scaleColor(substr(SCHEDULE_COLOR_TOURNAMENT,1,2), 1 + $scale);
	$g = scaleColor(substr(SCHEDULE_COLOR_TOURNAMENT,3,2), 1 + $scale);
	$b = scaleColor(substr(SCHEDULE_COLOR_TOURNAMENT,5,2), 1.1 + $scale);

	$color = "#".$r.$g.$b;

	return ($color);

}


/******************************************************************************/

function scaleColor($colorHexIn, $scale){

	$decBase = (int)hexdec($colorHexIn);

	$dec = (int)($scale * $decBase);
	$dec = min($dec, 255);
	$dec = max($dec, 0);

	if($dec < 16){
		$hex = '0'.dechex($dec);
	} else {
		$hex = dechex($dec);
	}

	return ($hex);
}

/******************************************************************************/
// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
