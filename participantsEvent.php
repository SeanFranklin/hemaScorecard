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
	
	$nameOrder = NAME_MODE;
	if($nameOrder == null){
		$nameOrder = 'firstName';
	}
	
	if($_SESSION['rosterViewMode'] == 'school'){
		$sortString = "(CASE WHEN schoolShortName='' then 1 ELSE 0 END), schoolShortName ASC, {$nameOrder} ASC";
	} else {
		$sortString = "{$nameOrder} ASC";
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
		if($_SESSION['addEventParticipantsMode'] == 'school'){
			addNewParticipantsBySchool($tournamentList,$schoolList);
		}
		
		confirmDeleteReveal('eventRosterForm', 'deleteFromEvent', 'large');
	/*		
	///// This breaks the javascript by having two buttons////
	<div style='display:inline'>
	<span id='deleteButtonContainer2'>
		<button class='button alert hollow' name='formName' value='deleteFromEvent' id='deleteButton2'>
			Delete Selected
		</button>
	</span>
	</div>*/
		
	}

// Display roster
	displayEventRoster($roster, $isTournamentScheduleUsed,
						$tournamentEntries, $tournamentNames,
						$scheduleByRosterID, $scheduleBlockNames);

}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

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
	$i = 0;
	
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
				<?=getFighterName($rosterID)?>
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
		
		<?php
		// Repeats the header row every 15 entries
		$i++;
		if($i >= 15){
			$i=0;
			tableHeaders();
		}?>

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
	if(ALLOW_EDITING == false){ return; }
	?>

	<div style='display:inline-block'>
	<form method='POST'><input type='hidden' name='formName' value='addEventParticipantsMode'>
	
		<?php if($_SESSION['addEventParticipantsMode'] != 'school'):?>
			<button class='button' name='newParticipantsMode' value='school'>
				Add Event Participants
			</button>
		<?php else:?>
			<button class='button' name='newParticipantsMode' value='off'>
				Done Adding Participants
			</button>
		<?php endif?>
		
		<a class='button hollow secondary' href='participantsSchools.php'>
			Add New Schools
		</a>
		<a class='button hollow secondary' href='participantsAdditional.php'>
			Non-Participating Entries
		</a>
		<BR>
	</form>
	</div>
	
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

function addNewParticipantsBySchool($tournamentList,$schoolList){
// Interface to add participants to the event

	if(ALLOW_EDITING == false){ return;}
	if(!isset($_SESSION['newParticipantsSchoolID'])){
		$_SESSION['newParticipantsSchoolID'] = '';
	}
	$schoolID = $_SESSION['newParticipantsSchoolID'];
	
	if($schoolID == 1){$s1 = 'selected';}
	if($schoolID == 2){$s2 = 'selected';}

	// If there is only one tournament make it selected by default
	if(count($tournamentList) == 1){
			$applyBackground = "style='background:#3adb76'";
			$applyChecked = "checked";
	} else {
		$applyBackground = null;
		$applyChecked = null;
	}

	$useStaff = logistics_isStaffAssignmentOnEventEntry($_SESSION['eventID']);
	$dStaffCompetency = logistics_getDefaultStaffCompetency($_SESSION['eventID']);

	?>
	
	
	<div class='align-middle callout secondary'  style='margin-bottom: 40px;'>
	
	<form method='POST'>
	<input type='hidden' name='formName' value='changeSchool'>

	
	<div class='grid-x'>
	<div class='input-group large-12'> 
		
	<!-- Select the school -->	
		<span class='input-group-label'>School:</span>
		<select class='input-group-field' name='schoolID'  onchange='this.form.submit()'>
	
		<?php if ($schoolID == null):?>
			<option></option>
		<?php endif?>
		
		<option <?=optionValue(1, $schoolID)?> >*Unknown</option>
		<option <?=optionValue(2, $schoolID)?> >*Unaffiliated</option>
		
		<?php foreach($schoolList as $school):
			if($school['schoolShortName'] == null || $school['schoolShortName'] == 'Unaffiliated'){continue;}
			?>
			
			<option <?=optionValue($school['schoolID'], $schoolID)?> >
				<?=$school['schoolShortName']?>, <?=$school['schoolBranch']?>
			</option>
		<?php endforeach?>
		
		</select>
	
	<!-- Add new participants button -->	
		<?php if($schoolID != null):?>
			<button class='button success input-group-button hide-for-small-only' 
				name='formName' value='addEventParticipants'>
				Add New Participants
			</button>
		<?php endif?>
		
		
	</div>
	<?php if($schoolID != null):?>	
		<button class='button success large expanded show-for-small-only' name='formName' value='addEventParticipants'>
			Add New Participants
		</button>
	<?php endif?>
	
	</div>

<!-- Information for new participants -->
	<?php if($schoolID != null):

		$schoolRoster = getSchoolRosterNotInEvent($schoolID, $_SESSION['eventID']);
		$tournamentNames = getTournamentsAlphabetical($_SESSION['eventID']);
		$numInSchool = count($schoolRoster);
		$numSpotsToDisplay = 6;
		$i=1; 	//counter to make each list item unique

		if($schoolID == 1 || $schoolID == 2){
			// Number of each type of field more spaces to add new fighters if Unaffiliated/Unknown
			$numID = 2;
			$numNew = 4;
		} else {
			$numID = min($numInSchool,4);
			$numNew = $numSpotsToDisplay - $numID;
		}
		
		?>
		
	<!-- Table headers -->
		<table class='stack'>

		<tr>
			<th>Name</th>
			<?php if($useStaff == true): ?>
				<th><?=tooltip("Assign as a staff member for this event. 
						The numbers are if you want to rate them based on their skillsets 
						to help assign table/ judge/ director/ etc...")?>
				</th>
			<?php endif ?>
			<th>Tournaments</th>
		</tr>
		
	<!-- Existing fighters from systemRoster -------------------------------------->
		<?php if($numID < 1): ?>
			<tr><td colspan='100%'>There are no more fighters from this club left in the database</td></tr>
		<?php endif ?>

		<?php for ($k = 1 ; $k <= $numID; $k++):?>
			<tr>
		<!-- Name -->
			<td>

				<select name='newParticipants[<?=$k?>][systemRosterID]'>
					<option></option>
				<?php foreach($schoolRoster as $fighter): 
				
				?>
					<option value='<?=$fighter['systemRosterID']?>'>
						<?=getFighterNameSystem($fighter['systemRosterID'])?>
					</option>";
				<?php endforeach?>
			
				</select>
				<input type='hidden' name='newParticipants[<?=$k?>][firstName]' value=''>
				<input type='hidden' name='newParticipants[<?=$k?>][lastName]' value=''>
				<input type='hidden' name='newParticipants[<?=$k?>][schoolID]' value='<?=$schoolID?>'>
			
			</td>

		<!-- Staffing -->
			<?php if($useStaff == true): ?>
				<td>
					<select name='newParticipants[<?=$k?>][staffCompetency]'>
						<option value='0'>No</option>
						<?php for($staffComp=1;$staffComp<=STAFF_COMPETENCY_MAX;$staffComp++): ?>
							<option <?=optionValue($staffComp,$dStaffCompetency)?> > <?=$staffComp?> </option>
						<?php endfor ?>
					</select>
				</td>
			<?php endif?>
			
			<td>
			
		<!-- Tournaments -->
			<?php foreach((array)$tournamentNames as $tournamentID => $tName):?>
				<div class='shrink tournament-box' onclick="toggleCheckbox('checkbox-<?=$i?>', this, 'skipCheck')"
					<?=$applyBackground?>>
				<input type='checkbox' name='newParticipants[<?=$k?>][tournamentIDs][<?=$i?>]' 
					value='<?=$tournamentID?>' id='checkbox-<?=$i?>' class='hidden' <?=$applyChecked?>>
				<?=$tName?>
				</div>
				<?php $i++;?>
			<?php endforeach?>
			</td>
			</tr>	
		<?php endfor?>


	<!-- New fighters -------------------------------------->
		<?php for ($k = ($numID + 1) ; $k <= ($numNew + $numID); $k++):
			if($k == ($numID + 1)){
				$style = "style='border-top:solid 1px;'";
			} else {
				$style = '';
			}?>
			
			<tr <?=$style?>>
		<!-- Name -->
			<td>
				<input type='hidden' name='newParticipants[<?=$k?>][systemRosterID]' value='0'>

				<div class='input-group no-margin' style='min-width:300px;'>
					<?php if(NAME_MODE == 'firstName'): ?>
					<input type='text' name='newParticipants[<?=$k?>][firstName]' 
						class='input-group-field no-margin' 
						placeholder='First Name'>
					<?php endif ?>
					<input type='text' name='newParticipants[<?=$k?>][lastName]' 
						class='input-group-field no-margin'
						placeholder='Last Name'>
					<?php if(NAME_MODE != 'firstName'): ?>
					<input type='text' name='newParticipants[<?=$k?>][firstName]' 
						class='input-group-field no-margin'
						placeholder='First Name'>
					<?php endif ?>
				</div>
				<input type='hidden' name='newParticipants[<?=$k?>][schoolID]' value='<?=$schoolID?>'>
			</td>

		<!-- Staffing -->
			<?php if($useStaff == true): ?>
				<td>
					<select name='newParticipants[<?=$k?>][staffCompetency]'>
						<option value='0'>No</option>
						<?php for($staffComp=1;$staffComp<=STAFF_COMPETENCY_MAX;$staffComp++): ?>
							<option <?=optionValue($staffComp,$dStaffCompetency)?> > <?=$staffComp?> </option>
						<?php endfor ?>
					</select>
				</td>
			<?php endif?>
			
		<!-- Tournaments -->	
			<td>
			<?php foreach((array)$tournamentNames as $tournamentID => $tName):?>
				<div class='shrink tournament-box' onclick="toggleCheckbox('checkbox-<?=$i?>', this, 'skipCheck')"
					<?=$applyBackground?>>
				<input type='checkbox' name='newParticipants[<?=$k?>][tournamentIDs][<?=$i?>]' 
				value='<?=$tournamentID?>' id='checkbox-<?=$i?>' class='hidden' <?=$applyChecked?>>
					<?=$tName?>
				</div>
				<?php $i++;?>
			<?php endforeach?>
	
			</td>
			</tr>
		<?php endfor?>
		
		</table>
	<?php endif?>
	
	</form>
	</div>
	
<?php }

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
