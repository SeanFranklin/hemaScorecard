<?php
/*******************************************************************************
	Logistics Staff Assigments
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Logistics Staff Grid View';
$includeTournamentName = false;
$hideEventNav = true;
$jsIncludes[] = 'logistics_management_scripts.js';
$jsIncludes[] = 'roster_management_scripts.js';
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

	define("TIME_INTERVAL",30);
	$eventRoster = getEventRoster(null, true);
	$eventDays = getEventDays($_SESSION['eventID']);

	$timeLimits = logistics_getEventMaxTimes($_SESSION['eventID']);
	$startTime = $timeLimits['startTime'];
	$endTime = $timeLimits['endTime'];
	$startTime = floor($startTime/TIME_INTERVAL)*TIME_INTERVAL;
	$endTime = ceil($endTime/TIME_INTERVAL)*TIME_INTERVAL;

	foreach($eventDays as $dayNum => $day){
		// This is necessary for the custom table-sortability
		$createSortableDataTable[] = "staffShiftGridTable-{$dayNum}";
	}


	for($time = $startTime; $time < $endTime; $time += TIME_INTERVAL){
		$emptyTimeLine[$time]['show'] = '';
	}

	$areConflicts = false;
	foreach($eventRoster as $person){

		$rosterID = $person['rosterID'];
		foreach($eventDays as $dayNum => $day){
			$timeLine[$dayNum][$rosterID] = $emptyTimeLine;
		}

		$staffInfo[$rosterID]['staffCompetency'] = $person['staffCompetency'];
		if($staffInfo[$rosterID]['staffCompetency'] == 0){
			$staffInfo[$rosterID]['staffCompetency'] = '';
		} 
		$staffInfo[$rosterID]['hours'] = 0;
		$staffInfo[$rosterID]['staffHoursTarget'] = $person['staffHoursTarget'];
		if($staffInfo[$rosterID]['staffHoursTarget'] == 0){
			$staffInfo[$rosterID]['staffHoursTarget'] = '';
		}
		

		$personalSchedule = logistics_getParticipantSchedule($person['rosterID'], $_SESSION['eventID']);
		
		if(isset($personalSchedule['scheduled']) == false){
			if($staffInfo[$rosterID]['staffCompetency'] == ''){
				$staffInfo[$rosterID]['hours'] = '';
			}
			continue;
		}

		$isConflict = false;
		$lastDayNum = 0;
		$lastSuppressConflicts = false;
		foreach($personalSchedule['scheduled'] as $scheduleItem){
			
			$dayNum = $scheduleItem['dayNum'];
			$startTime = floor($scheduleItem['startTime']/TIME_INTERVAL)*TIME_INTERVAL;
			$endTime = ceil($scheduleItem['endTime']/TIME_INTERVAL)*TIME_INTERVAL;

			if(isset($scheduleItem['shiftID']) == true 
				&& $scheduleItem['blockTypeID'] != SCHEDULE_BLOCK_MISC){

				$length = $scheduleItem['endTime'] - $scheduleItem['startTime'];
				$staffInfo[$rosterID]['hours'] += round($length/60,1);
			}

			if(    $scheduleItem['suppressConflicts'] == 0 
				&& $lastSuppressConflicts == 0
				&& $dayNum == $lastDayNum 
				&& $scheduleItem['startTime'] < $lastEndTime){
				
				$isConflict = true;
			}

			for($time = $startTime; $time < $endTime; $time += TIME_INTERVAL){

				if(	   $isConflict == true
					&& $timeLine[$dayNum][$rosterID][$time]['show'] != '' 
					&& substr($timeLine[$dayNum][$rosterID][$time]['show'],0,1) != '!'){

					$timeLine[$dayNum][$rosterID][$time]['show'] = '!'.$timeLine[$dayNum][$rosterID][$time]['show'];
					$areConflicts = true;
				}



				if(isset($scheduleItem['shiftID']) == true){
					if($scheduleItem['blockTypeID'] == SCHEDULE_BLOCK_MISC){
						$timeLine[$dayNum][$rosterID][$time]['show'] .= '*';
					} else {
						$timeLine[$dayNum][$rosterID][$time]['show'] .= 'S';
					}
					
					$timeLine[$dayNum][$rosterID][$time]['blockID'] = $scheduleItem['blockID'];
				} else {
					$timeLine[$dayNum][$rosterID][$time]['show'] .= 'T';
					$timeLine[$dayNum][$rosterID][$time]['tournamentID'] = $scheduleItem['tournamentID'];
				} 


			}
			$lastSuppressConflicts = (bool)$scheduleItem['suppressConflicts'];
			$lastEndTime = $scheduleItem['endTime'];
			$lastDayNum = $scheduleItem['dayNum'];
			$isConflict = false;

		}

		if($staffInfo[$rosterID]['hours'] == 0 && $staffInfo[$rosterID]['staffCompetency'] == ''){
			$staffInfo[$rosterID]['hours'] = '';
		}

	}

	if($areConflicts == true){
		displayAlert("<strong>Individuals with Schedule Conflicts detected.</strong><BR>
			You can search for '<strong>!</strong>' to find any scheduling conflicts,
			or view more details at <a href='logisticsStaffConflicts.php'>View Staff Conflicts</a>.",'alert');
	}

	displayAlert("
		<u>Note:</u> Staff shifts can sometimes 'share' a block on this display without being a conflict. eg: a shift ending at 10:40 and one starting at 10:50 would both have to share the 10:30-11:00 block. There will be a warning in red above this if you have real staff conflicts.");

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>
	
<!-- Tabs -->
	<ul class="tabs" data-tabs id="participantsGrid-tab">
		<?php foreach($eventDays as $dayNum => $day): 
			if($dayNum == $_SESSION['dayNum']){
				$firstDay = 'is-active';
			} else {
				$firstDay = '';
			}
			?>

			<li class="tabs-title <?=$firstDay?>">
				<a data-tabs-target="panel-grid-<?=$dayNum?>" href="#panel-grid-<?=$dayNum?>" 
					onclick="updateSession('dayNum',<?=$dayNum?>)">
					Day <?=$dayNum?>, <?=$day?>
				</a>
			</li>
		<?php endforeach ?>
	</ul>

<!-- Panels -->
	<div class="tabs-content" data-tabs-content="participantsGrid-tab">
		<?php foreach($eventDays as $dayNum => $day): 
			if($dayNum == $_SESSION['dayNum']){
				$firstDay = 'is-active';
			} else {
				$firstDay = '';
			}
			?>
			<div class="tabs-panel  <?=$firstDay?>" id="panel-grid-<?=$dayNum?>">
				<?=displayDayTable($timeLine[$dayNum],$emptyTimeLine, $dayNum, $staffInfo)?>
			</div>
		<?php endforeach ?>
	</div>



<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

function displayDayTable($timeLine, $emptyTimeLine, $dayNum, $staffInfo){
?>
	<table id="staffShiftGridTable-<?=$dayNum?>" class="display" >
		<thead>
		<tr>
			<th>Name</th>
			<th>Staff</th>
			<th>Hours</th>
			<th>Target</th>
			<?php foreach($emptyTimeLine as $time => $dummy):?>
				<th style='font-size:0.7em'>
					<?=min2hr($time, false)?>
				</th>
			<?php endforeach ?>
		</tr>
		</thead>

		<?php foreach($timeLine as $rosterID => $personalTimeLine): ?>
			<tr>
				<td style='font-size:0.7em; white-space:nowrap;'>
					<a onclick="goToPersonalSchedule(<?=$rosterID?>)">
						<?=getFighterName($rosterID)?>
					</a>

				</td>
				<td>
					<?=$staffInfo[$rosterID]['staffCompetency']?>
				</td>
				<td>
					<?=$staffInfo[$rosterID]['hours']?>
				</td>
				<td>
					<?=$staffInfo[$rosterID]['staffHoursTarget']?>
				</td>

				<?php foreach($personalTimeLine as $time => $timeSlot): 
					switch(substr($timeSlot['show'],0,1)){
						case 'T':
							$color = SCHEDULE_COLOR_TOURNAMENT;
							break;
						case 'S':
							$color = SCHEDULE_COLOR_STAFFING;
							break;
						case '!':
							$color = SCHEDULE_COLOR_CONFLICT;
							break;
						case '*':
							$color = SCHEDULE_COLOR_MISC;
							break;
						case '':
							$color = null;
							break;
						default:
							$color = 'grey';
					}

					?>
					<th style='background-color:<?=$color?>'>
						<?=createTextForGridItem($timeSlot)?>
					</th>
				<?php endforeach ?>
			</tr>

		<?php endforeach ?>

	</table>
<?php
}

/******************************************************************************/

function createTextForGridItem($info){

	$charArray = str_split($info['show']);
	$hoverText = null;



	foreach($charArray as $char){
		echo $char;
	}
	return;
	/////////////////////////////////////

	// This text is to make tooltips.

	
	

?>

	<?php foreach($charArray as $char): 
		switch($char){
			case 'T':
				$hoverText = getTournamentName($info['tournamentID']);
				break;
			case 'S':
			case '*':
				$hoverText = logistics_getScheduleBlockName($info['blockID']);
				break;
			default:
				break;
		}?>

		<?php if($hoverText != null): ?>
			<span data-tooltip aria-haspopup='true' class='has-tip' 
				data-disable-hover='false'  title="<?=$hoverText?>"
				data-position='top' data-allow-html='true' >
				<?=$char?>
			</span>
		<?php else: ?>
			<?=$char?>
		<?php endif ?>


	<?php endforeach ?>

<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
