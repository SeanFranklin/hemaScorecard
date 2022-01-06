<?php 
/*******************************************************************************
	Event Selection
	
	Select which event to use
	Login:
		- SUPER ADMIN can see hidden events
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Welcome to HEMA Scorecard";

include('includes/header.php');

// Get the event List
$activeEvents = getEventList('active');
$upcomingEvents = getEventList('upcoming');

if(ALLOW['VIEW_HIDDEN']){
	$hiddenEvents = getEventList('hidden');
} else if($_SESSION['userID'] != 0) {
	$hiddenEvents = getHiddenEventListForUser($_SESSION['userID']);
} else {
	// Check if someone made their event public without even creating a tournament.
	// If so then don't show it to regular users.
	foreach($activeEvents as $eventID => $data){
		if(count(getEventTournaments($eventID)) == 0){
			unset($activeEvents[$eventID]);
		}
	}
	foreach($upcomingEvents as $eventID => $data){
		if(count(getEventTournaments($eventID)) == 0){
			unset($activeEvents[$eventID]);
		}
	}

}
$recentEvents = getEventList('recent', 'DESC', 4);


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<div class='grid-x grid-margin-x' style='border-bottom:1px solid black'>
	<div class='cell medium-auto small-12'>
		<h1>Welcome to HEMA Scorecard</h1>

		<p>HEMA Scorecard is a free online tournament management software to run all publish results from all kinds of Historical European Martial Arts tournaments.</p>

		<p>If you are interested in using HEMA Scorecard to hold a tournament of your own, <a href='infoWhy.php'> why not have a look at some of it's best features</a>? </p>

		<p>Or if you are here to see some results, check out some of the recent and upcoming events. <a href='infoSelect.php'>Full Event List</a></p>
	</div>

	<div class='cell medium-shrink small-12 text-center'>
		<img src='includes\images\logo_square.png' style='padding:10px;border:1px solid black;'>
		<p class='text-right'><i>Supported by the <a href='https://www.hemaalliance.com/'>HEMA Alliance</a></i></p>
	</div>
</div>


<form method='POST'>
<input type='hidden' name='formName' value='selectEvent'>

<!-- Hidden Events -->
	<?php if($_SESSION['userID'] != 0 && $hiddenEvents != null): ?>
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

<!-- Recent Events -->
	<h5>Recent Events</h5>
		<?php displayEventsInCategory($recentEvents);?>

</form>

<?
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/**********************************************************************/

function displayEventsInCategory($eventList, $dateLimit = 0){

	if(ALLOW['VIEW_HIDDEN']){
		$dateLimit = 0;
	}

	echo "<div class='grid-x grid-padding-x'>";

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

		echo "<div class='large-6 medium-12 cell'>";

		displayEventButton($eventID, $eventInfo);
		echo "</div>";

	}
	echo "</div>";
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
