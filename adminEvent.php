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

include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false){
	pageError('user');
} else {
	
	$defaults = getEventDefaults($_SESSION['eventID']);
	$roles = logistics_getRoles();
	define("MAX_VAL",10);  	// Maximum value for most tournament parameters, arbitrary
	$contactEmail = getEventEmail($_SESSION['eventID']);
	$eventDates = getEventDates($_SESSION['eventID']);

	// Locks are HTML tags. 'disabled' means the lock is ON and the form is disabled.
	$formLock = 'disabled';
	$passwordLock = 'disabled';

	if(ALLOW['EVENT_MANAGEMENT'] == true){
		$formLock = '';
		$passwordLock = '';
	} elseif(ALLOW['SOFTWARE_ASSIST'] == true) {
		$passwordLock = '';
		$testEventDisable = '';
	}

	$testEventDisable = '';
	if($_SESSION['eventID'] == TEST_EVENT_ID && ALLOW['SOFTWARE_ASSIST'] == false){
		$testEventDisable = "disabled";
	}
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!--  Event Settings  -------------------------------->
	<?php if($testEventDisable == null): ?>
	<fieldset class='fieldset' <?=$formLock?> <?=$testEventDisable?> >
	<legend><h4>Event Status</h4></legend>
	<form method='POST'>
	
		<div class='grid-x grid-margin-x'>
			<div class='medium-8 large-4 cell input-group'>
			<span class='input-group-label pointer'>
				Event Status&nbsp;<img src='includes/images/help.png' data-open="statusTypes">
			</span>
			<select class='input-group-field' type='text' name='eventStatus' <?=$testEventDisable?> >
				
				<option <?=optionValue('hidden', getEventStatus())?> >
					Under Construction
				</option>
				
				<option <?=optionValue('upcoming', getEventStatus())?> >
					Publish Roster
				</option>
				
				<option <?=optionValue('active', getEventStatus())?> >
					Publish All
				</option>
			</select>
			</div>
			
			<div class='large-3 medium-4 small-12 text-center'>
			<button class='button success expanded' name='formName' value='eventStatusUpdate'>
				Update Status
			</button>
			</div>
		</div>
	</form>
	</fieldset>
	<?php endif ?>
	
	<div class='reveal' id='statusTypes' data-reveal>
		<ul>
			<li>
				<strong>Under Construction</strong> <i>(Hidden)</i><BR> 
				No one can see event details without logging in as event staff/organizer.
			</li>
			<li>
				<strong>Publish Roster</strong> <i>(Upcomming)</i><BR>
				Everyone can see the event tournaments and roster. Only event staff/organizers can see pools/matches.<BR>
				Use this if you want to share the rosters but don't want participants to see you working on the pools/brackets.
			</li>
			<li>
				<strong>Publish All</strong> <i>(Active)</i><BR>
				Everyone can see all results.
				<BR>Use this when your even is ready to go, and you want to share everything with the world!
			</li>
		</ul>
		<button class='close-button' data-close aria-label='Close modal' type='button'>
		<span aria-hidden='true'>&times;</span>
		</button>
	</div>
	

<!--  Tournament Defaults  -------------------------------->
	<fieldset class='fieldset' <?=$formLock?>>
	<legend><h4>Tournament Defaults</h4></legend>
	<form method='POST'>
	
		<div class='grid-x grid-margin-x'>
		
	<!-- Default Colors -->
		<?php colorSelectDropDown(1,$defaults['color1ID']); ?>
		<?php colorSelectDropDown(2,$defaults['color2ID']); ?>
		
	<!-- Double Hits -->
		<div class='medium-6 large-4 cell input-group'>
			<span class='input-group-label'>Maximum Double Hits:</span>

			<select class='input-group-field' type='text' name='maxDoubleHits'>
				<?php for($i=1; $i<=MAX_VAL; $i++): 
					$selected = isSelected($i == $defaults['maxDoubleHits']);
					?>
					<option value='<?=$i?>' <?=$selected?>><?=$i?></option>
				<?php endfor ?>
			</select>
		</div>

	<!-- Default Max Pool Size -->
		<div class='medium-6 large-4 cell input-group'>
			<span class='input-group-label'>Maximum Pool Size:</span>
			<select class='input-group-field' type='text' name='maxPoolSize'>
				<?php for($i=2; $i<=MAX_VAL; $i++):
					$selected = isSelected($i == $defaults['maxPoolSize']);
					?>
					<option value='<?=$i?>' <?=$selected?>><?=$i?></option>
				<?php endfor ?>
			</select>
		</div>

	<!-- Default Pool Size Normalization -->	
		<div class='medium-6 large-4 cell input-group'>
			<span class='input-group-label'>Normalize Pool Size To:</span>
			<select class='input-group-field' type='text' name='normalizePoolSize'>
				<option value='0'>Auto</option>
				<?php for($i=2; $i<=MAX_VAL; $i++):
					$selected = isSelected($i == $defaults['normalizePoolSize']);
					?>
					<option value='<?=$i?>' <?=$selected?>><?=$i?></option>
				<?php endfor ?>
			</select>
		</div>
		
	<!-- Default Allow Ties -->	
		<div class='medium-6 large-4 cell input-group'>
			<span class='input-group-label'>Allow Ties:</span>
			<select class='input-group-field' type='text' name='allowTies'>
				<?php $selected = isSelected(1, $defaults['allowTies']);?>
				<option value=0>No</option>
				<option value=1 <?=$selected?>>Yes</option>
			</select>
		</div>

	<!-- Default Use Control Point -->	
		<div class='medium-6 large-4 cell input-group'>
			<span class='input-group-label'>Use Control Points:</span>
			<select class='input-group-field' type='text' name='controlPoint'>
				<?php 

				$maxSize = 4;
					$selected = isSelected(0, $defaults['useControlPoint']);
					echo "<option value=0 {$selected}>No</option>";
					for($i = 1; $i <= $maxSize; $i++):
					$selected = isSelected($i, $defaults['useControlPoint']);
					?>
					<option value=<?=$i?> <?=$selected?>><?=$i?> Point<?=plrl($i)?></option>
				<?php endfor ?>
			</select>
		</div>
		
	<!-- Submit Button -->
		<div class='grid-x cell'>
			<div class='large-3 medium-4 small-12 text-center'>
				<button class='button success expanded' name='formName' value='eventDefaultUpdate'>
					Update Defaults
				</button>
			</div>
			<div class='large-1 show-for-large cell'>&nbsp;</div>
			<div class='medium-8 small-12 cell text-center'>
				<em>Defaults only affect the creation of new events. 
				To change the properties of current events use 
				<a href='adminTournaments.php'>Manage Event -> Tournament Settings</a></em>
			</div>
		</div>
		
	</div>
	</form>
	</fieldset>
	
	
<!--  Display settings  -------------------------------->
	<fieldset class='fieldset' <?=$formLock?>>
	<legend><h4>Display Settings</h4></legend>
	<form method='POST'>
	
	<div class='grid-x grid-margin-x'>
	
	<!-- Tournament name order -->
		<div class='medium-12 large-6 cell'>
			<div class='input-group'>
				<span class='input-group-label'>Tournament Names:</span>
				<select class='input-group-field' name='displaySettings[tournamentDisplay]'>
					<option value='weapon'>Weapon - Division Gender Material</option>
					<?php $selected = isSelected('prefix', $defaults['tournamentDisplay']); ?>
					<option value='prefix' <?=$selected?>>Division Gender Material Weapon</option>
				</select>
			</div>
		</div>
		
	<!-- Tournament sort order -->
		<div class='medium-12 large-6 cell'>
			<div class='input-group'>
				<span class='input-group-label'>Tournament Sorting:</span>
				<select class='input-group-field' name='displaySettings[tournamentSorting]'>
					<option value='numGrouped'>Number of Fighters, Group By Weapon</option>
					
					<?php $selected = isSelected('numSort', $defaults['tournamentSorting']); ?>
					<option value='numSort' <?=$selected?>>Number of Fighters</option>
					
					<?php $selected = isSelected('nameSort', $defaults['tournamentSorting']); ?>
					<option value='nameSort' <?=$selected?>>Alphabetically</option>
				</select>
			</div>
		</div>
	
	<!-- Fighter names -->
		<div class='medium-6 large-4 cell'>
			<div class='input-group'>
				<span class='input-group-label'>Fighter Names:</span>
				<select class='input-group-field' name='displaySettings[nameDisplay]'>
					<option value='firstName'>First Last</option>
					<?php $selected = isSelected('lastName', $defaults['nameDisplay']); ?>
					<option value='lastName' <?=$selected?>>Last, First</option>
				</select>
			</div>
		</div>
		
		
		
	<!-- Submit button -->
		<div class='medium-6 large-4 cell'>
			<button class='button success expanded' name='formName' value='displaySettings'>
				Update Display Settings
			</button>
		</div>
		
	</div>
	</form>
	</fieldset>


<!--  Staff Settings  -------------------------------->
	<fieldset class='fieldset' <?=$formLock?>>
	<legend><h4>Staff Settings</h4></legend>
	<form method='POST'>
	<input type='hidden' name="eventSettings[staffRegistration][eventID]" 
		value="<?=$_SESSION['eventID']?>">
	
	<div class='grid-x grid-margin-x'>
	
	<!-- Tournament name order -->
		<div class='medium-6 large-4 cell'>
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
		<div class='medium-6 large-4 cell'>
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
		<div class='medium-6 large-4 cell'>
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
		<div class='medium-6 large-4 cell'>
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
	
		<div class='medium-6 large-4 cell esr-assignCompetencies' >
			<a onclick="toggleClass('esr-assignCompetencies')">
				Assign competencies to roles &#x2193;
			</a>
		</div>

		<fieldset class='fieldset large-12 cell esr-assignCompetencies hidden'>
			<legend>
				<a onclick="toggleClass('esr-assignCompetencies')">
					Hide &#x2191;
				</a>
			</legend>

			Assigning staff bellow these competencies to a position will generate a warning.

			<div class='grid-x grid-margin-x'>
			<div class='large-5 medium-7 cell'>
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
			</div>
			</div>

		</fieldset>
		
	<!-- Submit button -->
		<div class='medium-6 large-4 cell'>
			<button class='button success expanded' name='formName' value='staffRegistrationSettings'>
				Update Staff Settings
			</button>
		</div>
		
	</div>
	</form>
	</fieldset>


<!--  Event Information -------------------------------->
	<?php if($testEventDisable == null): ?>
	<fieldset class='fieldset' <?=$formLock?> <?=$testEventDisable?> >
	<legend><h4>Event Information</h4></legend>

	<!-- Event Information -->
	<form method='POST'>
	<div class='grid-x grid-margin-x'>
		<div class='large-6 cell'>
			<div class=' input-group cell no-bottom'>
				<span class='input-group-label no-bottom'>Event Name:</span>
				<input class='input-group-field no-bottom' type='text' 
					name='newEventInfo[eventName]' 
					value="<?=getEventName($_SESSION['eventID'],'raw')?>">
			</div>
			<em class='red-text'>Don't include a year, it will be added automatically!</em><BR><BR>
		</div>

		<div class='large-6 cell input-group cell'>
			<span class='input-group-label no-bottom'>StartDate:</span>
			<input class='input-group-field no-bottom' type='date' name='newEventInfo[startDate]' 
				value='<?=$eventDates['eventStartDate']?>'>
		</div>

		<div class='large-6 cell input-group cell'>
			<span class='input-group-label'>End Date:</span>
			<input class='input-group-field no-bottom' type='date' name='newEventInfo[endDate]' 
				value='<?=$eventDates['eventEndDate']?>'>
		</div>

		<div class='large-3 cell'>
			<button class='button success expanded' name='formName' value='setEventInfo'>
				Update Event Information
			</button>
		</div>
	
	</div>
	</form>

	<!-- Contact E-mail -->
	<?php if(ALLOW['EVENT_MANAGEMENT'] == true || ALLOW['VIEW_EMAIL'] == true): ?>
		<HR>

		<form method='POST'>
		<div class='grid-x grid-margin-x'>
			<div class='large-6 input-group cell'>
				<span class='input-group-label'>Contact E-mail: <?=tooltip('This e-mail will not appear anywhere publicly visible.')?></span>
				<input class='input-group-field' type='text' name='contactEmail' 
					value='<?=$contactEmail?>' placeholder="Don't leave this blank!">
				<button class='button success input-group-button' name='formName'
					value='setContactEmail'>
					Update
				</button>
			</div>
		
		</div>
		</form>
	<?php endif ?>
	</fieldset>
	<?php endif ?>
	
		
<!-- Change Staff Password ----------------------------------->
	<?php if($testEventDisable == null): ?>
	<form method='POST'>
	<fieldset class='fieldset' <?=$passwordLock?> <?=$testEventDisable?> >
		<legend><h4>Change Password - Event Staff</h4></legend>
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
			<button class='button success expanded' 
				name='changePasswords[userName]' value='eventStaff'>
				Update Staff Password
			</button>
		</div>
		
		</div>
	</fieldset>
	</form>

<!-- Change Admin Password ----------------------------------->
	<form method='POST'>
	<fieldset class='fieldset' <?=$passwordLock?> <?=$testEventDisable?> >
		<legend><h4>Change Password - Event Organizer</h4></legend>
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
			<button class='button success expanded' 
				name='changePasswords[userName]' value='eventOrganizer'>
				Update Organizer Password
			</button>
		</div>
		
		</div>
	</fieldset>
	</form>
	<?php endif ?>
		
	
<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function colorSelectDropdown($number, $colorID){
// A drop down menu to select which color, with the current color highlighted	
	
	$eventID = $_SESSION['eventID'];
	if($eventID == null){
		displayAlert('colorSelectDropdown()','center');
		return;
	}

// Multi-use function, for color values for fighter 1 and fighter 2.
	if($number == 1){
		$name = 'color1ID';
	} else if ($number == 2){
		$name = 'color2ID';
	} else {
		displayAlert('colorSelectDropdown()','center');
		return;
	}

	$allColors = getColors();	// Colors avaliable in the database 
	?>
	

	<div class='medium-6 large-4 cell input-group'>
	<span class='input-group-label'>Color <?=$number?>: </span>

	
	<select class='input-group-field' name='<?=$name?>'>
		<?php foreach($allColors as $color):
			$selected = isSelected($color['colorID'] == $colorID);
			?>
			
			<option value='<?=$color['colorID']?>' <?=$selected?>>
				<?=$color['colorName']?>
			</option>
			
		<?php endforeach ?>
	</select>
	</div>

<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
