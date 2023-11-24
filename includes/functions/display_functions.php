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
		displayAlert("<strong>Error: </strong>".$message,'alert');
	}
	$_SESSION['alertMessages']['userErrors'] = [];

	foreach((array)$_SESSION['alertMessages']['userWarnings'] as $message){
		displayAlert("<strong>Warning: </strong>".$message, 'warning');
	}
	$_SESSION['alertMessages']['userWarnings'] = [];

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
	} elseif($_SESSION['userName'] != 'eventOrganizer' || isEventTermsAccepted($_SESSION['eventID'])){
		return;

// If they need to sign ToS, kicks them back to the log in page.
// This won't let them leave the log-in screen until they sign the ToS or log out
	} elseif($pageName != 'adminLogIn.php'){
		refreshPage('adminLogIn.php');
	}

	$email = getEventEmail($_SESSION['eventID']);

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


function displayPenalty($penalty, $showPoints = false){

	switch($penalty['card']){
		case 'yellowCard':
			$class = 'penalty-card-yellow';
			break;
		case 'redCard':
			$class = 'penalty-card-red';
			break;
		case 'blackCard':
			$class = 'penalty-card-black';
			break;
		default:
			$class = '';
			break;
	}

?>
	<div class='<?=$class?> penalty-card-display'>

		<strong><?=$penalty['cardName']?>: </strong>
		<i><?=$penalty['action']?></i> (<?=$penalty['scoreValue']?>)
		<BR>
		<strong><?=getTournamentName($penalty['tournamentID'])?>: </strong>
		 <?=getGroupName($penalty['groupID'])?> (vs <?=getFighterName($penalty['receivingID'])?>)
	</div>


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

function edit_tournamentOptionsRow($name, $toggle = null, $tooltip = null){

	if($toggle != null){
		$name = "<a onclick=\"$('.".$toggle."').toggle()\">".$name."↓ </a>";
	}

?>
	<tr>
		<td colspan='100%' style='border-bottom:1px solid black; padding-top:1.5em'>
			<b>
				<?=$name?>
			</b>
			<?php if($tooltip != null){
				tooltip($tooltip);
			}?>
		</td>
	</tr>
<?php
}

/******************************************************************************/

function edit_tournamentName($tournamentID){
// Select boxes for editing a tournament name
// Select boxes for creation of a new tournament will be made if no
// tournamentID is passed to the function

	$tournamentID = (int)$tournamentID;
	$name = '';

//Read all valid attributes from the database
	$sql = "SELECT tournamentTypeID, tournamentType
			FROM systemTournaments
			WHERE tournamentTypeMeta = 'prefix'
			ORDER BY tournamentType ASC";
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

	// Weapon List
	$sql = "SELECT tournamentTypeID, tournamentType
			FROM systemTournaments
			WHERE tournamentTypeMeta = 'weapon'
			ORDER BY numberOfInstances DESC
			LIMIT 8";
	$weaponListPopular = mysqlQuery($sql, KEY_SINGLES, 'tournamentTypeID', 'tournamentType');

	$sql = "SELECT tournamentTypeID, tournamentType
			FROM systemTournaments
			WHERE tournamentTypeMeta = 'weapon'
			ORDER BY tournamentType ASC";
	$weaponListName = mysqlQuery($sql, KEY_SINGLES, 'tournamentTypeID', 'tournamentType');

// Read the current attributes of the tournament
	if($tournamentID != 0){
		$sql = "SELECT tournamentID, tournamentWeaponID, tournamentPrefixID,
					tournamentGenderID, tournamentMaterialID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$currentSettings = mysqlQuery($sql, KEY, 'tournamentID');
		$tournamentName = getTournamentName($tournamentID);
	} else {
		$tournamentName = "<i>New Tournament</i>";
	}

?>

<!-- Begin Display --------------------------------------------->

<!-- Prefix ----------------------------------->
	<tr>
		<td class='shrink-column'>
			Division (Optional)
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[tournamentPrefixID]' class='shrink'
					id='prefixID_div<?=$tournamentID?>'>

					<?php foreach($prefixList as $ID => $name): ?>
						<option <?=optionValue($ID, @$currentSettings[$tournamentID]['tournamentPrefixID'])?> >
							<?=$name?>
						</option>
					<?php endforeach ?>
			</select>
			</div>
		</td>
	</tr>

<!-- Gender ----------------------------------->
	<tr>
		<td class='shrink-column'>
			Gender (Optional)
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[tournamentGenderID]' class='shrink'
					id='genderID_div<?=$tournamentID?>'>

					<?php foreach($genderList as $ID => $name): ?>
						<option <?=optionValue($ID, @$currentSettings[$tournamentID]['tournamentGenderID'])?> >
							<?=$name?>
						</option>
					<?php endforeach ?>
			</select>
			</div>
		</td>
	</tr>

<!-- Material ----------------------------------->
	<tr>
		<td class='shrink-column'>
			Material (Optional)
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[tournamentMaterialID]' class='shrink'
					id='materialID_div<?=$tournamentID?>'>

					<?php foreach($materialList as $ID => $name):?>
						<option <?=optionValue($ID, @$currentSettings[$tournamentID]['tournamentMaterialID'])?> >
							<?=$name?>
						</option>
					<?php endforeach ?>
			</select>
			</div>
		</td>
	</tr>

<!-- Weapon ----------------------------------->
	<tr>
		<td class='shrink-column'>
			Weapon
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[tournamentWeaponID]' class='shrink'
					id='weaponID_div<?=$tournamentID?>'>

					<?php if($tournamenID == 0):?>
						<option disabled selected> </option>
					<?php endif?>

					<option disabled>
						- Most Popular: ----------------
					</option>

					<?php foreach($weaponListPopular as $ID => $name): ?>
						<option <?=optionValue($ID, @$currentSettings[$tournamentID]['tournamentWeaponID'])?> >
							<?=$name?>
						</option>
					<?php endforeach ?>

					<option disabled>
						- By Name: ---------------------
					</option>

					<?php foreach($weaponListName as $ID => $name): ?>
						<option <?=optionValue($ID, @$currentSettings[$tournamentID]['tournamentWeaponID'])?> >
							<?=$name?>
						</option>
					<?php endforeach ?>
			</select>
			</div>
		</td>
	</tr>

<!-- Tournament Name ----------------------------------->
	<tr>
		<td class='shrink-column'>
			Tournament Name:
		</td>

		<td>
			<b>
			<?=$tournamentName?>
			</b>
		</td>
	</tr>

	<tr>
		<td colspan="100%">
			&nbsp;
		</td>

	</tr>

<?php }

/*****************************************************************************/

function edit_tournamentFormatType($tournamentID = 0){
// Select menu for the type of tournament

	$tournamentID = (int)$tournamentID;

	if($_SESSION['isMetaEvent'] == false){
		$sql = "SELECT formatID, formatName
				FROM systemFormats";
		$formatTypes = mysqlQuery($sql, KEY_SINGLES, 'formatID', 'formatName');
	} else {
		// If the event is a meta event then you are not able to create anything other than meta-event tournaments.
		$formatTypes[FORMAT_META] = 'Meta Event';
	}

	if($tournamentID !=  0){
		$sql = "SELECT formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$currentID = mysqlQuery($sql, SINGLE, 'formatID');
	}

?>

<!-- Start display -->
	<tr>
		<td class='shrink-column'>
			Tournament Type
			<?php tooltip("
				<u>Results Only</u> - Just the final placings.<BR>
				<u>Sparring</u> - People fight (normal tournament).<BR>
				<u>Solo</u> - Cutting tournaments, etc.<BR>
				<u>Composite</u> - Triathlons, etc."); ?>
		</td>

		<td>

			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[formatID]' class='shrink'
				onchange="edit_formatType('<?=$tournamentID?>')"
				id='formatID_select<?=$tournamentID?>'>

				<?php if($tournamentID == 0): ?>
					<option selected disabled></option>
				<?php endif ?>
				<?php foreach($formatTypes as $ID => $name): ?>
					<option <?=optionValue($ID, @$currentID)?> >
						<?=$name?>
					</option>
				<?php endforeach ?>
			</select>
			</div>

		</td>
	</tr>




<?php }

/*****************************************************************************/

function edit_tournamentDoubleType($tournamentID = 0){
// Select menu for the method of handling bilateral hits (double+afterblow)
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a select box to create a new tournament if no parameter is passed

	$sql = "SELECT doubleTypeID, doubleTypeName
			FROM systemDoubleTypes";
	$doubleTypes = mysqlQuery($sql, KEY_SINGLES, 'doubleTypeID', 'doubleTypeName');

	$doubleTypeID = null;
	$formatID = FORMAT_MATCH;
	$tournamentID = (int)$tournamentID;

	if($tournamentID !=  0){

		$sql = "SELECT formatID, doubleTypeID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$info = mysqlQuery($sql, SINGLE);

		$doubleTypeID = $info['doubleTypeID'];
		$formatID = $info['formatID'];
	} else {
		$doubleTypeID = 2;
	}

	if($formatID != FORMAT_MATCH){
		$hide = 'hidden';
	} else {
		$hide = '';
	}

?>

<!-- Start display -->

	<tr class='option-sparring <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Double/Afterblow Type
			</div>
		</td>

		<td>
		<div class='grid-x grid-padding-x'>


			<select name='updateTournament[doubleTypeID]' class='shrink'
				onchange="edit_doubleType('<?=$tournamentID?>')" id='doubleID_select<?=$tournamentID?>'>

					<?php foreach($doubleTypes as $ID => $name):?>
						<option <?=optionValue($ID, $doubleTypeID)?>>
							<?=$name?>
						</option>
					<?php endforeach ?>
			</select>
		</div>
		</td>
	</tr>

<?php }

/*****************************************************************************/

function edit_tournamentRankingType($tournamentID = 0){
// Select menu for the tournament ranking alogrithm

	$nullOptionSelected = "selected";
	$rankingTypeDescriptions = getRankingTypeDescriptions();
	$rankingTypes = [];
	$current = null;

	if($tournamentID != 0){

		$formatID = (int)getTournamentFormat($tournamentID);

		$sql = "SELECT tournamentRankingID, name
				FROM systemRankings
				WHERE formatID = {$formatID}
				ORDER BY name ASC";
		$rankingTypes = mysqlQuery($sql, KEY_SINGLES, 'tournamentRankingID', 'name');

		if($formatID == FORMAT_MATCH){
			$sql = "SELECT tournamentRankingID, name
					FROM systemRankings
					WHERE formatID = {$formatID}
					ORDER BY numberOfInstances DESC
					LIMIT 10";
			$rankingTypesPopular = mysqlQuery($sql, KEY_SINGLES, 'tournamentRankingID', 'name');
		} else {
			$rankingTypesPopular = [];
		}

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

	<tr>

		<td  class='shrink-column'>
			<strong><a data-open='rankingTypesReveal'>Ranking Type</a></strong>
			<?php tooltip("Method for calculating pool rankings/round scores.<BR>
							Click on link for description of each algorithm/method"); ?>

		</td>

		<td>

			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[tournamentRankingID]' class="shrink"
				onchange="enableTournamentButton('<?=$tournamentID?>')"
				id='rankingID_select<?=$tournamentID?>'>

				<option disabled <?=$nullOptionSelected?>></option>

				<?php if($rankingTypesPopular != []): ?>
					<option disabled>- Most Popular: ----------------</option>
				<?php endif ?>

				<?php foreach($rankingTypesPopular as $ID => $name):?>
					<option <?=optionValue($ID, $currentID)?> >
						<?=$name?>
					</option>
				<?php endforeach ?>

				<?php if($rankingTypesPopular != []): ?>
					<option disabled>- By Name: ---------------------</option>
				<?php endif ?>

				<?php foreach($rankingTypes as $ID => $name):?>
					<option <?=optionValue($ID, $currentID)?> >
						<?=$name?>
					</option>
				<?php endforeach ?>
			</select>
			</div>

		</td>
	</tr>

<!----- Ranking types reveal -->

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

function edit_tournamentBasePoints($tournamentID){
// Select menu for the base points associated with a turnament
// This is for scored events, such as the value of a cut, or points before deductions

	$tournamentID = (int)$tournamentID;
	$display = "hidden"; // Hidden for most cases
	$value = null;

	if($tournamentID != 0){

		$sql = "SELECT basePointValue
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
		$value = (int)mysqlQuery($sql, SINGLE, 'basePointValue');

		if($value == 0){
			$value = '';
		}
	}
	?>

<!-- Start display -->

	<tr>
		<td  class='shrink-column'>
		Base Point Value
		<?php
		tooltip("Number to use as a base for scoring calculations. (Most tournaments don't use this.)<BR>
			<u>Examples:</u> Base value for a cut, total round score before deductions,
			 or initial fighter score in Injury Score mode");
		?>
		</td>
		<td>
			<div class='grid-x grid-padding-x'>
			<input type='number'
				name='updateTournament[basePointValue]' value='<?=$value?>'
				onkeyup="enableTournamentButton('<?=$tournamentID?>')"
				id='baseValue_select<?=$tournamentID?>'>
			</div>
		</td>
	</tr>



<?php }

/******************************************************************************/

function edit_tournamentMaxDoubles($tournamentID = 0){
// Select menu for the maximum doubles allowed in a tournament
// Appears or disapears as controled by javascript

	$tournamentID = (int)$tournamentID;
	$formatID = FORMAT_MATCH;
	$maxDoublesLimit = 10;	// Arbitrary
	$maxDoubleHits = null; 		// Arbitrary

	if($tournamentID != 0){

		$sql = "SELECT formatID, maxDoubleHits
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$info = mysqlQuery($sql, SINGLE);

		$formatID = (int)$info['formatID'];
		$maxDoubleHits = (int)$info['maxDoubleHits'];
	} else {
		$sql = "SELECT maxDoubleHits
				FROM eventDefaults
				WHERE eventID = {$_SESSION['eventID']}";
		$maxDoubleHits = mysqlQuery($sql, SINGLE, 'maxDoubleHits');
		if($maxDoubleHits == null){
			$maxDoubleHits = 3; 		// Arbitrary
		}
	}



	if($formatID != FORMAT_MATCH){
		$hide = 'hidden';
	} else {
		$hide = '';
	}

?>

<!-- Start display -->
	<tr class='option-auto-conclude <?=$hide?>' id="maxDoubles_div<?=$tournamentID?>">
		<td class='shrink-column'>
			<div class='shrink'>
				Maximum Double Hits
				<?=tooltip("This only matters if you are scoring double hits explicitly.")?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[maxDoubleHits]' class='shrink'
					id='maxDoubles_select<?=$tournamentID?>'>

					<?php for($i = 1; $i <= $maxDoublesLimit; $i++):?>
						<option <?=optionValue($i,$maxDoubleHits)?> ><?=$i?></option>
					<?php endfor ?>

					<option <?=optionValue(0,$maxDoubleHits)?> >Unlimited :(</option>
			</select>
			</div>
		</td>
	</tr>

<?php }

/******************************************************************************/

function edit_tournamentLimitPoolMatches($tournamentID = 0){
// Select menu for the maximum doubles allowed in a tournament

	$tournamentID = (int)$tournamentID;
	$maxMatchesLimit = 10;	// Arbitrary
	$maxMatches = null; 	// Arbitrary

	if($tournamentID !=  0){
		$maxMatches = getTournamentPoolMatchLimit($tournamentID);
	}

	$hide = "hidden";

?>


<!-- Start display -->
	<tr class='option-misc <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Limit Pool Matches
				<?=tooltip("This will limit the number of fights each person has in a pool.<BR>
					<em><u>Example:</u> If there are 8 people in a pool and the limit is 3, then
					each of the fighters will only have 3 matches. (assigned at random)</em>
					<BR><b>This will lead to some unfair pool seeding, you have been warned.</b>")?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[limitPoolMatches]' class='shrink'
					id='limitPoolMatches_select<?=$tournamentID?>'>
					<option <?=optionValue(0,$maxMatches)?> > No </option>
					<?php for($i = 1; $i <= $maxMatchesLimit; $i++):?>
						<option <?=optionValue($i,$maxMatches)?> ><?=$i?></option>
					<?php endfor ?>
			</select>
			</div>
		</td>
	</tr>


<?php }

/******************************************************************************/

function edit_tournamentMaxPoolSize($tournamentID = 0){
// Select menu for the maximum pool size allowed in a tournament

	$tournamentID = (int)$tournamentID;
	$maxSize = null;
	$formatID = FORMAT_MATCH;

	if($tournamentID != 0){

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

	if($formatID != FORMAT_MATCH){
		$hide = 'hidden';
	} else {
		$hide = '';
	}

?>

<!-- Start display -->
	<tr class='option-pools <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Maximum Pool Size
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[maxPoolSize]' class='shrink'
					id='maxPoolSize_select<?=$tournamentID?>'>
						<?php for($i = 2; $i <= POOL_SIZE_LIMIT; $i++):
							$selected = isSelected($i, $maxSize);
							?>

							<option value=<?=$i?> <?=$selected?>><?=$i?></option>
						<?php endfor ?>

			</select>
			</div>
		</td>
	</tr>

<?php }

/******************************************************************************/

function edit_tournamentSubMatches($tournamentID = 0){
// Select menu for the maximum pool size allowed in a tournament

	$tournamentID = (int)$tournamentID;
	$numSubMatches = 0;
	$subMatchMode = SUB_MATCH_ANALOG;

	if($tournamentID != 0){

		$sql = "SELECT subMatchMode, numSubMatches
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$data = mysqlQuery($sql, SINGLE);

		$subMatchMode = $data['subMatchMode'];
		$numSubMatches = (int)$data['numSubMatches'];

	}

	if($numSubMatches == 0){
		$hide = 'hidden';
	} else {
		$hide = '';
	}

	?>

<!-- Start display -->
	<tr class='option-sub-match <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Use Sub-matches
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[numSubMatches]' class='shrink'
					id='numSubMatches_select<?=$tournamentID?>'
					onchange="edit_numSubMatches(<?=$tournamentID?>)"
					data-original='<?=$numSubMatches?>' >
						<option value=0>No</option>
						<?php for($i = 2; $i <= 9; $i++): ?>

							<option <?=optionValue($i,$numSubMatches)?> >
								<?=$i?> Sub-matches per Match
							</option>
						<?php endfor ?>

			</select>
			</div>
		</td>
	</tr>

<!-- Sub Match Mode ------------------------------------------>
	<tr class='option-sub-match <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Sub-match Mode
				<?=tooltip("<u>Analog</u>: The points from all sub-matches are added to determine
							the match winner.
							<BR><BR><u>Digital</u>: Winner is determined by who wins the most sub-matches,
							regardless of what the scores were.")?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[subMatchMode]' class='shrink'
					id='subMatchMode_select<?=$tournamentID?>'>
					<option <?=optionValue(SUB_MATCH_ANALOG,$subMatchMode)?> >	Analog 	</option>
					<option <?=optionValue(SUB_MATCH_DIGITAL,$subMatchMode)?> >	Digital	</option>

			</select>
			</div>
		</td>
	</tr>

<?php }

/******************************************************************************/

function edit_tournamentNormalization($tournamentID = 0){
// Select menu for the normalization pool size All pools results will be

	$tournamentID = (int)$tournamentID;
	$normSize = 0;

	if($tournamentID !=  0){

		$sql = "SELECT normalizePoolSize
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$normSize = mysqlQuery($sql, SINGLE, 'normalizePoolSize');
	}

	$hide = 'hidden';
?>

<!-- Start display -->
	<tr class='option-misc <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Normalize Pool Size
				<?php tooltip("Force the pool size normalization to a specific pool size (instead of auto-detect).
					<BR><BR><b>Almost no one will need this option. Leave it on auto unless you are really sure.</b>"); ?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[normalizePoolSize]' class='shrink'
					id='normalizePoolSize_select<?=$tournamentID?>'>
					<option value='0'>Auto</option>
					<?php for($i = 2; $i <= POOL_SIZE_LIMIT; $i++):?>
						<option <?=optionValue($i,$normSize)?>><?=$i?></option>
					<?php endfor ?>
			</select>
			</div>
		</td>
	</tr>
<?php }

/******************************************************************************/

function edit_tournamentPoolWinners($tournamentID = 0){
// Select menu for the number of pool winners to rank ahead of non-pool winners.
// Appears or disapears as controled by javascript.

	$tournamentID = (int)$tournamentID;
	$numWinners = 0;
	$formatID = null;

	if($tournamentID != 0){

		$sql = "SELECT poolWinnersFirst
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$numWinners = (int)mysqlQuery($sql, SINGLE, 'poolWinnersFirst');

		$sql = "SELECT formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$formatID = mysqlQuery($sql, SINGLE,'formatID');
	}

	if($formatID != FORMAT_MATCH){
		$hide = 'hidden';
	} else {
		$hide = '';
	}

?>

<!-- Start display -->

	<tr class='option-pools <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Sort Pool Winners First
				<?php tooltip("Using this option the top fighters in each pool will all
				be ranked at the top, even if a non pool-winner has a higher score."); ?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[poolWinnersFirst]' class='shrink'
					id='poolWinnersFirst_select<?=$tournamentID?>'>
					<option value='0'>No (Rank by Score)</option>
					<?php for($i = 1; $i <= (POOL_SIZE_LIMIT-1); $i++): ?>
						<option <?=optionValue($i,$numWinners)?> >Top <?=$i?> from pool</option>
					<?php endfor ?>
			</select>
			</div>
		</td>
	</tr>

<?php }

/***********************************************************(******************/

function edit_tournamentColors($tournamentID, $num){
// Select menu for the fighter colors. Called for fighter 1 and 2 depending
// on the value of $num.


	$tournamentID = (int)$tournamentID;
	$formatID = FORMAT_MATCH;

	$num = (int)$num;
	if($num != 1 AND $num != 2){ return; }

	$currentID = '';
	$colors = getColors();

	if($tournamentID != 0){

		$sql = "SELECT formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$formatID = mysqlQuery($sql, SINGLE, 'formatID');

		$sql = "SELECT color{$num}ID, colorName
				FROM eventTournaments
				INNER JOIN systemColors ON color{$num}ID = colorID
				WHERE tournamentID = {$tournamentID}";
		$color = mysqlQuery($sql, SINGLE);

		$currentName = $color['colorName'];
		$currentID = $color["color{$num}ID"];


	} else {

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

	if($formatID != FORMAT_MATCH){
		$hide = 'hidden';
	} else {
		$hide = '';
	}

?>

<!-- Start display -->
	<tr class='option-match-display <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Fighter <?=$num?> Color
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[color<?=$num?>ID]' class='shrink'
					id='color<?=$num?>_select<?=$tournamentID?>'>

					<?php foreach($colors as $color):?>
						<option <?=optionValue($color['colorID'], $currentID)?> >
							<?=$color['colorName']?>
						</option>
					<?php endforeach ?>
			</select>
			</div>
		</td>
	</tr>

<?php }

/******************************************************************************/

function edit_tournamentTies($tournamentID = 0){
// Select menu for whether or not the tournament allows ties
// Calls to java-script on change to alter the form based on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed

	$tournamentID = (int)$tournamentID;
	$formatID = FORMAT_MATCH;
	$allowTies = 0;

	if($tournamentID !=  0){
		$formatID = getTournamentFormat($tournamentID);
		$allowTies = readOption('T',$tournamentID,'MATCH_TIE_MODE');
	}

	if($formatID != FORMAT_MATCH){
		$hide = 'hidden';
	} else {
		$hide = '';
	}

?>

<!-- Start display -->

	<tr class='option-sparring <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Allow Ties
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[allowTies]' id='allowTies_select<?=$tournamentID?>' class='shrink '>
				<option <?=optionValue(MATCH_TIE_MODE_NONE,$allowTies)?>>No</option>
				<option <?=optionValue(MATCH_TIE_MODE_EQUAL,$allowTies)?>>If Score Equal</option>
				<option <?=optionValue(MATCH_TIE_MODE_UNEQUAL,$allowTies)?>>Always</option>

			</select>
			</div>
		</td>
	</tr>

<?php }

/******************************************************************************/

function edit_tournamentTimerCountdown($tournamentID = 0){
// Select menu for whether or not the tournament allows ties
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed


	$formatID = getTournamentFormat($tournamentID);
	if($formatID != FORMAT_MATCH){
		$hide = 'hidden';
	} else {
		$hide = "";
	}

	$timerCountdown = isTimerCountdown($tournamentID);

	?>


<!-- Start display -->
	<tr class='option-match-display <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Timer Mode
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
				<select name='updateTournament[timerCountdown]' class='shrink'
						id='timerCountdown_select<?=$tournamentID?>'>
						<option <?=optionValue(0,$timerCountdown)?>>Count Up</option>
						<option <?=optionValue(1,$timerCountdown)?>>Count Down</option>
				</select>
			</div>
		</td>
	</tr>

<?php }


/******************************************************************************/

function edit_tournamentStaffCheckin($tournamentID){


	$tournamentID = (int)$tournamentID;
	$checkInStaff = STAFF_CHECK_IN_NONE;

	if($tournamentID !=  0){
		$sql = "SELECT checkInStaff
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$checkInStaff = (int)mysqlQuery($sql, SINGLE, 'checkInStaff');
	}

	$hide = "hidden";

?>


<!-- Start display -->
	<tr class='option-misc <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Check in Staff for Matches
				<?=tooltip("Use this if you want to keep track of your judging/table staff on a match by match basis.")?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
				<select name='updateTournament[checkInStaff]' class='shrink'
						id='checkInStaff_select<?=$tournamentID?>'>
						<option <?=optionValue(STAFF_CHECK_IN_NONE,$checkInStaff)?> >No</option>
						<option <?=optionValue(STAFF_CHECK_IN_ALLOWED,$checkInStaff)?> >Optional</option>
						<option <?=optionValue(STAFF_CHECK_IN_MANDATORY,$checkInStaff)?> >Mandatory </option>
				</select>
			</div>
		</td>
	</tr>

<?php }

/******************************************************************************/

function edit_tournamentRequireSignOff($tournamentID = 0){

	$tournamentID = (int)$tournamentID;
	$requireSignOff = STAFF_CHECK_IN_NONE;

	if($tournamentID != 0){

		$sql = "SELECT requireSignOff
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$requireSignOff = (int)mysqlQuery($sql, SINGLE, 'requireSignOff');
	}

	$hide = "hidden";

?>


<!-- Start display -->
	<tr class='option-misc <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Match Sign Off
		<?=tooltip("Use this to require fighters to sign off on their scores after every match.")?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
				<select name='updateTournament[requireSignOff]' class='shrink'
						id='requireSignOff_<?=$tournamentID?>'>
						<option <?=optionValue(0,$requireSignOff)?> >No</option>
						<option <?=optionValue(1,$requireSignOff)?> >Yes</option>
				</select>
			</div>
		</td>
	</tr>

<?php }

/******************************************************************************/

function edit_tournamentReverseScore($tournamentID){
// Select menu for whether or not the tournament uses reverse scores,
// if points are entered to the fighter who got hit rather than
// the fighter who hits

	$tournamentID = (int)$tournamentID;
	$formatID = FORMAT_MATCH;
	$isReverseScore = REVERSE_SCORE_NO;

	if($tournamentID != 0){
		$sql = "SELECT isReverseScore, formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$info = mysqlQuery($sql, SINGLE);

		$isReverseScore = $info['isReverseScore'];
		$formatID = $info['formatID'];
	}

	if($formatID != FORMAT_MATCH){
		$hide = 'hidden';
	} else {
		$hide = '';
	}

?>

<!-- Start display -->
	<tr class='option-sparring <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Use Reverse Score
				<?php tooltip("<strong>Reverse Points</strong><BR>
								<u>Golf Score</u> - Fighters gain points when they are hit. Low score is good.<BR>
								<u>Injury Score</u> - Negative points are applied to the fighter who recieves a hit"); ?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[isReverseScore]' class='shrink'
					onchange="enableTournamentButton('<?=$tournamentID?>')"
					id='reverseScore_select<?=$tournamentID?>'>

					<option <?=optionValue(0,$isReverseScore)?> >No (Normal)</option>
					<option <?=optionValue(1,$isReverseScore)?> >Golf Score</option>
					<option <?=optionValue(2,$isReverseScore)?> >Injury Score</option>

			</select>
			</div>
		</td>
	</tr>

<?php }

/******************************************************************************/

function edit_tournamentOverrideDoubles($tournamentID = 0){
// Select menu for whether or not the tournament uses overrides
// the default double hit behavior.


	$tournamentID = (int)$tournamentID;
	$overrideDoubleType = 0;
	$formatID = null;

	if($tournamentID !=  0){

		$sql = "SELECT overrideDoubleType, formatID, doubleTypeID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$info = mysqlQuery($sql, SINGLE);

		$overrideDoubleType = $info['overrideDoubleType'];
		$formatID = $info['formatID'];
		$doubleTypeID = $info['doubleTypeID'];

	}

	if($formatID == FORMAT_MATCH && $doubleTypeID == FULL_AFTERBLOW){
		$hide = '';
	} else {
		$hide = 'hidden';
	}

?>

<!-- Start display -->
	<tr class='option-sparring <?=$hide?>' id="overrideDoubles_div<?=$tournamentID?>">
		<td class='shrink-column'>
			<div class='shrink'>
				Enable Doubles
				<?php tooltip("Enables double hits in Full Afterblow scoring"); ?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[overrideDoubleType]' class='shrink'
					onchange="enableTournamentButton('<?=$tournamentID?>')"
					id='overrideDoubles_select<?=$tournamentID?>'>

					<option <?=optionValue(0,$overrideDoubleType)?> >No (Normal)</option>
					<option <?=optionValue(1,$overrideDoubleType)?> >Yes</option>

			</select>
			</div>
		</td>
	</tr>

<?php }

/******************************************************************************/

function edit_tournamentNetScore($tournamentID = 0){
// Select menu for whether or not the tournament uses net score for Full Afterblow

	$formatID = FORMAT_MATCH;
	$tournamentID = (int)$tournamentID;
	$isNotNetScore = 0;

	if($tournamentID !=  0){

		$sql = "SELECT isNotNetScore, formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$info = mysqlQuery($sql, SINGLE);

		$isNotNetScore = (int)$info['isNotNetScore'];
		$formatID = $info['formatID'];

	}

	if($formatID != FORMAT_MATCH){
		$hide = 'hidden';
	} else {
		$hide = '';
	}

?>

<!-- Start display -->
	<tr class='option-sparring <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Use Net Points
				<?php tooltip("<strong>Net Points</strong><BR>
								Only the higher scoring fighter receives points.<BR>
								[High Score] - [Low Score]<BR><BR>
								<strong>No Net Points</strong><BR>
								Both fighters receive their score"); ?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[isNotNetScore]' class='shrink'
					onchange="enableTournamentButton('<?=$tournamentID?>')"
					id='notNetScore_select<?=$tournamentID?>'>

				<option <?=optionValue(0,$isNotNetScore);?> >Yes</option>
				<option <?=optionValue(1,$isNotNetScore);?> >No</option>

			</select>
			</div>
		</td>
	</tr>

<?php }

/**********************************************************(*******************/

function edit_tournamentCuttingQual($tournamentID = 0){
// Select menu for whether or not the tournament has a cutting qualification

	$tournamentID = (int)$tournamentID;
	$isQual = 0;

	if($tournamentID !=  0){

		$sql = "SELECT isCuttingQual
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$isQual = (int)mysqlQuery($sql, SINGLE, 'isCuttingQual');

	}

	$hide = "hidden";

?>


<!-- Start display -->
	<tr class='option-misc <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Cutting Qualification
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[isCuttingQual]'  class='shrink'
					id='isCuttingQual_select<?=$tournamentID?>'>
					<option <?=optionValue(0,$isQual)?>>No</option>
					<option <?=optionValue(1,$isQual)?>>Yes</option>

			</select>
			</div>
		</td>
	</tr>

<?php }

/*****************************************************************************/

function edit_tournamentDoublesCarryForward($tournamentID = 0){

	$tournamentID = (int)$tournamentID;

	if($tournamentID != 0){
		$isEnabled = readOption('T',$tournamentID,'DOUBLES_CARRY_FORWARD');
	} else {
		$isEnabled = 0;
	}

	$hide = "hidden";

?>

	<tr class='option-misc <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Carry Doubles Forward  <?=tooltip("
					If a fighter accrues enough double hits to 'double out' in a
					bracket match the table will be notified at the start
					of their next match.<BR>
					<i>Doesn't work for double elim.</i>")?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
				<select name='updateTournament[doublesCarryForward]' class='shrink'
						id='doublesCarryForward_select<?=$tournamentID?>'>
					<option <?=optionValue(0,$isEnabled)?>>No</option>
					<option <?=optionValue(1,$isEnabled)?>>Yes</option>
				</select>
			</div>
		</td>
	</tr>

<?php

}

/*****************************************************************************/

function edit_tournamentKeepPrivate($tournamentID = 0){
// Select menu for whether or not the software should warn people the event
// organizer would rather not have results posted or added to stuff like HEMA Ratings

	$tournamentID = (int)$tournamentID;
	$isPrivate = 0;

	if($tournamentID !=  0){
		$sql = "SELECT isPrivate
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$isPrivate = (int)mysqlQuery($sql, SINGLE, 'isPrivate');

	}

	$hide = 'hidden';

?>


<!-- Start display -->
	<tr class='option-misc <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Sharing Preference <?=tooltip("
					This expresses your preference for your data being used by organizations like HEMA Ratings.
					<BR><strong>YOU HAVE ALREADY AGREED THAT THIS INFORMATION IS PUBLIC</strong>
					<BR>This just expresses your preference. How people use the information is up to them.")?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[isPrivate]' class='shrink'
					id='isPrivate_select<?=$tournamentID?>'>
					<option <?=optionValue(0,$isPrivate)?>>Normal</option>
					<option <?=optionValue(1,$isPrivate)?>>I prefer if people don't use.</option>
			</select>
			</div>
		</td>
	</tr>

<?php }

/*****************************************************************************/

function edit_tournamentHideFinalResults($tournamentID = 0){

	$tournamentID = (int)$tournamentID;
	$hideFinalResults = 0;

	if($tournamentID != 0){

		$sql = "SELECT hideFinalResults
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$hideFinalResults = (bool)mysqlQuery($sql, SINGLE, 'hideFinalResults');

	}

	$hide = "hidden";

?>


<!-- Start display -->
	<tr class='option-misc <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Show Final Results<?=tooltip("Disable this to not show the overall tournament results.<BR>
					<BR>eg: Not showing the final results of tournaments that
					are components of a meta-tournament.")?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
				<select name='updateTournament[hideFinalResults]' class='shrink'
						id='hideFinalResults_select<?=$tournamentID?>'>
						<option <?=optionValue(0,$hideFinalResults)?> >Yes (normal)</option>
						<option <?=optionValue(1,$hideFinalResults)?> >No (hide them)</option>
				</select>
			</div>
		</td>
	</tr>

<?php }

/*****************************************************************************/

function edit_tournamentTeams($tournamentID = 0){
// Select if the tournament is a team event

	$tournamentID = (int)$tournamentID;
	$isTeams = 0;
	$mode = '';
	$teamSwitchPoints = 0;
	$teamSize = 0;

	if($tournamentID != 0){

		$sql = "SELECT isTeams
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$isTeams = (int)mysqlQuery($sql, SINGLE, 'isTeams');

		if($isTeams){
			$mode = getTournamentLogic($tournamentID);
		}
		$teamSwitchPoints = readOption('T',$tournamentID,'TEAM_SWITCH_POINTS');
		$teamSize = readOption('T',$tournamentID,'TEAM_SIZE');

	}

	if($isTeams != 0){
		$hide = '';
	} else {
		$hide = 'hidden';
	}

	if($teamSwitchPoints == 0){
		$teamSwitchPoints = null;
	}

	if($teamSize == 0){
		$teamSize = null;
	}


?>

<!-- Start display -->

	<tr class='option-teams <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Team Based Event
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
				<select name='updateTournament[isTeams]' class='shrink'
						id='isTeams_select<?=$tournamentID?>'>
					<option <?=optionValue(0,$isTeams)?>>No</option>
					<option <?=optionValue(1,$isTeams)?>>Team Event</option>
				</select>
			</div>
		</td>
	</tr>


<!-- Start display -->

	<tr class='option-teams <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Team Mode
				<?=tooltip("<u>Team vs Team</u><BR>Whole teams fight each other
							<BR><u>Solo</u><BR>Treated as an individual tournament with team points tabulated.
							<BR><u>All vs All</u><BR>Each team member faces each member of every other team individually.")?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
				<select name='updateTournament[logicMode]'  class='shrink'
						id='teamLogic_select<?=$tournamentID?>'>
						<option value='NULL'>Team vs Team</option>
						<option <?=optionValue('team_Solo',$mode)?> >Solo</option>
						<option <?=optionValue('team_AllVsAll',$mode)?> >All vs All</option>
				</select>
			</div>
		</td>
	</tr>

<!-- Max Team Size -->

	<tr class='option-teams <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Max Team Size
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<input type='number' placeholder='n/a'
				name='updateTournament[teamSize]' value='<?=$teamSize?>'
				id='teamSize_select<?=$tournamentID?>' min=0 max=10>
			</div>
		</td>
	</tr>

<!-- Switch Fighters Alert -->

	<tr class='option-teams <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Team Switch Points
				<?=tooltip("In a <b>Team vs Team</b> tournament the table will be instructed to change fighters whenever one team's score reaches a multiple of this value.")?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<input type='number' placeholder='n/a'
				name='updateTournament[teamSwitchPoints]' value='<?=$teamSwitchPoints?>'
				id='teamSwitchPoints_select<?=$tournamentID?>' min=0 max=99>
			</div>
		</td>
	</tr>

<?php }

/****************************************************(*************************/

function edit_tournamentMaxExchanges($tournamentID = 0){
// Select menu for whether or not the tournament allows ties

	$tournamentID = (int)$tournamentID;
	$formatID = FORMAT_MATCH;

	$maximumExchanges = 0;
	$doublesNotCounted = 0;
	$doublesAreNotScoringExch = 0;

	if($tournamentID !=  0){

		$sql = "SELECT maximumExchanges, formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$info = mysqlQuery($sql, SINGLE);

		$maximumExchanges = $info['maximumExchanges'];
		$formatID = $info['formatID'];

		$doublesAreNotScoringExch = readOption('T',$tournamentID,'DOUBLES_ARE_NOT_SCORING_EXCH');

	}

	if($formatID != FORMAT_MATCH){
		$hide = 'hidden';
	} else {
		$hide = '';
	}

	if($maximumExchanges == 0){
		$maximumExchanges = '';
	}

?>

<!-- Start display -->
	<tr class='option-auto-conclude <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Maximum Exchanges
				<?php tooltip("Match will automatically conclude after this number is reached. <BR>
					<strong>Leave blank for unlimited.</strong><BR>
					Only counts scoring hits and doubles."); ?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<input type='number' name='updateTournament[maximumExchanges]' value='<?=$maximumExchanges?>'
					placeholder='Unlimited' min=0 max=100 class='text-center'>
			</div>
		</td>
	</tr>

<!-- Start display -->
	<tr class='option-auto-conclude <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Doubles Aren't Exchanges
				<?php tooltip("If you don't want doubles to count as a scoring exchange.<BR>
					(When calculating to end the match based of maximum number of exchanges.)"); ?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[doublesAreNotScoringExch]' class='shrink'
						id='doublesAreNotScoringExch_<?=$tournamentID?>'>
						<option <?=optionValue(0,$doublesAreNotScoringExch)?> >No</option>
						<option <?=optionValue(1,$doublesAreNotScoringExch)?> >Yes</option>
				</select>
			</div>
		</td>
	</tr>

<?php }

/****************************************************(*************************/

function edit_tournamentMaxPoints($tournamentID = 0){
// Select menu for whether or not the tournament allows ties

	$tournamentID = (int)$tournamentID;
	$formatID = FORMAT_MATCH;
	$maximumPoints = 0;

	if($tournamentID != 0){

		$sql = "SELECT maximumPoints, formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$info = mysqlQuery($sql, SINGLE);

		$maximumPoints = $info['maximumPoints'];
		$formatID = $info['formatID'];
	}

	if($formatID != FORMAT_MATCH){
		$hide = 'hidden';
	} else {
		$hide = '';
	}

	if($maximumPoints == 0){
		$maximumPoints = '';
	}

?>

<!-- Start display -->
	<tr class='option-auto-conclude <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Maximum Points
		<?php tooltip("Match will automaticaly conclude after this number is reached. <BR>
			<strong>Leave blank for unlimited.</strong>"); ?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<input type='number' name='updateTournament[maximumPoints]' value='<?=$maximumPoints?>'
					placeholder='Unlimited' min=0 max=100 class='text-center'>
			</div>
		</td>
	</tr>

<?php }

/****************************************************(*************************/

function edit_tournamentMaxPointSpread($tournamentID = 0){

	$tournamentID = (int)$tournamentID;
	$formatID = FORMAT_MATCH;
	$pointSpreadLimit = 20; // Arbitrary
	$maxPointSpread = 0;


	if($tournamentID != 0){

		$sql = "SELECT maxPointSpread, formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$info = mysqlQuery($sql, SINGLE);

		$maxPointSpread = $info['maxPointSpread'];
		$formatID = $info['formatID'];

	}

	if($formatID != FORMAT_MATCH){
		$hide = 'hidden';
	} else {
		$hide = '';
	}

	?>

<!-- Start display -->
	<tr class='option-auto-conclude <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Maximum Points Spread
				<?php tooltip("Match will automatically conclude after this number is reached. <BR>
					<strong>Leave blank for unlimited.</strong>"); ?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
			<select name='updateTournament[maxPointSpread]' class='shrink'
					id='maxPointSpread_select<?=$tournamentID?>'>

					<option <?=optionValue(0,$maxPointSpread)?> >Unlimited</option>

					<?php for($i = 1; $i <= $pointSpreadLimit; $i++):?>
						<option <?=optionValue($i,$maxPointSpread)?> ><?=$i?></option>
					<?php endfor ?>


			</select>
			</div>
		</td>
	</tr>

<?php }

/****************************************************(*************************/

function edit_tournamentTimeLimit($tournamentID = 0){
// Select menu for whether or not the tournament allows ties


	$tournamentID = (int)$tournamentID;
	$timeLimit = 0;
	$formatID = FORMAT_MATCH;

	if($tournamentID != 0){

		$sql = "SELECT timeLimit, formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$info = mysqlQuery($sql, SINGLE);

		$timeLimit = $info['timeLimit'];
		$formatID = $info['formatID'];

	}

	if($formatID != FORMAT_MATCH){
		$hide = 'hidden';
	} else {
		$hide = '';
	}

	if($timeLimit == 0){
		$timeLimit = '';
	}

?>

<!-- Start display -->
	<tr class='option-auto-conclude <?=$hide?>'>
		<td class='shrink-column'>
			<div class='shrink'>
				Time Limit [seconds]
				<?php tooltip("Match will automaticaly conclude after this time is reached. <BR>
			<strong>Leave blank for unlimited.</strong>"); ?>
			</div>
		</td>

		<td>
			<div class='grid-x grid-padding-x'>
				<input type='number' name='updateTournament[timeLimit]' value='<?=$timeLimit?>'
						placeholder='Unlimited' min=0 max=300 class='text-center'>
			</div>
		</td>
	</tr>


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
	$tournamentID = (int)$_SESSION['tournamentID'];
	if($tournamentID == 0){
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
			$color =  COLOR_NAME_1;
		} else {
			$index1 = 2;
			$index2 = 1;
			$color =  COLOR_NAME_2;
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
				$exchanges[$i][$index1][2] = "";

				// $exchanges[$i][$index1][2] = "(".$exchange['scoreValue'].")";
				// I have no idea why I added this. Kept here incase the reason becomes apparent.
				break;

			case 'switchFighter':
				$exchanges[$i][$index1][1] = "<b>SW</b>";

				$rosterID = getTeamFighterByExchange($exchange['exchangeID']);
				$teamID = $exchange['rosterID'];
				$position = getTeamMemberPosition($teamID, $rosterID);
				$exchanges[$i][$index1][2] = "({$position})";

				break;

			default:
				break;
		}

		$scoresheet = '';
		$scoresheet .= "\n".$exchanges[$i]['time']." ".$color." ". $exchange['exchangeType'];

		$scoresheet .= " [".$exchange['scoreValue']."|".$exchange['scoreDeduction']."]";
		if((int)$exchange['refPrefix'] != 0)
		{
			$scoresheet .= ", ".GetAttackName($exchange['refPrefix']);
		}
		if((int)$exchange['refTarget'] != 0)
		{
			$scoresheet .= ", ".GetAttackName($exchange['refTarget']);
		}
		if((int)$exchange['refType'] != 0)
		{
			$scoresheet .= ", ".GetAttackName($exchange['refType']);
		}

		$exchanges[$i]['detail'] = $scoresheet;

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

		<div class='shrink text-center' style='width: 40px' title='<?=$exchange['detail']?>'>
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

function addVideoLink($matchID, $divider = true){
// Displays video link for match if it exists, and allows staff to add links

	$videoInfo = getMatchVideoLink($matchID);

	$url = $videoInfo['sourceLink'];
	$synchTime = $videoInfo['synchTime'];
	$synchTime2 = $videoInfo['synchTime2'];

	$sourceType = getVideoSourceType($videoInfo['sourceLink']);

	if($sourceType == VIDEO_SOURCE_YOUTUBE){
		$virtualLivestream = true;
	} else {
		$virtualLivestream = false;
	}

	define("VIRTUAL_LIVESTREAM_ENABLED",false);

?>

	<div class='grid-x grid-margin-x'>

		<div class='cell large-12'>
			&nbsp;
		</div>

		<?php if($virtualLivestream == true && VIRTUAL_LIVESTREAM_ENABLED): ?>
			<div class='cell large-3 medium-4'>
			<a class='button warning large expanded' onclick="openVideoWindow(<?=$matchID?>,<?=VIDEO_STREAM_VIRTUAL?>)">
				Virtual Livestream
			</a>
			</div>
		<?php endif ?>

		<?php if(ALLOW['EVENT_VIDEO'] == true): ?>

			<!-- Display entry field for staff -->
			<div class='cell large-9 medium-10 align-self-middle'>
			<form method='POST'>
			<div class='input-group grid-x'>

				<input type='hidden' name='updateVideoSource[matchID]' value='<?=$matchID?>'>

				<span class='input-group-label large-2 medium-3 small-12 text-center'>YouTube URL:</span>

				<input class='input-group-field' type='url' name='updateVideoSource[sourceLink]' value='<?=$url?>'
					id='updateVideoSource[sourceLink]' onkeyup="validateVideoLink()"  placeholder='Include https://'>

				<button name='formName'  value='updateVideoSource' disabled
					class='button success input-group-button hide-for-small-only videoSubmitButton'>
					 Update Link
				</button>

				<button class='button success expanded show-for-small-only videoSubmitButton'
					name='formName' value='updateVideoSource' disabled >
					Update Link
				</button>

			</div>
			</form>
			</div>

		<?php elseif($url != null) : ?>

			<!-- Displays bare link for guests, if one exists -->
			<div class='cell medium-9 align-self-middle'>

			<i>(Unofficial)</i> <strong>Video Link:</strong>

			<?=tooltip('These links are public videos on youtube/etc that HEMA Scorecard volunteers have attached to matches for your benefit. Do not complain to an event organizer about video links.<hr>
				<i>If there is an issue with a link please contact the HEMA Scorecard team.</i>')?>

			<a href='<?=$url?>'>
				<?=$url?>
			</a>

			</div>

		<?php endif ?>

		<?php if($virtualLivestream == false && VIRTUAL_LIVESTREAM_ENABLED): ?>
			<div class='cell large-2 medium-3'>
			<a class='button warning hollow expanded' onclick="openVideoWindow(<?=$matchID?>,<?=VIDEO_STREAM_VIRTUAL?>)">
				Virtual Livestream
			</a>
			</div>
		<?php endif ?>

	</div>

<!-- Virtual Livestream Reveal Window -------------->


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

function selectCountry($name, $selected = null, $countryList = null, $classes = null, $required = true){

	if($countryList == null){
		$countryList = getCountryList();
	}

	if($required == true){
		$required = 'required';
	} else {
		$required = '';
	}

	echo "<select name='{$name}' class='{$classes}' {$required}>";

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

function plotLineChart($chartData,$chartNum,$xLabel = null, $binWidth = null, $plotWidth = null){

	$divName = "chart-div-area-".$chartNum;


	if($plotWidth != null){
		$plotWidthClause = ", max: {$plotWidth}";
	} else {
		$plotWidthClause = "";
	}

	$numDataSeries = count((array)$chartData[0]);

	if($_SESSION['dataModes']['percent'] == false){
		$yLabel = "# of matches";
	} else {
		$yLabel = "% of matches";
	}

?>
	<script type="text/javascript">

		google.charts.setOnLoadCallback(drawChart);

		function drawChart() {

			data = [<?=$chartData?>];

			var chartData = google.visualization.arrayToDataTable(data);


			var options = {
				legend: { position: 'top' },
				curveType: 'function',
				vAxis: { title: "<?=$yLabel?>",
						viewWindow: {min: 0}
						 },
				hAxis: { title: "<?=$xLabel?>",
						viewWindow: {min: 0 <?=$plotWidthClause?>}
						}

			};

			var chart = new google.visualization.LineChart(document.getElementById('<?=$divName?>'));

			chart.draw(chartData, options);
		}
	</script>

	<div id="<?=$divName?>" style="width: 100%; height:400px;" ></div>
<?php
}

/******************************************************************************/

function changeParticipantFilterForm($eventID){

	if(ALLOW['EVENT_SCOREKEEP'] == true && ALLOW['SOFTWARE_ADMIN'] == false){
		return;
	}

	$schoolIDs = getEventSchoolIDs($eventID);

	if($_SESSION['filters']['school'] == true){
		$buttonText = "Modify Filter";
		$class = "hollow tiny";
	} else {
		$buttonText = "Create Filter";
		$class = "";
	}

?>

	<a class='button <?=$class?> no-bottom' data-open="filter-box"><?=$buttonText?></a>


	<div class='reveal medium text-center' id='filter-box' data-reveal>
	<form method='POST' class=''>
	<div class='grid-x grid-margin-x'>
			<h4 class='large-12 cell'>Create Filter</h4>

			<h5 class='large-12 cell text-left'>School</h5>

			<?php for($i = 0; $i < 5; $i++): ?>
				<select name='filters[schoolID][<?=$i?>]' class='cell'>
					<option <?=optionValue(0,0)?>>- not set -</option>
					<?php foreach($schoolIDs as $schoolID): ?>
						<option <?optionValue($schoolID, @$_SESSION['filters']['schoolID'][$i])?>>
							<?=getSchoolName($schoolID)?>
						</option>
					<?php endforeach ?>
				</select>
			<?php endfor ?>



			<HR class='cell large-12'>
			<a class='button secondary cell large-6' data-close aria-label='Close modal'>Cancel</a>
			<button class='button success cell large-6' name='formName' value="setDataFilters" >Apply Filter</button>

		<!-- Reveal close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>

	</div>
	</form>
	</div>

<?php
}

/******************************************************************************/

function activeFilterWarning(){

	if($_SESSION['filters']['school'] == false && $_SESSION['filters']['roster'] == false){
		return;
	}

?>
	<div class='callout secondary' data-closable>

		One or more filters are active and hiding data.

		<?=changeParticipantFilterForm($_SESSION['eventID'])?>

		<form method='POST' style='display:inline-block'>

			<button class='button secondary tiny no-bottom' name='formName' value='setDataFilters'>Clear Filters</button>
			<input type='hidden' name='filters[schoolID][0]' value='0'>
			<input type='hidden' name='filters[rosterID][0]' value='0'>
		</form>

		<button class='close-button' aria-label='Dismiss alert' type='button' data-close>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>

<?php
}

/******************************************************************************/

function changeRosterFilterDropdown(){

	if(ALLOW['EVENT_SCOREKEEP'] == true && ALLOW['SOFTWARE_ADMIN'] == false){
		return;
	}

	$roster = getSystemRosterInfo();

?>

	<form method='POST' class=''>
		<input type='hidden' name='formName' value='filterForSystemRosterID'>
		<div class='input-group'>
			<span class='input-group-label'>Filter By Fighter</span>
			<select  class='input-group-field' name='systemRosterID'>
				<option <?=optionValue(0,$_SESSION['filterForSystemRosterID'])?>>- not set -</option>
				<?php foreach($roster as $r): ?>
					<option <?=optionValue($r['systemRosterID'],$_SESSION['filterForSystemRosterID'])?>>
						<?=$r['lastName']?>, <?=$r['firstName']?>
					</option>
				<?php endforeach ?>
			</select>
			<button class='input-group-button button'>Go</button>
		</div>
	</form>

<?php
}

/******************************************************************************/

function burgeeDisplay($burgeeID){

	if($burgeeID == 0){
		return;
	}

	$burgeePoints = getBurgeePoints($burgeeID);
	$burgeeInfo = getBurgeeInfo($burgeeID);
	$paramList = getBurgeeRankingParameters($burgeeInfo['burgeeRankingID']);
	$name = getBurgeeName($burgeeID);


	$schoolIDs = array_keys($burgeePoints);

	$top4Burgees = [];
	foreach($burgeePoints as $schoolID => $placing){

		$burgeePoints[$schoolID]['placeText'] = $placing['place'];
		if($placing['text'] != ""){
			$burgeePoints[$schoolID]['placeText'] .= "<i>-".$placing['text']."</i>";
		}

		if($placing['place'] <= 4){
			$tmp = [];
			$tmp['school'] = getSchoolName($schoolID, 'long', true);
			$tmp['placeText'] = $burgeePoints[$schoolID]['placeText'];
			$top4Burgees[] = $tmp;
		}
	}

?>

	<fieldset class='large-10 small-12 fieldset'>

		<legend><h4><?=$name?></h4></legend>


		<table class='extra-burgee-<?=$burgeeID?>'>

		<?php foreach($top4Burgees as $burgee):?>

			<tr>
				<td class='text-center'>
					<?=$burgee['placeText']?>
				</td>
				<td>
					<strong><?=$burgee['school']?></strong>
				</td>
			</tr>

		<?php endforeach ?>
		</table>

		<?php if($top4Burgees != []):?>
			<a class='extra-burgee-<?=$burgeeID?>'
				onclick= "$('.extra-burgee-<?=$burgeeID?>').toggle()">
				Full Standings ↓
			</a>
		<?php else: ?>
			<a class='extra-burgee-<?=$burgeeID?>-explain'
				onclick= "$('.extra-burgee-<?=$burgeeID?>-explain').toggle()">
				How Is This Calculated? ↓
			</a>
		<?php endif ?>

		<a onclick= "$('.extra-burgee-<?=$burgeeID?>').toggle()" class='extra-burgee-<?=$burgeeID?> hidden'>Hide ↑</a>

		<table class='extra-burgee-<?=$burgeeID?> hidden'>

			<tr>
				<th>#</th>
				<th>School</th>
				<?php foreach($paramList as $params):?>
					<th><?=$params['name']?></th>
				<?php endforeach ?>
				<th>Points</th>
			</tr>


			<?php
				foreach($burgeePoints as $schoolID => $placing):?>

				<tr>
					<td><?=$placing['placeText']?></td>
					<td>
						<a onclick="$('.school-fighters-<?=$schoolID?>').toggle()">
							<?=getSchoolName($schoolID, 'long', true)?>
						</a>
					</td>
					<?php foreach($paramList as $i => $params):?>
						<td><?=@$burgeePoints['schools'][$schoolID]['count'][$i]?></td>
					<?php endforeach ?>

					<td><?=$placing['score']?></td>
				</tr>

				<tr>
					<td class='hidden' colspan='100%'></td>
				</tr>

				<tr class='hidden school-fighters-<?=$schoolID?> bottom-border'>
					<td></td>
					<td colspan='100%'>
						<?php foreach($burgeePoints[$schoolID]['fighters'] as $rosterID => $fighter):?>

							<li style='margin-bottom:0.3em;'>
								<i><?=$fighter['placingName']?>:</i>
								<b><?=getFighterName($rosterID)?></b>

									<?php foreach($fighter['tournamentIDs'] as $tournamentID):?>
										&nbsp; &nbsp; &nbsp;[<?=getTournamentName($tournamentID)?>]
									<?php endforeach ?>


							</li>

						<?php endforeach ?>
					</td>
				</tr>

			<?php endforeach ?>


		</table>

		<div class='extra-burgee-<?=$burgeeID?> extra-burgee-<?=$burgeeID?>-explain hidden'>
			<u>How was this calculated?</u>
			<?=displayBurgeeRankingExplanation($paramList)?>
			Each club member is counted only once with their best result. (No matter how many tournaments they win.)
		</div>

	</fieldset>





<?php
}

/******************************************************************************/

function displayBurgeeRankingExplanation($paramList){


	foreach($paramList as $param){
		if($param['type'] == 'percent'){
			$val = round($param['value']*100)." %";
		} elseif ($param['type'] == 'place') {
			$val = round($param['value'])." placings";
		} else {
			$val = 'ERROR';
		}

		echo "<li>";
		echo "{$param['weight']} pt - Individuals who made it into the top {$val} of at least one tournament they entered.";
		echo "</li>";

	}

}

/******************************************************************************/

function dataModeForm(){

	if($_SESSION['dataModes']['percent'] == true){
		$percentClass = '';
		$absClass = 'hollow';
	} else {
		$percentClass = 'hollow';
		$absClass = '';
	}

	if($_SESSION['dataModes']['extendedExchangeInfo'] == false){
		$extEchClass = 'hollow';
		$extEchValue = 1;
	} else {
		$extEchClass = '';
		$extEchValue = 0;
	}

?>
	<form method='POST'>
		<input type='hidden' name='formName' value='toggleDataModes'>

		<button class='button <?=$percentClass?>' name='dataModes[percent]' value=1>
			% - Display Percentages
		</button>

		<button class='button <?=$absClass?>' name='dataModes[percent]' value=0>
			# - Display Totals
		</button>

		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

		<button class='button <?=$extEchClass?> secondary'
			name='dataModes[extendedExchangeInfo]' value=<?=$extEchValue?>>

			Show Extended Exchange Info
		</button>

		<?=tooltip("The extended exchange info will break down exchange totals by all specifying info available, such as 'controlled thrusts to torso with afterblow'.<BR> <b>On a large event this makes the page pretty slow to load</b> so it's disabled by default. ")?>

	</form>
<?php
}

/******************************************************************************/

function displayFloorMapButton(){

	$fullPath = logistics_getFloorplanFilePath($_SESSION['eventID']);

	if($fullPath == null){
		return;
	}

	$imgHtml = "<img class='image-box' src='{$fullPath}'>";
?>

	<a class='button tiny alert hollow' id='floor-map-toggle-button'
		onclick="logistics_toggleFloormap()">
		Event Map
	</a>

	<div class='hidden' id='floor-map'>
		<?=$imgHtml?>
	<BR><BR>
	</div>

<?php
}

/******************************************************************************/
// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
