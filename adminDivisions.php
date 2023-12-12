<?php
/*******************************************************************************
	Event Details

	Change event passwords and set defaults for newly created tournaments
	LOGIN:
		- ADMIN or higher required to view

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Tournament Divisions';
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

	$tournamentIDs = getEventTournaments($_SESSION['eventID']);
	$tournamentDivisions = getTournamentDivisions($_SESSION['eventID']);




// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<div class='grid-x grid-margin-x'>

	<p class='cell'>Tournament divisions allow you to group tournaments together, such as creating on "Open Longsword" tournament with "Longsword Tier-A" and "Longsword Tier-B" grouped underneath. This doesn't affect how the tournaments are run, but makes it easier to keep track when you have long tournament lists that you want to keep organized.</p>



	<?php foreach($tournamentDivisions as $div):?>
		<?=tournamentDivisions($div, $tournamentIDs)?>
	<?php endforeach ?>

	<?=tournamentDivisions(null, $tournamentIDs)?>

	</div>


<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function tournamentDivisions($divInfo, $tournamentIDs){

	if($divInfo == null){
		$divisionID = -1;
		$divisionName = '';
		$placeholder = 'Add new division';
	} else {
		$divisionID = $divInfo['divisionID'];
		$divisionName = $divInfo['divisionName'];
		$placeholder = '';
	}

	$divisionItems = (array)getTournamentDivisionItems($divisionID);

	$toDisplay = [];
	foreach($tournamentIDs as $tournamentID){

		$tmp = [];
		$tmp['ID'] = $tournamentID;
		$tmp['name'] = getTournamentName($tournamentID);

		if(isset($divisionItems['items'][$tournamentID]) == true){
			$tmp['check'] = "checked";
		} else {
			$tmp['check'] = "";
		}

		$toDisplay[] = $tmp;

	}


?>

	<form method='POST' class='cell large-7'>
	<fieldset class='fieldset cell'>

		<legend>
			<div class='input-group no-bottom'>
				<span class='input-group-label no-bottom'>Division Name:</span>
				<input type='text' class='input-group-field no-bottom' name='divisionInfo[divisionName]' value='<?=$divisionName?>' placeholder='<?=$placeholder?>' required>
				<span class='input-group-label no-bottom'>
					<b><a onclick="$('#division-form-for-<?=$divisionID?>').toggleClass('hidden')">↓</a></b>
				</span>
			</div>
		</legend>

		<input class='hidden' name='formName' value='updateTournamentDivisions'>
		<input class='hidden' name='divisionInfo[eventID]' value='<?=$_SESSION['eventID']?>'>

		<div class='hidden' id='division-form-for-<?=$divisionID?>'>

		<button class='button success' name='divisionInfo[divisionID]' value=<?=$divisionID?>>
			Update Division
		</button>

		<?php foreach($toDisplay as $t):?>
			<BR>
			<input type='checkbox' class='radio no-bottom' name='divisionInfo[tournamentIDs][<?=$t['ID']?>]' value=<?=$t['ID']?> <?=$t['check']?>>
			<?=$t['name']?>

		<?php endforeach ?>

		</div>

	</fieldset>
	</form>

<?php
}

/******************************************************************************/

function tournamentDivisionsPaddle($subID, $baseID, $subTournamentBases){

	$id = "sub-{$subID}-base-{$baseID}" ;
	$name = "tournamentDivs[baseID][{$subID}]";

	if(isset($subTournamentBases[$subID]) == false && $subID == $baseID){
		$isChecked = 'checked';
	}
	elseif(isset($subTournamentBases[$subID]) == true && $subTournamentBases[$subID] == $baseID){
		$isChecked = 'checked';
	} else {
		$isChecked = '';
	}

?>

	<input class='no-bottom' type='radio' <?=$isChecked?>
			id='<?=$id?>'
			name='<?=$name?>' value='<?=$baseID?>'>


<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
