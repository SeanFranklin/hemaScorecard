<?php
/*******************************************************************************
	Event Details

	Change event passwords and set defaults for newly created tournaments
	LOGIN:
		- ADMIN or higher required to view

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Event Details';
$jsIncludes[] = "sortable_scripts.js";
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false){
	pageError('user');
} else {

	$defaults = getEventDefaults($_SESSION['eventID']);

	if(ALLOW['SOFTWARE_ASSIST'] == true){
		$canChangeSettings = true;
	} elseif($_SESSION['eventID'] == TEST_EVENT_ID){
		// user loged in to test event for test purposes
		$canChangeSettings = false;
	} elseif(ALLOW['EVENT_MANAGEMENT'] == true) {
		$canChangeSettings = true;
	} else {
		// VIEW_SETTINGS case
		$canChangeSettings = false;
	}



// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<div class='grid-x grid-margin-x'>

		<?=publicationsSettingsBox($canChangeSettings)?>
		<?=eventInfoBox($canChangeSettings)?>

		<?=eventDescriptionBox($canChangeSettings)?>
		<?=displaySettingsBox($defaults, $canChangeSettings)?>
		<?=staffSettingsBox($defaults, $canChangeSettings)?>
		<?=changePasswordBox($canChangeSettings)?>

	</div>


<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/******************************************************************************/

function publicationsSettingsBox($canChangeSettings){

	if(isDescriptionPublished($_SESSION['eventID'])){
		$publishDescriptionChecked = 'checked';
	} else {
		$publishDescriptionChecked = '';
	}

	if(isRulesPublished($_SESSION['eventID'])){
		$publishRulesChecked = 'checked';
	} else {
		$publishRulesChecked = '';
	}

	if(isSchedulePublished($_SESSION['eventID'])){
		$publishScheduleChecked = 'checked';
	} else {
		$publishScheduleChecked = '';
	}

	if(isRosterPublished($_SESSION['eventID'])){
		$publishRosterChecked = 'checked';
	} else {
		$publishRosterChecked = '';
	}

	if(isMatchesPublished($_SESSION['eventID'])){
		$publishMatchesChecked = 'checked';
	} else {
		$publishMatchesChecked = '';
	}

	$formDiabled = '';
	if($canChangeSettings == false){
		$formDiabled = 'disabled';
	}

?>

	<fieldset class='fieldset cell large-6' <?=$formDiabled?> >
	<legend><h4 class='no-bottom'>Event Publication</h4></legend>
	<form method='POST'>
		No one will be able to see your awesome event until you check these boxes. <b>Once you are ready be sure to publish the event.</b><BR>
		<i>Your event will not appear on the event list until you publish at least some information.</i>
		<div class='grid-x grid-margin-x'>
		<div class='large-8 medium-6 cell'>

			<table>
				<tr>
					<td>
						Description
						<?=tooltip("Shows basic information about the event that you entered lower on this page.")?>
					</td>
					<td>
						<input type='hidden'  name='publicationSettings[publishDescription]' value='0'>
						<input class='switch-input' type='checkbox' <?=$publishDescriptionChecked?>
							id='publicationSettings[publishDescription]'
							name='publicationSettings[publishDescription]' value='1'>
						<label class='switch-paddle' for='publicationSettings[publishDescription]'>
						</label>
					</td>
				</tr>

				<tr>
					<td>
						Rules
						<?=tooltip("Publish any rules that you created in <b>Manage Event -> Rules</b>.")?>
					</td>

					<td>
						<input type='hidden' name='publicationSettings[publishRules]' value='0'>
						<input class='switch-input' type='checkbox' <?=$publishRulesChecked?>
							id='publicationSettings[publishRules]'
							name='publicationSettings[publishRules]' value='1'>
						<label class='switch-paddle' for='publicationSettings[publishRules]'>
						</label>
					</td>
				</tr>


				<tr>
					<td>
						Schedule
						<?=tooltip("Publish the schedule you have created in <b>Event Logistics</b>.")?>
					</td>
					<td>
						<input type='hidden' name='publicationSettings[publishSchedule]' value='0'>
						<input class='switch-input' type='checkbox' <?=$publishScheduleChecked?>
							id='publicationSettings[publishSchedule]'
							name='publicationSettings[publishSchedule]' value='1'>
						<label class='switch-paddle' for='publicationSettings[publishSchedule]'>
						</label>
					</td>
				</tr>


				<tr>
					<td>
						Attendees
						<?=tooltip("Publish the list of competitors and tournament entries.")?>
					</td>
					<td>
						<input type='hidden' name='publicationSettings[publishRoster]' value='0'>
						<input class='switch-input' type='checkbox'
							id='publicationSettings[publishRoster]' <?=$publishRosterChecked?>
							name='publicationSettings[publishRoster]' value='1'>
						<label class='switch-paddle' for='publicationSettings[publishRoster]'>
						</label>
					</td>
				</tr>


				<tr>
					<td>
						Matches
						<?=tooltip("Make everything visible, so everyone can see you pools and matches.")?>
					</td>
					<td>
						<input type='hidden' name='publicationSettings[publishMatches]' value='0'>
					<input class='switch-input' type='checkbox' <?=$publishMatchesChecked?>
						id='publicationSettings[publishMatches]'
						name='publicationSettings[publishMatches]' value='1'>
					<label class='switch-paddle' for='publicationSettings[publishMatches]'>
					</label>
					</td>
				</tr>

			</table>


			<button class='button success no-bottom expanded <?=$formDiabled?>'
				name='formName' value='updateEventPublication'>
				Update Visibility
			</button>


		</div>
		</div>

	</form>
	</fieldset>

<?php
}

/******************************************************************************/

function eventInfoBox($canChangeSettings){

	$contactEmail = getEventEmail($_SESSION['eventID']);
	$eventDates = getEventDates($_SESSION['eventID']);
	$location = getEventLocation($_SESSION['eventID']);

	$formDiabled = '';
	if($canChangeSettings == false){
		$formDiabled = 'disabled';
		$numTextLines = 3;
	}

?>

	<fieldset class='fieldset cell large-6' <?=$formDiabled?> >
	<legend><h4>Event Information</h4></legend>

	<!-- Event Information -->
	<form method='POST'>
	<div class='grid-x grid-margin-x'>
		<div class='cell'>
			<div class=' input-group cell no-bottom'>
				<span class='input-group-label no-bottom'>Event Name:</span>
				<input class='input-group-field no-bottom' type='text'
					name='newEventInfo[eventName]'
					value="<?=getEventName($_SESSION['eventID'],'raw')?>">
			</div>
			<p class='red-text'>Don't include a year, it will be added automatically!</p>
		</div>

		<div class='large-6 cell input-group cell'>
			<span class='input-group-label no-bottom'>Start:</span>
			<input class='input-group-field no-bottom' type='date' name='newEventInfo[startDate]'
				value='<?=$eventDates['eventStartDate']?>'>
		</div>

		<div class='large-6 cell input-group cell'>
			<span class='input-group-label'>End:</span>
			<input class='input-group-field no-bottom' type='date' name='newEventInfo[endDate]'
				value='<?=$eventDates['eventEndDate']?>'>
		</div>

		<div class='cell'>
			<div class=' input-group cell no-bottom'>
				<span class='input-group-label no-bottom'>City:</span>
				<input class='input-group-field no-bottom' type='text'
					name='newEventInfo[eventCity]'
					value="<?=$location['eventCity']?>">
			</div>
		</div>

		<div class='cell'>
			<div class=' input-group cell no-bottom'>
				<span class='input-group-label no-bottom'>State/Province:</span>
				<input class='input-group-field no-bottom' type='text'
					name='newEventInfo[eventProvince]'
					value="<?=$location['eventProvince']?>">
			</div>
		</div>

		<div class='cell'>
			<div class=' input-group cell'>
				<span class='input-group-label no-bottom'>Country:</span>
				<input class='input-group-field no-bottom' type='text'
					disabled
					value="<?=$location['countryName']?>">
			</div>
		</div>

		<div class='large-6 cell'>
			<button class='button success expanded <?=$formDiabled?>' name='formName' value='setEventInfo'>
				Update Event Information
			</button>
		</div>

	</div>
	</form>

	<!-- Contact E-mail -->
	<?php if(ALLOW['EVENT_MANAGEMENT'] == true || ALLOW['VIEW_EMAIL'] == true): ?>
		<HR>

		<form method='POST'>
		<input class='hidden' name='formName' value='setContactEmail'>
		<div class='grid-x grid-margin-x'>
			<div class='large-12 input-group cell'>
				<span class='input-group-label'>Contact E-mail: <?=tooltip('This e-mail will not appear anywhere publicly visible.')?></span>
				<input class='input-group-field' type='text' name='contactEmail'
					value='<?=$contactEmail?>' placeholder="Don't leave this blank!">
				<input type='submit' class='button success input-group-button' value='Update' <?=$formDiabled?>>
			</div>

		</div>
		</form>
	<?php endif ?>
	</fieldset>

<?php
}

/******************************************************************************/

function eventDescriptionBox($canChangeSettings){

	$eventDescription = getEventDescription($_SESSION['eventID']);

	if($eventDescription != ''){
		$numTextLines = 15;
	} else {
		$numTextLines = 10;
	}


	$formDiabled = '';
	if($canChangeSettings == false){
		$formDiabled = 'disabled';
		$numTextLines = 3;
	}



?>

	<!--  Event Description -------------------------------->
	<fieldset class='fieldset large-12 cell' <?=$formDiabled?> >
	<legend><h4>Event Description</h4></legend>

		<i class='blue-text'>
			Sorry, this kind of sucks at the moment and won't display formatted unless you manually enter HTML tags. If you are having trouble bother me and I can format it for you (and also gives me another kick to try to figure out the proper solution.)
		</i>

		<form method='POST'>
		<div class='grid-x grid-margin-x'>

			<div class='large-12 cell'>
				<textarea name='eventDescription' required rows='<?$numTextLines?>'><?=$eventDescription ?></textarea>
			</div>


			<div class='large-3 cell'>
				<button class='button success expanded no-bottom'
					name='formName' value='setEventDescription' <?=$formDiabled?>>
					Update Event Description
				</button>
			</div>

		</div>
		</form>

	<?php if($eventDescription != ''):?>
		<hr>
		<i><a onclick="$('.event-description').toggle()">Show Event Description ↓</a></i>
		<div class='large-12 cell hidden event-description documentation-div callout success'>
			<?=$eventDescription?>
		</div>
	<?php endif ?>

	</fieldset>


<?php
}

/******************************************************************************/

function displaySettingsBox($defaults, $canChangeSettings){
	$tournamentIDs = getEventTournaments($_SESSION['eventID']);

	if($defaults['tournamentSorting'] != 'custom'){
		$showCustomSort = 'hidden';
	} else {
		$showCustomSort = '';
	}

	$formDiabled = '';
	if($canChangeSettings == false){
		$formDiabled = 'disabled';
	}

?>
	<!--  Display settings  -------------------------------->


	<fieldset class='fieldset cell large-6' <?=$formDiabled?>>
	<legend><h4>Display Settings</h4></legend>
	<form method='POST'>

	<div class='grid-x grid-margin-x'>

	<!-- Fighter names -->
		<div class='large-12 cell'>
			<div class='input-group'>
				<span class='input-group-label'>Fighter Names:</span>
				<select class='input-group-field' name='displaySettings[nameDisplay]'>

					<option value='firstName'>First Last</option>

					<option <?=optionValue('lastName', $defaults['nameDisplay'])?>>Last, First</option>

				</select>
			</div>
		</div>

	<!-- Tournament name order -->
		<div class='large-12 cell'>
			<div class='input-group'>
				<span class='input-group-label'>Tournament:</span>
				<select class='input-group-field' name='displaySettings[tournamentDisplay]'>

					<option value='weapon'>
						[Weapon] - [Division] [Gender] [Material]
					</option>

					<option <?=optionValue('prefix',$defaults['tournamentDisplay'])?>>
						[Division] [Gender] [Material] [Weapon]
					</option>

				</select>
			</div>
		</div>

	<!-- Tournament sort order -->
		<div class='large-12 cell'>
			<div class='input-group'>
				<span class='input-group-label'>Tournament Order:</span>
				<select class='input-group-field' name='displaySettings[tournamentSorting]'
					onchange="showForOption(this, 'custom', 'tournament-order-box')">

					<option value='numGrouped'>Number of Fighters, Group By Weapon</option>

					<option <?=optionValue('numSort', $defaults['tournamentSorting'])?>>
						Number of Fighters
					</option>

					<option <?=optionValue('nameSort', $defaults['tournamentSorting'])?>>
						Alphabetically
					</option>

					<option <?=optionValue('custom', $defaults['tournamentSorting'])?>>
						Custom (Input Below)
					</option>
				</select>
			</div>
		</div>

		<div class='large-12 cell tournament-order-box <?=$showCustomSort?>'>
			<h5>Tournament Order:</h5>
			<div id='sort-tournament-order'>

				<?php foreach($tournamentIDs as $index => $tournamentID): ?>
					<div class='callout primary' value=<?=$tournamentID?>>
						<?=getTournamentName($tournamentID)?>
					</div>
				<?php endforeach ?>

			</div>

			<?php foreach($tournamentIDs as $index => $tournamentID): ?>
				<input class='hidden' name='displaySettings[customSort][<?=$tournamentID?>]'
					id='tournament-order-for-<?=$tournamentID?>' value=<?=$index?>>
			<?php endforeach ?>

		</div>

		<div class='large-12 cell'>
		</div>

		<!-- Submit button -->

		<div class='large-6 cell'>
			<button class='button success expanded no-bottom' <?=$formDiabled?>
				name='formName' value='displaySettings'>
				Update Display Settings
			</button>
		</div>


	</div>
	</form>
	</fieldset>

<?php
}

/******************************************************************************/

function staffSettingsBox($defaults, $canChangeSettings){

	$matchMultipliers = logistics_getMatchMultipliers($_SESSION['eventID']);
	$roles = logistics_getRoles();

	$formDiabled = '';
	if($canChangeSettings == false){
		$formDiabled = 'disabled';
	}
?>

	<fieldset class='fieldset cell large-6' <?=$formDiabled?>>
	<legend><h4>Staff Settings</h4></legend>
	<form method='POST'>
	<input type='hidden' name="eventSettings[staffRegistration][eventID]"
		value="<?=$_SESSION['eventID']?>">

	<div class='grid-x grid-margin-x'>

	<!-- Tournament name order -->
		<div class='large-12 cell'>
			<div class='input-group'>
				<span class='input-group-label'>
					Assign Staff on Entry
					<?=tooltip("This controls if the option to assign people as staff appears when entering people into the event using <em>Event Roster</em>.<BR>
						You can always assign staff using the <em>Event Logistics</em> features.")?>
					:
				</span>
				<select class='input-group-field'
					name='eventSettings[staffRegistration][addStaff]'>

					<option value='weapon'>No</option>
					<option <?=optionValue(1,$defaults['addStaff'])?> >Yes</option>

				</select>
			</div>
		</div>

	<!-- Staff Competency -->
		<div class='large-12 cell'>
			<div class='input-group'>
				<span class='input-group-label'>
					Default Staff Competency
					<?=tooltip("You can assign numbers to staff members to help sort them by skill sets. Higher numbers are sorted to the top.
					<BR><u>Example:</u>
					<ol>
					<li>Table Staff</li>
					<li>Judges</li>
					<li>Directors</li>
					</ol>")?>
					:
				</span>
				<select class='input-group-field'
					name='eventSettings[staffRegistration][staffCompetency]'>

					<option <?=optionValue(0,$defaults['staffCompetency'])?> >Don't Use</option>
					<?php for($i=1;$i<=STAFF_COMPETENCY_MAX;$i++): ?>
						<option <?=optionValue($i,$defaults['staffCompetency'])?> > <?=$i?> </option>
					<?php endfor ?>

				</select>
			</div>
		</div>

	<!-- Target Hours -->
		<div class='large-12 cell'>
			<div class='input-group'>
				<span class='input-group-label'>
					Default Target Hours
					<?=tooltip("You can assign a target number of staffing hours for each staff member.
								This is the default number that is applied to all staff.<BR>
								<u>Note</u>: This is only applied when you add someone in as a staff member
								for the first time. It will not change the target hours of staff already
								added to the event.")?>
					:
				</span>
				<input type='number' class='input-group-field' value=<?=$defaults['staffHoursTarget']?>
					name='eventSettings[staffRegistration][staffHoursTarget]' min=0 max=100>
				</select>
			</div>
		</div>

	<!-- Staff Competency -->
		<div class='large-12 cell'>
			<div class='input-group'>
				<span class='input-group-label'>
					Staff Conflicts
					<?=tooltip("Use this to not allow people to be scheduled into conflicting
					staffing slots.
					<BR><strong>WARNING:</strong>
					<BR>This is not perfect. You need to double check the Staff Conflicts
					page to be sure.
					<BR><em>If you create conflicts and then turn this on, they won't
					be removed.</em>")?>

				</span>
				<select class='input-group-field'
					name='eventSettings[staffRegistration][limitStaffConflicts]'>

					<option <?=optionValue(STAFF_CONFLICTS_NO,$defaults['limitStaffConflicts'])?> >
						No Checking
					</option>
					<option <?=optionValue(STAFF_CONFLICTS_HARD,$defaults['limitStaffConflicts'])?> >
						Hard Limit
					</option>

				</select>
			</div>
		</div>

	<!-- Role Competencies -->
		<div class='large-6 cell' >
			<a onclick="toggleClass('esr-assignCompetencies')" class='esr-assignCompetencies'>
				Assign competencies to roles ↓
			</a>
		</div>


		<fieldset class='fieldset large-12 cell esr-assignCompetencies hidden'>
			<legend>
				<a onclick="toggleClass('esr-assignCompetencies')">
					Hide &#x2191;
				</a>
			</legend>

			Assigning staff bellow these competencies to a position will generate a warning.


			<table>
				<tr>
					<th>Role</th>
					<th>Minimum Competency</th>
				</tr>
			<?php foreach($roles as $role):
				$roleID = $role['logisticsRoleID'];
				if(	   $roleID == LOGISTICS_ROLE_UNKONWN
					|| $roleID == LOGISTICS_ROLE_UNKONWN
					|| $roleID == LOGISTICS_ROLE_GENERAL
					|| $roleID == LOGISTICS_ROLE_PARTICIPANT){
					continue;
				}

				if(isset($defaults['roleCompetency'][$roleID]) == true){
					$selectValue = $defaults['roleCompetency'][$roleID];
				} else {
					$selectValue = null;
				}
				?>
				<tr>
					<td><?=$role['roleName']?></td>
					<td>
						<select name='eventSettings[staffRegistration][competencyCheck][<?=$roleID?>]' >

							<option value='0'>- n/a -</option>
							<?php for($i=1;$i<=STAFF_COMPETENCY_MAX;$i++): ?>
								<option <?=optionValue($i,$selectValue)?> > <?=$i?> </option>
							<?php endfor ?>

						</select>
					</td>
				</tr>
			<?php endforeach ?>
			</table>


		</fieldset>

	<!-- Role Multipliers -->
		<div class='large-6 cell' >
			<a onclick="toggleClass('esr-assignMatchMultipiers')" class='esr-assignMatchMultipiers'>
				Assign match role multipliers ↓
			</a>
		</div>

		<fieldset class='fieldset large-12 cell esr-assignMatchMultipiers hidden'>
			<legend>
				<a onclick="toggleClass('esr-assignMatchMultipiers')">
					Hide &#x2191;
				</a>
			</legend>

			When calculating the number of matches judged the count will be multiplied by the number entered below


			<table>
				<tr>
					<th>Role</th>
					<th>Multiplier</th>
				</tr>
			<?php foreach($roles as $role):
				$roleID = $role['logisticsRoleID'];
				if($roleID == LOGISTICS_ROLE_PARTICIPANT){
					continue;
				}?>

				<tr>
					<td><?=$role['roleName']?></td>
					<td>
						<input type='number' class='no-bottom' min=0 max=99 placeholder="x0"
						name='eventSettings[staffRegistration][matchMultipliers][<?=$roleID?>]'
						value="<?=$matchMultipliers[$roleID]?>" step="0.01">
					</td>
				</tr>

			<?php endforeach ?>
			</table>


		</fieldset>

		<div class='large-12 cell'>
			<BR>
		</div>

	<!-- Submit button -->

		<div class='large-6 cell'>
			<button class='button success expanded' <?=$formDiabled?>
				name='formName' value='staffRegistrationSettings'>
				Update Staff Settings
			</button>
		</div>


	</div>
	</form>
	</fieldset>

<?php
}

/******************************************************************************/

function changePasswordBox($canChangeSettings){

	$formDiabled = '';
	if($canChangeSettings == false){
		$formDiabled = 'disabled';
	}
?>

	<fieldset class='fieldset cell large-12' <?=$formDiabled?> >
	<legend><h4>Change Password - Event Staff</h4></legend>
	<form method='POST'>


		<div class='grid-x grid-margin-x'>
		<input type='hidden' name='formName' value='updatePasswords'>

	<!-- New staff password -->
		<div class='large-5 input-group cell'>
			<span class='input-group-label'>New Staff Password:</span>
			<input class='input-group-field' type='text' name='changePasswords[newPassword]' required>
		</div>

	<!-- Current password -->
		<div class='large-5 input-group cell'>
			<span class='input-group-label'>Current Admin Password: </span>
			<input class='input-group-field' type='password' name='changePasswords[passwordVerification]'>
		</div>

	<!-- Submit button -->
		<div class='large-2 cell'>
			<button class='button success expanded' <?=$formDiabled?>
				name='changePasswords[userName]' value='eventStaff'>
				Update Staff Password
			</button>
		</div>

		</div>
	</form>
	</fieldset>

<!-- Change Admin Password ----------------------------------->
	<fieldset class='fieldset cell large-12' <?=$formDiabled?> >
	<legend><h4>Change Password - Event Organizer</h4></legend>

	<form method='POST'>

		<div class='grid-x grid-margin-x'>
		<input type='hidden' name='formName' value='updatePasswords'>

	<!-- New admin password -->
		<div class='large-5 input-group cell'>
			<span class='input-group-label'>New Admin Password:</span>
			<input class='input-group-field' type='text' name='changePasswords[newPassword]'>
		</div>

	<!-- Current password -->
		<div class='large-5 input-group cell'>
			<span class='input-group-label'>Current Admin Password: </span>
			<input class='input-group-field' type='password' name='changePasswords[passwordVerification]'>
		</div>

	<!-- Submit button -->
		<div class='large-2 cell'>
			<button class='button success expanded' <?=$formDiabled?>
				name='changePasswords[userName]' value='eventOrganizer'>
				Update Organizer Password
			</button>
		</div>

		</div>

	</form>
	</fieldset>

<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
