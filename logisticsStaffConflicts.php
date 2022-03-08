<?php
/*******************************************************************************
	Logistics Staff Assigments
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Logistics Staff Conflicts';
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
} elseif($_SESSION['isMetaEvent'] == true){
	redirect('infoSummary.php');
} else {

	
	
	$eventRoster = getEventRoster();
	$eventDays = getEventDays($_SESSION['eventID']);

	$conflictList = generateConflictList($eventRoster);
	$unfilledShifts = logistics_findUnfilledShifts($_SESSION['eventID']);
	$staffOverCompetency = logistics_findStaffOverCompetency($_SESSION['eventID']);


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>

<!-- Tabs -->
	<ul class="tabs" data-tabs id="conflicts-tab">

		
		<li class="tabs-title">
			<a data-tabs-target="panel-conflicts" href="#panel-conflicts">
				Schedule Conflicts
			</a>
		</li>
		

		<li class="tabs-title">
			<a data-tabs-target="panel-unstaffed" href="#panel-unstaffed">
				Unfilled Staff Shifts
			</a>
		</li>

		<li class="tabs-title">
			<a data-tabs-target="panel-competency" href="#panel-competency">
				Over-Competency Shifts
			</a>
		</li>

	</ul>

<!-- Tab Content -->
	<div class="tabs-content" data-tabs-content="conflicts-tab">

		
		<div class="tabs-panel" id="panel-conflicts">
			<?=scheduleConflictList($conflictList, $eventDays)?>
		</div>
		

		<div class="tabs-panel" id="panel-unstaffed">
			<?=scheduleUnfilledShifts($unfilledShifts, $eventDays)?>
		</div>

		<div class="tabs-panel" id="panel-competency">
			<?=scheduleStaffOverCompetency($staffOverCompetency, $eventDays)?>
		</div>

	</div>
	
	
	

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

function scheduleStaffOverCompetency($staffOverCompetency, $eventDays){

	if($staffOverCompetency == null){
		displayAlert("Everyone is within their limits.<BR>(Good job)");
		return;
	}

?>

	<table class='stack'>

	<tr>
		<th>Tournament</th>
		<th>Day</th>
		<th>Time</th>
		<th>Location</th>
		<th>Name</th>
		<th>Role</th>
		<th>Competency</th>
	</tr>

	<?php foreach($staffOverCompetency as $shift):
		$sInfo = logistics_getShiftInfo($shift['shiftID']);
		$roleName = logistics_getRoleName($shift['logisticsRoleID']);
		if($sInfo['tournamentID'] != null){
			$tournamentName = getTournamentName($sInfo['tournamentID']);
		} else {
			$tournamentName = "";
		}
		?>

			<tr>
				<td><?=$tournamentName?></td>
				<td>
					Day <?=$sInfo['dayNum']?> (<?=$eventDays[$sInfo['dayNum']]?>)
				</td>
				<td><?=min2hr($sInfo['startTime'])?> - <?=min2hr($sInfo['endTime'])?></td>
				<td><?=logistics_getLocationName($sInfo['locationID'])?></td>
				<td><strong><?=getFighterName($shift['rosterID'])?></strong></td>
				<td><?=$roleName?></td>
				<td><?=$shift['staffCompetency']?> / <?=$shift['roleCompetency']?></td>
			</tr>
			
	<?php endforeach ?>

	</table>


<?php
}

/******************************************************************************/

function scheduleUnfilledShifts($badShifts, $eventDays){

	if($badShifts == null){
		displayAlert("All shifts match their template.<BR>(Good job)");
		return;
	}
?>

	<table class='stack'>

	<tr>
		<th>Tournament</th>
		<th>Day</th>
		<th>Time</th>
		<th>Location</th>
		<th>Role</th>
		<th># of Staff</th>
	</tr>

	<?php foreach($badShifts as $shiftID => $shift):

		
		$sInfo = logistics_getShiftInfo($shiftID);
		foreach($shift as $staffType):
			$roleName = logistics_getRoleName($staffType['logisticsRoleID']);
			?>

			<tr>
				<td><?=getTournamentName($sInfo['tournamentID'])?></td>
				<td>
					Day <?=$sInfo['dayNum']?> (<?=$eventDays[$sInfo['dayNum']]?>)
				</td>
				<td><?=min2hr($sInfo['startTime'])?> - <?=min2hr($sInfo['endTime'])?></td>
				<td><?=logistics_getLocationName($sInfo['locationID'])?></td>
				<td><?=$roleName?></td>
				<td><?=$staffType['numStaff']?> / <?=$staffType['targetStaff']?></td>
			</tr>

		<?php endforeach ?>
	<?php endforeach ?>

	</table>


<?php
}

/******************************************************************************/

function scheduleConflictList($conflictList, $eventDays){

	if($conflictList == null){
		displayAlert("No staffing conflicts.<BR>(Good job)");
		return;
	}
?>
	<table class='stack'>


	<?php foreach($conflictList as $rosterID => $conflicts): ?>
		<?php foreach($conflicts as $conflict): 
			$info[1] = logistics_getScheduleItemDescription($conflict[1]['blockID'],$conflict[1]['shiftID']);
			$info[2] = logistics_getScheduleItemDescription($conflict[2]['blockID'],$conflict[2]['shiftID']);

			if($conflict[1]['shiftID'] != null){
				$info[1]['type'] = "Staffing";
			} else {
				$info[1]['type'] = "Tournament Entry";
			}

			if($conflict[2]['shiftID'] != null){
				$info[2]['type'] = "Staffing";
			} else {
				$info[2]['type'] = "Tournament Entry";
			}

			?>
			<tr style='border-top: 1px solid black;'>
				<td rowspan='2'>
					<h5><?=getFighterName($rosterID)?></h5>
				</td>
				<td rowspan='2'>
					<strong>
						Day <?=$conflict[1]['dayNum']?>
						<BR>
						<?=$eventDays[$conflict[1]['dayNum']]?>
					</strong>
				</td>
				<td>
					<strong><?=$info[1]['type']?></strong>
				</td>
				<td>
					<?=logistics_getScheduleBlockName($conflict[1]['blockID'])?>
				</td>
				<td>
					<?=min2hr($info[1]['startTime'])?> - <?=min2hr($info[1]['endTime'])?>
				</td>
				<td>
					<?=logistics_getLocationName($info[1]['locationID'])?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?=$info[2]['type']?></strong>
				</td>
				<td>
					<?=logistics_getScheduleBlockName($conflict[2]['blockID'])?>
				</td>
				<td>
					<?=min2hr($info[2]['startTime'])?> - <?=min2hr($info[2]['endTime'])?>
				</td>
				<td>
					<?=logistics_getLocationName($info[2]['locationID'])?>
				</td>
			<tr>


		<?php endforeach ?>
	<?php endforeach ?>

	</table>


<?php
}

/******************************************************************************/

function generateConflictList($eventRoster){

	$conflictList = [];
	foreach($eventRoster as $person){
		$personalSchedule = logistics_getParticipantSchedule($person['rosterID'], $_SESSION['eventID']);

		$dayNum = 0;
		$lastSuppressConflicts = false;

		if(isset($personalSchedule['scheduled']) == true){
			foreach($personalSchedule['scheduled'] as $item){

				if(($item['dayNum'] == $dayNum && $item['startTime'] < $lastEndTime)
					&& ($lastSuppressConflicts == false && $item['suppressConflicts'] == 0)){
					$conflict[1]['shiftID'] = $lastShift;
					$conflict[1]['blockID'] = $lastBlock;
					$conflict[1]['dayNum'] = $dayNum;
					$conflict[2]['shiftID'] = @$item['shiftID']; // might not exist
					$conflict[2]['blockID'] = $item['blockID'];

					$conflictList[$person['rosterID']][] = $conflict;

				}
				$lastSuppressConflicts = (bool)$item['suppressConflicts'];
				$lastEndTime = $item['endTime'];
				$dayNum = $item['dayNum'];
				$lastBlock = $item['blockID'];
				$lastShift = @$item['shiftID']; // Could also not exist


			}
		}
	}

	return $conflictList;
}




/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
