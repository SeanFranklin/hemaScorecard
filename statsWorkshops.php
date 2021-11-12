<?php
/*******************************************************************************

		
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Event Workshop Summary';
include('includes/header.php');

$eventStatus = getEventStatus($_SESSION['eventID']);

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(    ($eventStatus != 'archived' && $eventStatus != 'active')
	&& (ALLOW['EVENT_SCOREKEEP'] == false && ALLOW['VIEW_SETTINGS'] == false)
    && (ALLOW['STATS_EVENT'] != true)){
	pageError('user');
} else {
	

	$stats = logistics_getWorkshopStats($_SESSION['eventID']);
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>	
	
<!-- Participant summary -->

	<div class='grid-x align-center'>
	<div class='large-6 medium-10 small-12'>
	

	<table>
	
		<div class='callout success text-center'>
		<h5>
			Number of Workshops:
			<strong><?=$stats['number']?></strong><BR>

			Hours of Workshops:
			<strong><?=$stats['hours']?></strong><BR>

			Number of Workshop Instructors:
			<strong><?=$stats['numInstructors']?></strong>
		</h5>
	</div>


	<table>
	
	<caption>Instructor List </caption>

	<?php foreach($stats['instructors'] as $instructor): ?>
		<tr>
			<td><?=$instructor['lastName']?>, <?=$instructor['firstName']?></td>
		</tr>


	<?php endforeach ?>



	</table>

	
	</div>
	</div>
	
<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
