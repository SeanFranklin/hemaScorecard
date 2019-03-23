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
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false) {
	pageError('user');
} else {

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		$formLock = 'disabled';
	} else {
		$formLock = '';
	}

	$tournamentList = getTournamentsFull();
	if(count($tournamentList) == 1){
		$isActiveItem = 'is-active';
	} else {
		$isActiveItem = '';
	}
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<ul class='accordion' data-accordion data-allow-all-closed='true'>

	<?php foreach((array)$tournamentList as $tournamentID => $tournament):

		$numParticipants = $tournament['numParticipants'];
		$divName = "tournament".$tournamentID;
		$name = getTournamentName($tournamentID);

		// LOCK_TOURNAMENT can't be used because it only applies to the current tournament
		if(isFinalized($tournamentID)){
			$isLocked = 'disabled';
		} else {
			$isLocked = '';
		}
		?>
		
		
		<li class='accordion-item <?=$isActiveItem?>' data-accordion-item>
		<a class='accordion-title'>
			<div class='grid-x'>
				<div class='medium-10 small-12'>
					<h4><?=$name?></h4>
				</div>
				<div class='medium-2'>
					<?=$numParticipants?> Participants
				</div>
			</div>
			
			
		</a>
		<div class='accordion-content' data-tab-content>
			
			<?php if($isLocked != null): ?>
				<div class='callout alert text-center' data-closeable>
					Results for this tournament have been finalized, most changes have been disabled.
					<a href='infoSummary.php'>Remove final results</a> to edit.
				</div>
			<?php endif ?>

			<form method='POST'>
			<fieldset <?=$isLocked?> <?=$formLock?> >
				
			<input type='hidden' name='formName' value='updateTournamentInfo'>
			<input type='hidden' name='modifyTournamentID' value='<?=$tournamentID?>'>
			
			<?php edit_tournamentName($tournamentID); ?>


			<div id='requiredFields_<?=$tournamentID?>' class='grid-x grid-padding-x text-center'>
				<?php
				edit_tournamentFormatType($tournamentID);
				edit_tournamentDoubleType($tournamentID);
				edit_tournamentRankingType($tournamentID);
				edit_tournamentNetScore($tournamentID);
				edit_tournamentBasePoints($tournamentID);
				?>
			</div>
			<div class='grid-x grid-padding-x text-center'>
				<BR>Optional Fields:
			</div>
				
			<div id='optionalFields_<?=$tournamentID?>' class='grid-x grid-padding-x text-center'>
				<?php
				edit_tournamentTimer($tournamentID);
				edit_tournamentTies($tournamentID);
				edit_tournamentColors($tournamentID, 1);
				edit_tournamentColors($tournamentID, 2);
				edit_tournamentMaxDoubles($tournamentID);
				edit_tournamentMaxPoolSize($tournamentID);
				edit_tournamentNormalization($tournamentID);
				edit_tournamentPoolWinners($tournamentID);

				edit_tournamentTimeLimit($tournamentID);
				edit_tournamentMaxExchanges($tournamentID);
				edit_tournamentMaxPoints($tournamentID);
				edit_tournamentReverseScore($tournamentID);
				edit_tournamentLimitPoolMatches($tournamentID);
				edit_tournamentControlPoints($tournamentID);
				edit_tournamentOverrideDoubles($tournamentID);
				edit_tournamentCuttingQual($tournamentID);
				edit_tournamentNumSubMatches($tournamentID);
				edit_tournamentSubMatchMode($tournamentID);
				edit_tournamentTeams($tournamentID);
				edit_tournamentKeepPrivate($tournamentID);
				edit_tournamentHideFinalResults($tournamentID);
				edit_tournamentStaffCheckin($tournamentID);
				?>
			</div>
			<BR>
			<div id='tournamentWarnings_<?=$tournamentID?>'>
				<BR>
			</div>
			<div>
				<button class='button success' name='updateType' value='update' 
					id='editTournamentButton<?=$tournamentID?>' <?=$isLocked?>  <?=$formLock?>>
					Update <?=$name?>
				</button>
				<button class='button secondary' name='formName' value='' <?=$isLocked?>  <?=$formLock?>>
					Cancel
				</button>
				<button class='button' href='adminPoints.php' name='formName' value='goToPointsPage'
					style='float:middle' <?=$isLocked?>  <?=$formLock?>>
					Change Point Values
				</button>
				<a class='button alert' data-open='boxFor-<?=$tournamentID?>' 
					style='float:right' <?=$isLocked?>  <?=$formLock?>>
					Delete Tournament
				</a>
			</div>
			</fieldset>
			</form>

			
			<?php 
			if($isLocked == null &&  $formLock == null){
				confirmTournamentDeletionBox($tournamentID); 
			}	
			?>

		</div>
		</li>
		
	<?php endforeach ?>

	</ul>


<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

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
