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

$matchID = (int)0;
$streamInfo['streamMode'] = VIDEO_STREAM_VIRTUAL;
$streamInfo['synchTime'] = 0;
$streamInfo['synchTime2'] = 0;
$streamInfo['overlayEnabled'] = true;
$streamInfo['locationID'] = 0;
$streamInfo['opacity'] = 65;

$streamInfo['overlayOpacity'] = 70;
$streamInfo['matchID'] = $matchID;
$streamInfo['sourceType'] = VIDEO_SOURCE_NONE;

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
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/6.4.3/css/foundation.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.foundation.min.css">

	<link href="https://fonts.googleapis.com/css?family=Chivo:300,400,700" rel="stylesheet">
	<link rel="stylesheet" href="includes/foundation/css/app.css">
	<link rel="stylesheet" href="includes/foundation/css/custom.css<?=$vC?>">

	<link rel='icon' href='includes\images\favicon.png'>

	<?=streamStylesheet($streamInfo)?>

</head>

<body style='background-color: <?=$backgroundColor?>;'>

<?php

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<input type='hidden' id='stream-locationID' value=0>
	<input type='hidden' id='stream-video-source' value=<?=$streamInfo['sourceType']?>>

	<input type='hidden' id='stream-mode' value=<?=$streamInfo['streamMode']?>>
	<input type="hidden" id="stream-overlay-transparency">

	<?php if($streamInfo['overlayEnabled'] == true): ?>
		<script>
			window.onload = function(){ setInterval(updateStream,1000); setInterval(updateStreamMatchTime,100);};

		</script>
	<?php endif ?>



	<div class="holder" id='video-holder' style='border:black 1px solid; width: 99%; aspect-ratio: 16:9; '>
		<div class="video-container">

			<?=streamOverlay($streamInfo)?>

		</div>
	</div>

	<?=virtualStreamControl($streamInfo)?>

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

	<input type='hidden' id="stream-synch-time" value=0>
	<input type='hidden' id="stream-synch-time-2" value=0>

	<div class='grid-x grid-margin-x' style='width:100%'>
		<div class='input-group medium-4 no-bottom cell'>
			<span class='input-group-label'>
				Real Time
			</span>
			<input class='input-group-field' type='number' id="stream-video-time" value=0>
			<span class='input-group-label'>
				Go:
			</span>
			<input type='checkbox' class='input-group-field' class='no-bottom'  id='stream-run-match' >
		</div>


		<div class='input-group medium-5  no-bottom cell'>

		</div>

		<div class='input-group medium-3 no-bottom cell'>
			<input class='input-group-field' type='number' id="stream-matchID" value=<?=$streamInfo['matchID']?>>
			<span class='input-group-label'>
				MatchID
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

			<div style='display: inline-block;'>
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
			<div class='shrink' style='background:#555;' id='lastExchange'>
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
		position: relative;
		padding-bottom: 56.25%; /* 16:9 */
		padding-top: 30px; /* size of chrome */
		height: 0;
		overflow: hidden;
	}

	.video-container iframe,
	.video-container object,
	.video-container embed {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
	}


	.video-container #overlay {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
	}

	.video-container #overlay2 {
		position: absolute;
		bottom: 0px;
		left: 0;
		width: 100%;
	}

	#overlay, #overlay2 {
		background:#000;
		opacity:<?=$opacity?>;
		/*background:rgba(255,255,255,0.8); or just this*/
		z-index:50;
		color:#fff;
		pointer-events: none;
	}

	</style>
<?php
}


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
