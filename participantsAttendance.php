<?php
/*******************************************************************************

	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Filter Matches By School';
$hidePageTitle = true;
include('includes/header.php');
$createSortableDataTable[] = ['matchesBySystemRosterID',25];

$eventID = (int)$_SESSION['eventID'];
{

	$attendanceList = (array)getAttendanceBySystemRosterID($_SESSION['filterForSystemRosterID']);

	$HemaRatingsID = (int)hemaRatings_getFighterID($_SESSION['filterForSystemRosterID']);
	if($HemaRatingsID == 0){
		$HemaRatingsID = "<i>not mapped</i>";
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?php if($_SESSION['filterForSystemRosterID'] != 0): ?>
		<form method='POST'>
		<h3>Showing: <b><?=getFighterNameSystem($_SESSION['filterForSystemRosterID'])?></b>
			<?=tooltip("???")?>
			<button class='button' name='formName' value='filterForSystemRosterID'>Clear</button>
			<input type='hidden' name='systemRosterID' value='0'>
		</h3>
		</form>
		[School: <b><?=getFighterSchoolNameSystem($_SESSION['filterForSystemRosterID'])?></b>]
		[ScorecardID: <?=$_SESSION['filterForSystemRosterID']?>]
		[HEMA Ratings ID: <?=$HemaRatingsID?>]
	<?php endif ?>

	<?=changeRosterFilterDropdown()?>

	<table  id="matchesBySystemRosterID" class="display">
		<thead>
				<th>Year</th>
				<th>Event</th>
				<th>Tournament</th>
				<th>Match</th>
				<th>Fighter1</th>
				<th></th>
				<th></th>
				<th>Fighter2</th>
		</thead>
		<tbody>
		<?php foreach($attendanceList as $event): ?>

			<?php if($event['matches'] == []): ?>
				<tr>
					<td><?=$event['year']?></td>
					<td><?=$event['name']?></td>
					<td>Did not compete</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			<?php endif ?>

			<?php foreach($event['matches'] as $m):

				$name = getGroupName($m['groupID']);

				if($m['groupType'] == 'pool'){
					$name .= ", Match ".$m['matchNumber'];
					$page = 'scoreMatch.php';
					
				} elseif($m['groupType'] == 'round') {
					$page = 'scorePiece.php';
					$m['fighter2ID'] = 0;
					$m['fighter2Score'] = "";
				} else {
					$name .= ", ".getMatchStageName($m['matchID']);
					$page = 'scoreMatch.php';
				}

				$params = "?e=".$m['eventID'];
				$params .= "&t=".$m['tournamentID'];
				$params .= "&m=".$m['matchID'];
				$nameTag = "<a href='{$page}".$params."'>".$name."</a>";

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
					<td><?=$event['year']?></td>
					<td><?=$event['name']?></td>
					<td><?=getTournamentName($m['tournamentID'])?></td>
					<td><?=$nameTag?></td>
					<td class='<?=$f1class?>'><?=getFighterName($m['fighter1ID'])?></td>
					<td class='<?=$f1class?>'><?=$m['fighter1Score']?></td>
					<td class='<?=$f2class?>'><?=$m['fighter2Score']?></td>
					<td class='<?=$f2class?>'><?=getFighterName($m['fighter2ID'])?></td>
				</tr>
			<?php endforeach ?>

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
