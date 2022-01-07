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
	
	if(!isset($_SESSION['StatsInfo']['displayType'])){
		$_SESSION['StatsInfo']['displayType'] = 'percent';
	}

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
	
	
// Display tables
	eventExchangesTable($overall);
	echo "<HR>";
	tournamentExchangesTable($stats);
	echo "<HR>";
	tournamentTargetTable($stats);	

// Toggle button
	
	?>
	<div class='text-right'>

		<form method='POST'>
			<input type='hidden' name='formName' value='toggleStatsType'>

			<?php $class = ifSet('percent' != $_SESSION['StatsInfo']['displayType'] , 'hollow');?>
			<button class='button <?=$class?>' name='statsType[display]' value='percent'>
				% - Display Percentages
			</button>

			<?php $class = ifSet('value' != $_SESSION['StatsInfo']['displayType'] , 'hollow');?>
			<button class='button <?=$class?>' name='statsType[display]' value='value'>
				# - Display Totals
			</button>
		</form>
	</div>

	<?php
	
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function tournamentTargetTable($stats){
	?>
	
	<table>
		<caption>Target Areas By Tournament</caption>
		
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

		for($i = 1; $i <= 5; $i++){

			if($total > 0 && $data[$i] > 0){
				$percent[$i] = round(100*$data[$i]/$total,0)."%";
			} else {
				$percent[$i] = '';
			}

			if($_SESSION['StatsInfo']['displayType'] == 'value'){
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

function eventExchangesTable($totals){


	if(empty($totals['all'])){
		$total = 1;
		$actualTotal = 0; // To avoid the divide by zero
	} else {
		$total = $totals['all'];
		$actualTotal = $total;
	}

	if(empty($totals['clean'])){
		$cleanN = 0;
		$cleanP = '';
	} else {
		$cleanN = $totals['clean'];
		$cleanP = "(".(round($cleanN/$total,2)*100).'%)';
	}

	if(empty($totals['double'])){
		$doubleN = 0;
		$doubleP = '';
	} else {
		$doubleN = $totals['double'];
		$doubleP = "(".(round($doubleN/$total,2)*100).'%)';
	}

	if(empty($totals['afterblow'])){
		$afterN = 0;
		$afterP = '';
	} else {
		$afterN = $totals['afterblow'];
		$afterP = "(".(round($afterN/$total,2)*100).'%)';
	}
	
	if(empty($totals['noExchange'])){
		$noExchangeN = 0;
		$noExchangeP = '';
	} else {
		$noExchangeN = $totals['noExchange'];
		$noExchangeP = "(".(round($noExchangeN/$total,2)*100).'%)';
	}
	
	if(empty($totals['noQuality'])){
		$noQualityN = 0;
		$noQualityP = '';
	} else {
		$noQualityN = $totals['noQuality'];
		$noQualityP = "(".(round($noQualityN/$total,2)*100).'%)';
	}

	?>

	<div class='grid-x grid-margin-x'>
	

	<div class='medium-6 small-12 callout cell'>
		<h5>
		<div class='grid-x  grid-margin-x'>
		
		<!-- Title -->
		<div class='large-12 small-12 text-center'>
			<strong>Event Summary</strong>
		</div>
		
		<div class='medium-12 show-for-large'>
			&nbsp;
		</div>
		
		<!-- Tournaments -->
		<div class='small-9 cell'>
			Number of Tournaments:
		</div>
		<div class='small-3 cell align-self-middle'>
			<?=$totals['tournaments']?>
		</div>
		
		<div class='medium-12 hide-for-small-only'>
			&nbsp;
		</div>
		
		<!-- Weapons -->
		<div class='small-9 cell'>
			Number of Weapon Sets:
		</div>
		<div class='small-3 cell align-self-middle'>
			<?=$totals['weapons']?>
		</div>
		
		<div class='medium-12 hide-for-small-only'>
			&nbsp;
		</div>
		
		<!-- Matches -->
		<div class='small-9 cell'>
			Number of Matches:
		</div>
		<div class='small-3 cell align-self-middle'>
			<?=$totals['matches']?>
		</div>
		
		<div class='medium-12 hide-for-small-only'>
			&nbsp;
		</div>
		
		<!-- Pieces -->
		<div class='small-9 cell'>
			Number of Pieces:
			<?php tooltip("A piece is a &#39;match&#39; from a solo event. If you have an idea 
						for a better name that doesn&#39;t already belong to something please let us know. :)
						<BR><em>A &#39;round&#39; is already something.</em>");?>
		</div>
		<div class='small-3 cell align-self-middle'>
			<?=$totals['pieces']?>
		</div>
		
		</div>
		
		
		</h5>
		
	
	
	</div>
	
<!-- Exchanges -->
	<div class='medium-6 small-12 cell'>
	<table>
		<caption>Exchange Summary</caption>
		<tr>
			<td>Clean Hits:</td>
			<td><?=$cleanN?></td>
			<td><?=$cleanP?></td>
		</tr>
		<tr>
			<td>Double Hits:</td>
			<td><?=$doubleN?></td>
			<td><?=$doubleP?></td>
		</tr>
		<tr>
			<td>Afterblows:</td>
			<td><?=$afterN?></td>
			<td><?=$afterP?></td>
		</tr>
		<tr>
			<td>No Quality:</td>
			<td><?=$noQualityN?></td>
			<td><?=$noQualityP?></td>
		</tr>
		<tr>
			<td>No Exchanges:</td>
			<td><?=$noExchangeN?></td>
			<td><?=$noExchangeP?></td>
		</tr>
		
		<tr style='border-top:solid 1px'>
			<th>
				<em>Total Exchanges:</em>
			</th>
			<th class='text-left'>
				<em><?=$actualTotal?></em>
			</th>
			<th>
				&nbsp;
			</th>
		</tr>
	</table>
	
	</div>
	</div>
	
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

			if($_SESSION['StatsInfo']['displayType'] == 'value'){
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
