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

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['VIEW_ROSTER'] == false) {
	displayAlert("Event is still upcoming<BR>Roster not yet released");
} else {
	
	$roster = getEventRoster(null);
	$tournamentList = getTournamentsFull($_SESSION['eventID']);

	$numParticipants = count($roster);
	$numFighters = getNumEventFighters($_SESSION['eventID']);
	
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
			Total Event Participants
			<?=tooltip("Organizers will typically <b>NOT</b> enter non-fighting participants into Scorecard, and thus the total attendance may be higher than this number.")?>:
			<strong><?=$numParticipants?></strong><BR>

			Total Event Fighters
			<?=tooltip('Number of participants that fought in a tournament.')?>:
			<strong><?=$numFighters?></strong><BR>

			Total Tournament Registrations
			<?=tooltip('<u>Example</u>: If the same person fights in 3 tournaments they count as 3 registrations.')?>:
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
