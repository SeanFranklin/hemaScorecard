<?php
/*******************************************************************************
	Info Summary

	Displays all the tournament medalists
	Login:
		- ADMIN or above can add/remove final medalists

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Event Livestreams";
$jsIncludes[] = 'video_scripts.js';
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} else {

	$ringList = getEventVideoStreams($_SESSION['eventID']);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?=instructionsDisplay()?>

	<form method="POST">

		<table class='stack'>
			<?php foreach($ringList as $ring):?>

				<?=streamLocationInfoDisplay($ring)?>
				<?=streamLocationInfoEntry($ring)?>

			<?php endforeach ?>
		</table>

		<?php if(ALLOW['EVENT_MANAGEMENT'] == true):?>
			<button class='button success' name='formName' value="videoStreamSetLocations">Update</button>
		<?php endif ?>
	</form>



<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function instructionsDisplay(){
	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}
?>


	<a onclick="$('.livestream-help').toggle()" class='livestream-help'>
		Help! How does this work?<BR>
	</a>


	<div class='livestream-help hidden callout' >

		<h5>Match Streaming Locations</h5>
		<p>Streaming setup is done on a per-ring basis. First you must configure your rings using <a href='logisticsLocations.php'>Event Locations</a>, and then assign pools/matches to rings. If a livestream is active on a ring the table will receive prompts to continue to advance the active match for the display.</p>

		<h5>Live Rings</h5>
		<p>Prior to entering a Ring as <b>Live</b> the stream will not be viewable by anyone outside the event staff.</p>
		<p>If you have live rings that don't have a video source link they are viewable by anyone who navigates to <i>www.hemascorecard.com/videoLivestream.php</i>, but there won't be a way to navigate there from the main site. (This enables you to set up streaming overlays without needing to log in.) </p>
		<p>Once a live ring is configured with a video source, a notification of the active livestream will show up on the top of the page for all visitors.</p>

		<h5>Video Source Link</h5>
		<p>Currently Scorecard only supports playing videos from YouTube and Google Drive. If you leave the link empty an overly only will be generated.</p>
		<p><u>If you have plans to use a different platform let us know and we can prioritize getting it ready for you.</u></p>

		<h5>Watching The Stream</h5>
		<p>As an event organizer you can click on the big <b>Watch Ring</b> button at any time. If the stream is not live the button will be grey, but you can still click on it to test what someone would see if the stream was live, for testing purposes.<BR>
		The active match indication <u>does not update in real time</u>, and is stale until you reload the page. </p>

		<a onclick="$('.livestream-help').toggle()">
			Thanks, you can go away now.
		</a>
	</div>
	<BR>



<?php
}

/******************************************************************************/

function streamLocationInfoDisplay($info){

	if((int)$info['matchID'] == 0){
		$matchDescription = "No Active Match";
	} else {

		$matchInfo = getMatchInfo($info['matchID']);

		$matchDescription  = "<u>Active Match</u> <i>(at the time of opening this page)</i>";
		$matchDescription .= "<BR>";

		$matchDescription .= "<b>".getTournamentName($matchInfo['tournamentID'])."</b>";

		$matchDescription .= " | ";
		$matchDescription .= getGroupName($matchInfo['groupID']);

		$matchDescription .= " | ";
		if($matchInfo['matchType'] == 'pool'){
			$matchDescription .= "Match ".$matchInfo['matchNumber'];
		} else {
			$matchDescription .= getMatchStageName($info['matchID']);
		}

		$matchDescription .= "<BR>";
		$matchDescription .= getFighterName($matchInfo['fighter1ID']);
		$matchDescription .= " vs. ";
		$matchDescription .= getFighterName($matchInfo['fighter2ID']);
		$matchDescription .= "";

	}


?>
	<?php if($info['isLive'] == true): ?>

		<tr>
			<td style="width:0.1%; white-space: nowrap;">
				<a class='button hollow no-bottom'
					onclick="openVideoWindow(<?=$info['locationID']?>,<?=VIDEO_STREAM_LOCATION?>)">
					<h3 class='no-bottom'>Watch <b><?=$info['locationName']?></b></h3>
				</a>
			</td>

			<td colspan='100%'>
				<?=$matchDescription?>
			</td>
		</tr>

	<?php elseif(ALLOW['EVENT_MANAGEMENT'] == true): ?>

		<tr>
			<td style="width:0.1%; white-space: nowrap;">
				<a class='button hollow no-bottom secondary'
					onclick="openVideoWindow(<?=$info['locationID']?>,<?=VIDEO_STREAM_LOCATION?>)">
					<h3 class='no-bottom'>Watch <b><?=$info['locationName']?></b></h3>
				</a>
			</td>

			<td colspan='100%'>
				<?php
					echo $matchDescription;
				?>
			</td>
		</tr>

	<?php else: ?>

		<!-- Nothing to display -->

	<?php endif ?>

<?php
}

/******************************************************************************/

function streamLocationInfoEntry($info){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

	$nameStart = 'videoStreamSetLocations['.$info['locationID'].']';
	if($info['overlayOpacity'] === NULL){
		$info['overlayOpacity'] = 70;
	}
?>

	<tr>
		<input type='hidden' name='<?=$nameStart?>[locationID]' value=<?=$info['locationID']?>>
		<input type='hidden' name='<?=$nameStart?>[videoID]' value=<?=$info['videoID']?>>

		<td colspan='100%'>
			<div class='grid-x grid-margin-x'>

			<div class='input-group no-bottom cell large-3'>
				<span class='input-group-label'>Live:</span>
				<?=checkboxPaddle($nameStart.'[isLive]', 1, (bool)$info['isLive'], 0,null,'input-group-field')?>
			</div>

			<div class='input-group no-bottom cell large-3'>
				<span class='input-group-label'>Link:</span>
				<input class='input-group-field' type='text' placeholder='n/a'
					name='<?=$nameStart?>[sourceLink]' value='<?=$info['sourceLink']?>'>
			</div>

			<div class='input-group no-bottom cell large-3'>
				<span class='input-group-label'>
					Overlay %:
					<?=tooltip('How solid you want the overlay. <BR>70% is semi-transparent. <BR>0% will give the raw video with no overlay.')?>
				</span>
				<input class='input-group-field' type='number' min=0 max=100 placeholder='70%'
					name='<?=$nameStart?>[overlayOpacity]' value='<?=$info['overlayOpacity']?>'>
			</div>

			</div>
		</td>


	</tr>

	<tr>
		<td colspan='100%'><HR class='no-bottom'></td>
	</tr>


<?
}


/******************************************************************************/

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
