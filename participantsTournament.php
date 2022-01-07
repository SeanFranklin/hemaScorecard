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
} elseif(ALLOW['VIEW_ROSTER'] == false) {
	displayAlert("Event is still upcoming<BR>Roster not yet released");
} else{

	$eventRoster = getEventRoster();

	if($_SESSION['rosterViewMode'] == 'school'){
		$sortString = 'school';
	} else {
		$sortString = 'name';
	}


	// Check if editing the event tournament roster, or if checking fighters in is allowed.
	if(   (LOCK_TOURNAMENT != '')
	   || (ALLOW['EVENT_SCOREKEEP'] == false)
	   || ($_SESSION['isMetaEvent'] == true)
	   || ($_SESSION['formatID'] == FORMAT_META && isMetaTournamentRosterManual($tournamentID) == false)
	){
		define("ALLOW_EDITING", false);
		define("ALLOW_CHECKIN", false);
	} else {
		define("ALLOW_CHECKIN", true);
		if(ALLOW['EVENT_MANAGEMENT'] == true){
			define("ALLOW_EDITING", true);
		} else {
			define("ALLOW_EDITING", false);
		}
	}

	$tournamentRoster = getTournamentFighters($tournamentID,$sortString);
	$namesEntered = [];
	$numFighters = count($tournamentRoster);

	$startOfForm = "checkInFighters[tournament][{$tournamentID}]";
	importRosterBox();

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

	if(ALLOW_EDITING == false){
		return;
	}

	$tournamentIDs = getEventTournaments($_SESSION['eventID']);

?>

<!-- Delete participants -->

	<?php confirmDeleteReveal('tournamentRosterForm', 'deleteFromTournamentRoster', 'large'); ?>
	<span id='deleteButtonContainer'>
		<button class='button alert hollow' name='formName' value='deleteFromTournamentRoster' 
			id='deleteButton' <?=LOCK_TOURNAMENT?>>
			Delete Selected
		</button>
	</span>

	&nbsp;
	<a class='button hollow' onclick="toggleClass('add-fighters')" <?=LOCK_TOURNAMENT?>>
		Add Fighters
		<span class='add-fighters'>↓</span>
		<span class='add-fighters hidden'>↑</span>
	</a>

	&nbsp;
	<a class='button hollow warning hidden add-fighters' data-open='importFromTournament'>
		Import From Other Tournament
		<?=tooltip("Populate this tournament with the entries from another tournament. Can be used to reduce dupication or import the top seeds from another tournament.")?>
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

<?php
}

/******************************************************************************/

function importRosterBox(){
	if(ALLOW_EDITING == false){
		return;
	}

	$tournamentIDs = getEventTournaments($_SESSION['eventID']);
?>

<!-- Import participants -->
	<div class='reveal medium' id='importFromTournament' data-reveal>
		<form method='POST'>
		<h5>Import Roster:</h5>

		<input class='hidden' name='importTournamentRoster[toTournamentID]' value=<?=$_SESSION['tournamentID']?> >
		
		<div class='input-group'>
			<span class='input-group-label'>Tournament</span>
			<select class='input-group-field' name='importTournamentRoster[fromTournamentID]'>
				<option selected disabled></option>
				<?php foreach($tournamentIDs as $tournamentID):
					if($tournamentID == $_SESSION['tournamentID']){
						continue;
					}
					?>
					<option value='<?=$tournamentID?>'><?=getTournamentName($tournamentID)?></option>
				<?php endforeach ?>
			</select>
		</div>

		<div class='input-group'>
			<span class='input-group-label'>
				Seeding 
				<?=tooltip("Inport only the final placings of the above tournament, within this range. Leave blank to import the entire roster.<p><strong>Note:</strong> This can only be used on finalized tournaments</p>")?>
			</span>
			<input type='number' class='input-group-field' name='importTournamentRoster[minPlacing]'>
			<span class='input-group-label'>
				to
			</span>
			<input type='number' class='input-group-field' name='importTournamentRoster[maxPlacing]'>
		</div>


	<!-- Submit buttons -->
		<div class='grid-x grid-margin-x'>
			<button class='success button small-6 cell' name='formName' value='importTournamentRoster'>
				Update
			</button>
			<button class='secondary button small-6 cell' data-close aria-label='Close modal' type='button'>
				Cancel
			</button>
		</div>
		</form>
		
	<!-- Reveal close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	
	</div>


<?php

}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
