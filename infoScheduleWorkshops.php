<?php
/*******************************************************************************
	Logistics Schedule
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Workshop List';
$includeTournamentName = false;
$createSortableDataTable[] = ['workshopListView',100];
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif (ALLOW['VIEW_SCHEDULE'] == false){
	displayAlert("Event is still upcoming<BR>Schedule not yet released");
} elseif($_SESSION['isMetaEvent'] == true){
	redirect('infoSummary.php');
} else {

	$schedule = logistics_getEventSchedule($_SESSION['eventID']);
	$eventDays = (array)getEventDays($_SESSION['eventID']);

	$workshopList = [];
	foreach($schedule as $day){

		$cssClass = "table-top-border";

		foreach($day as $block){
			if($block['blockTypeID'] != SCHEDULE_BLOCK_WORKSHOP){
				continue;
			}

			$info = logistics_getScheduleBlockInfo($block['blockID']);
			$tmp['experience'] = $info['blockAttributes']['experience'];
			$tmp['equipment'] = $info['blockAttributes']['equipment'];

			$tmp['daySmall'] = $eventDays[$block['dayNum']];
			$tmp['day'] = "Day ".$block['dayNum']." (".$eventDays[$block['dayNum']].")";
			$tmp['startTime'] = base60($block['startTime'], true);
			$tmp['endTime'] = base60($block['endTime'], true);

			$tmp['location'] = '';
			foreach($block['locationIDs'] as $locationID){
				$tmp['location'] .= logistics_getLocationName($locationID).", ";
			}
			$tmp['location'] = rtrim($tmp['location'], ', ');
			
			$tmp['name'] = "<b>".$block['blockTitle']."</b> <i>".$block['blockSubtitle']."</i>";

			$instructors = logistics_getBlockInstructors($block['blockID']);
			$tmp['instructor'] = "";
			foreach($instructors as $instructor){
				$tmp['instructor'] .= $instructor['name'].", ";
			}
			$tmp['instructor'] = rtrim($tmp['instructor'], ', ');

			$tmp['cssClass'] = $cssClass;
			$cssClass = "";

			$workshopList[] = $tmp;

		}
	}

	foreach($eventDays as $dayNum => $dayName){
		$class[$dayNum] = "event-day-row-{$dayNum} event-day-row";

		if($dayNum != $_SESSION['dayNum']){
			$class[$dayNum] .= " hidden";
			$show[$dayNum] = "";
		} else {
			$show[$dayNum] = " hidden";
		}
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>

	<table class="display" id='workshopListView'>	

		<thead>
		<tr>
			<th class='hide-for-small-only'>Day</th>
			<th class='hide-for-small-only'>Start</th>
			<th class='hide-for-small-only'>End</th>
			<th class='hide-for-small-only'>Location</th>
			<td class='show-for-small-only'>Time & Place</th>
			<th>Class</th>
			<th>Instructor</th>
			<th>Equipment</th>
			<th>Experience</th>
		</tr>
		</thead>
		
		<tbody>
		<?php foreach($workshopList as $workshop):?>

			<tr class='link-table <?=$workshop['cssClass']?>'>

				<td class='hide-for-small-only'>
					<?=$workshop['day']?>
				</td>

				<td class='hide-for-small-only'>
					<?=$workshop['startTime']?>
				</td>
				<td class='hide-for-small-only'>
					<?=$workshop['endTime']?>
				</td>
				<td class='hide-for-small-only'>
					<?=$workshop['location']?>
				</td>

				<td class='show-for-small-only'>
					<u><?=$workshop['daySmall']?></u><BR>
					<?=$workshop['startTime']?> - <?=$workshop['endTime']?><BR>
					<i><?=$workshop['location']?></i>
				</td>
				<td>
					<?=$workshop['name']?>
				</td>
				<td>
					<?=$workshop['instructor']?>
				</td>
				<td>
					<?=$workshop['equipment']?>
				</td>
				<td>
					<?=$workshop['experience']?>
				</td>
			</tr>
			

		<?php endforeach ?>
		</tbody>


	</table>

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////