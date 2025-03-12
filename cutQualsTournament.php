<?php
/*******************************************************************************
	Cutting Qualifications for Tournaments

	Displays which fighters registered in a tournament have completed the
	approprtiate cutting qualification
	LOGIN: N/A

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Cutting Qualification';
$includeTournament = true;
$jsIncludes[] = 'misc_scripts.js';
$createSortableDataTable[] = ['cutQualList',50];
include('includes/header.php');

if($_SESSION['tournamentID'] == null){
	pageError('tournament');
}elseif(!isCuttingQual($_SESSION['tournamentID'])){
	displayAlert('No Cutting Qualification required for this tournament');
} else {



	$thisStandard = getCuttingStandard($_SESSION['tournamentID']);
	if($thisStandard != null){
		$qualList = getCuttingQualificationsList($thisStandard['standardID'], $thisStandard['date']);
	}
	$tournamentList =  getTournamentSystemRosterIDs();
	$allStandards = getCuttingQualificationsStandards();
	$numToQual = 0;
	$nonQualledFighters = [];
	$qualledFighters = [];
	$hide = getItemsHiddenByFilters($tournamentID,$_SESSION['filters'],'roster');

	if(isset($qualList)){
		foreach($tournamentList as $fighter){
			$entry = [];
			$systemRosterID = $fighter['systemRosterID'];
			$entry['name'] = getFighterNameSystem($systemRosterID);
			$entry['systemRosterID'] = $systemRosterID;
			$entry['rosterID'] = $fighter['rosterID'];
			$eventDate = getEventEndDate($_SESSION['eventID']);

			if(isset($qualList[$systemRosterID])){
				$entry['qualled'] = true;
				if($qualList[$systemRosterID]['date'] == $eventDate){
					$entry['thisEvent'] = true;
					$entry['qualID'] = $qualList[$systemRosterID]['qualID'];
				}
			} else {
				$entry['qualled'] = false;
				$numToQual++;
			}

			$displayMode = '';
			if(isset($_SESSION['cutQualDisplayMode'])){
				$displayMode = $_SESSION['cutQualDisplayMode'];
			}

			switch($displayMode){
				case 'name':
					$displayList[] = $entry;
					break;
				case 'qual':
				default:
					if($entry['qualled']){
						$qualledFighters[] = $entry;
					} else {
						$nonQualledFighters[] = $entry;
					}
					break;
			}
		}

		foreach((array)$nonQualledFighters as $fighter){
		$displayList[] = $fighter;
		}
		foreach((array)$qualledFighters as $fighter){
			$displayList[] = $fighter;
		}
	}



// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?=activeFilterWarning()?>

<!-- Select tournament standard -->
	<?php if($thisStandard != null): ?>
		Fighters who have completed
		cutting qualification
		since <strong><?=$thisStandard['date']?></strong><BR>
	<?php else:
		$alertText = "No Cutting Qualification Standards Set";
		if(ALLOW['EVENT_MANAGEMENT'] == true){
			$alertText .= "<BR><a data-open='changeStandardsBox'>Add Qualification Standard</a>";
		}
		displayAlert($alertText);
	endif ?>



	<?=cutQualManagementButton($allStandards, $thisStandard)?>



	<div class='grid-x grid-padding-x'>
	<div class='large-6 medium-8 cell'>



<!-- Display List of Fighters -->
	<?php if($thisStandard != null): ?>

		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
			<a class='button small primary' href='cutQualsTournament.php'>Reload Page</a>
			<span style='font-size:1.5em'><strong><?=$numToQual?></strong> left to Qualify.
			</span>
		<?php endif ?>
	<table id='cutQualList'>

	<!-- Header -->

		<thead>
		<form method='post'>
		<input type='hidden' name='formName' value='changeCutQualDisplay'>


		<tr>
			<th>
				Name
			</th>
			<th>
				Qual
			</th>
			<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
				<th>
					Update
					<?=tooltip('<u>Add</u><BR>Fighter has not met qual standard.<BR>
							<u>Update</u><BR>Fighter has qualled previously. Click to indicate if they re-qualified on this date.<BR>
							<u>Remove</u><BR>Remove qualification achieved at this event.'
							)?>
				</th>
			<?php endif ?>
		</tr>

		</form>
		</thead>

	<!-- Data -->
		<tbody>
		<?php foreach((array)$displayList as $fighter):


			$sysID = $fighter['systemRosterID'];

			if(isset($hide['roster'][$fighter['rosterID']]) == true){
				continue;
			}

			$class = "button tiny hollow no-bottom";
			$qualID = '';

			if($fighter['qualled'] == false){
				$isSet = 0;
				$qualID = '';
				$class .= " success";
				$txt = "Add";
			} else if (isset($fighter['thisEvent']) == true) {
				$qualID = $fighter['qualID'];
				$class .= " alert";
				$txt = "Remove";
			} else {
				$qualID = 0;
				$class .= "";
				$txt = "Update";
			}

			?>
			<tr>

				<input type='hidden' id='qualID-<?=$sysID?>' value='<?=$qualID?>'>

				<td><?=$fighter['name']?></td>
				<td>
					<?php if($fighter['qualled']): ?>
						<strong>
							&#x2714;
						</strong>
					<?php endif ?>
				</td>
				<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
					<td class='text-left'>

						<button class='<?=$class?>' style='width:10em' id='button-<?=$sysID?>'
							onclick="updateCutQualInfo(<?=$fighter['systemRosterID']?>)" value='<?=$txt?>'>
								<?=$txt?>
						</button>

					</td>
				<?php endif ?>

			</tr>
		<?php endforeach ?>

	</tbody>
	</table>
	<?php else: ?>
		<?php displayAlert('Qualification Standard not yet applied'); ?>
	<?php endif ?>
	</div>
	</div>

	<?=changeParticipantFilterForm($_SESSION['eventID'])?>

<?php }

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function cutQualManagementButton($allStandards, $thisStandard){
	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

?>
	<?php if(ALLOW['SOFTWARE_ASSIST'] == true): ?>
		<a class='button small-expanded hollow' href='cutQuals.php'>
			Master Qualification List
		</a>
	<?php endif ?>

	<a class='button small-expanded' data-open='changeStandardsBox'>
		Change Tournament Standards
	</a>


	<div class='reveal' id='changeStandardsBox' data-reveal>
		<form method='POST'>

			<h4>Tournament Cutting Standard</h4>
			<!-- Standards -->
			<div class='input-group grid-x'>
				<span class='input-group-label medium-shrink small-12'>Qualification Type:</span>
				<select class='input-group-field' name='qualStandard'>
					<?php foreach($allStandards as $standard):
						$standardID = $standard['standardID'];
						$name = $standard['standardName'];
						$selected = isSelected($standardID, @$thisStandard['standardID']);
						?>
						<option value=<?=$standardID?> <?=$selected?>><?=$name?></option>
					<?php endforeach ?>
				</select>
			</div>

			<!-- Relative Time -->
			<fieldset class='fieldset'>
			<legend>
				Use Relative Time:
				<input type='radio' name='useDateType' value='relative' checked id='relative-date'>
			</legend>

			<div class='input-group grid-x'>
				<input class='input-group-field' type='number' name='qualYears'
					min=0 max=10 placeholder='Years' onchange="toggleRadio('relative-date')">
				<input class='input-group-field' type='number' name='qualMonths'
					min=0 max=12 placeholder='Months' onchange="toggleRadio('relative-date')">
				<input class='input-group-field' type='number' name='qualDays'
					min=0 max=31 placeholder='Days' onchange="toggleRadio('relative-date')">
				<span class='input-group-label medium-shrink small-12'>Prior to event start</span>
			</div>
			</fieldset>

			<!-- Absolute Date -->
			<fieldset class='fieldset'>
			<legend>
				Use Absolute Date:
				<input type='radio' name='useDateType' value='absolute' id='absolute-date'>
			</legend>

			<div class='input-group grid-x'>
				<span class='input-group-label medium-shrink small-12'>Since Date:</span>
				<input class='input-group-field' type='date' name='qualDate' value='<?=@$thisStandard['date']?>'
					onchange="toggleRadio('absolute-date')">
			</div>
			</fieldset>

			<div class='grid-x grid-margin-x'>
				<button class='button success small-6 cell' name='formName' value='setCutQualStandards'>
					Update
				</button>
				<a class='button secondary small-6 cell' data-close aria-label='Close modal' type='button'>
					Cancel
				</a>
			</div>

		</form>

	<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>

	</div>

<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
