<?php
/*******************************************************************************
	Logistics Staff Assigments
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Logistics Staff';
$includeTournamentName = false;
$hideEventNav = true;
$jsIncludes[] = 'logistics_management_scripts.js';
include('includes/header.php');


if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false) {
	pageError('user');
} elseif(logistics_isTournamentScheduleUsed($_SESSION['eventID']) == false){
	displayAlert("A schedule has not been created for this event.");
}  else {

	// If they are just viewing the page without management permisions all the forms are locked.
	if(ALLOW['EVENT_MANAGEMENT'] == true){
		$formLock = '';
	} else {
		$formLock = 'disabled';
	}
	
	
	$roles = logistics_getRoles();
	$schedule = logistics_getEventSchedule($_SESSION['eventID'],null,true);

	// Shows the rating that the user has assigned each staff member, but only if
	// ratings have been used.
	$showStaffCompetency = logistics_areStaffCompetenciesSet($_SESSION['eventID']);


	$activeIndex = null;	// Choses which panel is open by default
	$blockInfo = [];

	if(isset($_SESSION['blockID']) == false){
		$_SESSION['blockID'] = null;
	}


	if($_SESSION['blockID'] != null){
		$blockInfo = logistics_getScheduleBlockInfo($_SESSION['blockID']);
	}

	if($blockInfo != null){

		$shifts = logistics_getScheduleBlockShifts($blockInfo['blockID']);
		
		if($blockInfo['blockTypeID'] == SCHEDULE_BLOCK_MISC){

			$eventRoster = getEventRoster();
			$staffTemplate = [];

			foreach($eventRoster as $person){
				$rosterID = $person['rosterID'];
				$assignableStaff[$rosterID]['optionName'] = getFighterName($rosterID);
			}


		} else {
			if(logistics_limitStaffConflicts($_SESSION['eventID']) == STAFF_CONFLICTS_NO){
				$assignableStaff = logistics_getAvaliableStaff($_SESSION['eventID']);
			} else {
				$assignableStaff = logistics_getAvaliableStaff($_SESSION['eventID'], 
																$blockInfo['tournamentID'] );
			}

			$staffTemplate = logistics_getStaffTemplate($blockInfo['tournamentID']);

			// Get a count of how much each person has worked
			foreach($assignableStaff as $rosterID => $person){
				$hrsStaffed = logistics_getStaffingMinutes($rosterID, 
															$_SESSION['eventID'], 
															'comb');
				$hrsStaffed = round($hrsStaffed/60,1);
				$assignableStaff[$rosterID]['hrsStaffed'] = $hrsStaffed;
			}

			// Generate a name for each staff member to appear in the <select> options
			foreach($assignableStaff as $rosterID => $staffMember){
				$name = '';
				if($showStaffCompetency == true){
					$name .= "[".$staffMember['staffCompetency']."] ";
				}
				$name .= getFighterName($rosterID);

				$hours = $hours = $staffMember['hrsStaffed'];
				if($staffMember['staffHoursTarget'] !== null){
					$hours .= "/".$staffMember['staffHoursTarget'];
				}
				$name .= " {".$hours." hrs}";

				$assignableStaff[$rosterID]['optionName'] = $name;
			}
		}

		// Choses which panel is open by default
		foreach($shifts as $index => $shiftTime){
			if($activeIndex == null){
				$activeIndex = $index;
			}
			if(isset($_SESSION['shiftIndex']) && $index == $_SESSION['shiftIndex']){
				$activeIndex = $index;
			}
		}

	}

	changeScheduleBlock($blockInfo, $schedule);
	bulkAddBox($roles);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>


	<?php if($_SESSION['blockID'] != null): ?>

<!-- Tabs -->
	<ul class="tabs" data-tabs id="editShifts-tab">
	<?php foreach($shifts as $index => $shiftTime):
		$startTime = min2hr($shiftTime[0]['startTime']);
		$endTime = min2hr($shiftTime[0]['endTime']);
		if($index == $activeIndex){
			$firstTab = 'is-active';
		} else {
			$firstTab = '';
		}
		?>

		<li class="tabs-title <?=$firstTab?>">
			<a data-tabs-target="panel-shift-<?=$index?>" href="#panel-edit-shift-<?=$startTime?>"
				onclick="updateSession('shiftIndex',<?=$index?>)">

				<?=$startTime?> - <?=$endTime?>
			</a>
		</li>

	<?php endforeach ?>
	</ul>

<!-- Panels -->
	<div class="tabs-content" data-tabs-content="editShifts-tab">
	<?php foreach($shifts as $index => $shiftTime):

		$startTime = min2hr($shiftTime[0]['startTime']);
		$endTime = min2hr($shiftTime[0]['endTime']);
		if($index == $activeIndex){
			$firstPanel = 'is-active';
		} else {
			$firstPanel ='';
		}
		?>

		
		<div class="tabs-panel <?=$firstPanel?>" id="panel-shift-<?=$index?>">

		<form method='POST'>

		<button class='button success' <?=$formLock?>
			name='formName' value='editStaffShifts'>
			Update Staff List
		</button>

		<div class='grid-x grid-margin-x'>

			<?php foreach($shiftTime as $shiftInfo):?>
				<div class='large-6 cell'>
				<fieldset class='fieldset' <?=$formLock?> >

					<legend><h4>
						<?=logistics_getLocationName($shiftInfo['locationID'])?>
						<a style='font-size:0.8em' 
							onclick="logistics_bulkAddStaff(<?=$shiftInfo['shiftID']?>)">
							<em>(Bulk Add)</em>
						</a>	
					</h4></legend>

					<?= shiftAssignmentsTable($shiftInfo, $assignableStaff, $roles, $staffTemplate);?>

				</fieldset>
				</div>
			<?php endforeach ?>

		</div>

		<button class='button success' <?=$formLock?>
			name='formName' value='editStaffShifts'>
			Update Staff List
		</button>

		</form>

		</div>

		
		

	<?php endforeach ?>
	</div>

	<?php endif ?>
	

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

function bulkAddBox($roles){

	$tournamentIDs = getEventTournaments($_SESSION['eventID']);

?>
	<div class='reveal' id='bulkStaffAssignBox' data-reveal>
	
		<h4>Bulk Assignment</h4>

		This will add <u>everyone</u> to the shift. Examples:
		<em>
			<li>Assign all staff to training.</li>
			<li>Assign all event participants to the dinner. </li>
			<li>Assign all tournament participants to a rules briefing. </li>
		</em>

		<BR>
		<div class='callout secondary'>
			Please take note:
			<li>This will add everyone regardless of conflicts</li>
			<li>If someone is already added it will not change their role.</li>
			<li>Assigning people as Participants does not count as staffing hours.</li>
		
		</div>

		<form method='POST'>
		<input type='hidden' name='bulkStaffAssign[shiftID]' id='bsa-shiftID'>
		<input type='hidden' name='bulkStaffAssign[eventID]' value='<?=$_SESSION['eventID']?>'>

		<div class='input-group'>
			<span class='input-group-label'>Assign all:</span>
			<select class='input-group-field' required name='bulkStaffAssign[type]'>
				<option disabled selected></option>
				<option value='staff'>Event Staff</option>
				<option value='all'>Event Participants</option>
				<option disabled>---------</option>
				<?php foreach($tournamentIDs as $tournamentID): ?>
					<option value='<?=$tournamentID?>'>
						Registered In: &lt;<?=getTournamentName($tournamentID)?>&gt;
					</option>
				<?php endforeach ?>
			</select>
		</div>

		<div class='input-group'>
			<span class='input-group-label'>Assign as:</span>
			<select class='input-group-field' name='bulkStaffAssign[logisticsRoleID]'>
				<?php foreach($roles as $role):?>
					<option <?=optionValue($role['logisticsRoleID'],LOGISTICS_ROLE_PARTICIPANT)?>>
						<?=$role['roleName']?>
					</option>
				<?php endforeach ?>
			</select>
		</div>

	<!-- Submit buttons -->
		<div class='grid-x grid-margin-x'>

			<button class='button success small-6 cell' name='formName' 
				value='bulkStaffAssign'>
				Bulk Add
			</button>

			<button class='button secondary small-6 cell' data-close aria-label='Close modal' type='button'>
				Cancel
			</button>
		</div>

		
	
		</form>

		<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>

	</div>

<?php
}

/******************************************************************************/

function shiftAssignmentsTable($shiftInfo, $assignableStaff, $roles, $staffTemplate){


	$shiftID = $shiftInfo['shiftID'];
	$emptyShift['rosterID'] = null;
	$emptyShift['logisticsRoleID'] = null;
	$emptyShift['staffShiftID'] = 0;
	$shiftStaff = logistics_getShiftStaff($shiftID);

	$staffingErrors = [];
	if($staffTemplate != null){
		foreach($shiftStaff as $staffShift){
			@$staffOnShift[$staffShift['logisticsRoleID']]++; // Could not exist. Treat as zero.
		}

		
		foreach($staffTemplate as $roleID => $numStaff){
			$onShift = (int)@$staffOnShift[$roleID]; // COuld not be set. Treat as zero.


		
			if($onShift != $numStaff){
				$err['set'] = $onShift;
				$err['template'] = $numStaff;
				$err['name'] = logistics_getRoleName($roleID);
				$staffingErrors[] = $err;
			}
		}
	}

	$checkConflicts = logistics_limitStaffConflicts($_SESSION['eventID']);
	if($checkConflicts != STAFF_CONFLICTS_NO){
		$unconflictedStaff = logistics_getUnconflictedShiftStaff($shiftID);

		foreach($assignableStaff as $rosterID => $data){
			if(!isset($unconflictedStaff[$rosterID])){
				unset($assignableStaff[$rosterID]);
			}
		}

	}

?>

	<?php if($staffingErrors != []): ?>
		<div class='grid-x grix-margin-x grid-padding-x'>
			<?php foreach($staffingErrors as $err): ?>
			<div class='callout alert shrink cell'>
				
					<strong><?=$err['set']?> / <?=$err['template']?></strong>
					<?=$err['name']?> staff assigned</li>
				
			</div>
			<?php endforeach ?>
		</div>
	<?php endif ?>

	<table>
		<tr>
			<th>
				Staff Member
				<?=tooltip("1) Shows available staff members not entered in this tournament<BR>
					2) If the event has no staff declared all event participants will be displayed.") ?>
				</th>
			<th>
				As
				<?=tooltip("New roles added to system on request.<BR>
						(<em>It only takes a minute, so don&#39;t hesitate to ask.</em>)") ?>
			</th>
		</tr>
		<?
		foreach($shiftStaff as $shiftInfo){
			shiftTableRow($shiftID, $shiftInfo, $assignableStaff, $roles, $emptyShift);
		}

		for($i = -1;$i>=-10;$i--){
			$emptyShift['staffShiftID'] = $i;
			shiftTableRow($shiftID, $emptyShift, $assignableStaff, $roles, $emptyShift);
		}
		

		?>
						

	</table>

<?php
}
/******************************************************************************/

function shiftTableRow($shiftID, $shiftInfo, $assignableStaff, $roles){

	$staffShiftID = $shiftInfo['staffShiftID'];
	$inputNamePath = "editStaffShifts[{$shiftID}][{$staffShiftID}]";
	if($staffShiftID < -1){
		$hide= 'hidden';
	} else {
		$hide = '';
	}
?>

	<tr class='add-staffShift<?=$staffShiftID?> <?=$hide?>'>
		<td>

			<select name="<?=$inputNamePath?>[rosterID]" class='no-bottom'
				id='staffShift-select<?=$staffShiftID?>'>

				<option></option>
				<?php if($shiftInfo['rosterID'] != null): 
					if(isset($assignableStaff[$shiftInfo['rosterID']]['optionName'])){
						$name = $assignableStaff[$shiftInfo['rosterID']]['optionName'];
					} else {
						$name = getFighterName($shiftInfo['rosterID']);
					}
					?>
					<option value='<?=$shiftInfo['rosterID']?>' selected >
						<?=$name?>
					</option>
				<?php endif ?>

				<?php foreach($assignableStaff as $rosterID => $staffMember):?>
					<option value='<?=$rosterID?>'>
						<?=$staffMember['optionName']?>
					</option>
				<?php endforeach ?>
			</select>
		</td>
		<td>
			<select class='no-bottom' 
				name="<?=$inputNamePath?>[logisticsRoleID]" >
				<?php foreach($roles as $role): ?>
					<option <?=optionValue($role['logisticsRoleID'],$shiftInfo['logisticsRoleID'])?> >
						<?=$role['roleName']?>	
					</option>
				<?php endforeach ?>
			</select>
		</td>
	</tr>

<?php
}

/******************************************************************************/

function changeScheduleBlock($blockInfo, $schedule){
	if($blockInfo != null){
		$blockID = $blockInfo['blockID'];
	} else {
		$blockID = null;
	}

?>

	<?php if($schedule == null): ?>
		<div class='callout warning'>
			<strong>No Staffing Blocks Created</strong>
			<BR>You have to create <em>Schedule Blocks</em> with <em>Staffing Shifts</em> to be 
			able to assign staff.
			<BR><a href='logisticsSchedule.php'>Edit Event Schedule</a>
		</div>
		<?php return ?>
	<?php endif ?>

	<div class='callout cell success text-center'>


	<form method='POST'>
	<input type='hidden' name='formName' value='selectScheduleBlock'>
	<div class='input-group grid-x no-bottom'>
	<span class='input-group-label large-3 medium-4 small-12 text-right'>
		Assigning Staff To:
	</span>

	<select name='blockID' onchange='this.form.submit()' class=' large-7 medium-8 small-12'>
		<option></option>
		<?php foreach($schedule as $dayNum => $daySchedule): ?>
			<option disabled>
				Day <?=$dayNum?>
				<?foreach($daySchedule as $loopBlock): ?>
					<option <?=optionValue($loopBlock['blockID'], $blockID)?> >
						<?php if(logistics_isBlockStaffed($loopBlock['blockID'], 
															$loopBlock['blockTypeID']) == false): ?>
							***
						<?php endif ?>

						<?=logistics_getScheduleBlockName($loopBlock['blockID'],'before');?>

					</option>
				<?php endforeach ?>
			</option>
		<?php endforeach ?>
	</select>

	<select id='staffShift-numToAdd' onchange="logistics_staffShiftNumToAdd()" 
			class='large-2 medium-3 small-12'>
		<?php for($i=1;$i<=10;$i++):?>
			<option value='<?=$i?>'>
				<?=$i?> at a Time
			</option>
		<?php endfor ?>
	</select>


	</div>
	</form>

	</div>


<?php
}


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
