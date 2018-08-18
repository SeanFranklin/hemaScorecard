<?php
/*******************************************************************************
	Database Read Functions
	
	Functions for reading from the HEMA Scorecard database
	
*******************************************************************************/

/******************************************************************************/

function createEventRoster_HemaRatings($eventID = null, $dir = "exports/"){
// Creates a .csv file with the eventRoster
// Format: | Name1 | Name2 | Result1 | Result2 | Stage of Tournament |


// Get roster/event information	
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"No Event ID in createRosterCsv");
		return;
	}

	$eventRoster = getEventRosterForExport($eventID);
	$eventName = getEventName($eventID);
	$fileName = "{$dir}{$eventName} - fighterRoster.csv";

// Create the CSV file	
	$fp = fopen($fileName, 'w');

	foreach ($eventRoster as $fields) {

		fputs($fp, getFighterNameSystem($fields['systemRosterID'], 'first').",");
		fputs($fp, $fields['schoolFullName'].",");
		fputs($fp, $fields['schoolCountry'].",");
		fputs($fp, ",");
		fputs($fp, $fields['HemaRatingsID']);	
		
		fputs($fp, '');
		
		fputs($fp, PHP_EOL);
	}
	fclose($fp);

	return $fileName;
}

/******************************************************************************/

function createTournamentResults_HemaRatings($tournamentID, $dir = "exports/"){
// Creates a .csv file with the results of all tournament matches
// Format: | Name1 | Name2 | Result1 | Result2 | Stage of Tournament |


// Get information about the tournament
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in exportTournament()");
		return;
	}
	$tournamentName = getTournamentName($tournamentID);

	$sql = "SELECT eventID
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$eventID = mysqlQuery($sql, SINGLE, 'tournamentID');
	$eventName = getEventName($eventID);

	$fileName = "{$dir}{$eventName} - {$tournamentName}.csv";

// Get the match results
	$sql = "SELECT scoringID, recievingID, exchangeType, matchID
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND (exchangeType = 'winner' OR exchangeType = 'doubleOut' OR exchangeType = 'tie')";
	$finishedMatches = mysqlQuery($sql, ASSOC);
	
// Create the CSV file	
	$fp = fopen($fileName, 'w');
	
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
	
	return $fileName;
	
}

/******************************************************************************/

function getMatchStageName($matchID){
// Returns the name of the stage a match occured at.
// ie. Pools, Elims, Gold Medal Match

	
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

function getAllPoolScores($tournamentID = null, $poolSet = 1){
// returns the scores of all the pool matches in the current tournament
// indexed by matchID	
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getAllPoolScores()");
		return;
	}
	
	if($poolSet == 'all'){
		$groupSet = null;
	} else {
		$groupSet = "AND eventGroups.groupSet = {$poolSet}";
	}
	
	$sql = "SELECT matchID, fighter2Score, fighter1Score 
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE eventGroups.tournamentID = {$tournamentID}
			{$groupSet}" ;
			
	return mysqlQuery($sql, KEY, 'matchID');
	
}

/******************************************************************************/

function getAllAttackTargets(){
	
	$sql = "SELECT attackID, attackText
			FROM systemAttacks
			WHERE attackClass = 'target'";
	return mysqlQuery($sql, ASSOC);
	
}

/******************************************************************************/

function getAllAttackPrefixes(){
	
	$sql = "SELECT attackID, attackText
			FROM systemAttacks
			WHERE attackClass = 'prefix'";
	return mysqlQuery($sql, ASSOC);
	
}

/******************************************************************************/

function getAllAttackTypes(){
	
	$sql = "SELECT attackID, attackText
			FROM systemAttacks
			WHERE attackClass = 'type'";
	
	return mysqlQuery($sql, ASSOC);
	
}

/******************************************************************************/

function getAttackAttributes($tableID){

	$tableID = (int)$tableID;
	if($tableID == 0){
		return null;
	}
	
	$sql = "SELECT attackPrefix, attackType, attackTarget, attackPoints
			FROM eventAttacks
			WHERE tableID = {$tableID}";
	return mysqlQuery($sql, SINGLE);
	
}

/******************************************************************************/

function getAllTournamentExchanges($tournamentID = null, $groupType = null, $poolSet = 1){
//gets all the exchanges in a tournament by fighter
		
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getAllTournamentExchanges()");
		return;
	}
	
	if($groupType == 'pool' || $groupType == null || $groupType == 'pools'){
		$whereClause = "AND eventGroups.groupType = 'pool'";
	} else if($groupType == 'final' || $groupType == 'finals'){
		$whereClause = "AND eventGroups.groupType = 'final'";
	} if($groupType == 'all'){
		$whereClause = null;
	} 
	
	if($poolSet == 'all'){
		$groupSet = null;
	} else {
		$groupSet = "AND eventGroups.groupSet = {$poolSet}";
	}
	
	$sql = "SELECT exchangeID, exchangeType, scoringID, recievingID, 
			scoreValue, scoreDeduction, matchID, groupID
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			WHERE eventGroups.tournamentID = {$tournamentID}
			{$groupSet}
			{$whereClause}
			AND ignoreMatch != 1";
	$rawExchangeData = (array)mysqlQuery($sql, ASSOC);
	
	$isFullAfterblow = isFullAfterblow($tournamentID);

	$matchesForFighter = [];
	$fighterStats = [];

	foreach($rawExchangeData as $exchange){
		$matchID = $exchange['matchID'];

		$scoringID = $exchange['scoringID'];
		$recievingID = $exchange['recievingID'];
		
		$matchesForFighter[$scoringID][$matchID] = true;
		$matchesForFighter[$recievingID][$matchID] = true;


		$fighterStats[$scoringID]['groupID'] = $exchange['groupID'];
		$fighterStats[$recievingID]['groupID'] = $exchange['groupID'];
		
		switch($exchange['exchangeType']){
			case 'clean':
			case 'afterblow':
				@$fighterStats[$scoringID]['pointsFor'] += ($exchange['scoreValue']-$exchange['scoreDeduction']);
				@$fighterStats[$scoringID]['AbsPointsFor'] += $exchange['scoreValue'];
				@$fighterStats[$scoringID]['AbsPointsAgainst'] += $exchange['scoreDeduction'];
				@$fighterStats[$scoringID]['hitsFor']++;
				
				@$fighterStats[$recievingID]['pointsAgainst'] += ($exchange['scoreValue']-$exchange['scoreDeduction']);
				@$fighterStats[$recievingID]['AbsPointsFor'] += $exchange['scoreDeduction'];
				@$fighterStats[$recievingID]['AbsPointsAgainst'] += $exchange['scoreValue'];
				@$fighterStats[$recievingID]['hitsAgainst']++;
				
				if($exchange['exchangeType'] == 'afterblow'){
					@$fighterStats[$scoringID]['afterblowsAgainst']++;
					@$fighterStats[$recievingID]['afterblowsFor']++;

					if(($exchange['scoreValue'] == $exchange['scoreDeduction'])
						&& $isFullAfterblow){
						@$fighterStats[$scoringID]['hitsFor']--;
						@$fighterStats[$scoringID]['hitsAgainst']++;
					}

				}
				break;
			case 'double':
				@$fighterStats[$scoringID]['doubles']++;
				@$fighterStats[$recievingID]['doubles']++;
				break;
			case 'noExchange':
				@$fighterStats[$scoringID]['noExchanges']++;
				@$fighterStats[$recievingID]['noExchanges']++;
				break;
			case 'penalty':
				@$fighterStats[$scoringID]['numPenalties']++;
				@$fighterStats[$scoringID]['penaltiesAgainst'] -= $exchange['scoreValue'];
				@$fighterStats[$scoringID]['pointsFor'] += $exchange['scoreValue'];
				@$fighterStats[$recievingID]['penaltiesAgainstOpponents'] -= $exchange['scoreValue'];
				break;
			case 'winner':
				@$fighterStats[$scoringID]['wins']++;
				@$fighterStats[$recievingID]['losses']++;
				break;
			case 'doubleOut':
				@$fighterStats[$scoringID]['doubleOuts']++;
				@$fighterStats[$recievingID]['doubleOuts']++;
				break;
			case 'tie':
				@$fighterStats[$scoringID]['ties']++;
				@$fighterStats[$recievingID]['ties']++;
				break;
			default:
		}

	}
	
	
// Number of matches for each fighter
	
	foreach($matchesForFighter as $rosterID => $matches){
		$fighterStats[$rosterID]['matches'] = count($matches);
	}
	
	
	$attributes[] = 'pointsFor';
	$attributes[] = 'pointsAgainst';
	$attributes[] = 'hitsFor';
	$attributes[] = 'hitsAgainst';
	$attributes[] = 'afterblowsFor';
	$attributes[] = 'afterblowsAgainst';
	$attributes[] = 'doubles';
	$attributes[] = 'noExchanges';
	$attributes[] = 'numPenalties';
	$attributes[] = 'penaltiesAgainstOpponents';
	$attributes[] = 'penaltiesAgainst';
	$attributes[] = 'wins';
	$attributes[] = 'losses';
	$attributes[] = 'doubleOuts';
	$attributes[] = 'ties';
	
	// Set all values not counted to zero
	foreach($fighterStats as $fighterID => $fighter){
		foreach($attributes as $attribute){
			if(!isset($fighter[$attribute]) || $fighter[$attribute] == null){
				$fighterStats[$fighterID][$attribute] = 0;
			}
		}
	}
	
	
	return $fighterStats;
	
}

/******************************************************************************/

function getBasePointValue($tournamentID, $groupSet, $returnNulls = null){
// returns the base point value of the given set in the given tournament
// if null is provided for a tournamentID it will use the current one in session
// if null is provided for the groupSet it will return the tournament default
// $returnNulls will return the value from the eventAttributes table, even
// if it has never been set
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getBasePointValue()");
		return;
	}

	// Check if set has it's own base value
	if($groupSet !== null){
		$sql = "SELECT attributeValue
				FROM eventAttributes
				WHERE tournamentID = {$tournamentID}
				AND attributeGroupSet = {$groupSet}
				AND attributeType = 'basePointValue'";
		$value = mysqlQuery($sql, SINGLE, 'attributeValue');
		
		if($value != null){
			return $value;
		}
		if($returnNulls){
			return $value;
		}
	}
	
	// Return base value for tournament
	$sql = "SELECT basePointValue
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return mysqlQuery($sql, SINGLE, 'basePointValue');
	
}

/**********************************************************************/

function getBracketInformation($tournamentID = null){
// return an unsorted array of all brackets in a tournament

	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getBracketInformation()");
		return;
	}	

	$sql = "SELECT groupID, bracketLevels, tournamentID, groupName, numFighters
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'";
	$result = (array)mysqlQuery($sql, ASSOC);

	if($result == null){
		return null;
	}
	
	foreach($result as $data){
		$groupName = $data['groupName'];
		$bracket[$groupName]['groupID'] = $data['groupID'];
		$bracket[$groupName]['bracketLevels'] = $data['bracketLevels'];
		$bracket[$groupName]['tournamentID'] = $data['tournamentID'];
		$bracket[$groupName]['numFighters'] = $data['numFighters'];
	}

	if(@$bracket['loser']['bracketLevels'] == 1){
		$bracket['winner']['loserID'] = $bracket['loser']['groupID'];
		unset($bracket['loser']);
	}
	
	return $bracket;
	
}

/******************************************************************************/

function getBracketMatchesByPosition($bracketID){
// return an unsorted array of all matches in a bracket
// indexed by bracketLevel (depth in the tree) 
// and bracketPosition (position on it's level)
	
	if($bracketID == null){
		setAlert(SYSTEM,"No bracketID in getBracketMatchesByPosition()");
		return;
	}
	
	$sql = "SELECT matchID, fighter1ID, fighter2ID, winnerID, fighter1Score, 
			fighter2Score, bracketPosition, bracketLevel
			FROM eventMatches
			WHERE groupID = {$bracketID}";
			
	$result = (array)mysqlQuery($sql, ASSOC);
	
	foreach($result as $entry){
		$bracketLevel = $entry['bracketLevel'];
		$bracketPosition = $entry['bracketPosition'];
		
		$matches[$bracketLevel][$bracketPosition]['fighter1ID'] = $entry['fighter1ID'];
		$matches[$bracketLevel][$bracketPosition]['fighter2ID'] = $entry['fighter2ID'];
		$matches[$bracketLevel][$bracketPosition]['fighter1Score'] = $entry['fighter1Score'];
		$matches[$bracketLevel][$bracketPosition]['fighter2Score'] = $entry['fighter2Score'];
		$matches[$bracketLevel][$bracketPosition]['winnerID'] = $entry['winnerID'];
		$matches[$bracketLevel][$bracketPosition]['matchID'] = $entry['matchID'];
		
		if($entry['fighter1ID'] == $entry['winnerID'] && isset($entry['winnerID'])){
			$matches[$bracketLevel][$bracketPosition]['loserID'] = $entry['fighter2ID'];
		} else if ($entry['fighter2ID'] == $entry['winnerID'] && isset($entry['winnerID'])){
			$matches[$bracketLevel][$bracketPosition]['loserID'] = $entry['fighter1ID'];
		}
		
	}
	
	return $matches;
	
}

/******************************************************************************/

function getColors(){
	$sql = "SELECT *
			FROM systemColors";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function getCuttingStandard($tournamentID = null){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getCuttingStandard()");
		return;
	}	
	
	$sql = "SELECT standardID, date, qualValue, standardName
			FROM eventCuttingStandards
			INNER JOIN cuttingStandards USING(standardID)
			WHERE tournamentID = {$tournamentID}";
	return mysqlQuery($sql, SINGLE);
	
}

/******************************************************************************/

function getControlPointValue($tournamentID = null){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getControlPointValue()");
		return;
	}	
	
	$sql = "SELECT useControlPoint
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return mysqlQuery($sql, SINGLE, 'useControlPoint');
	
}

/******************************************************************************/

function getCumulativeScores($group1ID, $group2ID){

	if($group1ID == null || $group2ID == null){
		setAlert(SYSTEM,"No group1ID or group2ID in getCumulativeScores()");
		return;
	}

	$sql = "SELECT fighter1ID, fighter1Score
			FROM eventMatches
			WHERE groupID = {$group2ID}
			AND ignoreMatch != 1";
	$round2Results = mysqlQuery($sql, ASSOC);
	
	foreach($round2Results as $result){
		$rosterID = $result['fighter1ID'];
		$score = $result['fighter1Score'];
		
		if($score === null){ // Has not competed yet
			continue;
		}
		
		$sql = "SELECT fighter1Score
				FROM eventMatches
				WHERE groupID = {$group1ID}
				AND fighter1ID = {$rosterID}";
		$score += mysqlQuery($sql, SINGLE, 'fighter1Score');
		
		$fighterScore['score'] = $score;
		$fighterScore['rosterID'] = $rosterID;
		$scores[] = $fighterScore;
		
	}	
			
	if(!isset($scores)){return;}
	foreach($scores as $key => $entry){
		$sort1[$key] = $entry['score'];
	}
	
	array_multisort($sort1, SORT_DESC, $scores);

	return $scores;
	
}

/******************************************************************************/

function getDefaultEvent(){
	
	$sql = "SELECT eventID FROM systemEvents
			WHERE eventStatus = 'default'";
	return mysqlQuery($sql, SINGLE, 'eventID');
	
}

/******************************************************************************/

function getDoubleTypes($tournamentID = null){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getDoubleTypes()");
		return;
	}	

	$sql = "SELECT doublesDisabled, afterblowDisabled, afterblowType, isNotNetScore
			FROM systemDoubleTypes
			INNER JOIN eventTournaments USING(doubleTypeID)
			WHERE tournamentID = {$tournamentID}";

	$doubleTypes = mysqlQuery($sql, SINGLE);
	
	return $doubleTypes;
	
}


/******************************************************************************/

function getElimID($tournamentID = null){
	if($tournamentID == null){$tournamentID = $_SESSION('tournamentID');}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getElimID()");
		return;
	}
	
	$sql = "SELECT tournamentElimID
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return mysqlQuery($sql, SINGLE, 'tournamentElimID');
	
}

/******************************************************************************/

function getEventEmail($eventID = null){
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"No eventID in getEventEmail");
		return;
	}

	$sql = "SELECT organizerEmail
			FROM systemEvents
			WHERE eventID = {$eventID}";
	return mysqlQuery($sql, SINGLE, 'organizerEmail');


}

/******************************************************************************/

function getEventExchanges($eventID = null){
// gets summary counts of all exchanges in the event

	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"No eventID in getEventExchanes()");
		return;
	}
	
	$tournaments = getEventTournaments($eventID);


	$tournamentStats = [];
	foreach($tournaments as $tournamentID){

		$sql = "SELECT exchangeType, scoreValue
				FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				WHERE exchangeType != 'winner'
				AND tournamentID = {$tournamentID}";
		$matchData = mysqlQuery($sql, ASSOC);
		
		if($matchData == null){
			continue;
		}

		$tournamentStats[$tournamentID]['clean'] = 0;
		$tournamentStats[$tournamentID]['double'] = 0;
		$tournamentStats[$tournamentID]['afterblow'] = 0;
		$tournamentStats[$tournamentID]['noExchange'] = 0;
		$tournamentStats[$tournamentID]['noQuality'] = 0;
		
		foreach($matchData as $data){
			switch($data['exchangeType']){
				case 'clean':
					$tournamentStats[$tournamentID]['clean'] += 1;
					break;
				case 'double':
					$tournamentStats[$tournamentID]['double'] += 1;
					break;
				case 'afterblow':
					$tournamentStats[$tournamentID]['afterblow'] += 1;
					break;
				case 'noExchange':
					$tournamentStats[$tournamentID]['noExchange'] += 1;
					break;
				case 'noQuality':
					$tournamentStats[$tournamentID]['noQuality'] += 1;
				default:
					continue;
				break;
			}
			$pointVal = $data['scoreValue'];

			if(!isset($tournamentStats[$tournamentID][$pointVal])){
				$tournamentStats[$tournamentID][$pointVal] = 0;
			}
			$tournamentStats[$tournamentID][$pointVal] += 1;
			
		}
	}
	
	return $tournamentStats;
	
}

/******************************************************************************/

function getEventIncompletes($eventID = null){
	
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"No eventID in getEventIncompletes()");
		return;
	}
	
	$sql = "SELECT matchID, bracketLevel, groupName, groupType, fighter1ID, fighter2ID, tournamentID
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE eventID = {$eventID}
			AND matchComplete = 0
			AND ignoreMatch = 0
			AND (groupType = 'pool' OR groupType = 'elim')
			ORDER BY tournamentID";
	return mysqlQuery($sql, ASSOC);
	
}

/******************************************************************************/

function getEventList($eventStatus, $order = null, $limit = null){
// returns an unsorted array of all events in the software
// indexed by eventID

	$validValues['active'] = true;
	$validValues['upcoming'] = true;
	$validValues['hidden'] = true;
	$validValues['archived'] = true;

	if(!isset($validValues[$eventStatus])){
		setAlert(SYSTEM,'Invalid eventStatus in getEventList()');
		return null;
	}
	if($order != 'DESC' && $order != 'ASC'){
		if($eventStatus == 'archived' || $eventStatus == '%'){
			$order = 'DESC';
		} else {
			$order = 'ASC';
		}

	}

	$limit = (int)$limit;
	if((int)$limit > 0){
		$limitString = "LIMIT {$limit}";
	} else {
		$limitString = '';
	}
	
	$sql = "SELECT eventID, eventName, eventYear, eventStartDate, 
			eventEndDate, eventCountry, eventProvince, eventCity, 
			eventStatus 
			FROM 
			systemEvents
			WHERE eventStatus LIKE '{$eventStatus}'
			ORDER BY eventStartDate {$order}
			{$limitString}";
	return mysqlQuery($sql, KEY, 'eventID');

}

/******************************************************************************/

function getEventListFULL(){
// returns an unsorted array of all events in the software
// indexed by eventID

	$sql = "SELECT * 
			FROM 
			systemEvents
			ORDER BY eventStartDate DESC";
	return mysqlQuery($sql, KEY, 'eventID');

}

/******************************************************************************/

function getEventName($eventID){
	//return the event name in form 'Test Event 1999'
	
	if($eventID == null){ //if no event is selected
		return null;
	}
	
	$sql = "SELECT eventName, eventYear
			FROM systemEvents
			WHERE eventID = {$eventID}";
	
	$result = mysqlQuery($sql, SINGLE);
	$eventName = $result['eventName']." ".$result['eventYear'];
	
	return $eventName;
	
}

/******************************************************************************/

function getEventRoster($sortString = null){
	
// return a sorted array of all fighters in the event

	$eventID = $_SESSION['eventID'];
	if($eventID == null){
		setAlert(SYSTEM,"No eventID in getEventRoster()");
		return;
	}

	if(isset($sortString)){
		$sortString = "ORDER BY ".$sortString;
	} else {
		$orderName = NAME_MODE;
		$sortString = "ORDER BY {$orderName}";
	}

	// The schoolID in eventRoster and systemRoste may not be the same
	// School in event is what they were at the time of the event, in
	// the system it is the school from the latest appearance
	$sql = "SELECT firstName, lastName, eventRoster.schoolID, 
			schoolShortName, schoolBranch, rosterID, eventID
			FROM eventRoster
			INNER JOIN systemSchools USING(schoolID)
			INNER JOIN systemRoster ON eventRoster.systemRosterID = systemRoster.systemRosterID
			AND eventID = {$eventID}
			{$sortString}";
	$roster = mysqlQuery($sql, ASSOC);
	
	return $roster;
	
}

/******************************************************************************/

function getEventTournaments($eventID = null){
	
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		// Not an error, just return an empty result
		return null;
	}
	
// Sort by numParticipants
	if($_SESSION['dataModes']['tournamentSort'] == 'numSort'){
		$sql = "SELECT tournamentID
				FROM eventTournaments
				WHERE eventID = {$eventID}
				ORDER BY numParticipants DESC";
		return mysqlQuery($sql, SINGLES);		
	} 
	
// Sort alphabeticaly
	if($_SESSION['dataModes']['tournamentSort'] == 'nameSort'){
		$sql = "SELECT tournamentID
				FROM eventTournaments
				WHERE eventID = {$eventID}";
		$res = mysqlQuery($sql, SINGLES);

		foreach($res as $data){
			$tournamentNames[$data] = getTournamentName($data);
		}
		if($tournamentNames == null){ return; }
	
		asort($tournamentNames);
		
		foreach($tournamentNames as $tournamentID => $data){
			$sortedList[] = $tournamentID;
		}
	
		return $sortedList;
	
	}
	
// Default option, $_SESSION['dataModes']['tournamentSort'] == 'numGrouped'
	// Returns tournaments grouped into weapon types, sorted by total 
	// number of fighters in the weapon type
	$sql = "SELECT tournamentID, numParticipants, tournamentWeaponID 
			FROM eventTournaments
			WHERE eventID = {$eventID}
			ORDER BY numParticipants DESC";
	$tournamentList = mysqlQuery($sql, ASSOC);

	$numParticipantsInWeapon = [];

	foreach($tournamentList as $tournament){
		$numParticipants = $tournament['numParticipants'];
		$weaponID = $tournament['tournamentWeaponID'];
		if(!isset($numParticipantsInWeapon[$weaponID])){
			$numParticipantsInWeapon[$weaponID] = 0;
		}
		$numParticipantsInWeapon[$weaponID] += $numParticipants;
	}
	
	arsort($numParticipantsInWeapon);

	$sortedList = [];
	foreach($numParticipantsInWeapon as $weaponID => $numParticipants){
		foreach($tournamentList as $index => $tournament){
			if($tournament['tournamentWeaponID'] == $weaponID){
				$sortedList[] = $tournament['tournamentID'];
				unset($tournamentList[$index]);
			}
		}
	}

	return $sortedList;
}

/******************************************************************************/

function getEventStatus($eventID = null){
	
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"Invalid eventID in getEventStatus()");
		return;
	}
	
	$sql = "SELECT eventStatus
			FROM systemEvents
			WHERE eventID = {$eventID}";
	
	return mysqlQuery($sql, SINGLE, 'eventStatus');
	
}

/******************************************************************************/

function getEventRosterForExport($eventID){
	if($eventID == null){
		setAlert(SYSTEM,"No eventID in getEventRosterForExport()");
		return;
	}
	
	// The schoolID in eventRoster and systemRoste may not be the same
	// School in event is what they were at the time of the event, in
	// the system it is the school from the latest appearance
	$sql = "SELECT sys.systemRosterID, sch.schoolFullName, sch.schoolCountry, sys.HemaRatingsID
			FROM eventRoster ev
			INNER JOIN systemRoster sys ON sys.systemRosterID = ev.systemRosterID
			INNER JOIN systemSchools sch ON ev.schoolID = sch.schoolID
			WHERE ev.eventID = {$eventID}";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function getFighterInfoFromSystem($systemRosterID = null){
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"No eventID in getFighterInfoFromSystem()"); 
		return;
	}

	$sql = "SELECT systemRoster.* 
			FROM systemRoster
			WHERE systemRoster.systemRosterID = {$systemRosterID}";
	$data = mysqlQuery($sql, SINGLE);
	return $data;
	
	
}
/******************************************************************************/

function getFighterExchanges($rosterID, $weaponID){

	// Exchanges with the fighter scoring
	$sql = "SELECT exchangeType, scoreValue
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			INNER JOIN eventRoster ON eventExchanges.scoringID = eventRoster.rosterID
			WHERE systemRosterID = {$rosterID}
			AND tournamentWeaponID = {$weaponID}";
	$result1 = mysqlQuery($sql, ASSOC);
	
	// Deliniator to note two different data sets when stepping through the result
	$r[0]['exchangeType'] = '*';
	$r[0]['scoreValue'] = '0';
	$result1 = array_merge($result1, $r);
	
	// Exchanges with the fighter recieving
	$sql = "SELECT exchangeType, scoreValue
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			INNER JOIN eventRoster ON recievingID = eventRoster.rosterID
			WHERE systemRosterID = {$rosterID}
			AND tournamentWeaponID = {$weaponID}";
	$result2 = mysqlQuery($sql, ASSOC);
	
	$result = array_merge($result1, $result2);
	
	return $result;
	
}

/******************************************************************************/

function getFighterInfo($rosterID){
	
	if($rosterID == null){ 
		setAlert(SYSTEM,"Invalid rosterID in getFighterInfo()");
		return;
	}
		
	$sql = "SELECT sys.firstName, sys.lastName, sys.middleName, 
			sys.nickname, sys.birthdate, sys.rosterCountry, sys.rosterProvince,
			sys.rosterCity, sys.eMail, 
			ev.rosterID, ev.schoolID 
			FROM eventRoster ev
			INNER JOIN systemRoster sys ON ev.systemRosterID = sys.systemRosterID
			WHERE ev.rosterID = {$rosterID}";
	
	$data = mysqlQuery($sql, SINGLE);
	
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){return $data;}
	
	$tournamentIDs = getEventTournaments();
	
	$sql = "SELECT eventTournaments.tournamentID 
			FROM eventTournamentRoster, eventTournaments
			WHERE rosterID = {$rosterID}
			AND eventTournaments.eventID = {$eventID}
			AND eventTournaments.tournamentID = eventTournamentRoster.tournamentID";
	$result = mysqlQuery($sql, ASSOC);
	
	foreach($tournamentIDs as $tournamentID){
		$isEnteredInTournament = null;
		foreach($result as $tournament){
			if($tournament['tournamentID'] == $tournamentID){
				$isEnteredInTournament = true;
				break;
			}
		}
		
		if($isEnteredInTournament){
			$data['tournamentIDs'][$tournamentID] = true;
		} else {
			$data['tournamentIDs'][$tournamentID] = false;
		}
	}
	
	return $data;
}

/******************************************************************************/

function getFighterName($rosterID, $splitName = null, $nameMode = null){
	if($rosterID == null){
		setAlert(SYSTEM,"No rosterID in getFighterName()");
		return;
	}
	
	if($splitName == null){
		$sql = "SELECT firstName, lastName
				FROM eventRoster
				INNER JOIN systemRoster USING(systemRosterID)
				WHERE eventRoster.rosterID = {$rosterID}";
		$result = mysqlQuery($sql, SINGLE);
		
		if($nameMode == 'first'){
			$name = $result['firstName']." ".$result['lastName'];
		} elseif(NAME_MODE == 'lastName' || $nameMode == 'last'){
			$name = $result['lastName'].", ".$result['firstName'];
		} else {
			$name = $result['firstName']." ".$result['lastName'];
		}
		
	} else {
		$sql = "SELECT systemRoster.firstName, systemRoster.lastName
				FROM eventRoster
				INNER JOIN systemRoster ON eventRoster.systemRosterID = systemRoster.systemRosterID
				WHERE eventRoster.rosterID = {$rosterID}";	
		$name = mysqlQuery($sql, SINGLE);
	}
	
	return $name;
}

/******************************************************************************/

function getFighterNameSystem($systemRosterID, $nameMode = null){
	if($systemRosterID == null){
		setAlert(SYSTEM,"No rosterID in getFighterName()");
		return;
	}
	
	$sql = "SELECT firstName, lastName
			FROM systemRoster
			WHERE systemRosterID = {$systemRosterID}";
	$result = mysqlQuery($sql, SINGLE);

	if($nameMode == null){
		$nameMode = NAME_MODE;
	}
	if($nameMode == null){
		$nameMode = first;
	}


	switch($nameMode){
		case 'first':
		case 'firstName':
			$retVal = $result['firstName']." ".$result['lastName'];
			break;
		case 'last':
		case 'lastName':
			$retVal = $result['lastName'].", ".$result['firstName'];
			break;
		case 'array':
			$retVal = $result;
			break;
		default:
			break;
	}

	return $retVal;
	
}

/******************************************************************************/

function getGroupName($groupID){
	if($groupID == null){
		setAlert(SYSTEM,"No groupID in getGroupName()");
		return;
	}
	
	$sql = "SELECT groupName
			FROM eventGroups
			WHERE groupID = {$groupID}";
	return mysqlQuery($sql, SINGLE, 'groupName');
	
}

/******************************************************************************/

function getGroupNumber($groupID){
	if($groupID == null){
		setAlert(SYSTEM,"No grouprID in getGroupNumber()");
		return;
	}
	
	$sql = "SELECT groupNumber
			FROM eventGroups
			WHERE groupID = {$groupID}";
	return mysqlQuery($sql, SINGLE, 'groupNumber');
	
}

/******************************************************************************/

function getIgnores($tournamentID = null){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getIgnores()");
		return;
	}
	
	$sql = "SELECT rosterID, ignoreFighter, groupSet
			FROM eventGroupRoster
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN systemRoster USING(systemRosterID)
			WHERE tournamentID = {$tournamentID}";
	$result = mysqlQuery($sql, ASSOC);
	
	$ignoreStartingAt = [];
	foreach($result as $data){
		$rosterID = $data['rosterID'];
		if($data['ignoreFighter'] == 1){
			if(!isset($ignoreStartingAt[$rosterID]) || $data['groupSet'] < $ignoreStartingAt[$rosterID]){
				$ignoreStartingAt[$rosterID] = $data['groupSet'];
			}
		}	
	}

	return $ignoreStartingAt;
}

/******************************************************************************/

function getStops($tournamentID = null){
// Fighters which can no longer advance in to subsequent brackets/rounds
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getStops()");
		return;
	}
	
	$sql = "SELECT rosterID, removeFromFinals
			FROM eventTournamentRoster
			WHERE tournamentID = {$tournamentID}";
	return mysqlQuery($sql, KEY_SINGLES, 'rosterID', 'removeFromFinals');
	
}

/******************************************************************************/

function getMatchDoubles($matchID){
// returns the number of doubles in a match	

	if($matchID == null){
		setAlert(SYSTEM,"No matchID in getMatchDoubles()");
		return;
	}
	
	$sql = "SELECT count(*) AS numDoubles
			FROM eventExchanges
			WHERE matchID = {$matchID}
			AND exchangeType ='double'";
	return mysqlQuery($sql, SINGLE, 'numDoubles');
	
}

/******************************************************************************/

function getMatchExchanges($matchID = null){
// returns an unsorted 	array of all exchanges in a match
	if($matchID == null){$matchID = $_SESSION['matchID'];}
	if($matchID == null){
		setAlert(SYSTEM,"No matchID in getMatchExchanges()");
		return;
	}
		
	$sql = "SELECT eventExchanges.exchangeType, eventExchanges.exchangeID,
			eventExchanges.scoreValue, eventExchanges.scoreDeduction,
			eventRoster.rosterID, exchangeTime, exchangeNumber
			FROM eventExchanges
			INNER JOIN eventRoster ON eventRoster.rosterID = eventExchanges.scoringID
			WHERE eventExchanges.matchID = {$matchID}
			ORDER BY exchangeNumber ASC, exchangeID ASC"; 
			
	$result = mysqlQuery($sql, ASSOC);

	return $result;
	
}

/******************************************************************************/

function getMatchInfo($matchID = null){
// returns and array of information about a match:
// 	- tournamentID, matchID, fighterIDs, fighter names, match type (pool/final)
// 	- afterblow and doubles type, maximum doubles
	
	if($matchID == null){$matchID = $_SESSION['matchID'];}
	if($matchID == null){
		setAlert(SYSTEM,"No matchID in getMatchInfo()");
		return;
	}

	$sql = "SELECT fighter1ID, fighter2ID, winnerID, 
			fighter1score, fighter2score, matchComplete, ignoreMatch,
			matchNumber, matchTime, bracketLevel
			FROM eventMatches
			WHERE matchID = {$matchID}";
	$matchInfo = mysqlQuery($sql, SINGLE);
	$matchInfo['matchID'] = $matchID;
	
	$id1 = $matchInfo['fighter1ID'];
	$id2 = $matchInfo['fighter2ID'];

	if($id1 != null){
		$sql = "SELECT eventRoster.schoolID
				FROM eventRoster
				INNER JOIN systemRoster ON systemRoster.systemRosterID = eventRoster.systemRosterID
				WHERE eventRoster.rosterID = {$id1}";
		$info = mysqlQuery($sql, SINGLE);	
		
		$matchInfo['fighter1School'] = getSchoolName($info['schoolID'],'full');
	}

	if($id2 != null){
		$sql = "SELECT eventRoster.schoolID
				FROM eventRoster
				INNER JOIN systemRoster ON systemRoster.systemRosterID = eventRoster.systemRosterID
				WHERE eventRoster.rosterID = {$id2}";
		$info = mysqlQuery($sql, SINGLE);	
			
		$matchInfo['fighter2School'] = getSchoolName($info['schoolID'],'full');
	}
	
	$sql = "SELECT eventGroups.groupType, eventGroups.groupID, groupName, groupNumber, tournamentID
			FROM eventGroups, eventMatches
			WHERE eventMatches.matchID = {$matchID}
			AND eventMatches.groupID = eventGroups.groupID";
	$result = mysqlQuery($sql, SINGLE);
	
	$matchInfo['matchType'] = $result['groupType'];
	$matchInfo['groupID'] = $result['groupID'];
	$matchInfo['groupName'] = $result['groupName'];
	$matchInfo['groupNumber'] = $result['groupNumber'];
	$matchInfo['tournamentID'] = $result['tournamentID'];
	
	$sql = "SELECT maxDoubleHits
			FROM eventTournaments
			WHERE tournamentID = {$matchInfo['tournamentID']}";
	$matchInfo['maxDoubles'] = mysqlQuery($sql, SINGLE, 'maxDoubleHits');
	
	
	$sql = "SELECT MAX(exchangeID)
			FROM eventExchanges
			WHERE matchID = {$matchID}";	
	$matchInfo['lastExchange'] = mysqlQuery($sql, SINGLE, 'MAX(exchangeID)');

	$sql = "SELECT doubleTypeID
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE matchID = {$matchID}";	
	$matchInfo['doubleType'] = mysqlQuery($sql, SINGLE, 'doubleTypeID');
	
	
	if($matchInfo['matchComplete'] == 1){
		$sql = "SELECT exchangeType
				FROM eventExchanges
				WHERE exchangeID = {$matchInfo['lastExchange']}";
		$matchInfo['endType'] = mysqlQuery($sql, SINGLE, 'exchangeType');	
	} elseif ($matchInfo['ignoreMatch'] == 1){
		$matchInfo['endType'] = 'ignore';
	}
	
	
	
	return $matchInfo;
}

/******************************************************************************/

function getRoundMatches($groupID){
	
	if($groupID == null){
		setAlert(SYSTEM,"No groupID in getRoundMatches()");
		return;
	}
	
	$sql = "SELECT matchID, matchNumber,fighter1ID, fighter1Score, matchComplete
			FROM eventMatches
			WHERE groupID = {$groupID}
			ORDER BY matchNumber ASC";
	return mysqlQuery($sql, ASSOC);
	
	
}

/******************************************************************************/

function getMatches($tournamentID = null, $exclude = null, $poolSet = 1){
// return an unsorted array of all pool matches and their fighters and winners
// indexed by groupID and matchID	
	

	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getMatches()");
		return;
	}
	
	if($exclude != 'all'){
		$exclude = "AND eventMatches.ignoreMatch != 1";
	} else {
		$exclude = null;
	}
	
	if($poolSet == 'all'){
		$groupSet = null;
	} else {
		$groupSet = "AND eventGroups.groupSet = {$poolSet}";
	}
	
	$sql = "SELECT eventMatches.matchID, eventMatches.groupID, 
			eventMatches.fighter1ID, eventMatches.fighter2ID, eventMatches.winnerID,
			eventMatches.matchNumber, eventMatches.ignoreMatch, eventMatches.matchNumber,
			eventGroups.groupName, eventMatches.matchComplete, eventGroups.groupNumber
			FROM eventMatches, eventGroups
			WHERE eventGroups.tournamentID = {$tournamentID}
			AND eventMatches.groupID = eventGroups.groupID
			AND eventGroups.groupType = 'pool'
			{$groupSet}
			{$exclude}
			ORDER BY eventMatches.matchNumber ASC, eventGroups.groupNumber ASC";
	$allMatches = (array)mysqlQuery($sql, ASSOC);

	$matchList = [];
	foreach($allMatches as $match){
		$matchID = $match['matchID'];
		$groupID = $match['groupID'];
		$matchList[$groupID]['groupName'] = $match['groupName'];
		$matchList[$groupID]['groupNumber'] = $match['groupNumber'];
		$matchList[$groupID][$matchID]['fighter1ID'] =  $match['fighter1ID'];
		$matchList[$groupID][$matchID]['fighter2ID'] =  $match['fighter2ID'];
		$matchList[$groupID][$matchID]['winnerID'] =  $match['winnerID'];
		$matchList[$groupID][$matchID]['matchNumber'] =  $match['matchNumber'];
		$matchList[$groupID][$matchID]['isComplete'] =  $match['matchComplete'];
		$matchList[$groupID][$matchID]['ignoreMatch'] =  $match['ignoreMatch'];
		
		if($match['matchComplete'] == 1){
			
		
			$sql = "SELECT exchangeType 
					FROM eventExchanges
					WHERE matchID = {$matchID}
					AND exchangeID = (	SELECT MAX(exchangeID)
										FROM eventExchanges
										WHERE matchID = {$matchID})";
			$tmp = mysqlQuery($sql, SINGLE, 'exchangeType');
			$matchList[$groupID][$matchID]['endType']  = $tmp;
			
		} elseif($match['ignoreMatch'] == 1){
			$matchList[$groupID][$matchID]['endType'] = 'ignore';
		}
		
	}
	
	return $matchList;
}

/******************************************************************************/

function getMaxExchanges($tournamentID = null){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getMaxExchanges()");
		return;
	}
	
	$sql = "SELECT maximumExchanges
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$max = mysqlQuery($sql, SINGLE, 'maximumExchanges');

	if($max == 0){
		$max = null;
	}
	return $max; 
	
}

/******************************************************************************/

function getNumberOfFightsTogether($rosterID1, $rosterID2, $tournamentID){
	$sql = "SELECT matchID
			FROM eventMatches
			INNER JOIN eventGroups ON eventMatches.groupID = eventGroups.groupID
			WHERE eventGroups.tournamentID = {$tournamentID}
			AND (
				(fighter1ID = {$rosterID1} AND fighter2ID = {$rosterID2}) 
				OR 
				(fighter1ID = {$rosterID2} AND fighter2ID = {$rosterID1})
			)";
	$result = mysqlQuery($sql, ASSOC);
	return count($result);
	
}

/******************************************************************************/

function getNumberOfFightersInSystem(){

	$sql = "SELECT COUNT(systemRosterID) AS numTotalFighters,
			COUNT(HemaRatingsID) AS numRatedFighters
			FROM systemRoster";
	return mysqlQuery($sql, SINGLE);
}

/******************************************************************************/

function getHemaRatingsInfo($bounds){
	$lowBound = (int)$bounds['lowBound'] - 1;
	$numRecords = (int)$bounds['highBound'] - $lowBound;

	$searchIDs = isset($bounds['searchIDs']);
	$searchNoIDs = isset($bounds['searchNoIDs']);

	if($searchIDs && !$searchNoIDs){
		$where = "WHERE HemaRatingsID IS NOT NULL";
	} elseif(!$searchIDs && $searchNoIDs){
		$where = "WHERE HemaRatingsID IS NULL";
	} elseif(!$searchIDs && !$searchNoIDs){
		// Would return no results
		return null;
	} else {
		// No need for where clause
		$where = '';
	}

	if($numRecords < 1){
		return null;
	}

	$sql = "SELECT systemRosterID, HemaRatingsID, schoolID
			FROM systemRoster
			{$where}
			ORDER BY systemRosterID DESC
			LIMIT $lowBound, $numRecords";
	return mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function getHemaRatingsID($systemRosterID){
	$systemRosterID = (int)$systemRosterID;

	$sql = "SELECT HemaRatingsID
			FROM systemRoster
			WHERE systemRosterID = {$systemRosterID}";
	return mysqlQuery($sql, SINGLE, 'HemaRatingsID');

}

/******************************************************************************/

function getNameMode(){
	
	$eventID = $_SESSION['eventID'];
	if($eventID == null){ return 'firstName';}
	
	$sql = "SELECT nameDisplay
			FROM eventDefaults
			WHERE eventID = {$eventID}";
	$nameDisplay = mysqlQuery($sql, SINGLE, 'nameDisplay');
	
	if($nameDisplay == null){
		return 'firstName';
	}
	return $nameDisplay;
	
}

/******************************************************************************/

function getNextPoolMatch($matchInfo){
// Gets the next match in a group, skipping matches which are set to be ignored
	
	if($matchInfo == null){
		setAlert(SYSTEM,"No matchInfo in getNextPoolMatch()");
		return;
	}

	if((int)$matchInfo['matchNumber']<= 0){
		return null;
	}
	
	$sql = "SELECT matchID
			FROM eventMatches
			WHERE groupID = {$matchInfo['groupID']}
			AND matchNumber > {$matchInfo['matchNumber']}
			AND ignoreMatch = 0
			ORDER BY matchNumber ASC
			LIMIT 1";
	$matchID = mysqlQuery($sql, SINGLE, 'matchID');

	if($matchID == null){
		return null;
	} else {
		return getMatchInfo($matchID);
	}
	
}

/******************************************************************************/

function getNumEventMatches($eventID = null){
	
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"No eventID in getNumEventMatches()");
		return;
	}
	
	$sql = "SELECT COUNT(matchID)
			AS numMatches
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE eventTournaments.eventID = {$eventID}
			AND (groupType = 'pool' OR groupType = 'elim')";
	$matches['matches'] = mysqlQuery($sql, SINGLE, 'numMatches');
	
	$sql = "SELECT COUNT(matchID)
			AS numMatches
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE eventTournaments.eventID = {$eventID}
			AND groupType = 'round'";
	$matches['pieces'] = mysqlQuery($sql, SINGLE, 'numMatches');
	
	return $matches;
	
	
}

/******************************************************************************/

function getNumParticipants($tournamentID){
	// counts the number of participants in a tournament
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getNumParticipants()");
		return;
	}
	
	$event = EVENT;
	
	$sql = "SELECT tableID FROM {$event}_tournamentParticipants
			WHERE tournamentID = {$tournamentID}";

	return mysqlQuery($sql, NUM_ROWS);
	
}

/******************************************************************************/

function getNumPools($groupSet, $tournamentID = null){
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getNumPools()");
		return;
	}
	
	$sql = "SELECT COUNT(*)
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'pool'
			AND groupSet = {$groupSet}";
	
	$res = $GLOBALS["___mysqli_ston"]->query($sql);

	$num = $res->fetch_row();
	return $num[0];
}

/******************************************************************************/

function getNumGroupSets($tournamentID = null){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getNumGroupSets()");
		return;
	}
	
	$sql = "SELECT numGroupSets
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
			
	return mysqlQuery($sql, SINGLE, 'numGroupSets');
	
}

/******************************************************************************/

function getNumStopsInGroup($groupID){

	$sql = "SELECT gR.rosterID
			FROM eventGroupRoster AS gR
			INNER JOIN eventTournamentRoster AS tR ON gR.tournamentTableID = tR.tableID
			WHERE groupID = {$groupID}
			AND removeFromFinals = 1";
	$res = mysqlQuery($sql, NUM_ROWS);

	return $res;

}

/******************************************************************************/

function getPassword($type, $eventID = null){
	
	if($eventID != null){ // Getting the password for an event.
		$sql = "SELECT {$type}
				FROM systemEvents
				WHERE eventID = {$eventID}";
		return mysqlQuery($sql, SINGLE, $type);
	}
	
	$sql = "SELECT password
			FROM systemUsers
			WHERE logInType = '{$type}'";
	return mysqlQuery($sql, SINGLE, 'password');
}

/******************************************************************************/

function getPasswords($eventID = null){
// returns an array containing the encrypted password data stored in the SQL table	
	
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"No eventID in getPasswords()");
		return;
	}
	
	$sql = "SELECT staffPassword, adminPassword, salt FROM systemEvents
			WHERE eventID = {$eventID}";
	$result = mysqlQuery($sql, SINGLE);
	
	$password[USER_STAFF] = $result['staffPassword'];
	$password[USER_ADMIN] = $result['adminPassword'];
	$password['salt'] = $result['salt'];
	$password[USER_SUPER_ADMIN] = SUPER_ADMIN_PASSWORD;
	$password[USER_VIDEO] = VIDEO_PASSWORD;
	$password[USER_STATS] = ANALYTICS_PASSWORD;
	
	return $password;
}

/******************************************************************************/

function getPoolMatchOrder($groupID, $groupRoster){

	if($groupID == null ){
		setAlert(SYSTEM,"No groupID in getPoolMatchOrder()");
		return;
	}

	if ($groupRoster== null){
		// This is not an error, it may be an empty group.
		return null;
	}

	$numFighters = count($groupRoster);
	
	$sql = "SELECT matchNumber, fighter1, fighter2
			FROM systemMatchOrder
			WHERE numberOfFighters = {$numFighters}";
	$result = mysqlQuery($sql, ASSOC);
	
	$matchOrder = [];
	foreach($result as $entry){
		$fighter1Num = $entry['fighter1'];
		$fighter2Num = $entry['fighter2'];
		$matchNumber = $entry['matchNumber'];
		$matchOrder[$matchNumber]['fighter1ID'] = $groupRoster[$fighter1Num]['rosterID'];
		$matchOrder[$matchNumber]['fighter2ID'] = $groupRoster[$fighter2Num]['rosterID'];
		
	}
	return $matchOrder;
}

/******************************************************************************/

function getPoolRosters($tournamentID = null, $groupSet = 1){
// returns an unsorted array of the rosters of each group
// indexed by groupID and poolPosition (order of each fighter in the pool)	
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getPoolRosters()");
		return;
	}
	
	if($groupSet == 'all'){
		$groupSet = null;
	} else {
		$groupSet = "AND eventGroups.groupSet = {$groupSet}";
	}

	$sql = "SELECT groupID
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			{$groupSet}";
	
	$poolList = mysqlQuery($sql, SINGLES, 'groupID');

	$pools = [];
	foreach($poolList as $groupID){
		$pools[$groupID] = [];
	}
	
	$sql = "SELECT eventGroupRoster.poolPosition, eventGroupRoster.tableID,
			eventGroups.groupID, eventRoster.rosterID, systemSchools.schoolAbreviation
			FROM eventGroupRoster
			INNER JOIN eventGroups ON eventGroups.groupID = eventGroupRoster.groupID
			INNER JOIN eventRoster ON eventGroupRoster.rosterID = eventRoster.rosterID
			INNER JOIN systemRoster ON eventRoster.systemRosterID = systemRoster.systemRosterID
			INNER JOIN systemSchools ON eventRoster.schoolID = systemSchools.schoolID
			WHERE eventGroups.tournamentID = {$tournamentID}
			{$groupSet}
			ORDER BY eventGroupRoster.poolPosition ASC";
	$result = (array)mysqlQuery($sql, ASSOC);

	foreach($result as $row){
		$groupID = $row['groupID'];
		$poolPosition = $row['poolPosition'];
		$pools[$groupID][$poolPosition]['rosterID'] = $row['rosterID'];
		$pools[$groupID][$poolPosition]['schoolAbreviation'] = $row['schoolAbreviation'];
		$pools[$groupID][$poolPosition]['tableID'] = $row['tableID'];
	}

	
	return $pools;
}

/******************************************************************************/

function getPools($tournamentID = null, $groupSet = 1){
// returns a sorted array of all pools, by pool number
// indexed by poolID (groupID)

	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getPools()");
		return;
	}
	
	if($groupSet == 'all'){
		$groupWhere = null;
	} else {
		$groupWhere = "AND eventGroups.groupSet = {$groupSet}";
	}
	
	$sql = "SELECT eventGroups.groupID, eventGroups.groupName, 
				eventGroups.groupComplete, eventGroups.groupSet,
				eventGroups.groupNumber, eventGroups.numFighters
			FROM eventGroups
			WHERE eventGroups.tournamentID = {$tournamentID}
			AND eventGroups.groupType = 'pool'
			{$groupWhere}
			ORDER BY groupNumber ASC";

	$pools = mysqlQuery($sql, ASSOC);
	
	return $pools;
}

/******************************************************************************/

function getGroupSetOfMatch($matchID = null){
	if($matchID == null){$matchID = $_SESSION['matchID'];}
	if($matchID == null){
		setAlert(SYSTEM,"No matchID in getGroupSetOfMatch()");
		return;
	}
	
	$sql = "SELECT groupSet
			FROM eventGroups
			INNER JOIN eventMatches USING(groupID)
			WHERE matchID = {$matchID}";
	return mysqlQuery($sql, SINGLE, 'groupSet');
}

/******************************************************************************/

function getGroupInfo($groupID){
// 

	if($groupID == null){
		setAlert(SYSTEM,"No groupID in getGroupInfo()");
		return;
	}
	
	
	$sql = "SELECT groupID, groupName, groupNumber, groupComplete, groupSet
			FROM eventGroups
			WHERE groupID = {$groupID}";
	
	$group = mysqlQuery($sql, ASSOC);
	return $group;
}

/******************************************************************************/

function getEventDefaults($eventID = null){
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		// Not an error, can return a null result
		return null;
	}
	
	$sql = "SELECT *
			FROM eventDefaults
			WHERE eventID = {$eventID}";
	$defaults = mysqlQuery($sql, SINGLE);
	
	if($defaults == null){
		$defaults['color1ID'] = 1;
		$defaults['color2ID'] = 2;
		$defaults['maxPoolSize'] = 5;
		$defaults['maxDoubleHits'] = 3;
		$defaults['normalizePoolSize'] = 0;
		$defaults['allowTies'] = 0;
		$defaults['nameDisplay'] = 'firstName';
		$defaults['tournamentDisplay'] = 'weapon';
		$defaults['tournamentSorting'] = 'numGrouped';
		$defaults['useTimer'] = 0;
		$defaults['useControlPoint'] = 0;
	}

	return $defaults;
	
}

/******************************************************************************/

function getTournamentIncompletes($tournamentID = null, $type = null, $poolSet = 1){
// Returns the status of the tournament pools (complete etc...)
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getTournamentIncompletes()");
		return;
	}
	
	if($poolSet == 'all'){
		$groupSet = null;
	} else {
		$groupSet = "AND eventGroups.groupSet = {$poolSet}";
	}
	
	$sql = "SELECT matchID, groupType
			FROM eventMatches
			INNER JOIN eventGroups ON eventGroups.groupID = eventMatches.groupID
			WHERE eventGroups.tournamentID = {$tournamentID}
			AND matchComplete != 1
			{$groupSet}
			AND ignoreMatch != 1";
	$result = mysqlQuery($sql, ASSOC);

	foreach($result as $match){
		$incompleteMatches[$match['groupType']][] = $match['matchID'];
	}

	if(!isset($incompleteMatches)){
		// No incomplete matches
		return null;
	} elseif($type != null){
		return @$incompleteMatches[$type]; // Might be empty
	} else {
		return $incompleteMatches;
	}

}

/******************************************************************************/

function getRounds($tournamentID = null, $groupSet = null){
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getRounds()");
		return;}
	
	$set = '';
	if($groupSet != null){
		$set = "AND groupSet = {$groupSet}";
	}
	
	$sql = "SELECT groupID, groupName, groupSet, groupNumber
			FROM eventGroups
			WHERE groupType = 'round'
			AND tournamentID = {$tournamentID}
			{$set}
			ORDER BY groupSet ASC, groupNumber ASC";
	return mysqlQuery($sql, ASSOC);
	
	
}

/******************************************************************************/

function getRoundScores($groupID){
// Returns an array of fighters in the round, sorted by score
	
	if($groupID == null){
		setAlert(SYSTEM,"No groupID in getRoundScores()");
		return;
	}

	$matches= getRoundMatches($groupID);
	$scores = [];

	foreach($matches as $match){
		$rosterID = $match['fighter1ID'];
		$score = $match['fighter1Score'];
		
		if($score === null){ // Has not competed yet
			continue;
		}
		$fighterData['rosterID'] = $rosterID;
		$fighterData['score'] = $score;
		
		$scores[] = $fighterData;
		
	}
	
	if(!isset($scores)){
		return null;
	}


	foreach($scores as $key => $entry){
		$sort1[$key] = $entry['score'];
	}

	if(isset($sort1)){
		array_multisort($sort1, SORT_DESC, $scores);
	}
	
	return $scores;
	
}


/******************************************************************************/

function getListForNextRound($tournamentID, $groupSet, $groupNumber){
//	Return a sorted array of fighters, for the purpose of populating the select
//	dropdowns in roundRosters
//	- First round of tournament -> Alphabetical list
//	- First round of stage -> Sorted by cumulative score in previous stage
//	- Second or higher round in stage -> Sorted by cumulative of prior rounds in stage

	$stops = getStops($tournamentID);
	
	if($groupSet == 1 && $groupNumber == 1){
		$r = getTournamentRoster();
		foreach($r as $index => $fighter){
			if($stops[$fighter['rosterID']] > 0){
				unset($r[$index]);
			}
		}
		return $r;
	}
	
	if($groupNumber == 1){
		$groupSet--;
		$groupNumber = null;
	}
	
	$rounds = getRounds($tournamentID, $groupSet);
	$ignores = getIgnores($tournamentID);
	
	if($groupNumber == null){
		$groupNumber = count($rounds)+1;
	}
	
	$startingIndex = $groupNumber -2; // Use all groups lower than the group number
	$highestRound = true;
	
	for($roundIndex = $startingIndex; $roundIndex >= 0; $roundIndex--){ 

		$groupID = $rounds[$roundIndex]['groupID'];
		$matches= getRoundMatches($groupID);
		
		foreach($matches as $match){
			$rosterID = $match['fighter1ID'];

			if(isset($ignores[$rosterID]) && $groupSet > $ignores[$rosterID]){ 
				continue; 
			}
			if(!$highestRound && !isset($scores[$rosterID])){ continue; } // Only adds fighters if they are in the highest round
			
			$score = $match['fighter1Score'];
			if($score === null){ continue; }// Has not competed yet

			if(!isset($scores[$rosterID])){
				$scores[$rosterID] = 0;
			}
			$scores[$rosterID] += $score;
			
		}
		
		$highestRound = false;
	}
	
	if(!isset($scores)){return null;}
	

	arsort($scores);
	
	
	$place = 0;
	foreach($scores as $rosterID => $score){
		if($stops[$rosterID] > 0){
			continue;
		}
		$sortedScores[$place]['rosterID'] = $rosterID;
		$sortedScores[$place]['score'] = $score;
		$sortedScores[$place]['place'] = $place;
		$place++;
	}
	
	return $sortedScores;
	
}

/******************************************************************************/

function getLivestreamInfo($eventID = null){
	
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		// Not an error, return no information
		return null;
	}
	
	$sql = "SELECT isLive, chanelName, platform, useOverlay, matchID
			FROM eventLivestreams
			WHERE eventID = {$eventID}";
	
	return mysqlQuery($sql, SINGLE);
	
}

/******************************************************************************/

function getLivestreamMatch($eventID = null){
	
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"getLivestreamMatch()");
		return;
	}
	
	$sql = "SELECT matchID
			FROM eventLivestreams
			WHERE eventID = {$eventID}";
	return mysqlQuery($sql, SINGLE, 'matchID');
	
}

/******************************************************************************/

function getLivestreamMatchOrder($eventID = null){
	
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"getLivestreamMatchOrder()");
		return;
	}
	
	$sql = "SELECT matchNumber, matchID
			FROM eventLivestreamMatches
			WHERE eventID = {$eventID}
			ORDER BY matchNumber ASC";
	return mysqlQuery($sql, KEY_SINGLES, 'matchNumber', 'matchID');
}

/******************************************************************************/

function getNormalization($tournamentID, $groupSet = 1){
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getNormalization()");
		return;
	}
	
	// Checks to see if the set has it's own normalization size
	if($groupSet != null){
		
		$sql = "SELECT attributeValue
				FROM eventAttributes
				WHERE tournamentID = {$tournamentID}
				AND attributeGroupSet = {$groupSet}
				AND attributeType = 'normalization'";
		$value = mysqlQuery($sql, SINGLE, 'attributeValue');
	}
	
	
	// Default size for the tournament
	if($value === null){
		$sql = "SELECT normalizePoolSize
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$value =  mysqlQuery($sql, SINGLE, 'normalizePoolSize');
	}

	
	// A value of 0 means the normalization is set to auto detect
	$numFightersInPool = [];
	if($value < 2 && $groupSet != null){
		$pools = getPools($tournamentID, $groupSet);
		
		foreach($pools as $pool){
			if($pool['numFighters'] < 1){ continue; }
			if(!isset($numFightersInPool[$pool['numFighters']])){
				$numFightersInPool[$pool['numFighters']] = 0;
			}

			$numFightersInPool[$pool['numFighters']]++;
		}
		
		$highestInstances = 0;
		$value = 0;

		foreach((array)$numFightersInPool as $fighters => $instances){
			if($instances > $highestInstances){
				$value = $fighters;
				$highestInstances = $instances;
			} elseif($instances == $highestInstances){
				// If there is a tie it goes with the lower number
				$value = $fighters;
			}
		}
	}
	if($value < 2){
		$value = 2;
	}

	return $value;
	
}

/******************************************************************************/

function getNormalizationCumulative($tournamentID, $groupSet = 1){
	
	$sql = "SELECT MAX(attributeGroupSet) as groupSet
			FROM eventAttributes
			WHERE tournamentID = {$tournamentID}
			AND attributeGroupSet < {$groupSet}
			AND attributeType = 'cumulative'
			AND attributeBool = FALSE ";
	$startAtSet = (int)mysqlQuery($sql, SINGLE, 'groupSet');
	if($startAtSet < 1){
		$startAtSet = 1;
	}
	
	return getNormalization($tournamentID, $groupSet);

}

/******************************************************************************/

function getSetName($setNumber, $tournamentID = null){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getSetName()");
		return;
	}
	
	$sql = "SELECT attributeText
			FROM eventAttributes
			WHERE tournamentID = {$tournamentID}
			AND attributeType = 'setName'
			AND attributeGroupSet = {$setNumber}";
	$name = mysqlQuery($sql, SINGLE, 'attributeText');
	
	if($name != null){
		return $name;
	}
	
	$sql = "SELECT tournamentElimID
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$elimID = mysqlQuery($sql, SINGLE, 'tournamentElimID');
	
	if($elimID == SCORED_EVENT){
		return "Stage {$setNumber}";
	}
	if($elimID == POOL_SETS){
		$sql = "SELECT groupName
				FROM eventGroups
				WHERE tournamentID = {$tournamentID}
				AND groupSet = {$setNumber}";
		$groups = mysqlQuery($sql, ASSOC);

		if(count($groups) == 1){
			
			$name = $groups[0]['groupName'];
			if($name == "Pool 1"){
				return "Pool Set {$setNumber}";
			} else {
				return $name;
			}
		} 
		
		return "Pool Set {$setNumber}";

	}
	if($elimID == POOL_BRACKET){
		return "Pool Matches";
	}
	
	return "Set {$setNumber}";
}

/******************************************************************************/

function getSetAttributes($tournamentID){
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getSetAttributes()");
		return;
	}
	
	$numGroupSets = getNumGroupSets($tournamentID);
	
	for($i = 1; $i <= $numGroupSets; $i++){
		$attributes[$i] = null;
	}
	
	$sql = "SELECT attributeType, attributeGroupSet, attributeBool, attributeText, attributeValue
			FROM eventAttributes
			WHERE tournamentID = {$tournamentID}
			AND (	attributeType = 'setName' 
					OR attributeType = 'cumulative'
					OR attributeType = 'normalization')";
					
	$result = mysqlQuery($sql, ASSOC);

	foreach($result as $data){
		$setNumber = $data['attributeGroupSet'];
		switch($data['attributeType']){
			case 'setName':
				$attributes[$setNumber]['name'] = $data['attributeText'];
				break;
			case 'cumulative':
				$attributes[$setNumber]['cumulative'] = $data['attributeBool'];
				break;
			case 'normalization':
				$attributes[$setNumber]['normalization'] = $data['attributeValue'];
				break;
			default:
		}
	}

	return $attributes;
	
}

/******************************************************************************/

function getSchoolInfo($schoolID){
// return a sorted array of all schools in the database 	
	
	if($schoolID == null){
		setAlert(SYSTEM,"No schoolID in getSchoolInfo()");
		return;
	}
		
	$sql = "SELECT *
			FROM systemSchools
			WHERE schoolID = {$schoolID}";
	return mysqlQuery($sql, SINGLE);

}

/******************************************************************************/

function getSchoolList($sortString = null){
// return a sorted array of all schools in the database 	
	
	if(isset($sortString)){
		$sortString = "ORDER BY ".$sortString;
	} else {
		$sortString = "ORDER BY schoolShortName, schoolBranch";
	}
	
	$sql = "SELECT schoolShortName, schoolID, schoolBranch, schoolAbreviation, schoolCountry
			FROM systemSchools
			{$sortString}";
	$allSchools = mysqlQuery($sql, ASSOC);

	
	return $allSchools;
}

/******************************************************************************/

function getSchoolListLONG($sortString = null){
// return a sorted array of all schools in the database 	
	
	if(isset($sortString)){
		$sortString = "ORDER BY ".$sortString;
	} else {
		$sortString = "ORDER BY schoolShortName, schoolBranch";
	}
	
	$sql = "SELECT *
			FROM systemSchools
			{$sortString}";
	$allSchools = mysqlQuery($sql, ASSOC);

	
	return $allSchools;
}

/******************************************************************************/

function getSchoolName($schoolID = null, $nameType = null, $includeBranch = null){
	if($schoolID == null){return "No schoolID in getSchoolName()";} 
	
	$sql = "SELECT schoolFullName, schoolShortName, schoolBranch, schoolAbreviation 
			FROM systemSchools
			WHERE schoolID = {$schoolID}";

	$result = mysqlQuery($sql, SINGLE);
	
	if($nameType == 'abrev'){
		$schoolName = $result['schoolAbreviation'];
	} else if($nameType == 'long' || $nameType == 'full'){
		$schoolName = $result['schoolFullName'];
	} else {
		$schoolName = $result['schoolShortName'];
	}
	
	if($includeBranch){
		if($result['schoolBranch'] != null){
			$schoolName .= ", ".$result['schoolBranch'];
		}
	}
	
	if($schoolName == null){$schoolName = '&nbsp';}
	
	return $schoolName;
	
}

/******************************************************************************/

function getSchoolPoints($eventID = null){
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"No eventID for updateSchoolPoints()");
		return;
	}
	
	$tournamentList = getTournamentsFull();
	$fullPlacings = array();
	$points = array();

	foreach($tournamentList as $tournamentID => $data){
		$placings = getTournamentPlacings($tournamentID);
		
		foreach($placings as $p => $data){
			
			$schoolID = $data['schoolID'];
			if($schoolID == 1 || $schoolID == 2){continue;}
			
			if($data['placeType'] == 'final'){
				$place = $data['placing'];
			} else {
				$place = "Top ".$data['placing'];
			}
			
			$score = calculateScoreForFighter($data);
			
			$fullPlacings[$schoolID]['score'] += $score;
			$fullPlacings[$schoolID][$place]++;
			$fullPlacings[$schoolID]['schoolID'] = $schoolID;
			$fullPlacings[$schoolID]['schoolName'] = $data['schoolFullName'];
			
		}
		
	}
	
	foreach($fullPlacings as $key => $data){
		$points[$key] = $data['score'];
	}
	
	array_multisort($points,SORT_DESC,$fullPlacings);
	
	return $fullPlacings;
	
}

/******************************************************************************/

function getSchoolRosterNotInEvent($schoolID, $eventID = null){

	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"No eventID in getSchoolRosterNotInEvent()");
		return;
	}
	if($schoolID == null){ 
		setAlert(SYSTEM,"No schoolID in getSchoolRosterNotInEvent()");
		return;
	}	
	
	$orderName = NAME_MODE;
	
	$sql = "SELECT system.systemRosterID
			FROM systemRoster AS system
			LEFT JOIN eventRoster AS event ON system.systemRosterID = event.systemRosterID AND event.eventID = {$eventID}
			WHERE system.schoolID = {$schoolID}
			AND event.eventID IS null
			ORDER BY {$orderName} ASC"; 
 
	return mysqlQuery($sql, ASSOC);
	
}

/******************************************************************************/

function getScoringAlgorithimsByType(){
	
	$sql = "SELECT tournamentRankingID, name,
			Pool_Bracket, Pool_Sets, Scored_Event
			FROM systemRankings
			ORDER BY numberOfInstances DESC";
	$result = mysqlQuery($sql, ASSOC);
	
	foreach($result as $rankType){
		$info['tournamentRankingID'] =  $rankType['tournamentRankingID'];
		$info['name'] = $rankType['name'];
		
		if($rankType['Pool_Bracket'] == 1){
			$allTypes['Pool_Bracket'][] = $info;
		}
		if($rankType['Pool_Sets'] == 1){
			$allTypes['Pool_Sets'][] = $info;
		}
		if($rankType['Scored_Event'] == 1){
			$allTypes['Scored_Event'][] = $info;
		}
		
	}
	
	return $allTypes;
	
}

/******************************************************************************/

function getScoreFormula($tournamentID = null){

	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getScoreFormula()");
		return;
	}

	$sql = "SELECT scoreFormula
			FROM eventTournaments
			INNER JOIN systemRankings USING(tournamentRankingID)
			WHERE tournamentID = {$tournamentID}";

	return mysqlQuery($sql,SINGLE, 'scoreFormula');

}

/******************************************************************************/

function getSystemRoster($tournamentID = null){
	
// return a sorted array of all fighters in the system

	$orderName = NAME_MODE;
	$sortString = "ORDER BY {$orderName} ASC";

	if($tournamentID == null){
		$sql = "SELECT systemRosterID
				FROM systemRoster
				{$sortString}";

		return mysqlQuery($sql, SINGLES, 'systemRosterID');
	}

	$sql = "SELECT systemRoster.systemRosterID
			FROM eventTournamentRoster
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN systemRoster ON eventRoster.systemRosterID = systemRoster.systemRosterID
			WHERE tournamentID = {$tournamentID}
			{$sortString}";

	return mysqlQuery($sql, SINGLES, 'systemRosterID');
	
	
}

/******************************************************************************/

function getTournamentWeaponsList(){
	$sql = "SELECT tournamentTypeID, tournamentType, numberOfInstances
			FROM systemTournaments
			WHERE tournamentTypeMeta = 'weapon'
			ORDER BY numberOfInstances DESC";
	return mysqlQuery($sql, ASSOC);
	
}

/******************************************************************************/

function getNumNoWinners($groupID){
	
	$sql = "SELECT matchID
			FROM eventMatches
			WHERE groupID = {$groupID}
			AND matchComplete = 1
			AND winnerID IS NULL";
			
	return mysqlQuery($sql, NUM_ROWS);
	
}
	
/******************************************************************************/

function getTournamentAttacks($tournamentID = null){
// Get the unique attacks attributed to a tournament

	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getTournamentAttacks()");
		return;
	}
	
	$sql = "SELECT attackTarget, attackType, attackPoints, attackPrefix, tableID
			FROM eventAttacks
			WHERE tournamentID = {$tournamentID}
			ORDER BY attackNumber ASC";
			
	$data = mysqlQuery($sql, ASSOC);
	
	
	foreach((array)$data as $index => $attack){
		
		$text = '';
		
		if($attack['attackPrefix'] != null){
			$sql = "SELECT attackText
					FROM systemAttacks
					WHERE attackID = {$attack['attackPrefix']}";
			$name = mysqlQuery($sql, SINGLE, 'attackText');
			
			$text .= $name;
		}
		
		if($attack['attackType'] != null){
			$sql = "SELECT attackText
					FROM systemAttacks
					WHERE attackID = {$attack['attackType']}";
			$name = mysqlQuery($sql, SINGLE, 'attackText');
			
			if($text != null){
				$text .= "  ";
			}
			$text .= $name;
		}
		if($attack['attackTarget'] != null){
			$sql = "SELECT attackText
					FROM systemAttacks
					WHERE attackID = {$attack['attackTarget']}";
			$name = mysqlQuery($sql, SINGLE, 'attackText');
			
			if($text != null){
				$text .= " to ";
			}
			$text .= $name;
		}
		if($text != null){
			$text .= ": ";
		}
		
		$text .= $attack['attackPoints'];
		if($attack['attackPoints'] == 1){
			$text .= " Point";
		} else {
			$text .= " Points";
		}
		
		$data[$index]['attackText'] = $text;
	}
	
	return $data;

}

/******************************************************************************/

function getTournamentAttributeName($ID = null){
	//the name of any tournament attribute given it's ID
	if($ID == null){
		return null;
	}
	
	$sql = "SELECT tournamentType FROM systemTournaments
			WHERE tournamentTypeID = {$ID}";
	$out = mysqlQuery($sql, SINGLE);
	return $out['tournamentType'];
}

/******************************************************************************/

function getTournamentName($tournamentID = null){
	//generates the tournament name give the ID
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getTournamentName()");
		return;
	}
	

	$weaponID = selectTournamentIdItem('tournamentWeaponID',$tournamentID);
	$prefixID = selectTournamentIdItem('tournamentPrefixID',$tournamentID);
	$suffixID = selectTournamentIdItem('tournamentSuffixID',$tournamentID);
	$genderID = selectTournamentIdItem('tournamentGenderID',$tournamentID);
	$materialID = selectTournamentIdItem('tournamentMaterialID',$tournamentID);
	
	$weaponName = getTournamentAttributeName($weaponID);
	$prefixName = getTournamentAttributeName($prefixID);
	$suffixName = getTournamentAttributeName($suffixID);
	$genderName = getTournamentAttributeName($genderID);
	$materialName = getTournamentAttributeName($materialID);
	
	$name = "";
	
	if($_SESSION['dataModes']['tournamentDisplay'] == 'prefix'){
		if(isset($prefixName)){$name = $prefixName." ";};
		if(isset($genderName)){$name .= $genderName." ";};
		if(isset($materialName)){$name .= $materialName." ";};
		$name .= $weaponName;
		if(isset($suffixName)){$name .= " ".$suffixName;};
	} else {
		$name = $weaponName;
		if(isset($prefixName) || isset($genderName) || isset($materialName)){
			$name .= " -";
			if(isset($prefixName)){$name .= " ".$prefixName;};
			if(isset($genderName)){$name .= " ".$genderName;};
			if(isset($materialName)){$name .= " ".$materialName;};
		}
	}
		
	return $name;
	
}

/******************************************************************************/

function getTournamentRoster($tournamentID = null, $sortType = null, $excluded = null){
// returns a sorted array of all fighters in a tournament
// indexed by rosterID	
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getTournamentRoster()");
		return;
	}
	
	$excludeTheDiscounted = null;
	/*
	if($excluded == 'ignore'){
		$excludeTheDiscounted = "AND eventTournamentRoster.participantStatus != 'ignore'";
	}*/
	
	$orderName = NAME_MODE;
	
	if($sortType == 'school'){
		$sortString = "ORDER BY (CASE WHEN schoolShortName='' then 1 ELSE 0 END), schoolShortName ASC, {$orderName} ASC";
	} else {
		$sortString = "ORDER BY systemRoster.{$orderName}";
	}

	$sql = "SELECT eventRoster.rosterID, systemSchools.schoolShortName, 
			systemSchools.schoolAbreviation, eventTournamentRoster.ignorePoolMatches, 
			eventTournamentRoster.removeFromFinals
			FROM eventTournamentRoster
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN systemRoster USING(systemRosterID)
			INNER JOIN systemSchools ON eventRoster.schoolID = systemSchools.schoolID
			WHERE eventTournamentRoster.tournamentID = {$tournamentID}
			{$excludeTheDiscounted}
			{$sortString}";	

	if($sortType == 'rosterID'){
		return mysqlQuery($sql,KEY,'rosterID');
	} else {
		return mysqlQuery($sql, ASSOC);
	}
}

/******************************************************************************/


function getTournamentPlacings($tournamentID){
	
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getTournamentPlacings()");
		return;
	}
	
	$sql = "SELECT eventRoster.rosterID, eventRoster.schoolID, placing, placeType, lowBound, highBound,
			systemSchools.schoolFullName, systemSchools.schoolBranch
			FROM eventPlacings
			INNER JOIN eventRoster ON eventRoster.rosterID = eventPlacings.rosterID
			INNER JOIN systemRoster ON eventRoster.systemRosterID = systemRoster.systemRosterID
			INNER JOIN systemSchools ON eventRoster.schoolID = systemSchools.schoolID
			WHERE eventPlacings.tournamentID = {$tournamentID}
			ORDER BY placing ASC";
	return mysqlQuery($sql, ASSOC);
	
}

/******************************************************************************/

function getTournamentsAlphabetical($eventID = null){
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"No eventID in getTournamentsAlphabetical()");
		return;
	}
	
	$sql = "SELECT tournamentID
			FROM eventTournaments
			WHERE eventID = {$eventID}";
	$result = mysqlQuery($sql, ASSOC, 'tournamentID');
	
	$namesList = [];
	foreach($result as $entry){
		$namesList[$entry['tournamentID']] = getTournamentName($entry['tournamentID']);
	}
	
	if($namesList == null){
		return null;
	}
	asort($namesList);
	return $namesList;

}

/******************************************************************************/

function getTournamentStandings($tournamentID = null, $poolSet = 1, $groupType = 'pool', $advancementsOnly = null){
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getTournamentStandings()");
		return;
	}
	
	
	if($advancementsOnly){
		$extraJoin = "INNER JOIN eventTournamentRoster ON eventTournamentRoster.rosterID = eventStandings.rosterID";
		$extraWhere = " AND eventTournamentRoster.removeFromFinals != 1
						AND eventTournamentRoster.tournamentID = {$tournamentID}";
	} else {
		$extraJoin = null;
		$extraWhere = null;
	}
	
	if($poolSet == 'all'){
		$groupSet = null;
	} else {
		if($poolSet == null){$poolSet = $_SESSION['groupSet'];}
		$groupSet = "AND eventStandings.groupSet = {$poolSet}";
	}
	
	if($groupType == 'pool'){
		$orderName = NAME_MODE;
		if(isNoPools($tournamentID)){
			$sql = "SELECT eventRoster.rosterID
					FROM eventTournamentRoster
					INNER JOIN eventRoster ON eventTournamentRoster.rosterID = eventRoster.rosterID
					INNER JOIN systemRoster ON eventRoster.systemRosterID = systemRoster.systemRosterID
					WHERE eventTournamentRoster.tournamentID = {$tournamentID}
					ORDER BY {$orderName} ASC";
		} else {
		
			$sql = "SELECT eventRoster.rosterID,
					rank, score, wins, losses, ties, pointsFor, pointsAgainst, doubles, matches,
					hitsFor, hitsAgainst, afterblowsFor, afterblowsAgainst
					FROM eventStandings
					INNER JOIN eventRoster ON eventStandings.rosterID = eventRoster.rosterID
					INNER JOIN systemRoster ON eventRoster.systemRosterID = systemRoster.systemRosterID
					{$extraJoin}
					WHERE eventStandings.tournamentID = {$tournamentID}
					AND groupType = 'pool'
					{$groupSet}
					{$extraWhere}
					ORDER BY rank ASC";
		}
		$rankedList = mysqlQuery($sql, ASSOC);
		
		if($advancementsOnly){
		// Removes and gaps in the list created by fighters who completed
		// pools but are stepping out of the finals.
			$lastRank = -1;
			foreach($rankedList as $rank => $fighter){
				$rankedList[$lastRank + 1] = $fighter;
				$lastRank ++;
			}
		}		
		
		return $rankedList;
	}
	
	if($groupType == 'final' OR $groupType == 'finals'){
			
		$poolStandings = getTournamentStandings($tournamentID, 'pool');
		$bracketInfo = getBracketInformation($tournamentID);
		
		$bracketInfo = getBracketInformation($tournamentID);
		$winnerBracketID = $bracketInfo['winner']['groupID'];
		
		
		if($bracketInfo['loser'] == null){	
			$loserBracketID = $bracketInfo['winner']['loserID'];
		} else {
			$loserBracketID = $bracketInfo['loser']['groupID'];
		}
		
		$winnerMatches = getBracketMatchesByPosition($winnerBracketID);
		$loserMatches = getBracketMatchesByPosition($loserBracketID);
		
		$standings[1]['rosterID'] = $winnerMatches[1][1]['winnerID'];
		$standings[1]['place'] = 1;
		$standings[1]['type'] = 'place';
		
		$standings[2]['rosterID'] = $winnerMatches[1][1]['loserID'];	
		$standings[2]['place'] = 2;
		$standings[2]['type'] = 'place';
		
		$standings[3]['rosterID'] = $loserMatches[1][1]['winnerID'];
		$standings[3]['place'] = 3;
		$standings[3]['type'] = 'place';
		
		$standings[4]['rosterID'] = $loserMatches[1][1]['loserID'];	
		$standings[4]['place'] = 4;
		$standings[4]['type'] = 'place';
		
		foreach($standings as $stuff){
			if($stuff['rosterID'] == null){
				return null;
			}
		}
		
		$bracketLevels = $bracketInfo['winner']['bracketLevels'];
			
		if($bracketInfo['loser'] == null){	
			for($bracketLevel = 2;$bracketLevel <= $bracketLevels; $bracketLevel++){
				foreach($bracketMatches[$bracketLevel] as $levelMatches){
					
				}
				
				$place[$placeNumber++] = $bracketMatches[$bracketLevel];
				
			}
			
		}
		
		return $standings;
	}

}

/******************************************************************************/

function getTournamentWeapon($tournamentID){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getTournamentWeapon()");
		return;
	}
	
	$sql = "SELECT tournamentWeaponID AS weaponID, tournamentType AS weaponName
			FROM eventTournaments eT
			INNER JOIN systemTournaments AS sT ON eT.tournamentWeaponID = sT.tournamentTypeID
			WHERE tournamentID = {$tournamentID}";
	return mysqlQuery($sql, SINGLE);
	
}

/******************************************************************************/

function getTournamentsFull($eventID = null){
// returns an unsorted array of all tournaments at the event, and all attributes 
// associated with each	
// indexed by tournamentID
	
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"No eventID in getTournamentsFull()");
		return;
	}
	
	$tournamentIDs = getEventTournaments($eventID);
	$metaTypes = ['weapon', 'prefix', 'ranking', 'gender', 'material'];

	
	$sql = "SELECT * FROM eventTournaments
			WHERE eventID = {$eventID}
			ORDER BY numParticipants DESC";
	$allTournamentData = mysqlQuery($sql, KEY, 'tournamentID');

	$returnVal = [];
	foreach((array)$tournamentIDs as $tournamentID){
		$returnVal[$tournamentID] = $allTournamentData[$tournamentID];
	}

	return $returnVal;
	
}

/******************************************************************************/

function getYouTube($matchID = null){
	if($matchID == null){$matchID = $_SESSION['matchID'];}
	if($matchID == null){
		setAlert(SYSTEM,"No matchID in getYouTube()");
		return;
	}
	
	$sql = "SELECT YouTubeLink
			FROM eventMatches
			WHERE matchID = {$matchID}";
	return mysqlQuery($sql, SINGLE, 'YouTubeLink');		
			
	
}

/******************************************************************************/

function isDoubleElim($tournamentID = null){
	// returns true if the tournament has a double elim bracket
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in isDoubleElim()");
		return;
	}
	
	$sql = "SELECT numFighters
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'
			AND groupNumber = 2";
	$num = mysqlQuery($sql, SINGLE, 'numFighters');
	
	if($num > 2){return true;}
	else {return false;}
	
}

/******************************************************************************/

function isPools($tournamentID = null){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return false;}

	$sql = "SELECT isPools
			FROM eventTournaments ev
			INNER JOIN systemElimTypes AS sys ON ev.tournamentElimID = sys.elimTypeID
			WHERE ev.tournamentID = {$tournamentID}";
	return (boolean)mysqlQuery($sql, SINGLE, 'isPools');
	
}

/******************************************************************************/

function isPoolSets($tournamentID){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in isPoolSets()");
		return;
	}
	
	$sql = "SELECT tournamentElimID
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	if(mysqlQuery($sql, SINGLE, 'tournamentElimID') == POOL_SETS){
		return true;
	} else {
		return false;
	}
	
}

/******************************************************************************/

function isCumulative($groupSet, $tournamentID = null){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in isCumulative()");
		return;
	}
	
	$sql = "SELECT attributeBool
			FROM eventAttributes
			WHERE tournamentID = {$tournamentID}
			AND attributeGroupSet = {$groupSet}
			AND attributeType = 'cumulative'";
	return mysqlQuery($sql, SINGLE, 'attributeBool');
}

/******************************************************************************/

function isCumulativeRounds($tournamentID){
// Checks to see if there is only one round per group set
// Used to change how rounds are displayed

	$sql = "SELECT groupSet
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'round'";
	$result = mysqlQuery($sql, ASSOC);
	
	$numInSet = [];
	foreach((array)$result as $data){
		$set = $data['groupSet'];
		if(!isset($numInSet[$set])){
			$numInSet[$set] = 0;
		}
		$numInSet[$set]++;
	}
	
	foreach($numInSet as $num){
		if($num > 1 && count($numInSet) > 1){
			return true;
		}
	}
	
	return false;
}

/******************************************************************************/

function isBrackets($tournamentID = null){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return false;}
	
	$sql = "SELECT isBrackets
			FROM eventTournaments as ev
			INNER JOIN systemElimTypes AS sys ON ev.tournamentElimID = sys.elimTypeID
			WHERE ev.tournamentID = {$tournamentID}";
	return (boolean)mysqlQuery($sql, SINGLE, 'isBrackets');
	
}

/******************************************************************************/

function isRounds($tournamentID = null){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return false;}
	
	$sql = "SELECT isRounds
			FROM eventTournaments as ev
			INNER JOIN systemElimTypes AS sys ON ev.tournamentElimID = sys.elimTypeID
			WHERE ev.tournamentID = {$tournamentID}";
	return (boolean)mysqlQuery($sql, SINGLE, 'isRounds');
	
}

/******************************************************************************/

function isTies($tournamentID = null){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return false;}
	
	$sql = "SELECT allowTies
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
			
	return (bool)mysqlQuery($sql, SINGLE, 'allowTies');
	
}
/******************************************************************************/

function isInProgress($tournamentID, $type = null){
	
	if($tournamentID == null){
		return false;
	}

	$sql = "SELECT eventStatus
			FROM eventTournaments
			INNER JOIN systemEvents USING(eventID)
			WHERE tournamentID = {$tournamentID}";
	$status = mysqlQuery($sql, SINGLE, 'eventStatus');
	if($status == 'archived'){// || isFinalized($tournamentID)){
		return false;
	}

	
	if($type == 'pool' || $type == null){
		$sql = "SELECT COUNT(*) FROM eventMatches
				INNER JOIN eventGroups USING(groupID)
				WHERE tournamentID = {$tournamentID}
				AND (matchComplete = 1 OR ignoreMatch = 1)
				AND groupType = 'pool'";
				
		$res = $GLOBALS["___mysqli_ston"]->query($sql);
		$num = $res->fetch_row();
		$numComplete = $num[0];	
		
		$sql = "SELECT COUNT(*) FROM eventMatches
				INNER JOIN eventGroups USING(groupID)
				WHERE tournamentID = {$tournamentID}
				AND (matchComplete = 0 AND ignoreMatch = 0)
				AND groupType = 'pool'";
				
		$res = $GLOBALS["___mysqli_ston"]->query($sql);
		$num = $res->fetch_row();
		$numIncomplete = $num[0];
		
		if($numIncomplete > 0 AND $numComplete > 0){
			
			return true;
		}
	}
	
	if($type == 'bracket' || $type == null){
		$sql = "SELECT COUNT(*) FROM eventMatches
				INNER JOIN eventGroups USING(groupID)
				WHERE tournamentID = {$tournamentID}
				AND (matchComplete = 1 OR ignoreMatch = 1)
				AND groupType = 'elim'
				AND bracketLevel > 1";
				
		$res = $GLOBALS["___mysqli_ston"]->query($sql);
		$num = $res->fetch_row();
		$numComplete = $num[0];	
		
		$sql = "SELECT COUNT(*) FROM eventMatches
				INNER JOIN eventGroups USING(groupID)
				WHERE tournamentID = {$tournamentID}
				AND (matchComplete = 0 AND ignoreMatch = 0)
				AND groupType = 'elim'
				AND bracketLevel > 1";
				
		$res = $GLOBALS["___mysqli_ston"]->query($sql);
		$num = $res->fetch_row();
		$numIncomplete = $num[0];
		
		if($numIncomplete > 0 AND $numComplete > 0){
			
			return true;
		}
	}
	
	if($type == 'round' || $type == null){
		
		// This only looks at complete and incomplete matches in the highest group set
		// that people have been competing in.
		$sql = "SELECT MAX(groupSet) AS maxGroupSet
				FROM eventMatches
				INNER JOIN eventGroups USING(groupID)
				WHERE tournamentID = {$tournamentID}
				AND fighter1Score IS NOT null";
		$maxGroupSet = mysqlQuery($sql, SINGLE, 'maxGroupSet');
		$rounds = getRounds($tournamentID, $maxGroupSet);

		foreach($rounds as $round){
			$groupID = $round['groupID'];
			
			$sql = "SELECT COUNT(*) AS numComplete
					FROM eventMatches
					INNER JOIN eventGroups USING(groupID)
					WHERE groupID = {$groupID}
					AND fighter1Score IS NOT null";
					
			$numComplete = mysqlQuery($sql, SINGLE, 'numComplete');
			
			
			$sql = "SELECT COUNT(*) AS numIncomplete
					FROM eventMatches
					INNER JOIN eventGroups USING(groupID)
					WHERE groupID = {$groupID}
					AND fighter1Score IS null";

			
			$numIncomplete = mysqlQuery($sql, SINGLE, 'numIncomplete');
			
			if($numIncomplete > 0 AND $numComplete > 0){
				return true;
			}
		}

	}
	
	
	
	return false;
	
}

/******************************************************************************/

function isInLosersBracket($matchID){
	if($matchID == null){
		setAlert(SYSTEM,"No matchID in isInLosersBracket()");
		return;
	}
	
	$sql = "SELECT groupNumber, bracketLevels
			FROM eventGroups
			INNER JOIN eventMatches on eventMatches.groupID = eventGroups.groupID
			WHERE matchID = {$matchID}";
	
	$group = mysqlQuery($sql, SINGLE);
	
	if($group['groupNumber'] == 2 && $group['bracketLevels'] > 1){
		return true;
	} else {
		return false;
	}
	
	
	
}

/******************************************************************************/

function isEventArchived($eventID = null){
			
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"No eventID in isEventArchived()");
		return;
	}

	$sql = "SELECT eventStatus
			FROM systemEvents
			WHERE eventID = {$eventID}";
	$result = mysqlQuery($sql, SINGLE);
	
	if($result['eventStatus'] == 'archived'){
		return true;
	} else {
		return false;
	}
	
}

/******************************************************************************/

function isEventTermsAccepted($eventID = null){

	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){return 0;}

	$sql = "SELECT termsOfUseAccepted
			FROM systemEvents
			WHERE eventID = {$eventID}";
	return (bool)mysqlQuery($sql, SINGLE, 'termsOfUseAccepted');

}

/******************************************************************************/

function isLastPiece($tournamentID){
// Determines if all pieces in a tournament have been concluded
// Looks for pieces w/o score, groups w/o matches, and sets w/o groups

	$sql = "SELECT COUNT(*) AS numIncompletes
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE fighter1Score IS NULL
			AND tournamentID = {$tournamentID}
			AND ignoreMatch != 1";
	
	$numIncompletes = mysqlQuery($sql, SINGLE, 'numIncompletes');
	
	if($numIncompletes > 0){
		return false;
	}
	
// Look for empty groups	
	$sql = "SELECT groupID
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}";
	$groups = mysqlQuery($sql, SINGLES, 'groupID');
	
	
	foreach($groups as $groupID){
		$sql = "SELECT COUNT(*) AS numMatches
				FROM eventMatches
				WHERE groupID = {$groupID}";
		$numMatches = mysqlQuery($sql, SINGLE, 'numMatches');
		
		
		// There are groups w/ no matches, ergo not complete
		if($numMatches == 0){
			return false;
		}
	}
	
// Look for sets with no groups created in them
	$sql = "SELECT numGroupSets
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$numSets = mysqlQuery($sql, SINGLE, 'numGroupSets');
	
	for($set = 1; $set <= $numSets; $set++){
		$sql = "SELECT COUNT(*) AS numGroups
				FROM eventGroups
				WHERE tournamentID = {$tournamentID}
				AND groupSet = {$set}";
		$numGroups  = mysqlQuery($sql, SINGLE, 'numGroups');
		
		// There are sets w/ no groups, ergo not complete
		if($numGroups == 0){
			return false;
		}
		
	}
	
	return true;

}

/******************************************************************************/

function isLastMatch($tournamentID){
// Check if the tournament is complete
// Used to prompt user to finalize tournament if it is.


	$sql = "SELECT COUNT(matchID) AS numIncomplete
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND matchComplete != 1
			AND ignoreMatch != 1";
	$numIncomplete = mysqlQuery($sql, SINGLE, 'numIncomplete');
	
	if($numIncomplete > 0){ return false; }
	
	// Bracket Events
	if(isBrackets($tournamentID)){
		
		$bracketInfo = getBracketInformation($tournamentID);

		if($bracketInfo == null){ 
			return false;
		} else {
			return true;
		}		
	}
	
	// Pool Sets
	if(isPoolSets($tournamentID)){
		$numSets = getNumGroupSets($tournamentID);
		for($set = 1; $set <= $numSets; $set++){
			$sql = "SELECT COUNT(*) AS numMatches
					FROM eventMatches
					INNER JOIN eventGroups USING(groupID)
					WHERE tournamentID = {$tournamentID}
					AND groupSet = {$set}";
			$numMatches = mysqlQuery($sql, SINGLE, 'numMatches');
			
			if($numMatches < 1){
				return false;
			}
			
		}
		return true;
	}
	
	return false;
	
	
}

/******************************************************************************/

function isReverseScore($tournamentID = null){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		return null;
	}

	$sql = "SELECT isReverseScore
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return (int)mysqlQuery($sql, SINGLE, 'isReverseScore');
}

/******************************************************************************/

function isNoPools($tournamentID = null, $poolSet = 1){
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in isNoPools()");
		return;
	}
	
	$sql = "SELECT systemTournaments.tournamentType
			FROM systemTournaments, eventTournaments
			WHERE eventTournaments.tournamentID = {$tournamentID}
			AND eventTournaments.tournamentRankingID = systemTournaments.tournamentTypeID";
	
	$rankingType = mysqlQuery($sql, SINGLE, 'tournamentType');
	
	if($rankingType == 'Direct Elim'){
		return true;
	} else {
		return false;
	}
	
}

/******************************************************************************/

function isFinalized($tournamentID){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return false;}
	
	$sql = "SELECT isFinalized
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$res = mysqlQuery($sql, SINGLE, 'isFinalized');
	
	if($res == 1){
		return true;
	}
	return false;
	
}

/******************************************************************************/

function isDoubleHits($tournamentID = null){
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in isDoubleHits()");
		return;
	}

	$sql = "SELECT doubleTypeID, overrideDoubleType
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$res = mysqlQuery($sql, SINGLE);

	if($res['doubleTypeID'] == FULL_AFTERBLOW && $res['overrideDoubleType'] == 0){
		return false;
	} else {
		return true;
	}

}

/******************************************************************************/

function isFullAfterblow($tournamentID = null){
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in isFullAfterblow()");
		return;
	}
	
	$sql = "SELECT doubleTypeID
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$id = mysqlQuery($sql, SINGLE, 'doubleTypeID');
	
	if($id == 3){
		return true;
	} else {
		return false;
	}
	
}

/******************************************************************************/

function isTournamentPrivate($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"Invalid tournamentID in isTournamentPrivate");
		return;
	}

	$sql = "SELECT isPrivate
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return (bool)mysqlQuery($sql, SINGLE, 'isPrivate');

}

/******************************************************************************/

function isUnconcludedMatchWarning($matchInfo){

	$sql = "SELECT count(matchID) AS numMatches
			FROM eventMatches
			WHERE groupID = {$matchInfo['groupID']}
			AND matchComplete = 0
			AND ( (matchNumber = {$matchInfo['matchNumber']} - 1)
				OR (matchNumber = {$matchInfo['matchNumber']} - 2)
				)";
			
	$numPastIncompletes = mysqlQuery($sql, SINGLE, 'numMatches');

	if($numPastIncompletes >= 2 
		&& @$_SESSION['clearOnLogOut']['ignorePastIncompletes'] != true
		&& USER_TYPE >= USER_STAFF
		&& USER_TYPE < USER_SUPER_ADMIN){
		return true;
	}

	return false;

}

/******************************************************************************/

function maxPoolSize($tournamentID = null){
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in maxPoolSize()");
		return;
	}
	
	// This is where you should fetch the max pool size for this tournament
	
	$sql = "SELECT maxPoolSize
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$maxPoolSize = mysqlQuery($sql, SINGLE, 'maxPoolSize');
	
	return $maxPoolSize;
	
	
}

/******************************************************************************/

function selectTournamentIdItem($item, $tournamentID = null){
	//returns the attribute of a specified field for a tournament
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in selectTournamentIdItem()");
		return;
	}
	
	$sql = "SELECT {$item}
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return mysqlQuery($sql, SINGLE, $item);
}

/******************************************************************************/

/******************************************************************************/

function getScoringFunctionName($tournamentID){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getRoundScoringFunctionName()");
		return;
	}
	
	$sql = "SELECT scoringFunction
			FROM systemRankings
			INNER JOIN eventTournaments USING(tournamentRankingID)
			WHERE tournamentID = {$tournamentID}";
	$name = mysqlQuery($sql, SINGLE, 'scoringFunction');
	return $name;
}

/******************************************************************************/

function getRankingFunctionName($tournamentID){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getRankingFunctionName()");
		return;
	}
	
	$sql = "SELECT rankingFunction
			FROM systemRankings
			INNER JOIN eventTournaments USING(tournamentRankingID)
			WHERE tournamentID = {$tournamentID}";
	$name = mysqlQuery($sql, SINGLE, 'rankingFunction');
	return $name;
}

/******************************************************************************/

function getRankingTypeDescriptions(){
	$sql = "SELECT tournamentRankingID, name, description
			FROM systemRankings
			ORDER BY name ASC";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function getDisplayFunctionName($tournamentID){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getDisplayFunctionName()");
		return;
	}
	
	$sql = "SELECT displayFunction
			FROM systemRankings
			INNER JOIN eventTournaments USING(tournamentRankingID)
			WHERE tournamentID = {$tournamentID}";
	$name = mysqlQuery($sql, SINGLE, 'displayFunction');
	return $name;
}

/******************************************************************************/

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
