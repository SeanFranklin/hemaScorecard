<?php
/*******************************************************************************
	Non-participant roster
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Non-Participant Registrations';
include('includes/header.php');

$eventID = $_SESSION['eventID'];

if($eventID == null){
	pageError('event');
} else {

	define("NUM_REG_LEVELS",4);
	addNewParticpants();

	$participants = getEventAdditionalParticipants($_SESSION['eventID']);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?php if(ALLOW['EVENT_MANAGEMENT'] == false): ?>

		<?php foreach($participants as $person): ?>
			<li><?=$person['firstName']?> <?=$person['lastName']?></li>
		<?php endforeach ?>

	<?php else: ?>

	<form method='POST'>




	<table class='stack'>
		<tr>
			<th></th>
			<th>Registration Type</th>
			<th>First Name</th>
			<th>Last Name</th>
		</tr>


		<?php foreach($participants as $person): 
			$i = $person['additionalRosterID']
			?>
			<tr>
				<td>
					<input type='checkbox' name='deleteAdditional[IDsToDelete][<?=$i?>]' 
					class='no-bottom' value='<?=$i?>'>
				</td>
				<td>
					<select  name='updateAdditional[list][<?=$i?>][type]'class='no-bottom'>
						<?=numberSelectMenu(1,4,$person['registrationType'])?>
					</select>
				</td>
				<td>
					<input type='text' name='updateAdditional[list][<?=$i?>][firstName]'
					value='<?=$person['firstName']?>' class='no-bottom'>
				</td>
				<td>
					<input type='text' name='updateAdditional[list][<?=$i?>][lastName]'
					value='<?=$person['lastName']?>' class='no-bottom'>
				</td>

			</tr>

		<?php endforeach ?>
	</table>

	<button class='button success' name='formName' value='updateAdditionalParticipants'>
		Update Registrations
	</button>
	<button class='button alert' name='formName' value='deleteAdditionalParticipants'>
		Delete Selected
	</button>

	</form>


	<?php endif?>


<?php
}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function addNewParticpants(){

	if(ALLOW['EVENT_MANAGEMENT'] == false){ return; }

?>
	<a onclick="$('.add-new-reg-box').toggle()" class='add-new-reg-box'>
		<em>Enter New Registrations</em>
	</a>

	<div class='hidden add-new-reg-box'>
	<fieldset class='fieldset'>

	<legend class='legend'>
	<h4 class='no-bottom'><a onclick="$('.add-new-reg-box').toggle()">Enter New Registrations</a></h4></legend>
	<em>
		Note: This is simply a list of names to help you (as an organizer) keep track and check people in.
		The list is not connected to the tournament list, and there is no way to 'promote' any non-participant to a participant. You have to enter them from scratch.<BR>
		<u>Staff and Instructors should be entered as participants.</u> 
		You can no schedule any staff from this list.
	</em>

	<form method='POST''>
		<input type='hidden' name='addAdditional[eventID]' value='<?=$_SESSION['eventID']?>'>

		<table class='stack'>

			<tr>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Registration Type
					<?=tooltip("Use this number if you want to keep track of the type of registration.<BR>
					<strong>Tournament Participants are listed as value 0</strong>")?>
				</th>

			<?php for($i = 0; $i<1;$i++): ?>
				<tr>
					<td>
						<input type='text' name='addAdditional[list][<?=$i?>][firstName]'
						placeholder='First Name' class='no-bottom'>
					</td>
					<td>
						<input type='text' name='addAdditional[list][<?=$i?>][lastName]'
						placeholder='Last Name' class='no-bottom'>
					</td>
					<td>
						<select  name='addAdditional[list][<?=$i?>][type]'class='no-bottom'>
							<?=numberSelectMenu(1,4)?>
						</select>
					</td>
				</tr>
			<?php endfor ?>
		</table>


	<button class='button success no-bottom' name='formName' value='addAdditionalParticipants'>
			Add Registrations
	</button>
	</form>
	</fieldset>

	</div>

<?	
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
