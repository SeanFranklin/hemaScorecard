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
	$tournamentList = sortTournamentsForPlacings($tournamentList);
	$anyFinalized = areAnyTournamentsFinalized($_SESSION['eventID']);

	if(!isset($_SESSION['manualTournamentPlacing'])){
		$_SESSION['manualTournamentPlacing'] = '';
	}

	if(ALLOW['EVENT_SCOREKEEP'] == true || ALLOW['EVENT_MANAGEMENT'] == true){
		define('ALLOW_EDITING',true);
	} else {
		define('ALLOW_EDITING',false);
	}

	$tournamentsToDisplay = sortTournamentAndDivisions($_SESSION['eventID']);

	foreach((array)$tournamentList as $tournamentID => $data){
		$tournamentList[$tournamentID]['name'] = getTournamentName($tournamentID);
		$tournamentList[$tournamentID]['link'] = "onclick='javascript:document.goToTournamentAlt{$tournamentID}.submit();'
					style='cursor: pointer;'";
	}

	foreach($tournamentsToDisplay as $j => $t){

		if(isset($t['divisionID']) == true){

			if(isset($t['tournaments']) == false){
				unset($tournamentsToDisplay[$j]);
				continue;
			}

			$firstTab = true;

			foreach((array)$t['tournaments'] as $i => $item){

				$tID = $item['tournamentID'];

				$numChars = strlen($t['tournaments'][$i]['shortName']);

				$size = 1 - 0.02 * ($numChars - 10);

				$size = min(1, $size);
				$size = max($size, 0.6);
				$tournamentsToDisplay[$j]['tournaments'][$i]['tabTextSize'] = $size;

				$tournamentList[$tID]['placings'] = getTournamentPlacings($tID);

				$tournamentList[$tID]['tabClass'] = "";
				$tournamentList[$tID]['bodyClass'] = " ";

				if(count($tournamentList[$tID]['placings']) == 0){
					$tournamentList[$tID]['tabClass'] .= 'unfinished';
				}

				if($firstTab == true){
					$tournamentList[$tID]['tabClass'] .= " selected";
					$firstTab = false;
				} else {
					$tournamentList[$tID]['bodyClass'] .= " hidden";
				}

			}

		}
	}



// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>
	<?php if($anyFinalized == false){
		showEventDescription();
	}?>

	<div class='grid-x grid-margin-x'>

		<?=manualTournamentPlacing((int)@$_SESSION['manualPlacing']['tournamentID'])?>

		<?=showEventBurgees()?>

		<?=showTournamentPlacings($tournamentsToDisplay,  $tournamentList)?>

	</div>

	<?php if($anyFinalized == true){
		showEventDescription();
	}?>

<?php }

unset($_SESSION['manualPlacingMessage']);

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function showTournamentPlacings($tournamentsToDisplay,  $tournamentList){
?>

	<?php foreach($tournamentsToDisplay as $j => $t):
		$groupClass = "tab-group-{$j}";

		?>
		<div class='large-6 medium-10 small-12 cell' style='padding-bottom: 1em;'>

			<?php if(isset($t['divisionID']) == false):?>

				<?= displayTournamentPlacings($t['tournamentID'], $tournamentList[$t['tournamentID']]) ?>

			<?php else: ?>

				<nav>
					<?php foreach(@(array)$t['tournaments'] as $i => $item):?>
						<div class="folder-tab  <?=$groupClass?>-tab <?=$tournamentList[$item['tournamentID']]['tabClass']?>" id='<?=$groupClass?>-tab-<?=$i?>'
							onclick="switchActiveTab(<?=$i?>, '<?=$groupClass?>')">
							<span style='font-size: <?=$item['tabTextSize']?>em;line-height: 1.0em;'>
								<?=$item['shortName']?>
							</span>
						</div>
					<?php endforeach?>
				</nav>

				<div class="tabs-body">
					<?php foreach(@(array)$t['tournaments'] as $i => $item):?>
						<div class='<?=$groupClass?>-body <?=$tournamentList[$item['tournamentID']]['bodyClass']?>' id='<?=$groupClass?>-body-<?=$i?>'>
							<?= displayTournamentPlacings($item['tournamentID'], $tournamentList[$item['tournamentID']]) ?>
						</div>
					<?php endforeach?>
				</div>

			<?php endif ?>

		</div>

	<?php endforeach ?>
<?php
}

/******************************************************************************/

function showEventBurgees(){

	$burgeeIDs = getEventBurgees($_SESSION['eventID']);

	$numBurgees = count($burgeeIDs );
	if($numBurgees > 1){
		$size = 6;
	} else {
		$size = 8;
	}

	foreach($burgeeIDs as $burgeeID){
		burgeeDisplay($burgeeID, true, $size);
		$isBurgees = true;
	}

	if($numBurgees != 0){
		echo "<div class='cell'><HR></div>";
	}

}

/******************************************************************************/

function showEventDescription(){

	$eventDescription = getEventDescription($_SESSION['eventID']);

	if(strlen($eventDescription) <= 1){
		$eventDescription = "<i>[No Event Description Created]</i>";
	}

?>
	<div class='documentation-div callout success'>
		<a name='top-of-description'></a>
		<?=$eventDescription?>
	</div>
<?php
}

/******************************************************************************/

function sortTournamentsForPlacings($tournamentList){

	$sortedList = [];
	foreach($tournamentList as $index => $data){
		if($data['isFinalized'] == 1 && $data['hideFinalResults'] == 1){
			$list[5][$index] = $data;
			unset($tournamentList[$index]);
		} elseif($data['formatID'] == FORMAT_META && $data['isFinalized'] == 1){
			$list[1][$index] = $data;
			unset($tournamentList[$index]);
		} elseif($data['isFinalized'] == 1){
			$list[2][$index] = $data;
			unset($tournamentList[$index]);
		} elseif($data['formatID'] == FORMAT_META){
			$list[4][$index] = $data;
			unset($tournamentList[$index]);
		} else {
			$list[3][$index] = $data;
			unset($tournamentList[$index]);
		}
	}

	for($i=1;$i<=5;$i++){
		if(isset($list[$i]) == false){
			continue;
		}

		foreach($list[$i] as $index => $data){
			$sortedList[$index] = $data;
		}
	}

	foreach($tournamentList as $index => $data){
		$sortedList[$index] = $data;
	}
	return $sortedList;
}


/******************************************************************************/

function displayTournamentPlacings($tournamentID, $data){

	$placings = getTournamentPlacings($tournamentID);
	$fieldsetLink = $data['link'];
	$isTeams = $data['isTeams'];


	if(($data['hideFinalResults'] == true)
		&& (ALLOW['EVENT_SCOREKEEP'] == false)
		&& (ALLOW['VIEW_SETTINGS'] == false)
		&& (ALLOW['STATS_EVENT'] == false)){

		echo "This tournament does not have placings.";
		return;
	 }

// Calculate which results to show on the main screen
	$i = 0;
	$j = 0;
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

	// Fighter name
		$rosterID = $placings[$i]['rosterID'];
		if($rosterID == null){continue;}

		if((int)$data['isTeams'] != 0){
			$tmp['name'] = getTeamName($rosterID);
		} else {
			$tmp['name'] = getFighterName($rosterID);
		}

	// Append "tie" if the standings are tied
		$tmp['placing'] = $placings[$i]['placing'];
		if($j != 0 && $placings[$i]['placing'] == $placingDisplay1[$j-1]['placing']){
			$txt = "<i>".$placings[$i]['placing']."-tie</i>";
			$tmp['placeText'] = $txt;
			$placingDisplay1[$j-1]['placeText'] = $txt;
		} else {
			$tmp['placeText'] = $placings[$i]['placing'];
		}

	// School name to display
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

		$placingDisplay1[$j] = $tmp;
		$j++;
	}

    //// !!   -------------------------------------------------------------   !! ////
	//      DO NOT RE-SET i BETWEEN THESE TWO LOOPS //
	//// !!   -------------------------------------------------------------   !! ////

// Calculate which results to show on the secondary display.
	$placingDisplay2 = [];
	$j = 0;
	for($i;$i<$placingsToShow;$i++){

	// Append "tie" if the standings are tied
		$tmp['placing'] = $placings[$i]['placing'];
		if($j != 0 && $placings[$i]['placing'] == $placingDisplay2[$j-1]['placing']){
			$txt = "<i>".$placings[$i]['placing']."-tie</i>";
			$tmp['placeText'] = $txt;
			$placingDisplay2[$j-1]['placeText'] = $txt;
		} else {
			$tmp['placeText'] = $placings[$i]['placing'];
		}

	// Fighter name
		$rosterID = $placings[$i]['rosterID'];
		if($rosterID == null){continue;}

		if((int)$data['isTeams'] != 0){
			$tmp['name'] = getTeamName($rosterID);
		} else {
			$tmp['name'] = getFighterName($rosterID);
		}

	// School name to display
		if(isset($placings[$i]['schoolFullName'])){
			$tmp['school'] = $placings[$i]['schoolFullName'];
			if($placings[$i]['schoolBranch'] != null){
				$tmp['school'] .= ", ".$placings[$i]['schoolBranch'];
			}
		} else {
			$tmp['school'] = '';
		}

		$placingDisplay2[$j] = $tmp;
		$j++;
	}

	$showAllText = null;
	if($placingsToShow != 0){
		$showAllText = "Show All  ↓ (".$placingsToShow.")";
	}

?>


<!-- Display the top 4 -->

	<form method='POST' name='goToTournamentAlt<?= $tournamentID; ?>'>
		<input type='hidden' name='formName' value='changeTournament'>
		<input type='hidden' name='newPage' value='participantsTournament.php'>
		<input type='hidden' name='newTournament' value=<?= $tournamentID; ?>>
	</form>

	<table class='data-table text-left results-table'>

		<tr>

			<td class='results-table-header' colspan='100%'>

				<a style='font-size:1.5em' <?=$fieldsetLink?>>
					<?=getTournamentName($tournamentID)?>

				</a>

				<span style='float: right;'>
					<a class='extra-results-<?=$tournamentID?>'
					onclick= "$('.extra-results-<?=$tournamentID?>').toggle()">
					<?=$showAllText?>
					</a>
					<a onclick= "$('.extra-results-<?=$tournamentID?>').toggle()" class='extra-results-<?=$tournamentID?> hidden'>Hide ↑</a>
				</span>

			</td>
		</tr>


		<?php foreach($placingDisplay1 as $data):?>

			<tr>
				<td class='text-center no-wrap'>
					<?= $data['placeText'] ?>
				</td>
				<td style='text-align: left;'>
					<strong><?=$data['name']?></strong>
				</td>
				<td style='text-align: left;'>
					<em><?= $data['school'] ?></em>
				</td>
			</tr>

		<?php endforeach ?>


<!-- Display results above 4th place -->

	<!-- Toggle visibility -->
	<?php if($placingDisplay2 != null): ?>

		<?php foreach($placingDisplay2 as $data):?>
			<!-- Display data -->
			<tr class='extra-results-<?=$tournamentID?> hidden'>

				<td class='text-center no-wrap'>
					<?= $data['placeText'] ?>
				</td>
				<td style='text-align: left;'>
					<strong><?=$data['name']?></strong>
				</td>
				<td style='text-align: left;'>
					<em><?= $data['school'] ?></em>
				</td>

			</tr>
		<?php endforeach ?>

	<?php else: ?>

		<tr>
			<td colspan='100%' class='results-table-info' >
				<i>The event organizer has not finalized the tournament placings.</i>
			</td>
		</tr>

	<?php endif ?>

	<tr>
		<td colspan='100%'  class='results-table-info'>

		</td>
	</tr>


	<?php if(ALLOW_EDITING == true):?>
		<tr>
			<td colspan='100%' class='results-table-info border-top'>
				<?= manageTournamentPlacings($tournamentID, $isTeams )?>
			</td>
		</tr>
	<?php endif ?>


	</table>



<?php
}

/******************************************************************************/

function manageTournamentPlacings($tournamentID, $isTeams){

	$tournamentID = (int)$tournamentID;

	$isFinalized = isFinalized($tournamentID);

	if($isFinalized == false && areAllMatchesFinished($tournamentID) == false){
		$manualClass = 'secondary';
		$allowAutoFinalize = false;
	} else {
		$manualClass = '';
		$allowAutoFinalize = true;
	}


	if($isFinalized == false && isBrackets($tournamentID) == true){
		autoFinalizeSpecificationBox($tournamentID);
		$useSpecs = true;
	} else{
		$useSpecs = false;
	}


	$incompleteMatches = [];
	if($allowAutoFinalize == false){
		$incompleteMatches = getTournamentIncompletes($tournamentID, $isTeams);
	}


?>
	<form method='POST'>
	<input type='hidden' name='tournamentID' value='<?=$tournamentID ?>'>

	<?php if($isFinalized == false): ?>

		<?php if(!isResultsOnly($tournamentID)): ?>

			<?php if($allowAutoFinalize == true): ?>
				<?php if($useSpecs == false): ?>
					<button class='button no-bottom success' name='formName'
						value='autoFinalizeTournament'>
						Auto Finalize Tournament
					</button>
				<?php else: ?>
					<a class='button no-bottom success'
						data-open='autoFinalizeBox-<?=$tournamentID?>'>
						Auto Finalize Tournament
					</a>
				<?php endif ?>
			<?php else: ?>

				<a onclick="$('#incomplete-matches-<?=$tournamentID?>').toggle()">
					<?=$incompleteMatches['count']?> Incomplete Matches ↓
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


	<div class='hidden' id='incomplete-matches-<?=$tournamentID?>'>

		<HR>

		<?php foreach($incompleteMatches['list'] as $m): ?>
			<b><?=$m['name1']?></b> vs <b><?=$m['name2']?></b>;
			<?=$m['groupName']?>
			(id: <?=$m['matchID']?>)
			<BR>
		<?php endforeach ?>

		<?php if($incompleteMatches['more'] != 0):?>
			and <?=$incompleteMatches['more']?> more
		<?php endif ?>

	</div>


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

function manualTournamentPlacing($tournamentID){

	if(ALLOW_EDITING == false || $tournamentID == 0){
		return;
	}

	$isTeams = isTeams($tournamentID);
	$roster = getTournamentCompetitors($tournamentID);
	$name = getTournamentName($tournamentID);
	$max = sizeof($roster);

	define("TIE_TOP",'↰');
	define("TIE_MIDDLE",'|');
	define("TIE_BOTTOM",'↲');
	define("TIE_NO",'&nbsp;');

	foreach($roster as $person){

		if($isTeams){
			$names[$person['rosterID']] = getTeamName($person['rosterID']);
		} else {
			$names[$person['rosterID']] = getFighterName($person['rosterID']);
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

	<div class='large-7 medium-10 small-12 fieldset cell primary callout'
		style='padding-bottom: 0.5em;padding-top: 0.5em;margin-bottom: 0.2em;'>


			<h4 class='no-bottom' ><?=$name?></h4>


		Click on squares to the right of names to set/clear ties.
		<form method='POST'>
		<input type='hidden' name='formName' value='finalizeTournament'>
		<input type='hidden' name='tournamentID' value='<?= $tournamentID ?>'>

		<?php

		for($i=1;$i<=$max;$i++):

			$class = '';

			if(isset($placings[$i-1]['place']) == true){
				$isOneBefore = true;
			} else {
				$isOneBefore = false;
			}

			if(isset($placings[$i+1]['place']) == true){
				$isOneMore = true;
			} else {
				$isOneMore = false;
			}

			if(isset($placings[$i]) == false){

				$placings[$i]['place'] = $i;
				$tieMark = TIE_NO;

			} else if($isOneMore == true && $placings[$i]['place'] == $placings[$i+1]['place']){

				if($i != 1 && $placings[$i]['place'] == $placings[$i-1]['place']){
					$tieMark = TIE_MIDDLE;
				} else {
					$tieMark = TIE_TOP;
				}

				$class = 'blue-text';

			} else {

				if($isOneBefore == true && $placings[$i]['place'] == $placings[$i-1]['place']){
					$class = 'blue-text';
					$tieMark = TIE_BOTTOM;
				} else {

					$tieMark = TIE_NO;
				}

			}

			$placeNum = $placings[$i]['place'];


			if($i != $max){
				$cursor  = 'pointer';
				$onClick = "onclick=\"placingsDeclareTie({$i},{$max})\"";
			} else {
				$cursor  = '';
				$onClick = '';
			}

			?>

			<!-- Select field -->
			<div class='input-group'>

			<!-- Hidden inputs -->
				<input type='hidden' id='place-value-<?=$i?>' value='<?=$placeNum?>'
					name='tournamentPlacings[placings][<?=$i?>][place]' >


			<!-- Label for place -->
				<span class='input-group-label <?=$class?>' id='place-label-<?=$i?>'><?=$placeNum?></span>


			<!-- Select tournament participant -->
				<select class='input-group-field' name='tournamentPlacings[placings][<?=$i?>][rosterID]'>
					<option></option>";
					<?php
					$checkAgainstID = @$placings[$i]['rosterID']; // Could not exist. Treat as null.
					foreach($roster as $person):
						$rosterID = $person['rosterID'];?>
						<option <?=optionValue($rosterID,$checkAgainstID)?>>
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
	</div>

	<div class='large-12 cell'>
		<HR>
	</div>


<?php
	unset($_SESSION['manualPlacing']);
}

/******************************************************************************/

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
