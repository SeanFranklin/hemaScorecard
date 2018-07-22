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
$hideEventNav = true;
$hidePageTitle = true;

include('includes/header.php');

// Get the event List
$eventList = getEventList();
$categorizedEventList = sortEventList($eventList); 


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
		<img src='includes\images\hemaa_logo_m.png' style='border:1px solid black;'>
		<p class='text-right'><i>Supported by the <a href='https://www.hemaalliance.com/'>HEMA Alliance</a></i></p>
	</div>
</div>


<form method='POST'>
<input type='hidden' name='formName' value='selectEvent'>

<?php if($categorizedEventList['active'] != null || $categorizedEventList['default'] != null):?>
	<h5>Active Events</h5>
	<?php displayEventsInCategory(array_reverse($categorizedEventList['active'],true)); ?>
<?php endif ?>

<?php if($categorizedEventList['upcoming'] != null): ?>
	<h5>Upcoming Events</h5>
	<?php displayEventsInCategory(array_reverse($categorizedEventList['upcoming'],true)); ?>
<?php endif ?>
<h5>Recent Events</h5>
	<?php displayEventsInCategory($categorizedEventList['archived'],4);?>

</form>

<?
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/**********************************************************************/

function displayEventsInCategory($eventList,$numToDisplay = null){


	echo "<div class='grid-x grid-padding-x'>";

	foreach((array)$eventList as $eventID => $eventInfo){

		echo "<div class='large-6 medium-12 cell'>";

		displayEventButton($eventID, $eventInfo);
		echo "</div>";
		$numDisplayed++;
		if($numToDisplay != null && $numDisplayed >= $numToDisplay){
			break;
		}
	}
	echo "</div>";
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
