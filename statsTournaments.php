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
} elseif(USER_TYPE < USER_ADMIN && USER_TYPE != USER_STATS){
	pageError('user');
} else {
	
	$exchangesByTournament = getEventExchanges();
	
	$stats = getEventStats($exchangesByTournament);
	$overall = $stats['overall'];
	unset($stats['overall']);
	
	$matchTotals = getNumEventMatches();
	$overall['matches'] = $matchTotals['matches'];
	$overall['pieces'] = $matchTotals['pieces'];
	
	$eventTournaments = getEventTournaments();
	$overall['tournaments'] = count($eventTournaments);
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
			<th>1</th>
			<th>2</th>
			<th>3</th>
			<th>4</th>
			<th>5</th>
		</tr>

	<?php foreach((array)$stats as $tournamentID => $data):
		
		$name = getTournamentName($data['tournamentID']);
		
		$pt1 = $data[1];
		$pt2 = $data[2];
		$pt3 = $data[3];
		$pt4 = $data[4];
		$pt5 = $data[5];
		$totalPts = $pt1 + $pt2 + $pt3 + $pt4 + $pt5;
		
		if($totalPts != 0){
			$pt1P = round($pt1/$totalPts,2)*100;
			$pt2P = round($pt2/$totalPts,2)*100;
			$pt3P = round($pt3/$totalPts,2)*100;
			$pt4P = round($pt4/$totalPts,2)*100;
			$pt5P = round($pt5/$totalPts,2)*100;
		}
		?>
		
	<!-- Data -->	
		<tr>
			<td><?=$name?></td>
			<td><?=$pt1?></td>
			<td><?=$pt2?></td>
			<td><?=$pt3?></td>
			<td><?=$pt4?></td>
			<td><?=$pt5?></td>
		</tr>
		
	<?php endforeach ?>
	
	</table>
	
<?php }

/******************************************************************************/

function eventExchangesTable($totals){
	if($totals['all'] != null){
		$total = $totals['all'];
	} else {$total = 1;}
	
	$cleanP = round($totals['clean']/$total,2)*100;
	$doubleP = round($totals['double']/$total,2)*100;
	$afterP = round($totals['afterblow']/$total,2)*100;
	$noP = round($totals['noExchange']/$total,2)*100;
	$noQ = round($totals['noQuality']/$total,2)*100;
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
			Number of Types:
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
			<td><?=$totals['clean']?></td>
			<td>(<?=$cleanP?>%)</td>
		</tr>
		<tr>
			<td>Double Hits:</td>
			<td><?=$totals['double']?></td>
			<td>(<?=$doubleP?>%)</td>
		</tr>
		<tr>
			<td>Afterblows:</td>
			<td><?=$totals['afterblow']?></td>
			<td>(<?=$afterP?>%)</td>
		</tr>
		<tr>
			<td>No Quality:</td>
			<td><?=$totals['noQuality']?></td>
			<td>(<?=$noQ?>%)</td>
		</tr>
		<tr>
			<td>No Exchanges:</td>
			<td><?=$totals['noExchange']?></td>
			<td>(<?=$noP?>%)</td>
		</tr>
		
		<tr style='border-top:solid 1px'>
			<th>
				<em>Total Exchanges:</em>
			</th>
			<th class='text-left'>
				<em><?=$totals['all']?></em>
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

			$clean = $data['clean'];
			$double = $data['double'];
			$noExchange = $data['noExchange'];
			$noQuality = $data['noQuality'];
			$afterblow = $data['afterblow'];
			$all = $data['total'];
			$cleanScore = $data['cleanScore'];
			$BpE = $data['BpE'];
			
			if($all != 0 ){
				
				$cleanP = round($clean/$all,2)*100;
				$doubleP = round($double/$all,2)*100;
				$afterP = round($afterblow/$all,2)*100;
				$noP = round($noExchange/$all,2)*100;
				$noQ = round($noQuality/$all,2)*100;
			}
			?>
			
	<!-- Data -->
			<tr>
				<td><?=$name?></td>
				<td><?=$all?></td>
				<td><?=$clean?> (<?=$cleanP?>%)</td>
				<td><?=$double?> (<?=$doubleP?>%)</td>
				<td><?=$afterblow?> (<?=$afterP?>%)</td>
				<td><?=$noQuality?> (<?=$noQ?>%)</td>
				<td><?=$noExchange?> (<?=$noP?>%)</td>
				<td><?=$BpE?>%</td>
			</tr>
			
		<?php endforeach ?>
	
	</table>

<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
