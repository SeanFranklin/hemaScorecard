<?php
/*******************************************************************************
	Event Check In
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Event Check In';

include('includes/header.php');
$createSortableDataTable[] = 'eventCheckInTable';
$eventID = $_SESSION['eventID'];

if($eventID == null){
	pageError('event');
} elseif(ALLOW['EVENT_SCOREKEEP'] == false) {
	pageError('user');
} else {

	$roster = getCheckInStatus($eventID,'event');

	$additionalRoster = getCheckInStatusAdditional($eventID,'event');
	$isAdditionals = false;
	if(count($additionalRoster) != 0){
		$isAdditionals = true;
	}

	foreach($roster as $index => $fighter){
		if($fighter['eventWaiver'] != 0){
			$roster[$index]['waiver'] = 'checked';
		} else {
			$roster[$index]['waiver'] = '';
		}

		if($fighter['eventCheckIn'] != 0){
			$roster[$index]['checkin'] = 'checked';
		} else {
			$roster[$index]['checkin'] = '';
		}
	}

	
	foreach((array)$additionalRoster as $index => $additional){
		if($additional['eventWaiver'] != 0){
			$additionalRoster[$index]['waiver'] = 'checked';
		} else {
			$additionalRoster[$index]['waiver'] = '';
		}

		if($additional['eventCheckIn'] != 0){
			$additionalRoster[$index]['checkin'] = 'checked';
		} else {
			$additionalRoster[$index]['checkin'] = '';
		}
	}

	$startOfForm = "checkInFighters[event][{$eventID}]";
	$startOfAdditionalForm = "checkInFighters[additional][{$eventID}]";

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<div class='grid-x grid-margin-x'>
	<div class='large-6 medium-10'>

	<form method="POST">	
	<table id="eventCheckInTable" class="display">
		<thead>
			<tr>
				<?php if($isAdditionals == true): ?>
					<th>Reg</th>
				<?php endif ?>

				<th> Name </th>
				<th> Waiver </th>
				<th> Check In </th>
				<th> Update </th>
			</tr>
		</thead>

		<tbody>
		<?php foreach($roster as $fighter): 
			$rosterID = $fighter['rosterID'];
			$startOfForm2 = $startOfForm."[{$rosterID}]";
			?>
			<tr>
				<?php if($isAdditionals == true): ?>
					<td>0</td>
				<?php endif ?>

				<td>
					<?=getFighterName($rosterID)?>
				</td>

				<td data-sort="<?=$fighter['waiver']?>">

					<div class='switch text-center no-bottom'>
						<input type='hidden' name='<?=$startOfForm2?>[waiver]' value='0'>
						<input class='switch-input' type='checkbox' 
							id='<?=$startOfForm2?>[waiver]' <?=$fighter['waiver']?>
							name='<?=$startOfForm2?>[waiver]' value='1'>
						<label class='switch-paddle' for='<?=$startOfForm2?>[waiver]'>
						</label>
					</div>
				</td>

				<td data-sort="<?=$fighter['checkin']?>">
					
					<div class='switch text-center no-bottom'>
						<input type='hidden' name='<?=$startOfForm2?>[checkin]' value='0'>
						<input class='switch-input' type='checkbox' 
							id='<?=$startOfForm2?>[checkin]' <?=$fighter['checkin']?>
							name='<?=$startOfForm2?>[checkin]' value='1'>
						<label class='switch-paddle' for='<?=$startOfForm2?>[checkin]'>
						</label>
					</div>
				</td>

				<td class='text-center'>
					<button class='button success hollow tiny no-bottom' 
						name='formName' value='checkInFighters'>
						<strong>✓</strong>
					</button>
				</td>

			</tr>

		<?php endforeach ?>

		<?php foreach((array)$additionalRoster as $additional): 
			$additionalID = $additional['additionalRosterID'];
			$startOfForm2 = $startOfAdditionalForm."[{$additionalID}]";
			?>
			<tr>
				
				<td>
					<?=$additional['registrationType']?>
				</td>
				

				<td>
					<?=getAdditionalName($additionalID)?>
				</td>

				<td data-sort="<?=$fighter['waiver']?>">

					<div class='switch text-center no-bottom'>
						<input type='hidden' name='<?=$startOfForm2?>[waiver]' value='0'>
						<input class='switch-input' type='checkbox' 
							id='<?=$startOfForm2?>[waiver]' <?=$additional['waiver']?>
							name='<?=$startOfForm2?>[waiver]' value='1'>
						<label class='switch-paddle' for='<?=$startOfForm2?>[waiver]'>
						</label>
					</div>
				</td>

				<td data-sort="<?=$fighter['checkin']?>">
					
					<div class='switch text-center no-bottom'>
						<input type='hidden' name='<?=$startOfForm2?>[checkin]' value='0'>
						<input class='switch-input' type='checkbox' 
							id='<?=$startOfForm2?>[checkin]' <?=$additional['checkin']?>
							name='<?=$startOfForm2?>[checkin]' value='1'>
						<label class='switch-paddle' for='<?=$startOfForm2?>[checkin]'>
						</label>
					</div>
				</td>

				<td class='text-center'>
					<button class='button success hollow tiny no-bottom' 
						name='formName' value='checkInFighters'>
						<strong>✓</strong>
					</button>
				</td>

			</tr>

		<?php endforeach ?>

		</tbody>
	</table>
	</form>

	</div>
	</div>


<?
}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////