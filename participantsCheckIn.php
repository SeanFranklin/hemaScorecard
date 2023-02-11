<?php
/*******************************************************************************
	Event Check In
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Event Check In';

$jsIncludes[] = 'logistics_management_scripts.js';

include('includes/header.php');
$createSortableDataTable[] = ['eventCheckInTable',100];
$eventID = $_SESSION['eventID'];

if($eventID == null){
	pageError('event');
} elseif(ALLOW['EVENT_SCOREKEEP'] == false) {
	pageError('user');
} else {

	$roster = getCheckInStatusEvent($eventID);

	$additionalRoster = getCheckInStatusAdditional($eventID);
	$isAdditionals = false;
	if(count($additionalRoster) != 0){
		$isAdditionals = true;
		$additionalsHeader 	= "<th>Reg Type</th>";
		$additionalsCol 	= "<td>Normal</td>";
	} else {
		$additionalsHeader 	= "";
		$additionalsCol 	= "";
	}

	$listToDisplay = [];
	foreach($roster as $index => $fighter){

		$tmp = [];

		$tmp['waiverID'] = "check-in-fighter-".$fighter['rosterID']."-waiver";
		$tmp['eventWaiver'] = $fighter['eventWaiver'];
		if($tmp['eventWaiver'] != 0){
			$tmp['waiverText'] = 'signed';
		} else {
			$tmp['waiverText'] = 'blank';
		}

		$tmp['checkInID'] = "check-in-fighter-".$fighter['rosterID']."-checkIn";
		$tmp['eventCheckIn'] = $fighter['eventCheckIn'];
		if($fighter['eventCheckIn'] != 0){
			$tmp['checkInText'] = 'done';
		} else {
			$tmp['checkInText'] = 'no';
		}

		$listToDisplay[$fighter['rosterID']] = $tmp;
	}

	$listOfAdditionals = [];
	foreach((array)$additionalRoster as $index => $additional){

		$tmp = [];

		$tmp['waiverID'] = "check-in-additional-".$additional['additionalRosterID']."-waiver";
		$tmp['eventWaiver'] = $additional['eventWaiver'];
		if($tmp['eventWaiver'] != 0){
			$tmp['waiverText'] = 'signed';
		} else {
			$tmp['waiverText'] = 'blank';
		}

		$tmp['checkInID'] = "check-in-additional-".$additional['additionalRosterID']."-checkIn";
		$tmp['eventCheckIn'] = $additional['eventCheckIn'];
		if($additional['eventCheckIn'] != 0){
			$tmp['checkInText'] = 'done';
		} else {
			$tmp['checkInText'] = 'no';
		}

		$tmp['regType'] = $additional['registrationType'];

		$listOfAdditionals[$additional['additionalRosterID']] = $tmp;

	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<script>

		var refreshPeriod = 1000; // msec

		window.onload = function(){
			refreshCheckInList('event', <?=$eventID?>);
			window.setInterval(
				function(){ refreshCheckInList('event',<?=$eventID?>);}, 
				refreshPeriod
			);
		}

	</script>

	<div class='callout primary'>
		I'm trying something new for form inputs here. <u>On this section alone</u> the data is updated as soon as it changes, without needing to submit. This also means that multiple people can be working on this page at once. (If you're having trouble, try reloading the page.)<BR>
		<a class='button tiny no-bottom secondary' href='participantsCheckIn.php'>Reload Page</a>
	</div>

	<div class='grid-x grid-margin-x'>
	<div class='large-7 medium-10 cell'>

	<table id="eventCheckInTable" class="display">
		<thead>
			<tr>
				<th> Name </th>
				<th> Waiver </th>
				<th> Check-In </th>
				<?=$additionalsHeader?>
			</tr>
		</thead>

		<tbody>
		<?php foreach($listToDisplay as $rosterID => $f):?>
			<tr>

				<td>
					<?=getFighterName($rosterID)?>
				</td>

				<td class='text-center'>

					<a class='button no-bottom' onclick="checkInFighterJs('waiver')"
						id='<?=$f['waiverID']?>'
						data-checkInType='event'
						data-rosterID=<?=$rosterID?> 
						data-signed=<?=$f['eventWaiver']?>>
						<?=$f['waiverText']?>
					</a>
					
				</td>

				<td class='text-center'>

					<a class='button no-bottom' onclick="checkInFighterJs('checkIn')"
						id='<?=$f['checkInID']?>'
						data-checkInType='event'
						data-rosterID=<?=$rosterID?> 
						data-checked=<?=$f['eventCheckIn']?>>
						<?=$f['checkInText']?>
					</a>

				</td>

				<?=$additionalsCol?>

			</tr>

		<?php endforeach ?>

		<?php foreach($listOfAdditionals as $additionalID => $a): ?>
			<tr>

				<td>
					<?=getAdditionalName($additionalID)?>
				</td>

				<td class='text-center'>

					<a class='button no-bottom italic' onclick="checkInFighterJs('waiver')"
						id='<?=$a['waiverID']?>'
						data-checkInType='additional'
						data-additionalRosterID=<?=$additionalID?> 
						data-signed=<?=$a['eventWaiver']?>>
						<?=$a['waiverText']?>
					</a>
					
				</td>

				<td class='text-center'>

					<a class='button no-bottom italic' onclick="checkInFighterJs('checkIn')"
						id='<?=$a['checkInID']?>'
						data-checkInType='additional'
						data-additionalRosterID=<?=$additionalID?> 
						data-checked=<?=$a['eventCheckIn']?>>
						<?=$a['checkInText']?>
					</a>

				</td>

				<td>
					<?=$a['regType']?>
				</td>

			</tr>

		<?php endforeach ?>

		</tbody>
	</table>

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