<?php
/*******************************************************************************
	Results Dump

	Exports the results and roster of a tournament in accordance with
	HEMA Scorecard standards.
	LOGIN:
		- SUPER ADMIN and above can use
		- Analytics user can use

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Export Tournament Results';
$hidePageTitle = true;
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} else {


	$tournamentList_unsorted = getTournamentsFull($_SESSION['eventID']);

	// Splits list into finalized tournaments first
	$finalizedTournaments = [];
	$unfinalizedTournaments = [];
	foreach($tournamentList_unsorted as $tournamentID => $tournament){
		if(isFinalized($tournamentID)){
			$finalizedTournaments[$tournamentID] = $tournament;
		} else {
			$unfinalizedTournaments[$tournamentID] = $tournament;
		}
	}

	$tournamentList = appendArray($finalizedTournaments, $unfinalizedTournaments);
	$email = getEventEmail($_SESSION['eventID']);


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>
	<?php if(ALLOW['VIEW_EMAIL']):?>
		<strong>Event Contact Information: </strong>
		<a href='mailto:<?=$email?>'><?=$email?></a>
		<HR>
	<?php endif ?>


	<?=HemaRatingExportOptions($tournamentList)?>

	<?=FerrotasRatingExportOptions($tournamentList)?>

	<div class='callout'>
	Do you have a different format you would like to be able to export results in? Contact the HEMA Scorecard team.
	</div>


<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/******************************************************************************/

function FerrotasRatingExportOptions($tournamentList){
?>
	<h4><a class="ferrotas-export" onclick="$('.ferrotas-export').toggle()">Ferrotas Ratings Format</a></h4>

	<fieldset class='fieldset ferrotas-export hidden'>

	<legend><h4><a onclick="$('.ferrotas-export').toggle()">Ferrotas Format</a></h4></legend>

	<p><i>This export assumes the tournament was pool(s) followed by a single elimination bracket.
		<BR>Other fomats will lead to unpredictable results.</i></p>

	<form method='POST'>

		<div class='grid-x grid-margin-x'>
		<input type='hidden' name='formName' value='ferrotas_ExportCsv'>

	<!-- Export tournaments -->
		<?php foreach((array)$tournamentList as $tournamentID => $tournament):
			$name = getTournamentName($tournamentID);
			$class = '';
				$warning = null;


			if(!isFinalized($tournamentID)){
				$class = 'secondary';
				$warning .= '<em> - Tournament not finalized</em><BR>';
			}

			if(isTournamentPrivate($tournamentID)){
				$class = 'alert';
				$warning = '<em> - Request for private results</em><BR>'.$warning;
			}


			?>

			<div class='large-4 medium-6 cell'>
			<button class='button hollow <?=$class?> expanded' name='FerrotasExportTournamentID' value='<?=$tournamentID?>'>
				Export <?=$name?>
			</button>
			<?=$warning?>
			</div>

		<?php endforeach ?>
		</div>

	</form>
<HR>
	<p>Ratings order:
	<ol>
		<li>Bracket placing.</li>
		<li>To break ties in the bracket (<i>eg:</i> everyone who was eliminated in top 8) the pool standings are used.</li>
		<li>Pool standings used for everyone not included in the elimination bracket.</li>
	</ol>

	</fieldset>
<?php
}


/******************************************************************************/

function HemaRatingExportOptions($tournamentList){
?>
	<h4><a class="hema-ratings-export" onclick="$('.hema-ratings-export').toggle()">HEMA Ratings Format</a></h4>

	<fieldset class='fieldset hema-ratings-export hidden'>

	<legend><h4><a onclick="$('.hema-ratings-export').toggle()">HEMA Ratings Format</a></h4></legend>

	<div class='callout warning'>
		Please submit these results to HEMA Ratings using the
		<a href='https://submit.hemaratings.com/' target="_blank">Ratings Submission Tool</a>.
	</div>

	<form method='POST'>

		<div class='grid-x grid-margin-x'>
		<input type='hidden' name='formName' value='hemaRatings_ExportCsv'>

	<!-- Export roster -->

		<div class='large-3 medium-5 cell'>
		<button class='button expanded' name='HemaRatingsExport' value='roster'>
			Export Roster
		</button>
		</div>


		<div class='large-12 cell'><HR></div>

	<!-- Export tournaments -->
		<?php foreach((array)$tournamentList as $tournamentID => $tournament):
			$name = getTournamentName($tournamentID);
			$class = '';
				$warning = null;


			if(!isFinalized($tournamentID)){
				$class = 'secondary';
				$warning .= '<em> - Tournament not finalized</em><BR>';
			}

			if(isTournamentPrivate($tournamentID)){
				$class = 'alert';
				$warning = '<em> - Request for private results</em><BR>'.$warning;
			}


			?>

			<div class='large-4 medium-6 cell'>
			<button class='button hollow <?=$class?> expanded' name='HemaRatingsExport' value='<?=$tournamentID?>'>
				Export <?=$name?>
			</button>
			<?=$warning?>
			</div>

		<?php endforeach ?>
		</div>

	</form>

	</fieldset>
<?php
}


/******************************************************************************/



/******************************************************************************

	----------------------------------------------------------
	-- 	Used as a hack to export results from a tournament	--
	-- 	where each exchange was a different weapon 			--
	----------------------------------------------------------

function exportTournament_SingleExchange($tournamentID){
	// Exports the result of the winner of a single exchange per match
	// ie EXCHANGE_NUM = 2 exports the winner of the second exchange
	// as the match winner.

	define("EXCHANGE_NUM",2);

	if($tournamentID == null){
		echo "<BR>Error in exportTournament(): No Tournament Loaded<BR>";
		return;
	}

	$sql = "SELECT exchangeType, scoringID, receivingID, matchID
			FROM eventGroups
			INNER JOIN eventMatches ON eventGroups.groupID = eventMatches.groupID
			INNER JOIN eventExchanges USING(matchID)
			WHERE tournamentID = {$tournamentID}";
	$matchData = mysqlQuery($sql, ASSOC);

	$tournamentName = getTournamentName($tournamentID);

	for($useExchange = 1; $useExchange<=EXCHANGE_NUM; $useExchange++){

		$matchID = null;
		$exchangeNum = 1;

		foreach($matchData as $index => $data){
			if($matchID == $data['matchID']){
				$exchangeNum++;
			} else {
				$exchangeNum = 1;
				$matchID = $data['matchID'];
			}

			if($exchangeNum == $useExchange){
				$wantedExchanges[$matchID] = $data;
			}
			$matchData[$index]['num'] = $exchangeNum;
		}

		$fp = fopen("exports/{$tournamentName}- {$useExchange}.csv", 'w');

		foreach($wantedExchanges as $match){
			$f1ID = $match['scoringID'];
			$f2ID = $match['receivingID'];
			$winID = $match['scoringID'];
			$matchID = $match['matchID'];

			$fighter1 = getFighterName($f1ID);
			$fighter2 = getFighterName($f2ID);
			$f1Result = 'Loss';
			$f2Result = 'Loss';

			echo "$fighter1 vs $fighter2 - {$match['exchangeType']}<BR>";
			if($match['exchangeType'] == 'clean' or $match['exchangeType'] == 'afterblow'){
				$f1Result = 'Win';
			}

			$fields = [$fighter1, $fighter2, $f1Result, $f2Result];
			$numFields = 4;

			//fputcsv($fp, $fields);
			$comma = ',';

			foreach($fields as $index => $field){
				if ($index == $numFields-1){
					$comma = null;
				}
				fputs($fp, $field.$comma);
			}
			fputs($fp, PHP_EOL);

		}
		fclose($fp);

	}
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
