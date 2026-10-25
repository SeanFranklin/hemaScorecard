<?php
/*******************************************************************************
	Event Roster

	Display information on individuals
	registered in the event and for adding new participants

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Event Roster';
$jsIncludes[] = 'roster_management_scripts.js';
include('includes/header.php');

$eventID = $_SESSION['eventID'];

if($eventID == null){
	pageError('event');
} elseif(ALLOW['VIEW_ROSTER'] == false) {
	displayAlert("Event is still upcoming<BR>Roster not yet released");
} else {

// Get information
	$tournamentList = getEventTournaments();
	$tournamentNames = getEventTournamentNames($tournamentList);
	$tournamentEntries = getEntriesByFighter($tournamentList);

	$isTournamentScheduleUsed = logistics_isTournamentScheduleUsed($_SESSION['eventID']);
	$scheduleByRosterID = logistics_getScheduleByFighter($_SESSION['eventID']);
	$scheduleBlockNames = logistics_getScheduleBlockNames($_SESSION['eventID']);


	$sortString = NAME_MODE." ASC, ".NAME_MODE_2." ASC";

	if($_SESSION['rosterViewMode'] == 'school'){
		$sortString = "(CASE WHEN schoolShortName='' then 1 ELSE 0 END), schoolShortName ASC, ".$sortString;
	}

	$roster = getEventRoster($sortString);
	$schoolList = getSchoolList();

	if(ALLOW['EVENT_MANAGEMENT'] == true && $_SESSION['isMetaEvent'] == false){
		define("ALLOW_EDITING", true);
	} else {
		define("ALLOW_EDITING", false);
	}


// For entering new participants
	if(ALLOW_EDITING == true){
		if(!isset($_SESSION['addEventParticipantsMode'])){
			$_SESSION['addEventParticipantsMode'] = '';
		}

		displayEntryConflicts();
		addNewParticipantsButtons();
		if($_SESSION['addEventParticipantsMode'] == 'on'){
			addNewParticipantsCard();
		}

		confirmDeleteReveal('eventRosterForm', 'deleteFromEvent', 'large');
	}

	eventRegistrationCountsDisplay();

// Display roster
	displayEventRoster($roster, $isTournamentScheduleUsed,
						$tournamentEntries, $tournamentNames,
						$scheduleByRosterID, $scheduleBlockNames, $schoolList);

}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function eventRegistrationCountsDisplay(){

	$numParticipants = getNumEventRegistrations($_SESSION['eventID']);
	$numFighters = getNumEventFighters($_SESSION['eventID']);
	$totalTournamentEntries = getNumEventTournamentEntries($_SESSION['eventID']);
?>

	<table class='stack roster-counts'>
		<tr>
			<td>
				Total Event Participants
				<?=tooltip("Organizers will typically <b>NOT</b> enter non-fighting participants into Scorecard, and thus the total attendance may be higher than this number.")?>:
				<strong><?=$numParticipants?></strong>
			</td>
			<td>
				Total Event Fighters
				<?=tooltip('Number of participants that fought in a tournament.')?>:
				<strong><?=$numFighters?></strong>
			</td>
			<td>
				Total Tournament Registrations
				<?=tooltip('<u>Example</u>: If the same person fights in 3 tournaments they count as 3 registrations.')?>:
				<strong><?=$totalTournamentEntries?></strong>
			</td>

		</tr>
	</table>

<?php
}

/******************************************************************************/

function displayEventRoster($roster, $isTournamentScheduleUsed,
							$tournamentEntries, $tournamentNames,
							$staffingBlocks, $scheduleBlockNames, $schoolList){
// Displays table of fighters already registered, and which tournaments they are in.
// Organizers click a row to edit it inline (see RosterTable in
// roster_management_scripts.js); the editor posts the same
// editParticipantData[...] fields the old modal did.
	?>

	<form method='POST' id='eventRosterForm'>
	<input type='hidden' id='eventID' name='eventID' value=<?=$_SESSION['eventID']?>>

	<table class='hover roster-table'>

	<?php

	tableHeaders();

	foreach ((array)$roster as $person):

		$rosterID = $person['rosterID'];
		$fullName = getFighterName($rosterID);
		$entries = isset($tournamentEntries[$rosterID]) ? (array)$tournamentEntries[$rosterID] : [];

		$schoolName = $person['schoolShortName'];
		if($person['schoolBranch'] != ''){
			$schoolName .= ", {$person['schoolBranch']}";
		}

		$scheduleHtml = rosterScheduleHtml($rosterID, $fullName, $isTournamentScheduleUsed,
											$staffingBlocks, $scheduleBlockNames);
		$rowClass = 'roster-row';
		$rowTabIndex = '';
		if(ALLOW_EDITING == true || $scheduleHtml != ''){
			$rowClass .= ' pointer';
			$rowTabIndex = "tabindex='0'"; // Enter or Space opens it (RosterTable)
		}
		?>

		<tr class='<?=$rowClass?>' <?=$rowTabIndex?> id='divFor<?=$rosterID?>'
			data-roster-id='<?=$rosterID?>'
			data-first-name='<?=htmlspecialchars($person['firstName'], ENT_QUOTES | ENT_SUBSTITUTE)?>'
			data-last-name='<?=htmlspecialchars($person['lastName'], ENT_QUOTES | ENT_SUBSTITUTE)?>'
			data-school-id='<?=(int)$person['schoolID']?>'
			data-tournament-ids='<?=implode(',', $entries)?>'>

		<!-- Deletion checkboxes -->
			<?php if(ALLOW_EDITING == true): ?>
				<td class='roster-remove'>
					<input type='checkbox' name='deleteFromEvent[<?=$rosterID?>]'
						id='<?=$rosterID?>' onchange="checkIfFought(this)">
				</td>
			<?php endif?>

		<!-- Participant info -->
			<td class='roster-name'>
				<?=getFighterName($rosterID, null, null, false, true)?>
			</td>

			<td class='roster-school'>
				<?=$schoolName?>
			</td>

		<!-- Tournament entries -->
			<td class='roster-tournaments'>
				<?php if(count($entries) == 0): ?>
					<span class='grey-text'>none</span>
				<?php endif ?>
				<?php foreach($entries as $tournamentID): ?>
					<div class='tournament-box is-static'><?=$tournamentNames[$tournamentID]?></div>
				<?php endforeach ?>
			</td>

		</tr>

		<?php if($scheduleHtml != ''): ?>
			<tr id='tList-<?=$rosterID?>' class='roster-schedule hidden'>
				<td colspan='100%'><?=$scheduleHtml?></td>
			</tr>
		<?php endif ?>

	<?php endforeach?>

	</table>

	<?php if(ALLOW_EDITING == true): ?>
		<span id='deleteButtonContainer'>
			<button class='button alert hollow' name='formName' value='deleteFromEvent' id='deleteButton'>
				Delete Selected
			</button>
		</span>
	<?php endif?>

	</form>

	<?php
	// Public viewers get row-click schedule toggles only; organizers also get
	// the inline editor and, when open, the entry card. One init for both
	// modules, deferred until the footer has loaded roster_management_scripts.js.
	$config = [];
	if(ALLOW_EDITING == true){
		rosterInlineEditor();
		$config['schools'] = rosterSchoolOptions($schoolList);
		$config['tournamentGroups'] = rosterTournamentGroups(array_keys((array)$tournamentNames));
		// Same threshold as the entry card's collapsing rows.
		$config['manyTournaments'] = count((array)$tournamentNames) > 8;
	}
	?>
	<script>
		window.addEventListener('DOMContentLoaded', function(){
			var config = <?=json_encode($config, JSON_FLAGS_INLINE)?>;
			RosterTable.init(config);
			if(document.getElementById('addParticipantsForm')){ RosterEntry.init(config); }
		});
	</script>

<?php }

/******************************************************************************/

function rosterScheduleHtml($rosterID, $fullName, $isTournamentScheduleUsed,
							$staffingBlocks, $scheduleBlockNames){
// Staffing assignments and personal schedule link for a participant.
// Returns '' if there is nothing to show.

	if(ALLOW['VIEW_SCHEDULE'] == false){ return ''; }

	$hasStaffing = isset($staffingBlocks[$rosterID]);
	if($hasStaffing == false && $isTournamentScheduleUsed == false){ return ''; }

	ob_start();
	?>

	<?php if($hasStaffing): ?>
		<u><?=$fullName?></u> is also scheduled for:

		<?php foreach((array)$staffingBlocks[$rosterID] as $shift):
			$name = $scheduleBlockNames[$shift['blockID']];
			?>
			<div class='tournament-box is-static'>
				<?php if($shift['blockTypeID'] != SCHEDULE_BLOCK_MISC): ?>
					<u>Staffing</u>:
				<?php endif ?>
				<?=$name?>
			</div>
		<?php endforeach?>
	<?php endif ?>

	<?php if($isTournamentScheduleUsed == true || $hasStaffing): ?>
		<BR>
		<a onclick="goToPersonalSchedule('<?=$rosterID?>')">
			View Full Schedule for <?=$fullName?>
		</a>
	<?php endif ?>

	<?php
	return trim(ob_get_clean());
}

/******************************************************************************/

function rosterInlineEditor(){
// Form + row template for editing a roster entry in place (built by
// RosterTable.openEditor). Editor inputs reference the form via the form=
// attribute because the roster table already sits inside the delete form
// and forms can not nest. The POST is identical to the old edit modal's.
	?>

	<form method='POST' id='rosterEditForm'>
		<input type='hidden' name='formName' value='editEventParticipant'>
		<input type='hidden' name='editParticipantData[rosterID]' id='editRosterID' value='0'>
	</form>

	<template id='rosterEditorTemplate'>
		<tr class='roster-editor'>
			<td colspan='100%'>
				<?php rosterEditorFields('rosterEditForm', 'editParticipantData') ?>

				<div class='roster-editor-warning hidden'>
					<p><span class='red-text'><u>Warning:</u> You are trying to remove a fighter from a tournament
					they have already started competing in.</span><BR>
					If they are injured or disqualified please use
					<strong><a href='adminFighters.php'>Manage Fighters > Withdraw Fighters</a></strong></p>
					<div class='text-right'>
						<button class='button alert hollow no-bottom roster-save-anyway' form='rosterEditForm'>
							I understand and still want to make the changes
						</button>
					</div>
				</div>

				<div class='roster-editor-schedule'></div>
			</td>
		</tr>
	</template>

<?php }

/******************************************************************************/

function addNewParticipantsButtons(){
// Mode selector bar. The 'Done' button sits on the far right, away from the
// navigation buttons, so it is not mistaken for one of them.
	if(ALLOW_EDITING == false){ return; }
	$isOpen = ($_SESSION['addEventParticipantsMode'] == 'on');
	?>

	<form method='POST' class='add-participants-bar'>
	<input type='hidden' name='formName' value='addEventParticipantsMode'>

		<div class='add-participants-bar-left'>
			<?php if($isOpen == false):?>
				<button class='button' name='newParticipantsMode' value='on'>
					Add Event Participants
				</button>
			<?php endif?>
			<a class='button hollow secondary' href='adminSchools.php'>
				Add New Schools
			</a>
			<a class='button hollow secondary' href='participantsAdditional.php'>
				Non-Participating Entries
			</a>
			<a class='button hollow secondary' href='participantsImport.php'>
				Import CSV
			</a>
		</div>

		<?php if($isOpen == true):?>
			<div class='add-participants-bar-right'>
				<button class='button primary' name='newParticipantsMode' value='off'>
					Done Adding Participants
				</button>
			</div>
		<?php endif?>

	</form>

<?php }


/******************************************************************************/

function displayEntryConflicts(){
// Asks for direction with entry conflicts
// ie. Duplicate Fighters, new fighter already in system
	if(!isset($_SESSION['rosterEntryConflicts'])){ return; }
	?>

	<div class='large-12 alert callout'>

	<!-- Fighters which have already been entered -->
	<?php foreach(@(array)$_SESSION['rosterEntryConflicts']['alreadyEntered'] as $systemRosterID):
		$name = getFighterNameSystem($systemRosterID);?>
		<b><?=$name?></b> is already entered in this event.
		<BR><BR>
	<?php endforeach?>


	<form method='POST'>

	<?php // Fighters which already exists in the system

	$k = 99; // An arbitrarialy large index for the fighters to be added. Has to be above the maximum that can be added at a time normally
	foreach(@(array)$_SESSION['rosterEntryConflicts']['alreadyExists'] as $conflict):

		$systemRosterID = $conflict['queryData']['systemRosterID'];
		$name = getFighterNameSystem($systemRosterID);

		$dbSchoolID = $conflict['queryData']['schoolID'];
		$dbSchoolName = getSchoolName($dbSchoolID, 'long', 'branch');

		$postSchoolID = $conflict['postData']['schoolID'];
		$postSchoolName = getSchoolName($postSchoolID, 'long', 'branch');

		$staffCompetency = $conflict['postData']['staffCompetency'];

		?>

		<b><?=$name?></b> already exists in the system, from <u><?=$dbSchoolName?></u><BR>

		<?php //The school they are registered with in the system?>
		<input type='hidden' name='newParticipants[<?=$k?>][systemRosterID]' value='<?=$systemRosterID?>'>
		<input type='radio' checked name='newParticipants[<?=$k?>][schoolID]' value='<?=$dbSchoolID?>'>
		Enter from <i><?=$dbSchoolName?></i> (what the database has)<BR>

		<?php //The school the user tried to enter them with?>
		<input type='radio' name='newParticipants[<?=$k?>][schoolID]' value='<?=$postSchoolID?>'>
		Enter from <i><?=$postSchoolName?></i> (what you entered)<BR>

		<input type='radio' name='newParticipants[<?=$k?>][schoolID]' value=''>
		Screw it, don't enter them at all

		<input type='hidden' name='newParticipants[<?=$k?>][staffCompetency]' value='<?=$staffCompetency?>'>
		<?php foreach((array)@$conflict['postData']['tournamentIDs'] as $tournamentID):?>
			<input type='hidden' name='newParticipants[<?=$k ?>][tournamentIDs][]' value=<?=$tournamentID?>>
		<?php endforeach?>
		<HR>
		<?php $k++;?>
	<?php endforeach?>

	<button class='button hollow text-center' name='formName' value='addEventParticipants'>
		Confirm All
	</button>

	</form>

	</div>

	<?php unset($_SESSION['rosterEntryConflicts'])?>

<?php }

/******************************************************************************/

function addNewParticipantsCard(){
// Interface to add participants to the event.
// Rows are built client side from the <template> below (see RosterEntry in
// roster_management_scripts.js, initialised from displayEventRoster): the
// card opens with one row and a fresh one appears whenever something goes
// into the last row. Each row posts the same newParticipants[k][...] fields the legacy
// per-school form did, so addEventParticipants() and the conflict callout
// are unchanged.

	if(ALLOW_EDITING == false){ return; }
	$eventID = $_SESSION['eventID'];

	$tournamentNames = (array)getTournamentsAlphabetical($eventID);

	// If certain tournaments have been configured to suppress direct entry they are not
	// listed on this page as options for registration.
	foreach($tournamentNames as $tournamentID => $tName){
		if(readOption('T', $tournamentID, 'SUPPRESS_DIRECT_ENTRY') != 0){
			unset($tournamentNames[$tournamentID]);
		}
	}

	// If there is only one tournament make it selected by default
	$autoCheck = (count($tournamentNames) == 1);

	// Chips are grouped by weapon. With many tournaments, rows other than the
	// one being worked on collapse to just their picks (RosterEntry.setActive).
	$tournamentGroups = rosterTournamentGroups(array_keys($tournamentNames));
	$collapseClass = count($tournamentNames) > 8 ? 'tournaments-collapse' : '';

	$useStaff = logistics_isStaffAssignmentOnEventEntry($eventID);
	$dStaffCompetency = logistics_getDefaultStaffCompetency($eventID);

	?>

	<div class='callout secondary add-participants-card <?=$collapseClass?>' id='addParticipantsCard'>

	<form method='POST' id='addParticipantsForm'>
	<input type='hidden' name='formName' value='addEventParticipants'>

		<div class='new-participant-header'>
			<div class='new-participant-cell name-cell'>Name</div>
			<div class='new-participant-cell school-cell'>School</div>
			<?php if($useStaff == true): ?>
				<div class='new-participant-cell staff-cell'>Staff
					<?=tooltip("Assign as a staff member for this event.
						The numbers are if you want to rate them based on their skillsets
						to help assign table/ judge/ director/ etc...")?>
				</div>
			<?php endif ?>
			<div class='new-participant-cell tournaments-cell'>Tournaments</div>
			<div class='new-participant-cell remove-cell'></div>
		</div>

		<div id='newParticipantRows'></div>

		<div class='new-participant-footer'>
			<button type='button' id='addParticipantRow' class='button hollow'>+ Add Row</button>
			<span class='new-participant-limit' id='newParticipantLimit' hidden></span>
			<span class='new-participant-error red-text' id='newParticipantError'></span>
			<button class='button success' name='formName' value='addEventParticipants' id='submitNewParticipants'>
				Add New Participants
			</button>
		</div>

	</form>

<!-- Row template. __K__ is replaced with the row index by RosterEntry.addRow() -->
	<template id='newParticipantRowTemplate'>
		<div class='new-participant-row'>

			<input type='hidden' class='system-roster-id' name='newParticipants[__K__][systemRosterID]' value='0'>
			<input type='hidden' class='school-id' name='newParticipants[__K__][schoolID]' value='0'>

		<!-- Name -->
			<div class='new-participant-cell name-cell'>
				<div class='name-inputs'>
					<?php if(NAME_MODE == 'firstName'): ?>
						<div class='autocomplete-wrap'>
							<input type='text' class='first-name-input' name='newParticipants[__K__][firstName]'
								placeholder='First Name' autocomplete='off'>
						</div>
						<div class='autocomplete-wrap'>
							<input type='text' class='last-name-input' name='newParticipants[__K__][lastName]'
								placeholder='Last Name' autocomplete='off'>
						</div>
					<?php else: ?>
						<div class='autocomplete-wrap'>
							<input type='text' class='last-name-input' name='newParticipants[__K__][lastName]'
								placeholder='Last Name' autocomplete='off'>
						</div>
						<div class='autocomplete-wrap'>
							<input type='text' class='first-name-input' name='newParticipants[__K__][firstName]'
								placeholder='First Name' autocomplete='off'>
						</div>
					<?php endif ?>
				</div>
			</div>

		<!-- School -->
			<div class='new-participant-cell school-cell'>
				<div class='autocomplete-wrap school-combobox'>
					<input type='text' class='school-input' placeholder='School' autocomplete='off'>
					<span class='school-toggle' tabindex='-1' aria-label='Show all schools'>&#9662;</span>
				</div>
			</div>

		<!-- Staffing -->
			<?php if($useStaff == true): ?>
				<div class='new-participant-cell staff-cell'>
					<select name='newParticipants[__K__][staffCompetency]'>
						<option value='0'>No</option>
						<?php for($staffComp=1;$staffComp<=STAFF_COMPETENCY_MAX;$staffComp++): ?>
							<option <?=optionValue($staffComp,$dStaffCompetency)?> > <?=$staffComp?> </option>
						<?php endfor ?>
					</select>
				</div>
			<?php endif?>

		<!-- Tournaments -->
			<div class='new-participant-cell tournaments-cell'>
				<?php foreach($tournamentGroups as $group):?>
					<div class='tournament-group'>
						<?php if($group['weapon'] != ''): ?>
							<span class='tournament-group-label'><?=htmlspecialchars($group['weapon'])?></span>
						<?php endif ?>
						<?php foreach($group['tournaments'] as $t):?>
							<div class='tournament-box <?=$autoCheck ? 'is-selected' : ''?>'
								tabindex='0' role='checkbox' aria-checked='<?=$autoCheck ? 'true' : 'false'?>'>
								<input type='checkbox' name='newParticipants[__K__][tournamentIDs][]'
									value='<?=$t['tournamentID']?>' class='hidden' <?=$autoCheck ? 'checked' : ''?>>
								<?php if($t['weapon'] != ''): ?><span class='chip-weapon'><?=htmlspecialchars($t['weapon'])?> - </span><?php endif ?><?=htmlspecialchars($t['label'])?>
							</div>
						<?php endforeach?>
					</div>
				<?php endforeach?>
			</div>

		<!-- Remove -->
			<div class='new-participant-cell remove-cell'>
				<button type='button' class='remove-row' title='Remove row' aria-label='Remove row'>&times;</button>
			</div>

		</div>
	</template>

	</div>

<?php }

/******************************************************************************/

function tableHeaders(){

	if($_SESSION['rosterViewMode'] == 'school'){
		$schoolArrow = "&#8595";
		$nameArrow = '';
	} else {
		$schoolArrow = "";
		$nameArrow = "&#8595";
	}
?>

	<thead >
	<tr>
		<?php if(ALLOW_EDITING == true):?>
			<th>
				<span class='hide-for-small-only'>Remove</span>
				<span class='show-for-small-only'>X</span>
			</th>
		<?php endif?>
		<th onclick="changeParticipantOrdering('rosterViewMode','name')" class='text-center'>
			<a>Name <?=$nameArrow?></a>
		</th>

		<th onclick="changeParticipantOrdering('rosterViewMode','school')"  class='text-center'>
			<a>School <?=$schoolArrow?></a>
		</th>
		<th class='text-center'>Tournaments</th>
	</tr>
	</thead>
<?php }

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
