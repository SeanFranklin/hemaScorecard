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
		<HR>
	<?php endif ?>


	<h4><a class="hema-ratings-export" onclick="$('.hema-ratings-export').toggle()">HEMA Ratings Format</a></h4>
		
	<fieldset class='fieldset hema-ratings-export hidden'>

	<legend><h4><a onclick="$('.hema-ratings-export').toggle()">HEMA Ratings Format</a></h4></legend>

	<form method='POST'>

		<div class='grid-x grid-margin-x'>
		<input type='hidden' name='formName' value='hemaRatings_ExportCsv'>
		
	<!-- Export roster -->
		<?php if(ALLOW['VIEW_EMAIL']):?>
			<div class='large-3 medium-5 cell'>
			<button class='button <?=$eventExportClass?> expanded' name='HemaRatingsExport' value='eventInfo'>
				Export Event Information
			</button>
			</div>
		<?php endif ?>

		<?=$eventExportErrText?>


		<div class='large-3 medium-5 cell'>
		<button class='button expanded' name='HemaRatingsExport' value='roster'>
			Export Roster
		</button>
		</div>
		<?php if(ALLOW['STATS_ALL'] == true): ?>
			<div class='large-6 medium-12 cell'>
			<i> - Remember to return any HEMA Ratings IDs not on this list!</i>
			</div>
		<?php endif ?>
		

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
