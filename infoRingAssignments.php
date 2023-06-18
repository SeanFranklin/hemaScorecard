<?php
/*******************************************************************************
	Logistics Locations

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Fighter Locations';
$includeTournamentName = false;
$jsIncludes[] = 'logistics_management_scripts.js';
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif($_SESSION['tournamentID'] == null){
	pageError('tournament');
} elseif (ALLOW['VIEW_SCHEDULE'] == false){
	displayAlert("Event is still upcoming<BR>Schedule not yet released");
} else {


	$entryList = getTournamentRosterByLocation($tournamentID);
	if(isset($_SESSION['fighterListColumns']) == true){
		$numColumns = (int)$_SESSION['fighterListColumns'];
		unset($_SESSION['fighterListColumns']);
	} else {
		$numColumns = 0;
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>


	<?php if($numColumns == 0):?>

		<form method='POST'>
		<div class='input-group'>
			<span class='input-group-label'>Number of Columns</span>
			<select class='input-group-field' name='fighterListColumns'>
				<option value='1'>1</option>
				<option value='2'>2</option>
				<option value='3' selected>3</option>
			</select>
			<button class='button input-group-button success' name='formName' value='setFighterListColumns'>
				Populate
			</button>
		</div>
		</form>

	<?php else: ?>
		<table>

		<?php foreach($entryList as $index => $entry): ?>


			<?php if(($index % $numColumns) == 0): ?>
				</tr><tr>
			<?php endif ?>

			<td style='border-left:solid 1px black'><strong><?=$entry['fighterName']?></strong></td>
			<td><?=$entry['groupName']?></td>
			<td><?=$entry['location']?></td>

		<?php endforeach ?>

		</table>
	<?php endif ?>

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/******************************************************************************/


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
