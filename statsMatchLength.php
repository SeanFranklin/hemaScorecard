<?php
/*******************************************************************************

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Match Length';
$hideEventNav = true;
$hidePageTitle = true;
$jsIncludes[] = 'stats_scripts.js';

include('includes/header.php');

if(true){

	$numDataSeries = countNumDataSeries();
	$legend = [];
	for($i = 0; $i < DATA_SERIES_MAX; $i++){
		$dataSeries[$i] = getTournamentExchangeTimeData($_SESSION['activeStatsItems']['tournamentIDs'][$i]);

		if($i < $numDataSeries){
			$legend[] = getEventAndTournamentName($_SESSION['activeStatsItems']['tournamentIDs'][$i], false);
		}
	}

	if(count($dataSeries[0]["exch"]["count"]['data']) > 10){
		$hasExchanges = true;
	} else {
		$hasExchanges = true;
	}

	if($dataSeries[0]['match']['length']['avg'] > 1){
		$hasTimestamps = true;
	} else {
		$hasTimestamps = true;
	}

	if($dataSeries[0]['exch']['fight']['avg'] > 1){
		$hasMatchTimers = true;
	} else {
		$hasMatchTimers = true;
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<script>
		window.onload = "updateMatchLengthPlot()";
	</script>


<!-- Warning Message ------------------------------------------------------------------------>

	<div class='callout cell primary' data-closable>
		Welcome to an exciting new feature that is <b>under construction</b>.<ol>
		<li>Due to the way that exchanges are in the database you can get some weirdness of things not lining up. I'm working on data integrity, but there might be small differences when you compare between the numbers you see below.</li>
		<li>This data is only as good as the table staff in the tournament make it. If the table does things like pool matches out of order, leaving a match open for a long time before closing it, or going back to matches to make changes, it will mess up the timestamps and look weird here.</li>
		<li>If you like this give me feedback on what other types of data you would like displayed!</li>

		<button class='close-button' aria-label='Dismiss alert' type='button' data-close>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>

<!-- Change Active Tournament ------------------------------------------------------------------------>

	<?=selectDataSeriesTournaments()?>

<!-- Display Graphs ------------------------------------------------------------------------>


	<?=displayMatchLengthPlot("Match Length [sec]",
								"Time between start and end of the match",
								$legend,
								30,500, $numDataSeries,
								[	$dataSeries[0]['match']['length'],
									$dataSeries[1]['match']['length'],
									$dataSeries[2]['match']['length'],
									$dataSeries[3]['match']['length']]
							)?>

	<?=displayMatchLengthPlot("Match Changeover [sec]",
								"Time between pool matches",
								$legend,
								30,500, $numDataSeries,
								[	$dataSeries[0]['match']['change'],
									$dataSeries[1]['match']['change'],
									$dataSeries[2]['match']['change'],
									$dataSeries[3]['match']['change']]
							)?>


	<?=displayMatchLengthPlot("Number of Exchanges",
								"Number of exchanges in a match",
								$legend,
								1,20, $numDataSeries,
								[	$dataSeries[0]['exch']['count'],
									$dataSeries[1]['exch']['count'],
									$dataSeries[2]['exch']['count'],
									$dataSeries[3]['exch']['count']]
							)?>





	<?=displayMatchLengthPlot("Exchange Time [sec]",
								"Total time, including fighting and judging",
								$legend,
								5, 60, $numDataSeries,
								[	$dataSeries[0]['exch']['total'],
									$dataSeries[1]['exch']['total'],
									$dataSeries[2]['exch']['total'],
									$dataSeries[3]['exch']['total']]
							)?>




	<?=displayMatchLengthPlot("Fighting Time [sec]",
								"Time between FIGHT and HOLD",
								$legend,
								2, 60, $numDataSeries,
								[	$dataSeries[0]['exch']['fight'],
									$dataSeries[1]['exch']['fight'],
									$dataSeries[2]['exch']['fight'],
									$dataSeries[3]['exch']['fight']]
							)?>

	<?=displayMatchLengthPlot("Judging Time [sec]",
								"Time between HOLD and FIGHT",
								$legend,
								2, 60, $numDataSeries,
								[	$dataSeries[0]['exch']['judge'],
									$dataSeries[1]['exch']['judge'],
									$dataSeries[2]['exch']['judge'],
									$dataSeries[3]['exch']['judge']]
							)?>

	<div class='text-right'>

		<?=dataModeForm();?>

	</div>

<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayMatchLengthPlot($dataName, $toolText, $legend, $binWidth, $plotWidth, $numDataSeries, $histogramData){

	if($numDataSeries == 0){
		return;
	}

	static $plotNum = 0;
	$plotNum++;

// Calcluate how many loops deep to go
	$maxDataPoints = 0;
	$numBins = ceil($plotWidth/$binWidth);
	$avgString = '';
	$allZeros = [];

	$plotData = "['x'";
	for($seriesNum = 0; $seriesNum < $numDataSeries; $seriesNum++){

		$allZeros[$seriesNum] = true;

		if($seriesNum != 0){
			$avgString .= " | ";
		}
		$avg = $histogramData[$seriesNum]['avg'];
		if($avg == null){
			$avg = '?';
		}
		$avgString .= $avg;

		$plotData .= ",\"".$legend[$seriesNum]."\"";

		for($i = 0;$i<$numBins;$i++){
			$bins[$i][$seriesNum] = 0;
			$totalData[$seriesNum] = 0;
		}

		foreach($histogramData[$seriesNum]['data'] as $data){
			$binNum = floor($data/$binWidth);

			if($binNum < 0){
				$binNum = 0;
			}
			if($binNum >= $numBins){
				$binNum = $numBins - 1;
			}

			if($binNum != 0){
				$allZeros[$seriesNum] = false;
			}

			$bins[$binNum][$seriesNum]++;
			$totalData[$seriesNum]++;
		}
	}
	$plotData .= "]";
	if($avgString == ''){
		$avgString = "?";
	}

	$combinedData = [];

	foreach($bins as $binNum => $binData){

		$binValue = ($binNum * $binWidth);
		if($binWidth != 1){
			$binValue += $binWidth/2;
		}

		$plotData .= ",[{$binValue }";
		foreach($binData as $seriesNum => $num){
			if($totalData[$seriesNum] != 0 && $allZeros[$seriesNum] == false){

				if($_SESSION['dataModes']['percent'] == false){
					$value = $num;
				} else {
					$value = 100 * $num / $totalData[$seriesNum];
				}

			} else {
				$value = 0;
			}

			$plotData .= ",{$value}";
		}
		$plotData .="]";
	}

?>
	<HR>
	<h5>
		<?=$dataName?>
		(Average: <?=$avgString ?>)
		<?=tooltip($toolText)?>
	</h5>
	<?=plotLineChart($plotData,$plotNum,$dataName, $binWidth, $plotWidth)?>

<?php
}



/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
