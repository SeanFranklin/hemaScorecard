<?php
/*******************************************************************************
	Event Selection

	Select which event to use
	Login:
		- SUPER ADMIN can see hidden events

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Tournament Selection";
$hideEventNav = true;
$hidePageTitle = true;
$createSortableDataTable[] = ['eventListAll',25,0,'desc'];

include('includes/header.php');

$eventList = getEventListByPublication();

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>





<table id="eventListAll" class="display">

<thead>
	<tr>
		<th>Date</th>
		<th>Name</th>
		<th>Location</th>
		<th>Status</th>
	</tr>
</thead>

<tbody>
	<?php foreach($eventList as $event):

		$date = $event['eventStartDate'];


		$dateDiffStart = compareDates($event['eventStartDate']);
		$dateDiffEnd = compareDates($event['eventEndDate']);

		if($event['eventStatus'] == 'active'){

			if($dateDiffStart > -2 && $dateDiffEnd < 2){
				$activeClass = "link-table-active";
				$eventStatus = "<b>ACTIVE</b>";
			} elseif($dateDiffEnd >= 2){
				$activeClass = "";
				$eventStatus = 'concluded';
			} else {
				$activeClass = "";
				$eventStatus = 'published';
			}

		} else {
			$activeClass = "";
			$eventStatus = $event['eventStatus'];
		}

		$location = $event['countryName'].", ";
		if($event['eventProvince'] != ''){
			$location .= $event['eventProvince'].", ".$event['eventCity'];
		} else {
			$location .= $event['eventCity'];
		}

		?>
		<tr onclick="changeEventJs(<?=$event['eventID']?>)" class='link-table <?=$activeClass?>'>
			<td style="white-space: nowrap;"><?=$date?></td>
			<td><?=$event['eventName']?> <span class='hide-for-small-only'><?=$event['eventYear']?></span></td>
			<td><?=$location?></td>

			<td style="white-space: nowrap;"><?=$eventStatus?></td>
		</tr>
	<?php endforeach ?>
</tbody>

</table>

</div>


<?
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayArchivedEvents($eventList){

	$oldYear = null;

	foreach($eventList as $eventID => $eventInfo){
		$year = $eventInfo['eventYear'];
		if($year != $oldYear){
			if($oldYear != null){ echo "</div></li>"; }
			echo "<li class='accordion-item' data-accordion-item>
				<a class='accordion-title' style='padding-top:10px; padding-bottom:1px'><h4>{$year} Events</h4></a>
				<div class='accordion-content' data-tab-content>";
			$oldYear = $year;

		}
		displayEventButton($eventID, $eventInfo);
	}
	echo "</div></li>";

}

/**********************************************************************/

function displayEventsInCategory($eventList, $dateLimit = 0){

	if(ALLOW['VIEW_HIDDEN']){
		$dateLimit = 0;
	}

	foreach((array)$eventList as $eventID => $eventInfo){
		// A check to make sure that old events don't show up in the
		// active/upcoming category.
		$then = date_create($eventInfo['eventEndDate']);
		$today= date_create(date("Y-m-d"));
		$diff = date_diff($then,$today);
		$num = (int)$diff->format('%R%a');

		if($dateLimit > 0 && $num > $dateLimit){
			continue;
		}


		displayEventButton($eventID, $eventInfo);
	}
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
