<?php 
/*******************************************************************************
	Info Summary
	
	Displays all the tournament medalists
	Login: 
		- ADMIN or above can add/remove final medalists 
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Final Results";
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} else {
	
	define("NUM_FINALISTS_DISPLAYED",4);
	$tournamentList = getTournamentsFull($_SESSION['eventID']);

	if(!isset($_SESSION['manualTournamentPlacing'])){
		$_SESSION['manualTournamentPlacing'] = '';
	}

	if(ALLOW['EVENT_SCOREKEEP'] == true || ALLOW['EVENT_MANAGEMENT'] == true){
		define('ALLOW_EDITING',true);
	} else {
		define('ALLOW_EDITING',false);
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<div class='grid-x'>
			
	<?php foreach((array)$tournamentList as $tournamentID => $data):
		
		$isTeams = isTeams($tournamentID);
		$name = getTournamentName($tournamentID); 
		$link = "onclick='javascript:document.goToTournamentAlt{$tournamentID}.submit();'
					style='cursor: pointer;'";

		$fieldsetLink = '';
		if(!ALLOW_EDITING && !isFinalized($tournamentID)){
			$fieldsetLink = $link;
		}
		?>
		

		<fieldset class='large-7 medium-10 small-12 fieldset' <?=$fieldsetLink?>>
		
			<a name='anchor<?=$tournamentID?>'></a>
			<legend><h4><a <?=$link?>><?= $name ?></a></h4></legend>

			<form method='POST' name='goToTournamentAlt<?= $tournamentID; ?>'>
			<input type='hidden' name='formName' value='changeTournament'>
			<input type='hidden' name='newPage' value='participantsTournament.php'>
			<input type='hidden' name='newTournament' value=<?= $tournamentID; ?>>
			</form>


			<?php if(ALLOW_EDITING && @$_SESSION['manualPlacing']['tournamentID'] == $tournamentID): ?>

				<?= manualTournamentPlacing($tournamentID, $isTeams); ?>

			<?php else: ?>

				<?= displayTournamentPlacings($tournamentID, $data, $isTeams) ?>
				<?= manageTournamentPlacings($tournamentID)?>

			<?php endif ?>
		
		</fieldset>
			
	<?php endforeach ?>
	</div>

<?php }

unset($_SESSION['manualPlacingMessage']);

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayTournamentPlacings($tournamentID, $data, $isTeams){


	 if(($data['hideFinalResults'] == true)
		&& (ALLOW['EVENT_SCOREKEEP'] == false)
		&& (ALLOW['VIEW_SETTINGS'] == false)
		&& (ALLOW['STATS_EVENT'] == false)){

	 	echo "This tournament does not have placings.";
	 	return;
	 }

	$placings = getTournamentPlacings($tournamentID);

	if($placings == null){
		return;
	}

// Calculate which results to show on the main screen
	$i = 0;
	$placingsToShow = sizeof($placings);
	$placingDisplay1 = [];
	while($i < $placingsToShow){
		
		if($placings[$i]['lowBound'] != null){
			if($placings[$i]['lowBound'] > 4){
				break;
			}
		} elseif ($placings[$i]['placing'] > NUM_FINALISTS_DISPLAYED){
			break;
		}

		$rosterID = $placings[$i]['rosterID'];
		if($rosterID == null){continue;}

		if($isTeams){
			$tmp['name'] = getTeamName($rosterID);
		} else {
			$tmp['name'] = getEntryName($rosterID);
		}

		
		if(isset($placings[$i]['schoolFullName'])){
			$tmp['school'] = $placings[$i]['schoolFullName'];
			if($placings[$i]['schoolBranch'] != null){
				$tmp['school'] .= ", ".$placings[$i]['schoolBranch'];
			}
		} else {
			$tmp['school'] = '';
		}


		$tmp['place'] = $placings[$i]['lowBound'];
		if($tmp['place'] == null){
			$tmp['place'] = $placings[$i]['placing'];
		}

		$i++;

		$placingDisplay1[] = $tmp;
	}

	/// NO NOT RE-SET i BETWEEN THESE TWO LOOPS /////

// Calculate which results to show on the secondary display.
	$placingDisplay2 = [];
	for($i;$i<$placingsToShow;$i++){

		switch($placings[$i]['placeType']){
			case 'bracket':
				$tmp['place'] = "Top ".$placings[$i]['placing'];
				break;
			case 'tie':
				$tmp['place'] = $placings[$i]['lowBound']."<em>{Tie}</em>";
				break;
			default:
				$tmp['place'] = $placings[$i]['placing'];
				break;
		}

		$rosterID = $placings[$i]['rosterID'];
		if($rosterID == null){continue;}

		if($isTeams){
			$tmp['name'] = getTeamName($rosterID);
		} else {
			$tmp['name'] = getEntryName($rosterID);
		}

		if(isset($placings[$i]['schoolFullName'])){
			$tmp['school'] = $placings[$i]['schoolFullName'];
			if($placings[$i]['schoolBranch'] != null){
				$tmp['school'] .= ", ".$placings[$i]['schoolBranch'];
			}
		} else {
			$tmp['school'] = '';
		}

		$placingDisplay2[] = $tmp;
	}


?>

			
<!-- Display the top 4 -->
	<table>
		<?php foreach($placingDisplay1 as $data):?>
			
			<tr>
				<td class='text-center'>
					<?= $data['place'] ?>
				</td>
				<td>
					<strong><?=$data['name']?></strong>
				</td>
				<td>
					<em><?= $data['school'] ?></em>
				</td>
			</tr>
			
		<?php endforeach ?>
	</table>

<!-- Display results above 4th place -->

	<!-- Toggle visibility -->
	<?php if($placingDisplay2 != null): ?>
		<a class='extra-results-<?=$tournamentID?>' 
			onclick= "$('.extra-results-<?=$tournamentID?>').toggle()">

			Show More &#8595;
		</a>

		<!-- Display data -->
		<div class='extra-results-<?=$tournamentID?> hidden'>

			<a onclick= "$('.extra-results-<?=$tournamentID?>').toggle()">Hide &#8593;</a>

			<?php foreach($placingDisplay2 as $data):?>
				<li>
					<?=($data['place'])?> - <?=$data['name']?>
					(<em><?=$data['school']?></em>)
				</li>
			<?php endforeach ?>
		</div>
	<?php endif ?>

<?php
}

/******************************************************************************/

function manageTournamentPlacings($tournamentID){

	if(ALLOW_EDITING == false){
		return;
	}

	

	$tournamentID = (int)$tournamentID;

	$isFinalized = isFinalized($tournamentID);

	if($isFinalized == false && areAllMatchesFinished($tournamentID) == false){
		$manualClass = 'secondary';
		$autoDisable = 'disabled';
	} else {
		$manualClass = '';
		$autoDisable = '';
	}

	if($isFinalized == false && isBrackets($tournamentID) == true){
		autoFinalizeSpecificationBox($tournamentID);
		$useSpecs = true;
	} else{
		$useSpecs = false;
	}

?>
	<form method='POST'>
	<input type='hidden' name='tournamentID' value='<?=$tournamentID ?>'>

 	<?php if($isFinalized == false): ?>

		<?php if(!isResultsOnly($tournamentID)): ?>
			<?php if($useSpecs == false): ?>
				<button class='button no-bottom success' name='formName' 
					value='autoFinalizeTournament' <?=$autoDisable?>>
					Auto Finalize Tournament
				</button>
			<?php else: ?>
				<a class='button no-bottom success' <?=$autoDisable?>
					data-open='autoFinalizeBox-<?=$tournamentID?>'>
					Auto Finalize Tournament
				</a>
			<?php endif ?>
		<?php endif ?>

		<button class='button hollow no-bottom <?=$manualClass?>' name='formName' value='editTournamentPlacings'>
			Manually Finalize Tournament
		</button>
						
	<?php else: ?>

		<a class='button hollow no-bottom alert' data-open='deleteConfirmBox<?=$tournamentID?>'>
			Remove Final Results
		</a>
		<button class='button hollow no-bottom' name='formName' value='editTournamentPlacings'>
			Edit Final Results
		</button>

	<?php endif ?>	
	</form>

<!-- Delete Confirmation Box -->
	<?php if($isFinalized == true): ?>
	<div class='reveal tiny' id='deleteConfirmBox<?=$tournamentID?>' data-reveal>

		<form method='POST'>
		<input type='hidden' name='tournamentID' value='<?=$tournamentID ?>'>

		<h4>Please Confirm Removal</h4>
		<em>Removing the final results will not affect any match data, only the list of 
		final placings.</em>
		<HR>
	<!-- Submit buttons -->
		<div class='grid-x grid-margin-x'>

			<button class='button alert small-6 cell' name='formName' value='removeTournamentPlacings'>
				Yes, Remove Final Results
			</button>

			<button class='button secondary small-6 cell' data-close aria-label='Close modal' type='button'>
				Cancel
			</button>
		</div>

		</form>
	
		<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	
	</div>
	<?php endif ?>	
		
<?php
}



/******************************************************************************/

function manualTournamentPlacing($tournamentID, $isTeams = false){

	$roster = getTournamentCompetitors($tournamentID);
	$max = sizeof($roster);

	define("TIE_TOP",'↰');
	define("TIE_MIDDLE",'|');
	define("TIE_BOTTOM",'↲');
	define("TIE_NO",'&nbsp;');

	foreach($roster as $person){

		if($isTeams){
			$names[$person['rosterID']] = getTeamName($person['rosterID']);
		} else {
			$names[$person['rosterID']] = getEntryName($person['rosterID']);
		}
	}

	$placings = [];
	if(isset($_SESSION['manualPlacing']['data'])){
		$placings = $_SESSION['manualPlacing']['data'];
		$adjustToZeroIndex = 0;
	} else {
		$placings = getTournamentPlacingsForEdit($tournamentID);
		$adjustToZeroIndex = 1;
	}

	if(areAllMatchesFinished($tournamentID) == false){
		displayAlert("<strong> === WARNING === </strong><BR>
			<span  class='red-text'>There are still unfinished matches in this tournament.</span><BR>
			<em>It is highly advised that you ensure all matches are properly concluded.</em>",
			'alert');
	}

	if(isset($_SESSION['manualPlacing']['message'])){
		displayAlert($_SESSION['manualPlacing']['message']);
	}

	?>

	Click on squares to the right of names to set/clear ties.
	<form method='POST'>
	<input type='hidden' name='formName' value='finalizeTournament'>
	<input type='hidden' name='tournamentID' value='<?= $tournamentID ?>'>
		
	<?php 
	$tieCounter = 0;
	for($i=1;$i<=$max;$i++): 


		
		if(@$placings[$i]['tie'] != 0){
			$tieCounter++;
			if($tieCounter == 1){
				$placeNum = $i;
				$tieMark = TIE_TOP;
			} elseif($tieCounter == @$placings[$i]['tie']){
				$tieCounter = 0;
				$tieMark = TIE_BOTTOM;
			} else {
				$tieMark = TIE_MIDDLE;
			}
			$tieVal = @$placings[$i]['tie'];
			$class = 'blue-text';

		} else{
			$tieCounter = 0;
			$tieVal = 0;
			$placeNum = $i;
			$class = '';
			$tieMark = TIE_NO;
		}

		if($i != $max){
			$cursor  = 'pointer';
			$onClick = "onclick=\"placingsDeclareTie({$i},{$max})\"";
		} else {
			$cursor  = '';
			$onClick = '';
		}
		
		?>
	
		<!-- Select field -->
		<div class='input-group <?=$extraClass?>'>

		<!-- Hidden inputs -->
			<input type='hidden' id='place-value-<?=$i?>' value='<?=$placeNum?>'
				name='tournamentPlacings[placings][<?=$i?>][place]' >
			<input type='hidden' id='place-tie-<?=$i?>' value='<?=$tieVal?>'
				name='tournamentPlacings[placings][<?=$i?>][tie]' >

		<!-- Label for place -->
			<span class='input-group-label <?=$class?>' id='place-label-<?=$i?>'><?=$placeNum?></span>


		<!-- Select tournament participant -->
			<select class='input-group-field' name='tournamentPlacings[placings][<?=$i?>][rosterID]'>
				<option></option>";
				<?php 
				$checkAgainstID = @$placings[$i]['rosterID']; // Could not exist. Treat as null.
				foreach($roster as $person):
					$rosterID = $person['rosterID'];

	
					$selected = isSelected($rosterID, $selectedID);
					 ?>
					<option <?=optionValue($rosterID,$checkAgainstID)?> <?=$selected?> >
						<?= $names[$rosterID] ?>
					</option>";
				<?php endforeach ?>
			</select>

		<!-- The field to toggle a tie on/off -->
			<span class='input-group-label <?=$cursor?> ' <?=$onClick?>
				style="font-family:'Courier New', Courier, monospace;" >
				<strong class='blue-text' id='declare-tie-<?=$i?>'><?=$tieMark?></strong>
			</span>
		</div>

		
	<?php endfor ?>
	<button class='button' name='formName' value='finalizeTournament'>
		Finalize Tournament
	</button>
	<button class='button secondary' name='formName' value='finalizeTournament-no'>
		Cancel
	</button>
	
	</form>
	
<?php
	unset($_SESSION['manualPlacing']);
}

/******************************************************************************/

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
