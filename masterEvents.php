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

if(USER_TYPE < USER_SUPER_ADMIN){
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

	displayAdminEventList($eventList);

	echo "</div>";

	include('includes/footer.php');
}


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

function displayAdminEventList($eventList){

	// These are the fields displayed
	$fieldsToDisplay = ['eventID',
						'eventName',
						'eventStartDate',
						'eventCity',
						'eventProvince',
						'eventCountry',
						'eventStatus'];
										
	?>
	
	<div class='cell'>

	<form method='POST'>
	<input type='hidden' name='formName' value='editEvent'>
	<table class='stack'>

	
<!-- Headers -->
	<tr class='hide-for-small-only'>
		<?php foreach($fieldsToDisplay as $fieldName): ?>
			<th><?=$fieldName?></th>
		<?php endforeach ?>
	</tr>
	
	

<!-- Events -->
	<?php foreach($eventList as $eventID => $info): ?>
		<tr>
			<td>
				<button class='button tiny hollow no-bottom expanded' name='eventToEdit' value='<?=$eventID?>'>
					Edit #<?=$eventID?>
				</button>
			</td>
			
			<?php foreach($fieldsToDisplay as $fieldName):
				if($fieldName == 'eventID'){continue;}
				$fieldValue = $info[$fieldName];
				?>
				<td><?=$fieldValue?></td>
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
	
	<fieldset class='fieldset cell large-6'>
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
						<option value='hidden'>Hidden</option>
						<option value='default'>Default</option>
						<option value='archived'>Archived</option>
					</select>
				</td>
			</tr>
		</table>
		
		<button class='button'>Add Event</button>
	
	</form>
	</fieldset>
	
<?php }

/******************************************************************************/

function editEventMenu($eventID,$eventInfo){
	
	$num = rand(0,999);		// random number acting as a delete confirmation
	$eventStatus = getEventStatus($eventID);
	$statusType = array('active','upcoming','hidden','default','archived');
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
		<BR><?=$num?>
		<input type='hidden' value='<?=$num?>' name='confirmDelete'>
		<button class='button alert hollow small' name='deleteEvent' value='Delete Event'>Delete Event</button>
		<input type='text' name='deleteCode' size='1'>
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
				name='eventName' value='<?=$eventInfo['eventName']?>'>
		</td>
	</tr>
	<tr>
		<td>Abreviation</td>
		<td>
			<input class='no-bottom' type='text' 
				name='eventAbreviation' value='<?=$eventInfo['eventAbreviation']?>'>
		</td>
	</tr>
	<tr>
		<td>Start Date</td>
		<td>
			<input class='no-bottom' type='date' required
				name='eventStartDate' value='<?=$eventInfo['eventStartDate']?>'>
		</td>
	</tr>
	<tr>
		<td>End Date</td>
		<td>
			<input class='no-bottom' type='date'
				name='eventEndDate' value='<?=$eventInfo['eventEndDate']?>'>
		</td>
	</tr>		
	<tr>
		<td>Country</td>
		<td>
			<input class='no-bottom' type='text' 
				name='eventCountry' value='<?=$eventInfo['eventCountry']?>'>
		</td>
	</tr>
	<tr>
		<td>Province/State</td>
		<td>
			<input class='no-bottom' type='text' 
				name='eventProvince' value='<?=$eventInfo['eventProvince']?>'>
		</td>
	</tr>
	<tr>
		<td>City</td>
		<td>
			<input class='no-bottom' type='text' 
				name='eventCity' value='<?=$eventInfo['eventCity']?>'>
		</td>
	</tr>

<?php }



/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

