<?php
/*******************************************************************************
	Display Functions
	
	Functions to display information to the screen which may be called from
	multiple pages.
	Also performs database access in some cases
	
*******************************************************************************/

/******************************************************************************/

function displayPageAlerts(){
// This function will display any messages which have been added to 
// $_SESSION['alertMessages']. These alert messages are written to by many 
// data processing functions, either as errors or completion confirmations.
// This function is called at the top of every page by the header
// to display error messages created in processing POST data.

// Only displays diagnostic errors for the Software Administrator
	if(ALLOW['SOFTWARE_ASSIST'] == true){
		foreach($_SESSION['alertMessages']['systemErrors'] as $message){
			displayAlert("<strong>Error: </strong>".$message, 'alert');
		}
	} else {
		// If it is a normal user, alert them that there was an error.
		if(sizeof($_SESSION['alertMessages']['systemErrors']) > 0){
			displayAlert("Appologies, but it seems we have encountered some sort of internal error.",'alert');
		}
	}
	$_SESSION['alertMessages']['systemErrors'] = [];

// Error messages for the user.

	foreach((array)$_SESSION['alertMessages']['userErrors'] as $message){
		displayAlert("<strong>Error: </strong>".$message,'warning');
	}
	$_SESSION['alertMessages']['userErrors'] = [];

// Alert messages for the user (ie confirmation messages)
	$alertMessage = '';
	if(sizeof($_SESSION['alertMessages']['userAlerts']) == 1){
		$alertMessage = $_SESSION['alertMessages']['userAlerts'][0];
	} elseif(sizeof($_SESSION['alertMessages']['userAlerts']) > 1) {
		$alertMessage = "<ul>";
		foreach($_SESSION['alertMessages']['userAlerts'] as $message){
			$alertMessage .= "<li>{$message}</li>";
		}
		$alertMessage .= "</ul>";
	}
	displayAlert($alertMessage);
	$_SESSION['alertMessages']['userAlerts'] = [];

}

/******************************************************************************/

function displayAlert ($text = null, $class = 'secondary'){
// Displays a large callout box containing the text parameter


	if($text == null){
		return;
	}

	echo"
	<div class='cell callout {$class} text-center' data-closable>
		<button class='close-button' aria-label='Dismiss alert' type='button' data-close>
			<span aria-hidden='true'>&times;</span>
		</button>

		{$text}	
	</div>";
	

}

/******************************************************************************/

function checkboxPaddle($name, $onVal, $isOn = false, $offVal = 0, $class1 = null, $class2 = null){
	
	// This is explicityly designed to catch a value of 0 as false
	$checked = '';
	if($isOn != false){
		$checked = 'checked';
	}
?>

	<div class='switch text-center no-bottom'>

		<input type='hidden' name='<?=$name?>' value='<?=$offVal?>' class='<?=$class2?>' >

		<input class='switch-input <?=$class1?>' type='checkbox' 
			id='<?=$name?>'  <?=$checked?>
			name='<?=$name?>' value='<?=$onVal?>'>

		<label class='switch-paddle' for='<?=$name?>'>
		</label>
	</div>
<?php
}

/******************************************************************************/

function pageError($type){
	
	if(strcasecmp($type, 'event') == 0){
		$str = "<strong>No Event Selected</strong><BR>
				<a href='infoSelect.php'>Select Event</a>";
	} elseif(strcasecmp($type, 'tournament') == 0){
		$str = "<strong>No Tournament Selected</strong><BR>
				Select a tournament in the upper left menu";
	} elseif(strcasecmp($type, 'user') == 0 || strcasecmp($type, 'login') == 0){
		$str = "
				You do not have permision to view this page.<BR>
				<strong>Click here to <a href='adminLogIn.php'>Login</a></strong>";
	} else {
		$str = "Page can not be displayed";
	}
	
 	displayAlert($str);
}

/******************************************************************************/

function checkForTermsOfUse(){

	$pageName = basename($_SERVER['PHP_SELF']);

// Just Signed ToS
	if(isset($_SESSION['tosConfirmed']) && $_SESSION['tosConfirmed'] === true){
		unset($_SESSION['tosConfirmed']);
		?>
		<div class='reveal medium' id='termsOfUseModal' data-reveal>
		<form method='POST'>
			<h3>Welcome to HEMA Scorecard</h3>
			<p>You are now good to go!</p>
			<p>This is the Event Settings page. Make sure everything here is set to what you want, and then you can start setting up your tournaments.</p>
			<p>I <strong>strongly</strong> suggest that the first things you do are:
				<ul>
				<li>Set the event passwords <i>(Bottom of this page)</i></li>
				<li>Set your event status <i>(Top of this page)</i></li>
			</ul>

			<!-- Reveal close button -->
			<button class='close-button' data-close aria-label='Close modal' type='button'>
				<span aria-hidden='true'>&times;</span>
			</button>

		</div>

		<?php
		return;

// Don't need to sign ToS
	} elseif($_SESSION['userName'] != 'eventOrganizer' || isEventTermsAccepted()){
		return;

// If they need to sign ToS, kicks them back to the log in page. 
// This won't let them leave the log-in screen until they sign the ToS or log out
	} elseif($pageName != 'adminLogIn.php'){
		header('Location: adminLogIn.php');
		exit;
	}

	$email = getEventEmail();

	?>

	<div class='reveal large' id='termsOfUseModal' data-reveal>
		<form method='POST'>
			<h3>Terms of Use</h3>
			<div class='grid-x grid-margin-x'>

			<div class='cell' style='margin-bottom:1em;'>
				Before using HEMA Scorecard please make sure you are cool with the following:
			</div>

		<!-- Public -->
			<div class='cell' style='margin-bottom:1em;'>
				<strong>All information in the software is public</strong><i> (except your contact info)</i>
				<BR>
				<div style='margin-left: 20px;'>
					<input class='no-bottom' type='checkbox' name='ToS[checkboxes][1]'>
					I understand that anyone can look at and have access to all the results/data from any tournament.
				</div>
			</div>

		<!-- Exchange Info -->
			<div class='cell' style='margin-bottom:1em;'>
				<strong>All data from a tournament must be recorded accurately and un-abridged</strong>
				<BR>
				<i>You may feel this is stupid, but it is important to us. We've put in hundreds of hours developing this for your benefit. At least humor us. ;)</i>
				<BR>
				<div style='margin-left: 20px;'>
					<input class='no-bottom' type='checkbox' name='ToS[checkboxes][2]'>
					I will make sure that all directors & staff enter 'No Exchange' and 'No Quality' exchanges as the judges call it.
				<BR>
					<input class='no-bottom' type='checkbox' name='ToS[checkboxes][3]'>
					I will make sure that all directors & staff enter the original score and the afterblow value, not just the overall points awarded.
				<BR>
			
					<i>(eg: 3 points with a -1 afterblow is not a 2 point clean hit)</i>
				</div>
			</div>

		<!-- Volunteer -->
			<div class='cell' style='margin-bottom:1em;'>
				<strong>HEMA Scorecard is made, maintained, and improved by volunteers</strong>
				<BR>
				<div style='margin-left: 20px;'>
					<input class='no-bottom' type='checkbox' name='ToS[checkboxes][4]'>
					I understand that the HEMA Scorecard team is delighted to receive constructive feedback on improvements but are in no way obligated to put up with any crap from me.
				</div>
			</div>
			
		<!-- Contact Info -->
			<div class='cell' style='margin-bottom:1em;'>
				<strong>The following person should be contacted with questions about the tournaments</strong>
				<div class='input-group'>
					<span class='input-group-label'>Contact E-mail</span>
					<input type='text' class='input-group-field' name='ToS[email]' value='<?=$email?>'>
				</div>
	
			</div>

		<!-- Submit -->
			<input type='hidden' name='ToS[numCheckboxes]' value='4'>
			<button class='button success small-6 cell' name='formName' value='SubmitToS'>
				Got it!<BR> I checked and agreed to all 4 boxes and filled in the e-mail.
			</button>

			<button class='button alert small-6 cell' name='formName' value='logUserOut'>
				Not cool.<BR>I'll go back to pen and paper like a cave man.
			</button>


			</div>

		</form>
		
	</div>

<?php
}

/******************************************************************************/

function displayEventButton($eventID, $eventInfo){
//Creates a button to navigate to an event
	
// Format location string

	$location = '';
	if($eventInfo['eventCity'] != null){
		$location = $eventInfo['eventCity'];
	}
	if($eventInfo['eventProvince'] != null){
		if(isset($location)){ $location .= ', '; }
		$location .= $eventInfo['eventProvince'];
	}
	if($eventInfo['countryName'] != null){
		if(isset($location)){ $location .= ', '; }
		$location .= $eventInfo['countryName'];
	}

	$location = rtrim($location,', \t');
	
// Format year and date string
	$name = $eventInfo['eventName'];
	$year = $eventInfo['eventYear'];
	
	$startDate = sqlDateToString($eventInfo['eventStartDate']);
	$endDate = sqlDateToString($eventInfo['eventEndDate']);
	
	if($startDate != null){
		if($endDate == null OR $endDate == $startDate){
			$dateString = $startDate;
		} else {
			$dateString = $startDate." - ".$endDate;
		}
	} else if($endDate != null){
		$dateString = $endDate;
	}
	
// Displays current event in red
	if($eventID == $_SESSION['eventID']){
		$isActive = "alert";
	} else { 
		$isActive = ''; 
	} 
	
	?>


	<button value='<?= $eventID ?>' style='width:100%'
		class='button hollow <?= $isActive ?>' name='changeEventTo' >
		<?= $name ?>, <?= $year ?>
		<span class='hide-for-small-only'> - </span>
		<BR class='show-for-small-only'>
		<?= $location ?>
		<BR>
		<?= $dateString ?>
	</button>

	
<?php }

/******************************************************************************/

function confirmDeleteReveal($formID, $formName){
	
	?>

	
	<script>
	
	</script>
	
	
	<input type='hidden' value = '<?=$formName?>' id='deleteFormName'>
	<div class='reveal medium text-center' id='confirmDelete' data-reveal>
	
	
	<div class='callout alert'><h4>Warning</h4></div>
	<p>You are attempting to delete fighters who have already fought and/or 
	groups which have already started.</p>
	<p>If fighters have been injured or disqualified please remove them using 
	<strong><a href='adminFighters.php'>Manage Fighters > Withdraw Fighters</a></strong></p>
	<HR>
	
	<div class='grid-x grid-margin-x'>

		<button class='button alert small-6 cell no-bottom' 
			onclick="submitForm('<?=$formID?>','<?=$formName?>');">
			I still want to Delete
		</button>

		<button class='button secondary small-6 cell no-bottom' 
			data-close aria-label='Close modal' type='button'>
			Cancel
		</button>

	</div>
	
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentName($tournamentID = 'new'){
// Select boxes for editing a tournament name
// Select boxes for creation of a new tournament will be made if no
// tournamentID is passed to the function
	
//Read all valid attributes from the database
	$sql = "SELECT tournamentTypeID, tournamentType
			FROM systemTournaments
			WHERE tournamentTypeMeta = 'prefix'
			ORDER BY numberOfInstances DESC";
	$prefixList = mysqlQuery($sql, KEY_SINGLES, 'tournamentTypeID', 'tournamentType');
	
	$sql = "SELECT tournamentTypeID, tournamentType
			FROM systemTournaments
			WHERE tournamentTypeMeta = 'gender'
			ORDER BY numberOfInstances DESC";
	$genderList = mysqlQuery($sql, KEY_SINGLES, 'tournamentTypeID', 'tournamentType');
	
	$sql = "SELECT tournamentTypeID, tournamentType
			FROM systemTournaments
			WHERE tournamentTypeMeta = 'material'
			ORDER BY numberOfInstances DESC";
	$materialList = mysqlQuery($sql, KEY_SINGLES, 'tournamentTypeID', 'tournamentType');
	
	$sql = "SELECT tournamentTypeID, tournamentType
			FROM systemTournaments
			WHERE tournamentTypeMeta = 'weapon'
			ORDER BY numberOfInstances DESC";
	$weaponList = mysqlQuery($sql, KEY_SINGLES, 'tournamentTypeID', 'tournamentType');
	
// Read the current attributes of the tournament
	if($tournamentID != 'new' && (int)$tournamentID > 0){
		$sql = "SELECT tournamentID, tournamentWeaponID, tournamentPrefixID, 
					tournamentGenderID, tournamentMaterialID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$currentSettings = mysqlQuery($sql, KEY, 'tournamentID');	
	}
	?>
	
<!-- Begin Display -->

	<div class='large-12 cell'>
	<div class='grid-x grid-padding-x text-center'>
	

	<!-- Prefix -->
		<div class='medium-3 cell text-center tournament-edit-name'>
			<i>Division (Optional)</i>
			<select name='updateTournament[tournamentPrefixID]' 
				id='prefixID_div<?=$tournamentID?>'>

				<?php foreach($prefixList as $ID => $name): ?>
					<option <?=optionValue($ID, @$currentSettings[$tournamentID]['tournamentPrefixID'])?> >
						<?=$name?>
					</option>
				<?php endforeach ?>
			</select>
		</div>
			
	<!-- Gender -->
		<div class='medium-3 cell text-center tournament-edit-name'>
			<i>Gender (Optional)</i>
			<select name='updateTournament[tournamentGenderID]' 
				id='genderID_div<?=$tournamentID?>'>

				<?php foreach($genderList as $ID => $name): ?>
					<option <?=optionValue($ID, @$currentSettings[$tournamentID]['tournamentGenderID'])?> >
						<?=$name?>
					</option>
				<?php endforeach ?>
			</select>
		</div>
	
	
	<!-- Material -->
		<div class='medium-3 cell text-center tournament-edit-name'>
			<i>Material (Optional)</i>
			<select name='updateTournament[tournamentMaterialID]' 
				id='materialID_div<?=$tournamentID?>'>

				<?php foreach($materialList as $ID => $name):?>
					<option <?=optionValue($ID, @$currentSettings[$tournamentID]['tournamentMaterialID'])?> >
						<?=$name?>
					</option>
				<?php endforeach ?>
			</select>
		</div>
		
	<!-- Weapon -->
		<div class='medium-3 cell text-center tournament-edit-name'>
			<strong>Weapon</strong>
			<select name='updateTournament[tournamentWeaponID]' 
				id='weaponID_div<?=$tournamentID?>'>

				<?php foreach($weaponList as $ID => $name): ?>
					<option <?=optionValue($ID, @$currentSettings[$tournamentID]['tournamentWeaponID'])?> >
						<?=$name?>
					</option>
				<?php endforeach ?>
			</select>
		</div>

	</div>
	</div>
	
<?php }

/*****************************************************************************/

function edit_tournamentFormatType($tournamentID = 'new'){
// Select menu for the type of tournament
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a select box to create a new tournament if no parameter is passed
	
	if($_SESSION['isMetaEvent'] == false){
		$sql = "SELECT formatID, formatName
				FROM systemFormats";
		$formatTypes = mysqlQuery($sql, KEY_SINGLES, 'formatID', 'formatName');
	} else {
		$formatTypes[FORMAT_META] = 'Meta Event';
	}

	if($tournamentID != 'new' && (int)$tournamentID > 0){
		$sql = "SELECT formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$currentID = mysqlQuery($sql, SINGLE, 'formatID');
	}
	?>
	
<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box' 
		id='formatID_div<?=$tournamentID?>'>
			
		<strong>Tournament Type</strong>

		<select name='updateTournament[formatID]' 
			onchange="edit_formatType('<?=$tournamentID?>')"
			id='formatID_select<?=$tournamentID?>'>
			
			<?php if($tournamentID == 'new'): ?>
				<option selected disabled></option>
			<?php endif ?>	
			<?php foreach($formatTypes as $ID => $name): ?>
				<option <?=optionValue($ID, @$currentID)?> >
					<?=$name?>
				</option>
			<?php endforeach ?>
		</select>
	</div>
	
<?php }

/*****************************************************************************/

function edit_tournamentDoubleType($tournamentID = 'new'){
// Select menu for the method of handling bilateral hits (double+afterblow)
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a select box to create a new tournament if no parameter is passed
	
	$sql = "SELECT doubleTypeID, doubleTypeName
			FROM systemDoubleTypes";
	$doubleTypes = mysqlQuery($sql, KEY_SINGLES, 'doubleTypeID', 'doubleTypeName');
	
	$display = "hidden"; 		// Hidden for most cases
	$nullOptionSelected = "selected";
	$currentID = null;

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$formatID = mysqlQuery($sql, SINGLE, 'formatID');
		
		if($formatID == FORMAT_MATCH){
			$display = '';
			$nullOptionSelected = '';
			
			$sql = "SELECT doubleTypeID
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$currentID = mysqlQuery($sql, SINGLE, 'doubleTypeID');	
		}
	}
	?>

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='doubleID_div<?=$tournamentID?>'>
			
		<strong>Double/Afterblow Type</strong>
		
		<select name='updateTournament[doubleTypeID]' 
			onchange="edit_doubleType('<?=$tournamentID?>')" id='doubleID_select<?=$tournamentID?>'>
			
			<option <?=$nullOptionSelected?> disabled></option>
				<?php foreach($doubleTypes as $ID => $name):?>
					<option <?=optionValue($ID, $currentID)?>>
						<?=$name?>						
					</option>
				<?php endforeach ?>
		</select>
	</div>
	
<?php }

/*****************************************************************************/

function edit_tournamentRankingType($tournamentID = 'new'){
// Select menu for the tournament ranking alogrithm
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a select box to create a new tournament if no parameter is passed	

	$display = "hidden"; 				// Hidden for most cases
	$nullOptionSelected = "selected";
	$rankingTypeDescriptions = getRankingTypeDescriptions();
	$rankingTypes = [];
	$current = null;

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT tournamentRankingID, name
				FROM systemRankings
				WHERE formatID = (	SELECT formatID
									FROM eventTournaments
									WHERE tournamentID = {$tournamentID})
				ORDER BY numberOfInstances DESC";
		$rankingTypes = mysqlQuery($sql, KEY_SINGLES, 'tournamentRankingID', 'name');

		if($rankingTypes != null){
			$display = null;
			$nullOptionSelected = null;	
		}


		$sql = "SELECT tournamentRankingID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$currentID = mysqlQuery($sql, SINGLE, 'tournamentRankingID');	
	}
	?>

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='rankingID_div<?=$tournamentID?>'>
			
		<strong><a data-open='rankingTypesReveal'>Ranking Type</a></strong>
		<?php tooltip("Method for calculating pool rankings/round scores.<BR>
						Click on link for description of each algorithm/method"); ?>
		
		<select name='updateTournament[tournamentRankingID]' 
			onchange="enableTournamentButton('<?=$tournamentID?>')"
			id='rankingID_select<?=$tournamentID?>'>
		
			<option disabled <?=$nullOptionSelected?>></option>
			<?php foreach($rankingTypes as $ID => $name):?>
				<option <?=optionValue($ID, $currentID)?> >
					<?=$name?>	
				</option>
			<?php endforeach ?>
		</select>
	</div>
	
	
	
<!-- Ranking types reveal-->

	<div class='reveal large' id='rankingTypesReveal' data-reveal>
	
		<h4 class='text-center'>- Ranking Types -</h4>

		<div class='grid-x grid-padding-x'>
			<div class='large-3 callout cell'>
				<ul class='menu medium-vertical show-for-medium'>
				<?php foreach($rankingTypeDescriptions as $type): ?>
					<li onclick="rankingDescriptionToggle('<?=$type['tournamentRankingID']?>')">
						<a><?=$type['name']?></a>
					</li>
				<?php endforeach ?>
				</ul>
				
				
				<ul class='dropdown menu tourney-menu-mobile 
					show-for-small-only align-center' data-dropdown-menu>
					<li>
						<a href='#'>Ranking Algorithms</a>
						<ul class='menu'>
							<?php foreach($rankingTypeDescriptions as $type): ?>
									<li onclick="rankingDescriptionToggle('<?=$type['tournamentRankingID']?>')">
										<a><?=$type['name']?></a>
									</li>
							<?php endforeach ?>
						</ul>
					</li>
				</ul>
				
				
			</div>
			<div class='large-9 cell' id='rankingDescriptionContainer'>
				<div class='rankingDescription'>
					<BR><BR><BR>
					<div class='callout success text-center'>
						<h5>If you would like a Ranking Algorithm not listed here, let the HEMA Scorecard Team know!</h5>
						<i>(It's super easy to add them)</i>
					</div>
				</div>
				<?php foreach($rankingTypeDescriptions as $type): ?>
					<div id='rankingID<?=$type['tournamentRankingID']?>' class='hidden rankingDescription'>
						<h5><?=$type['name']?></h5>
						<div style="white-space: pre-wrap;"><?=$type['description']?>
						</div>
					</div>
				<?php endforeach ?>
			</div>
		
		
		</div>

		
		<?php closeRevealButton(); ?>

	</div>
	
<?php }

/******************************************************************************/

function closeRevealButton(){
	?>
	<button class='close-button' data-close aria-label='Close modal' type='button'>
		<span aria-hidden='true'>&times;</span>
	</button>
	
<?php }

/******************************************************************************/

function edit_tournamentBasePoints($tournamentID = 'new'){
// Select menu for the base points associated with a turnament
// This is for scored events, such as the value of a cut, or points before deductions
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a select box to create a new tournament if no parameter is passed	
	
	$display = "hidden"; // Hidden for most cases
	$value = null;

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT formatID, isReverseScore
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$result = mysqlQuery($sql, SINGLE);
		
		if($result['formatID'] == FORMAT_SOLO 
			|| $result['formatID'] == FORMAT_META
			|| $result['isReverseScore'] > REVERSE_SCORE_NO){
			$display = '';
			
			$sql = "SELECT basePointValue
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$value = mysqlQuery($sql, SINGLE, 'basePointValue');
		}

	}
	?>

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='baseValue_div<?=$tournamentID?>' >
			
		<strong>Base Point Value</strong>
		<?php 
		tooltip("Number to use as a base for scoring calculations.<BR>
			<u>Examples:</u> Base value for a cut, total round score before deductions,
			 or initial fighter score in Injury Score mode");
		?>
			
		<input type='number' name='updateTournament[basePointValue]' value='<?=$value?>' 
			onkeyup="enableTournamentButton('<?=$tournamentID?>')"
			id='baseValue_select<?=$tournamentID?>'>

	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentControlPoints($tournamentID = 'new'){
// Configures if a control point is used in the tournament
// Acts as a boolean flag (0=false) and the value of the control point
	
	$display = "hidden"; 			// Hidden for most cases
	$pointLimit = 4;					// Arbitrary
	$value = null;

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$formatID = mysqlQuery($sql, SINGLE, 'formatID');
		
		if($formatID == FORMAT_MATCH){
			$display = '';
		}

		$sql = "SELECT useControlPoint
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$value = mysqlQuery($sql, SINGLE, 'useControlPoint');
		
	}
	
	if($value == null){
		$sql = "SELECT useControlPoint
				FROM eventDefaults
				WHERE eventID = {$_SESSION['eventID']}";
		$value = mysqlQuery($sql, SINGLE, 'useControlPoint');
		if($value == null){
			$value = 0; 	// Don't Use
		}
	}
	?>

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='controlPoint_div<?=$tournamentID?>'>
			
		Use Control Point
		<?php 
		tooltip("This gives the scorekeeper the option to assign additional points <u>on top of</u> 
			the normal point value for an exchange.");
		?>

		<select name='updateTournament[useControlPoint]'
			id='controlPoint_select<?=$tournamentID?>'>
			
				<?php 
					$selected = isSelected(0, $value);
					echo "<option value=0 {$selected}>No</option>";
					for($i = 1; $i <= $pointLimit; $i++):
						$selected = isSelected($i, $value);
						?>
						<option <?=optionValue($i, $value)?> >
							<?=$i?> Point<?=plrl($i)?>
						</option>
				<?php endfor ?>
			
		</select>		
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentMaxDoubles($tournamentID = 'new'){
// Select menu for the maximum doubles allowed in a tournament
// Appears or disapears as controled by javascript
	
	$display = "hidden"; 		// Hidden for most cases
	$maxDoublesLimit = 10;	// Arbitrary
	$maxDoubles = null; 		// Arbitrary

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT formatID, doubleTypeID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$info = mysqlQuery($sql, SINGLE);
		
		$sql = "SELECT maxDoubleHits
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$maxDoubles = mysqlQuery($sql, SINGLE, 'maxDoubleHits');
		
		if($info['formatID'] == FORMAT_MATCH AND $info['doubleTypeID'] != 3){
			$display = '';
		} else {
			$sql = "SELECT overrideDoubleType
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$isOverrideDoubles = (int)mysqlQuery($sql, SINGLE, 'overrideDoubleType');
			if($isOverrideDoubles == 1){
				$display = '';
			}
		}
	}
	
	if($maxDoubles == null){
		$sql = "SELECT maxDoubleHits
				FROM eventDefaults
				WHERE eventID = {$_SESSION['eventID']}";
		$maxDoubles = mysqlQuery($sql, SINGLE, 'maxDoubleHits');
		if($maxDoubles == null){
			$maxDoubles = 3; 		// Arbitrary
		}
	}
	?>

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='maxDoubles_div<?=$tournamentID?>'>
			
	Maximum Double Hits

		<select name='updateTournament[maxDoubleHits]' 
			id='maxDoubles_select<?=$tournamentID?>'>
			
			<?php for($i = 1; $i <= $maxDoublesLimit; $i++):?>	
				<option <?=optionValue($i,$maxDoubles)?> ><?=$i?></option>
			<?php endfor ?>

			<option <?=optionValue(0,$maxDoubles)?> >Unlimited :(</option>
		</select>		
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentLimitPoolMatches($tournamentID = 'new'){
// Select menu for the maximum doubles allowed in a tournament
// Appears or disapears as controled by javascript

	$display = "hidden"; 	// Hidden for most cases
	$maxMatchesLimit = 10;	// Arbitrary
	$maxMatches = null; 	// Arbitrary

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		if(getTournamentFormat($tournamentID) == FORMAT_MATCH){
			$display = '';
			$maxMatches = getTournamentPoolMatchLimit($tournamentID);
		}
	}
	
	?>

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='limitPoolMatches_div<?=$tournamentID?>'>
			
		Limit Pool Matches
		<?=tooltip("This will limit the number of fights each person has in a pool.<BR>
			<em><u>Example:</u> If there are 8 people in a pool and the limit is 3, then
			each of the fighters will only have 3 matches. (assigned at random)</em>")?>

		<select name='updateTournament[limitPoolMatches]' 
			id='limitPoolMatches_select<?=$tournamentID?>'>
			<option <?=optionValue(0,$maxMatches)?> > No </option>
			<?php for($i = 1; $i <= $maxMatchesLimit; $i++):?>
				<option <?=optionValue($i,$maxMatches)?> ><?=$i?></option>
			<?php endfor ?>
		</select>		
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentMaxPoolSize($tournamentID = 'new'){
// Select menu for the maximum pool size allowed in a tournament
// Appears or disapears as controled by javascript
	
	$display = "hidden"; 			// Hidden for most cases
	$maxSize = null;					
	
	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$formatID = mysqlQuery($sql, SINGLE, 'formatID');
		
		$sql = "SELECT maxPoolSize
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$maxSize = mysqlQuery($sql, SINGLE, 'maxPoolSize');
		
		if($formatID == FORMAT_MATCH){
			$display ='';
		}
	}
	
	if($maxSize == null){
		$sql = "SELECT maxPoolSize
				FROM eventDefaults
				WHERE eventID = {$_SESSION['eventID']}";
		$maxSize = mysqlQuery($sql, SINGLE, 'maxPoolSize');
		if($maxSize == null){
			$maxSize = 5;			// Arbitrary
		}
	}
	?>

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='maxPoolSize_div<?=$tournamentID?>'>
			
		Maximum Pool Size

		<select name='updateTournament[maxPoolSize]'
			id='maxPoolSize_select<?=$tournamentID?>'>
				<?php for($i = 2; $i <= POOL_SIZE_LIMIT; $i++):
					$selected = isSelected($i, $maxSize);
					?>
					
					<option value=<?=$i?> <?=$selected?>><?=$i?></option>
				<?php endfor ?>
			
		</select>		
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentNumSubMatches($tournamentID = 'new'){
// Select menu for the maximum pool size allowed in a tournament
// Appears or disapears as controled by javascript
	
	$display = "hidden"; 			// Hidden for most cases
	$numSubMatches = null;					
	
	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT formatID, numSubMatches
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$res = mysqlQuery($sql, SINGLE);
		
		$numSubMatches = $res['numSubMatches'];
		
		if($res['formatID'] == FORMAT_MATCH){
			$display ='';
		}
	}
	
	?>

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='numSubMatches_div<?=$tournamentID?>'>
			
		Use Sub-matches
		<?=tooltip("Sub-matches will create multiple 'sub-matches' for each match.
					<BR><u>Example</u>: A multi-weapon tournament where competitors
					face off with each weapon set one after another.")?>

		<select name='updateTournament[numSubMatches]'
			id='numSubMatches_select<?=$tournamentID?>'>
				<option value=0>No</option>
				<?php for($i = 2; $i <= 9; $i++): ?>
					
					<option <?=optionValue($i,$numSubMatches)?> >
						<?=$i?> Sub-matches per Match	
					</option>
				<?php endfor ?>
			
		</select>		
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentSubMatchMode($tournamentID = 'new'){
// Select menu for whether or not the tournament allows ties
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases
	$subMatchMode = SUB_MATCH_ANALOG;

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT subMatchMode, formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$data = mysqlQuery($sql, SINGLE);

		$subMatchMode = $data['subMatchMode'];

		if($data['formatID'] == FORMAT_MATCH){
			$display = '';
		}
	}
	?>
	

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='subMatchMode_div<?=$tournamentID?>' >
			
		Sub-match Mode
		<?=tooltip("<u>Analog</u>: The points from all sub-matches are added to determine
					the match winner.
					<BR><BR><u>Digital</u>: Winner is determined by who wins the most sub-matches, 
					regardless of what the scores were.")?>
		
		<select name='updateTournament[subMatchMode]'
			id='subMatchMode_select<?=$tournamentID?>'>
			<option <?=optionValue(SUB_MATCH_ANALOG,$subMatchMode)?> >	Analog 	</option>
			<option <?=optionValue(SUB_MATCH_DIGITAL,$subMatchMode)?> >	Digital	</option>
			
		</select>
		
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentNormalization($tournamentID = 'new'){
// Select menu for the normalization pool size All pools results will be 
// scaled to this size to ensure fairness across different sized pools.
// Appears or disapears as controled by javascript.
	
	$display = "hidden"; 			// Hidden for most cases
	$normSize = null;

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$formatID = mysqlQuery($sql, SINGLE, 'formatID');
		
		$sql = "SELECT normalizePoolSize
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$normSize = mysqlQuery($sql, SINGLE, 'normalizePoolSize');
		
		if($formatID == FORMAT_MATCH ){
			$display = '';
		}
	}
	
	if($normSize == null){
		$sql = "SELECT normalizePoolSize
				FROM eventDefaults
				WHERE eventID = {$_SESSION['eventID']}";
		$normSize = mysqlQuery($sql, SINGLE, 'normalizePoolSize');
		if($normSize == null){
			$normSize = 4;			// Arbitrary
		}
	}
	?>

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='normalizePoolSize_div<?=$tournamentID?>'>
			
		Normalize Pool Size
		<?php tooltip("Fighters in pools larger or small than this size 
					will have their scores scaled to make all pools fair."); ?>

		<select name='updateTournament[normalizePoolSize]'
			id='normalizePoolSize_select<?=$tournamentID?>'>
			<option value='0'>Auto</option>
			<?php for($i = 2; $i <= POOL_SIZE_LIMIT; $i++):
				$selected = isSelected($i, $normSize);
				?>
				
				<option value=<?=$i?> <?=$selected?>><?=$i?></option>
			<?php endfor ?>
		</select>			

	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentPoolWinners($tournamentID = 'new'){
// Select menu for the number of pool winners to rank ahead of non-pool winners.
// Appears or disapears as controled by javascript.
	
	$display = "hidden"; 			// Hidden for most cases
	$normSize = null;
	$numWinners = 0;

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT poolWinnersFirst
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$numWinners = mysqlQuery($sql, SINGLE, 'poolWinnersFirst');

		$sql = "SELECT formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$formatID = mysqlQuery($sql, SINGLE,'formatID');
		
		if($formatID = FORMAT_MATCH ){
			$display = '';
		}
		
	}
	
	?>

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='poolWinnersFirst_div<?=$tournamentID?>'>
			
		Sort Pool Winners First
		<?php tooltip("Using this option the top fighters in each pool will all
		be ranked at the top, even if a non pool-winner has a higher score."); ?>

		<select name='updateTournament[poolWinnersFirst]'
			id='poolWinnersFirst_select<?=$tournamentID?>'>
			<option value='0'>No (Rank by Score)</option>
			<?php for($i = 1; $i <= (POOL_SIZE_LIMIT-1); $i++): ?>
				<option <?=optionValue($i,$numWinners)?> >Top <?=$i?> from pool</option>
			<?php endfor ?>
		</select>			

	</div>
	
<?php }

/***********************************************************(******************/

function edit_tournamentColors($tournamentID = 'new', $num){
// Select menu for the fighter colors. Called for fighter 1 and 2 depending
// on the value of $num.
// Appears or disapears as controled by javascript.
	
	if($num != 1 AND $num != 2){ return; }
	
	$display = "hidden"; // Hidden for most cases
	$currentID = '';
	$colors = getColors();
	
	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$formatID = mysqlQuery($sql, SINGLE, 'formatID');
		
		if($formatID == FORMAT_MATCH){

			$display = '';
			
			$sql = "SELECT color{$num}ID, colorName
					FROM eventTournaments
					INNER JOIN systemColors ON color{$num}ID = colorID
					WHERE tournamentID = {$tournamentID}";
			$color = mysqlQuery($sql, SINGLE);

			$currentName = $color['colorName'];
			$currentID = $color["color{$num}ID"];
			
		}	
	} elseif ($tournamentID == 'new'){

		$eventID = $_SESSION['eventID'];
		
		$sql = "SELECT color{$num}ID, colorName
				FROM eventDefaults
				INNER JOIN systemColors ON color{$num}ID = colorID
				WHERE eventID = {$eventID}";
		$color = mysqlQuery($sql, SINGLE);
		
		if($color != null){
			$currentName = $color['colorName'];
			$currentID = $color["color{$num}ID"];
		} else {
			if($num == 1){
				$currentName = DEFAULT_COLOR_NAME_1;
				$currentID = DEFAULT_COLOR_CODE_1;
			} elseif($num == 2) {
				$currentName = DEFAULT_COLOR_NAME_2;
				$currentID = DEFAULT_COLOR_CODE_2;
			}
		}
	}
	?>
	
<!-- Start display -->	
	<div class='medium-6 large-3 cell tournament-edit-box  <?=$display?>' 
		id='color<?=$num?>_div<?=$tournamentID?>'>
			
		Fighter <?=$num?> Color
		
		<select name='updateTournament[color<?=$num?>ID]' 
			id='color<?=$num?>_select<?=$tournamentID?>'>
			
			<?php foreach($colors as $color):?>
				<option <?=optionValue($color['colorID'], $currentID)?> >
					<?=$color['colorName']?>
				</option>
			<?php endforeach ?>
		</select>
	</div>	
	
	
<?php }

/******************************************************************************/

function edit_tournamentTies($tournamentID = 'new'){
// Select menu for whether or not the tournament allows ties
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$formatID = getTournamentFormat($tournamentID);
		if($formatID == FORMAT_MATCH){
			$display = '';
			
			$sql = "SELECT allowTies
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$allowTies = (int)mysqlQuery($sql, SINGLE, 'allowTies');

		} else {
			$allowTies = 0;
		}

	} elseif($tournamentID == 'new') {
		$eventID = $_SESSION['eventID'];
		
		$sql = "SELECT allowTies
				FROM eventDefaults
				WHERE eventID = {$eventID}";
		$allowTies = (int)mysqlQuery($sql, SINGLE, 'allowTies');
		
	}
	$selected = isSelected(1 == $allowTies);
	
	?>
	

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='allowTies_div<?=$tournamentID?>' >
			
		Allow Ties	
		
		<select name='updateTournament[allowTies]'
			id='allowTies_select<?=$tournamentID?>'>
			<option value='0'>No</option>
			<option value='1' <?=$selected?>>Yes</option>
			
		</select>
		
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentTimerCountdown($tournamentID = 'new'){
// Select menu for whether or not the tournament allows ties
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed
	

	$formatID = getTournamentFormat($tournamentID);
	if($formatID == FORMAT_MATCH){
		$display = '';
	} else {
		$display = "hidden"; 
	}
	
	$timerCountdown = isTimerCountdown($tournamentID);
	$selected = isSelected(1 == $timerCountdown);
	
	?>
	

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='timerCountdown_div<?=$tournamentID?>' >
			
		Timer Mode	
		
		<select name='updateTournament[timerCountdown]'
			id='timerCountdown_select<?=$tournamentID?>'>
			<option value='0'>Count Up</option>
			<option value='1' <?=$selected?>>Count Down</option>
		</select>
		
	</div>
	
<?php }


/******************************************************************************/

function edit_tournamentStaffCheckin($tournamentID = 'new'){
// Select menu for whether or not the tournament allows ties
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases
	$checkInStaff = STAFF_CHECK_IN_NONE;

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$formatID = getTournamentFormat($tournamentID);
		if($formatID == FORMAT_MATCH){
			$display = '';
			
			$sql = "SELECT checkInStaff
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$checkInStaff = (int)mysqlQuery($sql, SINGLE, 'checkInStaff');

		}

	} 
	
	?>
	

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='checkInStaff_div<?=$tournamentID?>' >
			
		Check in Staff for Matches
		<?=tooltip("Use this if you want to keep track of your judging/table staff on a match by match basis.")?>	
		
		<select name='updateTournament[checkInStaff]'
			id='checkInStaff_select<?=$tournamentID?>'>
			<option <?=optionValue(STAFF_CHECK_IN_NONE,$checkInStaff)?> >No</option>
			<option <?=optionValue(STAFF_CHECK_IN_ALLOWED,$checkInStaff)?> >Optional</option>
			<option <?=optionValue(STAFF_CHECK_IN_MANDATORY,$checkInStaff)?> >Mandatory </option>
		</select>
		
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentRequireSignOff($tournamentID = 'new'){

	$display = "hidden"; // Hidden for most cases
	$requireSignOff = STAFF_CHECK_IN_NONE;

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$formatID = getTournamentFormat($tournamentID);
		if($formatID == FORMAT_MATCH){
			$display = '';
			
			$sql = "SELECT requireSignOff
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$requireSignOff = (int)mysqlQuery($sql, SINGLE, 'requireSignOff');

		}

	} 
	
	?>
	

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='requireSignOff_<?=$tournamentID?>' >
			
		Match Sign Off
		<?=tooltip("Use this to require fighters to sign off on their scores after every match.")?>	
		
		<select name='updateTournament[requireSignOff]' id='requireSignOff_<?=$tournamentID?>'>
			<option <?=optionValue(0,$requireSignOff)?> >No</option>
			<option <?=optionValue(1,$requireSignOff)?> >Yes</option>
		</select>
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentReverseScore($tournamentID = 'new'){
// Select menu for whether or not the tournament uses reverse scores,
// if points are entered to the fighter who got hit rather than 
// the fighter who hits
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a box to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases
	$isReverseScore = REVERSE_SCORE_NO;

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$formatID = getTournamentFormat($tournamentID);
		if($formatID == FORMAT_MATCH || $formatID == FORMAT_SOLO){
			
			$sql = "SELECT isReverseScore
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$isReverseScore = (int)mysqlQuery($sql, SINGLE, 'isReverseScore');
		}
		$display = '';

	} elseif($tournamentID == 'new') {
		// Not used
	}

	?>
	
<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='reverseScore_div<?=$tournamentID?>' >
			
		Use Reverse Score
		<?php tooltip("<strong>Reverse Points</strong><BR>
						<u>Golf Score</u> - Fighters gain points when they are hit. Low score is good.<BR>
						<u>Injury Score</u> - Negative points are applied to the fighter who recieves a hit"); ?>
		
		<select name='updateTournament[isReverseScore]'
			onchange="enableTournamentButton('<?=$tournamentID?>')"
			id='reverseScore_select<?=$tournamentID?>'>
			
			<option <?=optionValue(0,$isReverseScore)?> >No (Normal)</option>
			<option <?=optionValue(1,$isReverseScore)?> >Golf Score</option>
			<option <?=optionValue(2,$isReverseScore)?> >Injury Score</option>
			
		</select>
		
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentOverrideDoubles($tournamentID = 'new'){
// Select menu for whether or not the tournament uses overdides 
// the default double hit behavior.
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a box to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases
	$isOverrideDoubles = 0;

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		if(isFullAfterblow($tournamentID) && (getTournamentFormat($tournamentID) == FORMAT_MATCH)){
			
			$sql = "SELECT overrideDoubleType
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$isOverrideDoubles = (int)mysqlQuery($sql, SINGLE, 'overrideDoubleType');

			$display = '';
		}

	} elseif($tournamentID == 'new') {
		// Not used
	}

	?>
	
<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='overrideDoubles_div<?=$tournamentID?>' >
			
		Enable Doubles
		<?php tooltip("Enables double hits in Full Afterblow Scoring"); ?>
		
		<select name='updateTournament[overrideDoubleType]'
			onchange="enableTournamentButton('<?=$tournamentID?>')"
			id='overrideDoubles_select<?=$tournamentID?>'>
			
			<option <?=optionValue(0,$isOverrideDoubles)?> >No (Normal)</option>
			<option <?=optionValue(1,$isOverrideDoubles)?> >Yes</option>
			
		</select>
		
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentNetScore($tournamentID = 'new'){
// Select menu for whether or not the tournament uses net score for Full Afterblow
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a box to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases
	$noNetScore = null;
	$nullOptionSelected = '';

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		if(getTournamentFormat($tournamentID) == FORMAT_MATCH){
			
			$sql = "SELECT isNotNetScore
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$noNetScore = (int)mysqlQuery($sql, SINGLE, 'isNotNetScore');

			$doubleType = getDoubleTypes($tournamentID);
			if($doubleType['afterblowType'] == 'full'){
				$display = '';
			}


		}
	} elseif($tournamentID == 'new') {

		$nullOptionSelected = 'selected';
	}

	?>
	
<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='notNetScore_div<?=$tournamentID?>' >
			
		Use Net Points
		<?php tooltip("<strong>Net Points</strong><BR>
						Only the higher scoring fighter recieves points.<BR>
						[High Score] - [Low Score]<BR><BR>
						<strong>No Net Points</strong><BR>
						Both fighters recieve their score"); ?>
		
		<select name='updateTournament[isNotNetScore]'
			onchange="enableTournamentButton('<?=$tournamentID?>')"
			id='notNetScore_select<?=$tournamentID?>'>
			
			<option <?=$nullOptionSelected?> disabled></option>
			<option <?=optionValue(0,$noNetScore);?> >Yes</option>
			<option <?=optionValue(1,$noNetScore);?> >No</option>
			
		</select>
		
	</div>
	
<?php }

/**********************************************************(*******************/

function edit_tournamentCuttingQual($tournamentID = 'new'){
// Select menu for whether or not the tournament has a cutting qualification
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases
	$isQual = null;

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$formatID = getTournamentFormat($tournamentID);
		if($formatID == FORMAT_MATCH || $formatID == FORMAT_SOLO){
			$display = '';
			
			$sql = "SELECT isCuttingQual
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$isQual = (int)mysqlQuery($sql, SINGLE, 'isCuttingQual');

		}
	}
	$selected = isSelected(1 == $isQual);	
	?>
	

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='isCuttingQual_div<?=$tournamentID?>' >
			
		Cutting Qualification
		
		<select name='updateTournament[isCuttingQual]'
			id='isCuttingQual_select<?=$tournamentID?>'>
			<option value='0'>No</option>
			<option value='1' <?=$selected?>>Yes</option>
			
		</select>
		
	</div>
	
<?php }

/*****************************************************************************/

function edit_tournamentKeepPrivate($tournamentID = 'new'){
// Select menu for whether or not the software should warn people the event
// organizer would rather not have results posted or added to stuff like HEMA Ratings
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed
	

	$display = '';
	$isPrivate = null;

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$formatID = getTournamentFormat($tournamentID);
		if($formatID == FORMAT_MATCH || $formatID == FORMAT_SOLO){
			$display = '';
			
			$sql = "SELECT isPrivate
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$isPrivate = (int)mysqlQuery($sql, SINGLE, 'isPrivate');

		}
	}
	$selected = isSelected(1 == $isPrivate);	
	?>
	

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='isPrivate_div<?=$tournamentID?>' >
			
		Sharing Preference <?=tooltip("
			This expresses your preference for your data being used by organizations like HEMA Ratings.
			<BR><strong>YOU HAVE ALREADY AGREED THAT THIS INFORMATION IS PUBLIC</strong>
			<BR>This just expresses your preference. How people use the information is up to them.")?>
		
		<select name='updateTournament[isPrivate]'
			id='isPrivate_select<?=$tournamentID?>'>
			<option value='0'>Normal</option>
			<option value='1' <?=$selected?>>I prefer if people don't use.</option>
		</select>
		
	</div>
	
<?php }

/*****************************************************************************/

function edit_tournamentHideFinalResults($tournamentID = 'new'){

	$hideFinalResults = null;

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT hideFinalResults
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$hideFinalResults = (bool)mysqlQuery($sql, SINGLE, 'hideFinalResults');

	}
	?>
	

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box' 
		id='hideFinalResults_div<?=$tournamentID?>' >
			
		Show Final Results<?=tooltip("Disable this to not show the overall tournament results.<BR>
			<BR>eg: Not showing the final results of tournaments that 
			are components of a meta-tournament.")?>
		
		<select name='updateTournament[hideFinalResults]'
			id='hideFinalResults_select<?=$tournamentID?>'>
			<option <?=optionValue(0,$hideFinalResults)?> >Yes (normal)</option>
			<option <?=optionValue(1,$hideFinalResults)?> >No (hide them)</option>
		</select>
		
	</div>
	
<?php }

/*****************************************************************************/

function edit_tournamentTeams($tournamentID = 'new'){
// Select if the tournament is a team event
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed
	

	$isTeams = 0;
	$display = 'hidden';
	$mode = '';
	if($tournamentID != 'new' && (int)$tournamentID > 0){
		
		$sql = "SELECT isTeams
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$isTeams = (int)mysqlQuery($sql, SINGLE, 'isTeams');

		if($isTeams){
			$mode = getTournamentLogic($tournamentID);
			$display = '';
		}

	}

	$selected = isSelected(1 == $isTeams);	
	?>
	
<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box' 
		id='isTeams_div<?=$tournamentID?>' onchange="enableTournamentButton('<?=$tournamentID?>')">
		Team Based Event
		
		<select name='updateTournament[isTeams]'
			id='isTeams_select<?=$tournamentID?>'>
			<option value='0'>No</option>
			<option value='1' <?=$selected?>>Team Event</option>
		</select>
		
	</div>

<!-- Start display -->
	
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='teamLogic_div<?=$tournamentID?>'>
		Team Mode<?=tooltip("<u>Team vs Team</u><BR>Whole teams fight each other
				<BR><u>Solo</u><BR>Treated as an individual tournament with team points tabulated.
				<BR><u>All vs All</u><BR>Each team member faces each member of every other team individually.")?>
		
		<select name='updateTournament[logicMode]'
			id='teamLogic_select<?=$tournamentID?>'>
			<option value='NULL'>Team vs Team</option>
			<option <?=optionValue('team_Solo',$mode)?> >Solo</option>
			<option <?=optionValue('team_AllVsAll',$mode)?> >All vs All</option>
			
		</select>
		
	</div>
	
	
<?php }

/****************************************************(*************************/

function edit_tournamentMaxExchanges($tournamentID = 'new'){
// Select menu for whether or not the tournament allows ties
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases

	$maxExchanges = 0;
	if($tournamentID != 'new' && (int)$tournamentID > 0){

		if(getTournamentFormat($tournamentID) == FORMAT_MATCH){
			$display = '';
			
			$sql = "SELECT maximumExchanges
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$maxExchanges = mysqlQuery($sql, SINGLE, 'maximumExchanges');

		}
	} elseif($tournamentID == 'new') {
		
		// Pull from event defaults
		
	}

	if($maxExchanges == 0){
		$maxExchanges = '';
	}
	?>
	


<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='maxExchanges_div<?=$tournamentID?>' >
			
		Maximum Exchanges
		<?php tooltip("Match will automaticaly conclude after this number is reached. <BR>
			<strong>Leave blank for unlimited.</strong><BR>
			Only counts scoring hits and doubles."); ?>
		<input type='number' name='updateTournament[maximumExchanges]' value='<?=$maxExchanges?>'
			placeholder='Unlimited' min=0 max=100 class='text-center'>
	</div>
	
<?php }

/****************************************************(*************************/

function edit_tournamentMaxPoints($tournamentID = 'new'){
// Select menu for whether or not the tournament allows ties
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases

	$maxPoints = 0;
	if($tournamentID != 'new' && (int)$tournamentID > 0){

		if(getTournamentFormat($tournamentID) == FORMAT_MATCH){
			$display = '';
			
			$sql = "SELECT maximumPoints
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$maxPoints = mysqlQuery($sql, SINGLE, 'maximumPoints');

		}
	} elseif($tournamentID == 'new') {
		// Pull from event defaults
	}

	if($maxPoints == 0){
		$maxPoints = '';
	}
	?>
	
<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='maxPoints_div<?=$tournamentID?>' >
			
		Maximum Points
		<?php tooltip("Match will automaticaly conclude after this number is reached. <BR>
			<strong>Leave blank for unlimited.</strong>"); ?>
		<input type='number' name='updateTournament[maximumPoints]' value='<?=$maxPoints?>'
			placeholder='Unlimited' min=0 max=100 class='text-center'>
	</div>
	
<?php }

/****************************************************(*************************/

function edit_tournamentMaxPointSpread($tournamentID = 'new'){
// Select menu for whether or not the tournament allows ties
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases
	$pointSpreadLimit = 20; // Arbitrary

	$maxPointSpread = 0;
	if($tournamentID != 'new' && (int)$tournamentID > 0){

		if(getTournamentFormat($tournamentID) == FORMAT_MATCH){
			$display = '';
			
			$sql = "SELECT maxPointSpread
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$maxPointSpread = (int)mysqlQuery($sql, SINGLE, 'maxPointSpread');

		}
	}

	?>
	
<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='maxPointSpread_div<?=$tournamentID?>' >
			
		Maximum Points Spread
		<?php tooltip("Match will automaticaly conclude after this number is reached. <BR>
			<strong>Leave blank for unlimited.</strong>"); ?>
		<select name='updateTournament[maxPointSpread]' 
			id='maxPointSpread_select<?=$tournamentID?>'>
			
			<option <?=optionValue(0,$maxPointSpread)?> >Unlimited</option>

			<?php for($i = 1; $i <= $pointSpreadLimit; $i++):?>	
				<option <?=optionValue($i,$maxPointSpread)?> ><?=$i?></option>
			<?php endfor ?>


		</select>	
	</div>
	
<?php }

/****************************************************(*************************/

function edit_tournamentTimeLimit($tournamentID = 'new'){
// Select menu for whether or not the tournament allows ties
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases

	$timeLimit = 0;
	if($tournamentID != 'new' && (int)$tournamentID > 0){

		if(getTournamentFormat($tournamentID) == FORMAT_MATCH){
			$display = '';
			
			$sql = "SELECT timeLimit
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$timeLimit = mysqlQuery($sql, SINGLE, 'timeLimit');

		}
	} elseif($tournamentID == 'new') {
		// Pull from event defaults
	}

	if($timeLimit == 0){
		$timeLimit = '';
	}
	?>
	
<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='timeLimit_div<?=$tournamentID?>' >
			
		Time Limit [seconds]
		<?php tooltip("Match will automaticaly conclude after this time is reached. <BR>
			<strong>Leave blank for unlimited.</strong>"); ?>
		<input type='number' name='updateTournament[timeLimit]' value='<?=$timeLimit?>'
			placeholder='Unlimited' min=0 max=300 class='text-center'>
	</div>
	
<?php }

/*********************************************************(********************/

function tooltip($text, $tip = "<img src='includes/images/help.png'>", $dir='bottom'){
// Creates a tooltip that displays as $tip containing $text
// Defaults to displaying a help icon
	?>

	<?php if($tip == null): ?>
		<img src='includes/images/help.png'>
	<?php endif ?>

	
	<span data-tooltip aria-haspopup='true' class='has-tip' 
		data-disable-hover='false' tabindex='2' title="<?=$text?>"
		data-position='<?=$dir?>' data-allow-html='true' >
		
		<?=$tip?>
		
	</span>
	
<?php }

/******************************************************************************/

function poolSetNavigation($displayByPoolsButton = false){
// Buttons to navigate between pool sets, only display if it is a pool set tournament	
	
	
// Check that the tournament has pool sets
	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){
	 displayAlert('No Tournament selected for poolSetNavigation in display_functions.php');
		return;
	}
	
	$numGroupSets = getNumGroupSets($tournamentID);

	if($displayByPoolsButton == true){
		$sql = "SELECT COUNT(*) AS numNull
				FROM eventStandings
				WHERE tournamentID = {$tournamentID}
				AND groupID IS NULL";
		$numNull = mysqlQuery($sql, SINGLE, 'numNull');


		if($numNull != 0){
			$_SESSION['displayByPool'] = false;
			$displayByPoolsButton = false;
		} elseif($_SESSION['displayByPool'] == false){
			$displayByPoolText = "Display by Pool";
		} else {
			$displayByPoolText = "Display by Rank";
		}
	}

	?>
	
<!-- Start display -->
	<form method='POST' style='display:inline'>

	<?php if($numGroupSets > 1): ?>
		
		<input type='hidden' name='formName' value='changePoolSet'>
		
			<?php for($i = 1; $i <= $numGroupSets; $i++):
				
				if($i == $_SESSION['groupSet'] || !isset($_SESSION['groupSet'])){ 
					$selected = null;
				} else { 
					$selected = 'hollow'; 
				}
				$name = getSetName($i, $tournamentID);
				?>
				
				<button class='button <?=$selected?> secondary' 
					name='groupSet' value='<?=$i?>'>
					<?=$name?>
				</button>
			<?php endfor ?>
	<?php endif ?>

	<?php if($displayByPoolsButton == true): ?>
		<button class='button hollow float-right' name='formName' value='displayByPoolsToggle'>
				<?=$displayByPoolText?>
		</button>
	<?php endif ?>

	</form>
	
<?php 
}

/******************************************************************************/

function matchHistoryBar($matchInfo){
// displays all the fight's past exchanges


// Getting info and formating for summary
	$matchID = $matchInfo['matchID'];
	$exchangeInfo = getMatchExchanges($matchID);

	if(count($exchangeInfo) == 0){
		return;
	}
	$doubleTypes = getDoubleTypes();

	$i=0;
	$doubles = 0;
	$exchanges = array();
	
	$colorCode1 = COLOR_CODE_1;
	$colorCode2 = COLOR_CODE_2;
	$isZeroNumberedExchanges = false;
	
	foreach($exchangeInfo as $exchange){
	// Check if there are old exchanges in the system which don't have an exchange order assigned.
		if($exchange['exchangeNumber'] == 0){
			$isZeroNumberedExchanges = true;
		}


	// Create a list of exchanges with appropriate text for each
		$i++;
		if($exchange['exchangeTime'] > 0){
			$t = $exchange['exchangeTime'];

			$m = (int)($t/60);
			$s = $t - ($m * 60);
			if($s < 10){
				$s = "0".$s;
			}

			$exchanges[$i]['time'] = "{$m}:{$s}";
		} else {
			$exchanges[$i]['time'] = '';
		}
		

		if($exchange['rosterID'] == $matchInfo['fighter1ID']){
			$index1 = 1;
			$index2 = 2; 
		} else {
			$index1 = 2;
			$index2 = 1; 
		}
		
		if((isReverseScore($matchInfo['tournamentID']) > REVERSE_SCORE_NO)
			&& ($exchange['exchangeType'] == 'clean' 
				|| $exchange['exchangeType'] == 'afterblow')){
			$temp = $index1;
			$index1 = $index2;
			$index2 = $temp;
			if(isReverseScore($matchInfo['tournamentID']) == REVERSE_SCORE_INJURY){
				$exchange['scoreValue'] *= -1;
				$exchange['scoreDeduction'] *= -1;
			}
		}

		$exchanges[$i][1][1] = '';
		$exchanges[$i][1][2] = '';
		$exchanges[$i][2][1] = '';
		$exchanges[$i][2][2] = '';

		switch ($exchange['exchangeType']){
			case "doubleOut":
				$exchanges[$i][1][1] = "<em>D/</em>";
				$exchanges[$i][1][2] = "<em>Out</em>";
				$exchanges[$i][2][1] = $exchanges[$i][1][1];
				$exchanges[$i][2][2] = $exchanges[$i][1][2];
				break;

			case "tie":
				$exchanges[$i][1][2] = "<em>Tie</em>";
				$exchanges[$i][2][2] = $exchanges[$i][1][2];
				break;

			case "winner":
				$exchanges[$i][$index1][2] = "<em>Win</em>";
				break;

			case "penalty":
				$exchanges[$i][$index1][1] = "<b>P</b>";
				$exchanges[$i][$index1][2] = "<b>".$exchange['scoreValue']."</b>";

				if($exchange['refType'] != null || $exchange['refTarget'] != null){
					$penalties[$i] = getPenaltyInfo($exchange['exchangeID']); 
				}

				break;

			case "noQuality":
				$exchanges[$i][$index1][1] = "<b>No</b>";
				$exchanges[$i][$index1][2] = "<b>Q</b>";

				break;

			case "double":
				$doubles++;
				$exchanges[$i][1][1] = "<b>D</b>";
				$exchanges[$i][1][2] = "<b>#".$doubles."</b>";
				$exchanges[$i][2][1] = $exchanges[$i][1][1];
				$exchanges[$i][2][2] = $exchanges[$i][1][2];
				break;
				
			case "noExchange":
				$exchanges[$i][1][2] = "<b>/</b>";
				$exchanges[$i][2][1] = "<b>/</b>";
				break;
				
			case "afterblow":
				if($doubleTypes['afterblowType'] == 'deductive'){	

					$exchanges[$i][$index1][1] = "<b>".$exchange['scoreValue']."</b>";
					$exchanges[$i][$index1][2] = "(".(-$exchange['scoreDeduction']).")";	

				} else {

					if ($doubleTypes['isNotNetScore'] == 0){

						$exchanges[$i][$index1][1] = "<b>".($exchange['scoreValue'] - $exchange['scoreDeduction'])."</b>";
						$exchanges[$i][$index1][2] = "(".$exchange['scoreValue'].")";
						$exchanges[$i][$index2][2] = "(".$exchange['scoreDeduction'].")";

						if(($exchange['scoreValue'] - $exchange['scoreDeduction']) == 0){
							$exchanges[$i][$index1][1] = '';
						}
						
					} else {

						$exchanges[$i][$index1][1] = "<b>".$exchange['scoreValue']."</b>";
						$exchanges[$i][$index2][1] = "<b>".$exchange['scoreDeduction']."</b>";

					}

				}
				break;
				
			case "clean":
				$exchanges[$i][$index1][1] = "<b>".$exchange['scoreValue']."</b>";

				break;
			default:
				break;
		}


	}

/* Function to display each exchange on regular screens ***/
	function displayExchangeReg($exchange, $num = null, $background = null){
		$colorCode1 = COLOR_CODE_1;
		$colorCode2 = COLOR_CODE_2;
		
		$t1 = $exchange[1][1];
		if($t1 == null){$t1 = "&nbsp;";}
		$t2 = $exchange[1][2];
		if($t2 == null){$t2 = "&nbsp;";}
		$b1 = $exchange[2][1];
		if($b1 == null){$b1 = "&nbsp;";}
		$b2 = $exchange[2][2];
		if($b2 == null){$b2 = "&nbsp;";}

		if($exchange['time'] == ''){
			$exchange['time'] = "0:00";
		}	
		
		$class = '';
		$odd = '';
		if($num % 2 != 1){ 
			$class= 'old-exch-odd'; 
		} else {
			$odd= "opacity:0.92;";
		}

		$back1 = 'f1-BG';
		$back2 = 'f2-BG';
		if($background != null){
			$back1 = $background;
			$back2 = $background;
		}

		
		?>	
		
		<div class='shrink text-center' style='width: 40px'>
			<div class='cell <?=$class?>'>
				<?=$exchange['time']?>
			</div>
			<div class='cell <?=$back1?>' style='<?=$odd?>'>
				<?=$t1?><BR><?=$t2?>
			</div>
			<div class='cell <?=$back2?>' style='<?=$odd?>'>
				<?=$b1?><BR><?=$b2?>
			</div>
		</div>
		
		<?php
	}
	
/* Function to display each exchange on small screens ***/
	function displayExchangeSmall($exchange, $num = null, $background = null){
		$colorCode1 = COLOR_CODE_1;
		$colorCode2 = COLOR_CODE_2;
		
		$t1 = $exchange[1][1];
		if($t1 == null){$t1 = "&nbsp;";}
		$t2 = $exchange[1][2];
		if($t2 == null){$t2 = "&nbsp;";}
		$b1 = $exchange[2][1];
		if($b1 == null){$b1 = "&nbsp;";}
		$b2 = $exchange[2][2];
		if($b2 == null){$b2 = "&nbsp;";}
		
		if($exchange['time'] == ''){
			$exchange['time'] = "0:00";
		}	

		$class = '';
		$odd = '';
		if($num % 2 != 1){ 
			$class= 'old-exch-odd'; 
		} else {
			$odd= "opacity:0.92;";
		}

		$back1 = 'f1-BG';
		$back2 = 'f2-BG';
		if($background != null){
			$back1 = $background;
			$back2 = $background;
		}
		
		?>	
		
		<tr class='old-exch-mini'>
			<td class='<?=$class?>'>
				<?=$exchange['time']?>
			</td>
			<td class='<?=$back1?>' style='<?=$odd?>'>
				<?=$t1?>
			</td>
			<td class='<?=$back1?>' style='<?=$odd?>'>
				<?=$t2?>
			</td>
			<td class='<?=$back2?>' style='<?=$odd?>'>
				<?=$b1?>
			</td>
			<td class='<?=$back2?>' style='<?=$odd?>'>
				<?=$b2?>
			</td>
		</tr>
	
		<?php
	}
	
/***************************************************/

?>		

<!-- Normal size fight history -->
	<div class='large-12 cell black-border hide-for-small-only'>	
	<div class='grid-x grid-padding-x'>
		<?php foreach($exchanges as $num => $exchange){

			switch(@$penalties[$num]['card']){
				case 'yellowCard':
					$penaltyColor = 'penalty-card-yellow';
					break;
				case 'redCard':
					$penaltyColor = 'penalty-card-red';
					break;
				case 'blackCard':
					$penaltyColor = 'penalty-card-black';
					break;
				default:
					$penaltyColor = null;
					break;
			}
		
			displayExchangeReg($exchange, $num, $penaltyColor);
		} ?>
	</div>
	</div>
	
<!-- Small size fight history -->

	<div class='large-12 cell show-for-small-only'>
	<table>
	<caption>Match Exchanges</caption>	
	<?php foreach($exchanges as $num => $exchange){

			switch(@$penalties[$num]['card']){
				case 'yellowCard':
					$penaltyColor = 'penalty-card-yellow';
					break;
				case 'redCard':
					$penaltyColor = 'penalty-card-red';
					break;
				case 'blackCard':
					$penaltyColor = 'penalty-card-black';
					break;
				default:
					$penaltyColor = null;
					break;
			}

		displayExchangeSmall($exchange, $num, $penaltyColor);
	} ?>
	</table>
	</div>

	<?php if(isset($penalties) == true):?>
		<div class='small-12 cell'>

			<ul>
			<h5>Penalties:</h5>

			<?php foreach($penalties as $penalty): ?>
				<li>
					<strong><?=getFighterName($penalty['rosterID'])?></strong>
					<?php if($penalty['name'] != ''){echo "[".$penalty['name']."]";}?>
					: <em><?=$penalty['action']?></em>
				</li>
			<?php endforeach ?>
			</ul>

		</div>
	<?php endif ?>

	


<?php 
	return $isZeroNumberedExchanges;

}


/******************************************************************************/

function goToMatchButton($matchInfo){
// Creates a button to navigate to a match
// If referencing an empty bracket match it becomes an 'add' button

	$matchID = $matchInfo['matchID'];
	
	?>
	
	
<!-- If a matchID was passed instead of $matchInfo data it is a simple button -->
	<?php if(is_int($matchInfo)):
		$matchID = $matchInfo;
		?>
		
		<form method='POST'>
			<input type='hidden' name='formName' value='goToMatch'>
			<button class='button hollow tiny' value='<?=$matchID?>' name='matchID'>Go</button>
		</form>";
		
	<?php return;
	endif; ?>	

<!-- If a match with both fighters was passed -->	
	<?php if($matchInfo['fighter1ID'] != null && $matchInfo['fighter2ID'] != null): ?>
		
		<button class='button hollow tiny no-bottom' name='goToMatch' value='<?=$matchID?>'>Go</button>

<!-- If an unfiled match was passed -->
	<?php else: ?>
	
		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
			<button class='button success hollow tiny' style='margin-bottom: 5px;' 
				name='updateBracket' value='newFighters' <?=LOCK_TOURNAMENT?>>
				&#10004;
			</button>
		<?php else: ?>
			<BR>
		<?php endif ?>

	<?php endif ?>
	
	<!-- Checkbox for staff to delete fighters from a match -->
	<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
		<input type='checkbox' class='no-bottom'  name='selectedBracketMatches[matchIDs][<?=$matchID?>]' 
			value='true' <?=LOCK_TOURNAMENT?>>
	<?php endif ?>
	
	
<?php }

/******************************************************************************/

function addYoutube($matchID, $divider = true){
// Displays youtube link for match if it exists, and allows staff to add links

	$url = getYouTube($matchID);
?>

<!-- Display entry field for staff -->
	<?php if(ALLOW['EVENT_YOUTUBE'] == true): ?>
		<?php if($divider):?>
			<HR width='80%'>
		<?php endif ?>
		<form method='POST'>
		<input type='hidden' name='matchID' value='<?=$matchID?>'>
		<div class='input-group grid-x'>
			<span class='input-group-label large-2 medium-3 small-12 text-center'>YouTube URL:</span>
			<input class='input-group-field' type='url' name='url' value='<?=$url?>' 
				id='youtubeField' onkeyup="validateYoutube()"  placeholder='Include https://'>
			<button name='formName'  value='YouTubeLink' disabled
				class='button success input-group-button hide-for-small-only youtubeSubmitButton'>
				 Update Link
			</button>
			<button class='button success expanded show-for-small-only youtubeSubmitButton' 
			name='formName' value='YouTubeLink' disabled >Update Link</button>
		</form>
		</div>
		
		
<!-- Displays bare link for guests, if one exists -->
	<?php elseif($url != null) : ?>
		<?php if($divider):?>
			<HR width='80%'>
		<?php endif ?>
		<strong>YouTube Link:</strong>
		<a href='<?=$url?>'><?=$url?></a>
		
	<?php endif ?>
	
<?php }

/******************************************************************************/

function displayIncompleteMatches($incompleteMatches){
// Displays incomplete matches from a tournament
// Used to show staff which events are not complete if they need to 
// close them all to enable the bracket helper	
	
	if(count($incompleteMatches) < 1){
		return;
	}

	?>
	
	<form method='POST'>
	<input type='hidden' name='formName' value='goToMatch'>
	
	The following pool matches are incomplete:<BR>
	<?php foreach($incompleteMatches as $matchID):
		$matchInfo = getMatchInfo($matchID);
		$name1 = getFighterName($matchInfo['fighter1ID'],null,null,$matchInfo['teamEntry']);
		$name2 = getFighterName($matchInfo['fighter2ID'],null,null,$matchInfo['teamEntry']);
		$poolName = getGroupName($matchInfo['groupID']);
		?>
		
		
		<button class='button no-bottom' name='matchID' value='<?=$matchID?>'>
			<?=$poolName?> - <?=$name1?> vs <?=$name2?>
		</button>
	
	<?php endforeach ?>
	
	</form>
	
	<?php unset($_SESSION['incompletePoolMatches']); ?>
<?php }

/******************************************************************************/

function show_poolGeneration($fighterID,$poolPoints,$sizePoints,
							$ratingPoints,$schoolPoints,$refightPoints){
// This function is used to calibrate the pool auto-generation feature
// It shows the progress of every step in the pool generation, and what the
// weighted values are for each attribute to consider.
// THIS SHOULD NOT BE USED IN PRODUCTION

	$info = getFighterInfo($fighterID);
	echo "<BR><BR><h4>Adding <strong class='red-text'>{$info['name']}</strong> 
		from <strong>{$info['schoolName']}</strong></h4>";

	echo "<BR><u>Algorithm Scoring</u>";
	echo "<table>";
	echo "<tr><th></th>
		<th>Pool Size Score</th>
		<th>Rating Score</th>
		<th>Same-School Score</th>
		<th>Num Refights Score</th>
		<th>TOTAL SCORE</th></tr>";
	foreach($poolPoints as $poolNum => $numPoints){
		$size = round($sizePoints[$poolNum],2);
		$rating = round($ratingPoints[$poolNum],2);
		$school = round($schoolPoints[$poolNum],2);
		$refight = round($refightPoints[$poolNum],2);
		$total = round($numPoints,2);

		echo "<tr><th>Pool {$poolNum}</th>";
		echo "<td>{$size}</td>";
		echo "<td>{$rating}</td>";
		echo "<td>{$school}</td>";
		echo "<td>{$refight}</td>";
		echo "<td>{$total}</td>";
		echo "</tr>";
	}
	echo "</table>";

	echo "<u>Pool Rosters</u>";
	echo "<table>";
	foreach($_SESSION['poolSeeds'] as $poolNum => $poolRoster){
		echo "<tr><th>Pool {$poolNum}</th>";
		foreach($poolRoster as $rosterID){
			$name = getFighterName($rosterID);
			if($rosterID == $fighterID){
				echo "<td><strong class='red-text'>{$name}</strong></td>";
			}else{
				echo "<td>{$name}</td>";
			}
	
		}
		echo "</tr>";
	}
	echo "</table>";
	echo "<HR>";

}

/******************************************************************************/

function autoFinalizeSpecificationBox($tournamentID){

	if(getTournamentFormat($tournamentID) != FORMAT_MATCH
		|| isBrackets($tournamentID) == false ){
		return;

	}

	$tournamentID = (int)$tournamentID;

?>

	<div class='reveal tiny' id='autoFinalizeBox-<?=$tournamentID?>' data-reveal>
		<h4>Auto Finalize Options</h4>

		<?= autoFinalizeBracketForm($tournamentID) ?>

	<!-- Reveal close button -->
	<button class='close-button' data-close aria-label='Close modal' type='button'>
		<span aria-hidden='true'>&times;</span>
	</button>

	</div>


<?php
}

/******************************************************************************/

function autoFinalizeBracketForm($tournamentID){

	$sql = "SELECT numSubMatches
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$numSubMatches = (int)mysqlQuery($sql, SINGLE, 'numSubMatches');

	if($numSubMatches != 0){
		$showSubMatchOption = true;
	} else {
		$showSubMatchOption = false;
	}
?>

	<form method='POST'>
		<input type='hidden' name='tournamentID' value='<?=$tournamentID?>'>

	<!-- Break Ties -->
		<div class='input-group'>
			<span class='input-group-label'>
				Use Tie-Breakers?
				<?=tooltip("Normaly results are generated in the format 'Top 8, Top 16, etc..<BR>
							Using a tie breaker will attempt to generate an ordered list using:
							<BR>1) Win Percentage (including sub-matches)
							<BR>2) Points +/-")?>
			</span>

			<div class='switch no-bottom input-group-field'>
				<input class='switch-input' type='hidden'  
					name='autoFinalizeSpecs[breakTies]' value=0>
				<input class='switch-input polar-disables' type='checkbox' id='breakTies' 
					name='autoFinalizeSpecs[breakTies]' value=1>
				<label class='switch-paddle' for='breakTies'>
				</label>
			</div>
		</div>

	<!-- Limit Sub Matches -->
		<?php if($showSubMatchOption == true): ?>
			<div class='input-group'>
				<span class='input-group-label'>
					<strong>Limit Sub-Match Calculations
						<?=tooltip("Limit the number of sub-matches used in the Points +/- calculations
								to the first <x>.
								<BR> For example you may want to only include the first 2 of 3 matches.")?>
					</strong>
				</span>
				<select class='input-group-field' 
					name='autoFinalizeSpecs[subMatchLimit]'> 
					<option value='0'></option>
					<?php for($i = 1;$i<=$numSubMatches;$i++): ?>
						<option value='<?=$i?>'>First <?=$i?> Match(es)</option>
					<?php endfor ?>

				</select>
			</div>
		<?php endif ?>


		<!-- Submit buttons -->
		<div class='grid-x grid-margin-x'>
			<button class='success button small-6 cell' name='formName' 
				value='autoFinalizeTournament'>
				Auto Finalize Tournament
			</button>
			<button class='secondary button small-6 cell' 
				data-close aria-label='Close modal' type='button'>
				Cancel
			</button>
		</div>

	</form>


<?php
}


/******************************************************************************/

function toggleClass($class,$text1,$text2 = null, $hide = false){
// $type uses jQuery syntax to call a class or id. By default this uses classes 
	if($text2 == null){
		$text2 = $text1;
	}

	if($hide == false){
		$hide1 = '';
		$hide2 = 'hidden';
	} else {
		$hide1 = 'hidden';
		$hide2 = '';
	}

?>

	<a onclick="$('.<?=$class?>').toggle()" class='<?=$class?> <?=$hide1?>'>
		<?=$text1?>
	</a>
	<a onclick="$('.<?=$class?>').toggle()" class='<?=$class?> <?=$hide2?>'>
		<?=$text2?>
	</a>

<?php
}

/******************************************************************************/

function notSetMark($isSet){
// Returns a x if not set a ✓ if set.
// Emphasizes if it is not set.

	if($isSet == false){
		$str = "<strong class='red-text'>✗</strong>";
	} else {
		$str = "<span class='grey-text'>✓</span>";
	}
	return $str;
}

/******************************************************************************/

function isSetMark($isSet){
// Returns a x if not set a ✓ if set.
// Emphasizes if it is not set.

	if($isSet == false){
		$str = "<span class='grey-text'>_</span>";
	} else {
		$str = "<strong class='success-text'>✓</strong>";
	}
	return $str;
}

/******************************************************************************/

function selectCountry($name, $selected = null, $countryList = null, $classes = null){

	if($countryList == null){
		$countryList = getCountryList();
	}


	echo "<select name='{$name}' class='{$classes}' required>";
	
	if($selected == null){
		echo "<option selected disabled></option>";
	}

	foreach($countryList as $countryIso2 => $countryName){
		echo "<option value='{$countryIso2}'".isSelected($countryIso2, $selected).">";
		echo $countryName;
		echo "</option>";
	}

	echo "</select>";

}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
