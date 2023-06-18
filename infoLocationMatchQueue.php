<?php
/*******************************************************************************
	Logistics Locations

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Fighter Locations';
$includeTournamentName = false;
$hideEventNav = true;
$hidePageTitle = true;
$refreshPageTimer = 30;
$jsIncludes[] = 'logistics_management_scripts.js';
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif($_SESSION['tournamentID'] == null){
	pageError('tournament');
} elseif (ALLOW['VIEW_MATCHES'] == false){
	displayAlert("Event is still upcoming<BR>Ring assignments not yet released");
} else {


	$locations = logistics_getEventLocations($_SESSION['eventID'], 'ring');
	$numRings = sizeof($locations);
	$numOptions = min($numRings,2);

	$columnList = [];
	foreach(((array)@$_SESSION['fighterQueueColumns']) as $col){
		if((int)$col != 0){
			$columnList[] = $col;
		}
	}

	$matchData = [];
	foreach($columnList as $index => $locationID){
		$matchData[$index]['locationID'] = $locationID;
		$pools = getMatchesByLocationPool($locationID, $_SESSION['tournamentID'], true);
		$brackets = getMatchesByLocationBracket($locationID, $_SESSION['tournamentID'], true);
		$matchData[$index]['groups'] = array_merge($pools, $brackets);
	}


	$numCols = sizeof((array)$matchData);
	$colSize = 12; // flex grid size
	if($numCols > 1){
		$colSize = floor($colSize / $numCols);
	}

	$color1 = COLOR_CODE_1;
	$color2 = COLOR_CODE_2;
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<form method='POST'>
	<div class='input-group'>
		<span class='input-group-label'>Select Rings</span>
		<?php for($i = 0; $i<$numOptions; $i++):?>
			<select class='input-group-field' name='setFighterQueueColumns[<?=$i?>]'>
				<option value='0'>n/a</option>
				<?php foreach($locations as $location):?>
					<option value='<?=$location['locationID']?>'><?=$location['locationName']?></option>
				<?php endforeach ?>
			</select>
		<?php endfor ?>

		<button class='button input-group-button success' name='formName' value='setFighterQueueColumns'>
			Populate
		</button>
	</div>
	</form>

	<div class='grid-x grid-margin-x'>
		<?php for($i = 0; $i<$numCols; $i++):
			$locationID = (int)$matchData[$i]['locationID'];
			?>
			<div class='large-<?=$colSize?> cell text-center' style='border-left: solid black 1px'>
				<div class='callout warning'><b style='font-size: 200%'><?=logistics_getLocationName($locationID)?></b></div>
				<table style=''>
				<?php foreach($matchData[$i]['groups'] as $group):
					$groupName = $group[0]['groupName'];
					if($groupName == 'winner' || $groupName == 'loser'){
						$groupName = "Bracket";
					}
					?>

					<tr><td colspan='100%'>
						<div class='callout primary'><h3><?=$groupName?></h3></div>
					</td></tr>

					<?php foreach($group as $match):
						if($match['matchComplete'] == true || $match['ignoreMatch'] == true){
							$matchClass = '';
						} else {
							$matchClass = 'match-queue';
						}

						?>

						<tr class='<?=$matchClass?>'>
							<td><?=$match['matchNumber']?></td>
							<td class='<?=$matchClass?>' style='background-color: <?=$color1?>'><?=getFighterName($match['fighter1ID'])?></td>
							<td>vs</td>
							<td class='<?=$matchClass?>' style='background-color: <?=$color2?>'><?=getFighterName($match['fighter2ID'])?></td>
						</tr>
					<?php endforeach ?>

				<?php endforeach ?>
				</table>
			</div>
		<?php endfor?>


	</div>

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/******************************************************************************/


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
