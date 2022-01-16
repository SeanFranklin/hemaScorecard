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


	$useGrid = readOption('T',$_SESSION['tournamentID'],'ATTACK_DISPLAY_MODE');


	if($formLock == '' && isFullAfterblow($_SESSION['tournamentID']) == false){
		$showGridButton = true;
	} else {
		$showGridButton = false;
	}

	if($useGrid == false){
		$nextMode = 'Grid';
		$individualIsHollow = '';
	} else {
		$nextMode = 'Individual';
		$individualIsHollow = '';
	}

	$afterblowPointValue = getAfterblowPointValue($_SESSION['tournamentID']);
	$maxAfterblowValue = 9;
	$controlPointValue = getControlPointValue($_SESSION['tournamentID']);
	$maxControlValue = 9;

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Top Bar w/ Back and Grid Buttons ---------------------------------------->

<form method='POST'>
	<a class='button hollow' href='adminTournaments.php'>
		Back to Tournament Settings
	</a>
	<?php if($showGridButton == true):?>
		<button class='button' name='attackDefinitionMode' value='<?=$nextMode?>'>
			Switch to <?=$nextMode?> Mode
		</button>
		<input class='hidden' name='formName' value='switchAttackDefinitionMode'>
	<?php endif?>
</form>


<?php if(isFinalized($_SESSION['tournamentID']) == true): ?>

	<div class='callout alert text-center' data-closeable>
		Results for this tournament have been finalized, most changes have been disabled.
		<a href='infoSummary.php'>Remove final results</a> to edit.
	</div>

<?php else: ?>

	<?=tournamentTitle($_SESSION['tournamentID'],$useGrid,$formLock)?>

<!-- Afterblow/Control Points ---------------------------------------->

	<fieldset <?=$formLock?>  >
	<form method='POST'>
		<div class='grid-x grid-margin-x'>

			<?php if(isDeductiveAfterblow($_SESSION['tournamentID']) == true): ?>
				<div class='input-group shrink cell no-bottom'>
					<span class='input-group-label no-bottom'>
						Afterblow

					</span>
					<select class='input-group-field no-bottom' name='tournamentAttackModifiers[afterblow]'>
						<option value=0>Not Used</option>
						<?php for($p = 1;$p<=$maxAfterblowValue;$p++):?>
							<option <?=optionValue($p, $afterblowPointValue)?>>-<?=$p?></option>
						<?php endfor?>
					</select>
				</div>
			<?php endif ?>

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
					Update Modifiers
				</button>
		</div>
	</form>
	</fieldset>
	<HR>

<!-- Point Values ---------------------------------------------------------------->

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

	<?php 
		if($useGrid == false){
			displayModeIndividual($formLock);
		} else {
			displayModeIndividual($formLock);
		}
	?>
	

	<button class='button success' name='formName' value='addAttackTypes' <?=$formLock?>>
		Submit
	</button>

	</form>
	</fieldset>

<?php endif ?>
<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayModeGrid($formLock){
	$targets = getAllAttackTargets();
	$types = getAllAttackTypes();
	$prefixes = getAllAttackPrefixes();
	$existingAttacks = getTournamentAttacks($_SESSION['tournamentID']);
	$i = 0;

	$sql = "SELECT refTarget, refType";


?>
<HR><HR>
<?
}

/******************************************************************************/

function displayModeIndividual($formLock){

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

function tournamentTitle($tournamentID){
?>
	<h4>
		Attacks for <strong><?=getTournamentName($tournamentID);?></strong>
	</h4>
<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
