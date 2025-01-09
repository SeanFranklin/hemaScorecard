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
$jsIncludes[] = 'logistics_management_scripts.js';
$createSortableDataTable[] = ['tournamentCheckInTable',100];
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
		define("ALLOW_OTHER", false);
	} else {
		define("ALLOW_CHECKIN", true);
		if(ALLOW['EVENT_MANAGEMENT'] == true){
			define("ALLOW_EDITING", true);
			define("ALLOW_OTHER", true);
		} else {
			define("ALLOW_EDITING", false);
			define("ALLOW_OTHER", false);
		}
	}



	$tournamentRoster = (array)getTournamentFighters($tournamentID, $sortString);
	$showOtherNotice = readOption('T',$tournamentID,'DENOTE_FIGHTERS_WITH_OPTION_CHECK');
	$showRating = (bool)readOption('E',$_SESSION['eventID'],'SHOW_FIGHTER_RATINGS');
	$maxRatingDigits = 1;

	$namesEntered = [];
	$totals['numFighters'] = count($tournamentRoster);
	$totals['numToCheckIn'] = $totals['numFighters'];
	$totals['numToGearCheck'] = $totals['numFighters'];
	$totals['numOther'] = 0;

	importRosterBox();

	$rosterToDisplay = [];
	foreach ($tournamentRoster as $fighter){

		$tmp = [];
		$tmp['name']   = getFighterName($fighter['rosterID']);
		$tmp['school'] = getSchoolName($fighter['schoolID']);

		if((int)$fighter['rating'] == 0 || $showRating == false){
			$tmp['rating'] = "";
		} else {
			$tmp['rating'] = $fighter['rating'];


			if(strlen($fighter['rating']) > $maxRatingDigits){
				$maxRatingDigits = strlen($fighter['rating']);
			}

		}

		$tmp['checkInID'] = "check-in-tournament-".$fighter['rosterID']."-checkIn";
		$tmp['tournamentCheckIn'] = $fighter['tournamentCheckIn'];
		if($fighter['tournamentCheckIn'] != 0){
			$totals['numToCheckIn']--;
			$tmp['checkInText'] = 'done';
		} else {
			$tmp['checkInText'] = 'no';
		}

		$tmp['gearID'] = "check-in-tournament-".$fighter['rosterID']."-gearcheck";
		$tmp['tournamentGearCheck'] = $fighter['tournamentGearCheck'];
		if($tmp['tournamentGearCheck'] != 0){
			$totals['numToGearCheck']--;
			$tmp['gearText'] = 'done';
		} else {
			$tmp['gearText'] = 'no';
		}

		$tmp['otherID'] = "check-in-tournament-".$fighter['rosterID']."-other";
		$tmp['tournamentOtherCheck'] = $fighter[
			'tournamentOtherCheck'];
		$tmp['otherNotice'] = "";
		if($tmp['tournamentOtherCheck'] != 0){

			$tmp['otherText'] = 'yes';
			$totals['numOther']++;

			if($showOtherNotice == true && ALLOW['EVENT_SCOREKEEP'] == true){
				$tmp['name'] = $tmp['name']."*";
			}

		} else {
			$tmp['otherText'] = 'no';

		}

		$rosterToDisplay[$fighter['rosterID']] = $tmp;

		$namesEntered[$fighter['rosterID']] = 'entered';

	}


	if($showRating == true && $maxRatingDigits != 1){

		$spec = "%0{$maxRatingDigits}d";

		foreach($rosterToDisplay as $index => $fighter){
			if($fighter['rating'] != ""){
				$rosterToDisplay[$index]['rating'] = sprintf($spec, $fighter['rating']);
			}
		}
	}


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Page Structure -->
	<?php if(ALLOW['EVENT_SCOREKEEP'] == TRUE): ?>
	<script>

		var refreshPeriod = 1000; // msec

		window.onload = function(){
			refreshCheckInList('tournament', <?=$tournamentID?>);
			window.setInterval(
				function(){ refreshCheckInList('tournament',<?=$tournamentID?>);},
				refreshPeriod
			);
		}

	</script>

	<div class='callout primary'>
		I'm trying something new for form inputs here. <u>On this section alone</u> the data is updated as soon as it changes, without needing to submit. This also means that multiple people can be working on this page at once. (If you're having trouble, try reloading the page.)<BR>
		<a class='button tiny no-bottom secondary' href='participantsTournament.php'>Reload Page</a>
		<a onclick="$('.bulk-tournament-add').toggle()">Bulk Actions ↓</a>


		<span class='hidden bulk-tournament-add'>

		<form method="POST">
			<input type='hidden' name='formName' value='bulkCheckIn'>
			<input type='hidden' name='bulkCheckIn[tournamentID]' value='<?=$tournamentID?>'>

			<div class='grid-x grid-margin-x'>
				<HR class='cell'>


				<div class='cell medium-2'>
					<button class='button warning expanded' name='bulkCheckIn[mode]' value='tournament-in'>
						Check-In<BR><b>Set All</b>
					</button>
				</div>

				<div class='cell large-1'>&nbsp;</div>

				<div class='cell medium-2'>
					<button class='button warning expanded' name='bulkCheckIn[mode]' value='gear-in'>
						Gear Check<BR><b>Set All</b>
					</button>
				</div>

				<div class='cell large-1'>&nbsp;</div>

				<div class='cell medium-2'>
					<button class='button alert expanded' name='bulkCheckIn[mode]' value='tournament-out'>
						Check-In<BR><b>Clear All</b>
					</button>
				</div>

				<div class='cell large-1'>&nbsp;</div>

				<div class='cell medium-2'>
					<button class='button alert expanded' name='bulkCheckIn[mode]' value='gear-out'>
						Gear Check<BR><b>Clear All</b>
					</button>
				</div>

			</div>
		</form>
		</span>

	</div>
	<?php endif ?>


	<form method='POST' id='tournamentRosterForm'>
	<fieldset>

	<div class='grid-x grid-padding-x'>
	<div class='large-9 medium-10 cell'>
	<h4>Number of Fighters: <?=$totals['numFighters']?></h4>
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
		<?php if($showRating == true): ?>
			<th>
				Rating <?=tooltip("This is <u>what the event organizer has entered into Scorecard for seeding purposes</u> .
				It <i>may</i> be HEMA Ratings, or it may be some other rating metric.
				(Especially if there isn't good HR data for the tournament in question.)
				<BR> [Blank] means no data, either because it wasn't entered yet or it can't be found.")?>
			</th>
		<?php endif ?>


		<?php if(ALLOW_CHECKIN == true):?>
			<th style='width:0.1%;white-space: nowrap;'>Check-In</th>
			<th style='width:0.1%;white-space: nowrap;'>Gear Check</th>
		<?php endif ?>

		<?php if(ALLOW_OTHER == true):?>
			<th style='width:0.1%;white-space: nowrap;'>Other</th>
		<?php endif ?>
	</tr>
	</thead>
	<tbody>

<!-- Display existing participants -->
	<?php foreach ($rosterToDisplay as $rosterID => $f):?>

		<tr id='divFor<?= $rosterID ?>'>
			<?php if(ALLOW_EDITING == true):?>
				<td style="width:0.1%">
					<input type='checkbox' name='deleteFromTournament[<?= $rosterID ?>]'
						id='<?= $rosterID ?>' onchange="checkIfFought(this)">
				</td>
			<?php endif ?>

			<td><?=$f['name']?></td>

			<td><?=$f['school']?></td>

			<?php if($showRating == true): ?>
				<td><?=$f['rating']?></td>
			<?php endif ?>

			<?php if(ALLOW_CHECKIN == true): ?>

				<td class='text-center'>

					<a class='button no-bottom tiny' onclick="checkInFighterJs('checkIn')"
						id='<?=$f['checkInID']?>'
						data-checkInType='tournament'
						data-rosterID=<?=$rosterID?>
						data-checked=<?=$f['tournamentCheckIn']?>>
						<?=$f['checkInText']?>
					</a>

				</td>

				<td class='text-center'>

					<a class='button no-bottom tiny' onclick="checkInFighterJs('gearcheck')"
						id='<?=$f['gearID']?>'
						data-checkInType='tournament'
						data-rosterID=<?=$rosterID?>
						data-gearcheck=<?=$f['tournamentGearCheck']?>>
						<?=$f['gearText']?>
					</a>

				</td>

			<?php endif ?>

			<?php if(ALLOW_OTHER == true): ?>

				<td class='text-center'>

					<a class='button no-bottom tiny' onclick="checkInFighterJs('other')"
						id='<?=$f['otherID']?>'
						data-checkInType='tournament'
						data-rosterID=<?=$rosterID?>
						data-other=<?=$f['tournamentOtherCheck']?>>
						<?=$f['otherText']?>
					</a>

				</td>

			<?php endif ?>
		</tr>
	<?php endforeach ?>
	</tbody>
	</table>

	<?php if(ALLOW['EVENT_SCOREKEEP'] == true):?>

		<p>
			<?=$totals['numToCheckIn']?> / <?=$totals['numFighters']?> left To Check-In <BR>
			<?=$totals['numToGearCheck']?> / <?=$totals['numFighters']?> left To Gear Check <BR>
			<?=$totals['numOther']?> / <?=$totals['numFighters']?> left marked 'Other'<BR>
			<i>(You need to refresh the page for these numbers to update.)</i>
		</p>

	<?php endif ?>

	</div>

<!-- Add / Delete Fighter Buttons -->
	<div class='cell'>
	<?=tournamentRosterManagement($eventRoster, $namesEntered)?>
	</div>

	</div>

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
