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
						'eventCountry',
						'eventStatus'];
	if(ALLOW['VIEW_EMAIL'] == true){
		$fieldsToDisplay[] = 'organizerEmail';
		$fieldsToDisplay[] = 'termsOfUseAccepted';
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
		<?php foreach($fieldsToDisplay as $fieldName): 
			if($fieldName == 'termsOfUseAccepted'){
				$fieldName = 'Terms';
			}
			?>

			<th><?=$fieldName?></th>
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
				$fieldValue = $info[$fieldName];

				if($fieldName == 'termsOfUseAccepted'){
					if($fieldValue == '0'){
						$fieldValue = "<strong class='red-text'>✗</strong>";
					} else {
						$fieldValue = "<span class='grey-text'>✓</span>";
					}
				}


				?>
				<td class='<?=$topBorder?>'><?=$fieldValue?></td>
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
	
	$num = 0;
	while($num < 100){
		$num = rand(0,999);		// random number acting as a delete confirmation
	}

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
		</table>
		
	<!-- Submit buttons -->
	
		<button class='button success' name='editEvent' value=1 >Update Event</button>
		<button class='button secondary' name='formName'>Cancel</button>
		
	<!-- Delete event & confirmation -->
		<?php if(ALLOW['SOFTWARE_ADMIN'] == true): ?>
			<BR><?=$num?>
			<input type='hidden' value='<?=$num?>' name='confirmDelete'>
			<button class='button alert hollow small' name='deleteEvent' value='Delete Event'>Delete Event</button>
			<input type='text' name='deleteCode' size='1'>
		<?php endif ?>
	</form>
	Organizer E-mail: <strong><u><?=$e_mail?></u></strong>
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
			<input class='no-bottom' type='text' 
				name='eventCountry' value="<?=$eventInfo['eventCountry']?>">
		</td>
	</tr>

<?php }



/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

