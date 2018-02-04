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
	<div class='large-6 medium-8 small-12 cell' id='eventListContainer'>
		
	<h4 class='text-center'>Change Event</h4>
		
	<form method='POST'>
	<input type='hidden' name='formName' value='selectEvent'>
	<ul class='accordion' data-accordion  data-allow-all-closed='true'>
		<li class='accordion-item is-active' data-accordion-item>
			<a class='accordion-title'>
				<h4>Active & Recent</h4>
			</a>
			<div class='accordion-content' data-tab-content>
 
				<?php if($categorizedEventList['active'] != null || $categorizedEventList['default'] != null):?>
					<h5>Active Events</h5>
					<?php displayEventsInCategory($categorizedEventList['default'],null, 'isDefault'); ?>
					<?php displayEventsInCategory($categorizedEventList['active']); ?>
				<?php endif ?>
	
				<?php if($categorizedEventList['upcoming'] != null): ?>
					<h5>Upcoming Events</h5>
					<?php displayEventsInCategory($categorizedEventList['upcoming']); ?>
				<?php endif ?>
	
				<?php if(USER_TYPE == USER_SUPER_ADMIN && $categorizedEventList['hidden'] != null): ?>
					<h5>Hidden Events</h5>
					<?php displayEventsInCategory($categorizedEventList['hidden']); ?>
				<?php endif ?>
	
				<h5>Recent Events</h5>
				<?php displayEventsInCategory($categorizedEventList['archived'],3); ?>
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
		displayEvent($eventID, $eventInfo);
	}
	echo "</div></li>";
	
}

/******************************************************************************/

function displayEvent($eventID, $eventInfo){
//Creates a button for the event
	
// Format location string
	unset($location);
	if($eventInfo['eventCity'] != null){
		$location = $eventInfo['eventCity'];
	}
	if($eventInfo['eventProvince'] != null){
		if(isset($location)){ $location .= ', '; }
		$location .= $eventInfo['eventProvince'];
	}
	if($eventInfo['eventCountry'] != null){
		if(isset($location)){ $location .= ', '; }
		$location .= $eventInfo['eventCountry'];
	}
	$location = rtrim($location,', \t\n');
	
// Format year and date string
	$name = $eventInfo['eventName'];
	$year = $eventInfo['eventYear'];
	
	$startDate = sqlDateToString($eventInfo['eventStartDate']);
	$endDate = sqlDateToString($eventInfo['eventEndDate']);
	
	if($startDate != null){
		if($endDate == null OR $endDate == $startDate){
			$dateString = $startDate;
		} else {
			$dateString = $startDate." - ".$endDate;
		}
	} else if($endDate != null){
		$dateString = $endDate;
	}
	
// Displays current event in red
	if($eventID == $_SESSION['eventID']){
		$isActive = "alert";
	} else { 
		unset($isActive); 
	} 
	
	?>

	
	<div class='large-12 cell'>
		<button value='<?= $eventID ?>' style='width:100%'
			class='button hollow <?= $isActive ?>' name='changeEventTo' >
			<?= $name ?>, <?= $year ?>
			<span class='hide-for-small-only'> - </span>
			<BR class='show-for-small-only'>
			<?= $location ?>
			<BR>
			<?= $dateString ?>
		</button>
	</div>
	
<?php }

/**********************************************************************/

function displayEventsInCategory($eventList,$numToDisplay = null){

	foreach((array)$eventList as $eventID => $eventInfo){

		displayEvent($eventID, $eventInfo);

		$numDisplayed++;
		if($numToDisplay != null && $numDisplayed >= $numToDisplay){
			return;
		}

	}
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
