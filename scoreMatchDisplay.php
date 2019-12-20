<?php
/*******************************************************************************
	Match Scoring
	
	Scores a match
	LOGIN
		- STAFF and higher can score & conclude matches
		- YOUTUBE and higher can add links to youtube
		
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
	</style>

	<body style="height: 100vh; background-color:black">

<?php
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>



	<script>
		// Auto-refresh
		window.onload = function(){
			refreshOnNewExchange(<?=$matchID?>, <?=$matchInfo['lastExchange']?>);
		}
	</script>


	<div class='grid-y medium-grid-frame' 
		style='height: 100vh; font-size:0;'>

		<div class='cell shrink text-center align-middle'style='background:black;'>
			<span style='display:inline-block; height:100%; vertical-align: middle; padding-right: 20px'>
				<img src="includes/images/favicon2.png" style='height:5vh'>
			</span>
			
			<span class='white-text' style='font-size:5vh'>HEMA Scorecard</span>

			<span style='display:inline-block; height:100%; vertical-align: middle; padding-left: 20px'>
				<img src="includes/images/favicon2.png" style='height:5vh'>
			</span>
		</div>

		<div class='cell shrink medium-cell-block-container'>
			<div class='grid-x grid-padding-x'>
				<?=fighterNameDisplay($matchInfo,1)?>
				<?=fighterNameDisplay($matchInfo,2)?>
			</div>
		</div>

		<div class='cell shrink '>
			<div class='grid-x grid-padding-x'>
				<?=fighterSchoolDisplay($matchInfo,1)?>
				<?=fighterSchoolDisplay($matchInfo,2)?>
			</div>
		</div>

		<div class='cell auto'>
			<div class='grid-x grid-padding-x'>
				<?=fighterScoreDisplay($matchInfo,1)?>

				<?=matchInfoDisplay($matchInfo)?>

				<?=fighterScoreDisplay($matchInfo,2)?>
			</div>
		</div>

	</div>



<?php }
	
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/******************************************************************************/

function fighterNameDisplay($matchInfo, $num){
	$bold = '';
	if($num == 1){
		$colorCode = COLOR_CODE_1;
		$fighterName = getFighterName($matchInfo['fighter1ID']);
		$align = 'text-left';
		$border = 'right';
		if($matchInfo['winnerID'] == $matchInfo['fighter1ID']){
			$bold = 'bold';
		}
	} else {
		$colorCode = COLOR_CODE_2;
		$fighterName = getFighterName($matchInfo['fighter2ID']);
		$align = 'text-right';
		$border = 'left';
		if($matchInfo['winnerID'] == $matchInfo['fighter2ID']){
			$bold = 'bold';
		}
	}

?>
	<div class='small-6 cell medium-cell-block-y <?=$align?> <?=$bold?>' 
		style='background-color: <?=$colorCode?>; border-<?=$border?>: 2px solid;
		text-shadow: 0px 0px 1vh #FFF;'>
		<span style='font-size: 5.5vw;'> 
			
			<?=$fighterName?>
				
		</span>

	</div>
<?php
}

/******************************************************************************/

function fighterSchoolDisplay($matchInfo, $num){
	$bold = '';
	if($num == 1){
		$colorCode = COLOR_CODE_1;
		$schoolName = $matchInfo['fighter1School'];
		$align = 'text-left';
		$border = 'right';
		if($matchInfo['winnerID'] == $matchInfo['fighter1ID']){
			$bold = 'bold';
		}
	} else {
		$colorCode = COLOR_CODE_2;
		$schoolName = $matchInfo['fighter2School'];
		$align = 'text-right';
		$border = 'left';
		if($matchInfo['winnerID'] == $matchInfo['fighter2ID']){
			$bold = 'bold';
		}
	}

?>
	<div class='small-6 cell medium-cell-block-y <?=$align?> <?=$bold?>' 
		style='background-color: <?=$colorCode?>; border-<?=$border?>: 2px solid;
		text-shadow: 0px 0px 1vh #FFF;'>
		<i>
			<?php if(isTeamLogic($matchInfo['tournamentID']) == false): ?>
				<span style='font-size:65px;'> <?=$schoolName?></span>
			<?php else: ?>
				<span style='font-size:65px;'>(<?=$teamName?>)</span>
			<?php endif ?>
		</i>
	</div>
<?php
}

/******************************************************************************/

function fighterScoreDisplay($matchInfo, $num){

	$bold = '';
	if($num == 1){
		$colorCode = COLOR_CODE_1;
		$colorName = COLOR_NAME_1;
		$score = $matchInfo['fighter1score'];

		if($matchInfo['winnerID'] == $matchInfo['fighter1ID']){
			$bold = 'bold';
		}
	} else {
		$colorCode = COLOR_CODE_2;
		$colorName = COLOR_NAME_2;
		$score = $matchInfo['fighter2score'];

		if($matchInfo['winnerID'] == $matchInfo['fighter2ID']){
			$bold = 'bold';
		}
	}

	if($score === null){
		$score = "/";
	}

?>
	<div class='small-4 cell medium-cell-block-y text-center <?=$bold?>' 
		style='background-color: <?=$colorCode?>; border-top: 4px solid;
		text-shadow: 0px 0px 2vh #FFF;'>
		
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

	$endColor = '';
	switch($endType){
		case 'winner':
			$endText1 = 'Winner';
			if($matchInfo['winnerID'] == $matchInfo['fighter1ID']){

				$endText2 = COLOR_NAME_1;
				$endColor = COLOR_CODE_1;

			} elseif($matchInfo['winnerID'] == $matchInfo['fighter2ID']){
				
				$endText2 = COLOR_NAME_2;
				$endColor = COLOR_CODE_2;
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
			<div class='match-winner-name black-text' style='background-color:<?=$endColor?>;'>
				<span style='font-size:5.5vw; color: black; text-shadow: 0px 0px 2vh #FFF;'><?=$endText2?></span>
			</div>

	<!-- Match is ignored -->
		<?php elseif($matchInfo['ignoreMatch']): ?>

			<h4>Match Incomplete</h4>

	<!-- Show timer -->
		<?php else: ?>

			<input type='hidden' class='matchTime' id='matchTime' 
				name='matchTime' value='<?=$matchInfo['matchTime']?>'>
			<input type='hidden' id='timeLimit' value='<?=$matchInfo['timeLimit']?>'>
			<input type='hidden' name='restartTimer' value='0' id='restartTimerInput'>
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

	if(isDoubleHits() == false){
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
	$string = "{$doubles} Double Hit".ifSet($doubles != 1, "s");

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

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
