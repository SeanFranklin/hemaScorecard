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

$tournamentList = getEventTournaments();
$eventID = $_SESSION['eventID'];

if($eventID == null){
	pageError('event');;
} else {
	
// Get information
	foreach((array)$tournamentList as $tournamentID){
		$tournamentRosters[$tournamentID] = getTournamentRoster($tournamentID,'rosterID');
	}
	
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

// For entering new participants
	if(USER_TYPE >= USER_ADMIN){
		displayEntryConflicts();
		editParticipant($roster,$schoolList);
		addNewParticipantsButtons();
		if($_SESSION['addEventParticipantsMode'] == 'school'){
			addNewParticipantsBySchool($tournamentList,$schoolList);
		}
	}

// Display roster
	toggleFighterListSort();
	displayEventRoster($roster, $tournamentRosters, $tournamentList);

}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayEventRoster($roster, $tournamentRosters, $tournamentList){
// Displays table of fighters already registered, and which tournaments they are in
	?>
	
	<form method='POST' id='eventRosterForm'>
	<input type='hidden' id='eventID' name='eventID' value=<?=$_SESSION['eventID']?>>
	
	<table  class='hover'>
	
	<?php 	
	
	tableHeaders($tournamentList);
	$i = 0;
	
	foreach ((array)$roster as $person):
		unset($tournamentNames);
		$rosterID = $person['rosterID']; 
		$field1 = "tList-{$rosterID}";
		$field2 = "tList2-{$rosterID}";
		unset($tournamentNames);
		foreach((array)$tournamentRosters as $tournamentID => $tournamentRoster){
			
			if($tournamentRoster[$rosterID] != null){
				$tournamentNames[] = getTournamentName($tournamentID);
			}
		}		
		?>
		
		<tr class='pointer' id='divFor<?=$rosterID?>'
			>
		
		<!-- Deletion checkboxes -->
			<?php if(USER_TYPE >= USER_ADMIN): ?>
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
		
			
			<td onClick="toggleTableRow('<?=$field1?>', '<?=$field2?>')">
				<?=$person['schoolShortName']?>, <?=$person['schoolBranch']?>
			</td>

		</tr>
		<tr id='tList-<?=$rosterID?>' class='hidden'>
		
		<!-- Tournament Entries -->	
			<td colspan='100%' >
				Tournament Entries for 
				<u><?=$person['firstName']?> <?=$person['lastName']?></u>:
				<?php foreach((array)$tournamentNames as $name):?>
					<div class='shrink tournament-box'>
						<?=$name?>
					</div>
				<?php endforeach?>
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
			tableHeaders($tournamentList);
		}?>

	<?php endforeach?>
	
	</table>

	<?php if(USER_TYPE >= USER_ADMIN): ?>
		<?php confirmDeleteReveal('eventRosterForm', 'deleteFromEvent', 'large'); ?>
		<span id='deleteButtonContainer'>
			<button class='button alert hollow' name='formName' value='deleteFromEvent' id='deleteButton'>
				Delete Selected
			</button>
		</span>
	<?php endif?>
	
<?php }

/******************************************************************************/

function addNewParticipantsButtons(){
	if($_SESSION['userType'] < USER_ADMIN){ return; }
	?>

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
		<BR>
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
	<?php foreach((array)$_SESSION['rosterEntryConflicts']['alreadyEntered'] as $systemRosterID): 
		$name = getFighterNameSystem($systemRosterID);?>
		<b><?=$name?></b> is already entered in this event. 
		<BR><BR>
	<?php endforeach?>
	
		
	<form method='POST'>
	
	<?php // Fighters which already exists in the system
	 
	$k = 99; // An arbitrarialy large index for the fighters to be added. Has to be above the maximum that can be added at a time normally
	foreach((array)$_SESSION['rosterEntryConflicts']['alreadyExists'] as $fighterData):
		
		$systemRosterID = $fighterData['systemRosterID'];
		$name = getFighterNameSystem($systemRosterID);
		$systemRosterID = $fighterData['systemRosterID'];
		$systemSchoolID = $fighterData['systemSchoolID'];
		$enteredSchoolID = $fighterData['enteredSchoolID'];
		$systemName = getSchoolName($systemSchoolID, 'long', 'branch');
		$enteredName = getSchoolName($enteredSchoolID, 'long', 'branch');
		?>
				
		<b><?=$name?></b> already exists in the system, from <u><?=$systemName?></u><BR>
		
		<?php //The school they are registered with in the system?>
		<input type='hidden' name='newParticipants[byID][<?=$k?>][systemRosterID]' value='<?=$systemRosterID?>'>
		<input type='radio' checked name='newParticipants[byID][<?=$k?>][schoolID]' value='<?=$systemSchoolID?>'>
		Enter From <i><?=$systemName?></i><BR>
		
		<?php //The school the user tried to enter them with?>		
		<input type='radio' name='newParticipants[byID][<?=$k?>][schoolID]' value='<?=$enteredSchoolID?>'>
		<input type='hidden' name='newParticipants[byID][<?=$k?>][changeSchoolID]' value='<?=$enteredSchoolID?>'>
		Enter From <i><?=$enteredName?></i><BR>
		
		<input type='radio' name='newParticipants[byID][<?=$k?>][schoolID]' value=''>
		Don't Enter
				
		<?php foreach((array)$fighterData['tournamentIDs'] as $tournamentID):?>
			<input type='hidden' name='newParticipants[byID][<?=$k ?>][tournamentIDs][]' value=<?=$tournamentID?>>
		<?php endforeach?>
		<HR>
		<?php $k++;?>
	<?php endforeach?>

	<button class='button hollow text-center' name='formName' value='addEventParticipants'>
		Confirm
	</button>
	
	</form>
	
	</div>
	
	<?php unset($_SESSION['rosterEntryConflicts'])?>
	
<?php }

/******************************************************************************/

function addNewParticipantsBySchool($tournamentList,$schoolList){
// Interface to add participants to the event

	if(USER_TYPE < USER_ADMIN){ return;}
	$schoolID = $_SESSION['newParticipantsSchoolID'];
	
	if($schoolID == 1){$s1 = 'selected';}
	if($schoolID == 2){$s2 = 'selected';}
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
		
		<option value='1' <?=$s1?>>*Unknown</option>
		<option value='2' <?=$s2?>>*Unafiliated</option>
		
		<?php foreach($schoolList as $school):
			if($school['schoolShortName'] == null || $school['schoolShortName'] == 'Unafiliated'){continue;}
			if($school['schoolID'] == $schoolID){
				$s = " selected";
			} else {
				unset($s);
			}?>
			
			<option value='<?=$school['schoolID']?>' <?=$s?>>
				<?=$school['schoolShortName']?>, <?=$school['schoolBranch']?>
			</option>
		<?php endforeach?>
		
		</select>
	
	<!-- Add new participants button -->	
		<?php if($_SESSION['newParticipantsSchoolID'] != null):?>
			<button class='button success input-group-button hide-for-small-only' name='formName' value='addEventParticipants'>
				Add New Participants
			</button>
		<?php endif?>
		
		
	</div>
	<?php if($_SESSION['newParticipantsSchoolID'] != null):?>	
		<button class='button success large expanded show-for-small-only' name='formName' value='addEventParticipants'>
			Add New Participants
		</button>
	<?php endif?>
	
	</div>

<!-- Information for new participants -->
	<?php if($_SESSION['newParticipantsSchoolID'] != null):

		$schoolRoster = getSchoolRosterNotInEvent($schoolID);
		$tournamentNames = getTournamentsAlphabetical();
		$i=1; 	//counter to make each list item unique
		
		if($schoolID == 1 || $schoolID == 2){
			// Number of each type of field more spaces to add new fighters if Unafiliated/Unknown
			$numID = 2;
			$numNew = 4;
		} else {
			$numID = 4;
			$numNew = 2;
		}
		
		?>
		
	<!-- Table headers -->
		<table class='stack'>
		<tr>
			<th>Name</th>
			<th>Tournaments</th>
		</tr>
		
	<!-- Existing fighters from systemRoster -->
		<?php for ($k = 1 ; $k <= $numID; $k++):?>
			<tr>
		<!-- Name -->
			<td>
				<select name='newParticipants[byID][<?=$k?>][systemRosterID]'>
					<option></option>
				<?php foreach($schoolRoster as $fighter): 
				
				?>
					<option value='<?=$fighter['systemRosterID']?>'>
						<?=getFighterNameSystem($fighter['systemRosterID'])?>
					</option>";
				<?php endforeach?>
			
				</select>
				<input type='hidden' name='newParticipants[byID][<?=$k?>][schoolID]' value='<?=$schoolID?>'>
			
			</td>
			
			<td>
			
		<!-- Tournaments -->
			<?php foreach((array)$tournamentNames as $tournamentID => $tName):?>
				<div class='shrink tournament-box' onclick="toggleCheckbox('checkbox-<?=$i?>', this)">
				<input type='checkbox' name='newParticipants[byID][<?=$k?>][tournamentIDs][<?=$i?>]' 
					value='<?=$tournamentID?>' id='checkbox-<?=$i?>' class='hidden'>
				<?=$tName?>
				</div>
				<?php $i++;?>
			<?php endforeach?>
			</td>
			</tr>	
		<?php endfor?>


	<!-- New fighters -->
		<?php for ($k = 1 ; $k <= $numNew; $k++):
			if($k == 1){
				$style = "style='border-top:solid 1px;'";
			} else {
				unset($style);
			}?>
			
			<tr <?=$style?>>
		<!-- Name -->
			<td>
				<div class='input-group no-margin' style='min-width:300px;'>
				<?php if(NAME_MODE == 'firstName'): ?>
				<input type='text' name='newParticipants[new][<?=$k?>][firstName]' 
					class='input-group-field no-margin' 
					placeholder='First Name'>
				<?php endif ?>
				<input type='text' name='newParticipants[new][<?=$k?>][lastName]' 
					class='input-group-field no-margin'
					placeholder='Last Name'>
				<?php if(NAME_MODE != 'firstName'): ?>
				<input type='text' name='newParticipants[new][<?=$k?>][firstName]' 
					class='input-group-field no-margin'
					placeholder='First Name'>
				<?php endif ?>
				</div>
				<input type='hidden' name='newParticipants[new][<?=$k?>][schoolID]' value='<?=$schoolID?>'>
			</td>
			
		<!-- Tournaments -->	
			<td>
			<?php foreach((array)$tournamentNames as $tournamentID => $tName):?>
				<div class='shrink tournament-box' onclick="toggleCheckbox('checkbox-<?=$i?>', this)">
				<input type='checkbox' name='newParticipants[new][<?=$k?>][tournamentIDs][<?=$i?>]' 
				value='<?=$tournamentID?>' id='checkbox-<?=$i?>' class='hidden'>
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

	if(USER_TYPE < USER_ADMIN){ return; }
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
			<p>All information will be <u>perminantely</u> erased.</p>
			
			
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

function tableHeaders($tournamentList, $removeDisabled = false){
?>
	
	<thead >
	<tr>
		<?php if(USER_TYPE >= USER_ADMIN && !$removeDisabled):?>
			<th>
				<span class='hide-for-small-only'>Remove</span>
				<span class='show-for-small-only'>X</span>
			</th>
		<?php endif?>
		<th onclick="changeRosterOrderType('name')" class='text-center'>
			<a>Name</a>
		</th>	

		<th onclick="changeRosterOrderType('school')"  class='text-center'>
			<a>School</a>
		</th>
	</tr>
	</thead>
<?php }

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
