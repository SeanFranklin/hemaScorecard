<?php
/*******************************************************************************
	Tournament Roster

	View tournament roster and add fighters
	Login:
		- ADMIN or above can add or remove fighters from the tournament

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'System Roster';
$hidePageTitle = true;
$jsIncludes[] = 'roster_management_scripts.js';
$createSortableDataTable[] = ['systemRosterTable',100];
include('includes/header.php');

{

	$allSystemFighters = getSystemRosterInfo();

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?=editSystemFighterBox()?>

<!-- Table headers -->
	<table id="systemRosterTable" class="display stack">
	<thead>
	<tr>

		<th>Last</th>
		<th>First</th>
		<th>
			School
			<?=tooltip("This is the last school they were entered into a tournament under.")?>
		</th>
		<th>
			HEMA Ratings ID
			<?=tooltip("The databases aren't automatically synched, so these don't get updated/added very often.")?>
		</th>
		<?php if(ALLOW['SOFTWARE_ASSIST'] == TRUE): ?>
			<th>systemRosterID</th>
			<th></th>
		<?php endif?>
	</tr>
	</thead>
	<tbody>

<!-- Display existing participants -->
	<?php foreach ($allSystemFighters as $fighter):?>
		<tr class='link-table'>

			<td onclick="window.location='participantsAttendance.php?s=<?=$fighter['systemRosterID']?>';" ><?=$fighter['lastName']?></td>
			<td onclick="window.location='participantsAttendance.php?s=<?=$fighter['systemRosterID']?>';" ><?=$fighter['firstName']?></td>
			<td onclick="window.location='participantsAttendance.php?s=<?=$fighter['systemRosterID']?>';" >
				<?=$fighter['schoolFullName']?>
				<?php if(ALLOW['SOFTWARE_ASSIST'] == TRUE): ?>
					(<?=$fighter['schoolID']?>)
				<?php endif ?>

			</td >
			<td onclick="window.location='participantsAttendance.php?s=<?=$fighter['systemRosterID']?>';" ><?=$fighter['HemaRatingsID']?></td>
			<?php if(ALLOW['SOFTWARE_ASSIST'] == TRUE): ?>
				<td onclick="window.location='participantsAttendance.php?s=<?=$fighter['systemRosterID']?>';" ><?=$fighter['systemRosterID']?></td>
				<td>
					<a class='button hollow tiny no-bottom' data-open='editSystemParticipantModal'
					onclick="editSystemParticipant(<?=$fighter['systemRosterID']?>)">Edit</a>
				</td>
			<?php endif ?>
		</tr>
	<?php endforeach ?>
	</tbody>
	</table>


<?php

}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function editSystemFighterBox(){

	if(ALLOW['SOFTWARE_ASSIST'] == FALSE){
		return;
	}

	$allSchools = getSchoolList();
?>

	<div class='reveal tiny' id='editSystemParticipantModal' data-reveal>
	<form method='POST' id='editSystemParticipantForm'>

		SystemRosterID
		<input type='text' id='displaySystemRosterID' disabled>
		<input type='hidden' name='editSystemParticipant[systemRosterID]' id='editSystemRosterID'>

		First Name
		<input type='text' name='editSystemParticipant[firstName]' id='editSystemFirstName'>

		Last Name
		<input type='text' name='editSystemParticipant[lastName]' id='editSystemLastName'>

		HEMA Ratings ID
		<input type='number' name='editSystemParticipant[HemaRatingsID]' id='editSystemHemaRatingsID'>

		School
		<select name='editSystemParticipant[schoolID]' id='editSystemSchoolID'>

			<option value='1'>*Unknown</option>
			<option value='2'>*Unaffiliated</option>

			<?php foreach($allSchools as $school):
				if($school['schoolShortName'] == null || $school['schoolShortName'] == 'Unaffiliated'){continue;}
				?>

				<option value='<?=$school['schoolID']?>'>
					<?=$school['schoolShortName']?>, <?=$school['schoolBranch']?>
					<?php if(ALLOW['SOFTWARE_ASSIST'] == TRUE): ?>
						(<?=$school['schoolID']?>)
					<?php endif ?>
				</option>
			<?php endforeach?>
		</select>

		<HR>

<!-- Submit buttons -->

	<div class='grid-x grid-margin-x'>
		<button class='success button small-6 cell' name='formName' value='editSystemParticipant'>
			Update
		</button>
		<button class='secondary button small-6 cell' data-close aria-label='Close modal' type='button'>
			Cancel
		</button>
	</div>
	</form>

<!-- Reveal close button -->
	<button class='close-button' data-close aria-label='Close modal' type='button'>
		<span aria-hidden='true'>&times;</span>
	</button>

	</div>


<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
