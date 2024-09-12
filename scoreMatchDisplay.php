<?php
/*******************************************************************************


*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Match Score';
$hideEventNav = true;
$hidePageTitle = true;
$hideFooter = true;
$lockedTournamentWarning = true;
$jsIncludes[] = 'score_scripts.js';
include_once('includes/config.php');

$matchID = $_SESSION['matchID'];
$tournamentID = $_SESSION['tournamentID'];
$eventID = $_SESSION['eventID'];

if(ALLOW['EVENT_SCOREKEEP'] == false || $matchID == null || $tournamentID == null || $eventID == null){
	redirect('infoSummary.php');
} else {

	$matchInfo = getMatchInfo($matchID, $tournamentID);


	if($matchInfo['matchType'] == 'pool'){
		// If it is a pool we shrink the div and add a match que
		$mainDivSize = 'large-10';
	} else {
		$mainDivSize = 'large-12';
	}


// FAKE HEADER /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$vJ = '?=1.0.8'; // Javascript Version
$vC = '?=1.0.6'; // CSS Version
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



</head>


	<style>
		li.fighter_1_color {
			border-bottom-color: <?= COLOR_CODE_1 ?>;
		}
		li.fighter_2_color {
			border-bottom-color: <?= COLOR_CODE_2 ?>;
		}
		.f1-BG {
			background-color: <?= COLOR_CODE_1 ?>;
		}
		.f2-BG {
			background-color: <?= COLOR_CODE_2 ?>;
		}
		.f1-text {
			color: <?= COLOR_CONTRAST_CODE_1 ?>;
		}
		.f2-text {
			color: <?= COLOR_CONTRAST_CODE_2 ?>;
		}
	</style>

	<body style="height: 100vh; background-color:black">

<?php

	if(isset($_SESSION['flipMatchSides']) && $_SESSION['flipMatchSides'] == true)
	{
		$leftFighter = 2;
		$rightFighter = 1;
	} else {
		$leftFighter = 1;
		$rightFighter = 2;
	}


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>



	<script>
		// Auto-refresh
		window.onload = function(){
			refreshOnNewExchange(<?=$matchID?>, <?=$matchInfo['lastExchange']?>);
		}
	</script>

	<div class='grid-x'>
	<div class='<?=$mainDivSize?>'>

		<div class='grid-y medium-grid-frame'
			style='height: 100vh; font-size:0;'>

			<div class='cell shrink'>

				<div class='cell shrink text-center align-middle'style='background:black; vertical-align: middle;'>

					<span style='display:inline-block; height:100%; padding-right: 20px;vertical-align: top;'>
						<img src="includes/images/favicon2.png" style='height:7vh; '>
					</span>

					<span class='white-text' style='font-size:5vh'>HEMA Scorecard</span>

					<span style='display:inline-block; height:100%; vertical-align: top; padding-left: 20px'>
						<img src="includes/images/favicon2.png" style='height:7vh'>
					</span>
				</div>

				<div class='cell shrink medium-cell-block-container'>
					<div class='grid-x grid-padding-x'>
						<?=fighterNameDisplay($matchInfo,$leftFighter,'left')?>
						<?=fighterNameDisplay($matchInfo,$rightFighter,'right')?>
					</div>
				</div>

				<div class='cell shrink '>
					<div class='grid-x grid-padding-x'>
						<?=fighterSchoolDisplay($matchInfo,$leftFighter,'left')?>
						<?=fighterSchoolDisplay($matchInfo,$rightFighter,'right')?>
					</div>
				</div>

				<div class='cell auto'>
					<div class='grid-x grid-padding-x'>
						<?=fighterScoreDisplay($matchInfo,$leftFighter,'left')?>

						<?=matchInfoDisplay($matchInfo)?>

						<?=fighterScoreDisplay($matchInfo,$rightFighter,'right')?>
					</div>
				</div>

				<div class='cell auto'>
					<div class='grid-x grid-padding-x'>
						<?=displayFighterPenalties($matchInfo,1)?>
						<?=displayFighterPenalties($matchInfo,2)?>
					</div>
				</div>

			</div>
		</div>

	</div>

		<?=poolMatchQueue($matchInfo)?>

	</div>


<?php }

include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function poolMatchQueue($matchInfo){


	if($matchInfo['matchType'] != 'pool'){
		return;
	}

	$remainingMatches = [];
	$matches = getRemainingPoolMatches($matchInfo);
	$matchesShown = 0;
	$matchesHidden = 0;

	foreach($matches as $m){

		if($matchesShown < 3){
			$tmp = [];
			$tmp['name'][1] = getFighterName($m['fighter1ID']);
			$tmp['name'][2] = getFighterName($m['fighter2ID']);
			$remainingMatches[] = $tmp;
			$matchesShown++;
		} else {
			$matchesHidden++;
		}
	}

	if($matchesHidden > 0){
		$moreMatchesText = "<i>And {$matchesHidden} more ...</i>";
	} else {
		//$moreMatchesText = "- End of Pool ----";
		$moreMatchesText = "--------------------<BR> End of Pool";
	}

?>

	<div class='show-for-large large-2' style='border-left: 4px solid black; background-image: linear-gradient(to right, #777, #333)'>



		<div style='padding: 10px; height: 7.5vh; background-color: black; color:white; text-align: right;'>
			<h3>On Deck</h3>
		</div>

		<?php foreach($remainingMatches as $m): ?>

			<div style='margin: 10px'>
				<table>
					<tr>
						<td rowspan=2 style='background-color:darkblue; color: white'>
							<i>vs</i>
						</td>
						<td style='font-size:2em' class='f1-BG f1-text'>
							<?=$m['name'][1]?>
						</td>
					</tr>
					<tr>
						<td style='font-size:2em' class='f2-BG f2-text'>
							<?=$m['name'][2]?>
						</td>
					</tr>

				</table>

			</div>
		<?php endforeach ?>

		<div style='margin: 10px; color: white; font-size:2em;'>
			<?=$moreMatchesText?>
		</div>


	</div>

<?php
}

/******************************************************************************/

function fighterNameDisplay($matchInfo, $fighterNum, $pageSide){
	$class = '';
	if($fighterNum == 1){

		$fighterName = getFighterName($matchInfo['fighter1ID']);
		$class = 'f1-BG f1-text';
		$border = 'right';
		if($matchInfo['winnerID'] == $matchInfo['fighter1ID']){
			$class .= ' bold';
		}
	} else {
		$fighterName = getFighterName($matchInfo['fighter2ID']);
		$class = 'f2-BG f2-text';
		$border = 'left';
		if($matchInfo['winnerID'] == $matchInfo['fighter2ID']){
			$class .= ' bold';
		}
	}

	if($pageSide == 'left'){
		$class .= ' text-left';
	} else {
		$class .= ' text-right';
	}

?>

	<div class='small-6 cell medium-cell-block-y <?=$class?>'
		style='border-<?=$border?>: 2px solid black;'>

		<span style='font-size: 5.5vw;'>

			<?=$fighterName?>

		</span>

	</div>

<?php
}

/******************************************************************************/

function fighterSchoolDisplay($matchInfo, $fighterNum, $pageSide){
	$class = '';

	$class1 = 'f1-BG f1-text';
	$class2 = 'f2-BG f2-text';

	if($fighterNum == 1){
		$schoolName = $matchInfo['fighter1School'];
		$class .= $class1;
		$classOpposite .= $class2;
		$border = 'right';
		if($matchInfo['winnerID'] == $matchInfo['fighter1ID']){
			$class .= ' bold';
		}
	} else {
		$schoolName = $matchInfo['fighter2School'];
		$class .= $class2;
		$classOpposite .= $class1;
		$border = 'left';
		if($matchInfo['winnerID'] == $matchInfo['fighter2ID']){
			$class .= ' bold';
		}
	}

	if($pageSide == 'left'){
		$class .= ' text-left';
	} else {
		$class .= ' text-right';
	}

	if(readOption('T',$matchInfo['tournamentID'],'PRIORITY_NOTICE_ON_NON_SCORING') != 0 && isLastExchZeroPointClean($matchInfo, $fighterNum) == true){
		$priorityText = "Priority";
	} else {
		$priorityText = "";
	}

?>

	<div class='small-6 cell medium-cell-block-y <?=$class?>'
		style='border-<?=$border?>: 2px solid black;'>
		<i>
			<?php if($priorityText != ''): ?>
				<span style='font-size:65px;' class='priority-text-notice <?=$classOpposite?>'> <?=$priorityText?></span>
			<?php elseif(isTeamLogic($matchInfo['tournamentID']) == false): ?>
				<span style='font-size:65px;'> <?=$schoolName?></span>
			<?php else: ?>
				<span style='font-size:65px;'>(<?=$teamName?>)</span>
			<?php endif ?>
		</i>
	</div>

<?php
}

/******************************************************************************/

function fighterScoreDisplay($matchInfo, $fighterNum, $pageSide){

	$class = '';
	if($fighterNum == 1){
		$colorName = COLOR_NAME_1;
		$class .= 'f1-BG f1-text';
		$score = $matchInfo['fighter1score'];

		if($matchInfo['winnerID'] == $matchInfo['fighter1ID']){
			$class .= ' bold';
		}
	} else {
		$colorName = COLOR_NAME_2;
		$class .= 'f2-BG f2-text';
		$score = $matchInfo['fighter2score'];

		if($matchInfo['winnerID'] == $matchInfo['fighter2ID']){
			$class .= ' bold';
		}
	}

	if($score === null){
		$score = "/";
	}

?>
	<div class='small-4 cell medium-cell-block-y text-center <?=$class?>'
		style='border-top: 4px solid black;'>

		<span style='font-size:30vh;'><?=$score?></span>

	</div>
<?php
}

/******************************************************************************/

function matchInfoDisplay($matchInfo){

	if(isset($matchInfo['endType'])){
		$endType = $matchInfo['endType'];
	} else {
		$endType = '';
	}

	$class = '';
	switch($endType){
		case 'winner':
			$endText1 = 'Winner';
			if($matchInfo['winnerID'] == $matchInfo['fighter1ID']){
				$class = 'f1-BG f1-text';
				$endText2 = COLOR_NAME_1;

			} elseif($matchInfo['winnerID'] == $matchInfo['fighter2ID']){
				$class = 'f2-BG f2-text';
				$endText2 = COLOR_NAME_2;
			}
			break;
		case 'tie':
			$endText1 = '';
			$endText2 = '<HR>- Draw -<HR>';
			break;
		case 'ignore':
			$endText1 = '';
			$endText2 = 'Match Incomplete';
			break;
		case 'doubleOut':
			$endText1 = "<span class='red-text'>DOUBLE OUT</span>";
			$endText2 = "<HR>No Winner<HR>";
			break;
		default:
			$endText1 = '';
			$endText2 = '';
			break;
	}


?>

	<div class='small-4 cell text-center white-text' style='border: 4px solid black;'>

	<!-- Match has ended-->
		<?php if($endType != ''): ?>

			<span style='font-size:4vw'><?=$endText1?></span>
			<div class='match-winner-name black-text  <?=$class?> '>
				<span style='font-size:5.5vw;'>
					<?=$endText2?>

					</span>
			</div>

	<!-- Match is ignored -->
		<?php elseif($matchInfo['ignoreMatch']): ?>

			<h4>Match Incomplete</h4>

	<!-- Show timer -->
		<?php else: ?>

			<input type='hidden' class='matchTime' id='matchTime'
				name='matchTime' value='<?=$matchInfo['matchTime']?>'>
			<input type='hidden' id='timeLimit' value='<?=$matchInfo['timeLimit']?>'>
			<input type='hidden' name='restartTimer' class='restart-timer-input' value='0'>
			<input type='hidden' name='hideNegativeTime' value='1' id='hideNegativeTime'>
			<input type='hidden' id='timerCountdown' value='<?=isTimerCountdown($matchInfo['tournamentID'])?>'>

			<script>
				window.addEventListener("load",function(event) {
						updateTimerDisplay();
					});
			</script>


			<div id='currentTimeDiv'>
				<span id='currentTime' style="font-size:20vh;">
					0:00
				</span>
			</div>

		<?php endif ?>

	<!-- Double hit text -->
		<?php doublesTextDisplay($matchInfo) ?>



	</div>

<?php
}

/******************************************************************************/

function doublesTextDisplay($matchInfo){
// adds smiley and frowny faces depending on the number of double hits
// adds button to declare match as a double out

	if(isDoubleHits($matchInfo['tournamentID']) == false){
		return;
	}

	$doubles = getMatchDoubles($matchInfo['matchID']);
	$reverseScore = isReverseScore($matchInfo['tournamentID']);
	$basePointValue = getBasePointValue($matchInfo['tournamentID'], $_SESSION['groupSet']);

	$doubleOut = false;

	if(    (int)$matchInfo['maxDoubles'] != 0
		&& (int)$doubles >= (int)$matchInfo['maxDoubles']){

		$doubleOut = true;

	} else if($reverseScore == REVERSE_SCORE_INJURY){

		if( 	(  $matchInfo['fighter1score'] <= 0
				&& $matchInfo['fighter2score'] <= 0
				&& $matchInfo['lastExchange'] != 0)
			|| ($basePointValue == 0)
			)
		{
			$doubleOut = true;
		}

	} elseif($reverseScore == REVERSE_SCORE_GOLF){

		if(		(  $matchInfo['fighter1score'] >= $basePointValue
				&& $matchInfo['fighter2score'] >= $basePointValue)
			&& $basePointValue != 0)
		{
			$doubleOut = true;
		}

	} else {

		$doubleOut = false;

	}

	$class = ifSet($doubleOut,"class='red-text'");
	$string = "{$doubles} Double".ifSet($doubles != 1, "s");

	switch ($doubles){
	case 0:
		$string .= " :)";
		break;
	case 1:
		break;
	case 2:
		$string .= " :(";
	default:
		for($i=2;$i<$doubles&&$i<9;$i++){
			$string .="!";
		}
		break;
	}
	?>

	<span <?=$class?> style='font-size:4vw;'>
		<?=$string?>
	</span>

<?php
}

/******************************************************************************/

function displayFighterPenalties($matchInfo, $num){
	$penaltyList = getFighterMatchPenalties($matchInfo['matchID'], $num);

	if($num == 2){
		$class = 'text-right';
	} else {
		$class = '';
	}
?>

	<div class='cell small-6 <?=$class?>'>

	<?php foreach($penaltyList as $penalty):

		switch($penalty['card']){
			case 'yellowCard':
				$class = 'penalty-card-yellow';
				break;
			case 'redCard':
				$class = 'penalty-card-red';
				break;
			case 'blackCard':
				$class = 'penalty-card-black';
				break;
			default:
				continue 2;
				break;
		}

		?>

		<span class='<?=$class?> penalty-card-display' style='font-size: 2.5vw'>
			<?=$penalty['name']?>
		</span>
	<?php endforeach ?>

	</div>

<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
