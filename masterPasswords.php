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

if(		$_SESSION['userName'] == null
	|| 	$_SESSION['userName'] == 'eventOrganizer'
	|| 	$_SESSION['userName'] == 'eventStaff'){
	pageError('user');
} else {


	if(ALLOW['SOFTWARE_ADMIN'] == true){
		$sql = "SELECT userID, userName
				FROM systemUsers";
		$userList = mysqlQuery($sql, ASSOC);
		$firstOption = "<option disabled selected></option>";
	} else {
		$sql = "SELECT userID, userName
				FROM systemUsers
				WHERE userName = '{$_SESSION['userName']}'";
		$userList = mysqlQuery($sql, ASSOC);
		$firstOption = "";
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<form method='POST'>
	<fieldset class='fieldset'>
	<legend>Password Information</legend>
	<div class='grid-x grid-margin-x'>

	<!-- User -->
		<div class='input-group large-6 cell'>
			<span class='input-group-label inline'>User:</span>
			<select class='input-group-field' name='changePasswords[userID]'>
				<?=$firstOption?>
				<?php foreach($userList as $user): ?>

					<option value=<?=$user['userID']?>><?=$user['userName']?></option>
				<?php endforeach ?>

			</select>
		</div>

	<!-- Current password confirm-->
		<div class='input-group large-6 cell'>
			<span class='input-group-label inline'>Current Password:</span>
			<input class='input-group-field' type='password'
					name='changePasswords[passwordVerification]'>
		</div>

	<!-- New password & confirm box-->
		<div class='input-group large-12 cell'>
			<span class='input-group-label inline'>New Password:</span>
			<input class='input-group-field' type='password' required
					name='changePasswords[newPassword]'>

			<span class='input-group-label inline'>Confirm:</span>
			<input class='input-group-field' type='password' required
					name='changePasswords[newPassword2]'>

			<button class='button input-group-button' name='formName' value='updatePasswords'>
				Update
			</button>
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
