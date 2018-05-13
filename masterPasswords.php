<?php 
/*******************************************************************************
	Administrator Passwords Management
	
	Change the passwords for the general login types of the software
	LOGIN:
		- SUPER ADMIN required to access
		
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Software Password Management";
include('includes/header.php');

if(USER_TYPE < USER_SUPER_ADMIN){
	pageError('user');
} else {
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>

	<form method='POST'>
	<input type='hidden' name='formName' value='newSystemPasswords'>
	<fieldset class='fieldset'>
	<legend>Password Information</legend>
	<div class='grid-x grid-margin-x'>

	<!-- Current password -->
		<div class='input-group large-7 cell'>
			<span class='input-group-label inline'>Admin Password</span>
			<input class='input-group-field' type='password'  name='adminPassword'>
		</div>
		
	<!-- New Video User password -->
		<div class='input-group large-6 cell'>
			<span class='input-group-label inline'>New Video Password:</span>
			<input class='input-group-field' type='password' name='USER_VIDEO'>
			<button class='button input-group-button' name='passwordType' value='USER_VIDEO'>Update</button>
		</div>
		
	<!-- New Analytics User password -->
		<div class='input-group large-6 cell'>
			<span class='input-group-label inline'>New Stats Password:</span>
			<input class='input-group-field' type='password' name='USER_STATS'>
			<button class='button input-group-button' name='passwordType' value='USER_STATS'>Update</button>
		</div>

	<!-- New System Administrator password -->
		<div class='input-group large-12 cell'>
			<span class='input-group-label inline'>New Admin Password:</span>
			<input class='input-group-field' type='password' name='USER_SUPER_ADMIN'>
			<span class='input-group-label inline'>Confirm:</span>
			<input class='input-group-field' type='password' name='USER_SUPER_ADMIN_2'>
			<button class='button input-group-button' name='passwordType' value='USER_SUPER_ADMIN'>Update</button>
		</div>
		
		
	</div>
	</fieldset>
	

	</form>

<?php }

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
