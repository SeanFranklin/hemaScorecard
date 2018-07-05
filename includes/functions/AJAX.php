<?php
/*******************************************************************************
	AJAX Functions
	
	Database queries requested by Javascrip.
	Sorted into a giant select case based on the value passed through $_REQUEST['mode']
	
*******************************************************************************/

define('BASE_URL' , $_SERVER['DOCUMENT_ROOT'].'/v6/');
include_once(BASE_URL.'includes/config.php');

// SWITCH CASE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

switch ($_REQUEST['mode']){
	
/******************************************************************************/	

case 'hasFought': {
// Returns null if no exchanges have been recorded that meet the selection 
// criteria, or 'HAS FOUGHT' if there is an exchange that matches.
// Table join structure is dynamicaly changed depending on what the calling 
// function wants to know about. ie. Has fought in the event? Has fought in a pool?

	$rosterID = $_REQUEST['rosterID'];
	$matchID = $_REQUEST['matchID'];
	$groupID = $_REQUEST['groupID'];
	$tournamentID = $_REQUEST['tournamentID'];
	$eventID = $_REQUEST['eventID'];
	
	if($rosterID != null){
		$where[] = "(scoringID = {$rosterID} OR recievingID = {$rosterID}) ";
	}
	if($matchID != null){
		$where[] = "eventExchanges.matchID = {$matchID} ";
	}
	if($groupID != null){
		$where[] = "eventMatches.groupID = {$groupID} ";
		$joinLevel = 1;
	}
	if($tournamentID != null){
		$where[] = "eventGroups.tournamentID = {$tournamentID} ";
		$joinLevel = 2;
	}
	if($eventID != null){
		$where[] = "eventTournaments.eventID = {$eventID} ";
		$joinLevel = 3;
	}
	
	if($where == null){ return; }

	if($joinLevel >= 1){
		$joinString = "INNER JOIN eventMatches USING(matchID) ";
	}
	if($joinLevel >= 2){
		$joinString .= "INNER JOIN eventGroups USING(groupID) ";
	}
	if($joinLevel >= 3){
		$joinString .= "INNER JOIN eventTournaments USING(tournamentID) ";
	}
	

	$isFirst = true;
	foreach($where as $string){
		if($isFirst){ 
			$whereString = "WHERE "; 
			$isFirst = false;
		} else {
			$whereString .= "AND ";
		}
		$whereString .= $string;
	}
	
	$sql = "SELECT exchangeID
			FROM eventExchanges
			$joinString
			$whereString";
	
	$result = mysqlQuery($sql, SINGLE, 'exchangeID');
	
	if(isset($result)){
		echo "HAS FOUGHT";
	}
} break;

/******************************************************************************/

case 'newExchange': {
// Checks if the exchange provided is the same as the most recent exchange in the
// database. Used in page auto-refreshing functions.

	$lastExchange = $_REQUEST['exchangeID'];
	$matchID = $_REQUEST['matchID'];
	
	if($matchID == null){return;}
	
	$sql = "SELECT MAX(exchangeID)
			FROM eventExchanges
			WHERE matchID = {$matchID}";	
	$newExchange = mysqlQuery($sql, SINGLE, 'MAX(exchangeID)');
	
	if($newExchange != $lastExchange){
		echo "REFRESH";
	}	

} break;

/******************************************************************************/

case 'fighterInfo': {

	$rosterID = $_REQUEST['rosterID'];
	$eventID = $_REQUEST['eventID'];
	
	$sql = "SELECT firstName, lastName, eventRoster.schoolID
			FROM eventRoster
			INNER JOIN systemRoster USING(systemRosterID)
			WHERE rosterID = {$rosterID}";
	$res = mysqlQuery($sql, SINGLE);
	
	$sql = "SELECT tournamentID 
			FROM eventTournamentRoster
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE rosterID = {$rosterID}
			AND eventID = {$eventID}";
			
	$result = mysqlQuery($sql, ASSOC);
	
	foreach($result as $item){
		$tournamentID = $item['tournamentID'];
		$temp[$tournamentID] = true;
	}

	$res['tournamentIDs'] = $temp;
	
	echo json_encode($res);
} break;

/******************************************************************************/

case 'getRankingTypes': {
// Returns the ranking algorithms that match the given elimination type.
// Used to auto-populate form entry fields for creating and editing tournaments.

	$elimID = $_REQUEST['elimID'];

	switch($elimID){
		case POOL_BRACKET:
			$where = "WHERE Pool_Bracket = 1";
			break;
		case POOL_SETS:
			$where = "WHERE Pool_Sets = 1";
			break;
		case SCORED_EVENT:
			$where = "WHERE Scored_Event = 1";
			break;
		default:
			return;
	}
	
	$sql = "SELECT tournamentRankingID, name
			FROM systemRankings
			{$where}
			ORDER BY numberOfInstances DESC";
	$rankingTypes = mysqlQuery($sql, KEY_SINGLES, 'tournamentRankingID', 'name');
	
	echo json_encode($rankingTypes);

} break;
 
/******************************************************************************/
 
case 'updateMatchTime': {

	$sql = "UPDATE eventMatches
			SET matchTime = {$_REQUEST['matchTime']}
			WHERE matchID = {$_REQUEST['matchID']}";
	mysqlQuery($sql, SEND);
	
} break;

/******************************************************************************/

case 'getLivestreamMatch':{

	$matchID = getLivestreamMatch($_REQUEST['eventID']);
	if($matchID == null){ return; }
	
	
	$matchInfo = getMatchInfo($matchID);

// If there has been no new exchanges it returns no data
	if($_REQUEST['lastExchange'] == $matchInfo['lastExchange']){ 
		$returnInfo['matchTime'] = $matchInfo['matchTime'];
		echo json_encode($returnInfo);
		return;
	}
		
	$returnInfo['tournamentName'] = getTournamentName($matchInfo['tournamentID']);
	
	if($matchInfo['matchType'] == 'pool'){
		$matchName .= "Pool Match";
	} elseif ($matchInfo['matchType'] == 'elim'){
		if($matchInfo['bracketLevel'] == 1){
			if($matchInfo['groupName'] == 'winner'){
				$matchName .= "1st Place Match";
			} if($matchInfo['groupName'] == 'loser') {
				$matchName .= "3rd Place Match";
			}
		} else {
			$matchName .= "Bracket Match";
		}
	} else { $matchName = "&nbsp;";}
	$returnInfo['matchName'] = $matchName;
	
// Fighter names
	if($matchInfo['fighter1ID'] != null){
		$returnInfo['fighter1Name'] = getFighterName($matchInfo['fighter1ID']);
		if($matchInfo['winnerID'] == $matchInfo['fighter1ID']){
			$returnInfo['winner'] = 1;
		}
	}else {
		$returnInfo['fighter1Name'] = '----';
	}
	if($matchInfo['fighter2ID'] != null){
		$returnInfo['fighter2Name'] = getFighterName($matchInfo['fighter2ID']);
		if($matchInfo['winnerID'] == $matchInfo['fighter2ID']){
			$returnInfo['winner'] = 2;
		}
	} else {
		$returnInfo['fighter2Name'] = '----';
	}

	
// Fighter Schools	
	$returnInfo['fighter1School'] = $matchInfo['fighter1School'];
	$returnInfo['fighter2School'] = $matchInfo['fighter2School'];
	
// Fighter Scores
	$returnInfo['fighter1Score'] = $matchInfo['fighter1score'];
	$returnInfo['fighter2Score'] = $matchInfo['fighter2score'];
	if($returnInfo['fighter1Score'] == ''){$returnInfo['fighter1Score'] = 'X';}
	if($returnInfo['fighter2Score'] == ''){$returnInfo['fighter2Score'] = 'X';}
	
	$returnInfo['doubles'] = getMatchDoubles($matchID);
	$returnInfo['matchTime'] = $matchInfo['matchTime'];

// Fighter Colors
	$sql = "SELECT colorCode
			FROM eventTournaments, systemColors
			WHERE eventTournaments.tournamentID = {$matchInfo['tournamentID']}
			AND color1ID = colorID";
	$returnInfo['color1Code'] = mysqlQuery($sql, SINGLE, 'colorCode');

	$sql = "SELECT colorCode
			FROM eventTournaments, systemColors
			WHERE tournamentID = {$matchInfo['tournamentID']}
			AND color2ID = colorID";
	$returnInfo['color2Code'] = mysqlQuery($sql, SINGLE, 'colorCode');
	
	
// Return last exchange information
	$returnInfo['lastExchange'] = $matchInfo['lastExchange'];
	$returnInfo['endType'] = $matchInfo['endType'];
	if($returnInfo['lastExchange'] == null){
		$returnInfo['lastExchange'] = 0;
		$returnInfo['exchangeType'] = ' ';
		$returnInfo['points'] = ' ';
	} else {
		$sql = "SELECT scoringID, exchangeType, scoreValue, scoreDeduction
				FROM eventExchanges
				WHERE exchangeID = (SELECT MAX(exchangeID)
									FROM eventExchanges
									WHERE matchID = {$matchID}
									AND exchangeType != 'winner'
									AND exchangeType != 'doubleOut')";
		$tmp = mysqlQuery($sql, SINGLE);
		
		$returnInfo['exchangeType'] = $tmp['exchangeType'];
		$returnInfo['points'] = $tmp['scoreValue'] - $tmp['scoreDeduction'];
		if($tmp['exchangeType'] == 'clean' || $tmp['exchangeType'] == 'afterblow' 
			|| $tmp['exchangeType'] == 'penalty' || $tmp['exchangeType'] == 'noQuality'){
			if($tmp['scoringID'] == $matchInfo['fighter1ID']){
				$returnInfo['lastColor'] = 1;
			} elseif($tmp['scoringID'] == $matchInfo['fighter2ID']){
				$returnInfo['lastColor'] = 2;
			}
		}
	}
	
	echo json_encode($returnInfo);
	return;
	
} break;

/******************************************************************************/
}


// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
