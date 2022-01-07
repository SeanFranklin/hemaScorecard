<?php 
/*******************************************************************************
	Manage System Events
	
	Administrator page to add/edit/remove events
	LOGIN
		- SUPER ADMIN can access, no others can
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Manage System Events";
$forcePageTitle = true;
include('includes/header.php');

if(ALLOW['SOFTWARE_ASSIST'] == false){
	pageError('user');
} else {

	$eventList = getEventListFull();

	echo "<div class='grid-x'>";



	if(isset($_SESSION['editEventID'])){
		$eventID = $_SESSION['editEventID'];
		unset($_SESSION['editEventID']);

		editEventMenu($eventID,$eventList[$eventID]);
	} else {
		addNewEventMenu();
	}

?>

	<button class='button hollow secondary' onclick="$('.archived-event').toggle()">
		Toggle Archived Events
	</button>

<?php

	displayAdminEventList($eventList);

	echo "</div>";

		
}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

function displayAdminEventList($eventList){

	// These are the fields displayed
	$fieldsToDisplay = ['eventName',
						'eventStartDate',
						'countryName',
						'isArchived'];
	if(ALLOW['VIEW_EMAIL'] == true){
		$fieldsToDisplay[] = 'organizerEmail';
		$fieldsToDisplay[] = 'Setup';

		foreach($eventList as $eventID => $data){

			$isTournaments = true;
			$isParticipants = true;
			if($eventList[$eventID]['isArchived'] == 0){
				$eventID = (int)$eventID;
				$sql = "SELECT COUNT(*) as numTournaments
						FROM eventTournaments
						WHERE eventID = {$eventID}";
				$isTournaments = (bool)mysqlQuery($sql, SINGLE, 'numTournaments');

				$sql = "SELECT COUNT(*) as numParticipants
						FROM eventRoster
						WHERE eventID = {$eventID}";
				$isParticipants = (bool)mysqlQuery($sql, SINGLE, 'numParticipants');

				$str = '';
				$str = notSetMark($eventList[$eventID]['termsOfUseAccepted'])." ";
				$str .= notSetMark($isTournaments)." ";
				$str .= notSetMark($isParticipants)." ";
				$str .= "<span class='black-text'>|</span>";
				$str .= isSetMark(areMatchesStarted($eventID));
				$str .= isSetMark(areAllTournamentsFinalized($eventID));
				$str .= isSetMark(hemaRatings_isEventInfoComplete($eventID));


				$eventList[$eventID]['Setup'] = $str;

			} else {

				$eventList[$eventID]['Setup'] = '';

			}


		}

	}
	$archivedReached = false;
										
	?>
	
	<div class='cell'>

	<form method='POST'>
	<input type='hidden' name='formName' value='editEvent'>
	<table class='stack'>

	
<!-- Headers -->
	<tr class='hide-for-small-only'>
		<th></th>
		<?php foreach($fieldsToDisplay as $fieldName): ?>
			<th>
				<?=$fieldName?>	
				<?php 
					if($fieldName == 'Setup'):
						tooltip("1) Terms of Use<BR>2) Tournaments Created<BR>3) People Added<BR>
							----------
							<BR>4) Matches Fought<BR>5) Tournaments Finalized<BR>6) HEMA Ratings Info");
					endif 
				?>
			</th>
		<?php endforeach ?>
	</tr>
	
	

<!-- Events -->
	<?php foreach($eventList as $eventID => $info): 
		$topBorder = '';

		if($info['isArchived'] == 1){
			$class = 'success-text hidden archived-event';
			if($archivedReached == false){
				$topBorder = ' table-top-border';
			}
			$archivedReached = true;
		} elseif(isEventPublished($eventID) == true){
			if($archivedReached == true){
				$class = 'alert-text';
			} else {
				$class = 'warning-text';
			}
		} else {
			$class = '';
		}

		// Add a marking to indicate if something is a meta-event
		if($info['isMetaEvent'] == 1){
			$info['eventName'] = '<strong>[M]</strong> '.$info['eventName'];
		}

		?>
		<tr class='<?=$class?>'>
			<td>
				<?php if(ALLOW['SOFTWARE_ADMIN'] == true || $info['isArchived'] == false): ?>
					<button class='button tiny hollow no-bottom expanded' 
							name='eventInfo[eventToEdit]' 
							value='<?=$eventID?>'>

						Edit #<?=$eventID?>

					</button>
				<?php endif ?>
			</td>
			
			<?php foreach($fieldsToDisplay as $fieldName): 

				if($fieldName == 'Setup'){
					$noWrap = "style='white-space: nowrap;'";
				} else {
					$noWrap = '';
				}

				?>

					<td class='<?=$topBorder?>' <?=$noWrap?> >
						<?=$info[$fieldName]?>
					</td>
			<?php endforeach ?>
		</tr>
	<?php endforeach ?>


	</table>
	</form>
	</div>
	
<?php }

/******************************************************************************/

function addNewEventMenu(){
	?>
	
	<a class='button hollow' id="createNewEventToggleButton">
	 	Create New Event
	</a>
	<fieldset class='fieldset cell large-6 hidden' id='createNewEventField'>
	<legend><h4>Add New Event</h4></legend>
	<form method='POST'>
		<input type='hidden' name='formName' value='addNewEvent'>
		<table class='stack'>
			<?php entryFields(); ?>
			<tr>
				<td>
					Meta Event
				</td>
				<td>
					<input type='hidden' name='eventInfo[isMetaEvent]' value='0'>
					<input class='switch-input' type='checkbox' id='isMetaEvent' 
						name='eventInfo[isMetaEvent]' value='1' >
					<label class='switch-paddle' for='eventInfo[isMetaEvent]'>
					</label>
				</td>
			</tr>

		</table>
		
		<button class='button success'>Add Event</button>
	
	</form>
	</fieldset>
	
<?php }

/******************************************************************************/

function editEventMenu($eventID,$eventInfo){
	
	if(isEventArchived($eventID) == true){
		$isArchived = 'selected';
	} else {
		$isArchived = '';
	}
	$e_mail = getEventEmail($eventID);

	// Software assistants can't edit archived events
	if(ALLOW['SOFTWARE_ASSIST'] == false
		|| (ALLOW['SOFTWARE_ADMIN'] == false && (bool)$isArchived != false)){
		return;
	}
	?>
	
	<fieldset class='fieldset cell large-6'>
	<legend><h4>Edit Event</h4></legend>

	<form method='POST'>

		<input type='hidden' name='eventInfo[eventID]' value='<?=$eventID?>'>
		<input type='hidden' name='formName' value='editEvent'>
		<input type='hidden' name='eventInfo[changeEventTo]' value='<?=$eventID?>'>

	<!-- Data fields -->
		<table>
			<?php entryFields($eventInfo); ?>
			
			<!-- Event status -->
			<tr>
				<td>Archived:</td>
				<td>
					<select name='eventInfo[isArchived]'>
						<option value=0></option>
						<option value=1 <?=$isArchived?>>Yes</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class='no-wrap'>
					Organizer E-mail
				</td>
				<td>
					<u><?=$e_mail?></u>
				</td>
			</tr>
		</table>

		<?php if($eventInfo['isMetaEvent']==1): ?>
			<div class='text-center callout warning'>
				<h6>Meta-Event</h6>
			</div>
		<?php endif ?>

	<!-- Submit buttons -->
		<button class='button success no-bottom' name='eventInfo[editEvent]' value=1 >Update Event</button>
		<button class='button secondary no-bottom' name='formName'>Cancel</button>
		<button class='button no-bottom' name='formName' value='selectEvent'>Go To Event</button>

	<!-- Delete event & confirmation -->
		<?php if(ALLOW['SOFTWARE_ADMIN'] == true): ?>
			<HR><em>Type <strong>delete-[event name] [year]</strong> to delete.</em>
			<div class='input-group'>
				<input class='input-group-field no-bottom' type='text' name='deleteConfirmationCode' size='1'>
				<button class='button alert hollow small input-group-button no-bottom' 
					name='deleteEvent' value='Delete Event'>Delete Event</button>
			</div>
		<?php endif ?>
	</form>
	
	</fieldset>

<?php }

/******************************************************************************/

function entryFields($eventInfo = null){
	?>
	
	<tr>
		<td>Event Name</td>
		<td>
			<input class='no-bottom' type='text' required
				name='eventInfo[eventName]' value="<?=$eventInfo['eventName']?>">
		</td>
	</tr>
	<tr>
		<td>Abbreviation</td>
		<td>
			<input class='no-bottom' type='text' 
				name='eventInfo[eventAbbreviation]' value="<?=$eventInfo['eventAbbreviation']?>">
		</td>
	</tr>
	<tr>
		<td>Start Date</td>
		<td>
			<input class='no-bottom' type='date' required
				name='eventInfo[eventStartDate]' value="<?=$eventInfo['eventStartDate']?>">
		</td>
	</tr>
	<tr>
		<td>End Date</td>
		<td>
			<input class='no-bottom' type='date'
				name='eventInfo[eventEndDate]' value="<?=$eventInfo['eventEndDate']?>">
		</td>
	</tr>
	<tr>
		<td>City</td>
		<td>
			<input class='no-bottom' type='text'
				name='eventInfo[eventCity]' value="<?=$eventInfo['eventCity']?>">
		</td>
	</tr>
	<tr>
		<td>Province/State</td>
		<td>
			<input class='no-bottom' type='text' 
				name='eventInfo[eventProvince]' value="<?=$eventInfo['eventProvince']?>">
		</td>
	</tr>
	
	<tr>
		<td>Country</td>
		<td>
			<?=selectCountry("eventInfo[countryIso2]", $eventInfo['countryIso2'], null, 'no-bottom');?>
		</td>
	</tr>

<?php }



/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

