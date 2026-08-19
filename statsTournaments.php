<?php
/*******************************************************************************
	Tournament Summary

	Summary of all the tournament events, including clean hits, double, etc..
	LOGIN:
		- ADMIN and above can view
		- STATS can view

*******************************************************************************/

$pageName = 'Tournament Summary';
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['VIEW_MATCHES'] == false){
	displayAlert("Event is still upcoming<BR>Matches not yet released");
} else {

	$exchangesByTournament = getEventExchanges($_SESSION['eventID']);

	$stats = getEventStats($exchangesByTournament);
	$overall = $stats['overall'];
	unset($stats['overall']);

	$matchTotals = getNumEventMatches($_SESSION['eventID']);
	$overall['matches'] = $matchTotals['matches'];
	$overall['pieces'] = $matchTotals['pieces'];

	$eventTournaments = getEventTournaments();
	$overall['tournaments'] = count($eventTournaments);

	$weaponList = [];
	foreach($eventTournaments as $tournamentID){
		$weapon = getTournamentWeapon($tournamentID);
		$weaponList[$weapon['weaponID']] = true;
	}
	$overall['weapons'] = count($weaponList);

	foreach((array)$stats as $data){
		$createSortableDataTable[] = ['tournament-stats-'.$data['tournamentID'],100];
	}


// Display tables
	echo "<div class='cell grid-x grid-margin-x'>";
		eventExchangesTable($overall);
	echo "</div>";
	echo "<HR>";
	tournamentExchangesTable($stats);
	echo "<HR>";
	tournamentTargetTable($stats);

// Toggle button

	?>
	<div class='text-right'>

		<?=dataModeForm()?>
	</div>

	<?php

	echo "<HR>";
	tournamentExtendedExchangeInfo($stats);


}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function tournamentExtendedExchangeInfo($stats){

	if((int)@$_SESSION['dataModes']['extendedExchangeInfo'] != 1){
		return;
	}

	$extendedInfo = [];
	$total = [];
	$createSortableDataTable = [];

	$tIDs = getEventTournaments($_SESSION['eventID']);

	foreach((array)$tIDs as $tournamentID){
		$extendedInfo[$tournamentID] = getExchangeCountsByExtraInfo($tournamentID);
		$total[$tournamentID] = 0;

		foreach($extendedInfo[$tournamentID] as $attack){
			$total[$tournamentID] += (int)$attack['numExchanges'];
		}

	}

	foreach((array)$extendedInfo as $tournamentID => $data){

		foreach($data as $index => $attack){

			if($total[$tournamentID] != 0){
				$percent = 100*$attack['numExchanges']/$total[$tournamentID];
			} else {
				$percent = 0;
			}

			$percentDisp = number_format($percent,1)." %";
			$extendedInfo[$tournamentID][$index]['percentDisp'] = $percentDisp;

			if($percent < 1){
				$extendedInfo[$tournamentID][$index]['class'] = 'grey-text';
			} else {
				$extendedInfo[$tournamentID][$index]['class'] = '';
			}
		}

	}

?>
	<h3>Extended Exchange Info (<i>if captured</i>)</h3>


	<?php foreach((array)$extendedInfo as $tournamentID => $data): ?>
		<h4><?=getTournamentName($tournamentID )?></h4>
	<table  class="display" id='tournament-stats-<?=$tournamentID?>'>



		<thead>
			<tr>
				<th>prefix</td>
				<th>type</td>
				<th>target</td>
				<th>exchangeType</td>
				<th>numExchanges</td>
				<th>percentDisp</td>
			</tr>
		</thead>

		<tbody>
		<?php foreach($data as $attack):


			?>
			<tr>
				<td class='text-right'><?=$attack['prefix']?></td>
				<td class='text-right'><?=$attack['type']?></td>
				<td class='text-right'><?=$attack['target']?></td>
				<td class='text-right'><?=$attack['exchangeType']?></td>
				<td class='text-right'><?=$attack['numExchanges']?></td>
				<td class='text-right <?=$attack['class']?>'><?=$attack['percentDisp']?></td>
			</tr>

		<?php endforeach ?>
		</tbody>
	</table>
	<HR>
	<?php endforeach ?>


<?php

}

/******************************************************************************/

/******************************************************************************/

function tournamentTargetTable($stats){
?>

	<table>
		<caption>Target Values By Tournament</caption>

	<!-- Headers -->
		<tr>
			<th>Tournament</th>
			<th>1 pt</th>
			<th>2 pts</th>
			<th>3 pts</th>
			<th>4 pts</th>
			<th>5 pts</th>
		</tr>

	<?php foreach((array)$stats as $tournamentID => $data):

		$name = getTournamentName($data['tournamentID']);
		$total = 0;


		for($i = 1; $i <= 5; $i++){
			if(!isset($data[$i])){
				$data[$i] = 0;
			}
			$total += $data[$i];
		}

		$percent = [];

		for($i = 1; $i <= 5; $i++){

			if($total > 0 && $data[$i] > 0){
				$percent[$i] = round(100*$data[$i]/$total,0)."%";
			} else {
				$percent[$i] = '';
			}

			if($_SESSION['dataModes']['percent'] == false){
				$disp[$i] = $data[$i];
			} else {
				$disp[$i] = $percent[$i];
			}
		}



		?>

	<!-- Data -->
		<tr>
			<td><?=$name?></td>
			<td><?=$disp[1]?></td>
			<td><?=$disp[2]?></td>
			<td><?=$disp[3]?></td>
			<td><?=$disp[4]?></td>
			<td><?=$disp[5]?></td>
		</tr>

	<?php endforeach ?>

	</table>

<?php }



/******************************************************************************/
function tournamentExchangesTable($stats){
	$bilateralsText = "Bilaterals per Exchange - ";
	$bilateralsText .= "Doubles & Afterblows per total scoring exchanges";
	$displaMode = 'a';
	?>


	<table>
	<caption>Tournament Exchanges by Type</caption>

	<!-- Headers -->
		<tr>
			<th>Tournament</th>
			<th>Exchanges</th>
			<th>Clean Hits</th>
			<th>Double Hits</th>
			<th>Afterblows</th>
			<th>No Quality</th>
			<th>No Exchanges</th>
			<th>
				BpE
				<?php tooltip($bilateralsText); ?>
			</th>
		</tr>

		<?php foreach((array)$stats as $tournamentID => $data):

			$name = getTournamentName($data['tournamentID']);

			$cleanN = $data['clean'];
			$doubleN = $data['double'];
			$noExchangeN = $data['noExchange'];
			$noQualityN = $data['noQuality'];
			$afterblowN = $data['afterblow'];
			$all = $data['total'];
			$BpE = $data['BpE'];

			if($all == 0){
				continue;
			}

			$cleanP = (round($cleanN/$all,2)*100)."%";
			$doubleP = (round($doubleN/$all,2)*100)."%";
			$afterblowP = (round($afterblowN/$all,2)*100)."%";
			$noExchangeP = (round($noExchangeN/$all,2)*100)."%";
			$noQualityP = (round($noQualityN/$all,2)*100)."%";

			if($_SESSION['dataModes']['percent'] == false){
				$clean = $cleanN;
				$afterblow = $afterblowN;
				$double = $doubleN;
				$noExchange = $noExchangeN;
				$noQuality = $noQualityN;
			} else {
				$clean = $cleanP;
				$afterblow = $afterblowP;
				$double = $doubleP;
				$noExchange = $noExchangeP;
				$noQuality = $noQualityP;
			}

			?>

	<!-- Data -->
			<tr>
				<td><?=$name?></td>
				<td><?=$all?></td>
				<td><?=$clean?></td>
				<td><?=$double?></td>
				<td><?=$afterblow?></td>
				<td><?=$noQuality?></td>
				<td><?=$noExchange?></td>
				<td><?=$BpE?>%</td>
			</tr>

		<?php endforeach ?>

	</table>

<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
