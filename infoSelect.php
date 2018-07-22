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
$eventList = getEventList();
$categorizedEventList = sortEventList($eventList); 

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
				<h4>Active & Upcoming</h4>
			</a>
			<div class='accordion-content' data-tab-content>
 
				<?php if($categorizedEventList['active'] != null || $categorizedEventList['default'] != null):?>
					<h5>Active Events</h5>
					<?php displayEventsInCategory(
							array_reverse((array)$categorizedEventList['default'],true)
						); ?>
					<?php displayEventsInCategory(
							array_reverse((array)$categorizedEventList['active'],true)
						); ?>
				<?php endif ?>
	
				<?php if($categorizedEventList['upcoming'] != null): ?>
					<h5>Upcoming Events</h5>
					<?php displayEventsInCategory(
							array_reverse((array)$categorizedEventList['upcoming'],true)
						); ?>
				<?php endif ?>
	
				<?php if(USER_TYPE == USER_SUPER_ADMIN && $categorizedEventList['hidden'] != null): ?>
					<h5>Hidden Events</h5>
					<?php displayEventsInCategory(
							array_reverse((array)$categorizedEventList['hidden'],true)
						); ?>
				<?php endif ?>
	
			</div>
		</li>

		<?php displayArchivedEvents($categorizedEventList['archived']); //Old events displayed by year ?>

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

function displayEventsInCategory($eventList,$numToDisplay = null){

	foreach((array)$eventList as $eventID => $eventInfo){

		displayEventButton($eventID, $eventInfo);

		$numDisplayed++;
		if($numToDisplay != null && $numDisplayed >= $numToDisplay){
			return;
		}

	}
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
