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

case 'postForm': {

	if(isset($_REQUEST['functionName']) == ''){
		break;
	}

	$success = false;

	switch($_REQUEST['functionName']){
		case 'checkInFighter':
			call_user_func($_REQUEST['functionName'], $_REQUEST);
			$success = true;
			break;
		case 'logisticsStaffFromRoster':
			call_user_func($_REQUEST['functionName'], $_REQUEST);
			$success = true;
			break;
		default:
			// Not a valid function
			break;
	}

	echo json_encode($success);

} break;
	
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

case 'updateStream': {

	$_SESSION['stream']['mode'] = (int)@$_REQUEST['streamMode'];
	$_SESSION['stream']['matchID'] = (int)@$_REQUEST['streamMatchID'];
	$_SESSION['stream']['locationID'] = (int)@$_REQUEST['streamLocationID'];

	echo json_encode($_SESSION['stream']);

} break;

/******************************************************************************/	

case 'getSessionDayNum':{

	echo json_encode($_SESSION['dayNum']);

} break;

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
			ORDER BY name ASC";
	$rankingTypes = mysqlQuery($sql, ASSOC);

	if($formatID == FORMAT_MATCH){
		$sql = "SELECT tournamentRankingID, name
				FROM systemRankings
				WHERE formatID = {$formatID}
				ORDER BY numberOfInstances DESC
				LIMIT 10";
		$rankingTypes['popular'] = mysqlQuery($sql, ASSOC);
	} else {
		$rankingTypes['popular'] = [];
	}

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

	$info['locationNames'] = [];
	if(isset($info['locationIDs']) == true && is_array($info['locationIDs']) == true){
		foreach($info['locationIDs'] as $locationID){
			$info['locationNames'][] = logistics_getLocationName($locationID);
		}
	}


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

	$info['rules'] = getTournamentRules($info['tournamentID']);

	$sql = "SELECT rosterID, locationName, lSS.startTime, lSS.endTime, roleName 
			FROM logisticsStaffShifts
			INNER JOIN logisticsScheduleShifts AS lSS USING(shiftID)
			INNER JOIN logisticsScheduleBlocks USING(blockID)
			INNER JOIN logisticsLocations USING(locationID)
			INNER JOIN systemLogisticsRoles USING(logisticsRoleID)
			WHERE blockID = {$blockID}
			ORDER BY lSS.startTime ASC, locationName ASC";
	$info['staffing'] = (array)mysqlQuery($sql, ASSOC);

	foreach($info['staffing'] as $index => $staff){
		$info['staffing'][$index]['name'] = getFighterName($staff['rosterID']);
	}

	echo json_encode($info);

} break;

/******************************************************************************/

case 'getStreamOverlayInfo':{

	
	$streamMode = (int)$_REQUEST['streamMode'];
	//$_REQUEST['identifier'] processed below
	$lastExchangeInPlayer = (int)$_REQUEST['lastExchange'];
	$videoTime = (int)$_REQUEST['videoTime'];
	$timeOfFirstCall = (int)$_REQUEST['synchTime'];
	$timeOfMatchStart = (int)$_REQUEST['synchTime2'];

	switch($streamMode){
		case VIDEO_STREAM_MATCH:
		case VIDEO_STREAM_VIRTUAL:
		{
			$matchID = (int)$_REQUEST['identifier'];
			break;
		}
		case VIDEO_STREAM_LOCATION:
		{
			$streamInfo = getStreamForLocation($_REQUEST['identifier']);
			$matchID = (int)$streamInfo['matchID'];
			break;
		}
		default: { return; }
	}

	if($matchID == 0){
		return;
	}

	$matchInfo = getMatchInfo($matchID);

	if($streamMode != VIDEO_STREAM_VIRTUAL){
		$lastExchangeID = $matchInfo['lastExchange'];
		$currentTimeClock = $matchInfo['matchTime'];
	} else {

		if($timeOfMatchStart >= $timeOfFirstCall){
			$sql = "SELECT exchangeTime
					FROM eventExchanges
					WHERE matchID = {$matchID}
					ORDER BY exchangeNumber ASC
					LIMIT 1";
			$firstExchangeTimeClock = (int)mysqlQuery($sql, SINGLE, 'exchangeTime');
			$timeOfFirstCall = $timeOfMatchStart + $firstExchangeTimeClock;
			$returnInfo['timeOfFirstCall'] = $timeOfFirstCall;
		}

		$currentTimeReal = $videoTime - $timeOfFirstCall;

		$sql = "SELECT (UNIX_TIMESTAMP(timestamp)) AS firstCallTimeAbs
				FROM eventExchanges
				WHERE matchID = {$matchID}
				ORDER BY exchangeNumber ASC
				LIMIT 1";
		$firstCallTimeAbs = (int)mysqlQuery($sql, SINGLE, 'firstCallTimeAbs');
		$currentTimeAbs = $firstCallTimeAbs + $currentTimeReal;
		
		$sql = "SELECT exchangeID, (UNIX_TIMESTAMP(timestamp) - {$firstCallTimeAbs}) AS exchangeTimeReal, 
					exchangeTime AS exchangeTimeClock
				FROM eventExchanges
				WHERE matchID = {$matchID}
				AND UNIX_TIMESTAMP(timestamp) > {$currentTimeAbs}
				ORDER BY UNIX_TIMESTAMP(timestamp) ASC
				LIMIT 1";
		$nextExchange = mysqlQuery($sql, SINGLE);

		$sql = "SELECT exchangeID, (UNIX_TIMESTAMP(timestamp) - {$firstCallTimeAbs}) AS exchangeTimeReal, 
					exchangeTime AS exchangeTimeClock
				FROM eventExchanges
				WHERE matchID = {$matchID}
				AND UNIX_TIMESTAMP(timestamp) <= {$currentTimeAbs}
				ORDER BY UNIX_TIMESTAMP(timestamp) DESC
				LIMIT 1";
		$lastExchange = (array)mysqlQuery($sql, SINGLE);

		if($lastExchange == []){
			$lastExchangeID = 0;
			$currentTimeClock = $videoTime - $timeOfMatchStart;
			if($currentTimeClock < 0){
				$currentTimeClock = 0;
			}
		} else {
			$lastExchangeID = $lastExchange['exchangeID'];

			$timeSinceLastExchange = $currentTimeReal - $lastExchange['exchangeTimeReal'];

			$currentTimeClock = $lastExchange['exchangeTimeClock'] + $timeSinceLastExchange;
		}

		if($nextExchange == []){
			$lastExchangeID = $matchInfo['lastExchange'];
			$currentTimeClock = $matchInfo['matchTime'];
			$streamMode == VIDEO_STREAM_MATCH;
		} elseif($currentTimeClock > $nextExchange['exchangeTimeClock']){
			$currentTimeClock = $nextExchange['exchangeTimeClock'];
		}
		
	}

	$returnInfo['lastExchange'] = $lastExchangeID;
	$returnInfo['matchTime'] = $currentTimeClock;

// If there has been no new exchanges it returns no data
	if($lastExchangeInPlayer == $lastExchangeID){ 
		echo(json_encode($returnInfo));
		return;
	}
		
// Fighter Scores

	if($streamMode == VIDEO_STREAM_VIRTUAL){
		// Can't use the match info for scores and winners, because it reflects 
		// the final score and not the 'current' score based on the video time.
		$fighter1ID = (int)$matchInfo['fighter1ID'];
		$sql = "SELECT (SUM(scoreValue) - SUM(scoreDeduction)) AS fighter1score
				FROM eventExchanges
				WHERE matchID = {$matchID}
				AND UNIX_TIMESTAMP(timestamp) <= {$currentTimeAbs}
				AND scoringID = {$fighter1ID} ";
		$matchInfo['fighter1score'] = (int)mysqlQuery($sql, SINGLE,'fighter1score');

		$fighter2ID = (int)$matchInfo['fighter2ID'];
		$sql = "SELECT (SUM(scoreValue) - SUM(scoreDeduction)) AS fighter2score
				FROM eventExchanges
				WHERE matchID = {$matchID}
				AND UNIX_TIMESTAMP(timestamp) <= {$currentTimeAbs}
				AND scoringID = {$fighter2ID} ";
		$matchInfo['fighter2score'] = (int)mysqlQuery($sql, SINGLE,'fighter2score');

	}

	$returnInfo['fighter1Score'] = $matchInfo['fighter1score'];
	$returnInfo['fighter2Score'] = $matchInfo['fighter2score'];
	if($returnInfo['fighter1Score'] == ''){$returnInfo['fighter1Score'] = 'X';}
	if($returnInfo['fighter2Score'] == ''){$returnInfo['fighter2Score'] = 'X';}
	
// Meta information about the match and tournament
	$returnInfo['tournamentName'] = getTournamentName($matchInfo['tournamentID']);
	
	$matchName = '';
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
	} else {
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

	$returnInfo['doubles'] = getMatchDoubles($matchID);

// Fighter Schools	
	$returnInfo['fighter1School'] = $matchInfo['fighter1School'];
	$returnInfo['fighter2School'] = $matchInfo['fighter2School'];

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

	$returnInfo['endType'] = $matchInfo['endType'];
	if($returnInfo['lastExchange'] == null){

		$returnInfo['lastExchange'] = 0;
		$returnInfo['exchangeType'] = ' ';
		$returnInfo['points'] = ' ';

		if($streamMode == VIDEO_STREAM_VIRTUAL){
			$returnInfo['endType'] = null;
			$returnInfo['winner'] = null;
		}

	} else {
		$lastExchangeID = (int)$returnInfo['lastExchange'];
		$sql = "SELECT scoringID, exchangeType, scoreValue, scoreDeduction
				FROM eventExchanges
				WHERE exchangeID <= {$lastExchangeID}
				AND matchID = {$matchID}
				AND exchangeType NOT IN ('winner','doubleOut')
				ORDER BY exchangeID DESC
				LIMIT 1";
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

		if($streamMode == VIDEO_STREAM_VIRTUAL){
			$sql = "SELECT COUNT(*) AS isFinished
					FROM eventExchanges
					WHERE exchangeID <= {$lastExchangeID}
					AND matchID = {$matchID}
					AND exchangeType IN ('winner','doubleOut','tie')
					ORDER BY exchangeID DESC
					LIMIT 1";
			$isFinished = (bool)(int)mysqlQuery($sql, SINGLE,'isFinished');

			if($isFinished == false){
				$returnInfo['endType'] = null;
				$returnInfo['winner'] = null;
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

case 'getEventTournaments':{

	$eventID = (int)$_REQUEST['eventID'];
	$tournamentIDs = getEventTournaments($eventID);

	$tournaments = [];
	foreach($tournamentIDs as $tournamentID){
		$a['tournamentID'] = (int)$tournamentID;
		$a['name'] = getTournamentName($a['tournamentID']);
		$tournaments[] = $a;
	}

	echo json_encode($tournaments);
	return;

} break;

/******************************************************************************/

case 'fighterSystemInfo': {

	$systemRosterID = (int)$_REQUEST['systemRosterID'];

	$sql = "SELECT systemRosterID, firstName, lastName, schoolID, HemaRatingsID
			FROM systemRoster
			WHERE systemRosterID = {$systemRosterID}";

	$res = mysqlQuery($sql, SINGLE);
	echo json_encode($res);
} break;

/******************************************************************************/

case 'getCheckInList': {

	$listType = $_REQUEST['listType'];

	switch($listType){
		case 'event': {
			$eventID = (int)$_REQUEST['ID'];
			$res['event'] = (array)getCheckInStatusEvent($eventID);
			$res['additional'] = (array)getCheckInStatusAdditional($eventID);
			break;
		}
		case 'tournament':{
			$tournamentID = (int)$_REQUEST['ID'];
			$res = (array)getTournamentFighters($tournamentID);
			break;
		}
		default: {$res = []; break;}
	}

	echo json_encode($res);

} break;


/******************************************************************************/
}


// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
