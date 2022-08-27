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
			$dateDiffStart = compareDates($event['eventStartDate']);
			$dateDiffEnd = compareDates($event['eventEndDate']);

			if($dateDiffEnd > 14){ continue; }

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

			?>

			<tr onclick="changeEventJs(<?=$event['eventID']?>)" class='link-table <?=$activeClass?>'>
				<td><?=$event['eventStartDate']?></td>
				<td><?=getEventName($event['eventID'])?></td>
				<td><?=$event['countryName']?> (<?=$event['eventCity']?>, <?=$event['eventProvince']?>)</td>
				<td><?=$eventStatus?></td>
			</tr>

		<?php endforeach ?>
	</tbody>
</table>

<?
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
