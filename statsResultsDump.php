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
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['STATS_EVENT'] == false){
	pageError('user');
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

	$eventExportClass = '';
	$eventExportErrText = '';
	if(areAllTournamentsFinalized($_SESSION['eventID']) == false){
		$eventExportClass = "alert hollow";
		$eventExportErrText = "<em>Unfinalized Tournaments.</em> ";
	}
	if(hemaRatings_isEventInfoComplete($_SESSION['eventID']) == false){
		$eventExportClass = "alert hollow";
		$eventExportErrText = "<em>Form incomplete</em> ";
	}
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>	
	<?php if(ALLOW['VIEW_EMAIL']):?>
		<strong>Event Contact Information: </strong>
		<a href='mailto:<?=$email?>'><?=$email?></a>
	<?php endif ?>
	<fieldset class='fieldset'>
	<legend><h4>HEMA Ratings Format</h4></legend>
	<form method='POST'>
	<input type='hidden' name='formName' value='hemaRatings_ExportCsv'>
	
<!-- Export All -->
	<!--<button class='button success' name='HemaRatingsExport' value='all'>
		Export All
	</button>
	<?=tooltip("This will export the roster and all tournaments which are:
	<li>Finalized</li><li>Versus Match format</li>")?>
	<BR>-->

<!-- Export roster -->
	<button class='button <?=$eventExportClass?>' name='HemaRatingsExport' value='eventInfo'>
		Export Event Information
	</button>
	<?=$eventExportErrText?>


	<BR>

	<button class='button' name='HemaRatingsExport' value='roster'>
		Export Roster
	</button>
	<?php if(ALLOW['STATS_ALL'] == true): ?>
		<i> - Remember to return any HEMA Ratings IDs not on this list!
	<?php endif ?>

	<BR><BR>

<!-- Export tournaments -->
	<?php foreach((array)$tournamentList as $tournamentID => $tournament):
		$name = getTournamentName($tournamentID);
		$class = '';
			$warning = null;


		if(!isFinalized($tournamentID)){
			$class = 'secondary';
			$warning .= '<em> - Tournament not finalized</em>';
		}

		if(isTournamentPrivate($tournamentID)){
			$class = 'alert';
			$warning = '<em> - Request for private results</em>'.$warning;
		} 
		
		
		?>
		<button class='button hollow <?=$class?>' name='HemaRatingsExport' value='<?=$tournamentID?>'>
			Export <?=$name?>
		</button>
		<?=$warning?>
		<BR>
	<?php endforeach ?>

	</form>

	</fieldset>


<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////


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
