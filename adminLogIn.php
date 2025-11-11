<?php
/*******************************************************************************
	Log In Page

	Log in to events or as a specialty user
	LOGIN: N/A

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Log In';
$jsIncludes[] = 'misc_scripts.js';
include('includes/header.php');


$openEvents = getEventList('open',0,0,"eventName ASC");
$metaEvents = getEventList('open',0,1,"eventName ASC");

if(isset($_SESSION['failedLogIn'])){
	$defaultEventID = $_SESSION['failedLogIn']['eventID'];
	$typeSelect = $_SESSION['failedLogIn']['type'];
	unset($_SESSION['failedLogIn']);
} else {
	$typeSelect = '';
	$defaultEventID = $_SESSION['eventID'];
}

if($typeSelect == null){
	$eventListVisibility = 'hidden';
	$userNameVisibility = 'hidden';
} elseif ($typeSelect == 'logInStaff' || $typeSelect == 'logInOrganizer') {
	$eventListVisibility = '';
	$userNameVisibility = 'hidden';
} else {
	$typeSelect = 'logInUser';
	$eventListVisibility = 'hidden';
	$userNameVisibility = '';
}


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<div class='grid-x grid-margin-x'>
		<div id='login-form' class='small-12 medium-6 large-4 cell'>
		<form action='adminLogIn.php' method='POST'>

		<!-- User type -->
			<label>
				<span>User Type</span>
				<select id='logInType' name='logInData[type]' autocomplete='username'
					onchange="logInTypeToggle(this)" required>
					<option selected disabled></option>
					<option <?=optionValue('logInStaff', $typeSelect)?>>
						Event Staff
					</option>
					<option <?=optionValue('logInOrganizer', $typeSelect)?>>
						Event Organizer
					</option>
					<option <?=optionValue('logInUser', $typeSelect)?>>
						Software User
					</option>
				</select>
			</label>


		<!-- Event list -->
			<label id='logInEventListDiv' class='<?=$eventListVisibility?>'>
				<span>Event</span>
				<select id='logInEventID' name='logInData[eventID]'  onchange="logInEventToggle('logInEventID')">
					<option selected disabled>- Open ------------------------</option>
					<?php populateEventSelectFields($openEvents, $defaultEventID); ?>
					<option disabled>- Leagues -----------------------</option>
					<?php populateEventSelectFields($metaEvents, $defaultEventID); ?>
				</select>
			</label>


		<!-- User Name -->
			<label id='logInUserNameDiv' class='<?=$userNameVisibility?>'>
				<span>Username</span>
				<input id='logInUserName' type='text' name='logInData[userName]'>
			</label>


		<!-- Password -->
			<label>
				<span>Password</span>
			<input type='password' name='logInData[password]'>
			</label>
			<button id='logInSubmitButton' class='button large small-12 cell'
					name='formName' value='logUserIn'>
				<strong>Log In</strong>
			</button>
		</form>
		</div>
	</div>


<?php
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function populateEventSelectFields($eventList, $defaultEventID){

	if($eventList == null){
		return;
	}

	foreach($eventList as $eventID => $eventInfo){
		$eventName = getEventName($eventID);
		$selected = isSelected($eventID, $defaultEventID);

		echo "<option value='{$eventID}' {$selected} id='eventName{$eventID}'>";
			echo $eventName;
		echo "</option>";

	}

}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
