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

include('includes/header.php');

// Get the event List
// Get the event List
$activeEvents = getEventList('active');
$upcomingEvents = getEventList('upcoming');
if(USER_TYPE >= USER_SUPER_ADMIN){
	$hiddenEvents = getEventList('hidden');
}
$archivedEvents = getEventList('old', 'DESC');


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<div class='grid-x grid-padding-x'>
	<div class='large-7 medium-10 small-12 cell' id='eventListContainer'>
		
	<h4 class='text-center'>Change Event</h4>
		
	<form method='POST'>
	<input type='hidden' name='formName' value='selectEvent'>
	<ul class='accordion' data-accordion  data-allow-all-closed='true'>
		<li class='accordion-item is-active' data-accordion-item>
			<a class='accordion-title'>
				<h4>Active Events</h4>
			</a>
			<div class='accordion-content' data-tab-content>
 
				<!-- Hidden Events -->
				<?php if(USER_TYPE >= USER_SUPER_ADMIN && $hiddenEvents != null): ?>
					<h5>Hidden Events</h5>
					<?php displayEventsInCategory($hiddenEvents); ?>
				<?php endif ?>
	
				<!-- Active Events -->
				<?php if($activeEvents != null):?>
					<h5>Active Events</h5>
					<?php displayEventsInCategory($activeEvents, EVENT_ACTIVE_LIMIT); ?>
				<?php endif ?>

				<!-- Upcoming Events -->
				<?php if($upcomingEvents != null): ?>
					<h5>Upcoming Events</h5>
					<?php displayEventsInCategory($upcomingEvents, EVENT_UPCOMING_LIMIT); ?>
				<?php endif ?>
	
			</div>
		</li>

		<!-- Old Events -->
		<?php displayArchivedEvents($archivedEvents);?>

	</ul>
	</form>
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

	if(USER_TYPE >= USER_SUPER_ADMIN){
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
