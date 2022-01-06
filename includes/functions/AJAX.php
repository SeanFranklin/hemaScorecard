<?php
/*******************************************************************************
	AJAX Functions
	
	Database queries requested by Javascrip.
	Sorted into a giant select case based on the value passed through $_REQUEST['mode']
	
*******************************************************************************/

define('BASE_URL' , $_SERVER['DOCUMENT_ROOT'].'/');
include_once(BASE_URL.'includes/config.php');

// SWITCH CASE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

switch ($_REQUEST['mode']){
	
/******************************************************************************/

case 'updateSession': {

	$acceptedIndexes = array('tournamentID','groupID','matchID','groupSet',
		'dayNum','shiftIndex');

	$index = $_REQUEST['index'];
	if(in_array($index, $acceptedIndexes)){
		$_SESSION[$index] = $_REQUEST['value'];
		echo 'Success';
	} else {
		echo 'Invalid Index';
	}

} break;

/******************************************************************************/	

case 'getSessionDayNum':{

	echo json_encode($_SESSION['dayNum']);

}

/******************************************************************************/	

case 'hasFought': {
// Returns null if no exchanges have been recorded that meet the selection 
// criteria, or 'HAS FOUGHT' if there is an exchange that matches.
// Table join structure is dynamicaly changed depending on what the calling 
// function wants to know about. ie. Has fought in the event? Has fought in a pool?

	if(isset($_REQUEST['rosterID'])){
		$rosterID = (int)$_REQUEST['rosterID'];
	} else {
		$rosterID = null;
	}

	if(isset($_REQUEST['matchID'])){
		$matchID = (int)$_REQUEST['matchID'];
	} else {
		$matchID = null;
	}

	if(isset($_REQUEST['groupID'])){
		$groupID  = (int)$_REQUEST['groupID'];
	} else {
		$groupID  = null;
	}

	if(isset($_REQUEST['tournamentID'])){
		$tournamentID  = (int)$_REQUEST['tournamentID'];
	} else {
		$tournamentID  = null;
	}

	if(isset($_REQUEST['eventID'])){
		$eventID  = (int)$_REQUEST['eventID'];
	} else {
		$eventID  = null;
	}

	$where = [];
	if($rosterID !== null){
		$where[] = "(scoringID = {$rosterID} OR receivingID = {$rosterID}) ";
	}
	if($matchID !== null){
		$where[] = "eventExchanges.matchID = {$matchID} ";
	}
	if($groupID !== null){
		$where[] = "eventMatches.groupID = {$groupID} ";
		$joinLevel = 1;
	}
	if($tournamentID !== null){
		$where[] = "eventGroups.tournamentID = {$tournamentID} ";
		$joinLevel = 2;
	}
	if($eventID !== null){
		$where[] = "eventTournaments.eventID = {$eventID} ";
		$joinLevel = 3;
	}

	if($where == null){ return; }

	$joinString = '';
	if($joinLevel >= 1){
		$joinString .= "INNER JOIN eventMatches USING(matchID) ";
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
	
	if(isset($result) && $result != null){
		echo "HAS FOUGHT";
	}
} break;

/******************************************************************************/

case 'newExchange': {
// Checks if the exchange provided is the same as the most recent exchange in the
// database. Used in page auto-refreshing functions.

	$lastExchange = (int)$_REQUEST['exchangeID'];
	$matchID = (int)$_REQUEST['matchID'];
	
	if($matchID == null){return;}
	
	$sql = "SELECT MAX(exchangeID)
			FROM eventExchanges
			WHERE matchID = {$matchID}";	
	$newExchange = mysqlQuery($sql, SINGLE, 'MAX(exchangeID)');

	$ReturnValue['refresh'] = false;
	$ReturnValue['matchTime'] = null;


	if($newExchange != $lastExchange){
		$ReturnValue['refresh'] = true;
	} else {
		$sql = "SELECT matchTime
				FROM eventMatches
				WHERE matchID = {$matchID}";	
		$ReturnValue['matchTime'] = mysqlQuery($sql, SINGLE, 'matchTime');
	}	

	echo json_encode($ReturnValue);
} break;

/******************************************************************************/

case 'fighterInfo': {

	$rosterID = (int)$_REQUEST['rosterID'];
	$eventID = (int)$_REQUEST['eventID'];
	
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
	
	$temp = [];
	foreach((array)$result as $item){
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

	$formatID = (int)$_REQUEST['formatID'];

	$sql = "SELECT tournamentRankingID, name
			FROM systemRankings
			WHERE formatID = {$formatID}
			ORDER BY numberOfInstances DESC";
	$rankingTypes = mysqlQuery($sql, ASSOC);
	
	echo json_encode($rankingTypes);

} break;
 
/******************************************************************************/
 
case 'updateMatchTime': {

	$matchTime = (int)$_REQUEST['matchTime'];
	$matchID = (int)$_REQUEST['matchID'];

	$sql = "UPDATE eventMatches
			SET matchTime = {$matchTime}
			WHERE matchID = {$matchID}";
	mysqlQuery($sql, SEND);

	
} break;

/******************************************************************************/

case 'getScheduleBlockInfo':{

	$blockID = (int)$_REQUEST['blockID'];
	$info = logistics_getScheduleBlockInfo($blockID);

	if($info['blockTypeID'] == SCHEDULE_BLOCK_TOURNAMENT){
		$info['tournamentTitle'] = getTournamentName($info['tournamentID']);
	} else {
		$info['tournamentTitle'] = '';
	}
	$info['startTimeHr'] = min2hr($info['startTime']);
	$info['endTimeHr'] = min2hr($info['endTime']);

	$sql = "SELECT COUNT(*) AS numShifts
			FROM logisticsScheduleShifts
			WHERE blockID = {$blockID}
			GROUP BY locationID";
	$info['numShifts'] = mysqlQuery($sql, SINGLE,'numShifts');

	
	$info['instructors'] = logistics_getBlockInstructors($blockID);

	echo json_encode($info);

} break;

/******************************************************************************/

case 'getLivestreamMatch':{

	$eventID = (int)$_REQUEST['eventID'];
	$lastExchange = (int)$_REQUEST['lastExchange'];
	$matchID = (int)getLivestreamMatch($eventID);
	if($matchID == 0){
		return;
	}
	
	$matchInfo = getMatchInfo($matchID);

// If there has been no new exchanges it returns no data
	if($lastExchange == $matchInfo['lastExchange']){ 
		$returnInfo['matchTime'] = $matchInfo['matchTime'];
		echo(json_encode($returnInfo));
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

case 'isFightingStarted':{
	echo json_encode(isFightingStarted($tournamentID));
	return;

} break;

/******************************************************************************/
}


// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
