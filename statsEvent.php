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
	displayAnyErrors('No Event Selected');
} elseif(USER_TYPE < USER_ADMIN && USER_TYPE != USER_STATS){	
	displayAnyErrors('Please log in to view event information');
} else {
	
	$roster = getEventRoster(null);
	$tournamentList = getTournamentsFull(null);

	$numParticipants = count($roster);
	
	if($roster != null){
		foreach($roster as $fighter){
			$schoolID = $fighter['schoolID'];
			$clubTotals[$schoolID]++; 
		}
		arsort($clubTotals);
	}
	$numUnknown = $clubTotals[1];
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>	
	
<!-- Participant summary -->

	<div class='grid-x align-center'>
	<div class='large-6 medium-10 small-12'>
	
	
	<div class='callout text-center'>
		<h5>
			Total Event Participants:
			<strong><?=$numParticipants?></strong>
		</h5>
	</div>
	
	<table>
	<caption>Participant Numbers</caption>
	
	<?php foreach($tournamentList as $ID => $tournament): 
		$name = getTournamentName($ID);
		$numbers = $tournament['numParticipants'];
		$total += $numbers;
		?>
		
		<tr>
			<td><?=$name?></td>
			<td class='text-center'><?=$numbers?></td>
		</tr>
	<?php endforeach ?>
	
		<tr style='border-top:solid 1px'>
			<th>
				<em>Total Entries:</em>
			</th>
			<th class='text-center'>
				<em><?=$total?></em>
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
