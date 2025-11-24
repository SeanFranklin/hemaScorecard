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
$createSortableDataTable[] = ['rankingAlgoList',100];
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
	$formatID = getTournamentFormat($tournamentID);

	$formLock = '';
	$isLocked = '';

// Disable form elements if the settings should not be changed.
	if(ALLOW['EVENT_MANAGEMENT'] == false || isFinalized($tournamentID)){
		$formLock = 'disabled';
	}

	rankingTypeDescriptions();
	importAttacksForm($_SESSION['tournamentID'], $formLock);


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<div class='callout success text-center' style='padding: 0px;'>
	<span style='font-size: 1.5em; m'>Tournament Settings for: <u><?=$tournamentName?></u></span>
	</div>


	<?php if($formLock != null): ?>
		<div class='callout alert text-center' data-closeable>
			Results for this tournament have been finalized, most changes have been disabled.
			<a href='infoSummary.php'>Remove final results</a> to edit.
		</div>
	<?php endif ?>


	<ul class="tabs" data-tabs id="exchange-type-tabs">

		<li class="tabs-title is-active">
			<a data-tabs-target="panel-settings" >Tournament Settings</a>
		</li>
		<li class="tabs-title">

			<a data-tabs-target="panel-attack">
				<?php if($formatID != FORMAT_SOLO): ?>
					Attack Definitions
				<?php else: ?>
					Deductions
				<?php endif ?>
			</a>
		</li>

		<li class="tabs-title">
			<a data-tabs-target="panel-misc">Misc Fancy Options</a>
		</li>
	</ul>



	<div class="tabs-content" data-tabs-content="exchange-type-tabs">

		<div class="tabs-panel is-active" id="panel-settings">
			<?=tournamentSettingsForm($tournamentID, $formLock, $tournamentName)?>
		</div>

		<div class="tabs-panel" id="panel-attack">
			<?php if($formatID != FORMAT_SOLO): ?>
				<?=exchangeTypeAttacks($formLock)?>
			<?php else: ?>
				<?=exchangeTypeDeductions($formLock)?>
			<?php endif ?>
		</div>

		<div class="tabs-panel" id="panel-misc">
			<?=exchangeTypeDataEntryMode($formLock)?>

			<?=exchangeTypeModifiers($formLock)?>

		</div>
	</div>




	<i>Use tournaments selection in upper left to change tournament.</i>


<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function tournamentSettingsForm($tournamentID, $formLock, $tournamentName){
?>
	<input type='hidden' id='doesBracketExist<?=$tournamentID?>' value=<?=isBrackets($tournamentID)?>>

	<fieldset <?=$formLock?> >
	<form method='POST'>

	<input type='hidden' name='formName' value='updateTournamentInfo'>
	<input type='hidden' name='modifyTournamentID' value='<?=$tournamentID?>'>



	<div class='grid-x grid-margin-x grid-padding-x'>


	<table class='options-table stack large-7 cell'>

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
				"Optional settings for the software to automatically end a match when these conditions are met.s
				Scorekeepers can always conclude (or re-open) matches regardless of what is set here.");
			echo "<tr class='option-auto-conclude'><td colspan=100%><b>Most Common</b><td></tr>";
			edit_tournamentTimeLimit($tournamentID);
			edit_tournamentMaxPoints($tournamentID);
			edit_tournamentBracketPointCap($tournamentID);
			edit_tournamentFinalsPointCap($tournamentID);
			echo "<tr class='option-auto-conclude'><td colspan=100%><b>Less Common</b><td></tr>";
			edit_tournamentMaxDoubles($tournamentID);
			edit_tournamentMaxExchanges($tournamentID);
			edit_tournamentMaxPointSpread($tournamentID);
			edit_tournamentPointSpreadStartVal($tournamentID);

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

			echo "<tr class='option-misc hidden'><td colspan=100%><b>Match Stuff</b><td></tr>";
			edit_tournamentSoftClock($tournamentID);
			edit_tournamentMinExchTime($tournamentID);
			edit_tournamentPenaltyEscalationMode($tournamentID);
			edit_tournamentPenaltiesAddPoints($tournamentID);
			edit_tournamentLimitScoreOvershoot($tournamentID);

			echo "<tr class='option-misc hidden'><td colspan=100%><b>Bookkeeping Stuff</b><td></tr>";
			edit_tournamentStaffCheckin($tournamentID);
			edit_tournamentCuttingQual($tournamentID);
			edit_tournamentRequireSignOff($tournamentID);
			edit_tournamentHideFinalResults($tournamentID);
			edit_tournamentKeepPrivate($tournamentID);

			echo "<tr class='option-misc hidden'><td colspan=100%><b>Really Esoteric Stuff</b><td></tr>";
			edit_tournamentPriorityNotice($tournamentID);
			edit_tournamentDenoteOtherCheck($tournamentID);
			edit_tournamentLimitPoolMatches($tournamentID);
			edit_tournamentDoublesCarryForward($tournamentID);
			edit_tournamentNormalization($tournamentID);
			edit_tournamentMatchOrderMode($tournamentID);
			edit_tournamentLimitShallow($tournamentID);
		?>
	</table>

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
			id='editTournamentButton<?=$tournamentID?>' <?=$formLock?>>
			Update <?=$tournamentName?>
		</button>
		<button class='button secondary' name='formName' value='' <?=$formLock?>>
			Cancel
		</button>
		<a class='button warning' onclick="$('#import-for-<?=$tournamentID?>').toggle()"
			<?=$formLock?>>
			Import/Copy
		</a>
		<a class='button alert' data-open='boxFor-<?=$tournamentID?>'
			style='float:right' <?=$formLock?>>
			Delete Tournament
		</a>
	</div>
	</form>
	<?=importSettingsForm($tournamentID)?>

	</fieldset>



	<?php
	if($formLock == null){
		confirmTournamentDeletionBox($tournamentID);
	}
	?>

<?php
}

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

function exchangeTypeDeductions($formLock){

	$deductionList = getDeductionList();
	$existingDeductions = getTournamentDeductions($_SESSION['tournamentID']);
	$comboMode = readOption('T',$_SESSION['tournamentID'],'DEDUCTION_ADDITION_MODE');

?>


	<fieldset <?=$formLock?>>

	<div class='grid-x grid-margin-x'>
	<div class='large-6 medium-8 cell'>
		<form method='POST'>

			<input type='hidden' name='deductionAdditionMode[tournamentID]' value=<?=$_SESSION['tournamentID']?>>
			<input type='hidden' name='formName' value='deductionAdditionMode'>

			<div class='input-group'>

				<span class='input-group-label'>
					Deduction Combination Mode&nbsp;
					<?=tooltip("<u>Add</u>: Add the deductions togather.<BR>
								<u>Max</u>: Take the highest deduction.<BR>
								<u>RMS</u>: v(d1<sup>2</sup> + d2<sup>2</sup> + d3<sup>2</sup>)")?>
				</span>

				<select class='input-group-field' name='deductionAdditionMode[mode]'>
					<option <?=optionValue(DEDUCTION_ADDITION_MODE_ADD, $comboMode)?>>Add</option>
					<option <?=optionValue(DEDUCTION_ADDITION_MODE_MAX, $comboMode)?>>Max</option>
					<option <?=optionValue(DEDUCTION_ADDITION_MODE_RMS, $comboMode)?>>RMS</option>
				</select>

				<input type='submit' class='input-group-button button success' value='Update Mode'>
			</div>
		</form>
	</div>
	</div>

	<HR>


	<form method='POST'>

		<input class='hidden' name='deductions[tournamentID]' value=<?=$_SESSION['tournamentID']?>>

		<table class='stack'>
			<tr>
				<th>Deduction</th>
				<th>Points 1<?php tooltip("Use positive values"); ?></th>
				<th>Points 2<?php tooltip("Use positive values"); ?></th>
				<th>Points 3<?php tooltip("Use positive values"); ?></th>
			</tr>

		<!-- Old Attacks -->
			<?php foreach($deductionList as $i => $deduction): ?>
				<?=deductionRow($deduction, $existingDeductions)?>
			<?php endforeach ?>

		</table>

		<button class='button success' name='formName' value='editTournamentDeductions' <?=$formLock?>>
			Update Deduction Values
		</button>
		<BR><i>If you would like another type of deduction please contact the HEMA Scorecard team.</i>

	</form>
	</fieldset>

<?
}

/******************************************************************************/

function deductionRow($deduction, $existing){

	$attackID = $deduction['attackID'];
	$prefix = "deductions[{$attackID}]";

	$points1 = "";
	$points2 = "";
	$points3 = "";

	if(isset($existing[1][$attackID ]) == true){
		$points1 = (int)$existing[1][$attackID ]['points'];
	}

	if(isset($existing[2][$attackID ]) == true){
		$points2 = (int)$existing[2][$attackID ]['points'];
	}

	if(isset($existing[3][$attackID ]) == true){
		$points3 = (int)$existing[3][$attackID ]['points'];
	}

?>

	<tr>

	<!-- Deduction -->
		<td>
			<?=$deduction['attackText']?>
		</td>


	<!-- Points 1 -->
		<td>
			<input type='number' name='deductions[groupPointValues][1][<?=$attackID?>]'
				value='<?=$points1?>' step=0.1 min=0 max=100
				class='no-bottom' placeholder='unused' >
		</td>

	<!-- Points 2 -->
		<td>
			<input type='number' name='deductions[groupPointValues][2][<?=$attackID?>]'
				value='<?=$points2?>' step=0.1 min=0 max=100
				class='no-bottom' placeholder='unused' >
		</td>

	<!-- Points 3 -->
		<td>
			<input type='number' name='deductions[groupPointValues][3][<?=$attackID?>]'
				value='<?=$points3?>' step=0.1 min=0 max=100
				class='no-bottom' placeholder='unused' >
		</td>

	</tr>

<?php
}

/******************************************************************************/

function exchangeTypeAttacks($formLock){
?>
	<fieldset <?=$formLock?> >
		<form method='POST'>
		<button class='button success' name='formName' value='addAttackTypes' <?=$formLock?>>
			Submit
		</button>
		<a class='button warning' onclick="$('#import-attacks').toggle()" <?=$formLock?> >
			Import/Copy
		</a>
		<a class='button hollow' onclick="$('#explain-attacks').toggle()" style='padding: 0.2em; font-size: 1.7em;' >
			&nbsp;?&nbsp;
		</a>
		<i>
			(Leave the points field blank to delete an entry)
		</i>

		<p class='hidden' id='explain-attacks'>
			This allows you to pre-define attacks available for the table to select.
			<u>It is completely optional.</u>
			If you do not pre-define attacks the scorekeeper will still be able to chose the point values to enter manually, however using pre-defined attacks will limit the scorekeeper to only being able to click from a list of attacks your have defined. In addition the attack type will be saved and anyone looking at the match can see exactly what the call was on any exchange.

		</p>

		<?php displayAttacksForTournament($formLock)?>

		<button class='button success' name='formName' value='addAttackTypes' <?=$formLock?>>
			Submit
		</button>

		</form>
	</fieldset>

<?
}

/******************************************************************************/

function exchangeTypeModifiers($formLock){

	$afterblowPointValue = getAfterblowPointValue($_SESSION['tournamentID']);
	$maxAfterblowValue = 9;
	$controlPointValue = getControlPointValue($_SESSION['tournamentID']);
	$maxControlValue = 9;
?>

	<div style='border-bottom: 1px solid black; margin-top: 3em; font-size: 1.5em;'>
		<b>Pre-Defined Modifiers</b>
		<a onclick="$('#explain-modifiers').toggle()">(?)</a>
	</div>

	<fieldset <?=$formLock?> >

	<form method='POST'>
		<BR>
		<div class='cell grid-x grid-margin-x no-bottom'>

			<div class='input-group medium-4 cell'>
				<?php if(isDeductiveAfterblow($_SESSION['tournamentID']) == true): ?>
					<span class='input-group-label no-bottom'>
						Afterblow
						 <?=tooltip('')?>

					</span>
					<select class='input-group-field no-bottom' name='tournamentAttackModifiers[afterblow]'>
						<option value=0>Not Used</option>
						<?php for($p = 1;$p<=$maxAfterblowValue;$p++):?>
							<option <?=optionValue($p, $afterblowPointValue)?>>-<?=$p?></option>
						<?php endfor?>
					</select>
				<?php else: ?>
					<span class='callout secondary no-bottom' style='padding: 0px 16px;'>
						<i>Pre-Specifying an afterblow value is only valid in deductive afterblow scoring.</i>
					</span>
				<?php endif ?>

			</div>


			<div class='input-group large-4 medium-5  cell'>
				<span class='input-group-label no-bottom'>Controlling Action</span>
				<select class='input-group-field no-bottom' name='tournamentAttackModifiers[control]'>
					<option value=0>Not Used</option>
					<?php for($p = 1;$p<=$maxControlValue;$p++):?>
						<option <?=optionValue($p, $controlPointValue)?>>+<?=$p?></option>
					<?php endfor?>
				</select>
			</div>

			<button class=' medium-3 large-2 cell button success' name='formName' value='tournamentAttackModifiers'>
				Update Attack Modifiers
			</button>


			<div class='cell hidden' id='explain-modifiers'>
				Pre-defining an afterblow or a control point will give the table a checkbox to assign these modifiers to an exchange, valued at whatever you set them at. Pre-defining an afterblow of -1 will, for example, allow them to check the toggle instead of selecting "-1" from the drop-down list.
        	</div>
		</div>

	</form>
	</fieldset>

<?
}

/******************************************************************************/

function exchangeTypeDataEntryMode($formLock){

	$attackDisplayMode = readOption('T',$_SESSION['tournamentID'],'ATTACK_DISPLAY_MODE');

	$gridValid = '';
	$checkValid = '';
	$gridText = '';
	$checkText = '';

	if(isDeductiveAfterblow($_SESSION['tournamentID']) == true){
		$txt = "Deductive";
		$gridValid = '';
		$checkValid = 'disabled';
	} else if(isFullAfterblow($_SESSION['tournamentID']) == true) {
		$txt = "Full";
		$gridValid = 'disabled';
		$checkValid = '';
	} else {
		$txt = "No";
		$gridValid = 'disabled';
		$checkValid = 'disabled';
	}

	if($gridValid != ''){
		$gridText = '<BR> - Grid mode is not (yet) supported.';
	}

	if($checkValid != ''){
		$checkText = '<BR> - Check-Box mode is not (yet) supported.';
	}

	$warnMsg = "<div class='callout warning'>Your tournament is currently configured as <b>{$txt} Afterblow </b> mode.";
	$warnMsg .= $gridText;
	$warnMsg .= $checkText;
	$warnMsg .= "</div>";

?>

	<div style='border-bottom: 1px solid black; margin-top: 1em; font-size: 1.5em;'>
		<b>Input Mode</b>
		<a onclick="$('#explain-input-mode').toggle()">(?)</a>
	</div>

	<fieldset <?=$formLock?>>
		<legend></legend>
		<form method='POST'>
			<div class='cell grid-x grid-margin-x'>

				<div class='cell'>
					<?=$warnMsg?>
				</div>

			<div class='cell input-group large-4 medium-5'>

				<span class='input-group-label'>
					Input Mode
				</span>

				<select class='input-group-field' name='attackDefinitionMode' >
					<option <?=optionValue(ATTACK_DISPLAY_MODE_NORMAL, $attackDisplayMode)?> >Normal</option>
					<option <?=optionValue(ATTACK_DISPLAY_MODE_GRID, $attackDisplayMode)?> <?=$gridValid?>>Grid</option>
					<option <?=optionValue(ATTACK_DISPLAY_MODE_CHECK, $attackDisplayMode)?> <?=$checkValid?>>Check-Box</option>
				</select>

			</div>


			<button class='button success cell medium-4' name='formName' value='switchAttackDefinitionMode'>
				Update Input Mode
			</button>


			<div class='cell hidden' id='explain-input-mode'>
				<li><b>Normal</b>: Drop down menus for point and afterblow value.</li>
	            <li><b>Grid</b>: Organizer pre-specifies the point values for all actions/targets and the table clicks the targets, and the software assigns points.</li>
	            <li><b>Check-Box</b>: Organizer pre-specifies the point values for all actions/targets and these appear as checkboxes for the table.</li>
        	</div>

			</div>
		</from>
	</fieldset>



<?
}

/******************************************************************************/

function displayAttacksForTournament($formLock){

	$targets = getAllAttackTargets();
	$types = getAllAttackTypes();
	$prefixes = getAllAttackPrefixes();
	$existingAttacks = getTournamentAttacks($_SESSION['tournamentID']);
	$i = 0;

?>

	<table class='stack'>
		<tr>
			<th>
				Option #
				<?php tooltip("What order they will appear in the list."); ?>
			</th>
			<th>Attack Prefix</th>
			<th>Attack Target</th>
			<th>Attack Type</th>
			<th>Points</th>
		</tr>

	<!-- Old Attacks -->
		<?php foreach($existingAttacks as $attack):
			$i++; ?>
			<tr>

			<!-- Order -->
				<td>
					<input class='no-margin' type='number'
						name='newAttack[<?=$i?>][attackNumber]' value='<?=$i?>'>
				</td>

			<!-- Prefixes -->
				<td>
					<select name='newAttack[<?=$i?>][attackPrefix]'>
						<option value=''></option>
						<?php foreach($prefixes as $prefix):
							$selected = isSelected($attack['attackPrefix'],$prefix['attackID']);
							?>
							<option value='<?=$prefix['attackID']?>' <?=$selected?>>
								<?=$prefix['attackText']?>
							</option>
						<?php endforeach ?>
					</select>
				</td>

			<!-- Targets -->
				<td>
					<select name='newAttack[<?=$i?>][attackTarget]'>
						<option value=''></option>
						<?php foreach($targets as $target):
							$selected = isSelected($attack['attackTarget'],$target['attackID']);
							?>
							<option value='<?=$target['attackID']?>' <?=$selected?>>
								<?=$target['attackText']?>
							</option>
						<?php endforeach ?>
					</select>
				</td>

			<!-- Types -->
				<td>
					<select name='newAttack[<?=$i?>][attackType]'>
						<option value=''></option>
						<?php foreach($types as $type):
							$selected = isSelected($attack['attackType'],$type['attackID']);
							?>
							<option value='<?=$type['attackID']?>' <?=$selected?>>
								<?=$type['attackText']?>
							</option>
						<?php endforeach ?>
					</select>
				</td>

			<!-- Points -->
				<td>
					<input type='number' name='newAttack[<?=$i?>][attackPoints]' step=0.1 min=0 max=30
						value='<?=$attack['attackPoints']?>'
						class='no-bottom' >
				</td>

			</tr>
		<?php endforeach ?>


	<!-- Separator -->
		<?php if($i >= 1): ?>
			<tr>
				<td colspan='100%'><HR></td>
			</tr>
		<?php endif ?>

	<!-- New Attacks -->
		<?php for($j = (++$i); ($i - $j) < 5; $i++): ?>
			<tr>

			<!-- Order -->
				<td>
					<input class='no-margin' type='number' name='newAttack[<?=$i?>][attackNumber]'>
				</td>

			<!-- Prefixs -->

				<td>
					<select name='newAttack[<?=$i?>][attackPrefix]'>
						<option value=''>-blank-</option>
						<?php foreach($prefixes as $prefix): ?>
							<option value='<?=$prefix['attackID']?>'>
								<?=$prefix['attackText']?>
							</option>
						<?php endforeach ?>
					</select>
				</td>

			<!-- Targets -->
				<td>
					<select name='newAttack[<?=$i?>][attackTarget]'>
						<option value=''>-blank-</option>
						<?php foreach($targets as $target): ?>
							<option value='<?=$target['attackID']?>'><?=$target['attackText']?></option>
						<?php endforeach ?>
					</select>
				</td>

			<!-- Types -->
				<td>
					<select name='newAttack[<?=$i?>][attackType]'>
						<option value=''>-blank-</option>
						<?php foreach($types as $type): ?>
							<option value='<?=$type['attackID']?>'><?=$type['attackText']?></option>
						<?php endforeach ?>
					</select>
				</td>

			<!-- Points -->
				<td>
					<input type='number' name='newAttack[<?=$i?>][attackPoints]'
						step=0.1 min=0 max=30 placeholder='leave blank to delete'
						class='no-bottom'>
				</td>

			</tr>
		<?php endfor ?>
	</table>

<?php
}


/******************************************************************************/

function importAttacksForm($tournamentID, $formLock){
	$thisTournaments = getEventTournaments($_SESSION['eventID']);
	$allTournaments = getSystemTournaments();
?>


	<div id='import-attacks' class='hidden warning callout cell'>
	<fieldset <?=$formLock?> >
	<form method='POST'>
		<input type='hidden' name='importTournamentAttacks[targetID]' value='<?=$tournamentID?>'>

		<h4>Import Tournament Attacks</h4>
		<p>
		This will import the attacks from the selected tournament <strong>delete all existing attacks.</strong>
		</p>

		<p>From this event:
		<select name='importTournamentAttacks[sourceID1]'>
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
		<select name='importTournamentAttacks[sourceID2]'>
			<option></option>
			<?php foreach($allTournaments as $tournamentID => $tournament):?>
				<option <?=optionValue($tournamentID, null)?> >
					<?=$tournament['eventName']?> [<?=$tournament['tournamentName']?>]
				</option>
			<?php endforeach ?>
		</select>
		</p>

		<button class='button success' name='formName' value='importTournamentAttacks'>
			Import
		</button>

	</form>
	</fieldset>
	</div>
<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
