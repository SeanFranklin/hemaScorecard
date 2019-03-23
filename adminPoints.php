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

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		$formLock = 'disabled';
	} else {
		$formLock = '';
	}

	$targets = getAllAttackTargets();
	$types = getAllAttackTypes();
	$prefixes = getAllAttackPrefixes();
	$existingAttacks = getTournamentAttacks();
	$i = 0;

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<a class='button hollow' href='adminTournaments.php'>
	Back to Tournament Settings
</a>
<h4>Attacks for <strong><?=getTournamentName($_SESSION['tournamentID']);?></strong></h4><hr>

<fieldset <?=$formLock?> >
<form method='POST'>
<button class='button success' name='formName' value='addAttackTypes' <?=$formLock?>> 
	Submit
</button>

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
				<input type='number' name='newAttack[<?=$i?>][attackPoints]' step=0.1 min=0 max=10
					placeholder='leave blank to delete' value='<?=$attack['attackPoints']?>' >
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
					step=0.1 min=0 max=10 placeholder='leave blank to delete'>
			</td>
		
		</tr>
	<?php endfor ?>
</table>

<button class='button success' name='formName' value='addAttackTypes' <?=$formLock?>>
	Submit
</button>

</form>
</fieldset>
<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
