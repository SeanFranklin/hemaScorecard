<?php
/*******************************************************************************

	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Filter Matches By School';
include('includes/header.php');
$createSortableDataTable[] = ['matchesBySchoolTable',25];


if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['VIEW_MATCHES'] == false) {
	displayAlert("Event is still upcoming");
} else {

	$allMatches = getMatchesBySchool($_SESSION['eventID'], @$_SESSION['filters']['schoolID']);
	

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?php if($_SESSION['filters']['school'] != false): ?>
		<h3>Only Showing:
			<?php foreach((array)$_SESSION['filters']['schoolID'] as $schoolID):?>
			 <b><?=getSchoolName($schoolID)?>, </b>
			<?php endforeach ?>
			<?=changeParticipantFilterForm($_SESSION['eventID'])?>
		</h3>
		
		<?=displayMatchTable($allMatches)?>
	<?php else: ?>
		<div class='grid-x grid-padding-x'>
		<div class='large-5 medium-8 cell' style='border-right:1px solid black'>
			This page allows you to see all the matches for fighters of the specified schools.
			Please create a filter for the school(s) of intrest to see what everyone is doing.
		</div>
		<div class='large-2 medium-2 cell align-self-middle'>
			<?=changeParticipantFilterForm($_SESSION['eventID'])?>
		</div>
		</div>
		
	<?php endif ?>

	

	
		
<?php 		
	
}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayMatchTable($allMatches){
	$isArchived = isEventArchived($_SESSION['eventID']);
?>
	<table  id="matchesBySchoolTable" class="display">
		<thead>
				<th>Tournament</th>
				<th>Group</th>
				<th>Match</th>
				<th class='text-right'>Fighter 1</th>
				<th class='text-right'></th>
				<th class='text-left'></th>
				<th class='text-left'>Fighter 2</th>
				<?php if($isArchived == false):?>
					<th></th>
				<?php endif ?>
		</thead>
		<tbody>
		<?php foreach($allMatches as $m):

			if($m['groupType'] == 'pool'){
				$name = "Match ".$m['matchNumber'];
				$page = 'scoreMatch.php';
				
			} elseif($m['groupType'] == 'round') {
				$name = "- Go -";
				$page = 'scorePiece.php';
				$m['fighter2ID'] = 0;
				$m['fighter2Score'] = "";
			} else {
				$name = getMatchStageName($m['matchID']);
				$page = 'scoreMatch.php';
			}

			$nameTag = "<a href='{$page}?t=".$m['tournamentID']."&m=".$m['matchID']."'>".$name."</a>";

			if($m['matchComplete'] == 1){
				$isComplete = "✓";
			} elseif ($m['groupType'] == 'round' && $m['fighter1Score'] != 0){
				$isComplete = "✓";
			} else {
				$isComplete = "";
			}


			$f1class = "text-right";
			$f2class = "text-left";
			if($m['winnerID'] == $m['fighter1ID']){
				$f1class .= " bold";
			}
			if($m['winnerID'] == $m['fighter2ID']){
				$f2class .= " bold";
			}

			?>
			<tr>

				<td><?=getTournamentName($m['tournamentID'])?></td>
				<td><?=getGroupName($m['groupID'])?></td>
				<td><?=$nameTag?></td>
				<td class='<?=$f1class?>'><?=getFighterName($m['fighter1ID'])?></td>
				<td class='<?=$f1class?>'><?=$m['fighter1Score']?></td>
				<td class='<?=$f2class?>'><?=$m['fighter2Score']?></td>
				<td class='<?=$f2class?>'><?=getFighterName($m['fighter2ID'])?></td>
				<?php if($isArchived == false):?>
					<td><?=$isComplete?></td>
				<?php endif ?>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
<?php
}


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
