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

	$clubTotals = getAttendanceFromSchools($_SESSION['eventID']);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Participant summary -->

	<div class='grid-x align-center'>
	<div class='large-6 medium-10 small-12'>


	<?=showEventFighterCounts($_SESSION['eventID'])?>

	<?=showEventReg()?>
	<BR>

<!-- School registrations summary -->

	<table class='data_table'>
			<tr>
				<th>School</th>
				<th>Participants</th>
				<th>Fighters</th>
			</tr>
		<?php foreach((array)$clubTotals as $index => $school):
			if($school['schoolID'] == 1){
				$unknownIndex = $index;
				continue;
			}
			if($school['schoolID'] == 2){
				$unaffiliatedIndex = $index;
				continue;
			}
			$name = getSchoolName($school['schoolID'], 'full', 'Branch');
			?>

			<tr>
				<td><?=$name?></td>
				<td class='text-center'><?=$school['numTotal']?></td>
				<td class='text-center'><?=$school['numFighters']?></td>
			</tr>

		<?php endforeach ?>

		<?php if(isset($unaffiliatedIndex) == true): ?>
			<tr>
				<td><i>Unaffiliated</i></td>
				<td class='text-center'><?=$clubTotals[$unaffiliatedIndex]['numTotal']?></td>
				<td class='text-center'><?=$clubTotals[$unaffiliatedIndex]['numFighters']?></td>
			</tr>
		<?php endif ?>

		<?php if(isset($unknownIndex) == true): ?>
			<tr>
				<td><i>Unknown</i></td>
				<td class='text-center'><?=$clubTotals[$unknownIndex]['numTotal']?></td>
				<td class='text-center'><?=$clubTotals[$unknownIndex]['numFighters']?></td>
			</tr>
		<?php endif ?>

	</table>

	</div>
	</div>

<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function showEventReg(){


	$tData = (array)getTournamentsFull($_SESSION['eventID']);
	$displayList = sortTournamentAndDivisions($_SESSION['eventID']);

	foreach($displayList as $index => $t){
		if(isset($t['divisionID']) == true){
			$displayList[$index]['count'] = 0;
			foreach($t['tournaments'] as $item){
				$displayList[$index]['count'] += $tData[$item['tournamentID']]['numFighters'];
			}
		}
	}

?>

	<table>
	<caption>Participant Numbers</caption>

	<?php foreach($displayList as $t): ?>


		<?php if(isset($t['divisionID']) == false):?>
			<?=showTournamentRow($t['name'], $tData[$t['tournamentID']])?>
		<?php else: ?>
			<tr>
				<td>
					<?=$t['name']?>
					<a onclick="$('.item-for-<?=$t['divisionID']?>').toggleClass('hidden')">↓</a>
				</td>
				<td  class='text-right'><?=$t['count']?></td>
			</tr>
		<?php endif ?>

		<?php if(isset($t['divisionID']) == true):?>
			<?php foreach($t['tournaments'] as $item):?>
				<?=showTournamentRow($item['name'], $tData[$item['tournamentID']], $t['divisionID'])?>
			<?php endforeach?>
		<?php endif ?>


	<?php endforeach ?>

	</table>

<?
}

/******************************************************************************/


function showTournamentRow($name, $data, $divItemFor = 0){

	if($divItemFor != 0){
		$rowClass = "hidden item-for-{$divItemFor}";
	} else {
		$rowClass = "";
	}

	if($divItemFor != 0){
		$divStyle = 'padding-left: 30px; font-style: italic';
		$numberClass = "";
	} else {
		$divStyle = '';
		$numberClass = "text-right";
	}

?>

	<tr class='<?=$rowClass?>'>

		<td style='<?=$divStyle?>'>
			<?=$name?>
		</td>

		<td class='<?=$numberClass?>'>

			<?=$data['numFighters']?>

			<?php if($data['isTeams'] != 0): ?>
				(<i><?=$data['numParticipants']?> teams</i>)
			<?php endif ?>

		</td>
	</tr>

<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
