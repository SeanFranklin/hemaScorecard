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

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

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

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

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
	$sql = "SELECT colorCode, contrastCode
			FROM eventTournaments, systemColors
			WHERE eventTournaments.tournamentID = {$matchInfo['tournamentID']}
			AND color1ID = colorID";
	$ret_val = mysqlQuery($sql, SINGLE);

	$returnInfo['color1Code'] = $ret_val['colorCode'];
	$returnInfo['color1Contrast'] = $ret_val['contrastCode'];

	$sql = "SELECT colorCode, contrastCode
			FROM eventTournaments, systemColors
			WHERE tournamentID = {$matchInfo['tournamentID']}
			AND color2ID = colorID";
	$ret_val = mysqlQuery($sql, SINGLE);

	$returnInfo['color2Code'] = $ret_val['colorCode'];
	$returnInfo['color2Contrast'] = $ret_val['contrastCode'];

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

case 'getBracketMatchesToAssignRings': {

	$tournamentID = (int)$_REQUEST['tournamentID'];

	$eventID = getTournamentEventID($tournamentID);

	if($tournamentID == 0 || $eventID == 0){
		return;
	}

	$upcomingMatches = (array)getBracketMatchesIncomplete($tournamentID);
	$ringsInfo = (array)logistics_getEventLocations($eventID,'ring');

	$ret_val['queue'] = $upcomingMatches[0];
	unset($upcomingMatches[0]);
	$ret_val['assigned'] = $upcomingMatches;

	$avalibleRings = [];

	foreach($ringsInfo as $ring){
		if($ring['hasMatches'] == true){

			unset($tmp);

			$tmp['locationID'] = (int)$ring['locationID'];

			if($ring['locationNameShort'] != null){
				$tmp['locationName'] = $ring['locationNameShort'];
			} else {
				$tmp['locationName'] = $ring['locationName'];
			}

			if(isset($upcomingMatches[$tmp['locationID']])){
				$tmp['numMatches'] = (int)count($upcomingMatches[$tmp['locationID']]);
			} else {
				$tmp['numMatches'] = 0;
			}


			$avalibleRings[] = $tmp;
		}
	}

	$ret_val['rings'] = $avalibleRings;

	echo json_encode($ret_val);

} break;

/******************************************************************************/

case 'assignBracketMatchesToRings': {

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

	$locationID = (int)$_REQUEST['locationID'];
	$matchIDs = (array)json_decode($_REQUEST['matchIDs']);

	if($locationID == 0){
		return;
	}



	foreach($matchIDs as $matchID){

		$matchID = (int)$matchID;

		$sql = "SELECT locationID
				FROM logisticsLocationsMatches
				WHERE matchID = {$matchID}";
		$currentLocation = (int)mysqlQuery($sql, SINGLE, 'locationID');

		if($currentLocation == 0){
			$sql = "INSERT INTO logisticsLocationsMatches
					(locationID, matchID)
					VALUES
					({$locationID}, {$matchID})";
			mysqlQuery($sql, SEND);
		}

	}

	echo json_encode(0);

} break;

/******************************************************************************/

case 'divisionSeedingInfo': {

	$divisionID = (int)$_REQUEST['divisionID'];
	$divisionItems = (array)getTournamentDivisionItems($divisionID);

	$sql = "SELECT tournamentID, numParticipants AS sizeBefore
			FROM eventTournamentDivItems
			INNER JOIN eventTournaments USING(tournamentID)
			LEFT JOIN eventTournamentOrder USING(tournamentID)
			WHERE divisionID = {$divisionID}
			ORDER BY sortOrder ASC";

	$items = (array)mysqlQuery($sql, ASSOC);

	foreach($items as $i => $item){
		$items[$i]['name'] = getTournamentName($item['tournamentID']);
	}

	echo json_encode($items);

} break;

/******************************************************************************/

/******************************************************************************/

case 'tournamentRatings': {

	$tournamentID = (int)$_REQUEST['tournamentID'];
	$retVal['fighters'] = (array)getTournamentFighters($tournamentID, 'rating');
	$retVal['defaultRating'] = 1;

	if(isset($retVal['fighters'][2]) == true){
		$minRating = $retVal['fighters'][0]['rating'];
		$maxRating = $retVal['fighters'][0]['rating'];
		$numRated = 0;

		foreach($retVal['fighters'] as $i => $f){

			$rating = (int)$f['rating'];

			if($rating != 0){

				$numRated++;

				if($rating < $minRating){
					$minRating = $rating;
				}

			} else {
				$retVal['defaultRating'] = calculateRatingForUnrated($maxRating, $minRating, $numRated);
				break;
			}
		}

	}

	echo json_encode($retVal);

} break;

/******************************************************************************/

case 'penaltyEscalation': {

	$infractionID = (int)$_REQUEST['infractionID'];
	$fighterNum = (int)$_REQUEST['fighterNum'];
	$matchID = (int)$_REQUEST['matchID'];

	$matchInfo = getMatchInfo($matchID);
	$tournamentID = (int)$matchInfo['tournamentID'];
	$eventID = (int)getTournamentEventID($tournamentID);


	$sql = "SELECT isNonSafety
			FROM eventPenaltyDisabled
			WHERE eventID = {$eventID}
			AND attackID = {$infractionID}";
	$isNonSafety = (bool)mysqlQuery($sql, SINGLE, 'isNonSafety');


	if($isNonSafety == false){

		if($fighterNum == 1){
			$rosterID = $matchInfo['fighter1ID'];
		} else if($fighterNum == 2) {
			$rosterID = $matchInfo['fighter2ID'];
		} else {
			$rosterID = 0;
		}

		if(isTeams($tournamentID) == true && getTournamentLogic($tournamentID) == null){
			$teamMemberID = (int)getActiveTeamMembersAtExchange($matchID, $rosterID, 0);
		} else {
			$teamMemberID = 0;
		}


		$sql = "SELECT matchID, refTarget AS infractionID, exchangeID
				FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				WHERE exchangeType = 'penalty'
				AND refType IS NOT NULL
				AND scoringID = {$rosterID}
				AND (tournamentID = {$tournamentID})
				AND (	matchID = {$matchID}
						OR
						refTarget = {$infractionID})";

		$penaltiesInTournament = (array)mysqlQuery($sql, ASSOC);

		$numInTournament = 0;
		$numInMatch = 0;


		foreach($penaltiesInTournament as $p){

			if($teamMemberID != 0){
				$teamMemberIDAtPenalty = (int)getActiveTeamMembersAtExchange($p['matchID'], $rosterID, $p['exchangeID']);

				if($teamMemberID != $teamMemberIDAtPenalty){
					continue;
				}
			}


			if($p['matchID'] == $matchID){
				$numInMatch++;
			}

			if($p['infractionID'] == $infractionID){
				$numInTournament++;
			}
		}

		if($numInMatch == 0 && $numInTournament == 0){
			$mode = 'tournament';
			$numPrior = 0;
		} else if($numInMatch >= $numInTournament){
			$mode = 'match';
			$numPrior = $numInMatch;
		} else {
			$mode = 'tournament';
			$numPrior = $numInTournament;
		}


		if($numPrior >= 2) {
			$colorID = PENALTY_CARD_BLACK;
		} else if($numPrior != 0) {
			$colorID = PENALTY_CARD_RED;
		} else {
			$colorID = PENALTY_CARD_YELLOW;
		}

	} else {

		$numPrior = 0;
		$colorID = (int)PENALTY_CARD_NONE;
		$mode = "";

	}


	$retVal['mode'] = $mode;
	$retVal['colorID'] = $colorID;
	$retVal['numPrior'] = $numPrior;
	$retVal['isNonSafety'] = $isNonSafety;
	$retVal['infractionID'] = $infractionID;

	echo json_encode($retVal);

} break;

/******************************************************************************/

case 'getDataForYear': {

	$year = (int)$_REQUEST['year'];
	$futureView = (boolean)(@$_SESSION['stats']['futureView']);

	switch($_REQUEST['dataType']){
		case 'events-by-country':	{$retVal = getAnnualEventsByCountry($year, $futureView); break;}
		case 'exchanges-by-country':{$retVal = getAnnualExchangesByCountry($year, $futureView); break;}
		case 'events-by-us-state':	{$retVal = getAnnualEventsByUsState($year, $futureView); break;}
		case 'exchanges-by-us-state':{$retVal = getAnnualExchangesByUsState($year, $futureView); break;}
		case 'events-by-month':		{$retVal = getAnnualEventsByMonth($year, $futureView); break;}
		case 'events-by-days':		{$retVal = getAnnualEventsByDays($year, $futureView); break;}
		case 'exchanges-by-event':	{$retVal = getAnnualExchangesByEvent($year, $futureView); break;}
		case 'matches-by-event':	{$retVal = getAnnualMatchesByEvent($year, $futureView); break;}
		case 'tournaments-by-event':{$retVal = getAnnualTournamentsByEvent($year, $futureView); break;}
		case 'womens-by-event':		{$retVal = getAnnualWomensByEvent($year, $futureView); break;}
		case 'exchanges-by-event-day':	{$retVal = getAnnualExchangesByEventDay($year, $futureView); break;}
		case 'entries-by-club':		{$retVal = getAnnualEntriesByClub($year, $futureView); break;}
		case 'matches-by-club':		{$retVal = getAnnualMatchesByClub($year, $futureView); break;}
		case 'exchanges-by-club':	{$retVal = getAnnualExchangesByClub($year, $futureView); break;}
		case 'wins-by-club':		{$retVal = getAnnualWinsByClub($year, $futureView); break;}
		case 'tournaments-by-weapon':{$retVal = getAnnualTournamentsByWeapon($year, $futureView); break;}
		case 'exchanges-by-weapon':	{$retVal = getAnnualExchangesByWeapon($year, $futureView); break;}
		case 'wtournaments-by-weapon':{$retVal = getAnnualWTournamentsByWeapon($year, $futureView); break;}
		case 'womens-by-weapon':	{$retVal = getAnnualWomensByWeapon($year, $futureView); break;}
		case 'exchanges-by-fighter':{$retVal = getAnnualExchangesByFighter($year, $futureView); break;}
		case 'close-by-fighter':	{$retVal = getAnnualCloseByFighter($year, $futureView); break;}
		case 'entries-by-fighter':	{$retVal = getAnnualEntriesByFighter($year, $futureView); break;}
		case 'events-by-fighter':	{$retVal = getAnnualEventsByFighter($year, $futureView); break;}
		case 'shutouts-by-fighter':	{$retVal = getAnnualShutoutsByFighter($year, $futureView); break;}
		case 'matches-by-fighter':	{$retVal = getAnnualMatchesByFighter($year, $futureView); break;}
		case 'exchanges-by-match':	{$retVal = getAnnualExchangesByMatch($year, $futureView); break;}
		case 'comebacks-by-match':	{$retVal = getAnnualComebacksByMatch($year, $futureView); break;}
		case 'rematches-by-fighter':{$retVal = getAnnualRematchesByFighter($year, $futureView); break;}
		case 'exchanges-by-judge':	{$retVal = getAnnualExchangesByJudge($year, $futureView); break;}
		case 'exchanges-by-director':	{$retVal = getAnnualExchangesByDirector($year, $futureView); break;}
		case 'matches-by-table':	{$retVal = getAnnualMatchesByTable($year, $futureView); break;}
		case 'matches-by-staff':	{$retVal = getAnnualMatchesByStaff($year, $futureView); break;}
		case 'exchanges-by-judge-school':	{$retVal = getAnnualExchangesByJudgeSchool($year, $futureView); break;}
		case 'exchanges-by-staff-school':	{$retVal = getAnnualExchangesByStaffSchool($year, $futureView); break;}
	}

	echo json_encode($retVal);

} break;

/******************************************************************************/

case 'updateExchange': {

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

	$exchangeID = (int)$_REQUEST['exchangeID'];
	$field = @$_REQUEST['field'];
	$value = (float)@$_REQUEST['value'];

	$retVal = [];
	$retVal['error'] = "";

	$sql = "SELECT matchID, tournamentID
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			WHERE exchangeID = {$exchangeID}";
	$ids = mysqlQuery($sql, SINGLE);

	if($ids == null){
		$retVal['error'] = "noData";
		echo json_encode($retVal );
		return;
	}

	$deductionList = getTournamentDeductions( $tournamentID);
	$deductionAdditionMode = readOption('T',$tournamentID,'DEDUCTION_ADDITION_MODE');

	$deductions = [];
	if($field == 'refPrefix' || $field == 'refTarget' || $field == 'refType'){
		if($value == 0){
			$value = "NULL";
		}
	} elseif($field == 'scoreValue' || $field == 'scoreDeduction') {
		// Don't make a deductions list
	} else {
		return;
	}


// Update the data submitted

	$sql = "UPDATE eventExchanges
			SET {$field} = {$value}, exchangeType = 'scored'
			WHERE exchangeID = {$exchangeID}";
	mysqlQuery($sql, SEND);

	$matchID = (int)$ids['matchID'];

	$sql = "SELECT exchangeID, exchangeType, scoreValue, scoreDeduction, refPrefix, refTarget, refType
			FROM eventExchanges
			WHERE matchID = {$matchID}";
	$allExchangesInMatch = (array)mysqlQuery($sql, ASSOC);


// Update the match data

	$matchScore = 0;
	$exchToReturn = [];
	$i = 0;
	foreach($allExchangesInMatch as $e){

		if($e['exchangeType'] == 'pending'){
			continue;
		}


		$eID = (int)$e['exchangeID'];
		$exchToReturn[$i] = $e;

		$exchToReturn[$i]['refPrefix'] = (int)$exchToReturn[$i]['refPrefix'];
		$exchToReturn[$i]['refTarget'] = (int)$exchToReturn[$i]['refTarget'];
		$exchToReturn[$i]['refType'] = (int)$exchToReturn[$i]['refType'];


		if($deductionList != []){

			$scoreDeduction = (float)0;

			switch($deductionAdditionMode){
				case DEDUCTION_ADDITION_MODE_RMS:
				{
					$scoreDeduction += pow((float)@$deductionList[1][$e['refPrefix']]['points'],2);
					$scoreDeduction += pow((float)@$deductionList[2][$e['refTarget']]['points'],2);
					$scoreDeduction += pow((float)@$deductionList[3][$e['refType']]['points'],2);
					$scoreDeduction = sqrt($scoreDeduction);
					$retVal['mode2'] = 2;
					break;
				}
				case DEDUCTION_ADDITION_MODE_MAX:
				{
					$scoreDeduction = max($scoreDeduction, (float)@$deductionList[1][$e['refPrefix']]['points']);
					$scoreDeduction = max($scoreDeduction, (float)@$deductionList[2][$e['refTarget']]['points']);
					$scoreDeduction = max($scoreDeduction, (float)@$deductionList[3][$e['refType']]['points']);
					$retVal['mode2'] = 1;
					break;
				}
				case DEDUCTION_ADDITION_MODE_ADD:
				default:
				{
					$scoreDeduction += (float)@$deductionList[1][$e['refPrefix']]['points'];
					$scoreDeduction += (float)@$deductionList[2][$e['refTarget']]['points'];
					$scoreDeduction += (float)@$deductionList[3][$e['refType']]['points'];
					$retVal['mode2'] = 0;
					break;
				}

			}

			$scoreDeduction = round($scoreDeduction,1);

			if($scoreDeduction != $e['scoreDeduction']){
				$sql = "UPDATE eventExchanges
						SET scoreDeduction = {$scoreDeduction}
						WHERE exchangeID = {$eID}";
				mysqlQuery($sql, SEND);

				$exchToReturn[$i]['scoreDeduction'] = $scoreDeduction;
			}

		} else {
			$scoreDeduction = $e['scoreDeduction'];
		}


		$matchScore += $e['scoreValue'] - $scoreDeduction;
		$i++;
	}

	$matchScore = round($matchScore,1);

	$sql = "UPDATE eventMatches
			SET fighter1score = $matchScore
			WHERE matchID = {$matchID}";
	mysqlQuery($sql, SEND);


	$retVal['matchScore'] = $matchScore;
	$retVal['exchanges'] = $exchToReturn;

	echo json_encode($retVal );

} break;

/******************************************************************************/

case 'updateFighterRating': {

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

	$retVal = [];

	$tournamentRosterID = (int)$_REQUEST['tournamentRosterID'];
	$rating = $_REQUEST['rating'];

	if(strlen($rating) == 0){

		$sql = "DELETE FROM eventRatings
				WHERE tournamentRosterID = {$tournamentRosterID}";
		mySqlQuery($sql, SEND);

		$rating = "";

	} else {

		$rating = round($rating);
		$rating = min( max(0,$rating), 9001);

		$sql = "SELECT ratingID
				FROM eventRatings
				WHERE tournamentRosterID = {$tournamentRosterID}";
		$ratingIDs = mySqlQuery($sql, SINGLES, 'ratingID');


		if(sizeof($ratingIDs) > 1){

			// This is a redundancy clean up branch, in case somehow the database
			// gets fucked and there is more than one entry for the same tournamentRosterID
			$sql = "DELETE FROM eventRatings
					WHERE tournamentRosterID = {$tournamentRosterID}";
			mySqlQuery($sql, SEND);

			$ratingID = 0;

		} else {
			$ratingID = (int)@$ratingIDs[0]; // not existing is the same as zero
		}


		if($ratingID == 0){

			$sql = "INSERT INTO eventRatings
					(tournamentRosterID, rating)
					VALUES
					({$tournamentRosterID}, {$rating})";
			mySqlQuery($sql, SEND);

		} else {

			$sql = "UPDATE eventRatings
					SET rating = {$rating}
					WHERE ratingID = {$ratingID}";
			mySqlQuery($sql, SEND);

		}

	}

	$retVal['tournamentRosterID'] = $tournamentRosterID;
	$retVal['rating'] = $rating;


	echo json_encode($retVal );

} break;

/******************************************************************************/

case 'updateCuttingQual': {

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

	$retVal = [];

	$systemRosterID = (int)$_REQUEST['systemRosterID'];
	$qualID = (int)$_REQUEST['qualID'];
	$operation = $_REQUEST['operation'];

	$params = [];

	if($operation == 'Update' || $operation == 'Add'){
		$params['systemRosterID'] = $systemRosterID;
		$qualID = addNewCuttingQual_event($params);
		$txt = "Remove";
	} else if($operation == 'Remove') {
		$params['qualID'] = $qualID;
		removeCuttingQual_event($params);
		$txt = "Add";
		$qualID = "";
	} else {
		/* Invalid, do nothing. */
	}

	$retVal['qualID'] = $qualID;
	$retVal['systemRosterID'] = $systemRosterID;
	$retVal['txt'] = $txt;

	echo json_encode($retVal);

} break;

/******************************************************************************/

case 'judgeEval': {

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

	$retVal = [];
	$eventID = (int)$_SESSION['eventID'];

	if($_REQUEST['tournamentIDs'] == 0){

		$sql = "SELECT tournamentID
				FROM eventTournaments
				WHERE eventID = {$eventID}";

		$tournamentIDs = implode2int((array)mysqlQuery($sql, SINGLES, 'tournamentID'));

	} else {
		$tournamentIDs = implode2int((array)$_REQUEST['tournamentIDs']);
	}


	$roleIDs = implode2int((array)$_REQUEST['roleIDs']);
	$dataType = $_REQUEST['dataType'];
	if((int)$_REQUEST['sortByValue'] == 0){
		$sortMode = 'name';
	} else {
		$sortMode = 'value';
	}

	if($roleIDs === "0"){
		$roleClause = "";
	} else {
		$roleClause = "AND logisticsRoleID IN ({$roleIDs})";
	}

	$hideWhiteCards = (boolean)readOption('E', $eventID, 'HIDE_WHITE_CARD_PENALTIES');


	/* __ Parse Exchanges _______________________________________________________________ */

	// This is an upper bound on the exchange length, unless there is some weirdness in the data.
	$MAX_EXCH_LENGTH = 180;

	$sql = "SELECT exchangeID, matchID, exchangeTime, rosterID, timestamp, exchangeType, refType
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				LEFT JOIN logisticsStaffMatches USING(matchID)
			WHERE tournamentID IN ({$tournamentIDs})
				{$roleClause}
			ORDER BY rosterID ASC, matchID ASC, exchangeNumber ASC";
	$raw = mysqlQuery($sql, ASSOC);

	$lastExchangeTime = 0;
	$matchID = 0;
	$JudgeSummaries = [];
	$lastUnixTimestamp = 0;


	// Count each exchange and associate it with someone based on the role they had.
	foreach($raw as $e){
		$rosterID = (int)$e['rosterID'];

		$unixTimestamp = strtotime($e['timestamp']);

		if($e['matchID'] != $matchID){
			$matchID = $e['matchID'];
			$lastExchangeTime = 0;
			@$JudgeSummaries[$rosterID]['numMatches']++;


			$totalTimeSec = $e['exchangeTime'];

		} else {
			$totalTimeSec = $unixTimestamp - $lastUnixTimestamp;
		}

		if($e['exchangeType'] == 'penalty'){

			if($hideWhiteCards == true && $e['refType'] == PENALTY_CARD_NONE){
				// Don't count penalties without a color.
			} else {
				@$JudgeSummaries[$rosterID]['numPenalties']++;
			}

		}

		$onTimeSec = $e['exchangeTime'] - $lastExchangeTime;
		if($onTimeSec <= 0){
			continue;
		}

		$offTimeSec = $totalTimeSec - $onTimeSec;


		@$JudgeSummaries[$rosterID]['numExchanges']++;
		@$JudgeSummaries[$rosterID]['timeOnTotal'] += limit($onTimeSec, 0, $MAX_EXCH_LENGTH);
		@$JudgeSummaries[$rosterID]['timeAllTotal'] += limit($totalTimeSec, 0, $MAX_EXCH_LENGTH);
		@$JudgeSummaries[$rosterID]['timeOffTotal'] += limit($offTimeSec, 0, $MAX_EXCH_LENGTH);


		$lastUnixTimestamp = $unixTimestamp;
		$lastExchangeTime = $e['exchangeTime'];

	}

	$retVal = [];
	$retVal['plot'] = [];
	$retVal['notes'] = "";
	$i = 0;

	// Parse the data and calculate things based on the total counts.
	foreach($JudgeSummaries as $rosterID => $data){

		if($JudgeSummaries[$rosterID]['timeOffTotal'] != 0){
			@$JudgeSummaries[$rosterID]['timeRatio'] = $JudgeSummaries[$rosterID]['timeOnTotal'] / $JudgeSummaries[$rosterID]['timeOffTotal'];
		} else {
			@$JudgeSummaries[$rosterID]['timeRatio'] = 0;
		}

		if($JudgeSummaries[$rosterID]['numExchanges'] != 0){
			@$JudgeSummaries[$rosterID]['timeOnAvg'] = $JudgeSummaries[$rosterID]['timeOnTotal'] / $JudgeSummaries[$rosterID]['numExchanges'];
		} else {
			@$JudgeSummaries[$rosterID]['timeOnAvg'] = 0;
		}

		if($JudgeSummaries[$rosterID]['numExchanges'] != 0){
			@$JudgeSummaries[$rosterID]['timeOffAvg'] = $JudgeSummaries[$rosterID]['timeOffTotal'] / $JudgeSummaries[$rosterID]['numExchanges'];
		} else {
			@$JudgeSummaries[$rosterID]['timeOffAvg'] = 0;
		}

		if($JudgeSummaries[$rosterID]['numExchanges'] != 0){
			@$JudgeSummaries[$rosterID]['timeAllAvg'] = $JudgeSummaries[$rosterID]['timeAllTotal'] / $JudgeSummaries[$rosterID]['numExchanges'];
		} else {
			@$JudgeSummaries[$rosterID]['timeAllAvg'] = 0;
		}

		if($JudgeSummaries[$rosterID]['numExchanges'] != 0){
			@$JudgeSummaries[$rosterID]['penaltiesPerExch'] = $JudgeSummaries[$rosterID]['numPenalties'] / $JudgeSummaries[$rosterID]['numExchanges'];
		} else {
			@$JudgeSummaries[$rosterID]['penaltiesPerExch'] = 0;
		}


		$retVal['plot'][$i]['value'] = @$JudgeSummaries[$rosterID][$dataType];


		if($rosterID != 0){
			$retVal['plot'][$i]['name'] = getFighterName($rosterID);
		} else {
			$retVal['plot'][$i]['name'] = "*Unknown*";
		}

		$i++;

	}

	// currently only two modes are supported, name and descending by value.
	if($sortMode == 'name'){
		usort($retVal['plot'], function($a, $b) {
			return $a['name'] <=> $b['name'];
		});
	} else {
		usort($retVal['plot'], function($a, $b) {
			return $b['value'] <=> $a['value'];
		});
	}


	/* __ Description of the data returned _____________________________________ */

	switch($dataType){
		case 'numMatches':
			$retVal['notes'] .= "Total number of matches staffed.";
			break;
		case 'numExchanges':
			$retVal['notes'] .= "Total number of exchanges scored.";
			break;
		case 'numPenalties':
			$retVal['notes'] .= "Total number of penalties awarded";
			break;
		case 'penaltiesPerExch':
			$retVal['notes'] .= "Average number of penalties per exchange scored.";
			break;
		case 'timeOffTotal':
			$retVal['notes'] .= "Total time [seconds] in matches without the clock running. (aka judge time).";
			break;
		case 'timeOffAvg':
			$retVal['notes'] .= "Average time [seconds] per exchange with the clock not running (aka judge time).";
			break;
		case 'timeOnTotal':
			$retVal['notes'] .= "Total time [seconds] in matches while the clock is running. (aka fighting time).";
			break;
		case 'timeOnAvg':
			$retVal['notes'] .= "Average time [seconds] per exchange with the clock running (aka fighting time).";
			break;
		case 'timeAllTotal':
			$retVal['notes'] .= "Total time [seconds] in matches.";
			break;
		case 'timeAllAvg':
			$retVal['notes'] .= "Average time [seconds] per exchange (fighting + judging time).";
			break;
		case 'timeRatio':
			$retVal['notes'] .= "Ratio of [fighting : judging] time.";
			break;

		default:	break;
	}

	if($dataType == 'numPenalties' || $dataType == 'penaltiesPerExch'){
		if($hideWhiteCards == true){
			$retVal['notes'] .= '<BR>Only colored cards displayed.';
		} else {
			$retVal['notes'] .= '<BR>Showing <u>all</u> exchanges entered as penalties, including white cards.';
		}

	}

	$retVal['notes'] .= "<br><b>".getEventName($eventID)."<b>";


	echo json_encode($retVal);

} break;

/******************************************************************************/

}


// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
