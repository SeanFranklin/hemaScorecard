<?php
/*******************************************************************************
	Fighter Management

	Withdraw fighters if they are injured and can no longer compete
	LOGIN:
		- ADMIN or higher required to access

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Manage Fighters';
$lockedTournamentWarning = true;
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if(ALLOW['EVENT_SCOREKEEP'] == false && ALLOW['VIEW_SETTINGS'] == false){
	pageError('user');
} else if($tournamentID == null){
	pageError('tournament');
} else {

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		$formLock = 'disabled';
	} else {
		$formLock = '';
	}

	$isTeamLogic = isTeamLogic($tournamentID);
	$GLOBALS['ignores'] = getIgnores($tournamentID);

	if(isEntriesByTeam($tournamentID) == true){
		$showTeams = true;
		$teamRoster = getTournamentTeams($tournamentID);

	} else {
		$showTeams = false;
	}

	if(isMatchesByTeam($tournamentID) == true){
		$showFighters = false;
	} else {
		$showFighters = true;
		$fighterRoster = getTournamentFighters($tournamentID,'rosterID','full');
	}


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Navigate pool sets -->

	<div class='grid-x grid-padding-x'>
	<div class='large-7 medium-8 small-12'>

	<form method='POST'>
	<fieldset <?=LOCK_TOURNAMENT?> <?=$formLock?>>
	<input type='hidden' name='formName' value='ignoreFightersInTournament'>
	<input type='hidden' name='manageFighters[tournamentID]' value='<?=$tournamentID?>'>

	<?php if($showFighters & $showTeams): ?>
		<ul class="tabs" data-tabs id="example-tabs">
			<li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Fighters</a></li>
			<li class="tabs-title"><a data-tabs-target="panel2" href="#panel2">Teams</a></li>
		</ul>


		<div class="tabs-content" data-tabs-content="example-tabs">
			<div class="tabs-panel is-active" id="panel1">
				<?php removeRosterTable($fighterRoster, $ignores, 'fighter');?>
			</div>
			<div class="tabs-panel" id="panel2">
				<?php removeRosterTable($teamRoster, $ignores, 'team')?>
			</div>
		</div>
	<?php elseif($showTeams): ?>
		<?php removeRosterTable($teamRoster, $ignores, 'team')?>
	<?php else: ?>
		<?php removeRosterTable($fighterRoster, $ignores, 'fighter')?>
	<?php endif ?>

	<BR>
	<button class='button large success' name='updateTournament'
		value='<?=$tournamentID?>' <?=LOCK_TOURNAMENT?>>
		Update List
	</button>
	</fieldset>
	</form>

	</div>
	</div>


<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function removeRosterTable($roster, $ignores, $entryType = 'fighter'){

	$numGroupSets = getNumGroupSets($_SESSION['tournamentID']);

	// Text for tooltips
	$stopTitle = "Can not advance";
	$soloTitle = "Remove Individual";
	if($numGroupSets == 1){
		$ignoreTitle = "Remove From Scoring";
		$ignoreClause = '';
		$numSetsForStops = 1;
	} else {
		$ignoreTitle = "Remove from set number";
		$ignoreClause = " starting in this pool set, leaving older sets unaffected";
		if($entryType == 'fighter'){
			$numSetsForStops = 1;
		} else {
			$stopTitle = "Last set fought in";
			$numSetsForStops = $numGroupSets;
		}
	}
	$ignoreText = "Removes all matches involving the fighter from scoring calculations{$ignoreClause}.<BR><BR>
		Use if the fighter has been injured or disqualified before completing their pool.";

	$stopText = "Fighters have all matches and scores remain unchanged but will not
		advance. <BR><BR> Use if a fighter has completed their pool
		without incident but must withdraw from the tournament.";

	$soloText = "Have all the fighters matches and exchanges count towards scoring, but do not display
				this fighter's results. ";

	?>

	<table>
		<tr>
			<th>
				Name
			</th>
			<th>
				<?= $ignoreTitle ?>
				<?= tooltip($ignoreText); ?>
			</th>
			<th>
				<?= $stopTitle ?>
				<?php tooltip($stopText); ?>
			</th>
			<th>
				<?= $soloTitle ?>
				<?php tooltip($soloText); ?>
			</th>
		</tr>

	<?php foreach($roster as $entry):
		$rosterID = $entry['rosterID'];?>
		<tr>
			<td>
				<?
					if($entryType == 'team'){

						$name = getTeamName($rosterID);
					} else {
						$name = getFighterName($rosterID);
					}
				?>
				<?=$name?>
			</td>

		<!-- Remove from scoring in a set -->
			<td>
				<?=ignoreAtInput($rosterID, $numGroupSets, (int)@$ignores[$rosterID]['ignoreAtSet'])?>
			</td>

		<!-- Keep fighter pool results but don't advance them to the bracket -->
			<td>
				<?=stopAtInput($rosterID, $numSetsForStops, (int)@$ignores[$rosterID]['stopAtSet'])?>
			</td>

		<!-- Keep fighter pool match data, but don't display their results -->
			<td>
				<?=soloAtInput($rosterID, $numGroupSets, (int)@$ignores[$rosterID]['soloAtSet'])?>
			</td>
		</tr>
	<?php endforeach ?>

	</table>

<?php }

/******************************************************************************/

function stopAtInput($rosterID, $numGroupSets, $selectValue){

	if($selectValue != 0){
		$stopCheck = "checked";
	} else {
		$stopCheck = '';
	}

	?>
	<?php if($numGroupSets > 1): ?>
		<select name = 'manageFighters[rosterList][<?=$rosterID?>][stopAtSet]'>
			<option value=0></option>
			<?php for($i=$numGroupSets;$i>0; $i--): ?>
				<option <?=optionValue($i,$selectValue)?> >
					<?=getSetName($i, $_SESSION['tournamentID'])?>
				</option>
			<?php endfor ?>
		</select>
	<?php else: ?>
		<div class='switch text-center no-bottom'>
			<input type='hidden' name='manageFighters[rosterList][<?=$rosterID?>][stopAtSet]' value='0'>
			<input class='switch-input' type='checkbox'
				id='manageFighters[rosterList][<?=$rosterID?>][stopAtSet]'
				name='manageFighters[rosterList][<?=$rosterID?>][stopAtSet]' value='1' <?=$stopCheck?>>
			<label class='switch-paddle' for='manageFighters[rosterList][<?=$rosterID?>][stopAtSet]'>
			</label>
		</div>
	<?php endif ?>

<?php

}

/******************************************************************************/

function ignoreAtInput($rosterID, $numGroupSets, $selectValue){

	if($selectValue != 0){
		$stopCheck = "checked";
	} else {
		$stopCheck = '';
	}

	?>
	<?php if($numGroupSets > 1): ?>
		<select name = 'manageFighters[rosterList][<?=$rosterID?>][ignoreAtSet]'>
			<option value=0></option>
			<?php for($i=$numGroupSets;$i>0; $i--): ?>
				<option <?=optionValue($i,$selectValue)?> >
					<?=getSetName($i, $_SESSION['tournamentID'])?>
				</option>
			<?php endfor ?>
		</select>
	<?php else: ?>
		<div class='switch text-center no-bottom'>
			<input type='hidden' name='manageFighters[rosterList][<?=$rosterID?>][ignoreAtSet]' value='0'>
			<input class='switch-input' type='checkbox'
				id='manageFighters[rosterList][<?=$rosterID?>][ignoreAtSet]'
				name='manageFighters[rosterList][<?=$rosterID?>][ignoreAtSet]' value='1' <?=$stopCheck?>>
			<label class='switch-paddle' for='manageFighters[rosterList][<?=$rosterID?>][ignoreAtSet]'>
			</label>
		</div>
	<?php endif ?>

<?php

}

/******************************************************************************/

function soloAtInput($rosterID, $numGroupSets, $selectValue){

	if($selectValue != 0){
		$stopCheck = "checked";
	} else {
		$stopCheck = '';
	}

	?>
	<?php if($numGroupSets > 1): ?>
		<select name = 'manageFighters[rosterList][<?=$rosterID?>][soloAtSet]'>
			<option value=0></option>
			<?php for($i=$numGroupSets;$i>0; $i--): ?>
				<option <?=optionValue($i,$selectValue)?> >
					<?=getSetName($i, $_SESSION['tournamentID'])?>
				</option>
			<?php endfor ?>
		</select>
	<?php else: ?>
		<div class='switch text-center no-bottom'>
			<input type='hidden' name='manageFighters[rosterList][<?=$rosterID?>][soloAtSet]' value='0'>
			<input class='switch-input' type='checkbox'
				id='manageFighters[rosterList][<?=$rosterID?>][soloAtSet]'
				name='manageFighters[rosterList][<?=$rosterID?>][soloAtSet]' value='1' <?=$stopCheck?>>
			<label class='switch-paddle' for='manageFighters[rosterList][<?=$rosterID?>][soloAtSet]'>
			</label>
		</div>
	<?php endif ?>

<?php

}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
