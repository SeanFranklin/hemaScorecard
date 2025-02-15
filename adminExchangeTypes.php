<?php
/*******************************************************************************
	Manage Tournaments

	View and change settings of tournaments. Delete existing tournaments.
	LOGIN
		- ADMIN or higher required to view

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Point Values';
$hideEventNav = true;
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false) {
	pageError('user');
} elseif($_SESSION['tournamentID'] == null){
	pageError('tournament');
} else{

	if(ALLOW['EVENT_MANAGEMENT'] == false || isFinalized($_SESSION['tournamentID']) == true){
		$formLock = 'disabled';
	} else {
		$formLock = '';
	}

	// Ability to import attacks from other tournaments
	importAttacksForm($_SESSION['tournamentID']);
	$formatID = getTournamentFormat($_SESSION['tournamentID']);


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Top Bar w/ Back and Grid Buttons ---------------------------------------->






<?php if(isFinalized($_SESSION['tournamentID']) == true && ALLOW['SOFTWARE_ADMIN'] == FALSE): ?>

	<div class='callout alert text-center' data-closeable>
		Results for this tournament have been finalized, most changes have been disabled.
		<a href='infoSummary.php'>Remove final results</a> to edit.
	</div>

	<a class='button hollow' href='adminTournaments.php'>
		Back to Tournament Settings
	</a>

<?php else: ?>

	<h4>
		Attacks for <strong><?=getTournamentName($tournamentID);?></strong>
		<a class='button hollow' href='adminTournaments.php'>
			Back to Tournament Settings
		</a>
	</h4>

	<ul class="tabs" data-tabs id="exchange-type-tabs">


		<li class="tabs-title is-active">

			<a data-tabs-target="panel-attack" href="#panel-attack2">
				<?php if($formatID != FORMAT_SOLO): ?>
					Attack Definitions
				<?php else: ?>
					Deductions
				<?php endif ?>
			</a>
		</li>

		<li class="tabs-title">
			<a data-tabs-target="panel-mod" href="#panel-mod2">Attack Modifiers</a>
		</li>
		<li class="tabs-title">
			<a data-tabs-target="panel-entry" href="#panel-entry2">Data Entry Mode</a>
		</li>
	</ul>



	<div class="tabs-content" data-tabs-content="exchange-type-tabs">
		<div class="tabs-panel  is-active" id="panel-attack">
			<?php if($formatID != FORMAT_SOLO): ?>
				<?=exchangeTypeAttacks($formLock)?>
			<?php else: ?>
				<?=exchangeTypeDeductions($formLock)?>
			<?php endif ?>
		</div>
		<div class="tabs-panel" id="panel-mod">
			<?=exchangeTypeModifiers($formLock)?>
		</div>
		<div class="tabs-panel" id="panel-entry">
			<?=exchangeTypeDataEntryMode($formLock)?>
		</div>
	</div>



<!-- Afterblow/Control Points ---------------------------------------->




	<HR>

<!-- Point Values ---------------------------------------------------------------->



<?php endif ?>
<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

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
		<i>
			Leave the points field blank to delete an entry
		</i>

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

	<fieldset <?=$formLock?> >

	<form method='POST'>
		<div class='cell grid-x grid-margin-x no-bottom'>


				<div class='input-group shrink cell no-bottom'>
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
						<span class='input-group-label no-bottom'><i>Pre-Specifying an afterblow value is only valid <BR>in deductive afterblow scoring.</i>
						</span>
					<?php endif ?>

				</div>


				<div class='input-group shrink cell no-bottom'>
					<span class='input-group-label no-bottom'>Controlling Action</span>
					<select class='input-group-field no-bottom' name='tournamentAttackModifiers[control]'>
						<option value=0>Not Used</option>
						<?php for($p = 1;$p<=$maxControlValue;$p++):?>
							<option <?=optionValue($p, $controlPointValue)?>>+<?=$p?></option>
						<?php endfor?>
					</select>
				</div>

				<button class='shrink cell no-bottom button success' name='formName' value='tournamentAttackModifiers'>
					Update
				</button>
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



	<fieldset <?=$formLock?>>
		<legend></legend>
		<form method='POST'>
			<div class='cell grid-x grid-margin-x'>

				<div class='cell'>
					<?=$warnMsg?>
				</div>

			<div class=' cell input-group no-bottom shrink'>

				<span class='input-group-label'>
					Input Mode
				</span>

				<select class='input-group-field' name='attackDefinitionMode' >
					<option <?=optionValue(ATTACK_DISPLAY_MODE_NORMAL, $attackDisplayMode)?> >Normal</option>
					<option <?=optionValue(ATTACK_DISPLAY_MODE_GRID, $attackDisplayMode)?> <?=$gridValid?>>Grid</option>
					<option <?=optionValue(ATTACK_DISPLAY_MODE_CHECK, $attackDisplayMode)?> <?=$checkValid?>>Check-Box</option>
				</select>

				<div class='input-group-button'>
					<button class='button success' name='formName' value='switchAttackDefinitionMode'>
						Update
					</button>
				</div>

			</div>

			<div class='cell'>
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

function importAttacksForm($tournamentID){
	$thisTournaments = getEventTournaments($_SESSION['eventID']);
	$allTournaments = getSystemTournaments();
?>


	<div id='import-attacks' class='hidden warning callout cell'>
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
	</div>
<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
