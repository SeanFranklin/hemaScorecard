<?php
/*******************************************************************************
	T

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Event Video";
$createSortableDataTable[] = ['videoList',25];
include('includes/header.php');

// Get the event List
if((int)$_SESSION['eventID'] == 0){
	pageError('event');
} else if(ALLOW['VIEW_MATCHES'] == false){
	displayAlert("Matches for this event not yet published");
} if((int)$_SESSION['tournamentID'] == 0){
	pageError('tournament');
} else {

	$videoList = getTournamentVideo($_SESSION['tournamentID'], ALLOW['EVENT_VIDEO']);
	$colorNames = getTournamentColors($_SESSION['tournamentID']);
	$hide = getItemsHiddenByFilters($_SESSION['tournamentID'], $_SESSION['filters'],'match');

	// Suppress the sort-able table for editing the video list
	if(ALLOW['EVENT_VIDEO'] == true){
		$videoListID = '';
	} else {
		$videoListID = 'videoList';
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<p>Did you know that HEMA Scorecard allows you to attach a video link to matches? Here is a summary of the videos which have been attached to this event. If you are thinking <b>"Wow, this list sucks!"</b> you are probably right! HEMA Scorecard depends on a crew of passionate individuals to scour the land for video footage. If you are interested in helping, please get a hold of us.</p>

	<div class='callout alert'>
		<h3>This is not official tournament video</h3>
		These links are public videos on youtube/etc that HEMA Scorecard volunteers have attached to matches for your benefit.  If there is an issue with one of the links please contact the HEMA Scorecard team. <i>Do not complain to an event organizer about anything on this page.</i>
	</div>

	<?php if($videoList == []):?>
		<div class='callout secondary'>
			Sorry. Nothing here. :(
		</div>
	<?php else: ?>

		<?=activeFilterWarning()?>

		<h4>Video For: <b><u><?=getTournamentName($_SESSION['tournamentID'])?></u></b></h4>

		<table  id="<?=$videoListID?>" class="display">
		<thead>
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
		</thead>

		<tbody>
		<?php foreach($videoList as $match):

				if(isset($hide['match'][$match['matchID']]) == true){
					continue;
				}

				$matchInfo = getMatchInfo($match['matchID']);
				if($matchInfo['matchType'] == 'pool'){
					$name = "Match ".$matchInfo['matchNumber'];
				} else {
					$name = getMatchStageName($match['matchID']);
				}

				$name = "<a href='scoreMatch.php?m=".$match['matchID']."'>".$name."</a>";

			?>

			<tr>

				<form method="post">

				<td><?=getGroupName($matchInfo['groupID'])?></td>
				<td><?=$name?></td>
				<td><?=getFighterName($matchInfo['fighter1ID'])?></td>
				<td><?=getFighterName($matchInfo['fighter2ID'])?></td>


				<?php if(ALLOW['EVENT_VIDEO'] == false):?>
					<td><a href="<?=$match['sourceLink']?>"><?=$match['sourceLink']?></a></td>
				<?php else: ?>
					<td>
						<input type='hidden' name='updateVideoSource[matchID]' value='<?=$match['matchID']?>'>
						<input class='input-group-field' type='url' name='updateVideoSource[sourceLink]' value='<?=$match['sourceLink']?>' >
					</td>
					<td>
						<button class="button tiny success no-bottom"  name='formName'  value='updateVideoSource' >
							Update
						</button>
					</td>
				<?php endif?>

				</form>
			</tr>

		<?php endforeach ?>

		</tbody>
		</table>
	<?php endif ?>

	<?=changeParticipantFilterForm($_SESSION['eventID'])?>


<?
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/



/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
