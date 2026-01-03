<?php
/*******************************************************************************
	Stats Function

	Functions explicitly for statistical usage or displaying data.
	Split out to avoid continually cluttering DB_read or display files.

*******************************************************************************/

// START OF DOCUMENT ///////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

define("STATS_VALID_EXCHANGES", "'clean','afterblow','double','noExchange','scored'");

function makeEventListStr($year, $futureView = false){

	$year = (int)$year;

	if($year != 0){

		$whereClause = "";
		if(ALLOW['SOFTWARE_ADMIN'] == false){
			$whereClause .= " AND eventStatus != 'hidden' ";
		}
		if(ALLOW['SOFTWARE_ADMIN'] == false || $futureView == false){
			$whereClause .= " AND eventStartDate <= NOW() ";
		}

		$sql = "SELECT eventID
				FROM systemEvents
				WHERE eventYear = {$year}
				AND isMetaEvent = 0
				AND eventName NOT LIKE '%=%'
				{$whereClause}";
		$eventList = (array)mysqlQuery($sql, SINGLES);
		$eventListStr = "eventID IN (".implode2int($eventList).")";

	} else {
		$eventListStr = "eventID IS NOT NULL";
	}

	return ($eventListStr);

}

/******************************************************************************/

function getYearlySummaryCounts($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT COUNT(*) as num
			FROM systemEvents
			WHERE {$eventListStr}";
	$counts['events']['data'] = (int)mysqlQuery($sql, SINGLE, 'num');
	$counts['events']['name'] = "Number of Events ";

	$counts['countries']['data'] = sizeof(getAnnualEventsByCountry($year, $futureView));
	$counts['countries']['name'] = "Countries Represented ";

	$sql = "SELECT COUNT(*) as num
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE {$eventListStr}";
	$counts['exchanges']['data'] = (int)mysqlQuery($sql, SINGLE, 'num');
	$counts['exchanges']['name'] = "Exchanges Recorded";


	$sql = "SELECT COUNT(*) as num
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE {$eventListStr}";
	$counts['matches']['data'] = (int)mysqlQuery($sql, SINGLE, 'num');
	$counts['matches']['name'] = "Matches Fought";

	$sql = "SELECT COUNT(*) as num
			FROM eventTournaments
			WHERE {$eventListStr}";
	$counts['tournaments']['data'] = (int)mysqlQuery($sql, SINGLE, 'num');
	$counts['tournaments']['name'] = "Tournaments Created";

	$sql = "SELECT COUNT(*) as num
			FROM eventTournamentRoster
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE {$eventListStr}";
	$counts['entries_tournament']['data'] = (int)mysqlQuery($sql, SINGLE, 'num');
	$counts['entries_tournament']['name'] = "Tournament Entries";

	$sql = "SELECT COUNT(*) as num
			FROM eventRoster
			WHERE {$eventListStr}";
	$counts['entries_event']['data'] = (int)mysqlQuery($sql, SINGLE, 'num');
	$counts['entries_event']['name'] = "Event Registrations";

	$sql = "SELECT COUNT(DISTINCT(systemRosterID)) as num
			FROM eventRoster
			WHERE {$eventListStr}";
	$counts['entries_event_unique']['data'] = (int)mysqlQuery($sql, SINGLE, 'num');
	$counts['entries_event_unique']['name'] = "Unique Participants";

	$sql = "SELECT COUNT(DISTINCT(schoolID)) as num
			FROM eventRoster
			WHERE {$eventListStr}";
	$counts['schools_unique']['data'] = (int)mysqlQuery($sql, SINGLE, 'num');
	$counts['schools_unique']['name'] = "Clubs Represented";

	return ($counts);
}

/******************************************************************************/

function getAnnualEventsByCountry($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT countryName, COUNT(*) AS numEvents
			FROM systemEvents
			INNER JOIN systemCountries USING(countryIso2)
			WHERE {$eventListStr}
			GROUP BY countryIso2
			ORDER BY numEvents DESC, countryIso2 ASC";
	$eventList = (array)mysqlQuery($sql, ASSOC);

	$numCountries = sizeof($eventList);

	$totalNumEvents = 0;
	foreach($eventList as $e){
		$totalNumEvents += $e['numEvents'];
	}

	$eventsByCountry = [];
	foreach($eventList as $i => $f){
		$eventsByCountry[$i]['value'] = $f['numEvents'];
		$eventsByCountry[$i]['name'] = $f['countryName'];
	}

	return ($eventsByCountry);
}

/******************************************************************************/

function getAnnualExchangesByCountry($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);
	$validExchanges = STATS_VALID_EXCHANGES;

	$sql = "SELECT countryName, COUNT(*) AS numExchanges
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
				INNER JOIN systemCountries USING(countryIso2)
			WHERE {$eventListStr}
				AND exchangeType IN ($validExchanges)
			GROUP BY countryIso2
			ORDER BY numExchanges DESC, countryIso2 ASC";
	$eventList = (array)mysqlQuery($sql, ASSOC);

	$exchangesByCountry = [];
	foreach($eventList as $i => $f){
		$exchangesByCountry[$i]['value'] = $f['numExchanges'];
		$exchangesByCountry[$i]['name'] = $f['countryName'];
	}

	return ($exchangesByCountry);
}

/******************************************************************************/

function getAnnualExchangesByNonusCountry($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);
	$validExchanges = STATS_VALID_EXCHANGES;

	$sql = "SELECT countryName, COUNT(*) AS numExchanges
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
				INNER JOIN systemCountries USING(countryIso2)
			WHERE {$eventListStr}
				AND countryIso2 != 'US'
				AND exchangeType IN ($validExchanges)
			GROUP BY countryName
			ORDER BY numExchanges DESC, countryName ASC";
	$eventList = (array)mysqlQuery($sql, ASSOC);

	$exchangesByUsState = [];
	foreach($eventList as $i => $f){
		$exchangesByUsState[$i]['value'] = $f['numExchanges'];
		$exchangesByUsState[$i]['name'] = $f['countryName'];
	}

	return ($exchangesByUsState);
}

/******************************************************************************/

function getAnnualExchangesByUsState($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);
	$validExchanges = STATS_VALID_EXCHANGES;

	$sql = "SELECT eventProvince, COUNT(*) AS numExchanges
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND countryIso2 = 'US'
				AND exchangeType IN ($validExchanges)
			GROUP BY eventProvince
			ORDER BY numExchanges DESC, eventProvince ASC";
	$eventList = (array)mysqlQuery($sql, ASSOC);

	$exchangesByUsState = [];
	foreach($eventList as $i => $f){
		$exchangesByUsState[$i]['value'] = $f['numExchanges'];
		$exchangesByUsState[$i]['name'] = $f['eventProvince'];
	}

	return ($exchangesByUsState);
}

/******************************************************************************/

function getAnnualEventsByNonusCountry($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT countryName, COUNT(*) AS numEvents
			FROM systemEvents
			INNER JOIN systemCountries USING(countryIso2)
			WHERE {$eventListStr}
				AND countryIso2 != 'US'
			GROUP BY countryName
			ORDER BY numEvents DESC, countryName ASC";
	$eventList = (array)mysqlQuery($sql, ASSOC);

	$numCountries = sizeof($eventList);

	$totalNumEvents = 0;
	foreach($eventList as $e){
		$totalNumEvents += $e['numEvents'];
	}

	$eventsByUsState = [];
	foreach($eventList as $i => $f){
		$eventsByUsState[$i]['value'] = $f['numEvents'];
		$eventsByUsState[$i]['name'] = $f['countryName'];
	}

	return ($eventsByUsState);
}

/******************************************************************************/

function getAnnualEventsByUsState($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT eventProvince, COUNT(*) AS numEvents
			FROM systemEvents
			WHERE {$eventListStr}
				AND countryIso2 = 'US'
			GROUP BY eventProvince
			ORDER BY numEvents DESC, eventProvince ASC";
	$eventList = (array)mysqlQuery($sql, ASSOC);

	$numCountries = sizeof($eventList);

	$totalNumEvents = 0;
	foreach($eventList as $e){
		$totalNumEvents += $e['numEvents'];
	}

	$eventsByUsState = [];
	foreach($eventList as $i => $f){
		$eventsByUsState[$i]['value'] = $f['numEvents'];
		$eventsByUsState[$i]['name'] = $f['eventProvince'];
	}

	return ($eventsByUsState);
}

/******************************************************************************/

function getAnnualEventsByMonth($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT eventStartDate, eventEndDate
			FROM systemEvents
			WHERE {$eventListStr}
			ORDER BY eventStartDate ASC";
	$eventList = (array)mysqlQuery($sql, ASSOC);

	$eventsByMonth = [];

	for($i = 0; $i < 12; $i++){
		$monthNum = $i + 1; // To make the array zero index
		$eventsByMonth[$i]['name'] = date('F',strtotime("2000-{$monthNum}-01"));
		$eventsByMonth[$i]['value'] = 0;
	}

	foreach($eventList as $e){
		$month = (int)date('n',strtotime($e['eventStartDate']))-1;
		 // Subtract one to make the array zero index
		$eventsByMonth[$month]['value']++;
	}


	return ($eventsByMonth);
}

/******************************************************************************/

function getAnnualEventsByDays($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT eventStartDate, eventEndDate
			FROM systemEvents
			WHERE {$eventListStr}
			ORDER BY eventStartDate ASC";
	$eventList = (array)mysqlQuery($sql, ASSOC);

	$plotEventsByDays = [];

	$MAX_DAYS = 4;

	for($i = 0; $i < $MAX_DAYS; $i++){
		$day = $i+1;

		if($day != $MAX_DAYS){
			$plotEventsByDays[$i]['name'] = $day." Day".plrl($day);
		} else {
			$plotEventsByDays[$i]['name'] = $day."+ Days";
		}


		$plotEventsByDays[$i]['value'] = 0;
	}

	foreach($eventList as $e){
		$start = strtotime($e['eventStartDate']);
		$end = strtotime($e['eventEndDate']);
		$datediff = 1 + (($end - $start) / (60 * 60 * 24));
		$index = $datediff - 1; // Javascript will need a zero-indexed array

		if($index > $MAX_DAYS){
			$index = $MAX_DAYS;
		}

		if(isset($plotEventsByDays[$index]) == true){
			$plotEventsByDays[$index]['value']++;
		}
	}

	return ($plotEventsByDays);
}

/******************************************************************************/

function getAnnualExchangesByEvent($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$validExchanges = "'clean','afterblow','double','noExchange','scored'";

	$sql = "SELECT eventID, COUNT(*) AS value
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND exchangeType IN ($validExchanges)
			GROUP BY eventID
			ORDER BY value DESC";
	$exchangesByEvent = (array)mysqlQuery($sql, ASSOC);

	$rawName = true;
	if($eventListStr == "eventID IS NOT NULL"){
		$rawName = false;
	}

	$plotExchangesByEvent = [];
	foreach($exchangesByEvent as $event){
		$tmp = [];
		$tmp['name'] = getEventName($event['eventID'], $rawName);
		$tmp['value'] = $event['value'];
		$plotExchangesByEvent[] = $tmp;
	}


	return($plotExchangesByEvent);
}

/******************************************************************************/

function getAnnualExchangesByEventDay($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$validExchanges = "'clean','afterblow','double','noExchange','scored'";

	$sql = "SELECT eventID, COUNT(*) AS value, DATEDIFF(eventEndDate,eventStartDate)+1 AS days
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND exchangeType IN ($validExchanges)
			GROUP BY eventID
			ORDER BY value DESC";
	$exchangesByEvent = (array)mysqlQuery($sql, ASSOC);

	$rawName = true;
	if($eventListStr == "eventID IS NOT NULL"){
		$rawName = false;
	}

	$plotExchangesByEvent = [];
	foreach($exchangesByEvent as $event){
		$tmp = [];
		$tmp['name'] = getEventName($event['eventID'], $rawName);
		$tmp['value'] = $event['value']/$event['days'];
		$plotExchangesByEvent[] = $tmp;
	}

	$price = array_column($plotExchangesByEvent, 'value');

	array_multisort($price, SORT_DESC, $plotExchangesByEvent);


	return($plotExchangesByEvent);
}

/******************************************************************************/

function getAnnualMatchesByEvent($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT eventID, COUNT(*) AS value
			FROM eventMatches AS eM
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
			GROUP BY eventID
			ORDER BY value DESC";
	$matchesByEvent = (array)mysqlQuery($sql, ASSOC);

	$rawName = true;
	if($eventListStr == "eventID IS NOT NULL"){
		$rawName = false;
	}

	$plotMatchesByEvent = [];
	foreach($matchesByEvent as $event){
		$tmp = [];
		$tmp['name'] = getEventName($event['eventID'], $rawName);
		$tmp['value'] = $event['value'];
		$plotMatchesByEvent[] = $tmp;
	}

	return($plotMatchesByEvent);
}

/******************************************************************************/

function getAnnualTournamentsByEvent($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT eventID, COUNT(*) AS value
			FROM eventTournaments AS eT
			INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
			GROUP BY eventID
			ORDER BY value DESC, eventName ASC";
	$tournamentsByEvent = (array)mysqlQuery($sql, ASSOC);

	$rawName = true;
	if($eventListStr == "eventID IS NOT NULL"){
		$rawName = false;
	}

	$plotTournamentsByEvent = [];
	foreach($tournamentsByEvent as $event){
		$tmp = [];
		$tmp['name'] = getEventName($event['eventID'], $rawName);
		$tmp['value'] = $event['value'];
		$plotTournamentsByEvent[] = $tmp;
	}


	return($plotTournamentsByEvent);
}

/******************************************************************************/

function getAnnualWomensByEvent($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT eventID, eventName AS name, numParticipants
			FROM eventTournaments AS eT
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND tournamentGenderID IN (21,109,125, 132)
			ORDER BY eventName DESC";
	$womensTournaments = (array)mysqlQuery($sql, ASSOC);

	$urgTournaments = (array)mysqlQuery($sql, ASSOC);

	$numTournaments = 0;
	$regByEvent = [];

	$rawName = true;
	if($eventListStr == "eventID IS NOT NULL"){
		$rawName = false;
	}


	foreach($urgTournaments as $t){
		$eventID = $t['eventID'];
		@$regByEvent[$eventID]['value'] += $t['numParticipants'];
		if(isset($regByEvent[$eventID]['name']) == false){
			$regByEvent[$eventID]['name'] = getEventName($eventID, $rawName);
		}
	}

	usort($regByEvent, function($a, $b) {
		return $a['value'] < $b['value'];
	});

	return($regByEvent);
}

/******************************************************************************/

function getAnnualEntriesByClub($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT schoolID, COUNT(*) AS numReg
			FROM eventRoster AS eR
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND eR.schoolID IS NOT NULL
				AND eR.schoolID != 1
				AND eR.schoolID != 2
			GROUP BY schoolID
			ORDER BY numReg DESC
			LIMIT 100";
	$regBySchool = (array)mysqlQuery($sql, ASSOC);

    $plotRegBySchool = [];
	foreach($regBySchool as $i => $f){
		$plotRegBySchool[$i]['value'] = $f['numReg'];
		$plotRegBySchool[$i]['name'] = getSchoolName($f['schoolID']);
	}

	return($plotRegBySchool);

}

/******************************************************************************/

function getAnnualMatchesByClub($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT eR.schoolID, COUNT(*) AS numMatches
			FROM eventMatches AS eM
				INNER JOIN eventRoster AS eR ON eM.fighter1ID = eR.rosterID
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND eR.schoolID IS NOT NULL
				AND eR.schoolID != 1
				AND eR.schoolID != 2
			GROUP BY eR.schoolID
			ORDER BY numMatches DESC
			LIMIT 150";
	$matchBySchool1 = (array)mysqlQuery($sql, ASSOC);

	$sql = "SELECT eR.schoolID, COUNT(*) AS numMatches
			FROM eventMatches AS eM
				INNER JOIN eventRoster AS eR ON eM.fighter2ID = eR.rosterID
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND eR.schoolID IS NOT NULL
				AND eR.schoolID != 1
				AND eR.schoolID != 2
			GROUP BY eR.schoolID
			ORDER BY numMatches DESC
			LIMIT 150";
	$matchBySchool2 = (array)mysqlQuery($sql, ASSOC);


	$sortMatchBySchool = [];
	foreach($matchBySchool1 as $e){
		$sortMatchBySchool[$e['schoolID']] = $e['numMatches'];
	}

	foreach($matchBySchool2 as $e){
		@$sortMatchBySchool[$e['schoolID']] += $e['numMatches'];
	}

	arsort($sortMatchBySchool);

	$matchBySchool = [];
	$i = 0;
	foreach($sortMatchBySchool as $schoolID => $numMatches){
		$matchBySchool[$i]['value'] = $numMatches;
		$matchBySchool[$i]['name'] = getSchoolName($schoolID);
		$i++;
		if($i >= 100){
			break;
		}
	}
	return($matchBySchool);
}

/******************************************************************************/

function getAnnualExchangesByClub($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);
	$validExchanges = "'clean','afterblow','double','noExchange','scored'";

	$sql = "SELECT eR.schoolID, COUNT(*) AS numExchanges
			FROM eventExchanges
				INNER JOIN eventMatches AS eM USING(matchID)
				INNER JOIN eventRoster AS eR ON eM.fighter1ID = eR.rosterID
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND eR.schoolID IS NOT NULL
				AND eR.schoolID != 1
				AND eR.schoolID != 2
				AND exchangeType IN ($validExchanges)
			GROUP BY eR.schoolID
			ORDER BY numExchanges DESC
			LIMIT 60";
	$exchBySchool1 = (array)mysqlQuery($sql, ASSOC);

	$sql = "SELECT eR.schoolID, COUNT(*) AS numExchanges
			FROM eventExchanges
				INNER JOIN eventMatches AS eM USING(matchID)
				INNER JOIN eventRoster AS eR ON eM.fighter2ID = eR.rosterID
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND eR.schoolID IS NOT NULL
				AND eR.schoolID != 1
				AND eR.schoolID != 2
				AND exchangeType IN ($validExchanges)
			GROUP BY eR.schoolID
			ORDER BY numExchanges DESC
			LIMIT 60";
	$exchBySchool2 = (array)mysqlQuery($sql, ASSOC);

	$exchBySchool = [];
	foreach($exchBySchool1 as $e){
		$exchBySchool[$e['schoolID']] = $e['numExchanges'];
	}

	foreach($exchBySchool2 as $e){
		@$exchBySchool[$e['schoolID']] += $e['numExchanges'];
	}

	arsort($exchBySchool);

	$plotExchBySchool = [];
	$i = 0;
	foreach($exchBySchool as $schoolID => $numExchanges){
		$plotExchBySchool[$i]['value'] = $numExchanges;
		$plotExchBySchool[$i]['name'] = getSchoolName($schoolID);
		$i++;
		if($i >= 100){
			break;
		}
	}

	return($plotExchBySchool);
}

/******************************************************************************/

function getAnnualWinsByClub($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT eR.schoolID, COUNT(*) AS numMatches
			FROM eventMatches AS eM
				INNER JOIN eventRoster AS eR ON eM.winnerID = eR.rosterID
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
			AND eR.schoolID IS NOT NULL
			AND eR.schoolID != 1
			AND eR.schoolID != 2
			GROUP BY eR.schoolID
			ORDER BY numMatches DESC
			LIMIT 100";
	$winsBySchool = (array)mysqlQuery($sql, ASSOC);

    $plotWinsBySchool = [];
	foreach($winsBySchool as $i => $f){
		$plotWinsBySchool[$i]['value'] = $f['numMatches'];
		$plotWinsBySchool[$i]['name'] = getSchoolName($f['schoolID']);
	}

	return($plotWinsBySchool);
}

/******************************************************************************/

function getAnnualTournamentsByWeapon($year, $futureView){
	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT tournamentType AS name, COUNT(*) AS value
			FROM eventTournaments AS eT
			INNER JOIN systemTournaments AS sT ON eT.tournamentWeaponID = sT.tournamentTypeID
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
			GROUP BY tournamentType
			ORDER BY value DESC, tournamentType ASC";
	$plotTournamentsByWeapon = (array)mysqlQuery($sql, ASSOC);

	return($plotTournamentsByWeapon);
}

/******************************************************************************/

function getAnnualExchangesByWeapon($year, $futureView){
	$eventListStr = makeEventListStr($year, $futureView);

	$validExchanges = "'clean','afterblow','double','noExchange','scored'";

	$sql = "SELECT tournamentType as name, COUNT(*) AS value
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments AS eT USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
				INNER JOIN systemTournaments AS sT ON eT.tournamentWeaponID = sT.tournamentTypeID
			WHERE {$eventListStr}
				AND exchangeType IN ($validExchanges)
			GROUP BY tournamentType
			ORDER BY value DESC";
	$exchangesByWeapon = (array)mysqlQuery($sql, ASSOC);


	return($exchangesByWeapon);
}

/******************************************************************************/

function getAnnualWTournamentsByWeapon($year, $futureView){
	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT tournamentType AS weaponName, eventName, numParticipants
			FROM eventTournaments AS eT
				INNER JOIN systemEvents USING(eventID)
				INNER JOIN systemTournaments AS sT ON eT.tournamentWeaponID = sT.tournamentTypeID
			WHERE {$eventListStr}
				AND tournamentGenderID IN (21,109,125, 132)
			ORDER BY numParticipants DESC, weaponName ASC";
	$urgTournaments = (array)mysqlQuery($sql, ASSOC);

	$tournamentsByWeapon = [];

	foreach($urgTournaments as $t){
		@$tournamentsByWeapon[$t['weaponName']]++;
	}

	arsort($tournamentsByWeapon);


	$plotTournamentsByWeapon = [];
	$i = 0;
	foreach($tournamentsByWeapon as $weaponName => $num){
		$plotTournamentsByWeapon[$i]['name']  = $weaponName;
		$plotTournamentsByWeapon[$i]['value'] = $num;
		$i++;
	}

	return($plotTournamentsByWeapon);

}

/******************************************************************************/

function getAnnualWomensByWeapon($year, $futureView){
	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT tournamentType AS weaponName, eventName, numParticipants
			FROM eventTournaments AS eT
				INNER JOIN systemEvents USING(eventID)
				INNER JOIN systemTournaments AS sT ON eT.tournamentWeaponID = sT.tournamentTypeID
			WHERE {$eventListStr}
				AND tournamentGenderID IN (21,109,125, 132)
			ORDER BY numParticipants DESC, weaponName ASC";
	$urgTournaments = (array)mysqlQuery($sql, ASSOC);

	$regByWeapon = [];


	foreach($urgTournaments as $t){
		@$regByWeapon[$t['weaponName']] += $t['numParticipants'];
	}

	arsort($regByWeapon);

	$plotRegByWeapon = [];
	$i = 0;
	foreach($regByWeapon as $weaponName => $num){
		$plotRegByWeapon[$i]['name']  = $weaponName;
		$plotRegByWeapon[$i]['value'] = $num;
		$i++;
	}

	return($plotRegByWeapon);
}

/******************************************************************************/

function getAnnualExchangesByFighter($year, $futureView){
	$eventListStr = makeEventListStr($year, $futureView);

	$validExchanges = "'clean','afterblow','double','noExchange','scored'";

// By Fighter ------------------------------------------------------------------

	$sql = "SELECT systemRosterID, COUNT(*) AS numExchanges
			FROM eventExchanges
				INNER JOIN eventMatches AS eM USING(matchID)
				INNER JOIN eventRoster AS eR1 ON eM.fighter1ID = eR1.rosterID
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND systemRosterID IS NOT NULL
				AND exchangeType IN ($validExchanges)
			GROUP BY systemRosterID
			ORDER BY numExchanges DESC
			LIMIT 150";
	$exchByFighter1 = (array)mysqlQuery($sql, ASSOC);

	$sql = "SELECT systemRosterID, COUNT(*) AS numExchanges
			FROM eventExchanges
				INNER JOIN eventMatches AS eM USING(matchID)
				INNER JOIN eventRoster AS eR1 ON eM.fighter2ID = eR1.rosterID
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND systemRosterID IS NOT NULL
				AND exchangeType IN ($validExchanges)
			GROUP BY systemRosterID
			ORDER BY numExchanges DESC
			LIMIT 150";
	$exchByFighter2 = (array)mysqlQuery($sql, ASSOC);

	$exchByFighter = [];
	foreach($exchByFighter1 as $e){
		$exchByFighter[$e['systemRosterID']] = $e['numExchanges'];
	}

	foreach($exchByFighter2 as $e){
		@$exchByFighter[$e['systemRosterID']] += $e['numExchanges'];
	}

	arsort($exchByFighter);

	$plotExchByFighter = [];
	$i = 0;
	foreach($exchByFighter as $systemRosterID => $numExchanges){
		$plotExchByFighter[$i]['value'] = $numExchanges;
		$plotExchByFighter[$i]['name'] = getFighterNameSystem($systemRosterID);
		$i++;
		if($i >= 100){
			break;
		}
	}
	return($plotExchByFighter);
}

/******************************************************************************/

function getAnnualCloseByFighter($year, $futureView){
	$eventListStr = makeEventListStr($year, $futureView);

	$validExchanges = "'clean','afterblow','double','noExchange','scored'";

	$sql = "SELECT systemRosterID, COUNT(*) AS numMatches
			FROM eventMatches AS eM
				INNER JOIN eventRoster AS eR ON eR.rosterID = eM.winnerID
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND ABS(fighter1Score - fighter2Score) = 1
				AND fighter1Score > 3
				AND fighter2Score > 3
				AND systemRosterID IS NOT NULL
			GROUP BY systemRosterID
			ORDER BY numMatches DESC
			LIMIT 100";
	$closeMatches = (array)mysqlQuery($sql, ASSOC);

	$plotCloseMatches = [];
	foreach($closeMatches as $i => $w){
		$plotCloseMatches[$i]['value'] = $w['numMatches'];
		$plotCloseMatches[$i]['name'] = getFighterNameSystem($w['systemRosterID']);
	}

	return($plotCloseMatches);
}

/******************************************************************************/

function getAnnualEntriesByFighter($year, $futureView){
	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT systemRosterID, COUNT(*) AS numEvents
			FROM eventTournamentRoster
			INNER JOIN eventRoster USING(rosterID)
			WHERE {$eventListStr}
			AND systemRosterID IS NOT NULL
			GROUP BY systemRosterID
			ORDER BY numEvents DESC
			LIMIT 100";
	$numEvents = (array)mysqlQuery($sql, ASSOC);

	$plotNumEvents = [];
	foreach($numEvents as $i => $f){
		$plotNumEvents[$i]['value'] = $f['numEvents'];
		$plotNumEvents[$i]['name'] = getFighterNameSystem($f['systemRosterID']);
	}

	return($plotNumEvents);
}

/******************************************************************************/

function getAnnualEventsByFighter($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT systemRosterID, COUNT(*) AS numEvents
			FROM eventRoster
			WHERE {$eventListStr}
			AND systemRosterID IS NOT NULL
			GROUP BY systemRosterID
			ORDER BY numEvents DESC
			LIMIT 100";
	$numEvents = (array)mysqlQuery($sql, ASSOC);

	$plotNumEvents = [];
	foreach($numEvents as $i => $f){
		$plotNumEvents[$i]['value'] = $f['numEvents'];
		$plotNumEvents[$i]['name'] = getFighterNameSystem($f['systemRosterID']);
	}

	return($plotNumEvents);
}

/******************************************************************************/

function getAnnualShutoutsByFighter($year, $futureView){
	$eventListStr = makeEventListStr($year, $futureView);

	$validExchanges = "'clean','afterblow','double','noExchange','scored'";

	$sql = "SELECT systemRosterID, COUNT(*) AS numMatches
			FROM eventMatches AS eM
				INNER JOIN eventRoster AS eR ON eR.rosterID = eM.winnerID
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND ((fighter1Score = 0 && fighter2Score >= 4)
					OR (fighter2Score = 0 && fighter1Score >= 4))
				AND systemRosterID IS NOT NULL
				AND (	SELECT COUNT(*)
						FROM eventExchanges AS eE2
						WHERE eE2.matchID = eM.matchID
						AND scoreValue != 0) >= 3
				AND isPlaceholder = 0
			GROUP BY systemRosterID
			ORDER BY numMatches DESC
			LIMIT 100";
	$shutdoutMatches = (array)mysqlQuery($sql, ASSOC);

	$plotShutoutMatches = [];
	foreach($shutdoutMatches as $i => $w){
		$plotShutoutMatches[$i]['value'] = $w['numMatches'];
		$plotShutoutMatches[$i]['name'] = getFighterNameSystem($w['systemRosterID']);
	}

	return($plotShutoutMatches);
}

/******************************************************************************/

function getAnnualMatchesByFighter($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT systemRosterID, COUNT(*) AS numMatches
			FROM eventMatches AS eM
				INNER JOIN eventRoster AS eR ON eM.fighter1ID = eR.rosterID
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND systemRosterID IS NOT NULL
			GROUP BY systemRosterID
			ORDER BY numMatches DESC
			LIMIT 150";
	$matchByFighter1 = (array)mysqlQuery($sql, ASSOC);

	$sql = "SELECT systemRosterID, COUNT(*) AS numMatches
			FROM eventMatches AS eM
				INNER JOIN eventRoster AS eR ON eM.fighter2ID = eR.rosterID
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND systemRosterID IS NOT NULL
			GROUP BY systemRosterID
			ORDER BY numMatches DESC
			LIMIT 150";
	$matchByFighter2 = (array)mysqlQuery($sql, ASSOC);


	$matchByFighter = [];
	foreach($matchByFighter1 as $e){
		$matchByFighter[$e['systemRosterID']] = $e['numMatches'];
	}

	foreach($matchByFighter2 as $e){
		@$matchByFighter[$e['systemRosterID']] += $e['numMatches'];
	}

	arsort($matchByFighter);

	$plotMatchByFighter = [];
	$i = 0;
	foreach($matchByFighter as $systemRosterID => $numMatches){
		$plotMatchByFighter[$i]['value'] = $numMatches;
		$plotMatchByFighter[$i]['name'] = getFighterNameSystem($systemRosterID);
		$i++;
		if($i >= 100){
			break;
		}
	}

	return($plotMatchByFighter);
}

/******************************************************************************/

function getAnnualExchangesByMatch($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$validExchanges = "'clean','afterblow','double','noExchange','scored'";

	$sql = "SELECT matchID, COUNT(*) AS numExchanges
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND exchangeType IN ({$validExchanges})
				AND formatID = 2
				AND isTeams = 0
			GROUP BY matchID
			ORDER BY numExchanges DESC
			LIMIT 30";
	$longMatches = (array)mysqlQuery($sql, ASSOC);

	$plotLongMatches = [];
	foreach($longMatches as $i => $m){

		$plotLongMatches[$i]['value'] = $m['numExchanges'];
		$matchID = (int)$m['matchID'];

		$sql = "SELECT eventName, eventYear,
					(	SELECT systemRosterID
						FROM eventRoster AS eR2
						WHERE eR2.rosterID = eM.fighter1ID
					) AS sysID1,
					(	SELECT systemRosterID
						FROM eventRoster AS eR3
						WHERE eR3.rosterID = eM.fighter2ID
					) AS sysID2
				FROM eventMatches AS eM
					INNER JOIN eventGroups USING(groupID)
					INNER JOIN eventTournaments USING(tournamentID)
					INNER JOIN systemEvents USING(eventID)
				WHERE matchID = {$matchID}";
		$matchData = mysqlQuery($sql, SINGLE);

		$eventName = $matchData['eventName'];
		if($eventListStr == "eventID IS NOT NULL"){
			$eventName .= " ". $matchData['eventYear'];
		}

		$txt = getFighterNameSystem($matchData['sysID1']);
		$txt .= " <span style='font-size:0.8em;'>vs</span> ";
		$txt .= getFighterNameSystem($matchData['sysID2'])."";
		$txt .= "<i style='font-size:0.8em;'><BR>(".$eventName.")</i></span>";

		$plotLongMatches[$i]['name'] = $txt;
	}

	return ($plotLongMatches);
}

/******************************************************************************/

function getAnnualComebacksByMatch($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);

	$minScore = 6;

	$sql = "SELECT matchID, fighter1ID, fighter2ID, winnerID,
				fighter1Score, fighter2Score, eventName, tournamentID, eventYear
			FROM eventMatches AS eM
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND ((winnerID = fighter1ID AND fighter2Score > {$minScore} AND fighter1Score > fighter2Score)
					OR (winnerID = fighter2ID AND fighter1Score > {$minScore} AND fighter2Score > fighter1Score))
				AND winnerID IS NOT NULL
				AND (SELECT COUNT(*) AS numExchanges
					FROM eventExchanges AS eE2
					WHERE eE2.matchID = eM.matchID) >= 9"; //Take off one for the "winner" exchange
    $matches = mysqlQuery($sql, ASSOC);

    $matchIDstr = "";
    $matchList = [];
    foreach($matches as $index => $m){
    	$matchIDstr .= (int)$m['matchID'].",";

    	$matchList[$m['matchID']] = $m;
    	if($m['winnerID'] == $m['fighter1ID']){
    		$matchList[$m['matchID']]['loserID'] = $m['fighter2ID'];
    	} else {
    		$matchList[$m['matchID']]['loserID'] = $m['fighter1ID'];
    	}

    }

	$matchIDstr = rtrim($matchIDstr, ',');


	$sql = "SELECT matchID, fighter1ID, fighter2ID, winnerID, exchangeType, scoringID, scoreValue, scoreDeduction
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
			WHERE matchID IN ({$matchIDstr})
				AND (scoreValue != 0 AND scoreValue IS NOT NULL)";
    $exchangeList = mysqlQuery($sql, ASSOC);

    $matchID = 0;
    $i = 0;

    $matchComebacks = [];
    foreach($exchangeList as $e){


    	if($matchID != $e['matchID']){


			if($matchID != 0 && $maxDeficit > 0){
    			$matchComebacks[$matchID] = $maxDeficit;
    		}


    		$comebackScore = 0;
    		$loseScore = 0;
    		$maxDeficit = 0;
    		$matchID = $e['matchID'];

    	}

    	$points = $e['scoreValue'] - $e['scoreDeduction'];
    	if($points != 0){
    		if($e['scoringID'] == $matchList[$matchID]['winnerID']){
    			$comebackScore += $points;
    		} else {
    			$loseScore += $points;
    		}

    		$deficit = $loseScore - $comebackScore;

    		if($deficit > $maxDeficit){
    			$maxDeficit = $deficit;
    		}


    	}

    }

    arsort($matchComebacks);

    $i = 0;
    $plotMatchComebacks = [];
    foreach($matchComebacks as $matchID => $comeback){

    	$match = $matchList[$matchID];

    	$eventName = $match['eventName'];
		if($eventListStr == "eventID IS NOT NULL"){
			$eventName .= " ". $match['eventYear'];
		}
    	$tName = getTournamentName($match['tournamentID']);

    	if(isEntriesByTeam($match['tournamentID']) == false){
    		$name1 = getFighterName($match['winnerID']);
    		$name2 = getFighterName($match['loserID']);
    	} else {
    		$name1 = getTeamName($match['winnerID']);
    		$name2 = getTeamName($match['loserID']);
    	}

    	$txt = "";
		$txt .= "{$name1} <span style='font-size:0.8em;'>over</span> {$name2}";
		$txt .= "<BR><i style='font-size:0.8em;'>(".$tName."; ".$eventName.")</i></span>";

		$plotMatchComebacks[$i]['name'] = $txt;
		$plotMatchComebacks[$i]['value'] = $comeback;
		$i++;
		if($i >= 30){
			break;
		}
    }

    return ($plotMatchComebacks);

}

/******************************************************************************/

function getAnnualRematchesByFighter($year, $futureView){

	$eventListStr = makeEventListStr($year, $futureView);
	$eventListStr = "eT.".$eventListStr;

	$sql = "SELECT LEAST(eR1.systemRosterID, eR2.systemRosterID) AS systemRosterID1,
				GREATEST(eR1.systemRosterID, eR2.systemRosterID) AS systemRosterID2,
				COUNT(*) AS num
			FROM eventMatches AS eM
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments AS eT USING(tournamentID)
			INNER JOIN eventRoster AS eR1 ON eM.fighter1ID = eR1.rosterID
			INNER JOIN eventRoster AS eR2 ON eM.fighter2ID = eR2.rosterID
			WHERE {$eventListStr}
				AND fighter1ID != fighter2ID
				AND placeholderMatchID IS NULL
			GROUP BY systemRosterID1, systemRosterID2
			ORDER BY num DESC";
	$rematches = (array)mysqlQuery($sql, ASSOC);

	$plotRematches = [];
	$i = 0;
	foreach($rematches as $pair){

		if($pair['systemRosterID1'] == 0){
			continue;
		}

		$name1 = getFighterNameSystem($pair['systemRosterID1']);
		$name2 = getFighterNameSystem($pair['systemRosterID2']);


		$txt = "{$name1} <span style='font-size:0.8em;'>vs</span> {$name2}";


		$plotRematches[$i]['name'] = $txt;
		$plotRematches[$i]['value'] = $pair['num'];
		$i++;
		if($i >= 100){
			break;
		}
    }


	return ($plotRematches);
}

/******************************************************************************/

function getAnnualExchangesByJudge($year, $futureView){
	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT systemRosterID, COUNT(*) AS numExchanges
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN logisticsStaffMatches USING(matchID)
			INNER JOIN eventRoster USING(rosterID)
			WHERE {$eventListStr}
			AND systemRosterID IS NOT NULL
			AND logisticsRoleID IN (1,2,9,11,12)
			GROUP BY systemRosterID
			ORDER BY numExchanges DESC
			LIMIT 100";
	$numExchanges = (array)mysqlQuery($sql, ASSOC);

	$plotNumExchanges = [];
	foreach($numExchanges as $i => $f){
		$plotNumExchanges[$i]['value'] = $f['numExchanges'];
		$plotNumExchanges[$i]['name'] = getFighterNameSystem($f['systemRosterID']);
	}

	return($plotNumExchanges);
}

/******************************************************************************/

function getAnnualExchangesByDirector($year, $futureView){
	$eventListStr = makeEventListStr($year, $futureView);

		$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT systemRosterID, COUNT(*) AS numExchanges
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN logisticsStaffMatches USING(matchID)
			INNER JOIN eventRoster USING(rosterID)
			WHERE {$eventListStr}
			AND systemRosterID IS NOT NULL
			AND logisticsRoleID IN (1,9,11,12)
			GROUP BY systemRosterID
			ORDER BY numExchanges DESC
			LIMIT 100";
	$numExchanges = (array)mysqlQuery($sql, ASSOC);

	$plotNumExchanges = [];
	foreach($numExchanges as $i => $f){
		$plotNumExchanges[$i]['value'] = $f['numExchanges'];
		$plotNumExchanges[$i]['name'] = getFighterNameSystem($f['systemRosterID']);
	}

	return($plotNumExchanges);
}

/******************************************************************************/

function getAnnualMatchesByTable($year, $futureView){
	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT systemRosterID, COUNT(*) AS numMatches
			FROM logisticsStaffMatches
			INNER JOIN eventRoster USING(rosterID)
			WHERE {$eventListStr}
			AND systemRosterID IS NOT NULL
			AND logisticsRoleID IN (3)
			GROUP BY systemRosterID
			ORDER BY numMatches DESC
			LIMIT 100";
	$numMatches = (array)mysqlQuery($sql, ASSOC);

	$plotNumMatches = [];
	foreach($numMatches as $i => $f){
		$plotNumMatches[$i]['value'] = $f['numMatches'];
		$plotNumMatches[$i]['name'] = getFighterNameSystem($f['systemRosterID']);
	}

	return($plotNumMatches);
}

/******************************************************************************/

function getAnnualMatchesByStaff($year, $futureView){
	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT systemRosterID, COUNT(*) AS numMatches
			FROM logisticsStaffMatches
			INNER JOIN eventRoster USING(rosterID)
			WHERE {$eventListStr}
			AND systemRosterID IS NOT NULL
			GROUP BY systemRosterID
			ORDER BY numMatches DESC
			LIMIT 100";
	$numMatches = (array)mysqlQuery($sql, ASSOC);

	$plotNumMatches = [];
	foreach($numMatches as $i => $f){
		$plotNumMatches[$i]['value'] = $f['numMatches'];
		$plotNumMatches[$i]['name'] = getFighterNameSystem($f['systemRosterID']);
	}

	return($plotNumMatches);
}

/******************************************************************************/

function getAnnualExchangesByStaffSchool($year, $futureView){
	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT schoolID, COUNT(*) AS numExchanges
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN logisticsStaffMatches USING(matchID)
			INNER JOIN eventRoster USING(rosterID)
			WHERE {$eventListStr}
			AND systemRosterID IS NOT NULL
			GROUP BY schoolID
			ORDER BY numExchanges DESC
			LIMIT 100";
	$numExchanges = (array)mysqlQuery($sql, ASSOC);

	$plotNumExchanges = [];
	foreach($numExchanges as $i => $f){
		$plotNumExchanges[$i]['value'] = $f['numExchanges'];
		$plotNumExchanges[$i]['name'] = getSchoolName($f['schoolID']);
	}

	return($plotNumExchanges);
}

/******************************************************************************/

function getAnnualExchangesByJudgeSchool($year, $futureView){
	$eventListStr = makeEventListStr($year, $futureView);

	$sql = "SELECT schoolID, COUNT(*) AS numExchanges
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN logisticsStaffMatches USING(matchID)
			INNER JOIN eventRoster USING(rosterID)
			WHERE {$eventListStr}
			AND systemRosterID IS NOT NULL
			AND logisticsRoleID IN (1,2,9,11,12)
			GROUP BY schoolID
			ORDER BY numExchanges DESC
			LIMIT 100";
	$numExchanges = (array)mysqlQuery($sql, ASSOC);

	$plotNumExchanges = [];
	foreach($numExchanges as $i => $f){
		$plotNumExchanges[$i]['value'] = $f['numExchanges'];
		$plotNumExchanges[$i]['name'] = getSchoolName($f['schoolID']);
	}

	return($plotNumExchanges);
}

/******************************************************************************/

function calculateWinPercent($rating1, $rating2){

	$dev1 = ESTIMATE_DEVIATION_M * $rating1 + ESTIMATE_DEVIATION_B;
	$dev2 = ESTIMATE_DEVIATION_M * $rating2 + ESTIMATE_DEVIATION_B;

	$score1 = $rating1 + 2 * $dev1;
	$score2 = $rating2 + 2 * $dev2;

	$scoreDelta = $score1 - $score2;

	$deviationRMS = sqrt(pow($dev1/GLICKO_CONSTANT,2) + pow($dev2/GLICKO_CONSTANT,2));
	$deviationFactor = 1 / sqrt(1 + (3 * pow($deviationRMS,2)) / pow(pi(),2));
	$winProbability = 1 / (1 + exp(-$deviationFactor * $scoreDelta/GLICKO_CONSTANT));

	return ($winProbability);
}


/******************************************************************************/

function calculateEventRating($eventData){

	$ratingsList = $eventData['ratings'];
	$numFighters = sizeof($ratingsList);
	$numStages = ceil(log($numFighters,2));
	$matchTableSize = 2^$numStages;

	$fightersInStage = [];
	$fightersInStage[2][0] = 1;
	$fightersInStage[2][1] = 4;

	for($stageNum = 3;$stageNum <= $numStages; $stageNum++){

		$fightersInStage[$stageNum][0] = pow(2,$stageNum-1)+1;
		$fightersInStage[$stageNum][1] = pow(2,$stageNum);
	}


	for($i = 2100; $i >= 800; $i -= 100){
		$eventData['histogram'][$i] = 0;
	}

	foreach($ratingsList as $rating){
		$bin = 100*ceil($rating/100);
		$eventData['histogram'][$bin]++;
	}

	foreach($eventData['histogram'] as $bin => $count){
		if($count == 0){
			$eventData['histogram'][$bin] = "";
		}
	}


	$highestRatingToTest = (int)(max($ratingsList) * 1.3);


	if($numFighters >= 8){
		$lowestRatingToTest = $ratingsList[7];
	} else {
		$lowestRatingToTest = $ratingsList[$numFighters - 1];
	}

	for($testRating = $lowestRatingToTest; $testRating <= $highestRatingToTest; $testRating += RATING_STEP){

		$testWinProb = 1;

		for($stageNum = 2; $stageNum <= $numStages; $stageNum++){

			$integral = 0;
			$samples = 0;

			for($i = $fightersInStage[$stageNum][0]; $i <= $fightersInStage[$stageNum][1]; $i++){

				if($i > $numFighters){
					$matchWinPercent = 1;
				} else {
					$matchWinPercent = calculateWinPercent($testRating, $ratingsList[$i-1]);
				}


				$integral += $matchWinPercent;
				$samples++;

				$dispProb = round($matchWinPercent * 100,0);
				////echo "<BR>Fighter: {$i} | Win %: {$dispProb}";
			}

			$stageProbability = $integral / $samples;
			$testWinProb *= $stageProbability;
		}

		if($testWinProb >= PROBABILITY_THRESHOLD){
			break;
		}
	}

	$eventData['eventRating'] = $testRating;


	return ($eventData);


}

/******************************************************************************/

/******************************************************************************/
// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
