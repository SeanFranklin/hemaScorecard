<?php
/*******************************************************************************
	Round Roster
	
	Roster information for the tournament, including set and round management
	and adding/removing fighters from the rounds.
	LOGIN:
		- ADMIN and above can create/delete/rename stages
		- ADMIN and above can create/delete/rename rounds
		- STAFF and above can add/remove fighters to rounds
		
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Round Rosters';
$includeTournamentName = true;
$lockedTournamentWarning = true;
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
	
	<form method='POST' id='roundRosterForm'>
	<fieldset <?=LOCK_TOURNAMENT?>>
	<!-- Accordion start -->
	<?php if($showMultiple): ?>
		<ul class='accordion' data-accordion data-allow-all-closed='true'>
	<?php else: ?>
		<div class='grid-x grid-padding-x grid-margin-x' >
	<?php endif ?>

	<?php for($groupSet = 1; $groupSet <= $numGroupSets; $groupSet++):
		
		$rounds = getRounds($tournamentID, $groupSet);
		if($_SESSION['groupSet'] == $groupSet){
			$active = 'is-active';
		} else {
			unset($active);
		}	
		?>

		<!-- Accordion item start -->
		<?php if($showMultiple):
			$setName = getSetName($groupSet, $tournamentID);
			?>
		
			<li class='accordion-item <?=$active?>' data-accordion-item>
			<a class='accordion-title'>
				<h4><?=$setName?></h4>
			</a>
			<div class='accordion-content' data-tab-content>
		<?php endif ?>
		
		<?php if($rounds == null):
			displayAnyErrors("Stage {$groupSet}<BR>No Rounds Created");
		else:
			displayRounds($rounds, $showMultiple);	
		endif ?>
		
		<!-- Accordion item end -->
		<?php if($showMultiple): ?>
			</div>
			</li>
		<?php  endif ?>
		
	<?php endfor ?>
	
	<!-- Accordion end -->
	<?php if($showMultiple): ?> 
		</ul> 
	<?php else: ?>
		</div>
	<?php endif ?>
	
	<?php if(USER_TYPE >= USER_ADMIN): ?>
		<BR>
		
		<?php confirmDeleteReveal('roundRosterForm', 'deleteFromRounds'); ?>
		<button class='button success' name='formName' value='addFightersToRound'
			<?=LOCK_TOURNAMENT?>>
			Add Fighters
		</button> 
		<span id='deleteButtonContainer'>
			<button class='button alert hollow' name='formName' value='deleteFromPools' 
				id='deleteButton' <?=LOCK_TOURNAMENT?>>
				Delete Selected
			</button>
			
		</span>
	<?php endif ?>
	
	</fieldset>
	</form>
	
<!-- Round management -->
	<?php roundManagement($numGroupSets, $showMultiple); ?>
	
<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayRounds($rounds, $ownDiv = true){

	$roster = getTournamentRoster();
	?>
	
	<!-- If the round should span the entire screen -->
	<?php if($ownDiv): ?>
		<div class='grid-x grid-padding-x grid-margin-x' >
	<?php endif ?>
	
<!-- Step through rounds in set -->
	<?php foreach($rounds as $num => $round):
		$name = $round['groupName'];
		
		$groupID = $round['groupID'];
		$groupSet = $round['groupSet'];
		$groupNumber = $round['groupNumber'];

		$roundRoster = getPoolRosters($_SESSION['tournamentID'], $groupSet);
		$roundRoster = $roundRoster[$groupID];

		unset($fightersInRound);
		foreach((array)$roundRoster as  $entry){
			// Makes a list of the fighters already in the round
			// used to know who is eligible to be entered
			$rosterID = $entry['rosterID'];
			$fightersInRound[$rosterID] = true;
		} 
		
		$numInRound = count($roundRoster);
		$sortedRoster = getListForNextRound($tournamentID, $groupSet, $groupNumber);
		$numInEvent = count($sortedRoster); 

		// If a fighter has been removed from advancing but they are already in the
		// round they will be taking up a spot in the round despite not adding
		// to the number of fighters in the round. This corrects the number discrepency.
		$numInEvent += getNumStopsInGroup($groupID);
		?>
		
	<!-- Display round -->
		<fieldset class='fieldset large-4 medium-6 small-12 cell' id='divFor<?=$groupID?>'>
		<legend>
			<h3>
				<?php if(USER_TYPE >= USER_ADMIN): ?>
					<input type='checkbox' name='deleteGroup[<?=$groupID?>]' 
						id=<?=$groupID?> onchange="checkIfFought(this)">
				<?php endif ?>
				<?=$name?>
			</h3>
		</legend>
		
		<div class='grid-x grid-padding-x grid-margin-x'>
			
		
	<!-- Option to add multiple at a time ----------------------------------------------------------------->
		<?php if(USER_TYPE >= USER_STAFF && $numInRound < 1 &&
			((count($sortedRoster) > 0) ||
			($groupNumber > 1 && count($oldRoster) > 1 ))
			&& !defined('LOCK_TOURNAMENT') ): 
			?>	

			<div class='input-group cell'>
				<a class='input-group-button button align-middle' 
					onclick="submitAddMultipleToRound(<?=$groupID?>)"> 
					Add
				</a>
				<select class='input-group-field' name='numToAdd[<?=$groupID?>]'>
					<option value='0'>All</option>
					<?php for($i=$numInRound+1;$i<=$numInEvent;$i++): ?>
						<option value='<?=$i?>'>Top <?=$i?></option>
					<?php endfor ?>
				</select>
				<span class='input-group-label'>to round</span>
				
			</div>
		<?php endif ?>
		
		<!------------------------------------------------------------------------------------>
		
	<!-- Fighters in the round -->
		<?php foreach((array)$roundRoster as $fighter):
			$rosterID = $fighter['rosterID'];
			$name = getFighterName($rosterID);
			?>
		
			<div class='large-12 cell' id='divFor<?=$groupID?>-<?=$rosterID?>'>
			<div class='grid-x grid-padding-x'>
			
			<?php if(USER_TYPE >= USER_STAFF): ?>
				<div class='small-1 cell' >
				<input type='checkbox' 
					name='deleteFromGroup[<?=$groupID?>][<?=$rosterID?>]' 
					id=<?=$groupID?>-<?=$rosterID?> onchange="checkIfFought(this)">
				</div>				
			<?php endif ?>
			<div class='small-10 cell'><?=$name?></div>
			</div></div>
		<?php endforeach ?>
		
	<!-- Add new fighters to the round -->
		<?php if(USER_TYPE >= USER_STAFF): ?>
			<?php for($i=$numInRound+1;$i<=$numInEvent;$i++): ?>
				<div class='large-12 cell'>
					<div class='grid-x grid-padding-x'>
						<div class='small-1 cell'>
							<?=$i?>
						</div>
						<div class='small-10 cell'>
							
						<select name='groupAdditions[<?=$groupID?>][<?=$i?>]'>
							<option></option>
				
							<?php foreach($sortedRoster as $fighter): 
								$rosterID = $fighter['rosterID'];
								$name = getFighterName($rosterID);
								if($fightersInRound[$rosterID] == true){continue;} 

								if($fighter['place'] !== null){
									$place = $fighter['place']+1;
								} else {
									$place == null;
								} ?>
								
								<option value='<?=$rosterID?>'>
									<?=$place?> <?=$name?>
								</option>
							<?php endforeach ?>
						</select>
						</div>
					</div>
				</div>
			<?php endfor ?>
		<?php endif ?>
		
		</div>
		</fieldset>
		
		<?php $oldGroupID = $groupID; ?>
		<?php $oldRoster = $roundRoster; ?>
	<?php endforeach ?>
	
	<?php if($ownDiv): ?>
		</div>
	<?php endif ?>
	
<?php }

/******************************************************************************/

function roundManagement($numGroupSets, $multiRoundDisplay){
//	Change the number of stages/sets & rename stages
//	Add/remove/rename rounds in a set
	
	if(USER_TYPE < USER_ADMIN){return;}

	
	$maxGroupSets = 5;
	?>
	
	<fieldset class='fieldset'>
	<legend><h4>Manage Rounds</h4></legend>
	
	<div class='grid-x grid-margin-x'>
	
	<!-- Number of stages -->
		<div class='large-3 medium-4 text-center cell'>
			<span class='button expanded' data-open='createStages' <?=LOCK_TOURNAMENT?>>
				Add/Remove Stages
			</span>
		</div>
		<?php createStagesBox($numGroupSets); ?>

	<!-- Add rounds -->
		<div class='large-3 medium-4 text-center cell'>
			<span class='button expanded' data-open='createRounds' <?=LOCK_TOURNAMENT?>>
				Add New Rounds
			</span>
		</div>
		<?php createRoundsBox($numGroupSets); ?>	
			
			
	<!-- Rename rounds -->
		<div class='large-3 medium-4 text-center cell'>
			<span class='button expanded' data-open='renameRounds'>
				Rename Rounds
			</span>
		</div>
		<?php changeRoundNamesBox($multiRoundDisplay); ?>
		
	<!-- Rename rounds -->
		<div class='large-3 medium-4 text-center cell'>
			<span class='button expanded' data-open='stageOptions' <?=LOCK_TOURNAMENT?>>
				Stage Options
			</span>
		</div>
		<?php stageOptionsBox($numGroupSets); ?>
	
	
	</div>
	</fieldset>
	
	
<?php }

/******************************************************************************/

function stageOptionsBox($numGroupSets){
	$tournamentBase = getBasePointValue(null, null);

	?>
	
	<div class='reveal tiny' id='stageOptions' data-reveal>
	<form method='POST'>
	<fieldset <?=LOCK_TOURNAMENT?>>
		<h5>Stage Options</h5>
		
		Base score for:
		<?php for($i=1;$i<=$numGroupSets;$i++): ?>
			<?php $stageBase = getBasePointValue(null, $i, true);
			
			 ?>
			<div class='input-group grid-x'>
				
				<span class='input-group-label small-8 medium-12 large-8'>
					<?=getSetName($i); ?>
				</span>
				<input type='number' class='input-group-field no-bottom' 
					name='baseScore[<?=$i?>]' value='<?=$stageBase?>' placeholder='<?=$tournamentBase?>'>
				
			</div>
		<?php endfor ?>
		<em>The base score will be used in accordance with the tournament format.<BR>
		<u>Examples:</u> If it is a cutting tournament where each cut is assigned a 
		score with a deduction, it is the point value of a perfect cut.<BR>
		If it is an event where every competitor has a perfect score
		and is assesed deductions, it is the value of the perfect score.</em>
		
		<!-- Submit buttons -->
		<div class='grid-x grid-margin-x'>
			<button class='button success small-6 cell' name='formName' 
				value='stageOptions' <?=LOCK_TOURNAMENT?>>
				Update
			</button>
			<a class='button secondary small-6 cell' data-close aria-label='Close modal' type='button'>
				Cancel
			</a>
		</div>
	</fieldset>
	</form>
		
		<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>
	
<?php }

/******************************************************************************/

function createStagesBox($numGroupSets){
	$maxGroupSets = 5;	// Arbitrary
	?>
	
	<div class='reveal tiny' id='createStages' data-reveal>
	<form method='POST'>
	<fieldset <?=LOCK_TOURNAMENT?>>
		<h5>Number of Tournament Stages</h5>
		<BR>
		<div class='input-group grid-x no-bottom'>
			
			<span class='input-group-label'>
				Total Number of Stages:
			</span>
			<select class='input-group-field no-bottom' name='numGroupSets'>
				<?php for($i=1;$i<=$maxGroupSets;$i++):
					$s = isSelected($i == $numGroupSets);
					?>
					
					<option value='<?=$i?>' <?=$s?>><?=$i?></option>
				<?php endfor ?>
			</select>
			
		</div>
		<em>All rounds are cumulative within a stage</em>
		
	
		<BR><BR>
		<!-- Submit buttons -->
		<div class='grid-x grid-margin-x'>
			<button class='button success small-6 cell' name='formName' 
				value='numberOfGroupSets' <?=LOCK_TOURNAMENT?>>
				Update
			</button>
			<a class='button secondary small-6 cell' data-close aria-label='Close modal' 
				type='button'>
				Cancel
			</a>
		</div>
	</fieldset>
	</form>
		
		<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>
	
	
<?php }

/******************************************************************************/

function createRoundsBox($numGroupSets){
	$maxRoundsToAdd = 5;	// Arbitrary
	?>
	<div class='reveal tiny' id='createRounds' data-reveal>
		<form method='POST'>
		<fieldset <?=LOCK_TOURNAMENT?>>
			
		<h5>Create new rounds</h5>
		<div class='input-group grid-x'>

			<span class='input-group-label small-8 medium-12 large-8'>
				# Rounds to Add:
			</span>
			<select class='input-group-field small-4 medium-12 large-4' name='numRoundsToAdd'>
				<?php for($i=1;$i<=$maxRoundsToAdd;$i++): ?>
					<option value='<?=$i?>'><?=$i?></option>
				<?php endfor ?>
			</select>

		</div>
		Create rounds in:
		<div class='input-group grid-x'>
			<span class='input-group-label small-8 medium-12 large-8'>
				Stage:
			</span>
			<select class='input-group-field small-4 medium-12 large-4' name='setToAddRounds'>
				<?php for($i=1;$i<=$numGroupSets;$i++): ?>
					<option value='<?=$i?>'><?=$i?></option>
				<?php endfor ?>
			</select>
		</div>
		<div class='grid-x grid-margin-x'>
		
			<button class='button success small-6 cell' 
				name='formName' value='createNewRounds' <?=LOCK_TOURNAMENT?>>
				Add
			</button>
			<a class='button secondary small-6 cell' data-close aria-label='Close modal' type='button'>
				Cancel
			</a>
		
		</div>
		</fieldset>
		</form>
		
		
		<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>
	
	
<?php }

/******************************************************************************/

function changeRoundNamesBox($multiRoundDisplay = true){
	
	$rounds = getRounds();
	?>
	
	<div class='reveal tiny' id='renameRounds' data-reveal>
	<form method='POST'>
	<h5>Rename Rounds:</h5>
	
	<?php foreach($rounds as $round): 
		$set = $round['groupSet'];
		$setName = getSetName($set);
		if($setName == "Stage {$set}"){ 
			$setName = null;
		}
		$roundNum++;
		?>
		
		<!-- Stage name  -->
		<?php if($set != $oldSet && $multiRoundDisplay): ?>
			<?php if($set > 1){ echo "<BR>"; } ?>
			<div class='input-group grid-x'>
			<span class='input-group-label small-3 medium-5 large-3'>Stage <?=$set?>:</span>
			<input class='input-group-field large-7' type='text' name='renameSet[<?=$set?>]' 
				value='<?=$setName?>' placeholder='Stage <?=$set?>'>
			<div class='small-2 hide-for-medium-only'></div>
			</div>
			<?php 
				$oldSet = $set;
				$roundNum = 1;
			?>
		<?php endif ?>
		
			<div class='input-group grid-x'>
			<div class='small-1'></div>
			<span class='input-group-label small-2'><?=$roundNum?>:</span>
			<input class='input-group-field small-8' type='text' name='renameGroup[<?=$round['groupID']?>]' 
				value='<?=$round['groupName']?>' placeholder='Round <?=$round['groupNumber']?>'>
			</div>
	<?php endforeach ?>
	
	
	<!-- Sumbit/Cancel buttons -->
	<div class='grid-x grid-margin-x'>
		<button class='success button small-6 cell' name='formName' value='renameGroups'>
			Update
		</button>
		<button class='secondary button small-6 cell' data-close aria-label='Close modal' type='button'>
			Cancel
		</button>

	</div>
	
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
