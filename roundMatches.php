<?php
/*******************************************************************************
	Round Matchs

	Displays all the pieces in a round
	LOGIN: N/A

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Round Scores';
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];
if($_SESSION['eventID'] == null){
	pageError('event');
} elseif($tournamentID == null){
	pageError('tournament');
} elseif($_SESSION['formatID'] != FORMAT_SOLO){
	if($_SESSION['formatID'] == FORMAT_MATCH && ALLOW['VIEW_SETTINGS'] == false){
		redirect('poolMatches.php');
	}
	displayAlert('This is not a scored event<BR>Please navigate to a pool or bracket');
} elseif (ALLOW['VIEW_MATCHES'] == false){
	displayAlert("Event is still upcoming<BR>Rounds not yet released");
} else {

	$numGroupSets = getNumGroupSets($tournamentID);

	// Omits the accordion menu if there is only one round per set
	$showMultiple = isCumulativeRounds($tournamentID);
	$hide = getItemsHiddenByFilters($tournamentID, $_SESSION['filters'],'roster');

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?=activeFilterWarning()?>

	<form method='POST'>

	<!--Accordion start -->
	<?php if($showMultiple): ?>
		<ul class='accordion' data-accordion  data-allow-all-closed='true'>
	<?php else: ?>
		<div class='grid-x grid-padding-x grid-margin-x' >
	<?php endif ?>


<!-- Display set -->
	<?php for($groupSet = 1; $groupSet <= $numGroupSets; $groupSet++):
		if($_SESSION['groupSet'] == $groupSet){
			$active = 'is-active';
		} else {
			$active = '';
		}

		$rounds = getRounds($tournamentID, $groupSet); ?>

		<!--Accordion item start-->
		<?php if($showMultiple):
			$setName = getSetName($groupSet, $tournamentID); ?>
			<li class='accordion-item <?=$active?>' data-accordion-item>
			<a class='accordion-title'>
				<h4><?=$setName?></h4>
			</a>
			<div class='accordion-content' data-tab-content>
		<?php endif ?>


	<!-- Display the pieces -->
		<?php if($rounds == null):
			displayAlert('No Rounds Created');
		else:
			if($showMultiple): ?>
				<div class='large-12 cell'>
				<div class='grid-x grid-padding-x grid-margin-x cell'>
			<?php endif ?>

			<?php foreach($rounds as $round):
				displayRound($round, $hide);
			endforeach ?>

		<?php endif ?>

		<!--Accordion item end-->
		<?php if($showMultiple): ?>
			</div>
			</li>
		<?php endif ?>
	<?php endfor ?>

	<!--Accordion end -->
	<?php if($showMultiple): ?>
		</ul>
	<?php else: ?>
		</div>
	<?php endif ?>

	</form>

	<?=changeParticipantFilterForm($_SESSION['eventID'])?>

	<!-- Auto refresh -->
	<?php $time = autoRefreshTime(isInProgress($tournamentID, 'round')); ?>
	<script>window.onload = function(){autoRefresh(<?=$time?>);}</script>


<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayRound($roundInfo, $hide){

	$groupID = $roundInfo['groupID'];
	$groupSet = $roundInfo['groupSet'];
	$roundName = $roundInfo['groupName'];
	$matches= getRoundMatches($groupID);
	$schoolIDs = getTournamentFighterSchoolIDs($_SESSION['tournamentID']);
	?>

	<fieldset class='fieldset large-4 medium-6 small-12 cell'>
		<legend><h3><?=$roundName?></h3></legend>
		<form method='POST'>
		<input type='hidden' name='formName' value='goToPiece'>

		<?php foreach($matches as $match):
			$matchID = $match['matchID'];
			$rosterID = $match['fighter1ID'];

			if(isset($hide['roster'][$match['fighter1ID']]) == true){
				continue;
			}

			$name = getEntryName($rosterID);
			$score = max([$match['fighter1Score'],$match['fighter2Score']]);

			 ?>

			<div class='large-4 cell'>
			<a name='match<?=$matchID?>'></a>
			<button class='button tiny hollow' name='matchID' value=<?=$matchID?>>
				Go
			</button>
			<?=$name?> [<strong><?=$score?></strong>]
			</div>
		<?php endforeach ?>

		</form>
	</fieldset>

<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
