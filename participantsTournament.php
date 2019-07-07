<?php
/*******************************************************************************
	Tournament Roster
	
	View tournament roster and add fighters
	Login:
		- ADMIN or above can add or remove fighters from the tournament
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Tournament Roster';
$includeTournamentName = true;
$lockedTournamentWarning = true;
$jsIncludes[] = 'roster_management_scripts.js';
$createSortableDataTable[] = 'tournamentCheckInTable';
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];
if($tournamentID == null){
	pageError('tournament');
} else{

	$eventRoster = getEventRoster();

	if($_SESSION['rosterViewMode'] == 'school'){
		$sortString = 'school';
	} else {
		$sortString = 'name';
	}

 	


	if(LOCK_TOURNAMENT == ''){
		if(ALLOW['EVENT_MANAGEMENT'] != true){
			define("ALLOW_EDITING", false);
		} else {
			if(    $_SESSION['formatID'] != FORMAT_COMPOSITE
				|| isCompositeRosterManual($tournamentID) == true){
				define("ALLOW_EDITING", true);
			} else {
				define("ALLOW_EDITING", false);
			}
		}

		if(ALLOW['EVENT_SCOREKEEP'] != true){
			define("ALLOW_CHECKIN", false);
		} else {
			if(    $_SESSION['formatID'] != FORMAT_COMPOSITE
				|| isCompositeRosterManual($tournamentID) == true){
				define("ALLOW_CHECKIN", true);
			} else {
				define("ALLOW_CHECKIN", false);
			}
		}
	} else {
		define("ALLOW_EDITING", false);
		define("ALLOW_CHECKIN", false);
	}

	$tournamentRoster = getTournamentFighters($tournamentID,$sortString);
	$namesEntered = [];
	$numFighters = count($tournamentRoster);

	$startOfForm = "checkInFighters[tournament][{$tournamentID}]";

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Page Structure -->
	
	<form method='POST' id='tournamentRosterForm'>
	<fieldset>
	
	<div class='grid-x grid-padding-x'>
	<div class='large-8 medium-10 cell'>
	<h4>Number of Fighters: <?=$numFighters?></h4>					
	<input type='hidden' name='tournamentID' value=<?= $tournamentID ?> id='tournamentID'>

	<table id="tournamentCheckInTable" class="display">
		
<!-- Table headers -->
	<thead>
	<tr>

		<?php if(ALLOW_EDITING == true):?>
			<th style='width:0.1%;white-space: nowrap;'>✘</th>
		<?php endif ?>

		<th>Name</th>
		<th>School</th>


		<?php if(ALLOW_CHECKIN == true):?>
			<th style='width:0.1%;white-space: nowrap;'>Check-In</th>
			<th style='width:0.1%;white-space: nowrap;'>Gear Check</th>
			<th style='width:0.1%;white-space: nowrap;'>Update</th>
		<?php endif ?>
	</tr>
	</thead>
	<tbody>

<!-- Display existing participants -->
	<?php foreach ($tournamentRoster as $person):
		$namesEntered[$person['rosterID']] = 'entered';
		$rosterID = $person['rosterID'];
		$schoolID = $person['schoolID'];
		$startOfForm2 = $startOfForm."[{$rosterID}]";
		if($person['tournamentCheckIn'] == SQL_TRUE){
			$checkin = 'checked';
		} else {
			$checkin = '';
		}
		if($person['tournamentGearCheck'] == SQL_TRUE){
			$gearcheck = 'checked';
		} else {
			$gearcheck = '';
		}
	?>
		<tr id='divFor<?= $rosterID ?>'>
			<?php if(ALLOW_EDITING == true):?>
				<td style="width:0.1%">
					<input type='checkbox' name='deleteFromTournament[<?= $rosterID ?>]'
						id='<?= $rosterID ?>' onchange="checkIfFought(this)">
				</td>
			<?php endif ?>


			<td><?=getFighterName($rosterID)?></td>
			<td><?=getSchoolName($schoolID)?></td>
			<?php if(ALLOW_CHECKIN == true): ?>
				

				<td data-sort="<?=$checkin?>">

					<div class='switch text-center no-bottom'>
						<input type='hidden' name='<?=$startOfForm2?>[checkin]' value='0'>
						<input class='switch-input' type='checkbox' 
							id='<?=$startOfForm2?>[checkin]' <?=$checkin?>
							name='<?=$startOfForm2?>[checkin]' value='1'>
						<label class='switch-paddle' for='<?=$startOfForm2?>[checkin]'>
						</label>
					</div>
				</td>

				<td data-sort="<?=$gearcheck?>">

					<div class='switch text-center no-bottom'>
						<input type='hidden' name='<?=$startOfForm2?>[gearcheck]' value='0'>
						<input class='switch-input' type='checkbox' 
							id='<?=$startOfForm2?>[gearcheck]' <?=$gearcheck?>
							name='<?=$startOfForm2?>[gearcheck]' value='1'>
						<label class='switch-paddle' for='<?=$startOfForm2?>[gearcheck]'>
						</label>
					</div>
				</td>

				<td class='text-center'>
					<button class='button success hollow tiny no-bottom' 
						name='formName' value='checkInFighters'>
						<strong>✅</strong>
					</button>
				</td>
			<?php endif ?>
		</tr>
	<?php endforeach ?>
	</tbody>
	<table>

	</div>
	</div>

<!-- Add / Delete Fighter Buttons -->
	<?=tournamentRosterManagement($eventRoster, $namesEntered)?>
		
	</fieldset>
	</form>	
		
<?php 		
	
}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function tournamentRosterManagement($eventRoster, $namesEntered){

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

?>

<!-- Delete participants -->
	<?php if(ALLOW_EDITING == true): ?>
		<?php confirmDeleteReveal('tournamentRosterForm', 'deleteFromTournamentRoster', 'large'); ?>
		<span id='deleteButtonContainer'>
			<button class='button alert hollow' name='formName' value='deleteFromTournamentRoster' 
				id='deleteButton' <?=LOCK_TOURNAMENT?>>
				Delete Selected
			</button>
		</span>
	<?php endif ?>

	<?php if(ALLOW_EDITING == true): ?>
		&nbsp;
		<a class='button hollow' onclick="toggleClass('add-fighters')" <?=LOCK_TOURNAMENT?>>
			Add Fighters
			<span class='add-fighters'>↓</span>
			<span class='add-fighters hidden'>↑</span>
		</a>

<!-- Add new participants -->
	<div class='hidden add-fighters'>
		<table class='hidden add-fighters'>
		
		<?php $numBlankEntries = 5;
			for ($k = 1 ; $k <= $numBlankEntries; $k++): ?>
				<tr>
				<td>
					<select name='addToTournament[<?= $k ?>]'>
					<option value=''></option>
					<?php foreach($eventRoster as $entry): ?>
						<?php if(!isset($namesEntered[$entry['rosterID']])): ?>
							<option value='<?= $entry['rosterID'] ?>'>
								<?=getFighterName($entry['rosterID']) ?>
							</option>
						<?php endif ?>
					<?php endforeach ?>
					</select>
				</td>
				</tr>
		
			<?php endfor ?>
		</table>

		<button class='button success hidden add-fighters' name='formName' 
			value='addToTournamentRoster' <?=LOCK_TOURNAMENT?>>
			Add Fighters
		</button>
	</div>

	
	<?php endif ?>


<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
