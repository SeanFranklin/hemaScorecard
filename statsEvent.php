<?php
/*******************************************************************************
	Event Summary
	
	Displays information about the event, such as fighter counts for
	each tournament and registrations from each club
	LOGIN
		- ADMIN and above can view the page
		
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Event Summary';
include('includes/header.php');

$eventStatus = getEventStatus($_SESSION['eventID']);

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(    ($eventStatus != 'archived' && $eventStatus != 'active')
	&& (ALLOW['EVENT_SCOREKEEP'] == false && ALLOW['VIEW_SETTINGS'] == false)
    && (ALLOW['STATS_EVENT'] != true)){
	pageError('user');
} else {
	
	$roster = getEventRoster(null);
	$tournamentList = getTournamentsFull($_SESSION['eventID']);

	$numParticipants = count($roster);
	
	$clubTotals[1] = 0;
	if($roster != null){
		foreach($roster as $fighter){
			$schoolID = $fighter['schoolID'];
			if(!isset($clubTotals[$schoolID])){
				$clubTotals[$schoolID] = 0;
			}
			$clubTotals[$schoolID]++; 
		}
		arsort($clubTotals);
	}

	$numUnknown = $clubTotals[1];

	$totalTournamentEntries = 0; // Placeholder, it is set in a loop bellow.
	foreach((array)$tournamentList as $ID => $tournament){

		$tmp['name'] = getTournamentName($ID);
		$tmp['number'] = $tournament['numParticipants'];
		$totalTournamentEntries += $tmp['number'];

		$tournamentDisplayList[] = $tmp;
	}
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>	
	
<!-- Participant summary -->

	<div class='grid-x align-center'>
	<div class='large-6 medium-10 small-12'>
	
	
	<div class='callout success text-center'>
		<h5>
			Total Event Participants:
			<strong><?=$numParticipants?></strong><BR>

			Total Tournament Registrations:
			<strong><?=$totalTournamentEntries?></strong>
		</h5>
	</div>
	
	<table>
	<caption>Participant Numbers</caption>
	

	<?php foreach((array)$tournamentDisplayList as $data): ?>
		<tr>
			<td><?=$data['name']?></td>
			<td class='text-right'><?=$data['number']?></td>
		</tr>
	<?php endforeach ?>
	
		<tr style='border-top:solid 1px'>
			<th class='text-right'>
				<em>Total:</em>
			</th>
			<th class='text-right'>
				<em><?=$totalTournamentEntries?></em>
			</th>
		</tr>
	</table>

<!-- School registrations summary -->

	<table class='data_table'>
		<caption>School Attendance</caption>
		<?php foreach((array)$clubTotals as $schoolID => $num):
			if($schoolID == 1){continue;}
			$name = getSchoolName($schoolID, 'full', 'Branch');
			?>
			
			<tr>
				<td><?=$name?></td>
				<td class='text-center'><?=$num?></td>
			</tr>
			
		<?php endforeach ?>
		
		<?php if($numUnknown > 0): ?>
			<tr>
				<td>Unknown</td>
				<td class='text-center'><?=$numUnknown?></td>
			</tr>
		<?php endif ?>
		
		<tr style='border-top:solid 1px'>
			<th>
				<em>Total Participants:</em>
			</th>
			<th class='text-center'>
				<em><?=$numParticipants?></em>
			</th>
		</tr>
	
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
