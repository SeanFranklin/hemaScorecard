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
	if(USER_TYPE >= USER_SUPER_ADMIN){
		foreach((array)$_SESSION['alertMessages']['systemErrors'] as $message){
			displayAlert("<strong>Error: </strong>".$message, 'alert');
		}
	} else {
		// If it is a normal user, alert them that there was an error.
		if(sizeof($_SESSION['alertMessages']['systemErrors']) > 0){
			displayAlert("Appologies, but it seems we have encountered some sort of internal error.",'alert');
		}
	}

// Error messages for the user.
	foreach((array)$_SESSION['alertMessages']['userErrors'] as $message){
		displayAlert("<stron>Error: </strong>".$message,'warning');
	}

// Alert messages for the user (ie confirmation messages)
	$alertMessage = '';
	if(sizeof($_SESSION['userAlerts']) < 1){
		$alertMessage = $_SESSION['alertMessages']['userAlerts'][0];
	} else {
		$alertMessage = "<ul>";
		foreach((array)$_SESSION['alertMessages']['userAlerts'] as $message){
			$alertMessage .= "<li>{$message}</li>";
		}
		$alertMessage .= "</ul>";
	}
	displayAlert($alertMessage);
	
	unset($_SESSION['alertMessages']);
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

function pageError($type){
	
	if(strcasecmp($type, 'event') == 0){
		$str = "<strong>No Event Selected</strong><BR>
				<a href='infoSelect.php'>Select Event</a>";
	} elseif(strcasecmp($type, 'tournament') == 0){
		$str = "<strong>No Tournament Selected</strong><BR>
				Select a tournament in the upper left menu";
	} elseif(strcasecmp($type, 'user') == 0 || strcasecmp($type, 'login') == 0){
		$str = "<strong>Not Logged In</strong><BR>
				<a href='adminLogin.php'>Login</a>";
	} else {
		$str = "Page can not be displayed";
	}
	
 displayAlert($str);
}

/******************************************************************************/

function confirmDeleteReveal($formID, $formName){
	
	?>

	
	<script>
	function submitThisForm(){
		var form = document.getElementById('<?=$formID?>');
		
		var formName = document.createElement('input');
		formName.type = 'hidden';
		formName.name = 'formName';
		formName.value = '<?=$formName?>';
		form.appendChild(formName);
		
		form.submit();
	}
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
		<button class='button alert small-6 cell no-bottom' onclick="submitThisForm();">
			I still want to Delete
		</button>
		<button class='button secondary small-6 cell no-bottom' data-close aria-label='Close modal' type='button'>
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

				<?php foreach($prefixList as $ID => $name):
					$selected = isSelected($ID, $currentSettings[$tournamentID]['tournamentPrefixID']);
					?>
					
					<option value='<?=$ID?>' <?=$selected?>><?=$name?></option>
				<?php endforeach ?>
			</select>
		</div>
			
	<!-- Gender -->
		<div class='medium-3 cell text-center tournament-edit-name'>
			<i>Gender (Optional)</i>
			<select name='updateTournament[tournamentGenderID]' 
				id='genderID_div<?=$tournamentID?>'>

				<?php foreach($genderList as $ID => $name):
					$selected = isSelected($ID, $currentSettings[$tournamentID]['tournamentGenderID']);
					?>
					
					<option value='<?=$ID?>' <?=$selected?>><?=$name?></option>
					<?php endforeach ?>
			</select>
		</div>
	
	
	<!-- Material -->
		<div class='medium-3 cell text-center tournament-edit-name'>
			<i>Material (Optional)</i>
			<select name='updateTournament[tournamentMaterialID]' 
				id='materialID_div<?=$tournamentID?>'>

			<?php foreach($materialList as $ID => $name):
				$selected = isSelected($ID, $currentSettings[$tournamentID]['tournamentMaterialID']);
				?>
				
				<option value='<?=$ID?>' <?=$selected?>><?=$name?></option>
			<?php endforeach ?>
			</select>
		</div>
		
	<!-- Weapon -->
		<div class='medium-3 cell text-center tournament-edit-name'>
			<strong>Weapon</strong>
			<select name='updateTournament[tournamentWeaponID]' 
				id='weaponID_div<?=$tournamentID?>'>

				<?php foreach($weaponList as $ID => $name):
					if($ID == $currentSettings[$tournamentID]['tournamentWeaponID']){
						$selected = "selected='selected'";
					} else { 
						$selected = null; 
					} ?>
					
					<option value='<?=$ID?>' <?=$selected?>><?=$name?></option>
				<?php endforeach ?>
			</select>
		</div>

	</div>
	</div>
	
<?php }

/*****************************************************************************/

function edit_tournamentElimType($tournamentID = 'new'){
// Select menu for the type of tournament
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a select box to create a new tournament if no parameter is passed
	
	$sql = "SELECT elimTypeID, elimTypeName
			FROM systemElimTypes";
	$elimTypes = mysqlQuery($sql, KEY_SINGLES, 'elimTypeID', 'elimTypeName');
	
	if($tournamentID != 'new' && (int)$tournamentID > 0){
		$sql = "SELECT tournamentElimID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$currentID = mysqlQuery($sql, SINGLE, 'tournamentElimID');
	}
	?>
	
<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box' 
		id='elimID_div<?=$tournamentID?>'>
			
		<strong>Tournament Type</strong>

		<select name='updateTournament[tournamentElimID]' 
			onchange="edit_elimType('<?=$tournamentID?>')"
			id='elimID_select<?=$tournamentID?>'>
			
			<?php if($tournamentID == 'new'): ?>
				<option selected disabled></option>
			<?php endif ?>	
			<?php foreach($elimTypes as $ID => $name):
				if($ID == $currentID){
					$selected = 'selected';
				} else {
					$selected = null; 
				} ?>
				
				<option value='<?=$ID?>' <?=$selected?>>
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

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT tournamentElimID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$elimTypeID = mysqlQuery($sql, SINGLE, 'tournamentElimID');
		
		switch($elimTypeID){
			case POOL_BRACKET:
			case DIRECT_BRACKET:
			case POOL_SETS:
				unset($display);
				unset($nullOptionSelected);
				
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
			onchange="edit_doubleType('<?=$tournamentID?>')"
			id='doubleID_select<?=$tournamentID?>'>
			
			<option <?=$nullOptionSelected?> disabled></option>
				<?php foreach($doubleTypes as $ID => $name):
					$selected = isSelected($ID, $currentID);
					?>
					
					<option value='<?=$ID?>' <?=$selected?>><?=$name?></option>
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

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT tournamentElimID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$elimTypeID = mysqlQuery($sql, SINGLE, 'tournamentElimID');
		
		// Generate query to get eligible alogrithms based on tournament type
		switch($elimTypeID){
			case POOL_BRACKET:
				$where = "WHERE Pool_Bracket = 1";
				break;
			case POOL_SETS:
				$where = "WHERE Pool_Sets = 1";
				break;
			case SCORED_EVENT:
				$where = "WHERE Scored_Event = 1";
				break;
			default:
				$notValid = true;	
		}
		
		// Get ranking types
		if($notValid != true){
			unset($display);
			unset($nullOptionSelected);
			
			$sql = "SELECT tournamentRankingID
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$currentID = mysqlQuery($sql, SINGLE, 'tournamentRankingID');
			
			
			$sql = "SELECT rankingID, name
					FROM systemRankings
					{$where}
					ORDER BY numberOfInstances DESC";
			$rankingTypes = mysqlQuery($sql, KEY_SINGLES, 'rankingID', 'name');
		}		
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
			<?php foreach((array)$rankingTypes as $ID => $name):
				$selected = isSelected($ID, $currentID);
				?>
				
				<option value='<?=$ID?>' <?=$selected?>><?=$name?></option>
			<?php endforeach ?>
		</select>
	</div>
	
	
	
<!-- Ranking types reveal-->

	<script>
	
		function rankingDescriptionToggle(rankingID){
			$(".rankingDescription").hide();
			var divName = "rankingID"+rankingID; 
			$("#"+divName).show();
		}
	
	</script>


	<div class='reveal large' id='rankingTypesReveal' data-reveal>
	
		<h4 class='text-center'>- Ranking Types -</h4>

		<div class='grid-x grid-padding-x'>
			<div class='large-3 callout cell'>
				<ul class='menu medium-vertical show-for-medium'>
				<?php foreach($rankingTypeDescriptions as $type): ?>
					<li onclick="rankingDescriptionToggle('<?=$type['rankingID']?>')">
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
									<li onclick="rankingDescriptionToggle('<?=$type['rankingID']?>')">
										<a><?=$type['name']?></a>
									</li>
							<?php endforeach ?>
						</ul>
					</li>
				</ul>
				
				
			</div>
			<div class='large-9 cell' id='rankingDescriptionContainer'>
				<?php foreach($rankingTypeDescriptions as $type): ?>
					<div id='rankingID<?=$type['rankingID']?>' class='hidden rankingDescription'>
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

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT tournamentElimID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$elimTypeID = mysqlQuery($sql, SINGLE, 'tournamentElimID');
		
		switch($elimTypeID){
			case SCORED_EVENT:
				unset($display);
				
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
			<u>Examples:</u> Base value for a cut, or total round score before deductions.");
		?>
			
		<input type='number' name='updateTournament[basePointValue]' value='<?=$value?>' 
			onchange="enableTournamentButton('<?=$tournamentID?>')"
			id='baseValue_select<?=$tournamentID?>'>

	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentControlPoints($tournamentID = 'new'){
// Configures if a control point is used in the tournament
// Acts as a boolean flag (0=false) and the value of the control point
	
	$display = "hidden"; 			// Hidden for most cases
	$maxSize = 4;					// Arbitrary
	
	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT tournamentElimID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$info = mysqlQuery($sql, SINGLE);
		
		$sql = "SELECT useControlPoint
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$value = mysqlQuery($sql, SINGLE, 'useControlPoint');
		
		if(in_array($info['tournamentElimID'], array(2,3,4)) ){
			unset($display);
		}
	}
	
	if($value == null){
		$sql = "SELECT useControlPoint
				FROM eventDefaults
				WHERE eventID = {$_SESSION['eventID']}";
		$value = mysqlQuery($sql, SINGLE, 'useControlPoint');
		if($value == null){$value = 0;}
	}
	?>

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='controlPoint_div<?=$tournamentID?>'>
			
		Use Control Point

		<select name='updateTournament[useControlPoint]'
			id='controlPoint_select<?=$tournamentID?>'>
			
				<?php 
					$selected = isSelected(0, $value);
					echo "<option value=0 {$selected}>No</option>";
					for($i = 1; $i <= $maxSize; $i++):
					$selected = isSelected($i, $value);
					?>
					<option value=<?=$i?> <?=$selected?>><?=$i?> Point<?=plrl($i)?></option>
				<?php endfor ?>
			
		</select>		
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentMaxDoubles($tournamentID = 'new'){
// Select menu for the maximum doubles allowed in a tournament
// Appears or disapears as controled by javascript
	
	$display = "hidden"; 		// Hidden for most cases
	$maxDoubles = 10;			// Arbitrary

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT tournamentElimID, doubleTypeID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$info = mysqlQuery($sql, SINGLE);
		
		$sql = "SELECT maxDoubleHits
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$value = mysqlQuery($sql, SINGLE, 'maxDoubleHits');
		
		if(in_array($info['tournamentElimID'], array(2,3,4)) AND $info['doubleTypeID'] != 3){
			unset($display);
		}
	}
	
	if($value == null){
		$sql = "SELECT maxDoubleHits
				FROM eventDefaults
				WHERE eventID = {$_SESSION['eventID']}";
		$value = mysqlQuery($sql, SINGLE, 'maxDoubleHits');
		if($value == null){$value = 3;}
	}
	?>

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='maxDoubles_div<?=$tournamentID?>'>
			
	Maximum Double Hits

		<select name='updateTournament[maxDoubleHits]' 
			id='maxDoubles_select<?=$tournamentID?>'>
			
			<?php for($i = 1; $i <= $maxDoubles; $i++):
				$selected = isSelected($i, $value);
				?>
				
				<option value=<?=$i?> <?=$selected?>><?=$i?></option>
			<?php endfor ?>
		</select>		
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentMaxPoolSize($tournamentID = 'new'){
// Select menu for the maximum pool size allowed in a tournament
// Appears or disapears as controled by javascript
	
	$display = "hidden"; 			// Hidden for most cases
	$maxSize = 10;					// Arbitrary
	
	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT tournamentElimID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$info = mysqlQuery($sql, SINGLE);
		
		$sql = "SELECT maxPoolSize
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$value = mysqlQuery($sql, SINGLE, 'maxPoolSize');
		
		if(in_array($info['tournamentElimID'], array(2,4)) ){
			unset($display);
		}
	}
	
	if($value == null){
		$sql = "SELECT maxPoolSize
				FROM eventDefaults
				WHERE eventID = {$_SESSION['eventID']}";
		$value = mysqlQuery($sql, SINGLE, 'maxPoolSize');
		if($value == null){$value = 5;}
	}
	?>

<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='maxPoolSize_div<?=$tournamentID?>'>
			
		Maximum Pool Size

		<select name='updateTournament[maxPoolSize]'
			id='maxPoolSize_select<?=$tournamentID?>'>
				<?php for($i = 2; $i <= $maxSize; $i++):
					$selected = isSelected($i, $value);
					?>
					
					<option value=<?=$i?> <?=$selected?>><?=$i?></option>
				<?php endfor ?>
			
		</select>		
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentNormalization($tournamentID = 'new'){
// Select menu for the normalization pool size All pools results will be 
// scaled to this size to ensure fairness across different sized pools.
// Appears or disapears as controled by javascript.
	
	$display = "hidden"; 			// Hidden for most cases
	$maxSize = 10;					// Arbitrary

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT tournamentElimID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$info = mysqlQuery($sql, SINGLE);
		
		$sql = "SELECT normalizePoolSize
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$value = mysqlQuery($sql, SINGLE, 'normalizePoolSize');
		
		if(in_array($info['tournamentElimID'], array(2,4)) ){
			unset($display);
		}
	}
	
	if($value == null){
		$sql = "SELECT normalizePoolSize
				FROM eventDefaults
				WHERE eventID = {$_SESSION['eventID']}";
		$value = mysqlQuery($sql, SINGLE, 'normalizePoolSize');
		if($value == null){$value = 4;}
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
			<?php for($i = 2; $i <= 10; $i++):
				$selected = isSelected($i, $value);
				?>
				
				<option value=<?=$i?> <?=$selected?>><?=$i?></option>
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
	
	$colors = getColors();
	
	if($tournamentID != 'new' && (int)$tournamentID > 0){

		$sql = "SELECT tournamentElimID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$elimTypeID = mysqlQuery($sql, SINGLE, 'tournamentElimID');
		
		if($elimTypeID == POOL_SETS || $elimTypeID == POOL_BRACKET || $elimTypeID == DIRECT_BRACKET){
			unset($display);
			
			
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
			
			<?php foreach($colors as $color):
				$selected = isSelected($color['colorID'], $currentID);
				?>
				
				<option value='<?=$color['colorID']?>' <?=$selected?>>
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

		if(isPools($tournamentID) || isBrackets($tournamentID)){
			unset($display);
			
			$sql = "SELECT allowTies
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$allowTies = (int)mysqlQuery($sql, SINGLE, 'allowTies');

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

function edit_tournamentNetScore($tournamentID = 'new'){
// Select menu for whether or not the tournament uses net score for Full Afterblow
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a box to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		if(isPools($tournamentID) || isBrackets($tournamentID)){
			
			$sql = "SELECT isNotNetScore
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$noNetScore = (int)mysqlQuery($sql, SINGLE, 'isNotNetScore');

			$doubleType = getDoubleTypes($tournamentID);
			if($doubleType['afterblowType'] == 'full'){
				unset($display);
			}
			$selected0 = isSelected($noNetScore,0);	
			$selected1 = isSelected($noNetScore,1);	

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
			<option value='0' <?=$selected0?> >Yes</option>
			<option value='1' <?=$selected1?> >No</option>
			
		</select>
		
	</div>
	
<?php }

/******************************************************************************/

function edit_tournamentTimer($tournamentID = 'new'){
// Select menu for whether or not the tournament uses a timer
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		if(isPools($tournamentID) || isBrackets($tournamentID)){
			unset($display);
			
			$sql = "SELECT useTimer
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$useTimer = (int)mysqlQuery($sql, SINGLE, 'useTimer');

		}
	} elseif($tournamentID == 'new') {
		$eventID = $_SESSION['eventID'];
		
		$sql = "SELECT useTimer
				FROM eventDefaults
				WHERE eventID = {$eventID}";
				
				
		$useTimer = (int)mysqlQuery($sql, SINGLE, 'useTimer');
	}
	$selected = isSelected(1 == $useTimer);
	
	?>
	


<!-- Start display -->
	<div class='medium-6 large-3 cell tournament-edit-box <?=$display?>' 
		id='useTimer_div<?=$tournamentID?>' >
			
		Use Timer
		
		<select name='updateTournament[useTimer]'
			id='useTimer_select<?=$tournamentID?>'>
			<option value='0'>No</option>
			<option value='1' <?=$selected?>>Yes</option>
			
		</select>
		
	</div>
	
<?php }

/**********************************************************(*******************/

function edit_tournamentCuttingQual($tournamentID = 'new'){
// Select menu for whether or not the tournament has a cutting qualification
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		if(isPools($tournamentID) || isBrackets($tournamentID) || isRounds($tournamentID)){
			unset($display);
			
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
			id='allowTies_select<?=$tournamentID?>'>
			<option value='0'>No</option>
			<option value='1' <?=$selected?>>Yes</option>
			
		</select>
		
	</div>
	
<?php }

/****************************************************(*************************/

function edit_tournamentMaxExchanges($tournamentID = 'new'){
// Select menu for whether or not the tournament allows ties
// Calls to javascrip on change to alter the form based	on it's selection
// Appears as a checkbox to create a new tournament if no parameter is passed
	

	$display = "hidden"; // Hidden for most cases

	if($tournamentID != 'new' && (int)$tournamentID > 0){

		if(isPools($tournamentID) || isBrackets($tournamentID)){
			unset($display);
			
			$sql = "SELECT maximumExchanges
					FROM eventTournaments
					WHERE tournamentID = {$tournamentID}";
			$maxExchanges = mysqlQuery($sql, SINGLE, 'maximumExchanges');

		}
	} elseif($tournamentID == 'new') {
		
		// Pull from event defaults
		
	}
	$selected = isSelected(1 == $allowTies);
	if($maxExchanges == 0){
		unset($maxExchanges);
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

/*******************************************************(**********************/

function toggleFighterListSort(){
// Hidden form to be submited to change the sorting mode of roster displays
// ie. Sort by laste name, sort by school	
	?>
	
	<form method='POST' id='rosterViewMode'>
		<input type='hidden' name='formName' value='rosterViewMode'>
	</form>
	
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
		data-disable-hover='false' tabindex='2' title='<?=$text?>'
		data-position='<?=$dir?>' data-allow-html='true' >
		
		<?=$tip?>
		
	</span>
	
<?php }

/******************************************************************************/

function poolSetNavigation(){
// Buttons to navigate between pool sets, only display if it is a pool set tournament	
	
	
// Check that the tournament has pool sets
	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){
	 displayAlert('No Tournament selected for poolSetNavigation in display_functions.php');
		return;
	}
	
	$sql = "SELECT numGroupSets, tournamentElimID
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$result = mysqlQuery($sql, SINGLE);
	
	$numGroupSets = $result['numGroupSets'];
	$tournamentElimID = $result['tournamentElimID'];

	if($numGroupSets <= 1){ return 1; }
	?>
	
<!-- Start display -->
	<form method='POST' style='display:inline'>
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
	
	</form>
	
<?php 
	return $numGroupSets;
}

/******************************************************************************/
 
 function bracket_finalistDropDown($fighterNum,$matchID, $finalists, $seedID){
// Creates a drop down list of all the fighters provided
// Used in elimination brackets

	$rankedNames = $finalists;
	$tournamentID = $_SESSION['tournamentID'];
	?>

	
	<select name='newFinalists[<?=$matchID?>][<?=$fighterNum?>]' class='bracket-select' <?=LOCK_TOURNAMENT?>>
		<option value=''></option>
		<?php foreach($rankedNames as $fighter):
			if(isset($fighter['rank']) AND $_SESSION['bracketHelper'] != 'on'):
				$rank = "#{$fighter['rank']} - ";
			endif;
			$selected = isSelected($fighter['rosterID'], $seedID);
			?>
			
			<option value='<?=$fighter['rosterID']?>' <?=$selected?>>
				<?=$rank?><?=getFighterName($fighter['rosterID'])?>
			</option>
			
		<?php endforeach ?>
	</select>
<?php }

/******************************************************************************/

function bracket_finalistEntry($fighterNum,$matchInfo, $bracketInfo, $finalists, $seedID){
// Creates entry field or displays previously entered fighter
// for the bracket position specified

	$matchID = $matchInfo['matchID'];
	if($fighterNum == 1){
		$fighterID = 'fighter1ID';
		$color = COLOR_CODE_1;
	}else if($fighterNum == 2){
		$fighterID = 'fighter2ID';
		$color = COLOR_CODE_2; 
	}else { 
	 displayAlert("Error in 'finalistEntry' !!!!!!");
	}
	?>
	
<!-- If no data exists for the bracket position	-->
	<?php if($matchInfo[$fighterID] == 0 || $matchInfo[$fighterID] == null):
	
		if($fighterNum == 1){
			$class = "bracket-top-slot";
		}
		?>
		
		<div class='<?=$class?>'>
							
		<?php 
			if(USER_TYPE >= USER_STAFF){
				// Staff and higher can add fighters
				bracket_finalistDropDown($fighterNum, $matchID, $finalists, $seedID);
			} else {
				// Blank for guests
				echo "&nbsp;";
			}
		?>
		
		</div>
		

<!-- If data exists for the bracket position -->		
	<?php else: 
		$name = getFighterName($matchInfo['fighter'.$fighterNum.'ID']);
		$score = $matchInfo['fighter'.$fighterNum.'Score'];
		
		// If is match winner
		if ($matchInfo['fighter'.$fighterNum.'ID'] == $matchInfo['winnerID']){
			$style .= "font-weight: bold; ";
		}
		
		if($fighterNum == 1){
			$class = "bracket-top-slot";
		}
		
		if(USER_TYPE >= USER_STAFF){	// shows fighter color for staff only
			$style .= "background-color: {$color}; ";
		}
		?>

		<div class='<?=$class?>' style='<?=$style?> padding-left: 3px;'>
			<?=$score?> <?=$name?>
		</div>

	<?php endif ?>

<?php }

/******************************************************************************/

function bracket_management($tournamentID, $allBracketInfo, $finalists){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}

	?>

<!-- Bracket Management -->
	<div style='margin-top: 50px;'>&nbsp;</div>
	
	<?php if(USER_TYPE < USER_STAFF){ return; } ?>
	
	<fieldset class='fieldset'>
	<legend>Bracket Management</legend>

	<div class='grid-x grid-padding-x'>
		
<!-- Create Bracket -->	

	<?php if($allBracketInfo == null): ?>
		<?php if(USER_TYPE >= USER_ADMIN): ?>
			<div class='large-3 medium-4 cell'>
				<a class='button expanded' data-open='createBracket' <?=LOCK_TOURNAMENT?>>
					Create Bracket
				</a>
			</div>
		<?php endif ?>
		
<!-- Bracket Exists -->
	<?php else: ?>
	
	
	
	<!-- Delete Bracket -->
		<?php if(USER_TYPE >= USER_ADMIN): ?>
		<div class='large-3 medium-4 cell'>
			<a class='button alert expanded' data-open='deleteBracket' <?=LOCK_TOURNAMENT?>>
				Delete Bracket
			</a>
		</div>
		<?php endif ?>

	<?php endif ?>
	
	</div>

	</fieldset>
	
	
<!-- Create Bracket Box ------------------------------------------------------->

	<?php $maxBracketSize = 64; ?>
	<div class='reveal tiny' id='createBracket' data-reveal>
	<form method='POST'>
	<fieldset <?=LOCK_TOURNAMENT?>>
		<h5>Create Bracket</h5>
		
		<form method='POST'>
	
		<div class='input-group'>
			<span class='input-group-label'>Number of Fighters:</span>
			<input class='input-group-field' type='number' name='numWinnerBracketFighters'
				min=2 max=<?=$maxBracketSize?> >
		</div>
		<div class='input-group grid-x'>
			<span class='input-group-label small-12'>
				Consolation Bracket for top # of Fighters:
			</span>
			<input class='input-group-field' type='number' 
				name='numLooserBracketFighters' 
				placeholder='Leave blank for single elim'
				min=2 max=<?=$maxBracketSize?>>
		</div>	
	
	
	
	<!-- Submit buttons -->
		<div class='grid-x grid-margin-x'>
			<button class='button success small-6 cell' name='formName' 
				value='createBracket' <?=LOCK_TOURNAMENT?>>
				Create
			</button>
			<button class='button secondary small-6 cell' data-close aria-label='Close modal' 
				type='button' <?=LOCK_TOURNAMENT?>>
				Cancel
			</button>
		</div>
		</fieldset>
		</form>
		
	<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>
	
	
<!-- Delete Bracket Box ------------------------------------------------------->

	<div class='reveal' id='deleteBracket' data-reveal>
		<fieldset <?=LOCK_TOURNAMENT?>>
		<form method='POST'>
		<h4 class='text-center'>Warning!</h4>
		<p>You are about to erase all finals brackets for this tournament.<BR>
		This includes data from any matches in the winners & consolation brackets.</p>
		
		<div class='grid-x grid-margin-x'>
		
			<button class='button alert small-6 cell' name='formName' value='deleteBracket'
				<?=LOCK_TOURNAMENT?>>
				Delete Bracket
			</button>
			<button class='button secondary small-6 cell' data-close aria-label='Close modal' 
				type='button' <?=LOCK_TOURNAMENT?>>
				Cancel
			</button>
		
		</div>
		</fieldset>
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>
	
<?php }

/******************************************************************************/

function bracket_display($bracketInfo, $finalists,$type,$bracketAdvancements){
// Displays the bracket described by $bracket info	
	
// Initialization
	$tournamentID = $bracketInfo['tournamentID'];
	$bracketLevels = $bracketInfo['bracketLevels'];
	$bracketID = $bracketInfo['groupID'];
	if($type == 'win' OR $type == 'winner' OR $type == 'winners'){
		$isWinnersBracket = true;
	} else {
		$isWinnersBracket = false;
	}

	if($isWinnersBracket AND isset($bracketInfo['loserID'])){
		$loserID = $bracketInfo['loserID'];
		$isSingleElim = true;
	} else {$isSingleElim = false;}

	$bracketMatches = getBracketMatchesByPosition($bracketID);
	
	// Warning if there are ties
	$numTies = getNumNoWinners($bracketID);
	if($numTies > 0 && $_SESSION['bracketHelper'] == 'on'){
		$_SESSION['bracketWarnings'][] = "
		<u>Warning:</u> One of the elimination bracket matches has ended with no winner.<BR>
		I hope you know what you are doing, because the bracket helper no longer does.
		";
	}
	
	
	// php to generate css based on bracket properties
	include('finalsCSS.php');	
	?>

<!-- Buttons above bracket -->	
	<form method='POST' style='display:inline;'>
	
	<input type='hidden' name='formName' value='updateBracket'>
	<input type='hidden' name='groupID' value='<?=$bracketID?>'>

	<?php if(USER_TYPE >= USER_STAFF): ?>
		
		<button class= 'button success' name='updateBracket' value='newFighters' <?=LOCK_TOURNAMENT?>>
			Add Fighters
		</button>
		<button class= 'button alert' name='updateBracket' value='clearMatches' <?=LOCK_TOURNAMENT?>>
			Clear Selected
		</button>
			
		<?php 
		foreach((array)$_SESSION['bracketWarnings'] as $type => $warning):
			if($isWinnersBracket && $type === 'loser'){ continue; }
		?>
		
			<div class='callout secondary' data-closable>
				<?=$warning?>
				<button class="close-button" aria-label="Dismiss secondary" type="button" data-close>
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		<?php endforeach ?>
		
		<?php unset($_SESSION['bracketWarnings']); ?>
		
	<?php endif ?>

<!-- Bracket display -->
	<div id='tournament_box'>
		
	<?php	
	for($currentLevel=$bracketLevels;$currentLevel >=1;$currentLevel--):
		echo"<div class='tier'>";
		insertTierName($currentLevel, $isWinnersBracket,$isSingleElim);
		
		if($isWinnersBracket){ 
			$maxMatchesAtLevel = pow(2,$currentLevel-1);
		} else {
			$maxMatchesAtLevel = getNumEntriesAtLevel_consolation($currentLevel,'matches');
		}
		
		for($bracketPosition = 1;$bracketPosition<=$maxMatchesAtLevel;$bracketPosition++):
			$matchInfo = $bracketMatches[$currentLevel][$bracketPosition];
	
			if($matchInfo == null){
				$isNotBlank = false;
			} else {$isNotBlank = true;}
			
			// Displays 3rd place match in single elims & bracket advancements
			if($isWinnersBracket){
				if($isSingleElim AND $currentLevel == 1){
					$loserMatches = getBracketMatchesByPosition($loserID);
					$matchInfo = $loserMatches[1][1];
					$seed1 = $bracketAdvancements['losers'][$currentLevel][$bracketPosition][1]['rosterID'];
					$seed2 = $bracketAdvancements['losers'][$currentLevel][$bracketPosition][2]['rosterID'];
				} else {
					$seed1 = $bracketAdvancements['winners'][$currentLevel][$bracketPosition][1]['rosterID'];
					$seed2 = $bracketAdvancements['winners'][$currentLevel][$bracketPosition][2]['rosterID'];
				}
			} else {
				$seed1 = $bracketAdvancements['losers'][$currentLevel][$bracketPosition][1]['rosterID'];
				$seed2 = $bracketAdvancements['losers'][$currentLevel][$bracketPosition][2]['rosterID'];
			}
			
			$name = "depth{$currentLevel}";
			$matchID = $matchInfo['matchID'];
			?>
			
<!--  Bracket level -->
		<div class='<?=$name?>'>
			<div class='centerCrap'>
			<div class='grid-x grid-padding-x text-center' style='width:<?=$boxWidth?>px;'>
				<div class='large-2 small-2 medium-2 align-self-middle text-center'>
					<?php if($isNotBlank){
						goToMatchButton($matchInfo);
					} ?>
				</div>
				<div class='large-10 small-10 medium-10'>
	
				<?php if($isNotBlank): ?>
					<?php bracket_finalistEntry(1,$matchInfo, $bracketInfo, $finalists,$seed1); ?>
					<?php bracket_finalistEntry(2,$matchInfo, $bracketInfo, $finalists,$seed2); ?>
					<a name='anchor<?=$matchID?>'></a>
				<?php endif ?>	
				</div>
			</div>
			</div>
			
		<!-- Vertical Lines -->
			<?php if($currentLevel > 1 AND $isNotBlank 
				AND ($isWinnersBracket OR $currentLevel % 2 == 0)):
					
				if($bracketPosition % 2 != 0){
					$name .='_rightTop';
				} else {
					$name .= '_rightBottom';
				} ?>
			
				<div class='<?=$name?>'>
				</div>
			<?php endif ?>
			
				
			</div>
			
			<?php 
			if($isSingleElim AND $currentLevel == 1 AND $isWinnersBracket){
				$isSingleElim = false;
				$currentLevel++;
			}
			?>
			
		<?php endfor ?>
		</div>
		
	<?php endfor ?>
	
	</div>

	</form>

<?php }

/******************************************************************************/

function insertTierName($level, $type, $isSingleElim){
// Inserts the name of the bracket level
// Context dependent based on winners/consolation bracket	
	
	if($type == 1 OR $type == 'winner' OR $type=='winners'){
		switch($level){
			case 1:
				if($isSingleElim){
					$name = '3rd Place';
				} else {
					$name = '1st Place';
				}
				break;
			case 2: 
				$name = 'Semi-Finals';
				break;
			case 3:
				$name = 'Quarter-Finals';
				break;
			default:
				$name = 'Bracket';
				break;
		}
	} else {
		switch($level){
			case 1:
				$name = '3rd Place';
				break;
			default:
				$bracketInfo = getBracketInformation($_SESSION['tournamentID']);
				$max = $bracketInfo['loser']['numFighters']+2;
				$n = getNumEntriesAtLevel_consolation($level,'fighters')+2;
				if($n > $max){$n = $max;}
				$name = "Top {$n}";
				break;
		}
	}
	
	echo "<h3 class='center'>{$name}</h3>";
}

/******************************************************************************/

function matchHistoryBar($matchInfo){
// displays all the fight's past exchanges


// Getting info and formating for summary
	$matchID = $matchInfo['matchID'];
	$exchangeInfo = getMatchExchanges($matchID);
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
		$t = $exchange['exchangeTime'];
		$m = (int)($t/60);
		$s = $t - ($m * 60);
		if($s < 10){
			$s = "0".$s;
		}

		$exchanges[$i]['time'] = "{$m}:{$s}";

		if($exchange['rosterID'] == $matchInfo['fighter1ID']){
			$index1 = 1;
			$index2 = 2; 
		} else {
			$index1 = 2;
			$index2 = 1; 
		}

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
							unset($exchanges[$i][$index1][1]);
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
	function displayExchangeReg($exchange, $num = null){
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
		
		if($num % 2 != 1){ 
			$class= 'old-exch-odd'; 
		} else {
			$odd= "opacity:0.92;";
		}
		
		?>	
		
		<div class='shrink text-center' style='width: 40px'>
			<?php if(IS_TIMER): ?>
				<div class='cell <?=$class?>'>
					<?=$exchange['time']?>
				</div>
			<?php endif ?>
			<div class='cell f1-BG' style='<?=$odd?>'>
				<?=$t1?><BR><?=$t2?>
			</div>
			<div class='cell f2-BG' style='<?=$odd?>'>
				<?=$b1?><BR><?=$b2?>
			</div>
		</div>
		
		<?php
	}
	
/* Function to display each exchange on small screens ***/
	function displayExchangeSmall($exchange, $num = null){
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
		
		if($num % 2 != 1){ 
			$class= 'old-exch-odd'; 
		} else {
			$odd= "opacity:0.92;";
		}
		
		?>	
		
		<tr class='old-exch-mini'>
			<td class='<?=$class?>'>
				<?php if(IS_TIMER){
					echo $exchange['time'];
				} else {
					echo $num;
				} ?>
			</td>
			<td class='f1-BG' style='<?=$odd?>'>
				<?=$t1?>
			</td>
			<td class='f1-BG' style='<?=$odd?>'>
				<?=$t2?>
			</td>
			<td class='f2-BG' style='<?=$odd?>'>
				<?=$b1?>
			</td>
			<td class='f2-BG' style='<?=$odd?>'>
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
		<div class='shrink'>
			<?php if(IS_TIMER): ?>
				<div class='cell text-center'>
					<strong>Time:</strong>
				</div>
			<?php endif ?>
			<div class='cell old-exch-large-first text-center f1-BG'>
				<strong>Score:<BR><?=$matchInfo['fighter1score']?></strong>
			</div>
			<div class='cell old-exch-large-first text-center f2-BG'>
				<strong>Score:<BR><?=$matchInfo['fighter2score']?></strong>
			</div>
		</div>
		<?php foreach($exchanges as $num => $exchange){
			displayExchangeReg($exchange, $num);
		} ?>
	</div>
	</div>
	
<!-- Small size fight history -->

	<div class='large-12 cell show-for-small-only'>
	<table>
	<caption>Match Exchanges</caption>	
	<?php foreach($exchanges as $num => $exchange){
		displayExchangeSmall($exchange, $num);
	} ?>
	</table>
	</div>


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
	
		<?php if(USER_TYPE >= USER_STAFF): ?>
			<button class='button success hollow small' style='margin-bottom: 5px;' 
				name='updateBracket' value='newFighters' <?=LOCK_TOURNAMENT?>>
				Add
			</button>
		<?php else: ?>
			<BR>
		<?php endif ?>

	<?php endif ?>
	
	<!-- Checkbox for staff to delete fighters from a match -->
	<?php if(USER_TYPE >= USER_STAFF): ?>
		<input type='checkbox' class='no-bottom'  name='clearMatch[<?=$matchID?>]' 
			value='true' <?=LOCK_TOURNAMENT?>>
	<?php endif ?>
	
	
<?php }

/******************************************************************************/

function addYoutube($matchID, $divider = true){
// Displays youtube link for match if it exists, and allows staff to add links

	$url = getYouTube($matchID);
?>

<!-- Display entry field for staff -->
	<?php if(USER_TYPE >= USER_VIDEO): ?>
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

function bracketHelperToggleButton($allBracketInfo, $finalists, $isLoserBracket = null){
// Creates a button to toggle the bracket helper on/off
// If the helper will go into a 'try' state if it is attempted to be enabled
// while there are incomplete pool matches, informing the user of the difficulty.

	if($_SESSION['userType'] < USER_STAFF){ return; }
		
// Checks if bracket helper is attempted to be turned on
// and turns on if no incomplete matches
	if($_SESSION['bracketHelper'] == 'try'){
		$_SESSION['incompletePoolMatches'] = getTournamentIncompletes($tournamentID, 'pool');	
		if($_SESSION['incompletePoolMatches'] == null){
			$_SESSION['bracketHelper'] = 'on';
		}	
		
	}
	
	// For the tooltip
	$descriptionText = "Automatically seeds based on pool placements, 
			and suggests bracket advancements based on fight winners.";
	
	
	if($_SESSION['bracketHelper'] == 'on' && !$isWinnersBracket){
		// Warns the user that the bracket helper is not defined for consolation
		// brackets that are not for a number of fighters equal to a power of 2
		$s = (int)($allBracketInfo['loser']['numFighters']+2);
	
		if(($s & ($s - 1)) != 0){ // is not a power of 2
			
			$_SESSION['bracketWarnings']['loser'] = "<u>Warning:</u> 
			Consolation Bracket Helper not configured for this size bracket.<BR>
			Seeding results may be unpredictable.";
		}
	}
	?>
	
<!-- Start display -->
	<?php if($_SESSION['bracketHelper'] == 'try'): ?>
		<div class='callout secondary'>
	<?php endif ?>
	
	<form method='POST' style='display:inline'>
	
	
<!-- Text for the toggle button -->
	<?php switch($_SESSION['bracketHelper']):
		case 'on': ?>
			<button class='button' value='toggleBracketHelper' name='formName' <?=LOCK_TOURNAMENT?>>
				Disable Bracket Helper
			</button>
			<?php break;
		case 'try': ?>
			There are still incomplete pool matches. 
			Finalize all matches before to ensure pool rankings are accurate.<BR>
			<input type='hidden' name='formName' value='toggleBracketHelper'>
			<button class='button secondary' value='toggleBracketHelper' name='formName' <?=LOCK_TOURNAMENT?>>
				Cancel
			</button>
			<button class='button hollow warning' value='true' name='bracketHelperOverride' <?=LOCK_TOURNAMENT?>>
				Override
			</button>

			<?php break;
		case 'off': 
		default: ?>
			<button class='button' value='toggleBracketHelper' name='formName' <?=LOCK_TOURNAMENT?>> 
				Enable Bracket Helper
			</button>
			<?php tooltip($descriptionText); ?>
			<?php break;
	endswitch; ?>
	
	</form>
	
<!-- Displays incomplete pool matches inhibiting bracket helper from enabling -->


	<?php
	if(isset($_SESSION['incompletePoolMatches'])){
		displayIncompleteMatches($_SESSION['incompletePoolMatches']);
	}
	?>
	
	<?php if($_SESSION['bracketHelper'] == 'try'): ?>
		</div>
	<?php endif ?>
	
	
	
	
	

<?php }

/******************************************************************************/

function displayIncompleteMatches($incompleteMatches){
// Displays incomplete matches from a tournament
// Used to show staff which events are not complete if they need to 
// close them all to enable the bracket helper	
	
	?>
	
	<form method='POST'>
	<input type='hidden' name='formName' value='goToMatch'>
	
	The following pool matches are incomplete:
	<?php foreach($incompleteMatches as $matchID):
		$matchInfo = getMatchInfo($matchID);
		$name1 = getFighterName($matchInfo['fighter1ID']);
		$name2 = getFighterName($matchInfo['fighter2ID']);
		$poolName = getGroupName($matchInfo['groupID']);
		?>
		
		<BR>
		<button class='button' name='matchID' value='<?=$matchID?>'>
			<?=$poolName?> - <?=$name1?> vs <?=$name2?>
		</button>
	
	<?php endforeach ?>
	
	</form>
	
	<?php unset($_SESSION['incompletePoolMatches']); ?>
<?php }

/********************************************************************((********/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
