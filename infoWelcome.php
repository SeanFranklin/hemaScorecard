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

	$eventList = getEventListByPublication(ALLOW['VIEW_HIDDEN'],'date');

	$eventsToShow['active'] = [];
	$eventsToShow['recent'] = [];
	$eventsToShow['published'] = [];

	$isAnyEventActive = false;

	foreach($eventList as $i => $event){

		$dateDiffStart = compareDates($event['eventStartDate']);
		$dateDiffEnd = compareDates($event['eventEndDate']);

		if($dateDiffEnd > 14){ continue; }

		$event['displayClass'] = "";


		if($dateDiffStart > -2 && $dateDiffEnd < 2){

			// Events that are scheduled for the current date

			if($event['eventStatus'] == 'active'){
				$event['displayStatus'] = '<b>ACTIVE</b>';
				$event['displayClass'] = "link-table-active";
			} else {
				$event['displayStatus'] = 'Unpublished';
			}

			$eventsToShow['active'][] = $event;
			$isAnyEventActive = true;

		} elseif ($dateDiffEnd >= 2){

			// Events that are scheduled for fulture dates

			if($event['eventStatus'] == 'active' || $event['eventStatus'] == 'complete'){
				$event['displayStatus'] = '<b>Published</b>';
			} else {
				$event['displayStatus'] = 'Unpublished';
			}

			$eventsToShow['recent'][] = $event;

		} else {

			// Events that are less than 14 days old

			if($event['eventStatus'] == 'active'){
				$event['displayStatus'] = '<b>Published</b>';
			} else {
				$event['displayStatus'] = 'Upcoming';
			}

			$eventsToShow['upcoming'][] = $event;

		}


		// Only make the Active tab the default if there are active events to show.
		// Otherwise the Recent tab will be active on page load.
		if($isAnyEventActive == true){
			$activeClass = " is-active";
			$recentClass = "";
		} else {
			$activeClass = "";
			$recentClass = " is-active";
		}


	}


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<div class='cell' style='border-bottom:2px solid black'>

	<img style='width: 400px;' src="includes/images/logo_rect.jpg">
	<BR><BR>
	<p>HEMA Scorecard is a <b>FREE</b> online tournament management software for Historical European Martial Arts tournaments. Scorecard allows you to:<ul>
		<li>Automate all your scoring and bracket paperwork.</li>
		<li>Communicate information to your fencers and spectators. All matches & scores are live online.</li>
		<li>Host your full event logistics chain, including scheduling workshops and volunteer shifts.</li>
		<li>Do so much more than I can fit in this little box.</li>
	</ul></p>

	<p>If you are interested in using HEMA Scorecard to hold a tournament of your own, <a href='infoWhy.php'> why not have a look at some of it's best features</a>? </p>

</div>


<h3>Recent and Upcoming Events (<a href='infoSelect.php'>Full Event List</a>)</h3>


<!-- Tabs ------------------------------------------------------------>

<ul class="tabs" data-tabs id="recent-events-tabs">
	<li class="tabs-title <?=$activeClass?>"><a data-tabs-target="panel-active">
		Active
	</a></li>

	<li class="tabs-title"><a data-tabs-target="panel-upcoming">
		Upcoming
	</a></li>

	<li class="tabs-title <?=$recentClass?>"><a data-tabs-target="panel-recent">
		Recent
	</a></li>

</ul>


<!-- Panels ------------------------------------------------------------>

<div class="tabs-content" data-tabs-content="recent-events-tabs">

	<div class="tabs-panel <?=$activeClass?>" id="panel-active">
		<?=displayEventTabe($eventsToShow['active'])?>
	</div>

	<div class="tabs-panel" id="panel-upcoming">
		<?=displayEventTabe($eventsToShow['upcoming'])?>
	</div>

	<div class="tabs-panel <?=$recentClass?>" id="panel-recent">
		<?=displayEventTabe($eventsToShow['recent'])?>
	</div>

</div>


<?
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayEventTabe($eventList){
?>

<table class="display">

	<thead>
		<tr>
			<th>Date
				<?=tooltip("Y-M-D")?></th>
			<th>Name</th>
			<th>Location</th>
			<th class='hide-for-small-only'>Status</th>
		</tr>
	</thead>

	<tbody>

		<?php foreach($eventList as $event): ?>
			<tr onclick="changeEventJs(<?=$event['eventID']?>)" class='link-table <?=$event['displayClass']?>'>
				<td><?=$event['eventStartDate']?></td>
				<td><?=getEventName($event['eventID'])?></td>
				<td><?=$event['countryName']?> (<?=$event['eventCity']?>, <?=$event['eventProvince']?>)</td>
				<td class='hide-for-small-only'><?=$event['displayStatus']?></td>
			</tr>

		<?php endforeach ?>
	</tbody>
</table>

<?php
}


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
