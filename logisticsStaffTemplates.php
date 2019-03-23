<?php
/*******************************************************************************
	Logistics Staff Templates
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Logistics Staffing Templates';
$includeTournamentName = false;
$hideEventNav = true;
$jsIncludes[] = 'logistics_management_scripts.js';
include('includes/header.php');


if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false) {
	pageError('user');
} elseif($_SESSION['tournamentID'] == null) {
	pageError('tournament');
} elseif(logistics_isTournamentScheduleUsed($_SESSION['eventID']) == false){
	displayAlert("A schedule has not been created for this event.");
}  else {

	$roles = logistics_getRoles();
	$template = logistics_getStaffTemplate($_SESSION['tournamentID']);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>

	<h4>Template for: <strong><?=getTournamentName($_SESSION['tournamentID'])?></strong></h4>
	<em>(Change the current tournament in the upper left to assign templates to a different tournament.)</em>

	<div class='grid-x grid-margin-x'>
	<div class='large-4 medium-8 cell'>

	<form method='POST'>
	<table>
		<tr>
			<th>Role</th>
			<th>
				Num Spots
				<?=tooltip("Leave blank for unset.<BR><BR>
							Adding '0' will check to make sure none are assigned.")?>
			</th>
		</tr>

		<?php foreach($roles as $role): 
			if(isset($template[$role['logisticsRoleID']]) == true){
				$value = $template[$role['logisticsRoleID']];
			} else {
				$value = '';
			}
			?>
			<tr>
				<td>
					<?=$role['roleName']?>
				</td>
				<td>
					<input type='number' placeholder='none' min=0 max=255
						name='staffTemplateInfo[<?=$_SESSION['tournamentID']?>][<?=$role['logisticsRoleID']?>]' value='<?=$value?>'>
				</td>
			</tr>
		<?php endforeach ?>

	</table>

	<button class='button success' name='formName' value='updateStaffTemplates'>
		Update
	</button>

	</form>

	</div>
	</div>

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////