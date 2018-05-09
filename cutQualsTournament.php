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
include('includes/header.php');

if($_SESSION['tournamentID'] == null){
	displayAnyErrors('No Tournament Selected');
}elseif(!isCuttingQual()){
	displayAnyErrors('No Cutting Qualification required for this tournament');
} else {

	$thisStandard = getCuttingStandard();
	if($thisStandard != null){
		$qualList = getCuttingQualificationsList($thisStandard['standardID'], $thisStandard['date']);
	}
	$tournamentList =  getTournamentSystemRosterIDs();
	$allStandards = getCuttingQualificationsStandards();
	
	
	foreach($tournamentList as $fighter){
			unset($entry);
			$systemRosterID = $fighter['systemRosterID']; 
			$entry['name'] = getFighterNameSystem($systemRosterID);
			$entry['systemRosterID'] = $systemRosterID;
			$eventDate = getEventEndDate();
			
			
			if($qualList[$systemRosterID]['date'] == $eventDate){
				$entry['thisEvent'] = true;
				$entry['qualID'] = $qualList[$systemRosterID]['qualID'];
			}
			
			if(isset($qualList[$systemRosterID])){ 
				$entry['qualled'] = true;
			} else {
				$entry['qualled'] = false;
			}
			
			switch($_SESSION['cutQualDisplayMode']){
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
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>

<!-- Select tournament standard -->
	Fighters who have completed 
	<strong><?=$thisStandard['standardName']?></strong> 
	since <strong><?=$thisStandard['date']?></strong><BR>

	<?php if(USER_TYPE >= USER_SUPER_ADMIN || $_SESSION['eventID'] == 23): ?>
		<a class='button small-expanded hollow' href='cutQuals.php'>
			Master Quallification List
		</a>
	<?php endif ?>

	<?php if(USER_TYPE >= USER_STAFF): ?>
		
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
					$selected = isSelected($standardID, $thisStandard['standardID']);
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
			<input class='input-group-field' type='date' name='qualDate' value='<?=$thisStandard['date']?>'
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
		
	<?php endif ?>

	<div class='grid-x grid-padding-x'>
	<div class='large-5 medium-7 cell'>
		
	
	
<!-- Display List of Fighters -->
	<table>
		
	<!-- Header -->
		<form method='post'>
		<input type='hidden' name='formName' value='changeCutQualDisplay'>
		
		<tr>
			<th>
				<button name='cutQualDisplayMode' value='name'><a><strong>
					Name
				</strong></a></button>
			</th>
			<th>
				<button name='cutQualDisplayMode' value='quall'><a><strong>
					Qualled
				</strong></a></button>
			</th>
		</tr>
		</form>

	<!-- Data -->
		
		<?php foreach((array)$displayList as $fighter): ?>
			<tr>
				<form method='POST'>
				<input type='hidden' name='systemRosterID' value='<?=$fighter['systemRosterID']?>'>
				<input type='hidden' name='qualID' value='<?=$fighter['qualID']?>'>
				
				<td><?=$fighter['name']?></td>
				<th>
					<?php if($fighter['qualled']): ?>
						<?php if($fighter['thisEvent'] && USER_TYPE >= USER_STAFF):?>
							<button class='button tiny hollow alert no-bottom' 
								name='formName' value='removeQualledFighterEvent'>
								Remove
							</button>
						<?php else: ?>	
							X 
						<?php endif ?>
					 <?php else: ?>
						 <?php if(USER_TYPE >= USER_STAFF): ?>
							<button class='button tiny hollow success no-bottom' 
								name='formName' value='addQualledFighterEvent'>
								Add
							</button>
						<?php endif ?>
					 <?php endif ?>
				</th>
				</form>
			</tr>
		<?php endforeach ?>
		
	
	</table>
	</div>
	</div>
	
<?php }

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
