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
// Safe inside <script>; substitutes rather than failing on non-UTF-8 names (tables are latin1).
define('JSON_FLAGS_INLINE', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
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
		editParticipant(null,$schoolList);
		addNewParticipantsButtons();
		if($_SESSION['addEventParticipantsMode'] == 'on'){
			addNewParticipantsCard($schoolList);
		}

		confirmDeleteReveal('eventRosterForm', 'deleteFromEvent', 'large');
	}

	eventRegistrationCountsDisplay();

// Display roster
	displayEventRoster($roster, $isTournamentScheduleUsed,
						$tournamentEntries, $tournamentNames,
						$scheduleByRosterID, $scheduleBlockNames);

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
							$staffingBlocks, $scheduleBlockNames){
// Displays table of fighters already registered, and which tournaments they are in
	?>

	<form method='POST' id='eventRosterForm'>
	<input type='hidden' id='eventID' name='eventID' value=<?=$_SESSION['eventID']?>>

	<table  class='hover'>

	<?php

	tableHeaders();

	foreach ((array)$roster as $person):

		$rosterID = $person['rosterID'];
		$fullName = getFighterName($rosterID);
		$field1 = "tList-{$rosterID}";
		$field2 = "tList2-{$rosterID}";
		?>

		<tr class='pointer' id='divFor<?=$rosterID?>'
			>

		<!-- Deletion checkboxes -->
			<?php if(ALLOW_EDITING == true): ?>
				<td>
					<a name="anchor<?=$rosterID?>"></a>
					<input type='checkbox' name='deleteFromEvent[<?=$rosterID?>]'
						id='<?=$rosterID?>' onchange="checkIfFought(this)">
					<span class='button tiny hollow' onclick="editParticipant(<?=$rosterID?>)">
						Edit
					</span>
				</td>
			<?php endif?>

		<!-- Participant info -->
			<td onClick="toggleTableRow('<?=$field1?>', '<?=$field2?>')">
				<?=getFighterName($rosterID, null, null, false, true)?>
			</td>

			<?php
				$schoolName = $person['schoolShortName'];
				if($person['schoolBranch'] != ''){
					$schoolName .= ", {$person['schoolBranch']}";
				}

			?>


			<td onClick="toggleTableRow('<?=$field1?>', '<?=$field2?>')">
				<?=$schoolName?>
			</td>

		</tr>
		<tr id='tList-<?=$rosterID?>' class='hidden'>

		<!-- Entries & assignements -->
			<td colspan='100%' >


			<!-- Tournament entries -->
				<?php if(isset($tournamentEntries[$rosterID]) == true): ?>
					Tournament Entries for
					<u><?=$fullName?></u>:

					<?php foreach((array)$tournamentEntries[$rosterID] as $tournamentID):
						$name = $tournamentNames[$tournamentID];
						?>
						<div class='shrink tournament-box'>
							<?=$name?>
						</div>
					<?php endforeach?>
				<?php else: ?>
					<u><?=$person['firstName']?> <?=$person['lastName']?></u>
					has no tournament entries
				<?php endif ?>


			<!-- Staffing assignments -->
				<?php if(ALLOW['VIEW_SCHEDULE'] == true): ?>

					<?php if(isset($staffingBlocks[$rosterID]) == true): ?>
						<BR><u><?=$fullName?></u> is also scheduled for:

						<?php foreach((array)$staffingBlocks[$rosterID] as $shift):
							$name = $scheduleBlockNames[$shift['blockID']];
							?>
							<div class='shrink tournament-box'>
								<?php if($shift['blockTypeID'] != SCHEDULE_BLOCK_MISC): ?>
									<u>Staffing</u>:
								<?php endif ?>
								<?=$name?>
							</div>
						<?php endforeach?>
					<?php endif ?>

					<?php if($isTournamentScheduleUsed == true || isset($staffingAssignments[$rosterID])): ?>
						<BR>
						<a onclick="goToPersonalSchedule('<?=$rosterID?>')">
							View Full Schedule for <?=$fullName?>
						</a>
					<?php endif ?>
				<?php endif ?>

			</td>
		</tr>
		<tr id='tList2-<?=$rosterID?>' class='hidden'>
			<td colspan='100%' ></td>
		</tr>

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

function addNewParticipantsCard($schoolList){
// Interface to add participants to the event.
// Rows are built client side from the <template> below (see RosterEntry in
// roster_management_scripts.js): the card opens with one row and a fresh one
// appears whenever the last row is focused. Each row posts the same newParticipants[k][...] fields the legacy
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

	$useStaff = logistics_isStaffAssignmentOnEventEntry($eventID);
	$dStaffCompetency = logistics_getDefaultStaffCompetency($eventID);

	?>

	<div class='callout secondary add-participants-card' id='addParticipantsCard'>

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
			<a id='addParticipantRow' class='button hollow'>+ Add Row</a>
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
				<?php foreach($tournamentNames as $tournamentID => $tName):?>
					<div class='tournament-box <?=$autoCheck ? 'is-selected' : ''?>'>
						<input type='checkbox' name='newParticipants[__K__][tournamentIDs][]'
							value='<?=$tournamentID?>' class='hidden' <?=$autoCheck ? 'checked' : ''?>>
						<?=$tName?>
					</div>
				<?php endforeach?>
			</div>

		<!-- Remove -->
			<div class='new-participant-cell remove-cell'>
				<span class='remove-row' title='Remove row' aria-label='Remove row'>&times;</span>
			</div>

		</div>
	</template>

	<script>
		// roster_management_scripts.js is loaded in the footer, so wait for it.
		window.addEventListener('DOMContentLoaded', function(){
			RosterEntry.init({ schools: <?=json_encode(rosterSchoolOptions($schoolList), JSON_FLAGS_INLINE)?> });
		});
	</script>

	</div>

<?php }

/******************************************************************************/

function rosterSchoolOptions($schoolList){
// Schools as {schoolID, label} for the school combobox. IDs 1 and 2 are the
// Unknown/Unaffiliated placeholders, labelled here as the old form did.

	$schools = [];
	foreach((array)$schoolList as $school){
		$label = $school['schoolShortName'];
		if($label == null){
			if($school['schoolID'] == 1){ $label = '*Unknown'; }
			elseif($school['schoolID'] == 2){ $label = '*Unaffiliated'; }
			else { continue; }
		} elseif($school['schoolBranch'] != ''){
			$label .= ", ".$school['schoolBranch'];
		}
		$schools[] = ['schoolID' => (int)$school['schoolID'], 'label' => $label];
	}
	return $schools;
}

/******************************************************************************/

function editParticipant($rosterID,$schoolList){
// Edit the information attributed with a participant
// Values to be filled in by Javascript

	if(ALLOW_EDITING == false){ return; }
	$tournamentIDs = getEventTournaments();
	?>

	<div class='reveal large' id='editParticipantModal' data-reveal>
		<h4 class='text-center'>Edit Participant Information</h4>
		<BR>
		<form method='POST' id='editParticipantForm'>

		<input type='hidden' name='formName' value='editEventParticipant'>
		<input type='hidden' name='editParticipantData[rosterID]' id='editRosterID'>

		<div class='grid-x  grid-margin-x'>
	<!-- Name -->
		<div class='input-group cell medium-6'>
			<span class='input-group-label hide-for-small-only'>Name</span>
			<input type='text' class='input-group-field' name='editParticipantData[firstName]' id='editFirstName'>
			<input type='text' class='input-group-field' name='editParticipantData[lastName]' id='editLastName'>
		</div>

	<!-- School -->
		<div class='input-group cell medium-6'>
			<span class='input-group-label hide-for-small-only'>School</span>
			<select class='input-group-field' name='editParticipantData[schoolID]' id='editSchoolID'>


			<?php foreach($schoolList as $school):?>
				<option value='<?=$school['schoolID']?>'>
				<?=$school['schoolShortName']?>, <?=$school['schoolBranch']?>
				</option>
			<?php endforeach?>

			</select>
		</div>



	<!-- Tournament entries -->
		<div class='medium-10 cell callout' id='editTournamentListDiv'>
			<?php foreach($tournamentIDs as $tournamentID):?>
				<?php $tName = getTournamentName($tournamentID);?>

				<div class='shrink tournamentSelectBox tournament-box'
					onclick="toggleCheckbox('editTournamentID<?=$tournamentID?>', this)"
					id='divForeditTournamentID<?=$tournamentID?>'>
					<input type='checkbox' name='editParticipantData[tournamentIDs][<?=$tournamentID?>]'
					id='editTournamentID<?=$tournamentID?>' class='hidden'>
					<?=$tName?>
				</div>
			<?php endforeach?>


			<div class='hidden' style='border: solid 1px; margin-top: 10px; padding: 8px;' id='confirmEditSubmit'>
			<p><span class='red-text'><u>Warning:</u> You are trying to remove a fighter from a tournament
			they have already started competing in.</span><BR>
			If they are injured or disqualified please use
			<strong><a href='adminFighters.php'>Manage Fighters > Withdraw Fighters</a></strong></p>
			<div class='text-right'>
				<button class='button alert hollow no-bottom' name='rosterID'
					value='<?=$rosterID?>'>
					I understand and still want to make the changes
				</button>
			</div>
			</div>

		</div>

	<!-- Sumbit options -->
		<div  class='medium-2 small-12 cell'>
			<button class='button success expanded' name='rosterID'
				value='<?=$rosterID?>' id='normalEditSubmit'>
				Update Fighter
			</button>

			<span class='button secondary expanded' onclick="editParticipant(0)">Cancel</span>
		</div>

		</div>
		</form>

<!-- Delete Participant Option -->
		<BR><a class='button alert no-bottom' data-open='confirmIndividualDelete'>
		Remove from event
		</a>

		<div class='reveal medium text-center' id='confirmIndividualDelete' data-reveal>


			<p>This will completely remove <BR><strong id='editFullName'></strong><BR> from the event.</p>
			<p>All information will be <u>permanently</u> erased.</p>


			<HR>
			<span id='warnIfFought'></span>
			<form method='POST' style='display:inline;'>
			<div class='grid-x grid-margin-x'>
				<input type='hidden' name='deleteFromEvent[]' value='true' id='rosterIDforDelete'>
				<button class='button alert small-6 cell no-bottom' name='formName'  value='deleteFromEvent'>
					Delete Participant
				</button>
				</form>

				<button class='button secondary small-6 cell no-bottom' data-close aria-label='Close modal' type='button'>
					Cancel
				</button>
			</div>

			<button class='close-button' data-close aria-label='Close modal' type='button'>
				<span aria-hidden='true'>&times;</span>
			</button>

		</div>



		<button class='close-button' onclick="editParticipant(0)">
			<span aria-hidden='true'>&times;</span>
		</button>

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
	</tr>
	</thead>
<?php }

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
