<?php
/*******************************************************************************


*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Match Score';
$hideEventNav = true;
$hidePageTitle = true;
$hideFooter = true;
$jsIncludes[] = 'score_scripts.js';
$jsIncludes[] = 'video_scripts.js';
include_once('includes/config.php');

$vJ = '?=1.0.8'; // Javascript Version
$vC = '?=1.0.6'; // CSS Version


/////////////////////////////////////
$backgroundColor = 'black';
if(ALLOW['SOFTWARE_ADMIN'] == true){
	$backgroundColor = 'white';
}
////////////////////////////////////

$locationID = (int)@$_SESSION['stream']['locationID'];
$matchID = (int)@$_SESSION['stream']['matchID'];
$streamInfo['streamMode'] = (int)@$_SESSION['stream']['mode'];

if($streamInfo['streamMode'] == VIDEO_STREAM_LOCATION){

	$streamInfo = (array)getStreamForLocation($locationID);

} else {

	$videoInfo = getMatchVideoLink($matchID);
	$streamInfo['sourceLink'] = $videoInfo['sourceLink'];
	$streamInfo['synchTime'] = $videoInfo['synchTime'];
	$streamInfo['synchTime2'] = $videoInfo['synchTime2'];
	$streamInfo['overlayEnabled'] = $videoInfo['overlayEnabled'];
	$streamInfo['locationID'] = $locationID;
	$streamInfo['opacity'] = 65;
}

$streamInfo['sourceType'] = getVideoSourceType($streamInfo['sourceLink']);
$streamInfo = parseYoutubeLink($streamInfo);

if($streamInfo['sourceType'] == VIDEO_SOURCE_UNKNOWN){
	echo "<h3>!! Error !!!!!</h3>
		Link provided <b>'{$streamInfo['sourceLink']}'</b> is not of playable format.
		<BR>(If this is a software error please try closing this view window, reloading the main scorecard page, and re-opening the livestream link. If it is a bad link then nothing can save you.)";
} else {

	$backgroundColor = 'black';
	if(ALLOW['SOFTWARE_ADMIN'] == true || $streamInfo['sourceType'] == VIDEO_SOURCE_NONE){
		$backgroundColor = 'white';
	}

// FAKE HEADER \/////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

?>

<!doctype html>
<html class="no-js" lang="en" dir="ltr">

<script>
	<?php
		// Output base URL of site for Javascript use
		$b = BASE_URL;
		echo "var BASE_URL = '$b';";
	?>
</script>

<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="
		HEMA Scorecard is a free online software application for running
		Historical European Martial Arts tournaments and making the information
		easily accessible.
	">
	<meta name="keywords" content="HEMA, Tournament, Historical European Martial Arts, Martial Arts, Sword">
	<title>HEMA Scorecard</title>
<?php include("includes/head_scripts.php"); ?>

	<?=streamStylesheet($streamInfo)?>

</head>

<body >

<?php

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<input type='hidden' id='stream-locationID' value=<?=$locationID?>>
	<input type='hidden' id='stream-video-source' value=<?=$streamInfo['sourceType']?>>
	<input type='hidden' id='stream-matchID' value=<?=$streamInfo['matchID']?>>
	<input type='hidden' id='stream-mode' value=<?=$streamInfo['streamMode']?>>
	<input type="hidden" id="stream-overlay-transparency">

	<?php if($streamInfo['overlayEnabled'] == true): ?>
		<script>
			window.onload = function(){ setInterval(updateStream,1000); };
		</script>
	<?php endif ?>

	<?=virtualStreamControl($streamInfo)?>

	<div class="holder" id='video-holder' >
		<div class="video-container">

			<?=youtubeFrame($streamInfo)?>
			<?=googleDriveFrame($streamInfo)?>

			<?=streamOverlay($streamInfo)?>

		</div>
	</div>

<?php
}

include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/******************************************************************************/

function virtualStreamControl($streamInfo){

	if($streamInfo['streamMode'] != VIDEO_STREAM_VIRTUAL){
		return;
	}

	$transparency = 100 - $streamInfo['opacity'];

?>

	<div class='grid-x grid-margin-x'>
		<div class='input-group medium-3 no-bottom cell'>
			<span class='input-group-label'>
				Synch Time
				<?=tooltip("The time when the director calls 'FIGHT' to start the <b><u>second</u></b> exchange.<BR><BR>
				<i>(I know it sounds weird, but we need to also synch the judge time to the fight time.)</i>")?>
			</span>
			<input class='input-group-field' type='number' id="stream-synch-time" value=<?=$streamInfo['synchTime']?>>
		</div>


		<div class='input-group medium-6  no-bottom cell'>
			<span class='input-group-label'>Overlay Transparency:</span>
			<div class="slider input-group-field no-bottom"
				data-slider data-initial-start="<?=$transparency?>" data-end="100">
				<span class="slider-handle" data-slider-handle role="slider" tabindex="1"></span>
				<span class="slider-fill" data-slider-fill></span>
			</div>
		</div>

		<div class='input-group medium-3 no-bottom cell'>
			<input class='input-group-field' type='number' id="stream-synch-time-2" value=<?=$streamInfo['synchTime']?>>
			<span class='input-group-label'>
				Synch Time 2
				<?=tooltip("The time when the director calls 'FIGHT' to start the first exchange.<BR><BR>
				<i>(Not neceassary but the clock will be out of synch for the first exchange without it.)</i>")?>
			</span>

		</div>


	</div>

<?php
}

/******************************************************************************/

function streamOverlay($streamInfo){

	if($streamInfo['overlayEnabled'] == false){
		return;
	}
?>

	<!-- Upper overlay. Scores and match information -->
	<div id="overlay" class='overlay-container'>
	<div class='grid-x align-middle overlay'>
		<div class='shrink cell' id='color1Div'>
			<div class='grid-x align-middle'>
				<div class='shrink cell'>
					<span class='overlay-score overlay-left' id='fighter1Score'>-</span>
				</div>
				<div class='shrink cell overlay-div'>
					<span class='overlay-name' id='fighter1Name'>----</span><BR>
					<span class='overlay-school hide-for-small-only' id='fighter1School'>----</span>
				</div>
			</div>


		</div>

		<div class='auto text-center cell' >

			<div >
				<div class='hide-for-small-only'><span class='overlay-school' id='tournamentName'>------</span><BR></div>
				<span class='overlay-name' id='matchName'>----</span>
			</div>
		</div>

		<div class='shrink cell' id='color2Div'>
			<div class='grid-x align-middle'>

				<div class='shrink cell'>
					<span class='overlay-name overlay-left' id='fighter2Name'>----</span><BR>
					<span class='overlay-school overlay-left hide-for-small-only' id='fighter2School'>----</span>
				</div>
				<div class='shrink cell overlay-div'>
					<span class='overlay-score' id='fighter2Score'>-</span>
				</div>
			</div>
		</div>
	</div>
	</div>

	<!-- Lower overlay. Last exchange, match time, and doubles -->
	<div id="overlay2" class='overlay-container'>
	<div class='grid-x align-middle overlay'>
		<div class='small-5'>
			<div class='grid-x align-middle overlay'>
			<div class='shrink'>
				<span class='overlay-school overlay-left hide-for-small-only'>Last Exchange: </span>
				<span class='overlay-name overlay-left show-for-small-only'>Last: </span>
			</div>
			<div class='shrink' id='lastExchange'>
				<span class='overlay-name overlay-left' id='exchangeType'>
				&nbsp;
				</span><BR>
				<span class='overlay-name overlay-left' id='exchangePoints'>
				&nbsp;
				</span>
			</div>
			</div>

		</div>
		<div class='small-2 text-center'>
			<span class='overlay-score' id='timeDiv'>0:00</span>
		</div>

		<div class='small-2 text-right'>
			<span class='overlay-name' id='doublesDiv'></span>
		</div>

		<div class='small-3 text-right'>
			<img src="includes/images/hema_scorecard_logo_white_rgb_300px.png">
		</div>
	</div>
	</div>


<?php
}

/******************************************************************************/

function streamStylesheet($streamInfo){

	$opacity = (int)$streamInfo['overlayOpacity'];
	if($opacity < 0){
		$opacity = 0;
	} elseif($opacity > 100) {
		$opacity = 100;
	} else {
		// Good value
	}

	$opacity = $opacity/100;

?>
	<style>
	.video-container {

	}

	.video-container iframe,
	.video-container object,
	.video-container embed {

	}


	.video-container #overlay {

	}

	.video-container #overlay2 {

	}

	#overlay, #overlay2 {

	}

	</style>
<?php
}

/******************************************************************************/

function googleDriveFrame($streamInfo){

	if($streamInfo['sourceType'] != VIDEO_SOURCE_GOOGLE_DRIVE){
		return;
	}
?>

	<iframe src="<?=$streamInfo['sourceType']?>"
		width="640" height="480" allow="autoplay">
	</iframe>

<?php
}

/******************************************************************************/

function youtubeFrame($streamInfo){

	if($streamInfo['sourceType'] != VIDEO_SOURCE_YOUTUBE){
		return;
	}

?>

	<!-- 1. The <iframe> (and video player) will replace this <div> tag. -->
	<div id="player"></div>

	<script>

	// 2. This code loads the IFrame Player API code asynchronously.
		var tag = document.createElement('script');

		tag.src = "https://www.youtube.com/iframe_api";
		var firstScriptTag = document.getElementsByTagName('script')[0];
		firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

	// 3. This function creates an <iframe> (and YouTube player) after the API code downloads.
		var player;
		function onYouTubeIframeAPIReady() {
			player = new YT.Player('player', {
				height: '390',
				width: '640',
				videoId: '<?=$streamInfo['youtubeID']?>',

				playerVars: {
					'playsinline': 1,
					start: <?=$streamInfo['ytStart']?>
				},
				events: {
					'onReady': onPlayerReady,
					'onStateChange': onPlayerStateChange
				}
			});
		}

	// 4. The API will call this function when the video player is ready.
		function onPlayerReady(event) {
			//event.target.playVideo();
		}

	// 5. The API calls this function when the player's state changes.
	//    The function indicates that when playing a video (state=1),
	//    the player should play for six seconds and then stop.
		var done = false;

		function onPlayerStateChange(event) {
			if (event.data == YT.PlayerState.PLAYING && !done) {
				//setTimeout(stopVideo, 6000);
				done = true;
			}
		}

		function stopVideo() {
			player.stopVideo();
		}
	</script>

<?php
}



/******************************************************************************/

function parseYoutubeLink($streamInfo){

	if($streamInfo['sourceType'] != VIDEO_SOURCE_YOUTUBE){
		return ($streamInfo);
	}

	$sourceLink = $streamInfo['sourceLink'];

	$linkComponents = parse_url($sourceLink);
	if(isset($linkComponents['query']) == true){
		parse_str($linkComponents['query'] ,$params);
	} else {
		$params = [];
	}

	if(isset($params['v']) == true){
		$streamInfo['youtubeID'] = $params['v'];
	} elseif($linkComponents['path'] != '/watch'){
		$streamInfo['youtubeID'] = substr($linkComponents['path'], strrpos($linkComponents['path'], "/") + 1);
	} else {
		$streamInfo['placeType'] =  VIDEO_SOURCE_UNKOWN;
	}

	if(isset($params['t']) == true){
		if($streamInfo['synchTime'] == 0){
			$streamInfo['synchTime'] = (int)$params['t'];
		}
		if($streamInfo['synchTime2'] == 0){
			$streamInfo['synchTime2'] = (int)$params['t'];
		}
		$streamInfo['ytStart'] = (int)$params['t'];

	} else {
		$streamInfo['ytStart'] = 0;
	}

	return($streamInfo);
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
