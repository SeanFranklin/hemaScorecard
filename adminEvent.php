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
} elseif(USER_TYPE < USER_ADMIN){
	pageError('user');
} else {
	
	$defaults = getEventDefaults();
	define(MAX_VAL,10);  	// Maximum value for most tournament parameters, arbitrary
	$contactEmail = getEventEmail();
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!--  Event Settings  -------------------------------->
	<fieldset class='fieldset'>
	<legend><h4>Event Status</h4></legend>
	<form method='POST'>
	
		<div class='grid-x grid-margin-x'>
			<div class='medium-6 large-4 cell input-group'>
			<span class='input-group-label pointer'>
				Event Status&nbsp;<img src='includes/images/help.png' data-open="statusTypes">
			</span>
			<select class='input-group-field' type='text' name='eventStatus'>
				<?php				
				
				$selected = isSelected('hidden', getEventStatus());?>
				<option value='hidden'<?=$selected?>>Hidden</option>
				<?php $selected = isSelected('upcoming', getEventStatus());?>
				<option value='upcoming' <?=$selected?>>Upcoming</option>
				<?php $selected = isSelected('active', getEventStatus());?>
				<option value='active'<?=$selected?>>Active</option>
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
	
	<div class='reveal' id='statusTypes' data-reveal>
		<ul>
			<li>
				<strong>Hidden</strong> - 
				No one can see event details without logging in as event staff/organizer.
			</li>
			<li>
				<strong>Upcomming</strong> - 
				Everyone can see the event tournaments and roster. Only event staff/organizers can see pools/matches.
			</li>
			<li>
				<strong>Active</strong> - 
				Everyone can see all results.
			</li>
		</ul>
		<button class='close-button' data-close aria-label='Close modal' type='button'>
		<span aria-hidden='true'>&times;</span>
		</button>
	</div>
	

<!--  Tournament Defaults  -------------------------------->
	<fieldset class='fieldset'>
	<legend><h4>Tournament Defaults</h4></legend>
	<form method='POST'>
	
		<div class='grid-x grid-margin-x'>
		
	<!-- Default Colors -->
		<?php colorSelectDropDown(1,$defaults['color1ID']); ?>
		<?php colorSelectDropDown(2,$defaults['color2ID']); ?>
		
	<!-- Match Timer -->
		<div class='medium-6 large-4 cell input-group'>
			<span class='input-group-label'>Use Match Timer:</span>

			<select class='input-group-field' type='text' name='useTimer'>
				<?php $selected = isSelected(1, $defaults['useTimer']);?>
				<option value=0>No</option>
				<option value=1 <?=$selected?>>Yes</option>
			</select>
		</div>
		
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
	<fieldset class='fieldset'>
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


<!--  Contact information -------------------------------->
	<fieldset class='fieldset'>
	<legend><h4>Contact Information</h4></legend>
	<form method='POST'>
	
	<div class='grid-x grid-margin-x'>
	
	<!-- Tournament name order -->
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
	</fieldset>
	
		
<!-- Change Passwords ----------------------------------->
	<form method='POST'>
	<fieldset class='fieldset'>
		<legend><h4>Change Passwords</h4></legend>
		<div class='grid-x grid-margin-x'>
		<input type='hidden' name='formName' value='newPasswords'>

	<!-- New staff password -->
		<div class='large-6 input-group cell'>
			<span class='input-group-label'>New Staff Password:</span>
			<input class='input-group-field' type='text' name='<?=USER_STAFF?>' 
				placeholder=' Leave blank for no change'>
		</div>

	<!-- New admin password -->	
		<div class='large-6 input-group cell'>
			<span class='input-group-label'>New Admin Password:</span>
			<input class='input-group-field' type='text' name='<?=USER_ADMIN?>' 
				placeholder=' Leave blank for no change'>
		</div>
		
	<!-- Current password -->
		<div class='large-12 input-group cell'>
			<span class='input-group-label'>Current Admin Password: </span>
			<input class='input-group-field' type='password' name='passwordVerification'>
			<button class='button success input-group-button hide-for-small-only' 
				name='updateEventPasswords' value='Update Passwords'>
				Update Passwords
			</button>
		</div>
		
	<!-- Submit button -->
		<div class='cell'>
			<button class='button success expanded show-for-small-only' 
				name='updateEventPasswords' value='Update Passwords'>
				Update Passwords
			</button>
		</div>
		
		</div>
	</fieldset>
	</form>
		
	
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
