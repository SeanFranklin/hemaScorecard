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
		redirect('participantsRoster.php');
	} else {
		displayAlert("No Piece Selected<BR><a href='roundMatches.php'>Piece List</a>");
	}
} elseif($_SESSION['formatID'] != FORMAT_SOLO){
	displayAlert("So close and yet so far. <BR>Perhaps <a href='scoreMatch.php'>this</a> is what you're looking for? :)");
} else {

	define("EXCHANGES", 'Cuts');
	$matchInfo = getMatchInfo($matchID, $tournamentID);
	
	$_SESSION['groupSet'] = getGroupSetOfMatch($matchID);
	// Updates the group set so that when they navigate back the set this is a part
	// of is expanded.
	
	$name = getEntryName($matchInfo['fighter1ID']);
	$school = $matchInfo['fighter1School'];
	$score = $matchInfo['fighter1score'];
	$matchID = $matchInfo['matchID'];
	
	$exchanges = getMatchExchanges($matchID);
	
	if($score == null){
		$exchName = EXCHANGES;
		$legend = "- No {$exchName} -";
	} else {
		$legend = "Score: {$score}";
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
	<h3><?=$name?></h3>
	<?=$school?>
	<BR>
	<a class='button hollow' href='roundMatches.php#match<?=$matchID?>'>Back To List</a>
	<BR>
	
	<div class='grid-x grid-padding-x grid-margin-x'>
		
<!-- Display exchanges -->
	<fieldset class='fieldset large-6 cell' <?=LOCK_TOURNAMENT?>>
		<legend><h3><?=$legend?></h3></legend>
		<form method='POST'>
		<input type='hidden' name='matchID' value=<?=$matchID?>>
	
		<?php scored_DisplyExchanges(null, $score); ?>
	
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

<!-- Add New Exchanges -->
	<?php addNewExchangesBox($matchInfo); ?>
	
	</div>
	
<!-- Youtube -->
	<?php addYoutube($matchID); // display_functions.php ?>
	
<?php }
	
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

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
		This appears to be the last match of the tournament. 
		Would you like to finalize the results?
		<input type='hidden' name='formName' value='finalizeTournament'><BR><BR>
		<button class='button no-bottom' name='tournamentID' value='<?=$tournamentID?>'>
			Finalize Tournament
		</button>
		<button class='button secondary no-bottom' name='tournamentID' value='cancel'>
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
	<fieldset class='fieldset large-6 cell' <?=LOCK_TOURNAMENT?>>
	<legend><h3>Add More <?=EXCHANGES?></h3></legend>
	<form method='POST'>
	<div class='input-group grid-x'>
		<span class='input-group-label large-4 medium-3 small-12'># <?=EXCHANGES?> to Add:</span>
		<input class='input-group-field' type='number' name='exchangesToAdd'>
		<button class='button success input-group-button' name='formName' 
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
			Next in List - <?=getEntryName($nextMatchInfo['fighter1ID'])?>
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
