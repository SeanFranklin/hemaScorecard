<?php
/*******************************************************************************
	Piece Scoring

	Scoring for solo events, such as cutting
	LOGIN:
		- STAFF and higher can add exchanges
		- STAFF and higher can add deductions

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Round Scoring';
$hideEventNav = true;
$hidePageTitle = true;
$lockedTournamentWarning = true;
$jsIncludes[] = 'score_scripts.js';
include('includes/header.php');

$matchID = $_SESSION['matchID'];
$tournamentID = $_SESSION['tournamentID'];
$eventID = $_SESSION['eventID'];

if($matchID == null || $tournamentID == null || $eventID == null){
	if(ALLOW['VIEW_SETTINGS']){
		displayAlert("No Piece Selected<BR><a href='roundMatches.php'>Piece List</a>");
	} elseif($eventID == null){
		redirect('infoSelect.php');
	} elseif($tournamentID == null){
		redirect('infoSummary.php');
	} elseif($matchID == null){
		redirect('participantsEvent.php');
	} else {
		displayAlert("No Piece Selected<BR><a href='roundMatches.php'>Piece List</a>");
	}
} elseif(ALLOW['VIEW_MATCHES'] == false) {
	displayAlert("Event is still upcoming<BR>And you clearly got on this page by unorthodox means");
} elseif($_SESSION['formatID'] != FORMAT_SOLO){
	displayAlert("So close and yet so far. <BR>Perhaps <a href='scoreMatch.php'>this</a> is what you're looking for? :)");
} else {

	define("EXCHANGES", 'Cut');
	$matchInfo = getMatchInfo($matchID, $tournamentID);

	$_SESSION['groupSet'] = getGroupSetOfMatch($matchID);
	// Updates the group set so that when they navigate back the set this is a part
	// of is expanded.

	$name = getEntryName($matchInfo['fighter1ID']);
	$school = $matchInfo['fighter1School'];
	$score = max([$matchInfo['fighter1score'],$matchInfo['fighter2score']]);
	$matchID = $matchInfo['matchID'];

	$exchanges = getMatchExchanges($matchID);
	$deductionList = getTournamentDeductions( $matchInfo['tournamentID']);

	if($score == null){
		$exchName = EXCHANGES;
		$scoreText = "- No {$exchName}s -";
	} else {
		$scoreText = "Score: <b>{$score}</b>";
	}


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?php askForFinalization($tournamentID); ?>

<!-- Warning if match is ignored -->
	<?php if($matchInfo['ignoreMatch'] == 1): ?>
		<div class='callout secondary text-center'>
			<span class='red-text'>This match has been excluded from scoring calcultions</span>
			<BR>Possible reasons include injury or disqualification from the tournament
		</div>

	<?php endif ?>

<!-- Fighter info -->

	<div style='background-image: linear-gradient(to bottom right, <?=COLOR_CODE_2?>, <?=COLOR_CODE_1?>);border:1px solid black; margin-bottom:10px' class='grid-x align-bottom'>
		<div class='large-6 small-12' style='padding-left:10px;'><span style='font-size: 2em;'><?=$name?></span></div>
		<div class='large-6 small-12 text-right'style='padding-right:10px'><span style='font-size: 1.5em;'><?=$school?></span></div>
	</div>

	<div class='grid-x grid-margin-x'>

		<div class='cell large-3 medium-4 small-6 success' style='font-size:2em; border'>
			&nbsp;<span id='match-score'><?=$scoreText?></span>
		</div>

		<div class='cell small-2 show-for-small-only'>&nbsp;</div>

		<a class='button cell large-2 medium-3 small-4 hollow' href='roundMatches.php#match<?=$matchID?>'>
			Back To List
		</a>


		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
			<a class='cell button  hollow hide-for-small-only large-2 medium-3 '
				onclick="window.open('scorePieceDisplay.php','scoreDisplayWindow','toolbar=0,location=0,menubar=0')">
				Display Window
				<?=tooltip("Opens a display window to put onto a projector. This window will <u>not</u> update itself when you switch to a different match. You will need to click the <strong>Display Window</strong> button to have the display window update to your currently active match.")?>
			</a>
		<?php endif ?>


	</div>

	<div class='grid-x grid-padding-x grid-margin-x'>

<!-- Display exchanges -->
	<?php if($exchanges == []): ?>
		<!-- No exchanges, don't display anything -->
	<?php elseif($deductionList != []): ?>
		<?=existingExchangesFormWithDeductions($matchInfo, $deductionList)?>
	<?php else: ?>
		<?=existingExchangesForm($matchInfo)?>
	<?php endif ?>

<!-- Add New Exchanges -->
	<?=addNewExchangesBox($matchInfo)?>

	</div>

<!-- Video Link -->
	<?php addVideoLink($matchID); // display_functions.php ?>

<?php }

include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function existingExchangesFormWithDeductions($matchInfo, $deductionList){


	$numDeductions = sizeof($deductionList);

	$matchID = (int)$matchInfo['matchID'];
	$exchanges = getMatchExchanges($matchID);



	$num = 0;
	foreach($exchanges as $i => $e){

		$num++;
		$exchanges[$i]['numToDisplay'] = "#".$num;


		if($e['exchangeType'] == 'pending'){
			// Exchange has been added, but not the score.

			if(ALLOW['EVENT_SCOREKEEP'] == false){

				// If they aren't staff they shouldn't see un-scored exchanges.
				unset($exchanges[$i]);
				$num--;

			} else {

				$exchanges[$i]['scoreDeduction'] = '?';
				$exchanges[$i]['scoreFinal'] = "??";
				$exchanges[$i]['placeholder'] = "0";

			}

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

		$deductionSmall = "";

	// Deduction 1
		$name = getAttackName($e['refPrefix']);
		if($name != ""){
			$exchanges[$i]['deductionName'][1] = $name;
			$exchanges[$i]['deductionID'][1]   = $e['refPrefix'];
			$deductionTextSmall .= "<i>, {$name}</i>";
		} else {
			$exchanges[$i]['deductionName'][1] = "-";
			$exchanges[$i]['deductionID'][1]   = 0;
		}

	// Deduction 2
		$name = getAttackName($e['refTarget']);
		if($name != ""){
			$exchanges[$i]['deductionName'][2] = $name;
			$exchanges[$i]['deductionID'][2]   = $e['refTarget'];
			$deductionTextSmall .= "<i>, {$name}</i>";
		} else {
			$exchanges[$i]['deductionName'][2] = "-";
			$exchanges[$i]['deductionID'][2]   = 0;
		}

	// Deduction 3
		$name = getAttackName($e['refType']);
		if($name != ""){
			$exchanges[$i]['deductionName'][3] = $name;
			$exchanges[$i]['deductionID'][3]   = $e['refType'];
			$deductionTextSmall .= "<i>, {$name}</i>";
		} else {
			$exchanges[$i]['deductionName'][3] = "-";
			$exchanges[$i]['deductionID'][3]   = 0;
		}

		$exchanges[$i]['deductionTextSmall'] = $deductionTextSmall;

	}


// Calculate Page Size
	$sizeLarge = 5;
	$sizeLarge += floor(1.5 * $numDeductions);


	$sizeMedium = 7;
	$sizeMedium += floor(1.5 * $numDeductions);
	if($sizeMedium > 10){
		$sizeMedium = 12;
	}

	$tableClass = "";
	if(ALLOW['EVENT_SCOREKEEP'] == true){
		$tableClass .= " stack";
	}


?>

	<fieldset class='fieldset large-<?=$sizeLarge?> medium-<?=$sizeMedium?> cell' <?=LOCK_TOURNAMENT?>>

		<legend>
			<h5 class='no-bottom'>
				<?=EXCHANGES?> Scores
				<a class='button tiny no-bottom hollow' href='scorePiece.php' id='reload-page-button'>
					Reload Page
				</a>
			</h5>
		</legend>

		<form method="POST">

			<input type='hidden' name='matchID' value=<?=$matchID?>>


			<table class='<?=$tableClass?>'>
				<tr>
					<th><?=EXCHANGES?></th>
					<th>Score</th>
					<?php for($i = 1; $i <= $numDeductions; $i++): ?>
						<th  class='hide-for-small-only'>Deduction <?=$i?></th>
					<?php endfor ?>
					<th>Deduction</th>
					<th>Points</th>
				</tr>

				<?php foreach($exchanges as $i => $exchange): ?>
					<?=displayPieceExchangeRowForm($exchange, $deductionList)?>
				<?php endforeach ?>

			</table>



			<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
				<div class='grid-x grid-padding-x '>
					<div class='large-6 cell' id='warning-message-div'>
					</div>
					<div class='large-6 cell'>
						<button class='button large alert hollow expanded'
							name='formName' value='deleteExchanges' <?=LOCK_TOURNAMENT?>>
							Delete Selected
						</button>
					</div>

				</div>
			<?php endif ?>

		</form>
	</fieldset>

<?php
}

/******************************************************************************/

function displayPieceExchangeRow($exchange, $numDeductions){
?>
	<tr class='text-center'>

		<td><?=$exchange['numToDisplay']?></td>

		<td><?=$exchange['scoreValue']?></td>

		<?php for($i = 1; $i <= $numDeductions; $i++): ?>
			<td class='hide-for-small-only'><?=$exchange['deductionName'][$i]?></td>
		<?php endfor ?>

		<td class='hide-for-small-only'>
			<?=$exchange['scoreDeduction']?>
		</td>

		<td class='show-for-small-only'>
			<?=$exchange['deductionTextSmall']?>

		</td>

		<td class='bold'>
			<?=$exchange['scoreFinal']?>
		</td>

	</tr>
<?
}

/******************************************************************************/

function displayPieceExchangeRowForm($exchange, $deductionList){

	$numDeductions = sizeof($deductionList);

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		displayPieceExchangeRow($exchange, $numDeductions);
		return;
	}


	$trClass = "";
	$isPending = false;
	if($exchange['exchangeType'] == 'pending'){
		$isPending = true;
		$trClass .= " pending-exchange italic";
	}

	$eID = (int)@$exchange['exchangeID'];

?>
	<tr class='text-center <?=$trClass?>' id='tr-exchange-<?=$eID?>'>

		<td class='text-center'>
			<?=$exchange['numToDisplay']?>
			<input type='checkbox' class='no-bottom' name='exchangesToDelete[<?=$eID?>]'>

		</td>

		<td>

			<input type='number' step='0.1' class='no-bottom'
				style='width:5em'
				id='scoreValue-<?=$eID?>'
				data-deductiontype='scoreValue'
				onchange="updatePieceExchange(this, <?=$eID?>)"
				value='<?=$exchange['scoreValue']?>'
				placeholder='0'>

		</td>

		<?php for($i = 1; $i <= $numDeductions; $i++): ?>

			<td>
				<select id='<?=NUM_2_ATK[$i]?>-<?=$eID?>'
					data-deductiontype='<?=NUM_2_ATK[$i]?>'
					onchange="updatePieceExchange(this, <?=$eID?>)"  >

					<?php if($isPending == true): ?>
						<option disabled selected>-- unset --</option>
					<?php endif ?>

					<option <?=optionValue(0, @$exchange['deductionID'][$i])?>>* clean *</option>

					<?php foreach($deductionList[$i] as $s): ?>
						<option <?=optionValue($s['attackID'], @$exchange['deductionID'][$i])?>>
							<?=$s['name']?>
						</option>
					<?php endforeach ?>
				</select>
			</td>


		<?php endfor ?>

		<td id='scoreDeduction-<?=$eID?>'><?=$exchange['scoreDeduction']?></td>

		<td id='scoreFinal-<?=$eID?>' class='bold'>
			<?=$exchange['scoreFinal']?>
		</td>

	</tr>
<?
}


/******************************************************************************/

function existingExchangesForm($matchInfo){

?>

	<fieldset class='fieldset large-6 cell' <?=LOCK_TOURNAMENT?>>
		<legend>
			<h5 class='no-bottom'>
				<?=EXCHANGES?> Scores
			</h5></legend>
		<form method='POST'>
		<input type='hidden' name='matchID' value=<?=$matchInfo['matchID']?>>

		<?php scored_DisplyExchanges($matchInfo['matchID']); ?>

		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
			<div class='grid-x grid-padding-x '>
				<div class='large-6 cell'>
					<button class='button large success expanded'
						name='formName' value='updateScores' <?=LOCK_TOURNAMENT?>>
						Update
					</button>
				</div>
				<div class='large-6 cell'>
					<button class='button large alert hollow expanded'
						name='formName' value='deleteExchanges' <?=LOCK_TOURNAMENT?>>
						Delete Selected
					</button>
				</div>
			</div>
		<?php endif ?>
		</form>
	</fieldset>

<?php
}

/******************************************************************************/

function askForFinalization($tournamentID){
/*	After the final match of a tournament has concluded this will prompt the
	scorekeeper to finalize the tournament results */

	if(ALLOW['EVENT_SCOREKEEP'] == false){			return; }
	if(!isset($_SESSION['askForFinalization'])){	return; }

	unset($_SESSION['manualTournamentPlacing']);
	unset($_SESSION['askForFinalization']);
	?>

	<div class='callout alert text-center'>
	<form method='POST'>
	<input type='hidden' name='tournamentID' value='<?=$tournamentID?>'>

		This appears to be the last match of the tournament.
		Would you like to finalize the results?
		<BR><BR>
		<button class='button no-bottom' name='formName' value='autoFinalizeTournament'>
			Finalize Tournament
		</button>
		<button class='button secondary no-bottom'>
			Do It Later
		</button>
	</form>
	</div>

<?php }

/******************************************************************************/

function addNewExchangesBox($matchInfo){

	if(ALLOW['EVENT_SCOREKEEP'] == false){ return; }
	$nextMatchInfo = getNextPoolMatch($matchInfo);
?>

<!-- Add exchanges -->
	<fieldset class='fieldset large-2 cell' <?=LOCK_TOURNAMENT?>>


	<legend><h5 class='no-bottom'>Add <?=EXCHANGES?>s</h5></legend>
	<form method='POST'>
	<div class='grid-x grid-margin-x'>
		<input class='cell' type='number' name='exchangesToAdd'
			placeholder="# <?=EXCHANGES?>s to Add" value=1>
		<button class='cell button success' name='formName'
			value='addExchanges' <?=LOCK_TOURNAMENT?>>
			Add
		</button>
	</div>
	</form>

	<HR><BR>

<!-- Go to next match button -->
	<?php if(isset($nextMatchInfo)): ?>
		<form method='POST'>
		<input type='hidden' name='formName' value='goToPiece'>
		<button class='button hollow expanded' value='<?=$nextMatchInfo['matchID']?>' name='matchID'>
			Next in List: <?=getEntryName($nextMatchInfo['fighter1ID'])?>
		</button>

		</form>
	<?php else: ?>
		<a class='button warning expanded' href='roundMatches.php'>End of List</a>
	<?php endif ?>

	</fieldset>


<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
