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

$eventList = getEventList('ASC');
$eventList = sortEventList($eventList);
$eventTypes = ['default','active','upcoming', 'hidden']; 

if(isset($_SESSION['failedLogIn'])){
	$defaultEventID = $_SESSION['failedLogIn']['eventID'];
	$typeSelect = $_SESSION['failedLogIn']['type'];
	unset($_SESSION['failedLogIn']);
} else {
	$typeSelect = USER_STAFF;
	$defaultEventID = $_SESSION['eventID'];
}

if($typeSelect != USER_STAFF && $typeSelect != USER_ADMIN){
	$logInEventListClass = "style='display:none'";
}
$eventNameForLogin = getEventName($defaultEventID);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>	

	<div class='grid-x grid-margin-x'>
		<div id='login-form' class='small-12 medium-6 large-4 cell'>
		<form action='adminLogIn.php' method='POST'>
		
		<!-- User type -->
			<label>
				<span>User Type</span>
				<select id='logInType' name='logInType' autocomplete='username' 
					onchange="logInTypeToggle(this)" <?=$typeSelect?>>

					<option <?=optionValue(USER_STAFF, $typeSelect)?>>
						Event Staff
					</option>
					<option <?=optionValue(USER_ADMIN, $typeSelect)?>>
						Event Organizer
					</option>
					<option <?=optionValue(USER_SUPER_ADMIN, $typeSelect)?>>
						Software Administrator
					</option>
					<option <?=optionValue(USER_VIDEO, $typeSelect)?>>
						Video Manager
					</option>
					<option <?=optionValue(USER_STATS, $typeSelect)?>>
						Analytics User
					</option>

				</select>
			</label>
			


		<!-- Event list -->
		
			<label id='logInEventList' <?=$logInEventListClass?> >
				<span>Event to Log Into</span>
				<select id='logInEventID' name='logInEventID'  onchange="logInEventToggle('logInEventID')">	
				<?php foreach((array)$eventTypes as $type):

					foreach((array)$eventList[$type] as $eventID => $eventInfo):
						$eventName = getEventName($eventID);
						if($eventNameForLogin == null){
							$eventNameForLogin = $eventName;
						}
						$selected = isSelected($eventID, $defaultEventID);		
						?>
							
						<option value='<?=$eventID?>' <?=$selected?> id='eventName<?=$eventID?>'>
							<?=$eventName?>
						</option>";
						
					<?php endforeach ?>	
				<?php endforeach ?>	
				</select>
			</label>
		

		<!-- This exists to give a username to password manager functionality of the browser
			It is hidden from the user. -->
			<input id='LogInUserName' type='text' name='userName' value='Event Staff: <?=$eventNameForLogin?>' style='display:none'>
			
		<!-- Password -->	
			<label>
				<span>Password</span>
			<input type='password' name='password'>
			</label>
			<button class='button large small-12 cell' name='formName' value='logUserIn'>
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

/******************************************************************************

function displayLogInLevel($userType){ 		No longer used
	$eventName = getEventName(null);
	echo "<BR>";
	echo "<form action='adminLogIn.php' method='POST'>
	<input type='hidden' name='formName' value='logUserIn'>";
	
	
	if($_SESSION['userType'] != USER_GUEST){
		echo "<div class='grid-x grid-padding-x'>
				<div class='large-4 medium-4 cell align-self-middle'>
					Currently logged in to {$eventName} as:
				</div>
				<div class='large-2 medium-2 cell callout'>";
		switch ($userType){
			case USER_STAFF:
				echo "Event Staff";
				break;
			case USER_ADMIN:
				echo "Tournament Organizer";
				break;
			case USER_SUPER_ADMIN:
				echo "Program Staff";
				break;
			case USER_VIDEO:
				echo "Video Manager";
				break;
			case USER_STATS:
				echo "Analytics User";
				break;
			default:
				break;
		}
		
		echo "	</div>
				<div class='large-2 medium-2 cell'>
					<button class='button large alert' name='logInType' value='1'>
						Log Out
					</button>
				</div>
			</div>";
	} else {
		echo "You are not logged in.<BR>
				If you are event staff or an event organizer please log in.";
	}
	echo "</form>";
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
