<?php
/*******************************************************************************
	Add Tournament Types

	Page for adding new tournament meta types (weapons/classes/materials/etc...)
	LOGIN:
		- SUPER ADMIN required for access

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Add Tournament Types';
include('includes/header.php');

if(ALLOW['SOFTWARE_ASSIST'] == false){
	pageError('user');
} else {

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<form method='POST'>
	<input type='hidden' name='formName' value='addTournamentType'>

	<a href='adminNewTournaments.php' class='button hollow'>Add New Tournaments</a>

	<div class='input-group'>
		<span class='input-group-label'>Type:</span>
		<select name='tournamentTypeMeta' class='input-group-field'>
			<option value='weapon'>Weapon</option>
			<option value='prefix'>Prefix</option>
			<option value='material'>Material</option>
			<option value='suffix'>Suffix</option>
			<option value='gender'>Gender</option>
		</select>

		<span class='input-group-label'>Name:</span>
		<input class='input-group-field' type='text' name='tournamentType'>

		<button class='button success input-group-button'>
			Add
		</button>
	</div>




	</form>

<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
