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

if(!isRounds($tournamentID)){
	if(isPools($tournamentID) && USER_TYPE < USER_SUPER_ADMIN){
		redirect('poolMatches.php');
	}
	displayAnyErrors('This is not a scored event<BR>Please navigate to a pool or bracket');
} else {
	
	$numGroupSets = getNumGroupSets($tournamentID);
	
	// Omits the accordion menu if there is only one round per set
	$showMultiple = isCumulativeRounds($tournamentID); 

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>		
	
	<form method='POST'>
	
	<!--Accordion start -->
	<?php if($showMultiple): ?>
		<ul class='accordion' data-accordion  data-allow-all-closed='true'>
	<? else: ?>
		<div class='grid-x grid-padding-x grid-margin-x' >
	<?php endif ?>


<!-- Display set -->
	<?php for($groupSet = 1; $groupSet <= $numGroupSets; $groupSet++): 
		if($_SESSION['groupSet'] == $groupSet){
			$active = 'is-active';
		} else {
			unset($active);
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
			displayAnyErrors('No Rounds Created');
		else:
			if($showMultiple): ?>
				<div class='large-12 cell'>
				<div class='grid-x grid-padding-x grid-margin-x cell'>
			<?php endif ?>
			
			<?php foreach($rounds as $round):
				displayRound($round);
			endforeach ?>
			
			<?php if($isMultiple): ?>
				</div>
				</div>
			<?php endif ?>
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
	
	<!-- Auto refresh -->
	<?php $time = autoRefreshTime(isInProgress($tournamentID, 'round')); ?>
	<script>window.onload = function(){autoRefresh(<?=$time?>);}</script>
	
	
<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayRound($roundInfo){
	
	$groupID = $roundInfo['groupID'];
	$groupSet = $roundInfo['groupSet'];
	$roundName = $roundInfo['groupName'];
	$matches= getRoundMatches($groupID);
	?>
	
	<fieldset class='fieldset large-4 medium-6 small-12 cell'>
		<legend><h3><?=$roundName?></h3></legend>
		<form method='POST'>
		<input type='hidden' name='formName' value='goToPiece'>
	
		<?php foreach($matches as $match):
			$matchID = $match['matchID'];
			$rosterID = $match['fighter1ID'];
			$name = getFighterName($rosterID);
			$score = $match['fighter1Score']; ?>
			
			<div class='large-4 cell'>
			<a name='match<?=$matchID?>'></a>
			<button class='button tiny hollow' name='matchID' value=<?=$matchID?>>
				Go
			</button>
			<?=$name?> <strong><?=$score?></strong>
			</div>
		<?php endforeach ?>
	
	
		</form>
	</fieldset>
		
<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
