<?php
/*******************************************************************************
	Cutting Qualifications
	
	Add and remove cutting qualifications
	Not fully implemented
	LOGIN 
		SUPER ADMIN can add qualifications

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Cutting Quallifications';
include('includes/header.php');

if(USER_TYPE < USER_SUPER_ADMIN){
	pageError('user');
} else {
	if(isSet($_SESSION['cuttingQualDate'])){
		$date = $_SESSION['cuttingQualDate'];
	} else{
		$time = strtotime("-2 year", time());
		$date = date("Y-m-d", $time);
	}
	
	
	$standardID = $_SESSION['cuttingQualStandard'];
	if($standardID != null){
		$qualList = getCuttingQualificationsList($standardID, $date);
	}
	$standards = getCuttingQualificationsStandards();

	

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>
	<div class='grid-x grid-margin-x'>
	<div class='shrink cell'>
	<a class='button hollow' href='cutQualsTournament.php'>
		Back to Tournament List
	</a>
	</div>
	
	<div class='cell shrink'>
	<form method='POST' style='display:inline' id='cuttingQualStandard'>
	<input class='hidden' name='formName' value='changeCuttingStandard'>	

	
	<div class='input-group grid-x'>
		<span class='input-group-label small-12 medium-shrink'>
			Standard to display: 
		</span>
		
		<select name='standardID' class='shrink input-group-field'>
			<?php if($_SESSION['cuttingQualStandard'] == null): ?>
				<option selected disabled></option>
			<?php endif ?>
			<?php foreach($standards as $standard): 
				$selected = isSelected($standard['standardID'], $_SESSION['cuttingQualStandard']);
				?>
				<option value='<?=$standard['standardID']?>' <?=$selected?>><?=$standard['standardName']?></option>
			<?php endforeach ?>
		</select>
		
		<span class='input-group-label small-12 medium-shrink'>
			Since:
		</span>
		
		<input type='date' class='no-bottom input-group-field small-12 medium-shrink' 
			name='cuttingQualDate' value='<?=$date?>' id='datePicker'  required>
			
		<button class='input-group-button button hollow success'>Update</button>
	</div>
	
	</form>
	</div>
	</div>
	
	<?php addToQualList($standards); ?>
	<?php showQualList($qualList); ?>
	
<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function addToQualList($standards){
// Interface to add new cutting qualifications
	
	if(USER_TYPE < USER_SUPER_ADMIN){return;}
	$listMode = $_SESSION['newCutQualMode'];
	
	if($listMode != 'all'){
		$qualsToAdd = 5;
	} else {
		$qualsToAdd = 1;
	}

	switch($listMode){
		case 'all':
			$roster = getSystemRoster();
			break;
		case 'school': // Not implemented
		case 'tournament':
		default:
			$roster = getSystemRoster($_SESSION['tournamentID']);
			$_SESSION['newCutQualMode'] = 'tournament';
			$listMode = $_SESSION['newCutQualMode'];
	}
	?> 
		
		
	<fieldset class='fieldset'>
	<legend><h5>Add New Quallification</h5></legend>
	
	<form method='POST'>
		
	<input type='hidden' name='formName' value='newCutQualMode'>
	
	<!-- By tournament entry -->
	<?php $hollow = isNotSelected($listMode == 'tournament', 'hollow'); ?>
	<button class='button secondary <?=$hollow?>' name='newCutQualMode' value='tournament'>
		By Tournament Entry
	</button>
	
	<!-- By school name --
	<?php $hollow = isNotSelected($listMode == 'school', 'hollow'); ?>
	<button class='button secondary <?=$hollow?>' name='newCutQualMode' value='school'>
		By School
	</button> -->
	
	<!-- List all fighters -->
	<?php $hollow = isNotSelected($listMode == 'all', 'hollow'); ?>
	<button class='button secondary <?=$hollow?>' name='newCutQualMode' value='all'>
		All System Fighters
	</button>
	
	</form>
	
	<form method='POST'>
	
	<table class='stack'>
		<tr class='show-for-large'>
			<th>Fighter</th>
			<th>Date [Y-M-D]</th>
			<th>Standard</th>
			<th>Value</th>
		</tr>
		
	<?php for($i = 1; $i <= $qualsToAdd ; $i++): ?>
	<!-- Fighter Input -->
		<tr>
			<td>
				<select name='newQuals[<?=$i?>][systemRosterID]' required>
				<option disabled selected></option>
				<?php foreach($roster as $systemRosterID): 
					$name = getFighterNameSystem($systemRosterID);
					 ?>
					<option value=<?=$systemRosterID?>><?=$name?></option>
				<?php endforeach ?>
		
	<!-- Date Inpute -->
			<td>
				<input type='date' class='no-bottom' name='newQuals[<?=$i?>][qualDate]' 
					id='datePicker' value='<?=date('Y-m-d');?>'
					required>
			</td>
		
	<!-- Standards Input -->
			<td>
				<select name='newQuals[<?=$i?>][standardID]' required>
				
				<?php if($_SESSION['cuttingQualStandard'] == null): ?>
					<option selected disabled></option>
				<?php endif ?>
				<?php foreach($standards as $standard): 
					$selected = isSelected($standard['standardID'], $_SESSION['cuttingQualStandard']);
					$standardID = $standard['standardID'];
					$name = $standard['standardName'];?>
					<option value=<?=$standardID?> <?=$selected?>><?=$name?></option>
				<?php endforeach ?>
				</select>
			</td>
	<!-- Value Input -->
			<td>
				<input type='number' class='no-bottom' name='newQuals[<?=$i?>][qualValue]' value='1'
					min=1 max=10>
			</td>
		</tr>
	<?php endfor ?>	
		
	</table>
	
	<div class='grid-x'>
	<div class='large-10'>
	&nbsp;
	</div>
	<div class='large-2 medium-12 small-12'>
	
	
	<button class='button success expanded' name='formName' value='newCutQuals'>
		Submit
	</button>
	</div>
	</div>
	
	</form>
	</fieldset>	
		
<?php }

/******************************************************************************/


function showQualList($qualList){
// Display the returned quallifications
?>	
	<BR><BR>
	<table>
	<tr>
		<th></th>
		<th>Name</th>
		<th>Date</th>
		<th>Standard</th>
		<th>Value</th>
	</tr>
	
	
	<?php if($qualList != null): ?>
	<?php foreach($qualList as $systemRosterID => $fighter):

		$name = getFighterNameSystem($systemRosterID);
		$date = $fighter['date'];
		$standard = $fighter['standardName'];
		$value = $fighter['qualValue'];
		 ?>
		<tr>
			<form method='POST'>
			<input type='hidden' name='systemRosterID' value='<?=$systemRosterID?>'>
			<input type='hidden' name='qualID' value='<?=$fighter['qualID']?>'>
			<td>
				<button class='button tiny hollow alert no-bottom' 
					name='formName' value='removeQualledFighterEvent'>
					Remove
				</button>
			</td>
			<td><?=$name?></td>
			<td><?=$date?></td>
			<td><?=$standard?></td>
			<td><?=$value?></td>
			</form>
		</tr>
	<?php endforeach ?>
	<?php else: ?>
		<tr>
			<td colspan='100%' class='text-center'>
				No results found
			</td>
		</tr>
	
	<?php endif ?>

	</table>
	
<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
