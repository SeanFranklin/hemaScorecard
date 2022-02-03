<?php
/*******************************************************************************

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Match Length';

include('includes/header.php');

if(ALLOW['VIEW_MATCHES'] == false){
	displayAlert("Event is still upcoming<BR>Matches not yet released");
} else if($_SESSION['tournamentID'] == null){
	pageError('tournament');
} elseif($_SESSION['formatID'] != FORMAT_MATCH){
	displayAlert('This data can only be displayed for <em>Sparring Matches</em> type tournaments.');
} else {

	$tLength = getTournamentExchangeTimeData($_SESSION['tournamentID']);

	$exchWindowSize = 60;
	$exchBinSize = 2;

	$totalExchanges = count($tLength["exch"]["count"]['data']);
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>


	<script>
		window.onload = "updateMatchLengthPlot()";
	</script>

	<div class='callout primary'>
		Welcome to an exciting new feature that is <b>under construction</b>.<ol>
		<li>Due to the way that exchanges are in the database you can get some weirdness of things not lining up. I'm working on data integrity, but there might be small differences when you compare between the numbers you see below.</li>
		<li>This data is only as good as the table staff in the tournament make it. If the table does things like pool matches out of order, leaving a match open for a long time before closing it, or going back to matches to make changes, it will mess up the timestamps and look weird here.</li>
		<li>If you like this give me feedback on what other types of data you would like displayed!</li>
	</div>

	<?php if(count($tLength["exch"]["count"]['data']) < 10): ?>
		<div class='callout warning'>
			Less that 10 exchanges have been recorded for this tournament, so we will hold of on publishing the data until they fight more.
		</div>
	<?php elseif($tLength['match']['length']['avg'] > 1):?>
		<?=displayMatchLengthPlot("Match Length [sec]",
									$tLength['match']['length'],
									"Time between start and end of the match",
									30,500)?>
		
		<?=displayMatchLengthPlot("Match Changeover [sec]",
									$tLength['match']['change'],
									"Time between pool matches",
									30,500)?>
	<?php else: ?>
		<div class='callout warning'>
			This tournament predates the HEMA Scorecard database saving a timestamp on exchanges. Saddly we can't give you any information on match or exchange lengths.
		</div>
	<?php endif ?>

	<?=displayMatchLengthPlot("Number of Exchanges",
								$tLength['exch']['count'],
								"Number of exchanges in a match",
								1)?>

	<?php if($tLength['match']['length']['avg'] > 1):?>



		<?=displayMatchLengthPlot("Exchange Time [sec]",
									$tLength['exch']['total'],
									"Total time, including fighting and judging",
									$exchBinSize ,$exchWindowSize)?>



		<?php if($tLength['exch']['fight']['avg'] > 1):?>


			<?=displayMatchLengthPlot("Fighting Time [sec]",
										$tLength['exch']['fight'],
										"Time between FIGHT and HOLD",
										$exchBinSize ,$exchWindowSize)?>

			<?=displayMatchLengthPlot("Judging Time [sec]",
										$tLength['exch']['judge'],
										"Time between HOLD and FIGHT",
										$exchBinSize ,$exchWindowSize)?>
		<?php else: ?>
			<HR>
			<div class='callout warning'>
				The match timers in HEMA Scorecard were not used consistently in this tournament, therefore Fighting and Judging time breakdowns are not available.
			</div>
			
		<?php endif ?>
	<?php endif ?>


<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayMatchLengthPlot($dataName, $histogramData, $toolText, $binWidth = null, $plotWidth = null){
	static $plotNum = 0;
	$plotNum++;
?>
	<HR>
	<h5>
		<?=$dataName?>
		(Average: <?=$histogramData['avg']?>)
		<?=tooltip($toolText)?>
	</h5>
	<?=plotHistogram($histogramData['data'],$plotNum,$dataName, $binWidth, $plotWidth)?>
	
<?php
}



/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
