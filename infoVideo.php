<?php 
/*******************************************************************************
	T
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Event Video";

include('includes/header.php');

// Get the event List
if((int)$_SESSION['eventID'] == 0){
	pageError('event');
} else if(ALLOW['VIEW_MATCHES'] == false){
	displayAlert("Matches for this event not yet published");
} if((int)$_SESSION['tournamentID'] == 0){
	pageError('tournament');
} else {
	
	$tournamentID = $_SESSION['tournamentID'];

	if(ALLOW['EVENT_VIDEO'] == false){
		$isVideoClause = "AND videoLink IS NOT NULL";
	} else {
		$isVideoClause = '';
	}

	$sql = "SELECT matchID, videoLink
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			{$isVideoClause}";
	$videoList = (array)mysqlQuery($sql, ASSOC);
	$colorNames = getTournamentColors($tournamentID);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>
	
	<p>Did you know that HEMA Scorecard allows you to attach a video link to matches? Here is a summary of the videos which have been attached to this event. If you are thinking <b>"Wow, this list sucks!"</b> you are probably right! HEMA Scorecard depends on a crew of passionate individuals to scour the land for video footage. If you are interested in helping, please get a hold of us.</p>

	<?php if($videoList == []):?>
		<div class='callout secondary'>
			Sorry. Nothing here. :(
		</div>
	<?php else: ?>

		<h4>Video For: <b><u><?=getTournamentName($_SESSION['tournamentID'])?></u></b></h4>

		<table>
			<tr>
				<th>Group</th>
				<th>Match</th>
				<th><?=$colorNames[1]?></th>
				<th><?=$colorNames[2]?></th>
				<th>Link</th>
				<?php if(ALLOW['EVENT_VIDEO'] == true):?>
					<th>Update</th>
				<?php endif?>
			</tr>

		<?php foreach($videoList as $match):
				$matchInfo = getMatchInfo($match['matchID']);
				if($matchInfo['matchType'] == 'pool'){
					$name = "Match ".$matchInfo['matchNumber'];
				} else {
					$name = getMatchStageName($match['matchID']);
				}

				if(ALLOW['EVENT_VIDEO'] == true){
					$name = "<a href='scoreMatch.php?m=".$match['matchID']."'>".$name."</a>";
				}

			?>

			

			<tr>

				<form method="post">

				<td><?=getGroupName($matchInfo['groupID'])?></td>
				<td><?=$name?></td>
				<td><?=getFighterName($matchInfo['fighter1ID'])?></td>
				<td><?=getFighterName($matchInfo['fighter2ID'])?></td>
				

				<?php if(ALLOW['EVENT_VIDEO'] == false):?>
					<td><a href="<?=$match['videoLink']?>"><?=$match['videoLink']?></a></td>
				<?php else: ?>
					<td>
						<input type='hidden' name='matchID' value='<?=$match['matchID']?>'>
						<input class='input-group-field' type='url' name='url' value='<?=$match['videoLink']?>' >
					</td>
					<td>
						<button class="button tiny success no-bottom"  name='formName'  value='videoLink' >
							Update
						</button>
					</td>
				<?php endif?>

				</form>
			</tr>



		<?php endforeach ?>

		</table>
	<?php endif ?>
	

<?
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/



/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
