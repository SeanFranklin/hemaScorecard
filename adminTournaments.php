<?php
/*******************************************************************************
	Manage Tournaments

	View and change settings of tournaments. Delete existing tournaments.
	LOGIN
		- ADMIN or higher required to view

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Manage Tournaments';
$jsIncludes[] = 'tournament_management_scripts.js';
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif($_SESSION['tournamentID'] == null){
	pageError('tournament');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false) {
	pageError('user');
} else {

	$tournamentID = $_SESSION['tournamentID'];
	$tournamentName = getTournamentName($tournamentID);
	$formLock = '';
	$isLocked = '';

// Disable form elements if the settings should not be changed.
	if(ALLOW['EVENT_MANAGEMENT'] == false){
		$formLock = 'disabled';
	}

	if(isFinalized($tournamentID)){
		$isLocked = 'disabled';
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>


	<?php if($isLocked != null): ?>
		<div class='callout alert text-center' data-closeable>
			Results for this tournament have been finalized, most changes have been disabled.
			<a href='infoSummary.php'>Remove final results</a> to edit.
		</div>
	<?php endif ?>

	<input type='hidden' id='doesBracketExist<?=$tournamentID?>' value=<?=isBrackets($tournamentID)?>>

	<fieldset <?=$isLocked?> <?=$formLock?> >
	<form method='POST'>

	<input type='hidden' name='formName' value='updateTournamentInfo'>
	<input type='hidden' name='modifyTournamentID' value='<?=$tournamentID?>'>

	<h3>Tournament Settings for: <u><?=$tournamentName?></u></h3>

	<div class='grid-x grid-margin-x grid-padding-x'>
	<div class='large-7 cell'>

	<table class='options-table stack'>

		<!-- Tournament Info --------------------------------------------->
			<?php

			// Tournament Info --------------------------
				edit_tournamentOptionsRow("General Configuration");
				edit_tournamentName($tournamentID);
				edit_tournamentFormatType($tournamentID);
				edit_tournamentRankingType($tournamentID);
				edit_tournamentBasePoints($tournamentID);

			// Sparring Tournaments Info --------------------------
				edit_tournamentOptionsRow("Sparring Info","option-sparring");
				edit_tournamentDoubleType($tournamentID);
				edit_tournamentNetScore($tournamentID);
				edit_tournamentOverrideDoubles($tournamentID);
				edit_tournamentTies($tournamentID);
				edit_tournamentReverseScore($tournamentID);

			// Match Display --------------------------
				edit_tournamentOptionsRow("Match Display","option-match-display");
				edit_tournamentColors($tournamentID, 1);
				edit_tournamentColors($tournamentID, 2);
				edit_tournamentTimerCountdown($tournamentID);

			// Pools & Standings --------------------------
				edit_tournamentOptionsRow("Pool Configuration","option-pools");
				edit_tournamentMaxPoolSize($tournamentID);
				edit_tournamentPoolWinners($tournamentID);

			// Match Conclusion --------------------------
				edit_tournamentOptionsRow("Match Auto-Conclude","option-auto-conclude",
					"Optional settings for the software to automatically end a match when these conditions are met.
					Scorekeepers can always conclude (or re-open) matches regardless of what is set here.");
				edit_tournamentMaxDoubles($tournamentID);
				edit_tournamentTimeLimit($tournamentID);
				edit_tournamentMaxPoints($tournamentID);
				edit_tournamentMaxPointSpread($tournamentID);
				edit_tournamentMaxExchanges($tournamentID);

			// Sub Matches --------------------------
				edit_tournamentOptionsRow("Sub-Match Info","option-sub-match",
					"Sub-matches will create multiple 'sub-matches' for each match.
							<BR><u>Example</u>: A multi-weapon tournament where competitors
							face off with each weapon set one after another.");
				edit_tournamentSubMatches($tournamentID);

			// Teams --------------------------
				edit_tournamentOptionsRow("Team Tournaments","option-teams");
				edit_tournamentTeams($tournamentID);

			// Logistics
				edit_tournamentOptionsRow("Other Miscelanious Options","option-misc");
				edit_tournamentCuttingQual($tournamentID);
				edit_tournamentDoublesCarryForward($tournamentID);
				edit_tournamentHideFinalResults($tournamentID);
				edit_tournamentKeepPrivate($tournamentID);
				edit_tournamentRequireSignOff($tournamentID);
				edit_tournamentStaffCheckin($tournamentID);
				edit_tournamentNormalization($tournamentID);
				edit_tournamentLimitPoolMatches($tournamentID);
				edit_tournamentPriorityNotice($tournamentID);
				edit_tournamentDenoteOtherCheck($tournamentID);

			?>
	</table>
	</div>
	</div>

	<? if(isFinalsSubMatches($tournamentID) && getNumSubMatches($tournamentID) == 0): ?>
		<div class='callout warning'>
			<h4 class='red-text'>WARNING</h4>
			You have a finals match with sub matches created in it. If you update these settings you will
			<u>overwrite</u> the settings for your finals matches to zero sub matces.
			<BR>
			<strong>THIS WILL PERMINATLY ERASE MATCHES</strong>
			<BR>
			<em><u>Example</u>: If you have 3 sub-matches in the gold medal round, and you update this form
			with 'Use Sub Matches' set to 'No', it will delete all the sub matches and you would
			lose your results for the finals.</em>
			<BR>
			<strong class='red-text'>Do not click 'Update' unless you are <u>very</u> sure you know what you are doing.</strong>
		</div>
	<?php endif ?>

	<div id='tournamentWarnings_<?=$tournamentID?>'>
		<BR>
	</div>

<!-- Submit Form Options -------------------------------------------------->
	<div>

		<button class='button success' name='updateType' value='update'
			id='editTournamentButton<?=$tournamentID?>' <?=$isLocked?>  <?=$formLock?>>
			Update <?=$tournamentName?>
		</button>
		<button class='button secondary' name='formName' value='' <?=$isLocked?>  <?=$formLock?>>
			Cancel
		</button>
		<button class='button' href='adminExchangeTypes.php' name='formName' value='goToPointsPage'
			style='float:middle' <?=$isLocked?>  <?=$formLock?>>
			Change Point Values
		</button>
		<a class='button warning' onclick="$('#import-for-<?=$tournamentID?>').toggle()"
			<?=$isLocked?>  <?=$formLock?>>
			Import/Copy
		</a>
		<a class='button alert' data-open='boxFor-<?=$tournamentID?>'
			style='float:right' <?=$isLocked?>  <?=$formLock?>>
			Delete Tournament
		</a>
	</div>
	</form>
	<?=importSettingsForm($tournamentID)?>

	</fieldset>



	<?php
	if($isLocked == null &&  $formLock == null){
		confirmTournamentDeletionBox($tournamentID);
	}
	?>

	<i>Use tournaments selection in upper left to change tournament.</i>


<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function importSettingsForm($tournamentID){
	$thisTournaments = getEventTournaments($_SESSION['eventID']);
	$allTournaments = getSystemTournaments();
?>
	<div id='import-for-<?=$tournamentID?>' class='hidden warning callout cell'>
	<form method='POST'>
		<input type='hidden' name='importTournamentSettings[targetID]' value='<?=$tournamentID?>'>

		<h4>Import Tournament Settings</h4>
		<p>
		This will import <strong>ALL</strong> settings from the selected tournament except:<BR>
		- Names<BR>
		- Group Set information<BR>
		- Pre-defined tournament attacks
		</p>

		<div class='callout alert'>
			<strong>IMPORTANT!!</strong><BR>
			This is meant as a feature to save time, but <u>does not</u> absolve you of needing to make sure
			that your tournaments are set up correctly. <BR>
			Options may have changed since a past event was run,
			or the options might not work the same way.<BR>
			<span class='red-text'>CHECK THAT THE IMPORTED SETTINGS WORK!</span>
		</div>

		<p>From this event:
		<select name='importTournamentSettings[sourceID1]'>
			<option></option>
			<?php foreach($thisTournaments as $tournamentID):?>
				<option <?=optionValue($tournamentID, null)?> >
					<?=getTournamentName($tournamentID)?>
				</option>
			<?php endforeach ?>
		</select>
		</p>

		<p>
		From other events:<BR>
		<select name='importTournamentSettings[sourceID2]'>
			<option></option>
			<?php foreach($allTournaments as $tournamentID => $tournament):?>
				<option <?=optionValue($tournamentID, null)?> >
					<?=$tournament['eventName']?> [<?=$tournament['tournamentName']?>]
				</option>
			<?php endforeach ?>
		</select>
		</p>

		<button class='button success' name='formName' value='importTournamentSettings'>
			Import
		</button>

	</form>
	</div>

<?php
}

/******************************************************************************/

function confirmTournamentDeletionBox($tournamentID){
	$name = getTournamentName($tournamentID);

	?>

	<div class='reveal text-center' id='boxFor-<?=$tournamentID?>' data-reveal>

	<form method='POST'>
		<input type='hidden' name='formName' value='deleteTournament'>
		<input type='hidden' name='deleteTournamentID' value='<?=$tournamentID?>'>

		<p>You are about to delete the following tournament:</p>
		<h1><?=$name?></h1><BR>

		<p><span style='color:red'>Warning: </span>
		Deleting this tournament will <u>permanently</u>
		erase any data associated with it.</p>

		<button class='button alert large text-center'>
			Delete Tournament
		</button>

		<span class='button large secondary' data-close aria-label='Close modal' type='button'>
			Cancel
		</span>

	</form>

	<!-- Close button -->
	<button class='close-button' data-close aria-label='Close modal' type='button'>
		<span aria-hidden='true'>&times;</span>
	</button>

	</div>


<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
