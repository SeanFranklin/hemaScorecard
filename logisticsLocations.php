<?php
/*******************************************************************************
	Logistics Locations
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Logistics Locations';
$includeTournamentName = false;
$jsIncludes[] = 'logistics_management_scripts.js';
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false) {
	pageError('user');
} elseif($_SESSION['isMetaEvent'] == true){
	redirect('infoSummary.php');
} else {
	
	// If they are just viewing the page without management permisions all the forms are locked.
	if(ALLOW['EVENT_MANAGEMENT'] == true){
		$formLock = '';
	} else {
		$formLock = 'disabled';
	}

	$locationInfo = (array)logistics_getEventLocations($_SESSION['eventID']);
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>

<form method='POST' id="ll-form">
<fieldset class='fieldset' <?=$formLock?> >
<legend><h3>Tournament Layout</h3></legend>
	
	<input type='hidden' name='editLocationInformation[eventID]' value='<?=$_SESSION['eventID']?>'>

	<table>

		<tr>
			<th></th>
			<th>Location Name</th>
			<th>
				Holds Matches
				<?=tooltip("Unchecking this will remove this location from the list of places you can assign tournament matches to.")?>
			</th>
			<th>
				Holds Classes
				<?=tooltip("Unchecking this will remove this location from the list of places you can assign classes to.")?>
			</th>
		</tr>

		<?php 


			foreach($locationInfo as $location){
				editLocationRow($location);
			}

			echo "<tr><td colspan='100%'>Add Locations:</td></tr>";

			editLocationRow(-1);
			editLocationRow(-2);
			editLocationRow(-3);
			editLocationRow(-4);
			?>

	</table>
	<em>
		You can assign Staffing Shifts to any location. You can create a location such as "First-Aid Table" which can not hold matches or classes.
	</em><BR>
	<a class='button success' onclick="logistics_locationsFormSubmit()">
		Update Locations
	</a>
	<button class='button alert' name='formName' value='deleteLocations'>
		Delete Selected
	</button>

	<?=confirmFormBox()?>

</fieldset>



</form>	
	

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

function confirmFormBox(){
?>

	<div class='reveal' id='ll-confirm-modal' data-reveal>

	<div class='text-center'>
		<HR class='no-bottom'>
		<h3 class='red-text no-bottom'>WARNING</h3>
		<HR class='no-top'>
	</div>
	You are removing the ability of one or more locations to hold tournaments/classes.

	<BR><BR>
	Removing the ability to hold tournaments will:
	<li>Delete any tournament schedule blocks associated with this ring.</li>
	<li>Delete any tournament staffing shifts associated with this ring.</li>
	<li>Un-Assign any groups and matches allocated to this ring.</li>

	<BR>
	Removing the ability to hold classes will:
	<li>Delete any classes associated with this locaiton.</li>

	<BR>
	The following are not affected:
	<li>Schedule blocks which are neither tournaments nor classes</li>
	<li>Staff checked in to matches individually.</li>

	<HR>

	<!-- Submit buttons -->
		<div class='grid-x grid-margin-x'>

			<a class='button success small-6 cell' onclick="submitForm('ll-form','editLocations')">
				Yes, I understand
			</a>

			<button class='button secondary small-6 cell' data-close aria-label='Close modal' type='button'>
				No, Cancel Update
			</button>
		</div>




		<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>

	</div>

<?php
}

/******************************************************************************/

function editLocationRow($locationInfo){
	if(is_array($locationInfo)){
		$locationID = (int)$locationInfo['locationID'];
		$locationName = $locationInfo['locationName'];

		if($locationInfo['hasMatches'] != 0){
			$hasMatches = 'checked';
		} else {
			$hasMatches = '';
		}

		if($locationInfo['hasClasses'] != 0){
			$hasClasses = 'checked';
		} else {
			$hasClasses = '';
		}

		$onClick = "onchange='logistics_locationsFormPaddleCheck(this)'";

	} else {
		$locationID = $locationInfo;
		$locationName = null;
		$hasMatches = 'checked';
		$hasClasses= '';
		$onClick = '';
	}

?>
	<tr>
		<td>
			<?php if(((int)$locationID) > 0): ?>
				<input type='checkbox' class='no-bottom'
						name='locationsToDelete[<?=$locationID?>]' 
						value=<?=$locationID?> >
			<?php endif ?>
		</td>
		<td>
			<input type='text' class='no-bottom' 
					name='editLocationInformation[locations][<?=$locationID?>][locationName]' 
					value='<?=$locationName?>' placeholder='eg: Ring 1, South Classrom, etc...'>
		</td>
		<td>

			<div class='switch text-center no-bottom'>
				<input type='hidden' value='0'
						name='editLocationInformation[locations][<?=$locationID?>][hasMatches]'>
				<input class='switch-input' type='checkbox' <?=$onClick?>
					id='editLocationInformation[locations][<?=$locationID?>][hasMatches]' <?=$hasMatches?>
					name='editLocationInformation[locations][<?=$locationID?>][hasMatches]' value='1' >
				<label class='switch-paddle' 
					for='editLocationInformation[locations][<?=$locationID?>][hasMatches]'>
				</label>
			</div>

		</td>

		<td>
			
			<div class='switch text-center no-bottom'>
				<input type='hidden' value='0'
						name='editLocationInformation[locations][<?=$locationID?>][hasClasses]'>
				<input class='switch-input' type='checkbox' <?=$onClick?>
					id='editLocationInformation[locations][<?=$locationID?>][hasClasses]' <?=$hasClasses?>
					name='editLocationInformation[locations][<?=$locationID?>][hasClasses]' value='1' >
				<label class='switch-paddle' 
					for='editLocationInformation[locations][<?=$locationID?>][hasClasses]'>
				</label>
			</div>

		</td>


	</tr>

<?php
}


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
