<?php
/*******************************************************************************

	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Filter Matches By School';
include('includes/header.php');
$createSortableDataTable[] = ['matchesBySchoolTable',25];

$eventID = (int)$_SESSION['eventID'];
if($eventID == null){
	pageError('event');
} elseif(ALLOW['VIEW_MATCHES'] == false) {
	displayAlert("Event is still upcoming");
} else {

	$allMatches = getMatchesBySchool($eventID, $_SESSION['filterForSchoolID']);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?php if($_SESSION['filterForSchoolID'] != 0): ?>
		<form method='POST'>
		<h3>Only Showing <b><?=getSchoolName($_SESSION['filterForSchoolID'])?></b>
			<button class='button' name='formName' value='filterForSchoolID'>Clear</button>
			<input type='hidden' name='schoolID' value='0'>
		</h3>
		</form>
	<?php endif ?>
	<?=changeClubFilterDropdown($_SESSION['eventID'])?>

	<table  id="matchesBySchoolTable" class="display">
		<thead>
				<th>Tournament</th>
				<th>Group</th>
				<th>Match</th>
				<th class='text-right'>Fighter 1</th>
				<th class='text-right'></th>
				<th class='text-left'></th>
				<th class='text-left'>Fighter 2</th>
				<th></th>
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
				<td><?=$isComplete?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
		
<?php 		
	
}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/



/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
