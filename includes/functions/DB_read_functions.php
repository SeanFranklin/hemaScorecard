<?php
/*******************************************************************************
	Database Read Functions

	Functions for reading from the HEMA Scorecard database

*******************************************************************************/

/******************************************************************************/

function readOption($type, $id, $optionEnum){
	$id = (int)$id;

	switch($type){
		case 'e':
		case 'E':
			$table = 'eventEventOptions';
			$column = 'eventID';
			$optionID = (int)OPTION['E'][$optionEnum];
			break;
		case 't':
		case 'T':
			$table = 'eventTournamentOptions';
			$column = 'tournamentID';
			$optionID = (int)OPTION['T'][$optionEnum];
			break;
		case 'm':
		case 'M':
			$table = 'eventMatchOptions';
			$column = 'matchID';
			$optionID = (int)OPTION['M'][$optionEnum];
			break;
		default:
			$optionID = 0;
	}

	if($optionID == 0){
		return 0;
	}

	$sql = "SELECT optionValue
			FROM {$table}
			WHERE {$column} = {$id}
			AND optionID = {$optionID}";
	return ( (int)mysqlQuery($sql, SINGLE, 'optionValue') );
}

/******************************************************************************/

function hemaRatings_getSystemCount(){

	$formatID = (int)FORMAT_MATCH;
	$sql = "SELECT COUNT(DISTINCT(systemRosterID)) as num
			FROM eventTournamentRoster
			INNER JOIN eventTournaments USING(tournamentID)
			INNER JOIN eventRoster USING(rosterID)
			WHERE formatID = {$formatID}";
	$inSystem['total'] = (int)mysqlQuery($sql, SINGLE, 'num');

	$sql = "SELECT COUNT(DISTINCT(systemRosterID)) as num
			FROM eventTournamentRoster
			INNER JOIN eventTournaments USING(tournamentID)
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN systemRoster USING(systemRosterID)
			WHERE formatID = {$formatID}
			AND HemaRatingsID IS NOT NULL";
	$inSystem['rated'] = (int)mysqlQuery($sql, SINGLE, 'num');
	$inSystem['unrated'] = $inSystem['total'] - $inSystem['rated'];

	return $inSystem;
}

/******************************************************************************/

function hemaRatings_getUnrated(){

	$formatID = (int)FORMAT_MATCH;
	$sql = "SELECT DISTINCT(systemRosterID), sR.schoolID, countryName,
				schoolShortName, firstName
			FROM eventTournamentRoster
			INNER JOIN eventTournaments USING(tournamentID)
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN systemRoster AS sR USING(systemRosterID)
			INNER JOIN systemSchools ON sR.schoolID = systemSchools.schoolID
			INNER JOIN systemCountries USING(countryIso2)
			WHERE formatID = {$formatID}
			AND HemaRatingsID IS NULL
			ORDER BY schoolShortName ASC, firstName ASC";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function hemaRatings_GetEventInfo($eventID){
	$eventID = (int)$eventID;

	$sql = "SELECT CONCAT(eventName, ' ', eventYear) AS eventName, eventStartDate,
				countryIso2,eventProvince, eventCity,
				(	SELECT schoolFullName
					FROM systemSchools
					WHERE schoolID = organizingSchool) AS schoolName,
				socialMediaLink, photoLink, submitterName, submitterEmail,
				organizerName, organizerEmail,
				eventConform, allMatchesFought, missingMatches, notes,
				organizingSchool
			FROM systemEvents
			LEFT JOIN eventHemaRatingsInfo USING(eventID)
			LEFT JOIN eventSettings USING(eventID)
			WHERE eventID = {$eventID}";
	return mysqlQuery($sql, SINGLE);
}

/******************************************************************************/

function hemaRatings_GetEventRosterForExport($eventID){

	$eventID = (int)$eventID;

	if($eventID == 0){
		setAlert(SYSTEM,"No eventID in hemaRatings_GetEventRosterForExport()");
		return;
	}

	// The schoolID in eventRoster and systemRoste may not be the same
	// School in event is what they were at the time of the event, in
	// the system it is the school from the latest appearance
	$sql = "SELECT sys.systemRosterID, sch.schoolFullName, sch.countryIso2, sys.HemaRatingsID
			FROM eventRoster ev
			INNER JOIN systemRoster sys ON sys.systemRosterID = ev.systemRosterID
			INNER JOIN systemSchools sch ON ev.schoolID = sch.schoolID
			WHERE ev.eventID = {$eventID}";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function hemaRatings_getFighterID($systemRosterID){
	$systemRosterID = (int)$systemRosterID;

	$sql = "SELECT HemaRatingsID
			FROM systemRoster
			WHERE systemRosterID = {$systemRosterID}";
	return mysqlQuery($sql, SINGLE, 'HemaRatingsID');

}

/******************************************************************************/

function hemaRatings_getFighterIDfromRosterID($rosterID){
	$rosterID = (int)$rosterID;

	$sql = "SELECT HemaRatingsID
			FROM eventRoster
			INNER JOIN systemRoster USING(systemRosterID)
			WHERE rosterID = {$rosterID}";
	return mysqlQuery($sql, SINGLE, 'HemaRatingsID');

}

/******************************************************************************/

function hemaRatings_createEventInfoCsv($eventID, $dir = "exports/"){

// Get roster/event information
	$eventID = (int)$eventID;
	if($eventID == 0){
		setAlert(SYSTEM,"No Event ID in createRosterCsv");
		return;
	}

	$eventInfo = hemaRatings_GetEventInfo($eventID);

	$eventRoster = hemaRatings_GetEventRosterForExport($eventID);
	$fileName = "{$dir}eventInfo.csv";

// Create the CSV file
	$fp = fopen($fileName, 'w');

	foreach($eventInfo as $field => $data){
		if($field == 'organizingSchool'){
			// This is the schoolID, not the school name. Don't export this.
			continue;
		}

		$name = hemaRatings_getFieldDisplayName($field);

		fputs($fp, "{$name},{$data} ");
		fputs($fp, PHP_EOL);

	}

	fclose($fp);

	return $fileName;
}

/******************************************************************************/

function hemaRatings_createEventRosterCsv($eventID = null, $dir = "exports/"){
// Creates a .csv file with the eventRoster
// Format: | Name1 | Name2 | Result1 | Result2 | Stage of Tournament |


// Get roster/event information
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		setAlert(SYSTEM,"No Event ID in createRosterCsv");
		return;
	}

	$eventRoster = hemaRatings_GetEventRosterForExport($eventID);
	$eventName = getEventName($eventID);
	$fileName = "{$dir}fighters.csv";

// Create the CSV file
	$fp = fopen($fileName, 'w');

	foreach ($eventRoster as $fields) {

		fputs($fp, getFighterNameSystem($fields['systemRosterID'], 'first').",");
		fputs($fp, $fields['schoolFullName'].",");
		fputs($fp, $fields['countryIso2'].",");
		fputs($fp, ",");
		fputs($fp, $fields['HemaRatingsID']);

		fputs($fp, '');

		fputs($fp, PHP_EOL);
	}
	fclose($fp);

	return $fileName;
}

/******************************************************************************/

function hemaRatings_isEventInfoRequired($field){

	switch($field){
		case 'eventName':
		case 'eventStartDate':
		case 'countryIso2':
		case 'eventCity':
		case 'socialMediaLink':
		case 'photoLink':
		case 'submitterName':
		case 'submitterEmail':
		case 'eventConform':
		case 'allMatchesFought':
		case 'missingMatches':
			return true;
		default:
			return false;
	}

}

/******************************************************************************/

function hemaRatings_lockFieldUntilComplete($field){

	switch($field){
		case 'submitterName':
		case 'submitterEmail':
		case 'eventConform':
		case 'allMatchesFought':
		case 'missingMatches':
			return true;
		default:
			return false;
	}

}

/******************************************************************************/

function hemaRatings_getFieldDisplayName($field){

	switch($field){
		case 'eventName':			return 	'Event Name';		break;
		case 'eventStartDate':		return 	'Event Start Date';	break;
		case 'countryIso2':			return 	'Event Country';	break;
		case 'eventProvince':		return 	'Event State/Province';	break;
		case 'eventCity':			return 	'Event City';	break;
		case 'organizingSchool':	return 	'Organizing School';	break;
		case 'schoolName':			return 	'Organizing School';	break;
		case 'socialMediaLink':		return 	'Social Media Link';	break;
		case 'photoLink':			return 	'Photo Link';	break;
		case 'submitterName':		return 	'Submitter Name';	break;
		case 'submitterEmail':		return 	'Submitter E-mail';	break;
		case 'organizerName':		return 	'Organizer Name';	break;
		case 'organizerEmail':		return 	'Organizer E-mail';	break;
		case 'eventConform':		return 	'Does the event conform to the HEMA Ratings event criteria?';	break;
		case 'allMatchesFought':	return "Are there any fights in the submitted results that didn't happen?"; break;
		case 'missingMatches':		return 'Are there any missing fights in the data?';	break;
		case 'notes':				return 'Additional notes';	break;
		default:
			return null;
	}

}

/******************************************************************************/

function hemaRatings_isEventInfoComplete($eventID, $hemaRatingInfo = null){

	if($hemaRatingInfo == null){
		$hemaRatingInfo = hemaRatings_GetEventInfo($eventID);
	}

	foreach($hemaRatingInfo as $field => $value){
		if(hemaRatings_isEventInfoRequired($field) == false){
			continue;
		}

		if($value == null){
			return false;
		}
	}

	return true;

}

/******************************************************************************/

function ferrotas_createTournamentResultsCsv($tournamentID, $dir = "exports/"){
// Creates a .csv file with the results of all tournament matches

	$tournamentID = (int)$tournamentID;

	$bracketPlacings = [];
	$place = 1;

	$sql = "SELECT groupID, groupName
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'";
	$groups = mysqlQuery($sql, KEY, 'groupName');

	$primaryBracketID = $groups['winner']['groupID'];
	$secondaryBracketID = @$groups['loser']['groupID']; // may not exist if there is no loser bracket


	$sql = "SELECT matchID, fighter1ID, fighter2ID, winnerID, bracketPosition, bracketLevel
			FROM eventMatches
			WHERE eventMatches.groupID = {$primaryBracketID}
			AND placeholderMatchID IS NULL
			ORDER BY bracketLevel ASC, bracketPosition ASC";
	$primaryMatches = (array)mysqlQuery($sql, ASSOC);

	$sql = "SELECT matchID, fighter1ID, fighter2ID, winnerID, bracketPosition, bracketLevel
			FROM eventMatches
			WHERE eventMatches.groupID = {$secondaryBracketID}
			AND bracketLevel = 1
			AND bracketPosition = 1
			AND placeholderMatchID IS NULL";
	$bronzeMatch = (array)mysqlQuery($sql, SINGLE);

	if(isset($bronzeMatch['winnerID']) == true){
		$isBronzeMatch = true;
		$bronzeWinnerID = (int)$bronzeMatch['winnerID'];

		if($bronzeWinnerID == $bronzeMatch['fighter1ID']){

			$bronzeLoserID = $bronzeMatch['fighter2ID'];

		} elseif($bronzeWinnerID == $bronzeMatch['fighter2ID']){

			$bronzeLoserID = $bronzeMatch['fighter1ID'];

		} else {

			$bronzeLoserID = 0;
		}

	} else {
		$isBronzeMatch = false;
	}

	$numMatches = sizeof($primaryMatches);
	$losersAtLevel = [];
	foreach($primaryMatches as $index => $match){

		$winnerID = (int)$match['winnerID'];

		if($winnerID == $match['fighter1ID']){

			$loserID = $match['fighter2ID'];

		} elseif($winnerID == $match['fighter2ID']){

			$loserID = $match['fighter1ID'];

		} else {

			$loserID = 0;
		}


		if($match['bracketLevel'] == 1){

			$bracketPlacings[$place] = $winnerID;
			$place++;
			$assignedFighters[$winnerID] = true;

			$bracketPlacings[$place] = $loserID;
			$place++;
			$assignedFighters[$loserID] = true;

			if($isBronzeMatch == true){

				$bracketPlacings[$place] = $bronzeWinnerID;
				$place++;
				$assignedFighters[$bronzeWinnerID] = true;

				$bracketPlacings[$place] = $bronzeLoserID;
				$place++;
				$assignedFighters[$bronzeLoserID] = true;

			}

			continue;

		} elseif ($isBronzeMatch == true && $match['bracketLevel'] == 2){

			continue;
		}

		$losersAtLevel[] = $loserID;


		if(		isset($primaryMatches[$index+1]['bracketLevel']) == false
			|| 	$match['bracketLevel'] != $primaryMatches[$index+1]['bracketLevel']){


			$rosterIDs = implode2int($losersAtLevel);

			$sql = "SELECT rosterID
					FROM eventStandings
					WHERE rosterID IN ({$rosterIDs})
					AND tournamentID = {$tournamentID}
					ORDER BY rank ASC";
			$sortedRosterIDs = (array)mysqlQuery($sql, SINGLES, 'rosterID');

			foreach($sortedRosterIDs as $rosterID){
				$bracketPlacings[$place] = $rosterID;
				$place++;
				$assignedFighters[$rosterID] = true;
			}

			$losersAtLevel = [];
		}

	}

	$rosterIDsPlaced = implode2int($bracketPlacings);

	$sql = "SELECT rosterID
			FROM eventStandings
			WHERE rosterID NOT IN ({$rosterIDsPlaced})
			AND tournamentID = {$tournamentID}
			ORDER BY rank ASC";
	$sortedRosterIDs = (array)mysqlQuery($sql, SINGLES, 'rosterID');

	foreach($sortedRosterIDs as $rosterID){
		$bracketPlacings[$place] = $rosterID;
		$place++;
	}

// Create the CSV file
	$tournamentName = getTournamentName($tournamentID);
	$fileName = "{$dir}{$tournamentName}.csv";
	$fp = fopen($fileName, 'w');

	foreach($bracketPlacings as $place => $rosterID){

		$name = getFighterName($rosterID);
		$schoolName = getFighterSchoolName($rosterID);

		$fields = [$place, $name, $schoolName];

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

function hemaRatings_createTournamentResultsCsv($tournamentID, $dir = "exports/"){
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
	$eventID = mysqlQuery($sql, SINGLE, 'eventID');
	$eventName = getEventName($eventID);

	$fileName = "{$dir}{$tournamentName}.csv";

// Get the match results
	$sql = "SELECT matchID, winnerID, fighter1ID, fighter2ID, ignoreMatch,
				(	SELECT exchangeType
					FROM eventExchanges AS eE2
					WHERE eE2.matchID = eM.matchID
					AND (exchangeType = 'winner' OR exchangeType = 'doubleOut' OR exchangeType = 'tie')
					LIMIT 1) AS endExchangeType
			FROM eventMatches AS eM
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND placeholderMatchID IS NULL
			AND matchComplete = 1";
	$finishedMatches = mysqlQuery($sql, ASSOC);

// Create the CSV file
	$fp = fopen($fileName, 'w');

	foreach($finishedMatches as $match){

		$winnerID = (int)$match['winnerID'];

		if($winnerID == $match['fighter1ID']){
			$f1ID = $match['fighter1ID'];
			$f2ID = $match['fighter2ID'];
			$f1Result = 'Win';
			$f2Result = 'Loss';
		} elseif ($winnerID == $match['fighter2ID']){
			$f1ID = $match['fighter2ID'];
			$f2ID = $match['fighter1ID'];
			$f1Result = 'Win';
			$f2Result = 'Loss';
		} else {
			$f1ID = $match['fighter1ID'];
			$f2ID = $match['fighter2ID'];

			if($match['endExchangeType'] == 'doubleOut'){
				$f1Result = 'Loss';
			} else {
				$f1Result = 'Draw';
			}

			$f2Result = $f1Result;
		}

		$fighter1 = getFighterName($f1ID, null, 'first');
		$fighter2 = getFighterName($f2ID, null, 'first');



		$stageName = (string)getMatchStageName($match['matchID']);

		if((int)$match['ignoreMatch'] == 1){
			$stageName = "!! Attention !! Excluded From Scoring Calculations ".$stageName;
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

function isPartOfMetaTournament($componentTournamentID){

	$componentTournamentID = (int)$componentTournamentID;

	$sql = "SELECT COUNT(*) as numRet
			FROM eventComponentTournaments
			WHERE componentTournamentID = {$componentTournamentID}";

	return (bool)mysqlQuery($sql, SINGLE, 'numRet');

}

/******************************************************************************/

function isMetaTournamentRosterManual($metaTournamentID){

	$metaTournamentID = (int)$metaTournamentID;

	$sql = "SELECT COUNT(*) AS numRoster
			FROM eventTournamentComponents
			WHERE metaTournamentID = {$metaTournamentID}
			AND useRoster = 1";
	$rosterAuto = (bool)mysqlQuery($sql, SINGLE, 'numRoster');

	if($rosterAuto == true){
		return false;
	} else {
		return true;
	}

}

/******************************************************************************/

function getTournamentPoolMatchLimit($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT limitPoolMatches, maxPoolSize
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$res = mysqlQuery($sql, SINGLE);

	$maxMatches = (int)$res['limitPoolMatches'];

	if( $maxMatches == 0 || $maxMatches >= (int)$res['maxPoolSize']){
		return 0;
	} else {
		return $maxMatches;
	}
}

/******************************************************************************/

function getGroupTournamentID($groupID){

	$groupID = (int)$groupID;
	$sql = "SELECT tournamentID
			FROM eventGroups
			WHERE groupID = {$groupID}";
	return (int)mysqlQuery($sql, SINGLE, 'tournamentID');


}

/******************************************************************************/

function isTeamLogic($tournamentID){

	$isTeams = isTeams($tournamentID);
	if($isTeams == false){
		return false;
	}

	$logicMode = getTournamentLogic($tournamentID);
	switch($logicMode){
		case 'team_AllVsAll':
		case 'team_Solo':
			return true;
			break;
		default:
			return false;
			break;
	}

}

/******************************************************************************/

function isSignOffRequired($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT requireSignOff
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return (bool)mysqlQuery($sql, SINGLE, 'requireSignOff');
}

/******************************************************************************/

function getMatchSignOff($matchID){

	$matchID = (int)$matchID;

	$sql = "SELECT signOff1, signOff2
			FROM eventMatches
			WHERE matchID = {$matchID}";
	return mysqlQuery($sql, SINGLE);
}


/******************************************************************************/

function getMatchStageName($matchID){
// Returns the name of the stage a match occured at.
// ie. Pools, Elims, Gold Medal Match

	$matchID = (int)$matchID;

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

function getAllPoolScores($tournamentID, $groupSet){
// returns the scores of all the pool matches in the current tournament
// indexed by matchID

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getAllPoolScores()");
		return;
	}

	$groupSet = (int)$groupSet;

	if($groupSet < 1){
		$groupSetClause = null;
	} else {
		$groupSetClause = "AND eventGroups.groupSet = {$groupSet}";
	}

	$sql = "SELECT matchID, fighter2Score, fighter1Score
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE eventGroups.tournamentID = {$tournamentID}
			{$groupSetClause}" ;

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

function getAllTournamentExchanges($tournamentID, $groupType, $groupSet){
//gets all the exchanges in a tournament by fighter

	$tournamentID = (int)$tournamentID;
	$groupSet = (int)$groupSet;

	if($tournamentID == 0){
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

	if($groupSet == 'all'){
		$groupSetClause = null;
	} else {
		$groupSetClause = "AND eventGroups.groupSet = {$groupSet}";
	}

	$sql = "SELECT exchangeID, exchangeType, scoringID, receivingID,
			scoreValue, scoreDeduction, matchID, groupID, refType
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			WHERE eventGroups.tournamentID = {$tournamentID}
			{$groupSetClause}
			{$whereClause}
			AND ignoreMatch != 1
			AND isPlaceholder = 0";
	$rawExchangeData = (array)mysqlQuery($sql, ASSOC);

	$isFullAfterblow = isFullAfterblow($tournamentID);

	$matchesForFighter = [];
	$fighterStats = [];

	foreach($rawExchangeData as $exchange){
		$matchID = $exchange['matchID'];

		$scoringID = $exchange['scoringID'];
		$receivingID = $exchange['receivingID'];

		$matchesForFighter[$scoringID][$matchID] = true;
		$matchesForFighter[$receivingID][$matchID] = true;


		$fighterStats[$scoringID]['groupID'] = $exchange['groupID'];
		$fighterStats[$receivingID]['groupID'] = $exchange['groupID'];

		switch($exchange['exchangeType']){
			case 'clean':
			case 'afterblow':
				@$fighterStats[$scoringID]['pointsFor'] += ($exchange['scoreValue']-$exchange['scoreDeduction']);
				@$fighterStats[$scoringID]['AbsPointsFor'] += $exchange['scoreValue'];
				@$fighterStats[$scoringID]['AbsPointsAgainst'] += $exchange['scoreDeduction'];
				@$fighterStats[$scoringID]['AbsPointsAwarded'] += $exchange['scoreValue'];
				@$fighterStats[$scoringID]['hitsFor']++;

				@$fighterStats[$receivingID]['pointsAgainst'] += ($exchange['scoreValue']-$exchange['scoreDeduction']);
				@$fighterStats[$receivingID]['AbsPointsFor'] += $exchange['scoreDeduction'];
				@$fighterStats[$receivingID]['AbsPointsAgainst'] += $exchange['scoreValue'];
				@$fighterStats[$receivingID]['hitsAgainst']++;

				if($exchange['exchangeType'] == 'afterblow'){
					@$fighterStats[$scoringID]['afterblowsAgainst']++;
					@$fighterStats[$receivingID]['afterblowsFor']++;

					if(($exchange['scoreValue'] == $exchange['scoreDeduction'])
						&& $isFullAfterblow){
						@$fighterStats[$scoringID]['hitsFor']--;
						@$fighterStats[$scoringID]['hitsAgainst']++;
					}

				}
				break;
			case 'double':
				@$fighterStats[$scoringID]['doubles']++;
				@$fighterStats[$receivingID]['doubles']++;
				break;
			case 'noExchange':
				@$fighterStats[$scoringID]['noExchanges']++;
				@$fighterStats[$receivingID]['noExchanges']++;
				break;
			case 'penalty':
				@$fighterStats[$scoringID]['numPenalties']++;
				@$fighterStats[$scoringID]['penaltiesAgainst'] -= $exchange['scoreValue'];
				@$fighterStats[$scoringID]['pointsFor'] += $exchange['scoreValue'];
				@$fighterStats[$receivingID]['penaltiesAgainstOpponents'] -= $exchange['scoreValue'];

				if($exchange['refType'] == PENALTY_CARD_YELLOW){
					@$fighterStats[$scoringID]['numYellowCards']++;
				}
				if($exchange['refType'] == PENALTY_CARD_RED){
					@$fighterStats[$scoringID]['numRedCards']++;
				}

				break;
			case 'winner':
				@$fighterStats[$scoringID]['wins']++;
				@$fighterStats[$receivingID]['losses']++;
				break;
			case 'doubleOut':
				@$fighterStats[$scoringID]['doubleOuts']++;
				@$fighterStats[$receivingID]['doubleOuts']++;
				break;
			case 'tie':
				@$fighterStats[$scoringID]['ties']++;
				@$fighterStats[$receivingID]['ties']++;
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
	$attributes[] = 'numYellowCards';
	$attributes[] = 'numRedCards';
	$attributes[] = 'penaltiesAgainstOpponents';
	$attributes[] = 'penaltiesAgainst';
	$attributes[] = 'wins';
	$attributes[] = 'losses';
	$attributes[] = 'doubleOuts';
	$attributes[] = 'ties';
	$attributes[] = 'AbsPointsAgainst';
	$attributes[] = 'AbsPointsFor';
	$attributes[] = 'AbsPointsAwarded';

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

function findTiedFighters($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == null){
		return;
	}
	$groupSet = (int)$_SESSION['groupSet'];

	if(isTeams($tournamentID) == false){
		$useTeams = 0;
	} elseif(isMatchesByTeam($tournamentID) == true){
		$useTeams = 1;
	} else {
		$useTeams = 0;
	}

	$rankedPools = getRankedPools($tournamentID, $groupSet);

// Load meta data on how to rank fighters
	$sql = "SELECT poolWinnersFirst,
			orderByField1, orderBySort1,
			orderByField2, orderBySort2,
			orderByField3, orderBySort3,
			orderByField4, orderBySort4
			FROM eventTournaments
			INNER JOIN systemRankings USING(tournamentRankingID)
			WHERE tournamentID = {$tournamentID}";

	$meta = mysqlQuery($sql, SINGLE);

	if($meta == null){
		return [];
	}

	$numWinners = (int)$meta['poolWinnersFirst'];
	unset($meta['poolWinnersFist']);


// Check which of the ORDER BY fields are valid/used
	if($meta['orderByField1'] == ''
		|| ($meta['orderBySort1'] != 'ASC' && $meta['orderBySort1'] != 'DESC')){

		return [];
	}

	$orderBy = "eS.{$meta['orderByField1']} = eS2.{$meta['orderByField1']}";

	if($meta['orderByField2'] != ''
		&& ($meta['orderBySort2'] == 'ASC' || $meta['orderBySort2'] == 'DESC')){
			$orderBy .= "
		AND eS.{$meta['orderByField2']} = eS2.{$meta['orderByField2']}";
	}

	if($meta['orderByField3'] != ''
		&& ($meta['orderBySort3'] == 'ASC' || $meta['orderBySort3'] == 'DESC')){
			$orderBy .= "
		AND eS.{$meta['orderByField3']} = eS2.{$meta['orderByField3']}";
	}

	if($meta['orderByField4'] != ''
		&& ($meta['orderBySort4'] == 'ASC' || $meta['orderBySort4'] == 'DESC')){
			$orderBy .= "
		AND eS.{$meta['orderByField4']} = eS2.{$meta['orderByField4']}";
	}

// Retrieve List
	$sql = "SELECT standingID, rosterID, rank,
				(
					SELECT standingID
					FROM eventStandings AS eS2
					INNER JOIN eventRoster USING(rosterID)
					WHERE eS.standingID != eS2.standingID
					AND tournamentID = {$tournamentID}
					AND groupSet = {$groupSet}
					AND isTeam = {$useTeams}
					AND {$orderBy}
					LIMIT 1
				) AS tiedStandingID
			FROM eventStandings AS eS
			INNER JOIN eventRoster USING(rosterID)
			WHERE tournamentID = {$tournamentID}
			AND groupSet = {$groupSet}
			AND isTeam = {$useTeams}
			ORDER BY rank ASC";
	$standingsWithTies = mysqlQuery($sql, ASSOC);

	$ties = [];
	foreach($standingsWithTies as $standing){
		if($standing['tiedStandingID'] != null){
			$ties[$standing['rank']] = getFighterName($standing['rosterID']);
		}
	}

	return($ties);
}

/******************************************************************************/

function getBasePointValue($tournamentID, $groupSet, $returnNulls = null){
// returns the base point value of the given set in the given tournament
// if null is provided for a tournamentID it will use the current one in session
// if null is provided for the groupSet it will return the tournament default
// $returnNulls will return the value from the eventAttributes table, even
// if it has never been set

	$tournamentID = (int)$tournamentID;
	$groupSet = (int)$groupSet;

	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getBasePointValue()");
		return;
	}

	// Check if set has it's own base value
	if($groupSet != 0){
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

function getBracketInformation($tournamentID){
// return an unsorted array of all brackets in a tournament

	$tournamentID = (int)$tournamentID;
	$bracket['elimType'] = null;

	$sql = "SELECT groupID, bracketLevels, tournamentID, numFighters, groupNumber
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'";
	$result = (array)mysqlQuery($sql, ASSOC);

	if($result == null){
		return $bracket;
	}


	foreach($result as $data){
		$bracketType = $data['groupNumber'];
		$bracket[$bracketType]['groupID'] = $data['groupID'];
		$bracket[$bracketType]['bracketLevels'] = $data['bracketLevels'];
		$bracket[$bracketType]['tournamentID'] = $data['tournamentID'];
		$bracket[$bracketType]['numFighters'] = $data['numFighters'];
	}

	$sql = "SELECT COUNT(*) AS numMatches
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND bracketLevel = 1
			AND groupNumber = 1";
	$numFinalMatches = (int)mysqlQuery($sql, SINGLE, 'numMatches');

	if($numFinalMatches == 3){

		$bracket['elimType'] = ELIM_TYPE_TRUE_DOUBLE;

	} elseif($numFinalMatches == 2){

		$bracket['elimType'] = ELIM_TYPE_LOWER_BRACKET;

	} elseif(@$bracket[BRACKET_SECONDARY]['bracketLevels'] > 1){

		$bracket['elimType'] = ELIM_TYPE_CONSOLATION;

	} else {

		// Secondary bracket may not exist if there are only 2 or 3 people in the bracket.
		$bracket[BRACKET_PRIMARY]['secondaryID'] = @$bracket[BRACKET_SECONDARY]['groupID'];
		$bracket['elimType'] = ELIM_TYPE_SINGLE;
		unset($bracket[BRACKET_SECONDARY]);

	}

	return $bracket;

}

/******************************************************************************/

function getBracketMatchesByPosition($bracketID){
// return an unsorted array of all matches in a bracket
// indexed by bracketLevel (depth in the tree)
// and bracketPosition (position on it's level)

	$bracketID = (int)$bracketID;
	if($bracketID == 0){
		setAlert(SYSTEM,"No bracketID in getBracketMatchesByPosition()");
		return;
	}

	$sql = "SELECT matchID, fighter1ID, fighter2ID, winnerID, fighter1Score,
			fighter2Score, bracketPosition, bracketLevel, locationID, matchNumber
			FROM eventMatches
			LEFT JOIN logisticsLocationsMatches USING(matchID)
			WHERE eventMatches.groupID = {$bracketID}
			AND placeholderMatchID IS NULL
			ORDER BY bracketLevel DESC, bracketPosition ASC";

	$result = (array)mysqlQuery($sql, ASSOC);

	$matches = [];
	foreach($result as $entry){

		$bracketLevel = $entry['bracketLevel'];
		$bracketPosition = $entry['bracketPosition'];

		$matches[$bracketLevel][$bracketPosition]['fighter1ID'] = $entry['fighter1ID'];
		$matches[$bracketLevel][$bracketPosition]['fighter2ID'] = $entry['fighter2ID'];
		$matches[$bracketLevel][$bracketPosition]['fighter1Score'] = $entry['fighter1Score'];
		$matches[$bracketLevel][$bracketPosition]['fighter2Score'] = $entry['fighter2Score'];
		$matches[$bracketLevel][$bracketPosition]['winnerID'] = $entry['winnerID'];
		$matches[$bracketLevel][$bracketPosition]['matchID'] = $entry['matchID'];
		$matches[$bracketLevel][$bracketPosition]['locationID'] = $entry['locationID'];

		if($entry['fighter1ID'] == $entry['winnerID'] && isset($entry['winnerID'])){
			$matches[$bracketLevel][$bracketPosition]['loserID'] = $entry['fighter2ID'];
		} else if ($entry['fighter2ID'] == $entry['winnerID'] && isset($entry['winnerID'])){
			$matches[$bracketLevel][$bracketPosition]['loserID'] = $entry['fighter1ID'];
		}

	}

	return $matches;

}

/******************************************************************************/

function getBracketPriorDoubles($matchInfo){

	$tournamentID = $matchInfo['tournamentID'];

	if(readOption('T',$matchInfo['tournamentID'],'DOUBLES_CARRY_FORWARD') == 0){
		return [];
	}

	$sql = "SELECT maxDoubleHits
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$maxDoubles = (int)mysqlQuery($sql,SINGLE, 'maxDoubleHits');

	if($maxDoubles == 0){
		return [];
	}

	$doublesLimitExceeded = [];
	$bracketLevel = (int)$matchInfo['bracketLevel'];
	$prevBracketLevel = $bracketLevel + 1;

	$fighter1ID = $matchInfo['fighter1ID'];
	$fighter2ID = $matchInfo['fighter2ID'];

	$sql = "SELECT matchID, fighter1ID, fighter2ID,
				(SELECT COUNT(*) AS numDoubles
				FROM eventExchanges AS eE2
				WHERE eE2.matchID = eM.matchID
				AND exchangeType = 'double') AS numDoubles
			FROM eventMatches AS eM
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND bracketLevel = {$prevBracketLevel}
			AND (fighter1ID = {$fighter1ID} OR fighter2ID = {$fighter1ID})";
	$lastMatchInfo = mysqlQuery($sql, SINGLE);

	$tmp = [];
	if($lastMatchInfo != null && (int)$lastMatchInfo['numDoubles'] >= $maxDoubles){
		$tmp['numDoubles'] = $lastMatchInfo['numDoubles'];
		$tmp['fighterID'] = $fighter1ID;
		if($lastMatchInfo['fighter1ID'] == $fighter1ID){
			$tmp['versusID'] = $lastMatchInfo['fighter2ID'];
		} else {
			$tmp['versusID'] = $lastMatchInfo['fighter1ID'];
		}

		$doublesLimitExceeded[] = $tmp;
	}

	$sql = "SELECT matchID, fighter1ID, fighter2ID,
				(SELECT COUNT(*) AS numDoubles
				FROM eventExchanges AS eE2
				WHERE eE2.matchID = eM.matchID
				AND exchangeType = 'double') AS numDoubles
			FROM eventMatches AS eM
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND bracketLevel = {$prevBracketLevel}
			AND (fighter1ID = {$fighter2ID} OR fighter2ID = {$fighter2ID})";
	$lastMatchInfo = mysqlQuery($sql, SINGLE);

	$tmp = [];
	if($lastMatchInfo != null && (int)$lastMatchInfo['numDoubles'] >= $maxDoubles){
		$tmp['numDoubles'] = $lastMatchInfo['numDoubles'];
		$tmp['fighterID'] = $fighter2ID;
		if($lastMatchInfo['fighter2ID'] == $fighter2ID){
			$tmp['versusID'] = $lastMatchInfo['fighter1ID'];
		} else {
			$tmp['versusID'] = $lastMatchInfo['fighter2ID'];
		}

		$doublesLimitExceeded[] = $tmp;
	}


	return($doublesLimitExceeded);
}

/******************************************************************************/

function isFinalsMatch($matchID){
// returns true if it is a finals match (gold or bronze), or the sub-match of
// a finals match.

	$matchID = (int)$matchID;
	$sql = "SELECT bracketLevel, placeholderMatchID
			FROM eventMatches
			WHERE matchID = {$matchID}";
	$res = mysqlQuery($sql, SINGLE);

	if($res == null){
		return false;
	}

	if($res['placeholderMatchID'] != null){
		$placeholderMatchID = (int)$res['placeholderMatchID'];
		$sql = "SELECT bracketLevel, placeholderMatchID
				FROM eventMatches
				WHERE matchID = {$placeholderMatchID}";
		$res = mysqlQuery($sql, SINGLE);
	}

	if($res['bracketLevel'] == 1){
		return true;
	} else {
		return false;
	}

}

/******************************************************************************/

function getColors(){
	$sql = "SELECT *
			FROM systemColors";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function getCountryList(){

	$sql = "SELECT countryIso2, countryName
			FROM systemCountries
			ORDER BY countryName ASC";
	return mysqlQuery($sql, KEY_SINGLES, 'countryIso2', 'countryName');

}

/******************************************************************************/

function getCuttingStandard($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getCuttingStandard()");
		return;
	}

	$sql = "SELECT standardID, date, qualValue, standardName
			FROM eventCutStandards
			INNER JOIN systemCutStandards USING(standardID)
			WHERE tournamentID = {$tournamentID}";
	return mysqlQuery($sql, SINGLE);

}

/******************************************************************************/

function getControlPointValue($tournamentID){

	return readOption('T', $tournamentID, 'CONTROL_POINT_VALUE');

}

/******************************************************************************/

function getAfterblowPointValue($tournamentID){

	return readOption('T', $tournamentID, 'AFTERBLOW_POINT_VALUE');

}

/******************************************************************************/

function getCumulativeScores($group1ID, $group2ID){

	$group1ID = (int)$group1ID;
	$group2ID = (int)$group2ID;

	if($group1ID == 0 || $group2ID == 0){
		setAlert(SYSTEM,"No group1ID or group2ID in getCumulativeScores()");
		return;
	}

	$sql = "SELECT fighter1ID, fighter1Score
			FROM eventMatches
			WHERE groupID = {$group2ID}
			AND ignoreMatch != 1";
	$round2Results = mysqlQuery($sql, ASSOC);

	foreach($round2Results as $result){
		$rosterID = (int)$result['fighter1ID'];
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

function getEventDates($eventID){

	$eventID = (int)$eventID;
	$sql = "SELECT eventStartDate, eventEndDate
			FROM systemEvents
			WHERE eventID = {$eventID}";
	return mysqlQuery($sql, SINGLE);

}

/******************************************************************************/

function getEventLocation($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT eventCity, eventProvince, countryName
			FROM systemEvents
			INNER JOIN systemCountries USING(countryIso2)
			WHERE eventID = {$eventID}";
	return mysqlQuery($sql, SINGLE);

}


/******************************************************************************/

function getEventDays($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT eventStartDate, eventEndDate
			FROM systemEvents
			WHERE eventID = {$eventID}";
	$sqlDates = mysqlQuery($sql, SINGLE);

	if($sqlDates == null){
		return [];
	}

	$date = new DateTime($sqlDates['eventStartDate']);

	if($sqlDates['eventEndDate'] != null){
		$endDate = new DateTime($sqlDates['eventEndDate']);
	} else {
		$endDate = $date;
	}

	if($date > $endDate){
		return [];
	}

	$dayNum = 1;
	do{
		$dayList[$dayNum] = $date->format('l');
		$date->modify('+1 day');
		$dayNum++;
	} while ($date <= $endDate);

	return $dayList;

}

/******************************************************************************/

function getDoubleTypes($tournamentID = null){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == 0){
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

function getEventEmail($eventID){

	$eventID = (int)$eventID;
	if($eventID == 0){
		setAlert(SYSTEM,"No eventID in getEventEmail");
		return;
	}

	$sql = "SELECT organizerEmail
			FROM eventSettings
			WHERE eventID = {$eventID}";
	return mysqlQuery($sql, SINGLE, 'organizerEmail');


}

/******************************************************************************/

function getEventExchanges($eventID){
// gets summary counts of all exchanges in the event

	$eventID = (int)$eventID;
	if($eventID == 0){
		setAlert(SYSTEM,"No eventID in getEventExchanes()");
		return;
	}

	$tournaments = getEventTournaments($eventID);


	$tournamentStats = [];
	foreach($tournaments as $tournamentID){

		$tournamentID = (int)$tournamentID;

		$sql = "SELECT exchangeType, scoreValue
				FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				WHERE exchangeType != 'winner'
				AND tournamentID = {$tournamentID}
				AND isPlaceholder = 0";
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
					break;
				default:
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

function getExchangeCountsByExtraInfo($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT DISTINCT(refPrefix + 100*refType + 10000*refTarget) AS asdf,
			exchangeType,
			(
				SELECT attackText
				FROM systemAttacks
				WHERE attackID = eE.refPrefix
			) AS prefix,
			(
				SELECT attackText
				FROM systemAttacks
				WHERE attackID = eE.refType
			) AS type,
			(
				SELECT attackText
				FROM systemAttacks
				WHERE attackID = eE.refTarget
			) AS target,
			(
				SELECT COUNT(*) AS numExchanges
				FROM eventExchanges AS eE2
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				WHERE tournamentID = {$tournamentID}
				AND eE.exchangeType = eE2.exchangeType
				AND ((eE.refPrefix IS NULL AND eE2.refPrefix IS NULL) OR (eE2.refPrefix = eE.refPrefix))
				AND ((eE.refType IS NULL AND eE2.refType IS NULL) OR (eE2.refType = eE.refType))
				AND ((eE.refTarget IS NULL AND eE2.refTarget IS NULL) OR (eE2.refTarget = eE.refTarget))
			) AS numExchanges
		FROM eventExchanges AS eE
		INNER JOIN eventMatches USING(matchID)
		INNER JOIN eventGroups USING(groupID)
		WHERE tournamentID = {$tournamentID}
		AND exchangeType IN ('clean','afterblow')
		ORDER BY target, type, prefix, exchangeType DESC";

	return ((array)mysqlQuery($sql, ASSOC));
}

/******************************************************************************/

function getEventIncompletes($eventID){

	$eventID = (int)$eventID;
	if($eventID == 0){
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

function getEventList($eventStatus = null, $limit = 0, $isMetaEvent = 0, $orderClause = null){
// returns an unsorted array of all events in the software
// indexed by eventID

	if($eventStatus == 'meta'){
		$isMetaEvent = 1;
	}
	$isMetaEvent = (int)$isMetaEvent;

	$limit = (int)$limit;
	if($limit == 0){
		$limitClause = "";
	} else {
		$limitClause = "LIMIT {$limit}";
	}

	if($orderClause == null){
		$orderClause = "eventEndDate DESC, eventStartDate DESC";
	}


	$publishClause = "";

	switch($eventStatus){
		case 'recent':

			$eventActiveLimit = EVENT_ACTIVE_LIMIT-1;
			$eventUpcomingLimit = EVENT_UPCOMING_LIMIT-1;

			$publishClause	= "(
						(isArchived = 1 )
					OR 	(	publishMatches = 1
							AND DATEDIFF(eventEndDate,CURDATE()) < -{$eventActiveLimit} )
					OR 	(	(publishSchedule = 1 OR publishRoster = 1  OR publishRules = 1)
							AND DATEDIFF(eventEndDate,CURDATE()) < -{$eventUpcomingLimit} )
				   )";
			break;

		case 'old':
			$publishClause	= "(
						isArchived = 1
					OR 	publishRoster = 1
					OR 	publishSchedule = 1
					OR 	publishMatches = 1
					OR 	publishRules = 1
				   )";
			break;

		case 'active':
			$publishClause	= "(
						isArchived = 0
					AND publishRoster = 1
					AND	publishSchedule = 1
					AND	publishMatches = 1
					AND	publishRules = 1
				   )";
			break;

		case 'upcoming':
			$publishClause	= "(
						isArchived = 0
					AND (		publishRoster = 1
							OR	publishSchedule = 1
							OR	publishMatches = 1
							OR	publishRules = 1)
					AND ((		publishRoster = 1
							AND	publishSchedule = 1
							AND	publishMatches = 1
							AND	publishRules = 1) = FALSE)
				   )";
			break;

		case 'hidden':
			$publishClause	= "(
						isArchived = 0
					AND publishRoster = 0
					AND	publishSchedule = 0
					AND	publishMatches = 0
					AND	publishRules = 0
				   )";
			break;

		case 'archived':
			$publishClause	= "(isArchived = 1)";
			break;

		case 'matchesVisible':
			$publishClause	= "(
						isArchived = 1
					OR	publishMatches = 1
				   )";
			break;

		case 'meta':
		default:
			// In this case we don't care. Get everything.
			$publishClause	= "(1 = 1)";
			break;
	}


	$sql = "SELECT eventID, eventName, eventYear, eventStartDate,
			eventEndDate, countryName, eventProvince, eventCity
			FROM systemEvents
			INNER JOIN systemCountries USING(countryIso2)
			LEFT JOIN eventPublication USING(eventID)
			WHERE {$publishClause}
			AND isMetaEvent = {$isMetaEvent}
			ORDER BY {$orderClause}
			{$limitClause}";

	return mysqlQuery($sql, KEY, 'eventID');

}

/******************************************************************************/

function getEventListFull($includeArchived = true){
// returns an unsorted array of all events in the software
// indexed by eventID

	if($includeArchived == false){
		$whereClause = "WHERE isArchived = 0";
	} else {
		$whereClause = "";
	}

	$sql = "SELECT systemEvents.*, countryName, organizerEmail, isArchived, termsOfUseAccepted
			FROM systemEvents
			INNER JOIN systemCountries USING(countryIso2)
			LEFT JOIN eventSettings USING(eventID)
			{$whereClause}
			ORDER BY eventStartDate DESC";
	return mysqlQuery($sql, KEY, 'eventID');

}

/******************************************************************************/

function getEventListSmall(){
// returns an unsorted array of all events in the software
// indexed by eventID

	$sql = "SELECT eventID, CONCAT(eventName, ' ', eventYear) AS name
			FROM systemEvents
			ORDER BY eventStartDate DESC";
	return mysqlQuery($sql, KEY_SINGLES, 'eventID', 'name');

}

/******************************************************************************/

function getEventListByPublication($showHidden = false, $orderBy = 'name'){

	if($showHidden == false){
		$whereClause = "WHERE isArchived = 1
						OR publishDescription = 1
						OR publishRoster = 1
						OR publishSchedule = 1
						OR publishMatches = 1
						OR publishRules = 1";
	} else {
		$whereClause = "";
	}

	if($orderBy == 'date'){
		$orderClause = "eventStartDate ASC";
	} else {
		$orderClause = "eventName ASC, eventStartDate DESC";
	}

	$sql = "SELECT eventID, eventYear, eventName, eventCity, eventProvince, countryName, eventStartDate, eventEndDate,
			IF(isArchived = 1, 'complete',
				IF(publishMatches = 1, 'active',
					IF(publishDescription = 1
						OR publishRoster = 1
						OR publishSchedule = 1
						OR publishRules = 1, 'upcoming','hidden'))) AS eventStatus

			FROM systemEvents
			INNER JOIN systemCountries USING(countryIso2)
			LEFT JOIN eventPublication USING(eventID)
			{$whereClause}
			ORDER BY {$orderClause}";
	$eventList = mysqlQuery($sql, ASSOC);

	return $eventList;
}

/******************************************************************************/

function getEventComponentTournaments($eventID, $mTournamentID){

	$eventID = (int)$eventID;
	$mTournamentID = (int)$mTournamentID;
	$formatMeta = (int)FORMAT_META;

	$sql = "SELECT tournamentID, tournamentComponentID
			FROM eventTournaments AS eT
			LEFT JOIN eventTournamentComponents AS eTC ON eT.tournamentID = eTC.componentTournamentID
				AND metaTournamentID = {$mTournamentID}
			WHERE eventID = {$eventID}
			AND formatID != {$formatMeta}";
	return mysqlQuery($sql, KEY_SINGLES, 'tournamentID', 'tournamentComponentID');
}

/******************************************************************************/

function getMetaTournamentComponents($mTournamentID, $result = null, $roster = null){

	$mTournamentID = (int)$mTournamentID;

	if($result === null){
		$resultClause = '';
	} elseif($result == false){
		$resultClause = "AND useResult = 0";
	} else {
		$resultClause = "AND useResult = 1";
	}

	if($roster === null){
		$rosterClause = '';
	} elseif($roster == false){
		$rosterClause = "AND useRoster = 0";
	} else {
		$rosterClause = "AND useRoster = 1";
	}

	$sql = "SELECT componentTournamentID AS cTournamentID, isFinalized,
					useResult, useRoster, ignoreRoster, eventID, tournamentComponentID
			FROM eventTournamentComponents eTC
			INNER JOIN eventTournaments AS eT on eTC.componentTournamentID = eT.tournamentID
			WHERE metaTournamentID = {$mTournamentID}
			{$resultClause}
			{$rosterClause}";
	$componentList = mysqlQuery($sql, ASSOC);

	return $componentList;
}

/******************************************************************************/

function getBurgeeName($burgeeID){

	$burgeeID = (int)$burgeeID;

	$sql = "SELECT burgeeName
			FROM eventBurgees
			WHERE burgeeID = {$burgeeID}";
	return (mysqlQuery($sql, SINGLE, 'burgeeName'));
}

/******************************************************************************/

function getBurgeeRankings(){

	$sql = "SELECT burgeeRankingID, rankingName, functionName
			FROM systemBurgees";
	return (mysqlQuery($sql, ASSOC));
}

/******************************************************************************/

function getBurgeeRankingParameters($burgeeRankingID){

	$paramList = [];

	switch($burgeeRankingID){
		case 1: { // Num In Top 4

			$tmp['name'] = 'Finalist';
			$tmp['type'] = 'place';
			$tmp['value'] = 4;
			$tmp['weight'] = 1;
			$tmp['priority'] = 0;
			$paramList[$tmp['priority']] = $tmp;
			break;
		}

		case 2: { //
			$tmp['name'] = 'Finalist';
			$tmp['type'] = 'place';
			$tmp['value'] = 4;
			$tmp['weight'] = 9;
			$tmp['priority'] = 0;
			$paramList[$tmp['priority']] = $tmp;

			$tmp['name'] = 'Top Third';
			$tmp['type'] = 'percent';
			$tmp['value'] = (0.34);
			$tmp['weight'] = 3;
			$tmp['priority'] = 1;
			$paramList[$tmp['priority']] = $tmp;

			$tmp['name'] = 'Top 1/2';
			$tmp['type'] = 'percent';
			$tmp['value'] = (1/2);
			$tmp['weight'] = 1;
			$tmp['priority'] = 2;
			$paramList[$tmp['priority']] = $tmp;

			break;
		}

		case 3: { // Finalist Points
			$tmp['name'] = '1st';
			$tmp['type'] = 'place';
			$tmp['value'] = 1;
			$tmp['weight'] = 4;
			$tmp['priority'] = 0;
			$paramList[$tmp['priority']] = $tmp;

			$tmp['name'] = '2nd';
			$tmp['type'] = 'place';
			$tmp['value'] = 2;
			$tmp['weight'] = 3;
			$tmp['priority'] = 1;
			$paramList[$tmp['priority']] = $tmp;

			$tmp['name'] = '3rd';
			$tmp['type'] = 'place';
			$tmp['value'] = 3;
			$tmp['weight'] = 2;
			$tmp['priority'] = 2;
			$paramList[$tmp['priority']] = $tmp;

			$tmp['name'] = '4th';
			$tmp['type'] = 'place';
			$tmp['value'] = 4;
			$tmp['weight'] = 1;
			$tmp['priority'] = 3;
			$paramList[$tmp['priority']] = $tmp;

			break;
		}

		default: {
			$paramList = [];
			break;
		}
	}

	return ($paramList);
}

/******************************************************************************/

function getEventBurgees($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT burgeeID
			FROM eventBurgees
			WHERE eventID = {$eventID}";
	return ((array)mysqlQuery($sql, SINGLES));
}

/******************************************************************************/

function getBurgeeInfo($burgeeID){

	$burgeeID = (int)$burgeeID;

	$sql = "SELECT burgeeID, eventID, burgeeRankingID, burgeeName
			FROM eventBurgees
			WHERE burgeeID = {$burgeeID}";
	$info = (array)mysqlQuery($sql, SINGLE);

	if($info != []){

		$sql = "SELECT tournamentID, tournamentID AS tID
				FROM eventBurgeeComponents
				WHERE burgeeID = {$burgeeID}";

		$info['components'] = (array)mysqlQuery($sql, KEY_SINGLES, 'tournamentID','tID');
	}

	return ($info);
}

/******************************************************************************/

function getBurgeePoints($burgeeID){


	$burgeeID = (int)$burgeeID;

	$sql = "SELECT SUM(burgeePoints) AS score, schoolID
			FROM eventBurgeePlacings
			WHERE burgeeID = {$burgeeID}
			GROUP BY schoolID
			ORDER BY score DESC";
	$burgeePoints = (array)mysqlQuery($sql, KEY, 'schoolID');

	$placingNum = 0;
	$place = 0;
	$previousScore = 0;
	$previousSchoolID = 0;

	foreach($burgeePoints as $schoolID => $placing){
		$placingNum++;

		if($placing['score'] != $previousScore){
			$burgeePoints[$schoolID]['text'] = "";
			$place = $placingNum;
		} else {
			$burgeePoints[$schoolID]['text'] = "tie";
			$burgeePoints[$previousSchoolID]['text'] = "tie";
		}

		$burgeePoints[$schoolID]['place'] = $place;
		$previousSchoolID = $schoolID;
		$previousScore = $placing['score'];
	}

	$sql = "SELECT schoolID, rosterID, tournamentID, burgeePoints, placingName
			FROM eventBurgeePlacings
			WHERE burgeeID = {$burgeeID}
			ORDER BY burgeePoints DESC";
	$placings = (array)mysqlQuery($sql, ASSOC);

	foreach($placings as $placing){
		$schoolID = $placing['schoolID'];
		$rosterID = $placing['rosterID'];

		$burgeePoints[$schoolID]['fighters'][$rosterID]['placingName'] = $placing['placingName'];
		$burgeePoints[$schoolID]['fighters'][$rosterID]['tournamentIDs'][] = $placing['tournamentID'];

	}

	return($burgeePoints);
}

/******************************************************************************/

function processBurgeeFighters($bP, $placings, $priority, $weight){

	foreach($placings as $p){

		$schoolID = $p['schoolID'];

		// This fighter has already been counted before, so we shouldn't add them to the team score.
		if(isset($bP['fighters'][$p['rosterID']]) == true){

			// Update the list of tournaments that they achieved the results at
			if($bP['fightersBySchool'][$schoolID][$p['rosterID']]['priority'] == $priority){
				$bP['fightersBySchool'][$schoolID][$p['rosterID']]['tournamentIDs'][] = (int)$p['tournamentID'];
			}

			continue;

		} else {
			$bP['fighters'][$p['rosterID']] = (int)$p['rosterID'];
		}


		$bP['fightersBySchool'][$schoolID][$p['rosterID']]['priority'] = $priority;
		$bP['fightersBySchool'][$schoolID][$p['rosterID']]['tournamentIDs'][] = (int)$p['tournamentID'];

		if(isset($bP['schools'][$schoolID]['count'][$priority]) == true){
			$bP['schools'][$schoolID]['count'][$priority] += 1;
		} else {
			$bP['schools'][$schoolID]['count'][$priority] = 1;
		}

		if(isset($bP['schools'][$schoolID]['score']) == true){
			$bP['schools'][$schoolID]['score'] += $weight;
		} else {
			$bP['schools'][$schoolID]['score'] = $weight;
		}

	}

	return ($bP);

}

/******************************************************************************/

function getIncompletComponents($mTournamentID){
	$mTournamentID = (int)$mTournamentID;

	$sql = "SELECT componentTournamentID
			FROM eventTournamentComponents
			WHERE metaTournamentID = {$mTournamentID}
			AND resultsCalculated = 0
			AND useResult = 1";
	return mysqlQuery($sql, SINGLES, 'componentTournamentID');
}

/******************************************************************************/

function getComponentGroups($mTournamentID){
	$mTournamentID = (int)$mTournamentID;

	$sql = "SELECT componentGroupID, usedComponents, numComponents
			FROM eventTournamentCompGroups
			WHERE metaTournamentID = {$mTournamentID}";

	$componentGroups = mysqlQuery($sql, KEY, 'componentGroupID');


	$sql = "SELECT componentGroupItemID, componentGroupID, componentTournamentID
			FROM eventTournamentCompGroupItems
			INNER JOIN eventTournamentComponents USING(tournamentComponentID)
			WHERE metaTournamentID = {$mTournamentID}";
	$result = mysqlQuery($sql, ASSOC);

	foreach($result AS $compGroupItem){
		$componentGroups[$compGroupItem['componentGroupID']]['items'][] = $compGroupItem['componentTournamentID'];
	}

	return $componentGroups;

}

/******************************************************************************/

function getEventName($eventID, $rawName = false){
	//return the event name in form 'Test Event 1999'

	$eventID = (int)$eventID;
	if($eventID == 0){
		return null;
	}

	$sql = "SELECT eventName, eventYear
			FROM systemEvents
			WHERE eventID = {$eventID}";

	$result = mysqlQuery($sql, SINGLE);
	if($rawName == false){
		$eventName = $result['eventName']." ".$result['eventYear'];
	} else {
		$eventName = $result['eventName'];
	}

	return $eventName;

}

/******************************************************************************/

function getEventDescription($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT description
			FROM eventDescriptions
			WHERE eventID = {$eventID}";
	return mysqlQuery($sql, SINGLE, 'description');

}

/******************************************************************************/

function isMetaEvent($eventID){
	$eventID = (int)$eventID;

	$sql = "SELECT isMetaEvent
			FROM systemEvents
			WHERE eventID = {$eventID}";
	return (bool)mysqlQuery($sql, SINGLE, 'isMetaEvent');
}

/******************************************************************************/

function getEventRoster($sortString = null, $staffInfo = false){

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
		$orderName2 = NAME_MODE_2;
		$sortString = "ORDER BY {$orderName} ASC, {$orderName2} ASC";
	}

	if($staffInfo != false){
		$sInfo = ", staffCompetency, staffHoursTarget";
		$sInfo2 = "LEFT JOIN logisticsStaffCompetency USING(rosterID)";
	} else {
		$sInfo = '';
		$sInfo2 = '';
	}

	// The schoolID in eventRoster and systemRoster may not be the same
	// School in event is what they were at the time of the event, in
	// the system it is the school from the latest appearance
	$sql = "SELECT firstName, lastName, eventRoster.schoolID,
			schoolShortName, schoolBranch, rosterID, eventID{$sInfo}
			FROM eventRoster
			INNER JOIN systemSchools USING(schoolID)
			INNER JOIN systemRoster ON eventRoster.systemRosterID = systemRoster.systemRosterID
				AND eventID = {$eventID}
			{$sInfo2}
			{$sortString}";
	$roster = mysqlQuery($sql, ASSOC);

	return $roster;

}

/******************************************************************************/

function getEventAdditionalParticipants($eventID){

	$sortString = NAME_MODE;
	$sortString2 = NAME_MODE_2;
	$eventID = (int)$eventID;

	$sql = "SELECT firstName, lastName, registrationType, additionalRosterID
			FROM eventRosterAdditional
			WHERE eventID = {$eventID}
			ORDER BY registrationType ASC, {$sortString} ASC, {$sortString2} ASC";
	$roster = mysqlQuery($sql, ASSOC);

	return $roster;
}

/******************************************************************************/

function getCheckInStatusEvent($eventID){

	$eventID = (int)$eventID;

	$sortString = NAME_MODE." ASC, ".NAME_MODE_2." ASC";

	$sql = "SELECT rosterID, eventCheckIn, eventWaiver
			FROM eventRoster
			INNER JOIN systemRoster USING(systemRosterID)
			WHERE eventID = {$eventID}
			ORDER BY {$sortString}";

	return mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function getCheckInStatusAdditional($eventID){

	$eventID = (int)$eventID;

	$sortString = NAME_MODE." ASC, ".NAME_MODE_2." ASC";

	$sql = "SELECT firstName, lastName, registrationType,
				additionalRosterID, eventWaiver, eventCheckIn
			FROM eventRosterAdditional
			WHERE eventID = {$eventID}
			ORDER BY registrationType ASC, {$sortString}";

	return mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function getEntriesByFighter($tournamentIDs){

	if($tournamentIDs == null){
		return null;
	}

	$tournamentStr = implode2int($tournamentIDs);

	$sql = "SELECT rosterID, tournamentID
			FROM  eventTournamentRoster
			WHERE tournamentID IN ({$tournamentStr},0)
			ORDER BY FIND_IN_SET(tournamentID, '{$tournamentStr}')";
	$result = mysqlQuery($sql, ASSOC);

	$entriesByFighter = [];
	foreach($result as $entry){
		$entriesByFighter[$entry['rosterID']][] = $entry['tournamentID'];
	}

	return $entriesByFighter;

}

/******************************************************************************/

function getEventTournamentNames($tournamentIDs){

	$tournamentNames = [];
	foreach($tournamentIDs as $tournamentID){
		$tournamentNames[$tournamentID] = getTournamentName((int)$tournamentID);
	}

	return $tournamentNames;

}

/******************************************************************************/

function getEventTournaments($eventID = 0){

	$eventID = (int)$eventID;

	if($eventID == 0){$eventID = (int)$_SESSION['eventID'];}
	if($eventID == 0){
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

// Sort by custom defined list
	if($_SESSION['dataModes']['tournamentSort'] == 'custom'){
		$sql = "SELECT tournamentID
				FROM eventTournaments
				LEFT JOIN eventTournamentOrder USING(tournamentID)
				WHERE eventID = {$eventID}
				ORDER BY sortOrder ASC";
		return mysqlQuery($sql, SINGLES);
	}

// Sort alphabetically
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

function getTournamentDivisions($eventID){

	$eventID = (int)$eventID;


	$sql = "SELECT divisionID, eventID, divisionName
			FROM eventTournamentDivisions
			WHERE eventID = {$eventID}";
	$divisions = (array)mysqlQuery($sql, ASSOC);

	return ($divisions);
}

/******************************************************************************/

function getTournamentDivisionItems($divisionID){

	$divisionID = (int)$divisionID;


	$sql = "SELECT divisionID, tournamentID
			FROM eventTournamentDivItems
			LEFT JOIN eventTournamentOrder USING(tournamentID)
			WHERE divisionID = {$divisionID}
			ORDER BY sortOrder ASC";
	$divisions = (array)mysqlQuery($sql, ASSOC);

	$sortedDivs = [];

	foreach($divisions as $div){
		$sortedDivs['items'][$div['tournamentID']] = $div['tournamentID'];
	}

	return ($sortedDivs);
}

/******************************************************************************/

function getSystemTournaments($eventID = 0){

	$eventID = (int)$eventID;

	$sql = "SELECT eventName, eventYear, tournamentID, eventID, tournamentWeaponID AS weaponID,
					tournamentPrefixID AS prefixID, tournamentMaterialID AS materialID,
					tournamentGenderID AS genderID
			FROM eventTournaments
			INNER JOIN systemEvents USING(eventID)
			WHERE eventID != {$eventID}
			ORDER BY eventStartDate DESC";

	$allTournaments =mysqlQuery($sql, ASSOC);

	$sql = "SELECT tournamentTypeID, tournamentType
			FROM systemTournaments";
	$types = mysqlQuery($sql, KEY_SINGLES,'tournamentTypeID','tournamentType');

	foreach($allTournaments as $tournament){
		$name =  $types[$tournament['weaponID']];


		$name2 = $types[$tournament['prefixID']];
		$name2 .=  " ".$types[$tournament['genderID']];
		$name2 .=  " ".$types[$tournament['materialID']];

		if(strlen($name2) > 2){
			$name .= " - ".$name2;
		}
		$retVal[$tournament['tournamentID']]['tournamentName'] = $name;
		$retVal[$tournament['tournamentID']]['eventName'] = $tournament['eventName']." ".$tournament['eventYear'];
	}

	return $retVal;


}

/******************************************************************************/

function getFighterMatchPenalties($matchID, $num){
	$matchID = (int)$matchID;
	$fighter = "fighter".$num."ID";

	// protect from invalid SQL query
	if($fighter != 'fighter1ID' && $fighter != 'fighter2ID'){
		return null;
	}

	$sql = "SELECT (SELECT attackText
					FROM systemAttacks sA
					WHERE sA.attackID = eE.refType) AS name,
					(SELECT attackCode
					FROM systemAttacks sA2
					WHERE sA2.attackID = eE.refType) AS card
			FROM eventExchanges eE
			INNER JOIN eventMatches USING(matchID)
			WHERE matchID = {$matchID}
			AND scoringID = {$fighter}";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function getEventPenalties($eventID, $fighterIDs = null){

	$eventID = (int)$eventID;

	$idClause  = null;
	if($fighterIDs != null)
	{
		$idClause = "AND scoringID IN (".implode2int($fighterIDs).")";

		if(readOption('E', $eventID, 'HIDE_WHITE_CARD_PENALTIES') == true){
			$idClause .= " AND eE.refType IS NOT NULL";
		}
	}

	$sql = "SELECT scoringID, tournamentID, groupID, receivingID, scoreValue,
			(SELECT attackCode
				FROM systemAttacks sA2
				WHERE sA2.attackID = eE.refType) AS card,
			(SELECT attackText
				FROM systemAttacks sA3
				WHERE sA3.attackID = eE.refType) AS cardName,
			(SELECT attackText
				FROM systemAttacks sA4
				WHERE sA4.attackID = eE.refTarget) AS action
		FROM eventExchanges AS eE
		INNER JOIN eventMatches USING(matchID)
		INNER JOIN eventGroups USING(groupID)
		INNER JOIN eventTournaments USING(tournamentID)
		WHERE eventID = {$eventID}
		AND exchangeType = 'penalty'
		{$idClause}
		ORDER BY timestamp ASC";

	$penalties = mysqlQuery($sql, ASSOC);


	$penaltiesByFighter = [];
	foreach($penalties as $penalty){

		$fighterID = $penalty['scoringID'];

		if(isset($penaltiesByFighter[$fighterID]) == false){
			$penaltiesByFighter[$fighterID]['numPenalties'] = 1;
			$penaltiesByFighter[$fighterID]['fighterID'] = $fighterID;
		} else {
			$penaltiesByFighter[$fighterID]['numPenalties']++;
		}

		$penaltiesByFighter[$fighterID]['list'][] = $penalty;
	}

	if($penaltiesByFighter != []){

		foreach($penaltiesByFighter as $key => $fighterPenalties){
			$sort1[$key] = $fighterPenalties['numPenalties'];
		}
		array_multisort($sort1, SORT_DESC, $penaltiesByFighter);

	}


	return($penaltiesByFighter);
}

/******************************************************************************/

function getEventPenaltyList($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT sR.firstName, sR.lastName, schoolFullName, sA1.attackText AS card, sA2.attackText as action,
			scoreValue, groupName, sE.eventName, tournamentID, sR2.firstName AS first2, sR2.lastName AS last2,
			eR.rosterID, eE.refType
		FROM eventExchanges AS eE
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments AS eT USING(tournamentID)
			INNER JOIN systemEvents AS sE USING(eventID)
			INNER JOIN eventRoster AS eR ON eR.rosterID = eE.scoringID
			INNER JOIN systemRoster AS sR USING(systemRosterID)
			INNER JOIN eventRoster AS eR2 ON eR2.rosterID = eE.receivingID
			INNER JOIN systemRoster AS sR2 ON eR2.systemRosterID = sR2.systemRosterID
			INNER JOIN systemSchools AS sS ON eR.schoolID = sS.schoolID
			LEFT JOIN systemAttacks as sA1 ON eE.refType = sA1.attackID
			LEFT JOIN systemAttacks as sA2 ON eE.refTarget = sA2.attackID
		WHERE exchangeType = 'penalty'
			AND eT.eventID = {$eventID}
		ORDER BY sR.lastName, sR.firstName, eventName ASC";

	$eventPenalties = mysqlQuery($sql, ASSOC);

	return ($eventPenalties);
}

/******************************************************************************/

function getPenaltyInfo($exchangeID){
	$exchangeID = (int)$exchangeID;

	$sql = "SELECT (SELECT attackText
						FROM systemAttacks sA
						WHERE sA.attackID = eE.refType) AS name,
					(SELECT attackCode
						FROM systemAttacks sA2
						WHERE sA2.attackID = eE.refType) AS card,
					(SELECT attackText
						FROM systemAttacks sA3
						WHERE sA3.attackID = eE.refTarget) AS action,
					scoringID AS rosterID
			FROM eventExchanges eE
			WHERE exchangeID = {$exchangeID}";
	return mysqlQuery($sql, SINGLE);

}

/******************************************************************************/

function getFighterExchanges($rosterID, $weaponID){

	$rosterID = (int)$rosterID;
	$weaponID = (int)$weaponID;

	// Exchanges with the fighter scoring
	$sql = "SELECT exchangeType, scoreValue
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			INNER JOIN eventRoster ON eventExchanges.scoringID = eventRoster.rosterID
			WHERE systemRosterID = {$rosterID}
			AND tournamentWeaponID = {$weaponID}
			AND isPlaceholder = 0";
	$result1 = mysqlQuery($sql, ASSOC);

	// Deliminator to note two different data sets when stepping through the result
	$r[0]['exchangeType'] = '*';
	$r[0]['scoreValue'] = '0';
	$result1 = array_merge($result1, $r);

	// Exchanges with the fighter receiving
	$sql = "SELECT exchangeType, scoreValue
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			INNER JOIN eventRoster ON receivingID = eventRoster.rosterID
			WHERE systemRosterID = {$rosterID}
			AND tournamentWeaponID = {$weaponID}
			AND isPlaceholder = 0";
	$result2 = mysqlQuery($sql, ASSOC);

	$result = array_merge($result1, $result2);

	return $result;

}

/******************************************************************************/

function getFighterInfo($rosterID){

	$rosterID = (int)$rosterID;

	$data['rosterID'] = $rosterID;
	$data['name'] = getFighterName($rosterID);

	$sql = "SELECT schoolID
			FROM eventRoster
			WHERE rosterID = {$rosterID}";
	$data['schoolID'] = mysqlQuery($sql, SINGLE, 'schoolID');
	$data['schoolName'] = getSchoolName($data['schoolID'],'long','branch');

	return $data;
}

/******************************************************************************/

function getEntryName($rosterID, $splitName = null, $nameMode = null){
// If it is a team event it returns the team name, if it is a normal event
// returns the fighter name
// THIS FUNCTION IS BASED OFF THE CURRENT TOURNAMENT IN SESSION

	if($rosterID == null){
		setAlert(SYSTEM,"No rosterID in getEntryName()");
		return;
	}

	if(IS_TEAMS){
		$name = getTeamName($rosterID, $splitName);
	} else {
		$name = getFighterName($rosterID, $splitName, $nameMode);
	}

	return $name;
}

/******************************************************************************/

function getCombatantName($rosterID, $splitName = null, $nameMode = null){
// This returns either the team or fighter name of a rosterID. It is based off
// of the team logic of the tournament. Used for individual matches which could
// be either team vs team or fighter vs fighter
// THIS FUNCTION IS BASED OFF THE CURRENT TOURNAMENT IN SESSION

	if($rosterID == null){
		setAlert(SYSTEM,"No rosterID in getEntryName()");
		return;
	}

	switch(LOGIC_MODE){
		case 'team_AllVsAll':
		case 'team_Solo':
			$name = getFighterName($rosterID, $splitName, $nameMode);
			break;
		default:
			$name = getEntryName($rosterID, $splitName, $nameMode);
			break;
	}

	return $name;
}

/******************************************************************************/

function isEntriesByTeam($tournamentID){
// Returns TRUE if it is a team event which has teams being entered

	$tournamentID = (int)$tournamentID;
	$sql = "SELECT isTeams, logicMode
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$teamInfo = mysqlQuery($sql, SINGLE);

	if($teamInfo['isTeams'] == false){
		return false;
	}


	switch($teamInfo['logicMode']){
		case 'team_AllVsAll':
			$retVal = true;
			break;
		case 'team_Solo':
			$retVal = false;
			break;
		default:
			$retVal = true;
			break;
	}

	return $retVal;

}

/******************************************************************************/

function isMatchesByTeam($tournamentID){
// Returns TRUE if it is a team event which has teams fighting teams

	$tournamentID = (int)$tournamentID;
	$sql = "SELECT isTeams, logicMode
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$teamInfo = mysqlQuery($sql, SINGLE);

	if($teamInfo['isTeams'] == false){
		return false;
	}


	switch($teamInfo['logicMode']){
		case 'team_AllVsAll':
			$retVal = false;
			break;
		case 'team_Solo':
			$retVal = false;
			break;
		default:
			$retVal = true;
			break;
	}

	return $retVal;

}

/******************************************************************************/

function getActiveFighterOnTeam($matchID, $teamID){

	$matchID = (int)$matchID;
	$teamID = (int)$teamID;

	$sql = "SELECT receivingID
			FROM eventExchanges
			WHERE matchID = {$matchID}
			AND scoringID = {$teamID}
			AND exchangeType = 'switchFighter'
			ORDER BY exchangeNumber DESC
			LIMIT 1";
	$activeFighter = (int)mysqlQuery($sql, SINGLE, 'receivingID');

	return $activeFighter;
}

/******************************************************************************/

function getTeamName($teamID, $splitName = null, $returnType = null){

	$teamID = (int)$teamID;
	if($teamID == 0){
		setAlert(SYSTEM,"No rosterID in getFighterName()");
		return;
	}

	$sql = "SELECT memberName
			FROM eventTeamRoster
			WHERE teamID = {$teamID}
			AND memberRole = 'teamName'";
	$name = mysqlQuery($sql, SINGLE, 'memberName');

	if($returnType == 'raw'){
		return $name;
	} elseif ($returnType == 'members'){
		$name = '';
	}

	if($name == ''){
		unset($name);
		$sql = "SELECT rosterID
				FROM eventTeamRoster
				WHERE teamID = {$teamID}
				AND memberRole = 'member'
				ORDER BY teamOrder ASC";
		$teamMembers = mysqlQuery($sql, SINGLES);

		if($splitName != null){
			$numMembers = count($teamMembers);
			$numToGoInTop = floor($numMembers/2);
			$name['firstName'] = '';
			$name['lastName'] = '';
		} else {
			if(NAME_MODE == 'lastName'){
			$splitChar = ';';
			} else {
				$splitChar = ',';
			}

			$name = '';
		}


		$numInTop = 0;
		foreach($teamMembers as $rosterID){

			$thisName = getFighterName($rosterID);

			if($splitName == null){
				if($name != ''){
					$name .= $splitChar." ";
				}

				$name .= $thisName;

			} else {

				if($numInTop >= $numToGoInTop){
					$index = 'lastName';
				} else {
					$index = 'firstName';
				}

				if($name[$index] != ''){
					$name[$index] .= "<BR>";
				}
				$name[$index] .= $thisName;

			}
		}

	} elseif($splitName){
		$tmp['firstName'] = $name;
		$tmp['lastName'] = '';
		$name = $tmp;

	}

	return $name;

}

/******************************************************************************/

function getFighterName($rosterID, $splitName = null, $nameMode = null, $isTeam = false){

	$rosterID = (int)$rosterID;
	if($rosterID == 0){
		return '';
	}

	if($isTeam == true){
		return getTeamName($rosterID, $splitName);
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
			$name = @$result['firstName']." ".@$result['lastName'];
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

function getFighterSchoolName($rosterID, $nameType = null, $includeBranch = null){

	$rosterID = (int)$rosterID;
	if($rosterID == 0){
		setAlert(SYSTEM,"No rosterID in getFighterName()");
		return;
	}

	$sql = "SELECT schoolFullName, schoolShortName, schoolBranch, schoolAbbreviation
			FROM eventRoster
			INNER JOIN systemSchools USING(schoolID)
			WHERE rosterID = {$rosterID}";

	$result = mysqlQuery($sql, SINGLE);

	if($result == null){
		// If there is no result get the name of the unknown school.
		$result['schoolFullName'] = '';
		$result['schoolShortName'] = '';
		$result['schoolBranch'] = '';
		$result['schoolAbbreviation'] = '';
	}

	if($nameType == 'abbreviation'){
		$schoolName = $result['schoolAbbreviation'];
	} else if($nameType == 'long' || $nameType == 'full'){
		$schoolName = $result['schoolFullName'];
	} else {
		$schoolName = $result['schoolShortName'];
	}

	if($includeBranch != null){
		if($result['schoolBranch'] != null){
			$schoolName .= ", ".$result['schoolBranch'];
		}
	}

	if($schoolName == null){$schoolName = '&nbsp';}

	return $schoolName;

}

/******************************************************************************/

function getFighterSchoolNameSystem($systemRosterID, $nameType = null, $includeBranch = null){

	$systemRosterID = (int)$systemRosterID;
	if($systemRosterID == 0){
		setAlert(SYSTEM,"No systemRosterID in getFighterName()");
		return;
	}

	$sql = "SELECT schoolFullName, schoolShortName, schoolBranch, schoolAbbreviation
			FROM systemRoster
			INNER JOIN systemSchools USING(schoolID)
			WHERE systemRosterID = {$systemRosterID}";

	$result = mysqlQuery($sql, SINGLE);

	if($nameType == 'abbreviation'){
		$schoolName = $result['schoolAbbreviation'];
	} else if($nameType == 'long' || $nameType == 'full'){
		$schoolName = $result['schoolFullName'];
	} else {
		$schoolName = $result['schoolShortName'];
	}

	if($includeBranch != null){
		if($result['schoolBranch'] != null){
			$schoolName .= ", ".$result['schoolBranch'];
		}
	}

	if($schoolName == null){$schoolName = '&nbsp';}

	return $schoolName;

}

/******************************************************************************/

function getAdditionalName($additionalRosterID){

	$additionalRosterID = (int)$additionalRosterID;

	$sql = "SELECT firstName, lastName
			FROM eventRosterAdditional
			WHERE additionalRosterID = {$additionalRosterID}";
	$result = mysqlQuery($sql, SINGLE);

	if(NAME_MODE == 'lastName' ){
		$name = $result['lastName'].", ".$result['firstName'];
	} else {
		$name = $result['firstName']." ".$result['lastName'];
	}

	return $name;

}

/******************************************************************************/

function getFighterNameSystem($systemRosterID, $nameMode = null){

	$systemRosterID = (int)$systemRosterID;
	if($systemRosterID == 0){
		return "";
	}

	$sql = "SELECT firstName, lastName
			FROM systemRoster
			WHERE systemRosterID = {$systemRosterID}";
	$result = mysqlQuery($sql, SINGLE);

	if($result == []){
		return "";
	}

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

function getSystemRosterIDbyName($firstName, $lastName){

	$sql = "SELECT systemRosterID, schoolID
			FROM systemRoster
			WHERE firstName = ?
				AND lastName = ?"; // SQL with parameters

	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	// "s" means the database expects a string
	$bind = mysqli_stmt_bind_param($stmt, "ss", $firstName, $lastName);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $systemRosterID, $schoolID);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);

	$result['systemRosterID'] = (int)$systemRosterID;
	$result['schoolID'] = (int)$schoolID;

	return $result;
}

/******************************************************************************/

function getRosterIDbySystemID($eventID, $systemRosterID){

	$eventID = (int)$eventID;
	$systemRosterID = (int)$systemRosterID;

	$sql = "SELECT rosterID
			FROM eventRoster
			WHERE eventID = {$eventID}
			AND systemRosterID = {$systemRosterID}";
	return (int)mysqlQuery($sql, SINGLE, 'rosterID');
}

/******************************************************************************/

function getSchoolIDbySystemID($systemRosterID){

	$systemRosterID = (int)$systemRosterID;

	$sql = "SELECT schoolID
			FROM systemRoster
			WHERE systemRosterID = {$systemRosterID}";
	return (int)mysqlQuery($sql, SINGLE, 'schoolID');
}

/******************************************************************************/

function getGroupName($groupID){

	$groupID = (int)$groupID;
	if($groupID == 0){
		setAlert(SYSTEM,"No groupID in getGroupName()");
		return;
	}

	$sql = "SELECT groupName
			FROM eventGroups
			WHERE groupID = {$groupID}";
	$name =  mysqlQuery($sql, SINGLE, 'groupName');


	if($name == 'winner' || $name == 'loser')
	{
		$name = "Bracket";
	}

	return ($name);
}

/******************************************************************************/

function getGroupNumber($groupID){

	$groupID = (int)$groupID;
	if($groupID == 0){
		setAlert(SYSTEM,"No groupID in getGroupNumber()");
		return;
	}

	$sql = "SELECT groupNumber
			FROM eventGroups
			WHERE groupID = {$groupID}";
	return mysqlQuery($sql, SINGLE, 'groupNumber');

}

/******************************************************************************/

function getIgnores($tournamentID, $type = '', $setNumber = 0){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getIgnores()");
		return;
	}

	$setNumber = (int)$setNumber;

	if($type == ''){
		$sql = "SELECT rosterID, ignoreAtSet, stopAtSet, soloAtSet
				FROM eventIgnores
				WHERE tournamentID = {$tournamentID}";
		return mysqlQuery($sql, KEY, 'rosterID');
	} elseif($setNumber == 0) {

		// Only use valid types
		if(!in_array($type,['ignoreAtSet','stopAtSet','soloAtSet'])){
			setAlert(SYSTEM,"Invalid $type in getIgnores()");
			return;
		}

		$sql = "SELECT rosterID, {$type}
				FROM eventIgnores
				WHERE tournamentID = {$tournamentID}
				AND {$type} > 0";
		return mysqlQuery($sql, KEY_SINGLES, 'rosterID',$type);
	} else {
		// Only use valid types
		if(!in_array($type,['ignoreAtSet','stopAtSet','soloAtSet'])){
			setAlert(SYSTEM,"Invalid $type in getIgnores()");
			return;
		}

		$sql = "SELECT rosterID
				FROM eventIgnores
				WHERE tournamentID = {$tournamentID}
				AND {$type} >= {$setNumber}";
		return mysqlQuery($sql, SINGLES);

	}
}

/******************************************************************************/

function getMatchDoubles($matchID){
// returns the number of doubles in a match

	$matchID = (int)$matchID;
	if($matchID == 0){
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

function getMatchExchanges($matchID){
// returns an unsorted 	array of all exchanges in a match

	$matchID = (int)$matchID;
	if($matchID == 0){
		setAlert(SYSTEM,"No matchID in getMatchExchanges()");
		return;
	}

	$sql = "SELECT eventExchanges.exchangeType, eventExchanges.exchangeID,
			eventExchanges.scoreValue, eventExchanges.scoreDeduction,
			eventRoster.rosterID, exchangeTime, exchangeNumber, refPrefix, refType, refTarget
			FROM eventExchanges
			INNER JOIN eventRoster ON eventRoster.rosterID = eventExchanges.scoringID
			WHERE eventExchanges.matchID = {$matchID}
			ORDER BY exchangeNumber ASC, exchangeID ASC";

	$result = mysqlQuery($sql, ASSOC);

	return $result;

}

/******************************************************************************/

function getNumMatchScoringExchanges($matchID){

	$matchID = (int)$matchID;
	$sql = "SELECT tournamentID
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE matchID = {$matchID}";
	$tournamentID = (int)mysqlQuery($sql, SINGLE, 'tournamentID');

	if(readOption('T',$tournamentID,'DOUBLES_ARE_NOT_SCORING_EXCH') == 0){
		$validExchanges = "('clean','afterblow','double')";
	} else {
		$validExchanges = "('clean','afterblow')";
	}

	$sql = "SELECT COUNT(*) AS numExchanges
			FROM eventExchanges
			WHERE matchID = {$matchID}
			AND exchangeType IN {$validExchanges}";
	$numExchanges = (int)mysqlQuery($sql, SINGLE, 'numExchanges');

	return $numExchanges;
}

/******************************************************************************/

function isTimerCountdown($tournamentID){

	$tournamentID = (int)$tournamentID;
	$timerCountdown = false;

	if($tournamentID != 0){
		$sql = "SELECT timerCountdown
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$timerCountdown = (int)mysqlQuery($sql, SINGLE, 'timerCountdown');
	}

	return $timerCountdown;

}

/******************************************************************************/

function getMatchInfo($matchID = 0){
// returns and array of information about a match:
// 	- tournamentID, matchID, fighterIDs, fighter names, match type (pool/final)
// 	- afterblow and doubles type, maximum doubles

	$matchID = (int)$matchID;
	if($matchID == 0){$matchID = $_SESSION['matchID'];}
	if($matchID == 0){
		setAlert(SYSTEM,"No matchID in getMatchInfo()");
		return;
	}

	$sql = "SELECT fighter1ID, fighter2ID, winnerID, matchID,
			fighter1score, fighter2score, matchComplete, ignoreMatch, bracketPosition,
			matchNumber, matchTime, bracketLevel, isPlaceholder, placeholderMatchID
			FROM eventMatches
			WHERE matchID = {$matchID}";
	$matchInfo = mysqlQuery($sql, SINGLE);

	$id1 = (int)$matchInfo['fighter1ID'];
	$id2 = (int)$matchInfo['fighter2ID'];

	if($id1 != 0){
		$sql = "SELECT eventRoster.schoolID
				FROM eventRoster
				INNER JOIN systemRoster ON systemRoster.systemRosterID = eventRoster.systemRosterID
				WHERE eventRoster.rosterID = {$id1}";
		$info = mysqlQuery($sql, SINGLE);

		if(@$info['schoolID'] != null){
			$matchInfo['fighter1School'] = getSchoolName($info['schoolID'],'full');
		} else {
			$matchInfo['fighter1School'] = null;
		}
	}

	if($id2 != 0){
		$sql = "SELECT eventRoster.schoolID
				FROM eventRoster
				INNER JOIN systemRoster ON systemRoster.systemRosterID = eventRoster.systemRosterID
				WHERE eventRoster.rosterID = {$id2}";
		$info = mysqlQuery($sql, SINGLE);

		if(@$info['schoolID'] != null){
			$matchInfo['fighter2School'] = getSchoolName($info['schoolID'],'full');
		} else {
			$matchInfo['fighter2School'] = null;
		}
	}



	$sql = "SELECT eventGroups.groupType, eventGroups.groupID, groupName,
				groupNumber, tournamentID, groupSet
			FROM eventGroups, eventMatches
			WHERE eventMatches.matchID = {$matchID}
			AND eventMatches.groupID = eventGroups.groupID";
	$result = mysqlQuery($sql, SINGLE);

	$matchInfo['matchType'] = $result['groupType'];
	$matchInfo['groupID'] = $result['groupID'];
	$matchInfo['groupName'] = $result['groupName'];
	$matchInfo['groupNumber'] = $result['groupNumber'];
	$matchInfo['tournamentID'] = (int)$result['tournamentID'];
	$matchInfo['groupSet'] = (int)$result['groupSet'];



	$sql = "SELECT maxDoubleHits, timeLimit, maximumPoints, maximumExchanges, maxPointSpread
			FROM eventTournaments
			WHERE tournamentID = {$matchInfo['tournamentID']}";
	$data = mysqlQuery($sql, SINGLE);

	$matchInfo['maxDoubles'] = $data['maxDoubleHits'];
	$matchInfo['timeLimit'] = $data['timeLimit'];
	$matchInfo['maximumPoints'] = $data['maximumPoints'];
	$matchInfo['maximumExchanges'] = $data['maximumExchanges'];
	$matchInfo['maxPointSpread'] = $data['maxPointSpread'];



	if($matchInfo['matchType'] == 'pool'){
		$sql = "SELECT attributeType, attributeValue
				FROM eventAttributes
				WHERE tournamentID = {$matchInfo['tournamentID']}
				AND attributeGroupSet = {$matchInfo['groupSet']}
				AND attributeType IN ('timeLimit','maximumPoints','maximumExchanges','maxPointSpread') ";
		$setInfo = (array)mysqlQuery($sql, KEY_SINGLES, 'attributeType', 'attributeValue');

		if(isset($setInfo['timeLimit']) == true){
			$matchInfo['timeLimit'] = $setInfo['timeLimit'];
		}
		if(isset($setInfo['maximumPoints']) == true){
				$matchInfo['maximumPoints'] = $setInfo['maximumPoints'];
		}
		if(isset($setInfo['maximumExchanges']) == true){
				$matchInfo['maximumExchanges'] = $setInfo['maximumExchanges'];
		}
		if(isset($setInfo['maxPointSpread']) == true){
				$matchInfo['maxPointSpread'] = $setInfo['maxPointSpread'];
		}
	}




	$sql = "SELECT MAX(exchangeID)
			FROM eventExchanges
			WHERE matchID = {$matchID}";
	$matchInfo['lastExchange'] = (int)mysqlQuery($sql, SINGLE, 'MAX(exchangeID)');

	$sql = "SELECT doubleTypeID, tournamentID
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE matchID = {$matchID}";
	$temp = mysqlQuery($sql, SINGLE);

	$matchInfo['doubleType'] = $temp['doubleTypeID'];
	$matchInfo['teamEntry'] = isMatchesByTeam($temp['tournamentID']);



	$sql = "SELECT locationID
			FROM logisticsLocationsMatches
			WHERE matchID = {$matchID}";
	$matchInfo['locationID'] = mysqlQuery($sql, SINGLE, 'locationID');

	if((int)$matchInfo['matchComplete'] == 1){
		$sql = "SELECT exchangeType
				FROM eventExchanges
				WHERE exchangeID = {$matchInfo['lastExchange']}";
		$matchInfo['endType'] = mysqlQuery($sql, SINGLE, 'exchangeType');
	} elseif ($matchInfo['ignoreMatch'] == 1){
		$matchInfo['endType'] = 'ignore';
	} else {
		$matchInfo['endType'] = null;
	}

	return $matchInfo;
}

/******************************************************************************/

function getRoundMatches($groupID){

	$groupID = (int)$groupID;
	if($groupID == 0){
		setAlert(SYSTEM,"No groupID in getRoundMatches()");
		return;
	}

	$sql = "SELECT matchID, matchNumber,fighter1ID, fighter1Score, fighter2Score, matchComplete
			FROM eventMatches
			WHERE groupID = {$groupID}
			ORDER BY matchNumber ASC";
	return mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function getPoolMatches($tournamentID, $exclude = null, $groupSet = 1){
// return an unsorted array of all pool matches and their fighters and winners
// indexed by groupID and matchID

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getMatches()");
		return;
	}

	if($exclude != 'all'){
		$exclude = "AND eventMatches.ignoreMatch != 1";
	} else {
		$exclude = null;
	}

	if($groupSet == 'all'){
		$groupSetClause = null;
	} else {
		$groupSet = (int)$groupSet;
		$groupSetClause = "AND eventGroups.groupSet = {$groupSet}";
	}

	$sql = "SELECT eventMatches.matchID, eventMatches.groupID,
			eventMatches.fighter1ID, eventMatches.fighter2ID, eventMatches.winnerID,
			eventMatches.matchNumber, eventMatches.ignoreMatch, eventMatches.matchNumber,
			eventGroups.groupName, eventMatches.matchComplete, eventGroups.groupNumber
			FROM eventMatches, eventGroups
			WHERE eventGroups.tournamentID = {$tournamentID}
			AND eventMatches.groupID = eventGroups.groupID
			AND eventGroups.groupType = 'pool'
			AND eventMatches.placeholderMatchID IS NULL
			{$groupSetClause}
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

function getItemsHiddenByFilters($tournamentID, $filters, $type = null){

	$tournamentID = (int)$tournamentID;

	$hide['group'] = [];
	$hide['match'] = [];
	$hide['roster'] = [];
	$hide['systemRoster'] = [];

// Add new filter types to this statement as they are incorporated.

	if($filters['school'] == true && isset($filters['schoolID']) == true && sizeof($filters['schoolID']) != 0){
		$schoolList = implode2int($filters['schoolID']);
		$schoolClause = "AND schoolID NOT IN ({$schoolList})";
	} else {
		$filters['school'] = false;
		$schoolClause = "";
	}


	if($schoolClause == ""){
		// add new clauses here as they come up. If there is no filter clause, don't filter anything.
		return $hide;
	}

// Check schools which should be filtered out

	if($type == 'school' || $type == null){

		$sql = "SELECT schoolID, 1 AS placeholder
				FROM eventTournamentRoster
				INNER JOIN eventRoster USING(rosterID)
				WHERE tournamentID = {$tournamentID}
				{$schoolClause}";
		$hide['school'] = (array)mysqlQuery($sql, KEY_SINGLES, 'schoolID', 'placeholder') ;
	}

// Check groups/matches which should be filtered
	if($type == 'group' || $type == 'match' || $type == null){

		$schoolClause2 = "AND (eR1.schoolID NOT IN ({$schoolList}) AND eR2.schoolID NOT IN ({$schoolList}))";
		$schoolClause3 = "AND (eR1.schoolID IN ({$schoolList}) OR eR2.schoolID IN ({$schoolList}))";

		$sql = "SELECT groupID, matchID, fighter1ID, fighter2ID, eR1.schoolID AS school1ID, eR2.schoolID AS school12D
				FROM eventMatches AS eM
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventRoster AS eR1 ON eM.fighter1ID = eR1.rosterID
				INNER JOIN eventRoster AS eR2 ON eM.fighter2ID = eR2.rosterID
				WHERE tournamentID = {$tournamentID}
				{$schoolClause2}
				ORDER BY groupNumber ASC, matchNumber ASC";
		$matchList = (array)mysqlQuery($sql, ASSOC);

		foreach($matchList as $match){
			$hide['group'][$match['groupID']] = true;
			$hide['match'][$match['matchID']] = true;
		}

		$sql = "SELECT groupID
				FROM eventMatches AS eM
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventRoster AS eR1 ON eM.fighter1ID = eR1.rosterID
				INNER JOIN eventRoster AS eR2 ON eM.fighter2ID = eR2.rosterID
				WHERE tournamentID = {$tournamentID}
				{$schoolClause3}
				GROUP BY groupID";
		$groupsToShow = (array)mysqlQuery($sql, SINGLES,'groupID');

		foreach($groupsToShow as $groupID){
			unset($hide['group'][$groupID]);
		}
	}

// Check individual fighters which should be filtered
	if($type == 'roster' || $type == null){

		$sql = "SELECT rosterID, 1 AS placeholder
				FROM eventTournamentRoster
				INNER JOIN eventRoster USING(rosterID)
				WHERE tournamentID = {$tournamentID}
				{$schoolClause}";
		$hide['roster'] = (array)mysqlQuery($sql, KEY_SINGLES, 'rosterID', 'placeholder') ;

	}

	return ($hide);
}

/******************************************************************************/

function getTournamentMatchCaps($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getMaxExchanges()");
		return;
	}

	$sql = "SELECT maximumExchanges, maximumPoints, maxPointSpread, timeLimit
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return ( mysqlQuery($sql, SINGLE) );

}

/******************************************************************************/

function getNumberOfFightsTogether($rosterID1, $rosterID2, $tournamentID){

	$rosterID1 = (int)$rosterID1;
	$rosterID2 = (int)$rosterID2;
	$tournamentID = (int)$tournamentID;

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

function getNameMode($eventID){

	$eventID = (int)$eventID;
	if($eventID == 0){ return 'firstName';}

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

function getNextMatchInfo($matchInfo){

	if($matchInfo['matchType'] == 'elim'){
		return getNextBracketMatch($matchInfo);
	} else {
		return getNextPoolMatch($matchInfo);
	}

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

	// If it is the scorekeeper then skip the next match if it has been ignored.
	if(ALLOW['EVENT_SCOREKEEP'] == true && ALLOW['EVENT_MANAGEMENT'] == false){
		$ignoreClause = "AND ignoreMatch = 0";
	} else {
		$ignoreClause = '';
	}

	$sql = "SELECT matchID
			FROM eventMatches
			WHERE groupID = {$matchInfo['groupID']}
			AND matchNumber > {$matchInfo['matchNumber']}
			AND placeholderMatchID IS NULL
			{$ignoreClause}
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

function getNextBracketMatch($matchInfo){

	if($matchInfo == null){
		setAlert(SYSTEM,"No matchInfo in getNextPoolMatch()");
		return;
	}

	if($matchInfo['matchType'] != 'elim' || (int)$matchInfo['locationID'] == 0){
		// not an error, but nothing to return
		return null;
	}

	$locationID = (int)$matchInfo['locationID'];
	$groupID = (int)$matchInfo['groupID'];
	$matchID = (int)$matchInfo['matchID'];

	$sql = "SELECT matchID, bracketLevel, bracketPosition
			FROM eventMatches AS eM
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN logisticsLocationsMatches AS lLM USING(matchID)
			WHERE groupType = 'elim'
			AND matchID != {$matchID}
			AND groupID = {$groupID}
			AND fighter1ID IS NOT NULL
			AND fighter2ID IS NOT NULL
			AND lLM.locationID = {$locationID}
			AND matchComplete = 0
			AND ignoreMatch = 0
			AND placeholderMatchID IS NULL
			ORDER BY bracketLevel DESC, bracketPosition ASC";
	$elimMatches = (array)mysqlQuery($sql, ASSOC);

	if($elimMatches == []){
		return null;
	}

	$matchID = (int)$elimMatches[0]['matchID'];

	return (getMatchInfo($matchID));

}

/******************************************************************************/

function getBracketMatchesIncomplete($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT matchID, bracketLevel, bracketPosition, lLM.locationID,
				fighter1ID, fighter2ID, ignoreMatch
			FROM eventMatches AS eM
			INNER JOIN eventGroups USING(groupID)
			LEFT JOIN logisticsLocationsMatches AS lLM USING(matchID)
			WHERE groupType = 'elim'
			AND tournamentID = {$tournamentID}
			AND matchComplete = 0
			AND placeholderMatchID IS NULL
			ORDER BY bracketLevel DESC, bracketPosition ASC";

	$elimMatches = (array)mysqlQuery($sql, ASSOC);

	$sortedMatches = [];
	foreach($elimMatches as $match){

		$locationID = (int)$match['locationID'];
		$tmp = $match;

		if((int)$match['fighter1ID'] != 0){
			$tmp['name1'] = getFighterName($match['fighter1ID']);
		} else {
			$tmp['name1'] = "&lt;unsassigned&gt;";
		}

		if((int)$match['fighter2ID'] != 0){
			$tmp['name2'] = getFighterName($match['fighter2ID']);
		} else {
			$tmp['name2'] = "&lt;unsassigned&gt;";
		}

		$sortedMatches[$locationID][] = $tmp;

	}

	return ($sortedMatches);
}

/******************************************************************************/

function getPoolMatchesLeft($matchInfo){

	$groupID = (int)$matchInfo['groupID'];
	$matchID = (int)$matchInfo['matchID'];

	$sql = "SELECT matchNumber
			FROM eventMatches
			WHERE matchID = {$matchID}";
	$retVal['matchNumber'] = (int)mysqlQuery($sql, SINGLE, 'matchNumber');

	$sql = "SELECT COUNT(*) AS numMatches
			FROM eventMatches
			WHERE groupID = {$groupID}";
	$retVal['numMatches'] = (int)mysqlQuery($sql, SINGLE, 'numMatches');

	return $retVal;

}

/******************************************************************************/

function getNumEventMatches($eventID){

	$eventID = (int)$eventID;
	if($eventID == 0){
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

function getNumSubMatches($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT numSubMatches
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return (int)mysqlQuery($sql, SINGLE, 'numSubMatches');
}

/******************************************************************************/

function isFightingStarted($tournamentID, $isMeta = false){

	$tournamentID = (int)$tournamentID;

	if($isMeta == false){
		$sql = "SELECT exchangeID
				FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				WHERE tournamentID = {$tournamentID}";
	} else {
		$sql = "SELECT standingID
				FROM eventStandings
				WHERE tournamentID = {$tournamentID}
				LIMIT 1";
	}

	return (bool)mysqlQuery($sql, SINGLE);
}

/******************************************************************************/

function getNumSubMatchesByMatch($matchID){

	$matchID = (int)$matchID;

	$sql = "SELECT COUNT(*) AS numSubMatches
			FROM eventMatches
			WHERE placeholderMatchID = {$matchID}";
	return (int)mysqlQuery($sql, SINGLE, 'numSubMatches');
}

/******************************************************************************/

function getSubMatchMode($tournamentID){

	$tournamentID = (int)$tournamentID;
	$sql = "SELECT subMatchMode
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return mysqlQuery($sql, SINGLE, 'subMatchMode');
}

/******************************************************************************/

function isFinalsSubMatches($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT matchID
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND isPlaceholder != 0
			AND bracketLevel = 1";
	$isFinalsSubMatches = (bool)mysqlQuery($sql, SINGLE);

	return $isFinalsSubMatches;

}

/******************************************************************************/

function getSubMatchParts($matchID){

	$matchID = (int)$matchID;

	$sql = "SELECT matchID, matchNumber, isPlaceholder, matchComplete, ignoreMatch
			FROM eventMatches
			WHERE matchID = {$matchID}
			OR placeholderMatchID = {$matchID}
			ORDER BY isPlaceholder DESC, matchNumber ASC";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function getNumParticipants($tournamentID){
// counts the number of participants in a tournament

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getNumParticipants()");
		return;
	}

	$event = EVENT;

	$sql = "SELECT tableID FROM {$event}_tournamentParticipants
			WHERE tournamentID = {$tournamentID}";

	return mysqlQuery($sql, NUM_ROWS);

}

/******************************************************************************/

function getNumPools($groupSet, $tournamentID){

	$groupSet = (int)$groupSet;

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
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

function getNumGroupSets($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getNumGroupSets()");
		return;
	}

	$sql = "SELECT numGroupSets
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";

	return mysqlQuery($sql, SINGLE, 'numGroupSets');

}

/******************************************************************************/

function getEventStaffPassword($eventID){

	$eventID = (int)$eventID;
	$sql = "SELECT staffPassword
			FROM eventSettings
			WHERE eventID = {$eventID}";
	return mysqlQuery($sql, SINGLE, 'staffPassword');
}

/******************************************************************************/

function getEventOrganizerPassword($eventID){

	$eventID = (int)$eventID;
	$sql = "SELECT organizerPassword
			FROM eventSettings
			WHERE eventID = {$eventID}";
	return mysqlQuery($sql, SINGLE, 'organizerPassword');
}

/******************************************************************************/

function getPoolMatchOrder($groupID, $groupRoster){

	$groupID = (int)$groupID;
	if($groupID == 0 ){
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

function getPoolTeamRosters($tournamentID, $groupSet = 1){
// returns an unsorted array of the rosters of each group
// indexed by groupID and poolPosition (order of each fighter in the pool)

	$tournamentID = (int)$tournamentID;
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in getPoolTeamRosters()");
		return;
	}

	if($groupSet == 'all'){
		$groupSetClause = null;
	} else {
		$groupSet = (int)$groupSet;
		$groupSetClause = "AND eventGroups.groupSet = {$groupSet}";
	}

	$sql = "SELECT groupID
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			{$groupSetClause}";

	$poolList = mysqlQuery($sql, SINGLES, 'groupID');

	$pools = [];
	foreach($poolList as $groupID){

		$groupID = (int)$groupID;
		if($groupID == 0){
			continue;
		}
		$pools[$groupID] = [];

		$sql = "SELECT rosterID as teamID
				FROM eventGroupRoster
				WHERE groupID = {$groupID}";
		$poolTeams = mysqlQuery($sql,SINGLES);

		foreach($poolTeams as $teamID){

			$teamID = (int)$teamID;
			if($teamID == 0){
				continue;
			}

			$sql = "SELECT rosterID
					FROM eventTeamRoster
					WHERE teamID = {$teamID}
					AND memberRole = 'member'
					ORDER BY teamOrder ASC";
			$teamRoster = mysqlQuery($sql, SINGLES);

			$pools[$groupID][$teamID] = $teamRoster;
		}
	}

	return $pools;

}

/******************************************************************************/

function getPoolRosters($tournamentID, $groupSetMode){
// returns an unsorted array of the rosters of each group
// indexed by groupID and poolPosition (order of each fighter in the pool)

	$tournamentID = (int)$tournamentID;

	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getPoolRosters()");
		return;
	}

	if($groupSetMode == 'all'){
		$groupSetClause = null;
	} else {
		$groupSet = (int)$groupSetMode;
		$groupSetClause = "AND eventGroups.groupSet = {$groupSet}";
	}

	$sql = "SELECT groupID
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			{$groupSetClause}";

	$poolList = mysqlQuery($sql, SINGLES, 'groupID');

	$pools = [];
	foreach($poolList as $groupID){
		$pools[$groupID] = [];
	}


	$sql = "SELECT poolPosition, tableID, groupID, rosterID, schoolID
			FROM eventGroupRoster
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventRoster USING(rosterID)
			WHERE eventGroups.tournamentID = {$tournamentID}
			{$groupSetClause}
			ORDER BY eventGroupRoster.poolPosition ASC";


	$result = (array)mysqlQuery($sql, ASSOC);

	foreach($result as $row){
		$groupID = $row['groupID'];
		$poolPosition = $row['poolPosition'];
		$pools[$groupID][$poolPosition]['rosterID'] = $row['rosterID'];
		$pools[$groupID][$poolPosition]['tableID'] = $row['tableID'];
		$pools[$groupID][$poolPosition]['schoolID'] = $row['schoolID'];
	}


	return $pools;
}

/******************************************************************************/

function getTournamentRosterByLocation($tournamentID){

	$tournamentID = (int)$tournamentID;
	$entryList = [];

// Entries in the pools
	$sql = "SELECT rosterID, locationID, groupID
			FROM eventGroupRoster
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}";
	$entries = mysqlQuery($sql, ASSOC);

	foreach((array)$entries as $entry){

		$tmp['fighterName'] = getFighterName($entry['rosterID']);
		$tmp['groupName'] = getGroupName($entry['groupID']);
		$tmp['location'] = logistics_getLocationName($entry['locationID']);
		$entryList[] = $tmp;

	}

// Entries in the brackets
	$sql = "SELECT fighter1ID as rosterID
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'

			UNION DISTINCT

			SELECT fighter2ID as rosterID
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'
			";
	$rosterIDs = mysqlQuery($sql, SINGLES);

	foreach($rosterIDs as $rosterID){

		$tmp['fighterName'] = getFighterName($rosterID);
		$tmp['groupName'] = "Elimination Bracket";
		$tmp['location'] = '';
		$entryList[] = $tmp;
	}

// Sort the results by name
	if($entryList != null){
		foreach($entryList as $key => $entry){
			$sort1[$key] = $entry['fighterName'];
		}
		array_multisort($sort1, SORT_ASC, $entryList);
	}

	return($entryList);
}

/******************************************************************************/

function getMatchesByLocationPool($locationID, $tournamentID, $onlyIncomplete = false){

	$locationID = (int)$locationID;
	$tournamentID = (int)$tournamentID;

	if($locationID == 0 || $tournamentID == 0){
		return [];
	}

	if($onlyIncomplete == true){
		$completeClause = "AND matchComplete = 0 AND ignoreMatch = 0";
	} else {
		$completeClause = '';
	}

	$sql = "SELECT matchID, groupID, fighter1ID, fighter2ID, winnerID,
			matchNumber, ignoreMatch, matchNumber, groupName, matchComplete, groupID
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE locationID = {$locationID}
			AND tournamentID = {$tournamentID}
			AND eventMatches.placeholderMatchID IS NULL
			AND groupType = 'pool'
			{$completeClause}
			ORDER BY groupNumber ASC, matchNumber ASC";
	$data = (array)mysqlQuery($sql, ASSOC);

	$locationMatches = [];
	foreach($data as $match){
		$locationMatches[$match['groupID']][] = $match;
	}

	return $locationMatches;

}

/******************************************************************************/

function getMatchesByLocationBracket($locationID, $tournamentID, $onlyIncomplete = false){

	$locationID = (int)$locationID;
	$tournamentID = (int)$tournamentID;

	if($locationID == 0 || $tournamentID == 0){
		return [];
	}

	if($onlyIncomplete == true){
		$completeClause = "AND matchComplete = 0 AND ignoreMatch = 0";
	} else {
		$completeClause = '';
	}

	$sql = "SELECT matchID, groupID, fighter1ID, fighter2ID, winnerID,
			matchNumber, ignoreMatch, matchNumber, groupName, matchComplete, groupID
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN logisticsLocationsMatches USING(matchID)
			WHERE logisticsLocationsMatches.locationID = {$locationID}
			AND tournamentID = {$tournamentID}
			AND eventMatches.placeholderMatchID IS NULL
			AND groupType = 'elim'
			AND (fighter1ID IS NOT NULL || fighter2ID IS NOT NULL)
			{$completeClause}
			ORDER BY bracketLevel DESC, bracketPosition DESC";
	$data = (array)mysqlQuery($sql, ASSOC);

	$locationMatches = [];
	foreach($data as $match){
		$locationMatches[0][] = $match;
	}

	return $locationMatches;
}

/******************************************************************************/

function getMatchesBySchool($eventID, $schoolIDs){

	$eventID = (int)$eventID;
	$schoolIDs = implode2int($schoolIDs);

	if($eventID == 0 || $schoolIDs == ""){
		return [];
	}

	$sql = "SELECT matchID, fighter1ID, fighter2ID, tournamentID, groupID, groupType, matchNumber,
					winnerID, fighter1Score, fighter2Score, ignoreMatch, matchComplete,
					IF(eR1.schoolID IN ({$schoolIDs}), 1, 0) AS isFighter1
			FROM eventMatches AS eM
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments AS eT USING(tournamentID)
			INNER JOIN eventRoster AS eR1 ON eM.fighter1ID = eR1.rosterID
			INNER JOIN eventRoster AS eR2 ON eM.fighter2ID = eR2.rosterID
			WHERE eT.eventID = {$eventID}
			AND (eR1.schoolID IN ({$schoolIDs}) OR eR2.schoolID IN ({$schoolIDs}))
			ORDER BY tournamentID, groupType DESC, groupID, matchNumber";


	$matchList = mysqlQuery($sql, ASSOC);

	foreach($matchList as $index => $m){

		if($m['isFighter1'] == 0 && $m['groupType'] != 'round'){

			$tmp = $m['fighter2ID'];
			$matchList[$index]['fighter2ID'] 	= $m['fighter1ID'];
			$matchList[$index]['fighter1ID'] 	= $tmp;

			$tmp = $m['fighter2Score'];
			$matchList[$index]['fighter2Score'] 	= $m['fighter1Score'];
			$matchList[$index]['fighter1Score'] 	= $tmp;
		}
	}

	return $matchList;

}

/******************************************************************************/

function getAttendanceBySystemRosterID($systemRosterID){

	$systemRosterID = (int)$systemRosterID;
	if($systemRosterID == 0){
		return;
	}

	$sql = "SELECT eventID, eventName, eventYear, eventStartDate
			FROM eventRoster
			INNER JOIN systemEvents USING(eventID)
			WHERE systemRosterID = {$systemRosterID}
			ORDER BY eventStartDate DESC";
	$events = (array)mysqlQuery($sql, ASSOC);

	$attendanceList = [];
	$eventIDs = [];
	foreach($events as $e){
		$attendanceList[$e['eventID']]['name'] = $e['eventName'];
		$attendanceList[$e['eventID']]['year'] = $e['eventYear'];
		$attendanceList[$e['eventID']]['eventStartDate'] = $e['eventStartDate'];
		$attendanceList[$e['eventID']]['matches'] = [];
		$eventIDs[].= (int)$e['eventID'];
	}

	if($eventIDs != []){
		$eventIDs = implode(",",$eventIDs);

		$sql = "SELECT matchID, fighter1ID, fighter2ID, tournamentID, groupID, groupType, matchNumber,
						winnerID, fighter1Score, fighter2Score, ignoreMatch, matchComplete, eventID,
						IF((SELECT systemRosterID
							FROM eventMatches AS eM4
							INNER JOIN eventRoster AS eR4 ON eM4.fighter1ID = eR4.rosterID
							WHERE eM.matchID = eM4.matchID) = {$systemRosterID}, 1, 0) AS isFighter1
				FROM eventMatches AS eM
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
				WHERE eventID IN($eventIDs)
				AND ((SELECT systemRosterID
						FROM eventMatches AS eM2
						INNER JOIN eventRoster AS eR2 ON eM2.fighter1ID = eR2.rosterID
						WHERE eM.matchID = eM2.matchID) = {$systemRosterID}
					OR
					 (SELECT systemRosterID
						FROM eventMatches AS eM3
						INNER JOIN eventRoster AS eR3 ON eM3.fighter2ID = eR3.rosterID
						WHERE eM.matchID = eM3.matchID) = {$systemRosterID})
				ORDER BY eventStartDate ASC, tournamentID, groupType DESC, groupID, matchNumber";
		$matches = (array)mysqlQuery($sql, ASSOC);

		foreach($matches as $m){

			if($m['isFighter1'] == 0 && $m['groupType'] != 'round'){
				$tmp 				= $m['fighter2ID'];
				$m['fighter2ID'] 	= $m['fighter1ID'];
				$m['fighter1ID'] 	= $tmp;

				$tmp 					= $m['fighter2Score'];
				$m['fighter2Score'] 	= $m['fighter1Score'];
				$m['fighter1Score'] 	= $tmp;
			}

			$attendanceList[$m['eventID']]['matches'][] = $m;
		}
	}

	return $attendanceList;

}

/******************************************************************************/

function getGroupRoster($groupID){

	$groupID = (int)$groupID;

	$sortString = NAME_MODE." ASC, ".NAME_MODE_2." ASC";

	$sql = "SELECT rosterID, groupCheckIn, groupGearCheck
			FROM eventGroupRoster
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN systemRoster USING(systemRosterID)
			WHERE groupID = {$groupID}
			ORDER BY {$sortString}";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function logistics_getScheduleBlockName($blockID, $includeTime = null){

	$blockID = (int)$blockID;

	$sql = "SELECT tournamentID, blockTitle, blockSubtitle, startTime, endTime
			FROM logisticsScheduleBlocks
			WHERE blockID = {$blockID}";
	$data = mysqlQuery($sql, SINGLE);

	if($data['tournamentID'] != null){
		$data['blockTitle'] = getTournamentName($data['tournamentID']);
	}

	$name = $data['blockTitle'];
	if($data['blockSubtitle'] != ''){
		$name .= "; ".$data['blockSubtitle'];
	}

	if($includeTime != null){

		$t1 = min2hr($data['startTime'], false);
		$t2 = min2hr($data['endTime'], false);

		switch($includeTime){
			case 'before':
				$name = "[{$t1} - {$t2}] ".$name;
				break;
			default:
				$name .= " [{$t1} - {$t2}]";
				break;
		}
	}

	return $name;

}

/******************************************************************************/

function logistics_getEventScheduleConflicts($eventID){

	$eventID = (int)$eventID;

	$ringsInfo = (array)logistics_getEventLocations($_SESSION['eventID']);
	$conflicts = [];

	foreach($ringsInfo as $ringInfo){
		$locationID = (int)$ringInfo['locationID'];

		$sql = "SELECT blockID, dayNum, locationID, startTime, endTime
				FROM logisticsLocationsBlocks
				INNER JOIN logisticsScheduleBlocks USING(blockID)
				WHERE eventID = {$eventID}
				AND locationID = {$locationID}
				ORDER BY dayNum ASC, startTime ASC";
		$data = mysqlQuery($sql, ASSOC);

		$usingDayNum = 0;

		foreach($data as $item){
			if($usingDayNum != $item['dayNum']){

				$usingDayNum = $item['dayNum'];

			} else {

				if((int)$item['startTime'] < (int)$lastEndTime){
					$tmp['locationID'] = $locationID;
					$tmp['item1'] = logistics_getScheduleBlockInfo($lastBlockID);
					$tmp['item2'] = logistics_getScheduleBlockInfo($item['blockID']);
					$conflicts[] = $tmp;

				} else {
					// No conflict. Do nothing.
				}
			}

			$lastBlockID = $item['blockID'];
			$lastEndTime = $item['endTime'];
		}
	}

	return $conflicts;

}

/******************************************************************************/

function logistics_getBlockTypes(){

	$sql = "SELECT blockTypeID, typeName
			FROM systemBlockTypes";
	return mysqlQuery($sql, KEY_SINGLES, 'blockTypeID', 'typeName');

}

/******************************************************************************/

function logistics_getTournamentsOnDay($eventID, $dayNum){

	$eventID = (int)$eventID;
	$dayNum = (int)$dayNum;

	$sql = "SELECT DISTINCT(tournamentID)
			FROM logisticsScheduleBlocks
			WHERE eventID = {$eventID}
			AND dayNum = {$dayNum}
			AND tournamentID IS NOT NULL";

	return ((array)mysqlQuery($sql, SINGLES, 'tournamentID'));

}

/******************************************************************************/

function logistics_getEventMaxTimes($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT MIN(startTime) AS startTime, MAX(endTime) AS endTime
			FROM logisticsScheduleBlocks
			WHERE eventID = {$eventID}";

	return mysqlQuery($sql, SINGLE);

}

/******************************************************************************/

function logistics_isTournamentScheduleUsed($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT COUNT(*) AS numScheduleBlocks
			FROM logisticsScheduleBlocks
			WHERE eventID = {$eventID}";

	return (bool)mysqlQuery($sql, SINGLE, 'numScheduleBlocks');
}

/******************************************************************************/

function logistics_getScheduleBlockNames($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT blockID
			FROM logisticsScheduleBlocks
			WHERE eventID = {$eventID}";
	$result = mysqlQuery($sql, SINGLES, 'scheduleID');

	if($result == null){
		return null;
	}

	foreach($result as $scheduleID){
		$names[$scheduleID] = logistics_getScheduleBlockName($scheduleID);
	}

	return $names;
}

/******************************************************************************/

function logistics_getScheduleByFighter($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT rosterID, blockID, shiftID, blockTypeID
			FROM logisticsStaffShifts
			INNER JOIN logisticsScheduleShifts AS shifts USING(shiftID)
			INNER JOIN logisticsScheduleBlocks USING(blockID)
			WHERE eventID = {$eventID}
			ORDER BY dayNum ASC, shifts.startTime ASC";
	$result = mysqlQuery($sql, ASSOC);

	if($result == null){
		return null;
	}

	foreach($result as $assignment){
		$assignList[$assignment['rosterID']][] = $assignment;
	}

	return $assignList;
}

/******************************************************************************/

function logistics_getScheduleBlockInfo($blockID){

	$blockID = (int)$blockID;

	$sql = "SELECT *
			FROM logisticsScheduleBlocks
			WHERE blockID = {$blockID}";
	$data = mysqlQuery($sql, SINGLE);

	$sql = "SELECT locationID
			FROM logisticsLocationsBlocks
			WHERE blockID = {$blockID}";
	$locations = mysqlQuery($sql, SINGLES, 'locationID');

	$data['locationIDs'] = $locations;

	$data['blockAttributes']['experience'] = "";
	$data['blockAttributes']['equipment'] = "";
	$sql = "SELECT blockAttributeType, blockAttributeText
			FROM logisticsBlockAttributes
			WHERE blockID = {$blockID}
			AND (blockAttributeType = 'experience'
				OR blockAttributeType = 'equipment')";
	$attributes = (array)mysqlQuery($sql, ASSOC);

	foreach($attributes as $att){
		$data['blockAttributes'][$att['blockAttributeType']] = $att['blockAttributeText'];
	}


	return($data);

}

/******************************************************************************/

function logistics_getScheduleItemDescription($blockID, $shiftID = null){

	$blockID = (int)$blockID;
	$shiftID = (int)$shiftID;

	$sql = "SELECT blockTitle, blockSubtitle, startTime, endTime
			FROM logisticsScheduleBlocks
			WHERE blockID = {$blockID}";
	$returnData = mysqlQuery($sql, SINGLE);

	if($shiftID != 0){
		$sql = "SELECT startTime, endTime, locationID
				FROM logisticsScheduleShifts
				WHERE shiftID = {$shiftID}";
		$shiftData = mysqlQuery($sql, SINGLE);

		$returnData['startTime'] = $shiftData['startTime'];
		$returnData['endTime'] = $shiftData['endTime'];
		$returnData['locationID'] = $shiftData['locationID'];
	} else {
		$sql = "SELECT locationID
				FROM logisticsLocationsBlocks
				WHERE blockID = {$blockID}";
		$blockLocations = mysqlQuery($sql, SINGLES, 'locationID');

		if(count($blockLocations) == 1){
			$returnData['locationID'] = $blockLocations[0];
		} else {
			$returnData['locationID'] = null;
		}
	}

	return $returnData;

}

/******************************************************************************/

function logistics_isEventInstructors($eventID){
	$eventID = (int)$eventID;

	$sql = "SELECT COUNT(*) AS numInstructors
			FROM logisticsInstructors
			WHERE eventID = {$eventID}";
	$numInstructors = (int)mysqlQuery($sql, SINGLE, 'numInstructors');

	return ((bool)$numInstructors);
}

/******************************************************************************/

function logistics_getBlockInstructors($blockID){

	$blockID = (int)$blockID;

	$logisticsRoleID = (int)LOGISTICS_ROLE_INSTRUCTOR;
	$sql = "SELECT rosterID
			FROM logisticsStaffShifts
			INNER JOIN logisticsScheduleShifts USING(shiftID)
			INNER JOIN logisticsScheduleBlocks USING(blockID)
			WHERE blockID = {$blockID}
			AND logisticsRoleID = {$logisticsRoleID}";
	$instructorList = mysqlQuery($sql, ASSOC);

	if($instructorList != null){
		foreach($instructorList as $index => $instructor){
			$instructorList[$index]['name'] = getFighterName($instructor['rosterID']);
		}
	}

	return $instructorList;

}

/******************************************************************************/

function logistics_getShiftStaff($shiftID){

	$shiftID = (int)$shiftID;

	$sql = "SELECT shiftID, staffShiftID, rosterID, logisticsRoleID, checkedIn
			FROM logisticsStaffShifts
			INNER JOIN systemLogisticsRoles USING(logisticsRoleID)
			WHERE shiftID = {$shiftID}
			ORDER BY roleSortImportance DESC";
	return mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function logistics_getShiftInfo($shiftID){

	$shiftID = (int)$shiftID;

	$sql = "SELECT locationID, shift.startTime, shift.endTime, dayNum, tournamentID
			FROM logisticsScheduleShifts as shift
			INNER JOIN logisticsScheduleBlocks USING(blockID)
			WHERE shiftID = {$shiftID}";
	return mysqlQuery($sql, SINGLE);
}

/******************************************************************************/

function logistics_getScheduleBlockShifts($blockID){

	$blockID = (int)$blockID;

	$sql = "SELECT shiftID, locationID, startTime, endTime
			FROM logisticsScheduleShifts
			WHERE blockID = {$blockID}
			ORDER BY locationID ASC, startTime ASC";
	$data = mysqlQuery($sql, ASSOC);

	$shiftList = [];
	foreach($data as $shift){
		$shiftList[$shift['startTime']][] = $shift;
	}

	return $shiftList;

}

/******************************************************************************/

function logistics_getEventStaff($eventID,$areStaff = true){

	$eventID = (int)$eventID;

	if(isset($_SESSION['staffViewMode']) == false){
		$_SESSION['staffViewMode'] = 'name-asc';
	}

	switch($_SESSION['staffViewMode']){
		case 'name-desc':
			$sortString = NAME_MODE." DESC, ".NAME_MODE_2." DESC";
			break;
		case 'comp-asc':
			$sortString = "staffCompetency ASC, ".NAME_MODE." ASC, ".NAME_MODE_2." ASC";
			break;
		case 'comp-desc':
			$sortString = "staffCompetency DESC, ".NAME_MODE." ASC, ".NAME_MODE_2." ASC";
			break;
		case 'name-asc':
		default:
			$sortString = NAME_MODE." ASC, ".NAME_MODE_2." ASC";
			break;
	}

	if($areStaff == true){
		$staffState = "!= 0";
	} else {
		$staffState = "= 0";
	}

	$sql = "SELECT rosterID, staffCompetency, staffHoursTarget
			FROM eventRoster
			INNER JOIN systemRoster USING(systemRosterID)
			LEFT JOIN logisticsStaffCompetency USING(rosterID)
			WHERE eventID = {$eventID}
			AND staffCompetency {$staffState}
			ORDER BY {$sortString}";

	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function logistics_getRoleCompetencies($eventID){

	$eventID = (int)$eventID;

	$roles = logistics_getRoles();
	$sql = "SELECT logisticsRoleID, roleCompetency
			FROM logisticsRoleCompetency
			WHERE eventID = {$eventID}";
	$setCompetencies = mysqlQuery($sql, KEY_SINGLES, 'logisticsRoleID','roleCompetency');

	foreach($roles as $role){
		$roleID = $role['logisticsRoleID'];

		if(isset($setCompetencies[$roleID])){
			$roleCompetency[$roleID] = $setCompetencies[$roleID];
		} else {
			$roleCompetency[$roleID] = 0;
		}

	}

	return $roleCompetency;

}

/******************************************************************************/

function logistics_getMatchMultipliers($eventID){

	$eventID = (int)$eventID;

	$roles = logistics_getRoles();

	$sql = "SELECT logisticsRoleID, matchMultiplier
			FROM logisticsStaffMatchMultipliers
			WHERE eventID = {$eventID}";
	$multipliers = mysqlQuery($sql, KEY_SINGLES, 'logisticsRoleID','matchMultiplier');

	foreach($roles as $role){
		$roleID = $role['logisticsRoleID'];

		if(isset($multipliers[$roleID]) == false){
			$multipliers[$roleID] = 1;
		}
	}

	return $multipliers;

}

/******************************************************************************/

function getPools($tournamentID, $groupSelection = 1){
// returns a sorted array of all pools, by pool number

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getPools()");
		return;
	}

	if($groupSelection == 'all'){
		$groupSetClause = null;
	} else {
		$groupSet = (int)$groupSelection;
		$groupSetClause = "AND eventGroups.groupSet = {$groupSet}";
	}

	$sql = "SELECT groupID, groupName, groupComplete, groupSet,
				groupNumber,numFighters, locationID
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'pool'
			{$groupSetClause}
			ORDER BY groupNumber ASC";

	$pools = mysqlQuery($sql, ASSOC);

	return $pools;
}

/******************************************************************************/

function getRankedPools($tournamentID = 0, $groupSelection = 1){
// returns a sorted array of all pools, by pool number

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getPools()");
		return;
	}

	if($groupSelection == 'all'){
		$groupSetClause = null;
	} else {
		$groupSet = (int)$groupSelection;
		$groupSetClause = "AND eventGroups.groupSet = {$groupSet}";
	}

	$sql = "SELECT groupID, groupSet, groupRank, overlapSize
			FROM eventGroupRankings
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'pool'
			{$groupSetClause}
			ORDER BY groupSet DESC, groupRank ASC";

	$pools = mysqlQuery($sql, ASSOC);

	return $pools;
}

/******************************************************************************/

function arePoolsRanked($tournamentID, $groupSet){

	$tournamentID = (int)$tournamentID;
	$groupSet = (int)$groupSet;

	$sql = "SELECT groupRankingID
			FROM eventGroupRankings
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND groupSet = {$groupSet}
			LIMIT 1";
	return (bool)mysqlQuery($sql, SINGLE);

}

/******************************************************************************/

function getGroupSetOfMatch($matchID){

	$matchID = (int)$matchID;
	if($matchID == 0){
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

	$groupID = (int)$groupID;
	if($groupID == 0){
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

function getEventDefaults($eventID){

	$eventID = (int)$eventID;

	if($eventID != 0){
		$sql = "SELECT *
				FROM eventDefaults
				WHERE eventID = {$eventID}";
		$defaults = mysqlQuery($sql, SINGLE);
	} else {
		$defaults = null;
	}

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
		$defaults['staffCompetency'] = 0;
		$defaults['addStaff'] = 0;
		$defaults['staffHoursTarget'] = 0;
		$defaults['limitStaffConflicts'] = 0;
	}

	$roleCompetency = logistics_getRoleCompetencies($eventID);

	if($roleCompetency != null){
		$defaults['roleCompetency'] = $roleCompetency;
	}

	return $defaults;

}

/******************************************************************************/

function getTournamentPoolIncompletes($tournamentID, $groupSet = 0){
// Returns the status of the tournament pools (complete etc...)

	$groupSet = (int)$groupSet;
	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getTournamentPoolIncompletes()");
		return;
	}

	if($groupSet == 0){
		$groupSetClause = null;
	} else {
		$groupSetClause = "AND eventGroups.groupSet = {$groupSet}";
	}

	$sql = "SELECT matchID
			FROM eventMatches
			INNER JOIN eventGroups ON eventGroups.groupID = eventMatches.groupID
			WHERE eventGroups.tournamentID = {$tournamentID}
			AND matchComplete != 1
			AND groupType = 'pool'
			{$groupSetClause}
			AND ignoreMatch != 1";
	$incompleteMatches = (array)mysqlQuery($sql, SINGLES, 'matchID');

	return $incompleteMatches;


}

/******************************************************************************/

function getRounds($tournamentID , $groupSet = 0){

	$groupSet = (int)$groupSet;
	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getRounds()");
		return;
	}

	if($groupSet != 0){
		$set = "AND groupSet = {$groupSet}";
	} else {
		$set = '';
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

	$groupID = (int)$groupID;
	if($groupID == 0){
		setAlert(SYSTEM,"No groupID in getRoundScores()");
		return;
	}

	$matches= getRoundMatches($groupID);
	$scores = [];

	foreach($matches as $match){
		$rosterID = $match['fighter1ID'];
		$score = max([$match['fighter1Score'],$match['fighter2Score']]);

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

	$sql = "SELECT tournamentID
			FROM eventGroups
			WHERE groupID = {$groupID}";
	$tournamentID = mysqlQuery($sql, SINGLE,'tournamentID');

	if(isset($sort1)){
		if(isReverseScore($tournamentID) == REVERSE_SCORE_NO){
			array_multisort($sort1, SORT_DESC, $scores);
		} else {
			array_multisort($sort1, SORT_ASC, $scores);
		}
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

	$ignores = getIgnores($tournamentID);

	if($groupSet == 1 && $groupNumber == 1){
		$r = getTournamentCompetitors($tournamentID);
		foreach($r as $index => $fighter){
			if(@$ignores[$fighter['rosterID']]['stopAtSet'] > 0){
				// the array value not existing is logically the same as being zero
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

			if(isset($ignores[$rosterID])
				&& $groupSet > $ignores[$rosterID]['ignoreAtSet']
			){
				continue;
			}
			if(!$highestRound && !isset($scores[$rosterID])){ continue; } // Only adds fighters if they are in the highest round

			$score = max([$match['fighter1Score'],$match['fighter2Score']]);
			if($score === null){ continue; }// Has not competed yet

			if(!isset($scores[$rosterID])){
				$scores[$rosterID] = 0;
			}
			$scores[$rosterID] += $score;

		}

		$highestRound = false;
	}

	if(!isset($scores)){return null;}

	if(isReverseScore($tournamentID) == REVERSE_SCORE_NO){
		arsort($scores);
	} else {
		asort($scores);
	}


	$place = 0;
	foreach($scores as $rosterID => $score){
		if(@$ignores[$rosterID]['stopAtSet'] > 0){
			// the array value not existing is logically the same as being zero
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

function isVideoStreamingForEvent($eventID){
	$eventID = (int)$eventID;
	$emptyFrame = (int)VIDEO_SOURCE_NONE;

	$sql = "SELECT isLive
			FROM eventVideoStreams
			INNER JOIN eventVideo USING(videoID)
			INNER JOIN logisticsLocations USING(locationID)
			WHERE eventID = {$eventID}
			AND isLive = 1
			AND sourceLink IS NOT NULL";
	$ringStreaming = (array)mysqlQuery($sql, SINGLES, 'isLive');

	if($ringStreaming != []){
		$isEventStreaming = true;
	} else {
		$isEventStreaming = false;
	}

	return ($isEventStreaming);

}

/******************************************************************************/

function getEventVideoStreams($eventID){
	$eventID = (int)$eventID;

	$sql = "SELECT locationID, locationName, isLive,
				videoID, sourceLink, matchID, overlayEnabled, overlayOpacity
			FROM logisticsLocations
			LEFT JOIN eventVideoStreams USING(locationID)
			LEFT JOIN eventVideo USING(videoID)
			WHERE eventID = {$eventID}
			AND hasMatches = 1
			ORDER BY locationName ASC";
	$ringInfo = (array)mysqlQuery($sql, ASSOC);

	return ($ringInfo);
}

/******************************************************************************/

function getStreamForLocation($locationID){

	$locationID = (int)$locationID;
	if($locationID == 0){
		// Not an error, return no information
		return [];
	}


	$sql = "SELECT streamID, videoID, matchID, locationID, isLive,
				sourceType, sourceLink, overlayEnabled, overlayOpacity
			FROM eventVideoStreams
			INNER JOIN eventVideo USING(videoID)
			WHERE locationID = {$locationID}";
	$streamInfo = (array)mysqlQuery($sql, SINGLE);

	if($streamInfo != []){
		$streamInfo['streamMode'] = VIDEO_STREAM_LOCATION;
		$streamInfo['synchTime'] = 0;
		$streamInfo['synchTime2'] = 0;
	}

	return $streamInfo;

}

/******************************************************************************/

function logistics_getWorkshopStats($eventID){

	$eventID = (int)$eventID;
	$blockID = (int)SCHEDULE_BLOCK_WORKSHOP;
	$roleID = (int)LOGISTICS_ROLE_INSTRUCTOR;

	$sql = "SELECT blockID, startTime, endTime
			FROM logisticsScheduleBlocks
			WHERE eventID = {$eventID}
			AND blockTypeID = {$blockID}";

	$courseBlocks = mysqlQuery($sql, ASSOC);

	$workshops['number'] = 0;
	$workshops['hours'] = 0;
	foreach($courseBlocks as $block){
		$workshops['number']++;
		$workshops['hours'] += $block['endTime'] - $block['startTime'];
	}

	$workshops['hours'] = round($workshops['hours']/60,1);

	$sql = "SELECT DISTINCT(rosterID) AS rosterID, lastName, firstName, eR.schoolID
			FROM logisticsStaffShifts
			INNER JOIN logisticsScheduleShifts USING(shiftID)
			INNER JOIN logisticsScheduleBlocks AS lSB USING(blockID)
			INNER JOIN eventRoster AS eR USING(rosterID)
			INNER JOIN systemRoster USING(systemRosterID)
			WHERE lSB.eventID = {$eventID}
			AND blockTypeID = {$blockID}
			AND logisticsRoleID = {$roleID}
			ORDER BY lastName ASC, firstName DESC";

	$workshops['instructors'] = (array)mysqlQuery($sql, ASSOC);
	$workshops['numInstructors'] = count($workshops['instructors']);

	return $workshops;

}

/******************************************************************************/

function logistics_getEventSchedule($eventID, $withShifts = false){

	$eventID = (int)$eventID;

	if($withShifts == true){
		$shiftClause = "AND (	SELECT COUNT(*)
							FROM logisticsScheduleShifts
							WHERE blockID = blocks.blockID) > 0";
	} else {
		$shiftClause = '';
	}

	$sql = "SELECT blockID, dayNum, startTime, endTime, blockTypeID, tournamentID,
					blockTitle, blockSubtitle, blockDescription
			FROM logisticsScheduleBlocks AS blocks
			WHERE eventID = {$eventID}
			{$shiftClause}
			ORDER BY dayNum ASC, startTime ASC";
	$scheduleData = mysqlQuery($sql, ASSOC);

	foreach($scheduleData as $index => $scheduleBlock){
		$blockID = (int)$scheduleBlock['blockID'];
		$sql = "SELECT locationID
				FROM logisticsLocationsBlocks
				INNER JOIN logisticsLocations USING(locationID)
				WHERE blockID = {$blockID}";

		$scheduleData[$index]['locationIDs'] = mysqlQuery($sql, SINGLES,'locationID');
	}

	$sortedSchedule = [];
	foreach($scheduleData as $item){
		$sortedSchedule[$item['dayNum']][] = $item;
	}

	return $sortedSchedule;


}

/******************************************************************************/

function logistics_getEventLocations($eventID, $locationType = null){

	$eventID = (int)$eventID;

	$whereClause = '';

	if($locationType == 'ring' || $locationType == 'Ring'){
		$whereClause .= "AND hasMatches = 1";
	}
	if($locationType == 'class' || $locationType == 'Class'){
		$whereClause .= "AND hasClasses = 1";
	}

	$sql = "SELECT locationID, locationName, locationNameShort, hasMatches, hasClasses
			FROM logisticsLocations
			WHERE eventID = {$eventID}
			{$whereClause}
			ORDER BY locationOrder ASC, hasMatches DESC, hasClasses DESC, locationName ASC";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function logistics_getFloorplanFilePath($eventID){

	$eventID = (int)$eventID;

	$basePath = "includes/images/floormaps/{$eventID}";

	/* Don't display anything unless a floor map exists. */
	if(file_exists($basePath.'.png') == true){
		$fullPath = $basePath.'.png';
	} elseif(file_exists($basePath.'.jpg') == true){
		$fullPath = $basePath.'.jpg';
	} elseif(file_exists($basePath.'.jpeg') == true){
		$fullPath = $basePath.'.jpeg';
	} else {
		$fullPath = null;
	}

	return ($fullPath);

}

/******************************************************************************/

function logistics_getRoles(){

	$sql = "SELECT logisticsRoleID, roleName
			FROM systemLogisticsRoles
			ORDER BY roleSortImportance ASC";
	return mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function logistics_getRoleName($roleID){

	$roleID = (int)$roleID;

	$sql = "SELECT roleName
			FROM systemLogisticsRoles
			WHERE logisticsRoleID = {$roleID}";
	return mysqlQuery($sql, SINGLE, 'roleName');

}

/******************************************************************************/

function logistics_getStaffTemplate($tournamentID){

	$tournamentID = (int)$tournamentID;

	$roles = logistics_getRoles();

	if($roles == null){
		return null;
	}

	$sql = "SELECT logisticsRoleID, numStaff
			FROM logisticsStaffTemplates
			WHERE tournamentID = {$tournamentID}";
	$template = mysqlQuery($sql, KEY_SINGLES,'logisticsRoleID','numStaff');

	return $template;
}

/******************************************************************************/

function logistics_getLocationName($locationID, $short = false){

	$locationID = (int)$locationID;
	$name = "";

	if($short == true){
		$sql = "SELECT locationName, locationNameShort
				FROM logisticsLocations
				WHERE locationID = {$locationID}";
		$names = mysqlQuery($sql, SINGLE);

		if($names['locationNameShort'] != null){
			$name = $names['locationNameShort'];
		} else {
			$name = $names['locationName'];
		}

	} else {
		$sql = "SELECT locationName
				FROM logisticsLocations
				WHERE locationID = {$locationID}";
		$name = mysqlQuery($sql, SINGLE, 'locationName');
	}

	return ($name);

}

/******************************************************************************/

function logistics_getEventOfLoacation($locationID){
	$locationID = (int)$locationID;

	$sql = "SELECT eventID
			FROM logisticsLocations
			WHERE locationID = {$locationID}";
	$eventID = (int)mysqlQuery($sql, SINGLE, 'eventID');

	return ($eventID);
}

/******************************************************************************/

function logistics_getCurrentDayNum($eventID){

	$eventID = (int)$eventID;

	$dates = getEventDates($eventID);
	$numDays = count(getEventDays($eventID));

	$currentDay = date("Y-m-d");

	if($currentDay <= $dates['eventStartDate'] || $currentDay > $dates['eventEndDate']){
		return 1;
	} else {
		for($dayNum = 1;$dayNum <=$numDays;$dayNum++){
			$d = $dayNum - 1;
			$workingDay = strtotime("+{$d} day", strtotime($dates['eventStartDate']));
			$workingDay = date("Y-m-d", $workingDay);

			if($workingDay == $currentDay){

				return $dayNum;
			}
		}

	}

}

/******************************************************************************/

function logistics_getGroupLocationName($groupID){

	$groupID = (int)$groupID;

	$sql = "SELECT locationID
			FROM eventGroups
			WHERE groupID = {$groupID}";
	$locationID = mysqlQuery($sql, SINGLE, 'locationID');

	if($locationID == null){
		return null;
	} else {
		return logistics_getLocationName($locationID);
	}
}

/******************************************************************************/

function getNormalization($tournamentID, $groupSet = 0){

	$groupSet = (int)$groupSet;
	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getNormalization()");
		return;
	}

	$value = null;

	// Checks to see if the set has it's own normalization size
	if($groupSet != 0){

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

	if($groupSet == 0){
		// If there was no group set specified then we don't do any auto-detect size
		// for normalization and simply return the value retrieved by the query.
	} else {
		if($value < 2){
			// A value of less than 2 means the normalization is set to auto detect
			$numFightersInPool = [];
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
			// If the value is still less than two, force it to two
			$value = 2;
		}
	}

	return $value;

}

/******************************************************************************/

function getNormalizationCumulative($tournamentID, $groupSet = 1){

	$tournamentID = (int)$tournamentID;
	$groupSet = (int)$groupSet;

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

function getNormalizationAveraged($tournamentID, $groupSet = 1){

	$tournamentID = (int)$tournamentID;
	$groupSet = (int)$groupSet;

	$sql = "SELECT MAX(attributeGroupSet) as groupSet
			FROM eventAttributes
			WHERE tournamentID = {$tournamentID}
			AND attributeGroupSet < {$groupSet}
			AND attributeType = 'cumulative'
			AND attributeBool = FALSE ";
	$baseGroupSet = (int)mysqlQuery($sql, SINGLE, 'groupSet');
	if($baseGroupSet < 1){
		$baseGroupSet = 1;
	}

	$sql = "SELECT fighter1ID, fighter2ID
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND groupSet = {$baseGroupSet}";
	$matches = mysqlQuery($sql, ASSOC);

	$fighters = [];
	$numMatches = 0;
	$numFighters = 0;
	foreach($matches as $match){
		$numMatches++;

		if(!isset($fighters[$match['fighter1ID']])){
			$fighters[$match['fighter1ID']] = true;
			$numFighters++;
		}

		if(!isset($fighters[$match['fighter2ID']])){
			$fighters[$match['fighter2ID']] = true;
			$numFighters++;
		}

	}

	if($numFighters != 0){
		$averageNumMatches = (2 * $numMatches) / $numFighters;
		$averageGroupSize = $averageNumMatches + 1;

		$averageGroupSize = round($averageGroupSize);
	} else {
		$averageGroupSize = 0;
	}


	return $averageGroupSize;
}

/******************************************************************************/

function getSetName($setNumber, $tournamentID){

	$setNumber = (int)$setNumber;
	$tournamentID = (int)$tournamentID;

	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getSetName()");
		return;
	}

// Get Existing Name
	$sql = "SELECT attributeText
			FROM eventAttributes
			WHERE tournamentID = {$tournamentID}
			AND attributeType = 'setName'
			AND attributeGroupSet = {$setNumber}";
	$name = mysqlQuery($sql, SINGLE, 'attributeText');

	if($name != null){
		return $name;
	}

// Generate a name if none exists
	$sql = "SELECT formatID
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$formatID = mysqlQuery($sql, SINGLE, 'formatID');

	$name == null;
	switch($formatID){

		case FORMAT_SOLO:
			$name = "Stage {$setNumber}";
			break;

		case FORMAT_MATCH:
			$sql = "SELECT groupName
					FROM eventGroups
					WHERE tournamentID = {$tournamentID}
					AND groupSet = {$setNumber}";
			$groups = mysqlQuery($sql, ASSOC);

			// If there is only one pool, and they have named it, the
			// pool name will be used as the set name.
			if(count($groups) == 1){

				$name = $groups[0]['groupName'];

				if($name == "Pool 1"){
					return "Pool Set {$setNumber}";
				} else {
					return $name;
				}
			}

			$name = "Pool Set {$setNumber}";
			break;

		default:
			$name = "Set {$setNumber}";
			break;
	}

	return $name;
}

/******************************************************************************/

function getSetAttributes($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
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
			AND attributeType IN( 'setName','cumulative','normalization',
				'timeLimit','maximumPoints','maximumExchanges')";

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
			case 'timeLimit':
				$attributes[$setNumber]['timeLimit'] = $data['attributeValue'];
				break;
			case 'maximumPoints':
				$attributes[$setNumber]['maximumPoints'] = $data['attributeValue'];
				break;
			case 'maximumExchanges':
				$attributes[$setNumber]['maximumExchanges'] = $data['attributeValue'];
				break;
			default:
		}
	}

	return $attributes;

}

/******************************************************************************/

function getSchoolInfo($schoolID){
// return a sorted array of all schools in the database

	$schoolID = (int)$schoolID;
	if($schoolID == 0){
		setAlert(SYSTEM,"No schoolID in getSchoolInfo()");
		return;
	}

	$sql = "SELECT *
			FROM systemSchools
			WHERE schoolID = {$schoolID}";
	return mysqlQuery($sql, SINGLE);

}

/******************************************************************************/

function getSchoolList(){
// return a sorted array of all schools in the database

	$sql = "SELECT schoolShortName, schoolID, schoolBranch, schoolAbbreviation, countryName
			FROM systemSchools
			INNER JOIN systemCountries USING(countryIso2)
			ORDER BY schoolShortName, schoolBranch";
	$allSchools = mysqlQuery($sql, ASSOC);


	return $allSchools;
}

/******************************************************************************/

function getSchoolListLong(){
// return a sorted array of all schools in the database

	$sql = "SELECT schoolID, schoolFullName, schoolShortName, schoolBranch,
				schoolAbbreviation, schoolCity, schoolProvince, countryIso2,
				countryName,
					(	SELECT COUNT(DISTINCT(systemRosterID)) AS num
						FROM eventRoster AS eR2
						WHERE eR2.schoolID = sS.schoolID
					) AS numEventReg,
					(	SELECT COUNT(*) AS num
						FROM systemRoster AS sR3
						WHERE sR3.schoolID = sS.schoolID
					) AS numSysReg
			FROM systemSchools AS sS
			INNER JOIN systemCountries USING(countryIso2)
			ORDER BY schoolShortName, schoolBranch";
	$allSchools = (array)mysqlQuery($sql, ASSOC);

	return $allSchools;
}


/******************************************************************************/

function getSchoolName($schoolID, $nameType = null, $includeBranch = null){

	$schoolID = (int)$schoolID;
	if($schoolID == 0){
		return '';
	}

	$sql = "SELECT schoolFullName, schoolShortName, schoolBranch, schoolAbbreviation
			FROM systemSchools
			WHERE schoolID = {$schoolID}";

	$result = mysqlQuery($sql, SINGLE);

	if($nameType == 'abbreviation'){
		$schoolName = $result['schoolAbbreviation'];
	} else if($nameType == 'long' || $nameType == 'full'){
		$schoolName = $result['schoolFullName'];
	} else {
		$schoolName = $result['schoolShortName'];
	}

	if($includeBranch != null){
		if($result['schoolBranch'] != null){
			$schoolName .= ", ".$result['schoolBranch'];
		}
	}

	if($schoolName == null){$schoolName = '&nbsp';}

	return $schoolName;

}

/******************************************************************************/

function getAttendanceFromSchools($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT DISTINCT(schoolID),
				(
					SELECT COUNT(DISTINCT(rosterID)) AS num
					FROM eventRoster as eR2
					WHERE eR.schoolID = eR2.schoolID
					AND eventID = {$eventID}
					AND isTeam = 0
				) AS numTotal,
				(
					SELECT COUNT(DISTINCT(rosterID)) AS numFighters
					FROM eventTournamentRoster AS eTR3
					INNER JOIN eventTournaments AS eT3 USING(tournamentID)
					INNER JOIN eventRoster AS eR3 USING(rosterID)
					WHERE eR.schoolID = eR3.schoolID
					AND eT3.eventID = {$eventID}
					AND isTeam = 0
				) AS numFighters
			FROM eventRoster AS eR
			WHERE eventID = {$eventID}
			AND schoolID IS NOT NULL
			ORDER BY numTotal DESC, numFighters DESC";
	$clubTotals = (array)mysqlQuery($sql, ASSOC);

	return ($clubTotals);

}

/******************************************************************************/

function getSchoolPoints($eventID){

	$eventID = (int)$eventID;
	if($eventID == 0){
		setAlert(SYSTEM,"No eventID for updateSchoolPoints()");
		return;
	}

	$tournamentList = getTournamentsFull($eventID);
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

function getSchoolRosterNotInEvent($schoolID, $eventID){

	$eventID = (int)$eventID;
	if($eventID == 0){
		setAlert(SYSTEM,"No eventID in getSchoolRosterNotInEvent()");
		return;
	}

	$schoolID = (int)$schoolID;
	if($schoolID == 0){
		setAlert(SYSTEM,"No schoolID in getSchoolRosterNotInEvent()");
		return;
	}

	$orderName = NAME_MODE;
	$orderName2 = NAME_MODE_2;

	$sql = "SELECT sR.systemRosterID
			FROM systemRoster AS sR
			LEFT JOIN eventRoster AS eR ON sR.systemRosterID = eR.systemRosterID AND eR.eventID = {$eventID}
			WHERE sR.schoolID = {$schoolID}
			AND eR.eventID IS null
			ORDER BY sR.{$orderName} ASC, sR.{$orderName2} ASC";

	return mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function getScoreFormula($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
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

function getSystemRoster($tournamentID = 0){

// return a sorted array of all fighters in the system


	$sortString = NAME_MODE." ASC, ".NAME_MODE_2." ASC";
	$tournamentID = (int)$tournamentID;

	if($tournamentID == 0){

		$sql = "SELECT systemRosterID
				FROM systemRoster
				ORDER BY {$sortString}";

		return mysqlQuery($sql, SINGLES, 'systemRosterID');

	} else {

		$sql = "SELECT systemRoster.systemRosterID
				FROM eventTournamentRoster
				INNER JOIN eventRoster USING(rosterID)
				INNER JOIN systemRoster ON eventRoster.systemRosterID = systemRoster.systemRosterID
				WHERE tournamentID = {$tournamentID}
				ORDER BY {$sortString}";

		return mysqlQuery($sql, SINGLES, 'systemRosterID');

	}

}

/******************************************************************************/

function getSystemRosterInfo(){

	$sql = "SELECT systemRosterID, firstName, lastName, schoolFullName, HemaRatingsID, schoolID
			FROM systemRoster
			INNER JOIN systemSchools USING(schoolID)
			ORDER BY lastName ASC, firstName ASC";

	return (array)mysqlQuery($sql, ASSOC);

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

	$groupID = (int)$groupID;

	$sql = "SELECT matchID
			FROM eventMatches
			WHERE groupID = {$groupID}
			AND matchComplete = 1
			AND winnerID IS NULL";

	return mysqlQuery($sql, NUM_ROWS);

}

/******************************************************************************/

function getTournamentAttacks($tournamentID){
// Get the unique attacks attributed to a tournament

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
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

function getTournamentAttackParameters($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT DISTINCT(attackType) as attackType
			FROM eventAttacks
			WHERE tournamentID = {$tournamentID}
			AND attackType IS NOT NULL
			ORDER BY attackType ASC";
	$attacks['attackType'] = (array)mysqlQuery($sql, SINGLES, 'attackType');

	$sql = "SELECT DISTINCT(attackTarget) as attackTarget
			FROM eventAttacks
			WHERE tournamentID = {$tournamentID}
			AND attackTarget IS NOT NULL";
	$attacks['attackTarget'] = (array)mysqlQuery($sql, SINGLES, 'attackTarget');

	$sql = "SELECT DISTINCT(attackPrefix) as attackPrefix
			FROM eventAttacks
			WHERE tournamentID = {$tournamentID}
			AND attackPrefix IS NOT NULL";
	$attacks['attackPrefix'] = (array)mysqlQuery($sql, SINGLES, 'attackPrefix');

	$sql = "SELECT DISTINCT(attackPoints) as attackPoints
			FROM eventAttacks
			WHERE tournamentID = {$tournamentID}
			AND attackPoints IS NOT NULL
			AND attackPoints != 0";
	$attacks['attackPoints'] = (array)mysqlQuery($sql, SINGLES, 'attackPoints');

	return $attacks;
}

/******************************************************************************/

function getPenaltyColors(){

	$sql = "SELECT attackID, attackText
			FROM systemAttacks
			WHERE attackClass = 'penalty'";
	return mysqlQuery($sql, KEY_SINGLES, 'attackID', 'attackText');
}

/******************************************************************************/

function getPenaltyActions($eventID = null){

	$eventID = (int)$eventID;

	$sql = "SELECT attackID, attackText AS name
			FROM systemAttacks
			WHERE attackClass = 'illegalAction'
			ORDER BY attackText ASC";
	$penaltyList = (array)mysqlQuery($sql, ASSOC);


	// If the eventID was provided, then check to see if any penalties are disabled in this event.
	if($eventID != 0){
		$sql = "SELECT attackID, penaltyDisabledID
				FROM eventPenaltyDisabled
				WHERE eventID = {$eventID}";
		$disabledPenalties = (array)mysqlQuery($sql, KEY_SINGLES, 'attackID', 'penaltyDisabledID');
	} else {
		$disabledPenalties = [];
	}

	foreach($penaltyList as $i => $p){
		$attackID = $p['attackID'];
		if(isset($disabledPenalties[$attackID ]) == true){
			$penaltyList[$i]['enabled'] = false;
		} else {
			$penaltyList[$i]['enabled'] = true;
		}
	}


	return($penaltyList);

}

/******************************************************************************/

function getTournamentAttributeName($tournamentTypeID = 0){
	//the name of any tournament attribute given it's ID

	$tournamentTypeID = (int)$tournamentTypeID;
	if($tournamentTypeID == 0){
		return null;
	}

	$sql = "SELECT tournamentType
			FROM systemTournaments
			WHERE tournamentTypeID = {$tournamentTypeID} ";
	return  mysqlQuery($sql, SINGLE, 'tournamentType');
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

function getEventAndTournamentName($tournamentID, $bold = true){

	$str = '';
	if($tournamentID != 0){
		if($bold == true){
			$str .= '<strong>';
		}
		$str .= "[".getEventName(getTournamentEventID($tournamentID))."]";
		if($bold == true){
			$str .= '</strong>';
		}
		$str .= " ".getTournamentName($tournamentID);
	}


	return $str;

}

/******************************************************************************/

function logistics_limitStaffConflicts($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT limitStaffConflicts
			FROM eventDefaults
			WHERE eventID = {$eventID}";
	return (int)mysqlQuery($sql, SINGLE, 'limitStaffConflicts');
}

/******************************************************************************/

function logistics_getAvaliableStaff($eventID, $tournamentID = null){

	$eventID = (int)$eventID;
	$tournamentID = (int)$tournamentID;
	$orderName = NAME_MODE;
	$orderName2 = NAME_MODE_2;

	if($tournamentID != 0){
		$tSelect = "AND (	SELECT COUNT(*)
							FROM eventTournamentRoster as eTR
							WHERE tournamentID = {$tournamentID}
							AND eR.rosterID = eTR.rosterID) = 0";
	} else {
		$tSelect = '';
	}

	$sql = "SELECT eR.rosterID, staffCompetency, staffHoursTarget
			FROM eventRoster as eR
			INNER JOIN systemRoster AS sR USING(systemRosterID)
			LEFT JOIN logisticsStaffCompetency USING(rosterID)
			WHERE eventID = {$eventID}
			AND staffCompetency != 0
			AND staffCompetency IS NOT NULL
			{$tSelect}
			ORDER BY staffCompetency DESC, sR.{$orderName} ASC, sR.{$orderName2} ASC";

	$avaliableStaff = mysqlQuery($sql, KEY, 'rosterID');

	if($avaliableStaff == null){
		$sql = "SELECT COUNT(*) AS numEventStaff
				FROM eventRoster
				INNER JOIN logisticsStaffCompetency USING(rosterID)
				WHERE eventID = {$eventID}
				AND staffCompetency != 0
				AND staffCompetency IS NOT NULL";
		$numEventStaff = (int)mysqlQuery($sql, SINGLE, 'numEventStaff');

		if($numEventStaff == 0){
			$sql = "SELECT eR.rosterID, staffCompetency, staffHoursTarget
					FROM eventRoster as eR
					INNER JOIN systemRoster AS sR USING(systemRosterID)
					LEFT JOIN logisticsStaffCompetency USING(rosterID)
					WHERE eventID = {$eventID}
					{$tSelect}
					ORDER BY staffCompetency DESC, sR.{$orderName} ASC, sR.{$orderName2} ASC";
			$avaliableStaff = mysqlQuery($sql, KEY, 'rosterID');
		}
	}

	foreach($avaliableStaff as $rosterID => $staff){
		$avaliableStaff[$rosterID]['name'] = getFighterName($rosterID);
	}

	return ($avaliableStaff );
}

/******************************************************************************/

function logistics_getEventInstructors($eventID){

	$eventID = (int)$eventID;
	$orderName = NAME_MODE;
	$orderName2 = NAME_MODE_2;

	if(NAME_MODE == 'firstName'){
		$name = "CONCAT(firstName,' ',lastName)";
	} else {
		$name = "CONCAT(lastName,', ',firstName)";
	}

	$sql = "SELECT rosterID, {$name} AS name, instructorBio, systemRosterID
			FROM logisticsInstructors AS lI
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN systemRoster USING(systemRosterID)
			WHERE lI.eventID = {$eventID}
			ORDER BY {$orderName} ASC, {$orderName2} ASC";
	$list = (array)mysqlQuery($sql, ASSOC);

	return ($list);

}

/******************************************************************************/

function logistics_getUnconflictedShiftStaff($shiftID){

	$shiftID = (int)$shiftID;
	$orderName = NAME_MODE;
	$orderName2 = NAME_MODE_2;

	// Get start/end time of shift
	// Get if it is a tournament
	// Check they aren't in a tournament at the same time
	// Check they aren't staffing at the same time.

	$sql = "SELECT shift.startTime, shift.endTime, tournamentID, dayNum, eventID
			FROM logisticsScheduleShifts as shift
			INNER JOIN logisticsScheduleBlocks USING(blockID)
			WHERE shiftID = {$shiftID}";
	$sInfo = mysqlQuery($sql, SINGLE);

	$tournamentID = (int)$sInfo['tournamentID'];
	$dayNum = (int)$sInfo['dayNum'];
	$startTime = (int)$sInfo['startTime'];
	$endTime = (int)$sInfo['endTime'];
	$eventID = (int)$sInfo['eventID'];

	$sql = "SELECT 1
			FROM logisticsScheduleBlocks AS lSB
			INNER JOIN
			WHERE tournamentID IN (SELECT tournamentID
									FROM eventTournamentRoster AS eTR
									WHERE eR.rosterID = eTR.rosterID)
			AND dayNum = {$dayNum}
			AND (  (startTime <= {$startTime} AND endTime > {$startTime})
				OR (startTime < {$endTime} AND endTime >= {$endTime})
				OR (startTime >= {$startTime} AND endTime <= {$endTime}))";

	$sql = "SELECT eR.rosterID, 1 AS dummy
			FROM eventRoster as eR
			INNER JOIN systemRoster USING(systemRosterID)
			LEFT JOIN logisticsStaffCompetency USING(rosterID)
			WHERE eventID = {$eventID}
			AND staffCompetency != 0
			AND staffCompetency IS NOT NULL
			AND NOT EXISTS(	SELECT NULL
							FROM logisticsStaffShifts AS lss
							INNER JOIN logisticsScheduleShifts AS shift USING(shiftID)
							INNER JOIN logisticsScheduleBlocks USING(blockID)
							WHERE eR.rosterID = lss.rosterID
							AND dayNum = {$dayNum}
							AND suppressConflicts = 0
							AND (  (shift.startTime <= {$startTime} AND shift.endTime > {$startTime})
								OR (shift.startTime < {$endTime} AND shift.endTime >= {$endTime})
								OR (shift.startTime >= {$startTime} AND shift.endTime <= {$endTime}))
							LIMIT 1 )
			AND NOT EXISTS( SELECT 1
							FROM logisticsScheduleBlocks AS lSB
							WHERE tournamentID IN (SELECT tournamentID
													FROM eventTournamentRoster AS eTR
													WHERE eR.rosterID = eTR.rosterID)
							AND dayNum = {$dayNum}
							AND suppressConflicts = 0
							AND (  (startTime <= {$startTime} AND endTime > {$startTime})
								OR (startTime < {$endTime} AND endTime >= {$endTime})
								OR (startTime >= {$startTime} AND endTime <= {$endTime}))
							LIMIT 1)
			ORDER BY staffCompetency DESC, systemRoster.{$orderName} ASC, systemRoster.{$orderName2} ASC";

	$avaliableStaff = mysqlQuery($sql, KEY_SINGLES, 'rosterID','dummy');

	return ($avaliableStaff );
}

/******************************************************************************/

function logistics_isBlockStaffed($blockID, $blockTypeID, $tournamentID = null){

	$blockID = (int)$blockID;
	$blockTypeID = (int)$blockTypeID;

	$sql = "SELECT shiftID
			FROM logisticsScheduleShifts
			WHERE blockID = {$blockID}";
	$shifts = mysqlQuery($sql, SINGLES);


	foreach($shifts as $shiftID){

		$shiftID = (int)$shiftID;
		$isStaffed = true;

		$sql = "SELECT logisticsRoleID, COUNT(*) AS num
				FROM logisticsStaffShifts
				WHERE shiftID = {$shiftID}
				GROUP BY logisticsRoleID";
		$staffOnShift = mysqlQuery($sql, KEY_SINGLES, 'logisticsRoleID','num');

		switch($blockTypeID){
			case SCHEDULE_BLOCK_WORKSHOP:
				if(!isset($staffOnShift[LOGISTICS_ROLE_INSTRUCTOR])
					|| $staffOnShift[LOGISTICS_ROLE_INSTRUCTOR] == 0) {
					$isStaffed = false;
				}
				break;
			case SCHEDULE_BLOCK_TOURNAMENT:

				if($tournamentID == null){
					$sql = "SELECT tournamentID
							FROM logisticsScheduleBlocks
							WHERE blockID = {$blockID}";
					$tournamentID = (int)mysqlQuery($sql, SINGLE, 'tournamentID');
				}
				$tournamentID = (int)$tournamentID;
				if($tournamentID == 0){
					setAlert(SYSTEM,"No TournamentID in logistics_isBlockStaffed()");
					break;
				}

				$template = logistics_getStaffTemplate($tournamentID);

				foreach($template as $roleID => $numStaff){
					if($numStaff === null){
						continue;
					}

					if(   isset($staffOnShift[$roleID]) == false
						||(int)$numStaff != (int)$staffOnShift[$roleID]){

						return false;

					}

				}

				break;
			default:

		}

	}

	return true;
}

/******************************************************************************/

function logistics_getMatchStaff($matchID){

	$matchID = (int)$matchID;

	$sql = "SELECT matchStaffID, rosterID, logisticsRoleID
			FROM logisticsStaffMatches
			WHERE matchID = {$matchID}";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function logistics_getMatchStaffSuggestion($matchInfo){
// First look if there is already staff in the match, if so return that.
// Second look at the match before this one and load that staff
// Third look at the staff roster
// Fourth return null


// If the match has staff
	$staffInMatch = logistics_getMatchStaff($matchInfo['matchID']);

	if($staffInMatch != null){
		return $staffInMatch;
	}


// Look at previous matches in the same ring and grab the most recently edited one.
	$matchID = (int)$matchInfo['matchID'];
	$locationID = (int)$matchInfo['locationID'];
	if($locationID == 0){
		return [];
	}

	$sql = "SELECT matchID
			FROM logisticsStaffMatches
			INNER JOIN logisticsLocationsMatches USING(matchID)
			WHERE locationID = {$locationID}
			ORDER BY matchStaffID DESC
			LIMIT 1	";
	$prevMatchID = (int)mysqlQuery($sql, SINGLE, 'matchID');

	// Pick the match with the latest entry
	$sql = "SELECT (-1) AS matchStaffID, rosterID, logisticsRoleID
			FROM logisticsStaffMatches
			INNER JOIN systemLogisticsRoles USING(logisticsRoleID)
			WHERE matchID = {$prevMatchID}
			ORDER BY roleSortImportance DESC";
	$staffInMatch =  mysqlQuery($sql, ASSOC);

	if($staffInMatch != null){
		return $staffInMatch;
	}

// Give up
	return [];

}

/******************************************************************************/

function logistics_getMatchStaffShifts($matchInfo){

	$tournamentID = (int)$matchInfo['tournamentID'];
	$locationID = (int)$matchInfo['locationID'];

	$sql = "SELECT shiftID, dayNum, shifts.startTime, shifts.endTime, blockSubtitle
			FROM logisticsScheduleShifts AS shifts
			INNER JOIN logisticsScheduleBlocks USING(blockID)
			WHERE tournamentID = {$tournamentID}
			AND locationID = {$locationID}
			ORDER BY dayNum ASC, shifts.startTime ASC";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function logistics_getTournamentStaffCheckInLevel($tournamentID){

	$tournamentID = (int)$tournamentID;
	$sql = "SELECT checkInStaff
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return (int)mysqlQuery($sql, SINGLE, 'checkInStaff');
}

/******************************************************************************/

function logistics_areMatchStaffCheckedIn($matchID){

	$matchID = (int)$matchID;
	$sql = "SELECT COUNT(*) AS numStaff
			FROM logisticsStaffMatches
			WHERE matchID = {$matchID}";
	return (bool)mysqlQuery($sql, SINGLE, 'numStaff');
}

/******************************************************************************/

function logistics_areMatchStaffUsed($eventID){

	$eventID = (int)$eventID;
	$sql = "SELECT matchID
			FROM logisticsStaffMatches
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE eventID = {$eventID}
			LIMIT 1";
	return (bool)mysqlQuery($sql, SINGLE, 'matchID');
}

/******************************************************************************/

function logistics_getStaffingMinutes($rosterID, $eventID, $combine = false){

	$rosterID = (int)$rosterID;
	$eventID = (int)$eventID;
	$participantID = (int)LOGISTICS_ROLE_PARTICIPANT;

	$sql = "SELECT (shifts.endTime - shifts.startTime) AS length, logisticsRoleID
			FROM logisticsStaffShifts AS staff
			INNER JOIN logisticsScheduleShifts AS shifts USING(shiftID)
			INNER JOIN logisticsScheduleBlocks USING(blockID)
			INNER JOIN systemLogisticsRoles USING (logisticsRoleID)
			WHERE eventID = {$eventID}
			AND rosterID = {$rosterID}
			AND logisticsRoleID != {$participantID}
			ORDER BY roleSortImportance ASC";
	$minutes = mysqlQuery($sql, ASSOC);


	if($combine == false){
		$sum = [];
	} else {
		$sum = 0;
	}

	foreach($minutes as $shift){
		if($combine == false){
			@$sum[$shift['logisticsRoleID']] += $shift['length']; // May not be set, treat as zero.
		} else {
			$sum += $shift['length'];
		}

	}

	return $sum;
}

/******************************************************************************/

function logistics_getEventStaffingMinutes($eventID){

	$eventID = (int)$eventID;

	$sortString = NAME_MODE." ASC, ".NAME_MODE_2." ASC";
	$participantID = (int)LOGISTICS_ROLE_PARTICIPANT;

	$sql = "SELECT rosterID, (shifts.endTime - shifts.startTime) AS length,
					logisticsRoleID, checkedIn, staffHoursTarget
			FROM logisticsStaffShifts AS lSA
			INNER JOIN logisticsScheduleShifts AS shifts USING(shiftID)
			INNER JOIN logisticsScheduleBlocks AS blocks USING(blockID)
			INNER JOIN systemLogisticsRoles USING(logisticsRoleID)
			INNER JOIN eventRoster USING(rosterID)
			LEFT JOIN logisticsStaffCompetency USING(rosterID)
			WHERE blocks.eventID = {$eventID}
			AND logisticsRoleID != {$participantID}
			ORDER BY roleSortImportance ASC";
	$minutes = mysqlQuery($sql, ASSOC);

	$sql = "SELECT DISTINCT logisticsRoleID, roleSortImportance
			FROM logisticsStaffShifts AS lSA
			INNER JOIN logisticsScheduleShifts AS shifts USING(shiftID)
			INNER JOIN logisticsScheduleBlocks USING(blockID)
			INNER JOIN systemLogisticsRoles USING(logisticsRoleID)
			WHERE eventID = {$eventID}
			AND logisticsRoleID != {$participantID}
			ORDER BY roleSortImportance ASC";
	$roleList = mysqlQuery($sql, SINGLES, 'logisticsRoleID');


	$sql = "SELECT rosterID, staffHoursTarget
			FROM eventRoster
			INNER JOIN systemRoster USING(systemRosterID)
			LEFT JOIN logisticsStaffCompetency USING(rosterID)
			WHERE eventID = {$eventID}
			AND ((staffCompetency > 0 AND staffCompetency IS NOT NULL)
				|| staffHoursTarget IS NOT NULL)
			ORDER BY {$sortString}";
	$allStaff = mysqlQuery($sql, ASSOC);


	$finalList = [];
	foreach($allStaff as $staffMemeber){
		$rosterID = $staffMemeber['rosterID'];
		$finalList[$rosterID]['rosterID'] = $rosterID;
		$finalList[$rosterID]['staffHoursTarget'] = $staffMemeber['staffHoursTarget'];
		$finalList[$rosterID]['staffMinutesUnscheduled'] = ($staffMemeber['staffHoursTarget']*60);
		$finalList[$rosterID]['staffMinutesUnconfirmed'] = 0;
		$finalList[$rosterID]['totalMinutes'] = 0;
		$finalList[$rosterID]['confirmedTotalMinutes'] = 0;
		$finalList[$rosterID]['staffingMinutes'] = [];
		foreach($roleList as $roleID){
			$finalList[$rosterID]['staffingMinutes'][$roleID] = 0;
			$finalList[$rosterID]['confirmedStaffingMinutes'][$roleID] = 0;
		}
	}

	// This logic is horribly inneficient and should be re-worked.
	foreach($minutes as $shift){

		$rosterID = (int)$shift['rosterID'];

		// This is necessary because a person may somehow get a shift even if they aren't staff.
		if(isset($finalList[$rosterID]['rosterID']) == false){
			$finalList[$rosterID]['rosterID'] = $rosterID;
			$finalList[$rosterID]['staffHoursTarget'] = $shift['staffHoursTarget'];
			$finalList[$rosterID]['staffMinutesUnscheduled'] = ($shift['staffHoursTarget']*60);
			$finalList[$rosterID]['staffMinutesUnconfirmed'] = 0;
			$finalList[$rosterID]['totalMinutes'] = 0;
			$finalList[$rosterID]['confirmedTotalMinutes'] = 0;
			$finalList[$rosterID]['staffingMinutes'] = [];
			foreach($roleList as $roleID){
				$finalList[$rosterID]['staffingMinutes'][$roleID] = 0;
				$finalList[$rosterID]['confirmedStaffingMinutes'][$roleID] = 0;
			}
		}

		$roleID = (int)$shift['logisticsRoleID'];
		$minToAdd = $shift['length'];

		$finalList[$rosterID]['staffingMinutes'][$roleID] += $minToAdd;
		$finalList[$rosterID]['totalMinutes'] += $minToAdd;
		$finalList[$rosterID]['staffMinutesUnscheduled'] -= $minToAdd;
		if($finalList[$rosterID]['staffMinutesUnscheduled'] < 0){
			$finalList[$rosterID]['staffMinutesUnscheduled'] = 0;
		}

		if($shift['checkedIn'] != false){
			$finalList[$rosterID]['confirmedStaffingMinutes'][$roleID] += $minToAdd;
			$finalList[$rosterID]['confirmedTotalMinutes'] += $minToAdd;
		} else {
			$finalList[$rosterID]['staffMinutesUnconfirmed'] += $minToAdd;
		}

	}

	return $finalList;
}

/******************************************************************************/

function logistics_getEventStaffingMatches($eventID){

	$eventID = (int)$eventID;

	$orderName = NAME_MODE;

	$sql = "SELECT rosterID, logisticsRoleID as roleID
			FROM logisticsStaffMatches
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE eventID = {$eventID}";
	$matchShifts = mysqlQuery($sql, ASSOC);

	$sql = "SELECT DISTINCT logisticsRoleID as roleID, roleSortImportance
			FROM logisticsStaffMatches
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			INNER JOIN systemLogisticsRoles USING(logisticsRoleID)
			WHERE eventID = {$eventID}
			ORDER BY roleSortImportance ASC";
	$roles = mysqlQuery($sql, SINGLES);

	$multipliers = logistics_getMatchMultipliers($eventID);

	$finalHours = [];
	foreach($matchShifts as $shift){

		$rosterID = $shift['rosterID'];


		if(isset($finalHours[$rosterID]) == false){
			$finalHours[$rosterID]['totalMatches'] = 0;
			$finalHours[$rosterID]['scaledMatches'] = 0;
			foreach($roles as $roleID){
				$finalHours[$rosterID]['roleMatches'][$roleID] = 0;
			}
		}

		$roleID = $shift['roleID'];

		$finalHours[$rosterID]['totalMatches']++;
		$finalHours[$rosterID]['scaledMatches'] += $multipliers[$roleID];
		$finalHours[$rosterID]['roleMatches'][$roleID]++;

	}

	return $finalHours;

}

/******************************************************************************/

function logistics_getEventFullShiftList($eventID){

	$sql = "SELECT firstName, lastName, roleName, dayNum, locationName,
				(lSS.endTime - lSS.startTime) AS length, schoolFullName
			FROM logisticsStaffShifts
			INNER JOIN logisticsScheduleShifts AS lSS USING(shiftID)
			INNER JOIN logisticsScheduleBlocks AS lSB USING(blockID)
			INNER JOIN systemEvents AS sE ON sE.eventID = lSB.eventID
			INNER JOIN eventRoster AS eR USING(rosterID)
			INNER JOIN systemSchools AS sS ON eR.schoolID = sS.schoolID
			INNER JOIN systemRoster USING(systemRosterID)
			INNER JOIN logisticsLocations USING(locationID)
			INNER JOIN systemLogisticsRoles USING(logisticsRoleID)
			WHERE lSB.eventID = {$eventID}
			ORDER BY lastName ASC, firstName";

	$shiftList = (array)mysqlQuery($sql, ASSOC);

	return ($shiftList);
}

/******************************************************************************/

function logistics_getGroupStaffExceptions($groupID){

	$groupID = (int)$groupID;
	$sql = "SELECT staffExceptionID, rosterID, logisticsRoleID
			FROM logisticsStaffExceptions
			WHERE groupID = {$groupID}";
	return mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function logistics_areStaffCompetenciesSet($eventID){

	$eventID = (int)$eventID;
	$sql = "SELECT COUNT(*) numRated
			FROM eventRoster
			LEFT JOIN logisticsStaffCompetency USING(rosterID)
			WHERE eventID = {$eventID}
			AND staffCompetency IS NOT NULL
			AND staffCompetency > 0";
	return (bool)mysqlQuery($sql, SINGLE, 'numRated');

}

/******************************************************************************/

function logistics_getStaffCompetency($rosterID){

	$rosterID = (int)$rosterID;
	$sql = "SELECT staffCompetency
			FROM logisticsStaffCompetency
			WHERE rosterID = {$rosterID}";
	return (int)mysqlQuery($sql, SINGLE, 'staffCompetency');
}

/******************************************************************************/

function logistics_getDefaultStaffCompetency($eventID){

	$eventID = (int)$eventID;
	$sql = "SELECT staffCompetency
			FROM eventDefaults
			WHERE eventID = {$eventID}";
	return (int)mysqlQuery($sql, SINGLE, 'staffCompetency');
}

/******************************************************************************/

function logistics_isStaffAssignmentOnEventEntry($eventID){

	$eventID = (int)$eventID;
	$sql = "SELECT addStaff
			FROM eventDefaults
			WHERE eventID = {$eventID}";
	return (int)mysqlQuery($sql, SINGLE, 'addStaff');
}

/******************************************************************************/

function logistics_getParticipantSchedule($rosterID, $eventID){

	$rosterID = (int)$rosterID;
	$eventID = (int)$eventID;

// Get Tournament Entries
	$sql = "SELECT eTR.tournamentID, blockID, dayNum, startTime, endTime, suppressConflicts
			FROM eventTournamentRoster AS eTR
			INNER JOIN eventTournaments AS eT USING(tournamentID)
			LEFT JOIN logisticsScheduleBlocks AS blocks ON blocks.tournamentID = eTR.tournamentID
			WHERE rosterID = {$rosterID}
			AND eT.eventID = {$eventID}
			ORDER BY dayNum ASC, startTime ASC";
	$entryInfo = mysqlQuery($sql, ASSOC);

// Get Staffing Entries
	$sql = "SELECT shiftID, blockID, locationID, logisticsRoleID,
					dayNum, shifts.startTime, shifts.endTime, suppressConflicts,
					blockTypeID
			FROM logisticsStaffShifts
			INNER JOIN logisticsScheduleShifts AS shifts USING(shiftID)
			INNER JOIN logisticsScheduleBlocks AS blocks USING(blockID)
			WHERE rosterID = {$rosterID}
			AND blocks.eventID = {$eventID}
			ORDER BY dayNum ASC, startTime ASC";
	$staffInfo = mysqlQuery($sql, ASSOC);

	$eMax = count($entryInfo);
	$sMax = count($staffInfo);
	$totalEntries = $eMax + $sMax;

	$eIndex = 0;
	$sIndex = 0;
	$schedule = [];
	for($c = 1;$c <= $totalEntries; $c++){

		if(isset($entryInfo[$eIndex]) == false){
			$schedule['scheduled'][] = $staffInfo[$sIndex];
			$sIndex++;
		} elseif($entryInfo[$eIndex]['blockID'] == null){
			$schedule['unScheduled'][] = $entryInfo[$eIndex]['tournamentID'];
			$eIndex++;
		} elseif(isset($staffInfo[$sIndex]) == false){
			$schedule['scheduled'][] = $entryInfo[$eIndex];
			$eIndex++;
		} elseif($entryInfo[$eIndex]['dayNum'] < $staffInfo[$sIndex]['dayNum']){
			$schedule['scheduled'][] = $entryInfo[$eIndex];
			$eIndex++;
		} elseif($staffInfo[$sIndex]['dayNum'] < $entryInfo[$eIndex]['dayNum']){
			$schedule['scheduled'][] = $staffInfo[$sIndex];
			$sIndex++;
		} elseif($entryInfo[$eIndex]['startTime'] < $staffInfo[$sIndex]['startTime']){
			$schedule['scheduled'][] = $entryInfo[$eIndex];
			$eIndex++;
		} elseif($staffInfo[$sIndex]['startTime'] < $entryInfo[$eIndex]['startTime']){
			$schedule['scheduled'][] = $staffInfo[$sIndex];
			$sIndex++;
		} else {
			$schedule['scheduled'][] = $staffInfo[$sIndex];
			$sIndex++;
		}

	}

	return $schedule;

}

/******************************************************************************/

function logistics_findUnfilledShifts($eventID){

	$eventID = (int)$eventID;
	$tournamentIDs = getEventTournaments($eventID);

	$badShifts = [];

	foreach($tournamentIDs as $tournamentID){
		$tournamentID = (int)$tournamentID;
		$template = logistics_getStaffTemplate($tournamentID);

		if($template == null){
			continue;
		}

		$sql = "SELECT shiftID
				FROM logisticsScheduleShifts
				INNER JOIN logisticsScheduleBlocks USING(blockID)
				WHERE tournamentID = {$tournamentID}";
		$allShifts = mysqlQuery($sql, SINGLES, 'shiftID');


		foreach($allShifts as $shiftID){
			$shiftID = (int)$shiftID;

			$sql = "SELECT logisticsRoleID, COUNT(*) AS numStaff
					FROM logisticsStaffShifts
					WHERE shiftID = {$shiftID}
					GROUP BY logisticsRoleID";
			$staffLevels = mysqlQuery($sql, KEY_SINGLES, 'logisticsRoleID', 'numStaff');

			foreach($template as $logisticsRoleID => $targetStaff){
				$staffSet = (int)@$staffLevels[$logisticsRoleID];

				if($staffSet != $targetStaff){
					$tmp['logisticsRoleID'] = $logisticsRoleID;
					$tmp['numStaff'] = $staffSet;
					$tmp['targetStaff'] = $targetStaff;
					$badShifts[$shiftID][] = $tmp;
				}
			}

		}

	}

	return $badShifts;

}

/******************************************************************************/

function logistics_findStaffOverCompetency($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT shiftID, logisticsRoleID, staffCompetency, rosterID,
				(	SELECT roleCompetency
					FROM logisticsRoleCompetency lRC
					WHERE eventID = {$eventID}
					AND lRC.logisticsRoleID = lSS.logisticsRoleID
				) AS roleCompetency

			FROM logisticsStaffShifts AS lSS
			INNER JOIN eventRoster USING(rosterID)
			LEFT JOIN logisticsStaffCompetency USING(rosterID)
			WHERE eventID = {$eventID}
			AND ((	SELECT roleCompetency
					FROM logisticsRoleCompetency lRC2
					WHERE eventID = {$eventID}
					AND lRC2.logisticsRoleID = lSS.logisticsRoleID
				) > staffCompetency
				OR staffCompetency IS NULL
				)";
	return mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function logistics_getEventAnnouncments($eventID){

	$eventID = (int)$eventID;
	$sql = "SELECT announcementID, message, displayUntil, visibility
			FROM logisticsAnnouncements
			WHERE eventID = {$eventID}
			OR eventID IS NULL
			ORDER BY displayUntil DESC";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function getTournamentCompetitors($tournamentID, $sortType = null, $excluded = null){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getTournamentCompetitors()");
		return;
	}

	if(!isTeams($tournamentID)){
		return getTournamentRoster($tournamentID, $sortType, $excluded);
	} else {

		$sql = "SELECT eventRoster.rosterID, systemSchools.schoolShortName,
					systemSchools.schoolAbbreviation
				FROM eventTournamentRoster
				INNER JOIN eventRoster USING(rosterID)
				LEFT JOIN systemRoster USING(systemRosterID)
				LEFT JOIN systemSchools ON eventRoster.schoolID = systemSchools.schoolID
				WHERE eventTournamentRoster.tournamentID = {$tournamentID}
				AND eventRoster.isTeam = 1";

		if($sortType == 'rosterID'){
			return mysqlQuery($sql,KEY,'rosterID');
		} else {
			return mysqlQuery($sql, ASSOC);
		}

	}

}

/******************************************************************************/

function getTournamentEventID($tournamentID){
	$tournamentID = (int)$tournamentID;

	$sql = "SELECT eventID
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return mysqlQuery($sql, SINGLE, 'eventID');
}

/******************************************************************************/

function getTournamentFighters($tournamentID, $sortType = null, $excluded = null){


	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getTournamentFighters()");
		return;
	}

	$nameOrder = NAME_MODE." ASC, ".NAME_MODE_2." ASC";

	if($sortType == 'school'){
		$sortString = "(CASE WHEN schoolShortName='' then 1 ELSE 0 END), schoolShortName ASC, ".$nameOrder;
	} elseif ($sortType == 'rating') {
		$sortString = "rating DESC";
	} else {
		$sortString = $nameOrder;
	}

	$sql = "SELECT rosterID, eventRoster.schoolID, NULL AS teamID,
				tournamentRosterID, ratingID, rating, subGroupNum, rating2, tournamentCheckIn, tournamentGearCheck, tournamentOtherCheck
			FROM eventTournamentRoster
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN systemRoster USING(systemRosterID)
			INNER JOIN systemSchools ON eventRoster.schoolID = systemSchools.schoolID
			LEFT JOIN eventRatings USING(tournamentRosterID)
			WHERE tournamentID = {$tournamentID}
			AND isTeam = 0
			ORDER BY {$sortString}";

	return mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function getTournamentFighterSchoolIDs($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT rosterID, schoolID
			FROM eventTournamentRoster
			INNER JOIN eventRoster USING(rosterID)
			WHERE tournamentID = {$tournamentID}";
	return (array)mysqlQuery($sql, KEY_SINGLES, 'rosterID', 'schoolID');
}

/******************************************************************************/

function getEventSchoolIDs($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT DISTINCT schoolID, schoolShortName
			FROM eventRoster
			INNER JOIN systemSchools USING(schoolID)
			WHERE eventID = {$eventID}
			ORDER BY schoolShortName ASC";
	return (array)mysqlQuery($sql, SINGLES, 'schoolID');
}

/******************************************************************************/

function getNumTournamentFighters($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT numParticipants, tournamentID
			FROM eventTournaments
			WHERE eventID = {$eventID}";

	return mysqlquery($sql, KEY_SINGLES, 'tournamentID','numParicipants');
}

/******************************************************************************/


function getTournamentFightersWithExchangeNumbers($tournamentID){

	$tournamentID = (int)$tournamentID;
	$controlID = (int)ATTACK_CONTROL_DB;

	$sql = "SELECT rosterID, tournamentRosterID
			FROM eventTournamentRoster
			WHERE tournamentID = {$tournamentID}";
	$tRoster = mysqlQuery($sql, KEY, 'rosterID');

	$sql = "SELECT scoringID, receivingID, exchangeType, refPrefix, scoreValue, scoreDeduction
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}";
	$exchanges = mysqlQuery($sql, ASSOC);

	$systemExchangeTypes = [];
	$systemScoringTypes = [];

	foreach($exchanges as $exchange){
		$exchangeType = $exchange['exchangeType'];

		if($exchangeType == 'double'){

			@$fighterExchanges[$exchange['scoringID']]['Doubles']++;	// Might not be set
			@$fighterExchanges[$exchange['receivingID']]['Doubles']++;	// Might not be set
			$systemExchangeTypes['Doubles'] = true;

		} elseif ($exchangeType == 'noQuality'){

			@$fighterExchanges[$exchange['scoringID']]['No Quality']++;	// Might not be set
			$systemExchangeTypes['No Quality'] = true;

		} else{

			if ($exchange['refPrefix'] == ATTACK_CONTROL_DB){
				@$fighterExchanges[$exchange['scoringID']]['Control Points']++;	// Might not be set
				$systemExchangeTypes['Control Points'] = true;
			}

			if(    $exchangeType == 'clean'
				|| $exchangeType == 'afterblow')
			{

				$forStr = "✓ (".$exchange['scoreValue'];
				$againstStr = "✗ (".$exchange['scoreValue'];

				if($exchangeType == 'afterblow'){
					$forStr .= "-".$exchange['scoreDeduction'];
					$againstStr .= "-".$exchange['scoreDeduction'];
				}

				$forStr .= ")";
				$againstStr .= ")";

				@$fighterExchanges[$exchange['scoringID']][$forStr]++;		 // Might not be set
				@$fighterExchanges[$exchange['receivingID']][$againstStr]++; // Might not be set

				$systemScoringTypes[$forStr] = true;
				$systemScoringTypes[$againstStr] = true;
			}

		}
	}

	ksort($systemExchangeTypes);
	ksort($systemScoringTypes);

	foreach($tRoster as $rosterID => $data){
		$tRoster[$rosterID]['exchanges'] = [];
		$tRoster[$rosterID]['points'] = [];

		foreach((array)$systemExchangeTypes as $type => $dummy){
			// The $fighterExchanges might not exist. This is the same as zero.
			$tRoster[$rosterID]['exchanges'][$type] = (int)@$fighterExchanges[$rosterID][$type];
		}

		foreach((array)$systemScoringTypes as $type => $dummy){
			// The $fighterExchanges might not exist. This is the same as zero.
			$tRoster[$rosterID]['points'][$type] = (int)@$fighterExchanges[$rosterID][$type];
		}

	}

	return $tRoster;

}

/******************************************************************************/

function getNumTournamentEntries($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT COUNT(*) AS numParticipants
			FROM eventTournamentRoster
			WHERE tournamentID = {$tournamentID}";
	return (int)mysqlQuery($sql, SINGLE, 'numParticipants');
}

/******************************************************************************/

function getNumEventTournamentEntries($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT COUNT(*) AS numParticipants
			FROM eventTournamentRoster
			INNER JOIN eventTournaments AS eT USING(tournamentID)
			INNER JOIN eventRoster USING(rosterID)
			WHERE eT.eventID = {$eventID}
			AND isTeam = 0";
	return (int)mysqlQuery($sql, SINGLE, 'numParticipants');
}

/******************************************************************************/

function getNumTournamentGroups($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT COUNT(*) AS numTeams
			FROM eventTournamentRoster
			INNER JOIN eventRoster USING(rosterID)
			WHERE tournamentID = {$tournamentID}
			AND isTeam = 1";
	return (int)mysqlQuery($sql, SINGLE, 'numTeams');
}

/******************************************************************************/

function getTournamentRoster($tournamentID, $sortType = null, $excluded = null){
// returns a sorted array of all fighters in a tournament
// indexed by rosterID

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getTournamentRoster()");
		return;
	}


	$nameOrder = NAME_MODE." ASC, ".NAME_MODE_2." ASC";

	if($sortType == 'school'){
		$sortString = "ORDER BY (CASE WHEN schoolShortName='' then 1 ELSE 0 END), schoolShortName ASC, ".$nameOrder;
	} else {
		$sortString = $nameOrder;
	}

	$sql = "SELECT eventRoster.rosterID, systemSchools.schoolShortName,
			systemSchools.schoolAbbreviation
			FROM eventTournamentRoster
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN systemRoster USING(systemRosterID)
			INNER JOIN systemSchools ON eventRoster.schoolID = systemSchools.schoolID
			WHERE eventTournamentRoster.tournamentID = {$tournamentID}
			ORDER BY {$sortString}";

	if($sortType == 'rosterID'){
		return mysqlQuery($sql,KEY,'rosterID');
	} else {
		return mysqlQuery($sql, ASSOC);
	}

}

/******************************************************************************/


function getTournamentPlacings($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getTournamentPlacings()");
		return;
	}

	if(!isTeams($tournamentID)){

		$sql = "SELECT eventRoster.rosterID, eventRoster.schoolID, placing, placeType, lowBound, highBound,
				systemSchools.schoolFullName, systemSchools.schoolBranch
				FROM eventPlacings
				INNER JOIN eventRoster ON eventRoster.rosterID = eventPlacings.rosterID
				INNER JOIN systemRoster ON eventRoster.systemRosterID = systemRoster.systemRosterID
				INNER JOIN systemSchools ON eventRoster.schoolID = systemSchools.schoolID
				WHERE eventPlacings.tournamentID = {$tournamentID}
				ORDER BY placing ASC";
	} else {

		$sql = "SELECT rosterID, placing, placeType, lowBound, highBound
				FROM eventPlacings
				WHERE tournamentID = {$tournamentID}
				ORDER BY placing ASC";
	}
	return mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function getTournamentPlacingsForEdit($tournamentID){

	$rawPlacings = getTournamentPlacings($tournamentID);

	$placings = [];
	$index = 1;
	foreach($rawPlacings as $place){

		$tmp['rosterID'] = $place['rosterID'];

		switch($place['placeType']){
			case 'final':
				$tmp['place'] = $place['placing'];
				$tmp['tie'] = 0;
				break;
			case 'tie':
				$tmp['place'] = $place['placing'];
				$tmp['tie'] = ($place['highBound'] - $place['lowBound']) + 1;
				break;
			case 'bracket':
				$tmp['tie'] = ($place['highBound'] - $place['lowBound']) + 1;
				$tmp['place'] = $place['lowBound'];
				break;
		}

		$placings[$index] = $tmp;
		$index ++;

	}

	return $placings;
}

/******************************************************************************/

function getTournamentsAlphabetical($eventID){

	$eventID = (int)$eventID;
	if($eventID == 0){
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

function getTournamentFormat($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT formatID
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return mysqlQuery($sql, SINGLE, 'formatID');

}

/******************************************************************************/

function getFormatName($formatID){

	$formatID = (int)$formatID;

	$sql = "SELECT formatName
			FROM systemFormats
			WHERE formatID = {$formatID}";
	return mysqlQuery($sql, SINGLE, 'formatName');

}

/******************************************************************************/

function getRankingInfo($tournamentRankingID){

	$tournamentRankingID = (int)$tournamentRankingID;

	$sql = "SELECT name, formatID
			FROM systemRankings
			WHERE tournamentRankingID = {$tournamentRankingID}";
	return mysqlQuery($sql, SINGLE);

}

/******************************************************************************/

function getTournamentLogic($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getTournamentLogic()");
		return;
	}

	$sql = "SELECT logicMode
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return mysqlQuery($sql, SINGLE, 'logicMode');
}

/******************************************************************************/

function getTournamentStandings($tournamentID, $poolSet = 1, $groupType = 'pool', $advancementsOnly = null){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in getTournamentStandings()");
		return;
	}

	$ignoreWhere = '';
	if($advancementsOnly){
		$ignores = getIgnores($tournamentID,'stopAtSet',1);
		$ignoreWhere = "AND eventStandings.rank IS NOT NULL";
		if($ignores != null){
			$ignoreWhere = " AND eventStandings.rosterID NOT IN (".implode(",",$ignores).")";
		}

	}

	if($poolSet == 'all'){
		$groupSet = null;
	} else {
		if($poolSet == null){
			$poolSet = (int)$_SESSION['groupSet'];
		}
		$groupSet = "AND eventStandings.groupSet = {$poolSet}";
	}

	if($groupType == 'pool'){

		$orderName = NAME_MODE." ASC, ".NAME_MODE_2." ASC";

		if(isNoPools($tournamentID)){
			$sql = "SELECT eventRoster.rosterID
					FROM eventTournamentRoster
					INNER JOIN eventRoster ON eventTournamentRoster.rosterID = eventRoster.rosterID
					INNER JOIN systemRoster ON eventRoster.systemRosterID = systemRoster.systemRosterID
					WHERE eventTournamentRoster.tournamentID = {$tournamentID}
					ORDER BY {$orderName}";

		} else {

			if(isMatchesByTeam($tournamentID)){
				$teamClause = "AND isTeam = 1";
			} else {
				$teamClause = "AND isTeam = 0";
			}

			$sql = "SELECT rosterID, eventStandings.rank, score, wins, losses, ties, pointsFor,
					pointsAgainst, doubles, matches,
					hitsFor, hitsAgainst, afterblowsFor, afterblowsAgainst
					FROM eventStandings
					INNER JOIN eventRoster USING(rosterID)
					WHERE tournamentID = {$tournamentID}
					AND groupType = 'pool'
					{$groupSet}
					{$ignoreWhere}
					{$teamClause}
					ORDER BY eventStandings.rank ASC";
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

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
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

function getTournamentsFull($eventID){
// returns an unsorted array of all tournaments at the event, and all attributes
// associated with each
// indexed by tournamentID

	$eventID = (int)$eventID;
	if($eventID == 0){
		setAlert(SYSTEM,"No eventID in getTournamentsFull()");
		return;
	}

	$tournamentIDs = getEventTournaments($eventID);
	$metaTypes = ['weapon', 'prefix', 'ranking', 'gender', 'material'];


	$sql = "SELECT *, (SELECT COUNT(*) AS numFighters
						FROM eventTournamentRoster AS eTR2
						INNER JOIN eventRoster USING(rosterID)
						WHERE eTR2.tournamentID = eT.tournamentID
						AND isTeam = 0) AS numFighters
			FROM eventTournaments AS eT
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

function getNumEventFighters($eventID){
// Get the number of individuals in an event who are entered in at least one tournament.

	$eventID = (int)$eventID;

	$sql = "SELECT COUNT(DISTINCT(rosterID)) AS numFighters
			FROM eventTournamentRoster AS eTR
			INNER JOIN eventTournaments AS eT USING(tournamentID)
			INNER JOIN eventRoster USING(rosterID)
			WHERE eT.eventID = {$eventID}
			AND isTeam = 0";
	$numFighters = (int)mysqlQuery($sql,SINGLE,'numFighters');
	return ($numFighters);

}

/******************************************************************************/

function getNumEventRegistrations($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT COUNT(*) AS numFighters
			FROM eventRoster AS eTR
			WHERE eventID = {$eventID}
			AND isTeam = 0";
	$numRegistrations = (int)mysqlQuery($sql, SINGLE, 'numFighters');

	return ($numRegistrations);

}

/******************************************************************************/

function getMatchVideoLink($matchID){

	$matchID = (int)$matchID;
	if($matchID == 0){
		setAlert(SYSTEM,"No matchID in getVideoLink()");
		return;
	}

	$sql = "SELECT sourceLink, synchTime, synchTime2
			FROM eventVideo
			WHERE matchID = {$matchID}";
	$videoInfo = (array)mysqlQuery($sql, SINGLE);

	if($videoInfo == []){
		$videoInfo['sourceLink'] = '';
		$videoInfo['synchTime'] = 0;
		$videoInfo['synchTime2'] = 0;
	}

	$videoInfo['overlayEnabled'] = true;

	return ($videoInfo);


}

/******************************************************************************/

function getTournamentVideo($tournamentID, $includeNull){

	$tournamentID = (int)$tournamentID;

	if($includeNull == false){
		$isVideoClause = "AND videoLink IS NOT NULL AND videoLink != ''";
	} else {
		$isVideoClause = '';
	}

	$sql = "SELECT matchID, sourceLink
			FROM eventVideo
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			{$isVideoClause}";
	return (array)mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function isDoubleElim($tournamentID){
	// returns true if the tournament has a double elim bracket

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in isDoubleElim()");
		return;
	}

	$sql = "SELECT numFighters
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'
			AND groupNumber = 2";
	$num = mysqlQuery($sql, SINGLE, 'numFighters');

	if($num > 2){
		return true;
	}else {
		return false;
	}

}

/******************************************************************************/

function isPools($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT COUNT(*) as numPools
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'pool'";
	$numPools = mysqlQuery($sql, SINGLE, 'numPools');

	return (boolean)mysqlQuery($sql, SINGLE, 'numPools');

}

/******************************************************************************/

function isResultsOnly($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in isResultsOnly()");
		return;
	}

	$sql = "SELECT formatID
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	if(mysqlQuery($sql, SINGLE, 'formatID') == FORMAT_RESULTS){
		return true;
	} else {
		return false;
	}
}

/******************************************************************************/

function isCumulative($groupSet, $tournamentID){

	$groupSet = (int)$groupSet;
	if($groupSet == 0){
		setAlert(SYSTEM,"No groupSet in isCumulative()");
		return;
	}

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
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

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in isCumulativeRounds()");
		return;
	}

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

function isBrackets($tournamentID){
// Returns true if a bracket has been created for the tournament

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT COUNT(*) AS numBrackets
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'";
	$numBrackets = mysqlQuery($sql, SINGLE,'numBrackets');

	return (bool)$numBrackets;
}

/******************************************************************************/

function isBracketPopulated($tournamentID){
// Returns true if a bracket has been created for the tournament

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT COUNT(*) AS numBracketMatchesPopulated
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'
			AND fighter1ID IS NOT NULL
			AND fighter2ID IS NOT NULL";
	$numBracketMatches = (int)mysqlQuery($sql, SINGLE,'numBracketMatchesPopulated');

	return (bool)$numBracketMatches;
}

/******************************************************************************/

function isTeams($tournamentID = 0){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == 0){return false;}

	$sql = "SELECT isTeams
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";

	return (bool)mysqlQuery($sql, SINGLE, 'isTeams');

}

/******************************************************************************/

function getFighterTeam($rosterID, $tournamentID){

	$rosterID = (int)$rosterID;
	$tournamentID = (int)$tournamentID;

	$sql = "SELECT teamID
			FROM eventTeamRoster team
			INNER JOIN eventTournamentRoster tourn ON team.tournamentRosterID = tourn.tournamentRosterID
			WHERE team.rosterID = {$rosterID}
			AND tournamentID = {$tournamentID}";
	return (int)mysqlQuery($sql, SINGLE, 'teamID');

}


/******************************************************************************/

function getTournamentTeams($tournamentID = 0){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == 0){return false;}

	$sql = "SELECT eR.rosterID as rosterID, NULL AS schoolID, eR.rosterID as teamID
			FROM eventTournamentRoster
			INNER JOIN eventRoster AS eR USING(rosterID)
			WHERE isTeam = TRUE
			AND eventTournamentRoster.tournamentID = {$tournamentID}";

	return mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function getTeamRosters($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		return null;
	}

	$sql = "SELECT teamRost.rosterID, teamRost.teamID, teamRost.tableID, teamRost.teamOrder
			FROM eventTeamRoster as teamRost
			INNER JOIN eventRoster roster ON roster.rosterID = teamRost.teamID
			INNER JOIN eventTournamentRoster as tournRost ON tournRost.rosterID = roster.rosterID
			WHERE tournRost.tournamentID = {$tournamentID}
			AND teamRost.memberRole = 'member'
			ORDER BY teamOrder ASC";

	$allMembers =  mysqlQuery($sql, ASSOC);

	$retVal = [];

	foreach($allMembers as $member){
		$teamID = $member['teamID'];

		$temp['rosterID'] = $member['rosterID'];
		$temp['tableID'] = $member['tableID'];
		$temp['teamOrder'] = $member['teamOrder'];
		$retVal[$teamID]['members'][] = $temp;


	}

	return $retVal;

}

/******************************************************************************/

function getTeamRoster($teamID, $role = 'member'){

	$teamID = (int)$teamID;

	$approvedRoles = ['member'];
	if(!in_array($role, $approvedRoles)){
		setAlert(SYSTEM, 'Invalid role type supplied in getTeamRoster()');
		return;
	}

	$sql = "SELECT rosterID
			FROM eventTeamRoster
			WHERE teamID = {$teamID}
			AND memberRole = '{$role}'
			ORDER BY teamOrder ASC, tableID ASC";
	return mysqlQuery($sql, SINGLES, 'rosterID');

}


/******************************************************************************/
function getTeamFighterByExchange($exchangeID){

	$exchangeID = (int)$exchangeID;

	$sql = "SELECT receivingID
			FROM eventExchanges
			WHERE exchangeID = {$exchangeID}
			AND exchangeType = 'switchFighter'";
	$rosterID = (int)mysqlQuery($sql, SINGLE, 'receivingID');

	return $rosterID;
}

/******************************************************************************/

function getTeamMemberPosition($teamID, $teamMemberRosterID){

	$teamRoster = (array)getTeamRoster($teamID);
	$position = 0;

	foreach($teamRoster as $index => $rosterID){
		if($teamMemberRosterID == $rosterID){
			$position = $index + 1;
		}
	}

	return $position;
}

/******************************************************************************/

function getUngroupedRoster($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		return null;
	}

	$sql = "SELECT eTR1.rosterID
			FROM eventTeamRoster AS eTR1
			INNER JOIN eventTournamentRoster USING(tournamentRosterID)
			WHERE tournamentID = {$tournamentID}
			AND memberRole = 'member'";
	$inTeams = mysqlQuery($sql, SINGLES, 'rosterID');

	if(count($inTeams) > 0){
		$notEligibleIDs = " AND eventTournamentRoster.rosterID NOT IN (".implode(",", $inTeams).")";
	} else {
		$notEligibleIDs = '';
	}


	$sortString = NAME_MODE." ASC, ".NAME_MODE_2." ASC";

	$sql = "SELECT eventTournamentRoster.rosterID, systemSchools.schoolShortName AS school, CONCAT(firstName,' ',lastName) AS name
			FROM eventTournamentRoster
			INNER JOIN eventRoster ON eventTournamentRoster.rosterID = eventRoster.rosterID
			INNER JOIN systemRoster ON eventRoster.systemRosterID = systemRoster.systemRosterID
			INNER JOIN systemSchools ON eventRoster.schoolID = systemSchools.schoolID
			WHERE eventTournamentRoster.tournamentID = {$tournamentID}
			AND isTeam = 0
			{$notEligibleIDs}
			ORDER BY {$sortString}";

	$roster = mysqlQuery($sql, ASSOC);

	return $roster;
}

/******************************************************************************/

function isInProgress($tournamentID, $type = null){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		return false;
	}

	$sql = "SELECT isArchived
			FROM eventTournaments
			INNER JOIN systemEvents USING(eventID)
			WHERE tournamentID = {$tournamentID}";
	$isArchived = (bool)mysqlQuery($sql, SINGLE, 'isArchived');
	if($isArchived == true || isFinalized($tournamentID) == true){
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
			$groupID = (int)$round['groupID'];

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

	$matchID = (int)$matchID;
	if($matchID == 0){
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

function isEventArchived($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT isArchived
			FROM systemEvents
			WHERE eventID = {$eventID}";

	return (bool)mysqlQuery($sql, SINGLE,'isArchived');

}

/******************************************************************************/

function getLatestArchivedEvent(){

	$sql = "SELECT eventStartDate
			FROM systemEvents
			WHERE isArchived = 1
			ORDER BY eventStartDate DESC
			LIMIT 1";

	return mysqlQuery($sql, SINGLE,'eventStartDate');

}

/******************************************************************************/

function isRosterPublished($eventID){

	$eventID = (int)$eventID;
	$isPublished = false;

	if(isEventArchived($eventID) == true){
		$isPublished = true;
	} else {
		$sql = "SELECT publishRoster
				FROM eventPublication
				WHERE eventID = {$eventID}";
		$isPublished = (bool)mysqlQuery($sql, SINGLE, 'publishRoster');
	}

	return $isPublished;

}

/******************************************************************************/

function isSchedulePublished($eventID){

	$eventID = (int)$eventID;
	$isPublished = false;

	if(isEventArchived($eventID) == true){
		$isPublished = true;
	} else {
		$sql = "SELECT publishSchedule
				FROM eventPublication
				WHERE eventID = {$eventID}";
		$isPublished = (bool)mysqlQuery($sql, SINGLE, 'publishSchedule');
	}

	return $isPublished;

}

/******************************************************************************/

function isMatchesPublished($eventID){

	$eventID = (int)$eventID;
	$isPublished = false;

	if(isEventArchived($eventID) == true){
		$isPublished = true;
	} else {
		$sql = "SELECT publishMatches
				FROM eventPublication
				WHERE eventID = {$eventID}";
		$isPublished = (bool)mysqlQuery($sql, SINGLE, 'publishMatches');
	}

	return $isPublished;

}

/******************************************************************************/

function isDescriptionPublished($eventID){

	$eventID = (int)$eventID;
	$isPublished = false;

	if(isEventArchived($eventID) == true){
		$isPublished = true;
	} else {
		$sql = "SELECT publishDescription
				FROM eventPublication
				WHERE eventID = {$eventID}";
		$isPublished = (bool)mysqlQuery($sql, SINGLE, 'publishDescription');
	}

	return $isPublished;

}

/******************************************************************************/

function isRulesPublished($eventID){

	$eventID = (int)$eventID;
	$isPublished = false;

	if(isEventArchived($eventID) == true){
		$isPublished = true;
	} else {
		$sql = "SELECT publishRules
				FROM eventPublication
				WHERE eventID = {$eventID}";
		$isPublished = (bool)mysqlQuery($sql, SINGLE, 'publishRules');
	}

	return $isPublished;

}

/******************************************************************************/

function isEventPublished($eventID){

	$eventID = (int)$eventID;
	$isPublished = false;

	if(isEventArchived($eventID) == true){
		$isPublished = true;
	} else {
		$sql = "SELECT publishRoster, publishSchedule, publishMatches, publishRules
				FROM eventPublication
				WHERE eventID = {$eventID}";
		$publicationStatus = (array)mysqlQuery($sql, SINGLE);

		foreach($publicationStatus as $status){
			if((bool)$status == true){
				$isPublished = true;
				break;
			}
		}
	}

	return $isPublished;

}

/******************************************************************************/

function isEventTermsAccepted($eventID){

	$eventID = (int)$eventID;
	if($eventID == 0){
		setAlert(SYSTEM,"No eventID in isEventTermsAccepted()");
		return false;
	}

	$sql = "SELECT termsOfUseAccepted
			FROM eventSettings
			WHERE eventID = {$eventID}";
	return (bool)mysqlQuery($sql, SINGLE, 'termsOfUseAccepted');

}

/******************************************************************************/

function isLastPiece($tournamentID){
// Determines if all pieces in a tournament have been concluded
// Looks for pieces w/o score, groups w/o matches, and sets w/o groups

	$tournamentID = (int)$tournamentID;
	if(areAllMatchesFinished($tournamentID) == false){
		return false;
	}

// Look for empty groups
	$sql = "SELECT groupID
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}";
	$groups = mysqlQuery($sql, SINGLES, 'groupID');


	foreach($groups as $groupID){

		$groupID = (int)$groupID;

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

function areAllMatchesFinished($tournamentID){

	$tournamentID = (int)$tournamentID;

	$formatID = getTournamentFormat($tournamentID);

	if($formatID == FORMAT_MATCH){
		$condition = "AND matchComplete = 0";
	} elseif($formatID == FORMAT_SOLO){
		$condition = "AND fighter1Score IS NULL";
	} else {
		return true;
	}

	$sql = "SELECT COUNT(*) AS numIncompletes
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			{$condition}
			AND ignoreMatch = 0";

	return (!(bool)mysqlQuery($sql, SINGLE, 'numIncompletes'));
}

/******************************************************************************/

function isLastMatch($tournamentID){
// Check if the tournament is complete
// Used to prompt user to finalize tournament if it is.

	$tournamentID = (int)$tournamentID;
	if(areAllMatchesFinished($tournamentID) == false){
		return false;
	}

	// If there are no incomplete matches, step through all the pool sets
	// and see if they have been populated.
	// i.e. don't ask for finalization on the last match of Set 1 if Set 2 has
	// yet to be created.

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

/******************************************************************************/

function isReverseScore($tournamentID = 0){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){$tournamentID = (int)$_SESSION['tournamentID'];}
	if($tournamentID == 0){
		return null;
	}

	$sql = "SELECT isReverseScore
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return (int)mysqlQuery($sql, SINGLE, 'isReverseScore');
}

/******************************************************************************/

function isNoPools($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
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

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){$tournamentID = (int)$_SESSION['tournamentID'];}
	if($tournamentID == 0){return false;}

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

function areAllTournamentsFinalized($eventID){

	$eventID = (int)$eventID;
	$allAllFinalized = false;

	$sql = "SELECT COUNT(*) AS numUnfinalized
			FROM eventTournaments
			WHERE eventID = {$eventID}
			AND isFinalized = 0";
	$numUnfinalized = (int)mysqlQuery($sql, SINGLE, 'numUnfinalized');


	if($numUnfinalized == 0){

		// Second check to make sure there actually are tournaments at all.
		$sql = "SELECT COUNT(*) AS numTournaments
				FROM eventTournaments
				WHERE eventID = {$eventID}";
		$numTotal = (int)mysqlQuery($sql, SINGLE, 'numTournaments');

		if($numTotal != 0){
			$allAllFinalized = true;
		}
	}

	return $allAllFinalized;

}

/******************************************************************************/

function areAnyTournamentsFinalized($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT COUNT(*) AS numFinalized
			FROM eventTournaments
			WHERE eventID = {$eventID}
			AND isFinalized = 1";
	return (boolean)mysqlQuery($sql, SINGLE, 'numFinalized');

}

/******************************************************************************/

function areMatchesStarted($eventID){
	$eventID = (int)$eventID;

	$sql = "SELECT count(*) AS num
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE matchComplete = 1
			AND eventID = {$eventID}";
	$num = (int)mysqlQuery($sql, SINGLE, 'num');

	if($num >= 10){
		return true;
	} else {
		return false;
	}

}

/******************************************************************************/

function isDoubleHits($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
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

function isFullAfterblow($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in isFullAfterblow()");
		return;
	}

	$sql = "SELECT doubleTypeID
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$id = mysqlQuery($sql, SINGLE, 'doubleTypeID');

	if($id == FULL_AFTERBLOW){
		return true;
	} else {
		return false;
	}

}

/******************************************************************************/

function isDeductiveAfterblow($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in isFullAfterblow()");
		return;
	}

	$sql = "SELECT doubleTypeID
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$id = mysqlQuery($sql, SINGLE, 'doubleTypeID');

	if($id == DEDUCTIVE_AFTERBLOW){
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

	$groupID = (int)$matchInfo['groupID'];
	$matchNumber = (int)$matchInfo['matchNumber'];

	if($matchInfo['placeholderMatchID'] == null){
		$incompleteThreshold = 2;

		$sql = "SELECT count(matchID) AS numMatches
				FROM eventMatches
				WHERE groupID = {$groupID}
				AND matchComplete = 0
				AND ( 	   (matchNumber = ({$matchNumber} - 1) )
						OR (matchNumber = ({$matchNumber} - 2) )
					)
				AND placeholderMatchID IS NULL";
	} else {

		$placeholderMatchID = (int)$matchInfo['placeholderMatchID'];
		$incompleteThreshold = 1;

		$sql = "SELECT count(matchID) AS numMatches
				FROM eventMatches
				WHERE groupID = {$groupID}
				AND matchComplete = 0
				AND placeholderMatchID = {$placeholderMatchID}
				AND matchNumber < {$matchNumber}";
	}

	$numPastIncompletes = mysqlQuery($sql, SINGLE, 'numMatches');

	if(    $numPastIncompletes >= $incompleteThreshold
		&& @$_SESSION['clearOnLogOut']['ignorePastIncompletes'] != true
		&& ALLOW['EVENT_SCOREKEEP'] == true
		&& ALLOW['SOFTWARE_ADMIN'] == false){
		return $numPastIncompletes;
	}

	return 0;

}

/******************************************************************************/

function maxPoolSize($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in maxPoolSize()");
		return;
	}

	$sql = "SELECT maxPoolSize
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$maxPoolSize = mysqlQuery($sql, SINGLE, 'maxPoolSize');

	return $maxPoolSize;

}

/******************************************************************************/

function selectTournamentIdItem($item, $tournamentID){
	//returns the attribute of a specified field for a tournament

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in selectTournamentIdItem()");
		return;
	}

	switch($item){
		case 'tournamentWeaponID': {$field = 'tournamentWeaponID'; break;}
		case 'tournamentPrefixID': {$field = 'tournamentPrefixID'; break;}
		case 'tournamentSuffixID': {$field = 'tournamentSuffixID'; break;}
		case 'tournamentGenderID': {$field = 'tournamentGenderID'; break;}
		case 'tournamentMaterialID': {$field = 'tournamentMaterialID'; break;}
		default:
			{
				setAlert(SYSTEM,"Invalid field in selectTournamentIdItem()");
				return;
				break;
			}
	}

	$sql = "SELECT {$field}
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return mysqlQuery($sql, SINGLE, $item);
}

/******************************************************************************/

function getScoringFunctionName($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
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

function getRankingTypeDescriptions(){
	$sql = "SELECT tournamentRankingID, name, description
			FROM systemRankings
			ORDER BY name ASC";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function getRankingDescriptionByTournament($tournamentID){
	$tournamentID = (int)$tournamentID;

	$sql = "SELECT name, description, poolWinnersFirst, basePointValue
			FROM eventTournaments AS eT
			INNER JOIN systemRankings USING(tournamentRankingID)
			WHERE tournamentID = {$tournamentID}";

	return mysqlQuery($sql, SINGLE);

}

/******************************************************************************/

function getTournamentRankingTypeID($tournamentID){
	$tournamentID = (int)$tournamentID;

	$sql = "SELECT formatID
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return (int)mysqlQuery($sql, SINGLE, 'formatID');
}

/******************************************************************************/

function getDisplayFunctionName($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
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

function getTournamentColors($tournamentID)
{
// This is not zero indexed so that the array index maps to the fight number.
	$tournamentID = (int)$tournamentID;

	$sql = "SELECT colorName
			FROM eventTournaments AS eT
			INNER JOIN systemColors AS sC ON eT.color1ID = colorID
			WHERE tournamentID = {$tournamentID}";
	$color[1] = mysqlQuery($sql, SINGLE, 'colorName');

	$sql = "SELECT colorName
			FROM eventTournaments AS eT
			INNER JOIN systemColors AS sC ON eT.color2ID = colorID
			WHERE tournamentID = {$tournamentID}";
	$color[2] = mysqlQuery($sql, SINGLE, 'colorName');

	return ($color);

}

/******************************************************************************/

function getAttackName($attackID)
{
	$attackID = (int)$attackID;

	$sql = "SELECT attackText
			FROM systemAttacks
			WHERE attackID = {$attackID}";
	return (mysqlQuery($sql, SINGLE, 'attackText'));
}

/******************************************************************************/

function getScoresheets($eventID, $tournamentID)
{
	$eventID = (int)$eventID;
	$tournamentID = (int)$tournamentID;

	if($tournamentID != 0){
		$whereClause = "tournamentID = {$tournamentID}";
	} elseif ($eventID != 0) {
		$whereClause = "eventID = {$eventID}";
	} else {
		return ([]);
	}

	$sql = "SELECT matchID, scoresheet
			FROM eventScoresheets
			WHERE {$whereClause}
			ORDER BY tournamentID, matchID, scoresheetID";
	return (mysqlQuery($sql, ASSOC));

}


/******************************************************************************/

function getEventEndDate($eventID){

	$eventID = (int)$eventID;
	if($eventID == 0){return;}

	$sql = "SELECT GREATEST(eventStartDate, eventEndDate) AS eventDate
			FROM systemEvents
			WHERE eventID = {$eventID}";
	return mysqlQuery($sql, SINGLE, 'eventDate');

}

/******************************************************************************/

function doesUserHavePermission($systemUserID, $eventID, $permission){
// This function checks if a user EITHER:
// - Has the specified permissions globally
// - Has admin privileges for the event in question

	$systemUserID = (int)$systemUserID;
	$eventID = (int)$eventID;
	$isAdmin = false;

// Function input validation to ensure a valid permission was passed
	$permissionsList =
		['EVENT_VIDEO','EVENT_SCOREKEEP','EVENT_MANAGEMENT',
		'SOFTWARE_EVENT_SWITCHING','SOFTWARE_ASSIST','SOFTWARE_ADMIN',
		'STATS_EVENT','STATS_ALL',
		'VIEW_HIDDEN','VIEW_SETTINGS','VIEW_EMAIL'];
	$validPermissionType = false;

	foreach($permissionsList as $valid){
		if($valid == $permission){
			$validPermissionType = true;
			break;
		}
	}

// Check user
	if($validPermissionType == true){
		$sql = "SELECT {$permission}
				FROM systemUsers
				WHERE userID = {$systemUserID}";
		$isAdmin = (boolean)((int)mysqlQuery($sql, SINGLE, $permission));

		if($isAdmin == false){
			$sql = "SELECT count(*) AS num
					FROM systemUserEvents
					WHERE userID = {$systemUserID}
					AND eventID = {$eventID}";
			$isAdmin = (boolean)((int)mysqlQuery($sql, SINGLE, 'num'));
		}
	}

	return $isAdmin;

}

/******************************************************************************/

function isAnyEventInfoViewable(){

	if(    ALLOW['VIEW_ROSTER'] == true
		|| ALLOW['VIEW_SCHEDULE'] == true
		|| ALLOW['VIEW_MATCHES'] == true
		|| ALLOW['VIEW_RULES'] == true)
	{
		$viewable = true;
	} else {
		$viewable = false;
	}

	return $viewable;

}

/******************************************************************************/

function getEventRules($eventID){

	$eventID = (int)$eventID;

	$sql = "SELECT rulesID
			FROM eventRules
			WHERE eventID = {$eventID}
			ORDER BY rulesOrder ASC, rulesName ASC";
	return (array)mysqlQuery($sql, SINGLES, 'rulesID');
}

/******************************************************************************/

function getEventFaq($eventID){
	$eventID = (int)$eventID;

	$sql = "SELECT faqID, faqQuestion, faqAnswer
			FROM logisticsFaq
			WHERE eventID = {$eventID}
			ORDER BY faqOrder ASC";
	return (array)mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function getTournamentRules($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT rulesID, rulesName
			FROM eventRulesLinks
			INNER JOIN eventRules USING(rulesID)
			WHERE tournamentID = {$tournamentID}";
	return (array)mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function getRulesName($rulesID){

	$rulesID = (int)$rulesID;
	$sql = "SELECT rulesName
			FROM eventRules
			WHERE rulesID = {$rulesID}";
	return mysqlQuery($sql, SINGLE, 'rulesName');

}

/******************************************************************************/

function getRulesInfo($rulesID){

	$rulesID = (int)$rulesID;
	$sql = "SELECT rulesName, rulesText
			FROM eventRules
			WHERE rulesID = {$rulesID}";
	$rulesInfo = mysqlQuery($sql, SINGLE);

	$sql = "SELECT tournamentID
			FROM eventRulesLinks
			WHERE rulesID = {$rulesID}";
	$tournamentIDs = (array)mysqlQuery($sql, SINGLES, 'tournamentID');

	$rulesInfo['tournamentIDs'] = [];
	foreach($tournamentIDs as $tournamentID){
		$rulesInfo['tournamentIDs'][$tournamentID] = $tournamentID;
	}

	return $rulesInfo;

}

/******************************************************************************/

function getSponsorListGear($type = null){

	$sql = "SELECT sponsorID, sponsorName
			FROM systemSponsors
			WHERE sponsorType = 'gear'
			ORDER BY sponsorName ASC";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function getSponsorListEvent($type = null){

	$sql = "SELECT sponsorID, sponsorName
			FROM systemSponsors
			WHERE sponsorType = 'event'
			ORDER BY sponsorName ASC";
	return mysqlQuery($sql, ASSOC);
}


/******************************************************************************/

function getSponsorListLocal($type = null){

	$sql = "SELECT sponsorID, sponsorName
			FROM systemSponsors
			WHERE sponsorType = 'local'
			ORDER BY sponsorName ASC";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function getEventSponsors($eventID, $ignoreTiers = false){

	$eventID = (int)$eventID;

	if($ignoreTiers == false){
		$sortTier = "eventSponsorPercent DESC,";
	} else {
		$sortTier = "";
	}

	$sql = "SELECT sponsorID, eventSponsorID, eventSponsorPercent, sponsorName
			FROM eventSponsors
			INNER JOIN systemSponsors USING(sponsorID)
			WHERE eventID = {$eventID}
			ORDER BY {$sortTier} sponsorName ASC";
	$sponsors = (array)mysqlQuery($sql, ASSOC, 'sponsorID', 'eventSponsorID');

	$eventSponsors = [];
	foreach($sponsors AS $sponsor){
		$eventSponsors[$sponsor['sponsorID']]['sponsorID'] = (int)$sponsor['sponsorID'];
		$eventSponsors[$sponsor['sponsorID']]['eventSponsorID'] = (int)$sponsor['eventSponsorID'];
		$eventSponsors[$sponsor['sponsorID']]['eventSponsorPercent'] = (int)$sponsor['eventSponsorPercent'];
		$eventSponsors[$sponsor['sponsorID']]['sponsorName'] = $sponsor['sponsorName'];
	}

	return $eventSponsors;

}

/******************************************************************************/

function getTournamentExchangeTimeData($tournamentID){

	$tournamentID = (int)$tournamentID;
	$MAX_MATCH_LENGTH = 1000;

	$sql = "SELECT exchangeTime, UNIX_TIMESTAMP(timestamp) AS realTime, exchangeType, matchID, groupID, groupType
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND matchComplete = 1
			ORDER BY groupID ASC, matchID ASC, matchNumber ASC, exchangeNumber ASC";
	$exchanges = mysqlQuery($sql, ASSOC);

	$matchID = 0;
	$groupID = 0;
	$matchExchangeCount = 0;

	$tLength['exch']['total']['data'] = [];
	$tLength['exch']['fight']['data'] = [];
	$tLength['exch']['judge']['data'] = [];
	$tLength['exch']['count']['data'] = [];

	$tLength['match']['length']['data'] = [];
	$tLength['match']['count']['data'] = [];
	$tLength['match']['change']['data'] = [];

	foreach($exchanges as $index => $exchange){

		if($groupID != $exchange['groupID']){

			if($matchID != 0){

				$matchLengthTime = $lastRealTime - $matchStartRealTime;

				if($matchLengthTime < $MAX_MATCH_LENGTH){
					$tLength['match']['length']['data'][] = $matchLengthTime;
				}
				// There are no match change times when switching between groups

				if($matchExchangeCount != 0){
					$tLength['exch']['count']['data'][] = $matchExchangeCount;
					$matchExchangeCount = 0;
				}

			}

			$groupID = $exchange['groupID'];
			$lastRealTime = 0;
			$matchStartRealTime = $exchange['realTime'] - $exchange['exchangeTime'];
		}

		if($matchID != $exchange['matchID']){

			if($lastRealTime != 0){

				$matchLengthTime = $lastRealTime - $matchStartRealTime;

				$matchStartRealTime = $exchange['realTime'] - $exchange['exchangeTime'];
				$matchChangeTime = $matchStartRealTime - $lastRealTime;

				if($matchLengthTime < $MAX_MATCH_LENGTH){
					$tLength['match']['length']['data'][] = $matchLengthTime;
				}
				if($matchChangeTime < $MAX_MATCH_LENGTH && $exchange['groupType'] == 'pool' && $matchChangeTime > 0){
					$tLength['match']['change']['data'][] = $matchChangeTime;
				}

			}

			if($matchExchangeCount != 0){
				$tLength['exch']['count']['data'][] = $matchExchangeCount;
				$matchExchangeCount = 0;
			}

			$lastRealTime = $exchange['realTime'] - $exchange['exchangeTime'];
			$lastExchangeTime = 0;

			$matchID = $exchange['matchID'];
		}

		if(isScoringExchange($exchange['exchangeType']) == true){

			$matchExchangeCount++;

			$exchangeTime = $exchange['realTime'] - $lastRealTime;
			$fightTime = $exchange['exchangeTime'] - $lastExchangeTime;
			$judgeTime = $exchangeTime - $fightTime;
			$tLength['exch']['total']['data'][] = $exchangeTime;

			if($fightTime > 0){
				$tLength['exch']['fight']['data'][] = $fightTime;
			}
			if($judgeTime > 0){
				$tLength['exch']['judge']['data'][] = $judgeTime;
			}

			$lastExchangeTime = $exchange['exchangeTime'];
			$lastRealTime = $exchange['realTime'];
		}

	}

	arrayAvg($tLength['match']['length']);
	arrayAvg($tLength['match']['change']);
	arrayAvg($tLength['exch']['count'],1);

	arrayAvg($tLength['exch']['total'],1);
	arrayAvg($tLength['exch']['fight'],1);
	arrayAvg($tLength['exch']['judge'],1);

	return($tLength);
}

/******************************************************************************/

function isLastExchZeroPointClean($matchInfo, $num){

	$matchID = (int)$matchInfo['matchID'];

	$sql = "SELECT exchangeType, scoreValue, scoringID
			FROM eventExchanges
			WHERE matchID = {$matchID}
			AND exchangeType IN ('clean','double','afterblow')
			ORDER BY exchangeNumber DESC";
	$lastScoring = mysqlQuery($sql, SINGLE);

	if($lastScoring == null){
		return false;
	}

	// Check if the last exchange is a valid exchange of the type we are looking for.
	if($lastScoring['exchangeType'] != 'clean' || (int)$lastScoring['scoreValue'] != 0){
		return false;
	}

	if($num == 1 && $lastScoring['scoringID'] == $matchInfo['fighter1ID']){
		return true;
	} else if($num == 2 && $lastScoring['scoringID'] == $matchInfo['fighter2ID']){
		return true;
	} else {
		return false;
	}

}

/******************************************************************************/

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
