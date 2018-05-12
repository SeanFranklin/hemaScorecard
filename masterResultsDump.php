<?php
/*******************************************************************************
	Results Dump
	
	Exports the results and roster of a tournament in accordance with
	HEMA Scorecard standards.
	Double bad, contains both buisness logic and database queries
	LOGIN:
		- SUPER ADMIN and above can use
		
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'HEMA Rankings Exporter';
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(USER_TYPE < USER_SUPER_ADMIN && USER_TYPE != USER_STATS){
	pageError('user');
} else {
	$tournamentList_unsorted = getTournamentsFull();
	
	if(isset($_SESSION['resultsDump']['rosterDump'])){
		exportRoster();
		displayAnyErrors("Roster Exported");
	}
	
	if(isset($_SESSION['resultsDump']['tournamentDump'])){
		$tournamentID = $_SESSION['resultsDump']['tournamentDump'];
		$name = getTournamentName($tournamentID);
		$finalized = 
		
		
		exportTournament($tournamentID);
		
		$confirmMsg = "\"{$name}\" Tournament Exported";
		
		if(!isFinalized($tournamentID)){
			$confirmMsg .= "<BR><span class='red-text'>This tournament was not finalized.</span> Results may not be reliable.";
		}
		displayAnyErrors($confirmMsg);
	}
	unset($_SESSION['resultsDump']);
	
	// Splits list into finalized tournaments first
	foreach($tournamentList_unsorted as $tournamentID => $tournament){
		if(isFinalized($tournamentID)){
			$t1[$tournamentID] = $tournament;
		} else {
			$t2[$tournamentID] = $tournament;
		}
	}
	
	$tournamentList = appendArray($t1, $t2);
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>	

	<form method='POST'>
	<input type='hidden' name='formName' value='resultsDump'>
	
	<button class='button' name='rosterDump' value='true'>
		Export Roster
	</button>
	
	<BR><BR>

	<?php foreach((array)$tournamentList as $tournamentID => $tournament):
		$name = getTournamentName($tournamentID);
		if(isFinalized($tournamentID)){
			$class = '';
			$warning = null;
		} else {
			$class = 'secondary';
			$warning = '<em> - Tournament not finalized</em>';
		}
		
		
		?>
		<button class='button hollow <?=$class?>' name='tournamentDump' value='<?=$tournamentID?>'>
			Export <?=$name?>
		</button>
		<?=$warning?>
		<BR>
	<?php endforeach ?>

	</form>

<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function exportRoster(){
	
	$eventRoster = getEventRosterForExport($_SESSION['eventID']);
	$eventName = getEventName($_SESSION['eventID']);
	
	$fp = fopen("exports/{$eventName} - fighterRoster.csv", 'w');

	foreach ($eventRoster as $fields) {

		foreach($fields as $index => $field){
			if($index == 'systemRosterID'){
				$field = getFighterNameSystem($field, 'first');
			}
			fputs($fp, $field.",");
		}
		fputs($fp, '');
		
		fputs($fp, PHP_EOL);
	}
	fclose($fp);
	
	echo "<script>
	setTimeout(function () { window.location = 'exports/{$eventName} - fighterRoster.csv'; }, 300)
	</script>";
	
}

/******************************************************************************/

function getMatchStageName($matchID){
	
	$sql = "SELECT groupType, groupSet, groupName, bracketLevel, groupID, tournamentID
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE matchID = {$matchID}";
	$groupInfo = mysqlQuery($sql, SINGLE);
	
	if($groupInfo['groupType'] == 'pool'){
		return getSetName($groupInfo['groupSet'], $groupInfo['tournamentID']);
	}
	if($groupInfo['groupType'] == 'elim'){
		if($groupInfo['groupName'] == 'winner'){
			switch($groupInfo['bracketLevel']){
				case 1:
					return 'Gold Medal Match';
					break;
				case 2:
					return 'Semifinals';
					break;
				case 3:
					return 'Quarterfinals';
					break;
				case 4:
					return 'Eighth-Finals';
					break;
				default:
					return 'Elimination Bracket';
					break;
			}
			
		} elseif ($groupInfo['groupName'] == 'loser'){
			switch($groupInfo['bracketLevel']){
				case 1:
					return 'Bronze Medal Match';
					break;
				case 2:
					return 'Consolation Semifinals';
					break;
				default:
					return 'Consolation Bracket';
					break;
			}
		}
	}
	
}

/******************************************************************************/

function exportTournament($tournamentID){

	if($tournamentID == null){
		echo "<BR>Error in exportTournament(): No Tournament Loaded<BR>";
		return;
	}
	
	$sql = "SELECT scoringID, recievingID, exchangeType, matchID
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND (exchangeType = 'winner' OR exchangeType = 'doubleOut' OR exchangeType = 'tie')";
	$finishedMatches = mysqlQuery($sql, ASSOC);
	
	$tournamentName = getTournamentName($tournamentID);
	$eventName = getEventName($_SESSION['eventID']);
	
	$fp = fopen("exports/{$eventName} - {$tournamentName}.csv", 'w');
	
	foreach($finishedMatches as $match){
		
		
		$f1ID = $match['scoringID'];
		$f2ID = $match['recievingID'];
		$type = $match['exchangeType'];
		$stageName = getMatchStageName($match['matchID']);

		$fighter1 = getFighterName($f1ID, null, 'first');
		$fighter2 = getFighterName($f2ID, null, 'first');
		$f1Result = 'Loss';
		$f2Result = 'Loss';
		
		if($type == 'winner'){$f1Result = 'Win';}
		if($type == 'tie'){
			$f1Result = 'Draw';
			$f2Result = 'Draw';
		}
		
		$fields = [$fighter1, $fighter2, $f1Result, $f2Result, $stageName];
		
		$comma = ',';
		
		foreach($fields as $index => $field){
			if ($index == sizeof($fields)-1){
				$comma = null;
			}
			fputs($fp, $field.$comma);
		}
		fputs($fp, PHP_EOL);
		
	}
	fclose($fp);
	
	echo "<script>
	setTimeout(function () { window.location = 'exports/{$eventName} - {$tournamentName}.csv'; }, 300)
	</script>";
	
}

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
	
	$sql = "SELECT exchangeType, scoringID, recievingID, matchID
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
			$f2ID = $match['recievingID'];
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
