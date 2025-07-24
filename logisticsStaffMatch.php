<?php
/*******************************************************************************
	Logistics Staff Assigments

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Staff By Matches';
$includeTournamentName = false;
$hideEventNav = true;
$jsIncludes[] = 'logistics_management_scripts.js';

$createSortableDataTable[] = ['match-staff-list-table',20];

include('includes/header.php');


if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false) {
	pageError('user');
} else {

	$tournamentID = (int)$_SESSION['tournamentID'];
	$sql = "SELECT rosterID, matchID, logisticsRoleID, roleName
			FROM logisticsStaffMatches

			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN systemLogisticsRoles USING(logisticsRoleID)
			WHERE tournamentID = {$tournamentID}";

	$staffList = (array)mysqlQuery($sql, ASSOC);


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?=batchChangeBox($tournamentID)?>

	<BR><BR>

	<form method='POST'>


	<table class="display data-table" id='match-staff-list-table'>
		<thead>
			<tr>
				<th>MatchID</th>
				<th>Role</th>
				<th>Name</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach($staffList as $m): ?>
			<tr>
				<td><?=$m['matchID']?></td>
				<td><?=getFighterName($m['rosterID'])?></td>
				<td><?=$m['roleName']?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
	</table>
</form>




<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

/******************************************************************************/

function batchChangeBox($tournamentID){

	$rolesList = logistics_getRoles();

?>

	<a onclick="$('#batch-change-box').toggle()">Batch Change</a>

	<div class='hidden callout' id='batch-change-box'>
	<form method='POST'>

		<h4>Batch Change</h4>
		<p><i>The Batch Change feature will change <u><b>ALL</b></u> match staff roles from one type to the other. For instance if you wanted to change every instance of "Judge" to "Referee", etc. (This doesn't affect the schedule, only staff that have been checked into matches.) </i></p>
		<p class='red-text'><b>THIS CAN NOT BE UNDONE</b></p>

		<input class='hidden' name='matchStaffBatchChange[tournamentID]' value=<?=$tournamentID?>>

		<div class='input-group'>


			<select class='input-group-field' name='matchStaffBatchChange[fromRoleID]'>
				<option value=-1>-- Select Role --</option>
				<?php foreach($rolesList as $r): ?>
					<option value=<?=$r['logisticsRoleID']?>><?=$r['roleName']?></option>
				<?php endforeach ?>
			</select>

			<span class='input-group-label'>
				->
			</span>

			<select class='input-group-field' name='matchStaffBatchChange[toRoleID]'>
				<option value=-1>-- Select Role --</option>

				<?php foreach($rolesList as $r): ?>
					<option value=<?=$r['logisticsRoleID']?>><?=$r['roleName']?></option>
				<?php endforeach ?>
				<option value=0>! Delete Assignment !</option>
			</select>
		</div>

		<a class='submit button success make-it-so' onclick="$('.make-it-so').toggle()">Make It So</a>
		<a class='submit button secondary make-it-so hidden' onclick="$('.make-it-so').toggle()">Cancel, Abort!</a>
		<button class='submit button success hidden make-it-so' name='formName' value='matchStaffBatchChange'>Yes, I'm Sure </button>
	</form>
	</div>

<?php
}


/******************************************************************************/





/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
