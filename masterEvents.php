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
						'eventStatus'];
	if(ALLOW['VIEW_EMAIL'] == true){
		$fieldsToDisplay[] = 'organizerEmail';
		$fieldsToDisplay[] = 'Setup';

		foreach($eventList as $eventID => $data){

			$isTournaments = true;
			$isParticipants = true;
			if($eventList[$eventID]['eventStatus'] != 'archived'){
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
							<BR>4) Tournaments Finalized<BR>5) HEMA Ratings Info");
					endif 
				?>
			</th>
		<?php endforeach ?>
	</tr>
	
	

<!-- Events -->
	<?php foreach($eventList as $eventID => $info): 
		$topBorder = '';

		switch($info['eventStatus']){
			case 'active':
			case 'upcoming':
				if($archivedReached == true){
					$class = 'alert-text';
				} else {
					$class = 'warning-text';
				}
				break;
			case 'archived':
				$class = 'success-text hidden archived-event';
				if($archivedReached == false){
					$topBorder = ' table-top-border';
				}
				$archivedReached = true;
				break;
			case 'hidden':
			default:
				$class = '';
				break;

		}

		// Add a marking to indicate if something is a meta-event
		if($info['isMetaEvent'] == 1){
			$info['eventName'] = '<strong>[M]</strong> '.$info['eventName'];
		}

		?>
		<tr class='<?=$class?>'>
			<td>
				<?php if(ALLOW['SOFTWARE_ADMIN'] == true || $info['eventStatus'] != 'archived'): ?>
					<button class='button tiny hollow no-bottom expanded' 
							name='eventToEdit' 
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
					<input type='hidden' name='isMetaEvent' value='0'>
					<input class='switch-input' type='checkbox' id='isMetaEvent' 
						name='isMetaEvent' value='1' >
					<label class='switch-paddle' for='isMetaEvent'>
					</label>
				</td>
			</tr>



			<tr>
				<td>Event Status:</td>
				<td>
					<select name='eventStatus'>
						<option value='active'>Active</option>
						<option value='upcoming'>Upcoming</option>
						<option value='hidden' selected>Hidden</option>
						<option value='default'>Default</option>
						<option value='archived'>Archived</option>
					</select>
				</td>
			</tr>
		</table>
		
		<button class='button success'>Add Event</button>
	
	</form>
	</fieldset>
	
<?php }

/******************************************************************************/

function editEventMenu($eventID,$eventInfo){
	
	$eventStatus = getEventStatus($eventID);
	$statusType = array('active','upcoming','hidden','default','archived');
	$e_mail = getEventEmail($eventID);

	// Software assistants can't edit archived events
	if(ALLOW['SOFTWARE_ASSIST'] == false
		|| (ALLOW['SOFTWARE_ADMIN'] == false && $eventStatus == 'archived')){
		return;
	}
	?>
	
	<fieldset class='fieldset cell large-6'>
	<legend><h4>Edit Event</h4></legend>
	<form method='POST'>
		<input type='hidden' name='eventID' value='<?=$eventID?>'>
		<input type='hidden' name='formName' value='editEvent'>
		
	<!-- Data fields -->
		<table>
			<?php entryFields($eventInfo); ?>
			
			<!-- Event status -->
			<tr>
				<td>Event Status:</td>
				<td>
					<select name='eventStatus'>
						<?php foreach($statusType as $type):
							$selected = isSelected($type == $eventStatus);
							?>
							<option value='<?=$type?>' <?=$selected?>>
								<?=$type?>
							</option>
						<?php endforeach ?>
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
		<button class='button success no-bottom' name='editEvent' value=1 >Update Event</button>
		<button class='button secondary no-bottom' name='formName'>Cancel</button>

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
				name='eventName' value="<?=$eventInfo['eventName']?>">
		</td>
	</tr>
	<tr>
		<td>Abbreviation</td>
		<td>
			<input class='no-bottom' type='text' 
				name='eventAbbreviation' value="<?=$eventInfo['eventAbbreviation']?>">
		</td>
	</tr>
	<tr>
		<td>Start Date</td>
		<td>
			<input class='no-bottom' type='date' required
				name='eventStartDate' value="<?=$eventInfo['eventStartDate']?>">
		</td>
	</tr>
	<tr>
		<td>End Date</td>
		<td>
			<input class='no-bottom' type='date'
				name='eventEndDate' value="<?=$eventInfo['eventEndDate']?>">
		</td>
	</tr>
	<tr>
		<td>City</td>
		<td>
			<input class='no-bottom' type='text'
				name='eventCity' value="<?=$eventInfo['eventCity']?>">
		</td>
	</tr>
	<tr>
		<td>Province/State</td>
		<td>
			<input class='no-bottom' type='text' 
				name='eventProvince' value="<?=$eventInfo['eventProvince']?>">
		</td>
	</tr>
	
	<tr>
		<td>Country</td>
		<td>
			<?=selectCountry("countryIso2", $eventInfo['countryIso2'], null, 'no-bottom');?>
		</td>
	</tr>

<?php }



/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

