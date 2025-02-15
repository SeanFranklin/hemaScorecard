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

	<script>
		// Auto-refresh
		window.onload = function(){
			refreshOnNewExchange(<?=$matchID?>, <?=$matchInfo['lastExchange']?>);
		}
	</script>

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

</head>






<?php

	$fighterName = getCombatantName($matchInfo['fighter1ID']);
	$school = $matchInfo['fighter1School'];

 	$matchID = (int)$matchInfo['matchID'];



// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<body>





	<div class='large-12 cell'>
	<div class='cell shrink text-center align-middle align-top'style='background:black; vertical-align: top;'>


		<img src="includes/images/favicon2.png" style='height:7vh; vertical-align: top;'>


		<span class='white-text' style='font-size:5vh'>HEMA Scorecard</span>

		<img src="includes/images/favicon2.png" style='height:7vh; vertical-align: top;'>
	</div>
	</div>

	<div class='grid-container full' style='padding:10px'>
	<div class='grid-x grid-margin-x'>

		<div class='large-4 cell'>
			<div class='grid-x grid-margin-x'>
				<div style='background-image: linear-gradient(to bottom right, <?=COLOR_CODE_2?>, <?=COLOR_CODE_1?>);
					 padding-left:1em' class='cell large-12'>
					<span style='font-size: 5em;'>
						<?=$fighterName?>

					</span>
					<BR>
					<span style='font-size: 3.5em;'>
						<i><?=$school?></i>
					</span>
				</div>

				<div style='font-size: 30vh;' class='text-center cell large-12'>
					<?=$matchInfo['fighter1score']?>
				</div>


			</div>
		</div>

		<div class='large-8 cell'>
			<?=sideBar($matchID)?>
		</div>


	</div>


</div>


	</body>




<?php }

include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function sideBar($matchID){

	$exchanges = getMatchExchanges($matchID);


	foreach($exchanges as $i => $e){

		if($e['exchangeType'] == 'pending'){
			// Exchange has been added, but not the score.
			unset($exchanges[$i]);

			continue;

		}

		$exchanges[$i]['scoreFinal'] = $e['scoreValue'] - $e['scoreDeduction'];
		$exchanges[$i]['placeholder'] = "No Change";

		if($exchanges[$i]['scoreDeduction'] != 0){
			$exchanges[$i]['scoreDeduction'] = -$e['scoreDeduction'];
			$deductionTextSmall = "{$exchanges[$i]['scoreDeduction']}";
		} else {
			$deductionTextSmall = "0";
		}

		$deductionToDisplay = [];

	// Deduction 1
		$name = getAttackName($e['refPrefix']);
		if($name != ""){
			$deductionToDisplay[] = $name;
		}

	// Deduction 2
		$name = getAttackName($e['refTarget']);
		if($name != ""){
			$deductionToDisplay[] = $name;
		}

	// Deduction 3
		$name = getAttackName($e['refType']);
		if($name != ""){
			$deductionToDisplay[] = $name;
		}

		$exchanges[$i]['deductionToDisplay'] = $deductionToDisplay;

	}

	$lastExchange = count($exchanges)-1;


?>

	<div class='grid-x grid-margin-x'>

		<div class='cell large-12  cut-list-heading'>
			Last Cut
		</div>

		<?showExchange(@$exchanges[$lastExchange], true)?>


		<div class='cell large-12 cut-list-heading'>
			Full List (oldest → newest)
		</div>


		<?php for($i = $lastExchange; $i >= 0; $i--): ?>
			<?showExchange($exchanges[$i])?>
		<?php endfor ?>

	</div>

<?php
}

/******************************************************************************/

function showExchange($e, $full = false){

	if($e == null){
		$e['scoreFinal'] = "&nbsp";
		$e['deductionToDisplay'] = [];
	}

	if($full == true){
		$divSize = 12;
		$bigFont = "6em";
		$smallFont = "3em";
	} else {
		$divSize = 4;
		$bigFont = "3em";
		$smallFont = "1.5em";
	}

?>

	<div  class='cell medium-<?=$divSize?>' >
		<table>
			<tr>
				<td style='background-color:darkblue; color: white; font-size:<?=$bigFont?>; width:1.8em;' class='text-center'>
					<?=$e['scoreFinal']?>
				</td>
				<td style='font-size:<?=$smallFont?>; background-color:#EEE'>
					<?php foreach($e['deductionToDisplay'] as $d):?>
						<?=$d?><BR>
					<?php endforeach ?>
				</td>
			</tr>

		</table>

	</div>


<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
