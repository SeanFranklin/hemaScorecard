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
$createSortableDataTable[] = 'eventListActive';
$createSortableDataTable[] = 'eventListAll';

include('includes/header.php');

$eventList = getEventListByPublication();


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<ul class="tabs" data-tabs id="change-event-disp-tabs">

		
	<li class="tabs-title is-active">
		<a data-tabs-target="panel-recent" href="#change-recent">
			Recent and Upcoming
		</a>
	</li>


	<li class="tabs-title">
		<a data-tabs-target="panel-all" href="#change-all">
			All Events
		</a>
	</li>

</ul>



<div class="tabs-content" data-tabs-content="change-event-disp-tabs">
<div class="tabs-panel is-active" id="panel-recent">
<table id="eventListActive" class="display">

	<thead>
		<tr>
			<th>Date
				<?=tooltip("Y-M-D")?></th>
			<th>Name</th>
			<th>Location</th>
			<th>Status</th>
		</tr>
	</thead>

	<tbody>
		<?php foreach($eventList as $event):
			if(compareDates($event['eventStartDate']) > 21){ continue; }
			?>

			<tr onclick="changeEventJs(<?=$event['eventID']?>)" class='link-table'>
				<td><?=$event['eventStartDate']?></td>
				<td><?=getEventName($event['eventID'])?></td>
				<td><?=$event['countryName']?> (<?=$event['eventCity']?>, <?=$event['eventProvince']?>)</td>
				<td><?=$event['eventStatus']?></td>
			</tr>

		<?php endforeach ?>
	</tbody>
</table>
</div>



<div class="tabs-panel" id="panel-all">
<table id="eventListAll" class="display">

<thead>
	<tr>
		<th>Name</th>
		<th>Year</th>
		<th>Country</th>
		<th>Location</th>
		<th>Date</th>
		<th>Status</th>
	</tr>
</thead>

<tbody>
	<?php foreach($eventList as $event):
		if(ALLOW['SOFTWARE_EVENT_SWITCHING'] == true){
			$date = $event['eventStartDate'];
		} else {
			$date = sqlDateToString($event['eventStartDate']).", ".$event['eventYear'];
		}

		?>
		<tr onclick="changeEventJs(<?=$event['eventID']?>)" class='link-table'>
			<td><?=$event['eventName']?></td>
			<td><?=$event['eventYear']?></td>
			<td><?=$event['countryName']?></td>
			<td><?=$event['eventProvince']?>, <?=$event['eventCity']?></td>
			<td><?=$date?></td>
			<td><?=$event['eventStatus']?></td>
		</tr>
	<?php endforeach ?>
</tbody>

</table>
</div>
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
