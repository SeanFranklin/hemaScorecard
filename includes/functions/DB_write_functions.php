<?php
/*******************************************************************************
	Database Write Functions
	
	Functions for writing to the HEMA Scorecard database
	
*******************************************************************************/


/******************************************************************************/

function deleteFromEvent(){
	
	$eventID = $_SESSION['eventID'];
	if($eventID == null){return;}
	if(USER_TYPE < USER_ADMIN){return;}
	$tournamentIDs = getEventTournaments($eventID);
	
	foreach((array)$_POST['deleteFromEvent'] as $rosterID => $data){
		
		foreach($tournamentIDs as $tournamentID){
			$sql = "SELECT rosterID
					FROM eventTournamentRoster
					WHERE tournamentID = {$tournamentID}
					AND rosterID = {$rosterID}";
			$res = mysqlQuery($sql, SINGLE, 'rosterID');
			
			if(isFinalized($tournamentID) && $res != null){
				$name = getFighterName($rosterID);
				$tName = getTournamentName($tournamentID);
				
				$_SESSION['alertMessages']['userErrors'][] = "<span class='red-text'>Event Deletion Failed</span>
				 - Tournament has already been finalized<BR>
				 <strong>{$name}</strong> is a part of <strong>{$tName}</strong> and can not be removed<BR>
				 The tournament must be <a href='infoSummary.php'>re-opened</a> to make changes";
				continue 2;
			}
			
		}
		
		
		$sql = "DELETE FROM eventRoster
				WHERE rosterID = {$rosterID}
				AND eventID = {$eventID}";
		mysqlQuery($sql, SEND);
	}
	
	$_SESSION['checkEvent']['all'] = true;
	
	updateTournamentFighterCounts();
	
}

/******************************************************************************/

function activateLivestream($eventID = null){
	
	if($eventID == null){ $eventID = $_SESSION['eventID']; }
	if($eventID == null){return;}
	
	$status = (int)$_POST['livestreamStatus'];
	
	$sql = "UPDATE eventLivestreams
			SET isLive = {$status}";
	mysqlQuery($sql, SEND);
	
}

/******************************************************************************/

function addAttacksToTournament($tournamentID = null){
	
	if(USER_TYPE < USER_ADMIN){ return; }
	if($tournamentID == null){ $tournamentID = $_SESSION['tournamentID']; }
	if($tournamentID == null){return;}
	
	$sql = "DELETE FROM eventAttacks
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);
	
	$i = 0;
	foreach($_POST['newAttack'] as $a){
		$i++;
		if($a['attackPoints'] == ''){
			continue;
		}
		if($a['attackType'] == ''){
			$aType = 'NULL';
		} else {
			$aType = $a['attackType'];
		}
		
		if($a['attackTarget'] == ''){
			$aTarget = 'NULL';
		} else {
			$aTarget = $a['attackTarget'];
		}
		
		if($a['attackPrefix'] == ''){
			$aPrefix = 'NULL';
		} else {
			$aPrefix = $a['attackPrefix'];
		}
		
		if(((int)$a['attackNumber']) <= 0){
			$aNum = $i;
		} else {
			$aNum = $a['attackNumber'];
		}

		
		$sql = "INSERT INTO eventAttacks
				(tournamentID, attackTarget, attackType, attackPoints, attackNumber, attackPrefix)
				VALUES
				({$tournamentID}, {$aTarget}, {$aType}, {$a['attackPoints']}, {$aNum}, {$aPrefix})";
		mysqlQuery($sql, SEND);
	}
	
	
}

/******************************************************************************/

function addEventParticipantsByID(){
	$eventID = $_SESSION['eventID'];
	if($eventID == null){return;}
	if(USER_TYPE < USER_ADMIN){return;}
		
	foreach(@(array)$_POST['newParticipants']['byID'] as $fighter){

		$systemRosterID = $fighter['systemRosterID'];
		$schoolID = $fighter['schoolID'];
		
		if($systemRosterID == null || $schoolID == null){ continue; }
		
	// Check if fighter is already in the event
		$sql = "SELECT rosterID
				FROM eventRoster
				WHERE eventID = {$eventID}
				AND systemRosterID = {$systemRosterID}";
		$result = mysqlQuery($sql, ASSOC);
		
		if($result != null){
			$_SESSION['rosterEntryConflicts']['alreadyEntered'][] = $systemRosterID;
			continue;
		}
	
	// Adds fighter to the event
		$sql = "INSERT INTO eventRoster
				(systemRosterID, eventID, schoolID)
				VALUES
				({$systemRosterID}, {$eventID}, {$schoolID})";
		mysqlQuery($sql, SEND);
		$rosterID = mysqli_insert_id($GLOBALS["___mysqli_ston"]);
		
	// Add fighters to tournaments
		foreach((array)$fighter['tournamentIDs'] as $tournamentID){
			if(isFinalized($tournamentID)){
				$name = getFighterName($rosterID);
				$tName = getTournamentName($tournamentID);
				
				$_SESSION['alertMessages']['userErrors'][] = "<span class='red-text'>Tournament Addition Failed</span> - Tournament has already been finalized<BR>
				 <strong>{$name}</strong> can not be added to <strong>{$tName}</strong>";
				continue;
			}
			
			
			$sql = "INSERT INTO eventTournamentRoster
				(rosterID, tournamentID)
				VALUES
				({$rosterID}, {$tournamentID})";
			mysqlQuery($sql, SEND);
		}
		
	// Check if the schoolID in the systemRoster should be updated
		if(isset($fighter['changeSchoolID']) && $fighter['changeSchoolID'] == $schoolID){
			$sql = "UPDATE systemRoster
					SET schoolID = {$schoolID}
					WHERE systemRosterID = {$systemRosterID}";
			mysqlQuery($sql, SEND);
		}
		
	}
	
	updateTournamentFighterCounts();
	
}

/******************************************************************************/

function addEventParticipantsByName(){
	
	$eventID = $_SESSION['eventID'];
	if($eventID == null){return;}
	if(USER_TYPE < USER_ADMIN){return;}
	
	foreach(@(array)$_POST['newParticipants']['new'] as $fighter){
		
		$firstName = trim($fighter['firstName']);
		$lastName = trim($fighter['lastName']);
		$schoolID = $fighter['schoolID'];
		
		if($firstName == '' && $lastName == ''){ continue; }
		$firstNameEscape = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $firstName);
		$lastNameEscape = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $lastName);
		
	// Check if fighter already exists
		// If full name is already in systemRoster
		
		$sql = "SELECT schoolID, systemRosterID
				FROM systemRoster
				WHERE firstName = '{$firstNameEscape}'
				AND lastName = '{$lastNameEscape}'";	
		$result = mysqlQuery($sql, SINGLE);
		
		$systemSchoolID = $result['schoolID'];
		$systemRosterID = $result['systemRosterID'];

		if($systemSchoolID == $schoolID && $schoolID != null){
			// If a fighter with the same name and same school is already
			// in the system it just enters that fighter
			$newAdds = [];
			$newAdds['systemRosterID'] = $systemRosterID;
			$newAdds['schoolID'] = $schoolID;
			$newAdds['tournamentIDs'] = $fighter['tournamentIDs'];
			$_POST['newParticipants']['byID'][] = $newAdds;
			continue;
			
		} else if( $systemSchoolID != null){
			$error = [];
			
			$sql = "SELECT rosterID
					FROM eventRoster
					WHERE eventID = {$eventID}
					AND systemRosterID = {$result['systemRosterID']}";
			$result = mysqlQuery($sql, ASSOC);
			
			if($result != null){
				
				$_SESSION['rosterEntryConflicts']['alreadyEntered'][] = $systemRosterID;
				continue;
			}
			
			$error['enteredSchoolID'] = $schoolID;
			$error['systemSchoolID'] = $systemSchoolID;
			$error['systemRosterID'] = $systemRosterID;
			$error['tournamentIDs'] = $fighter['tournamentIDs'];
			$_SESSION['rosterEntryConflicts']['alreadyExists'][] = $error;
			continue;
		}
	
	// Adds fighter to the system
		$sql = "INSERT INTO systemRoster 
				(firstName, lastName, schoolID)
				VALUES
				(?,?,?)
				";

		$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
		// "s" means the database expects a string
		$bind = mysqli_stmt_bind_param($stmt, "ssi", $firstName, $lastName, $schoolID);
		$exec = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		
		$systemRosterID = mysqli_insert_id($GLOBALS["___mysqli_ston"]);
		
		$sql = "INSERT INTO eventRoster 
				(systemRosterID, schoolID, eventID)
				VALUES
				({$systemRosterID}, {$schoolID}, {$eventID})
				";
	
		mysqlQuery($sql,SEND);
		$rosterID = mysqli_insert_id($GLOBALS["___mysqli_ston"]); 
		
	// Add fighters to tournaments
		foreach(@(array)$fighter['tournamentIDs'] as $tournamentID){
			$sql = "INSERT INTO eventTournamentRoster
				(rosterID, tournamentID)
				VALUES
				({$rosterID}, {$tournamentID})";
			mysqlQuery($sql, SEND);
		}
	}
	
	updateTournamentFighterCounts();
	
}

/******************************************************************************/

function addMultipleFightersToRound(){
	
	if(USER_TYPE < USER_STAFF){return;}
	
	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){return;}

	
	$groupID = $_POST['groupID'];
	$numToAdd = $_POST['numToAdd'][$groupID];
	$groupInfo = getGroupInfo($groupID);
	$groupSet = $groupInfo[0]['groupSet'];
	$groupNumber = $groupInfo[0]['groupNumber'];
	unset($_POST['groupAdditions']);

	$eligibleRoster = getListForNextRound($tournamentID, $groupSet, $groupNumber);

	// If there are no fighters who have already completed the last round it adds
	// every fighter who was in the previous round
	if($eligibleRoster == null && $groupNumber > 1){
		$rounds = getRounds($tournamentID, $groupSet);
		foreach($rounds as $round){
			if($round['groupNumber'] == $groupNumber - 1){
				$previousGroupID = $round['groupID'];
				break;
			}
		}

		$roundRoster = getPoolRosters($tournamentID, $groupSet);
		$tmp = $roundRoster[$previousGroupID];
		foreach($tmp as $index => $data){
			$eligibleRoster[$index-1] = $data;
		}
	}

	$sql = "SELECT rosterID, poolPosition
			FROM eventGroupRoster
			WHERE groupID = {$groupID}";
	
	$currentRoster = mysqlQuery($sql, KEY_SINGLES, 'rosterID', 'poolPosition');

	if($numToAdd == 0){
		$numToAdd = count($eligibleRoster);
	}

	$position = 0;
	for($i = 0; $i < $numToAdd; $i++){
		
		$rosterID = $eligibleRoster[$i]['rosterID'];
		if(isset($currentRoster[$rosterID])){
			continue;
		}
		$position++;
		
		$_POST['groupAdditions'][$groupID][$position] = $rosterID;
		
	}

	addFightersToGroup();
}

/******************************************************************************/

function addFightersToGroup(){
	
	if(USER_TYPE < USER_STAFF){return;}
	
	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){return;}

	$skippedFighters = 0;
	if(!isset($_POST['groupAdditions'])){
		return;
	}

	foreach((array)$_POST['groupAdditions'] as $groupID => $groupAdditions){
		foreach($groupAdditions as $poolPosition => $rosterID){
			if(isset($fightersInList[$rosterID])){
				$skippedFighters++;
				continue;
			}
			if($rosterID == null){continue;}			
			
			$_SESSION['checkEvent'][$tournamentID][$groupID]['all'] = true;
			
			$sql = "SELECT tableID 
					FROM eventTournamentRoster
					WHERE tournamentID = {$tournamentID}
					AND rosterID = {$rosterID}";
				
			$tournamentTableID = mysqlQuery($sql, SINGLE, 'tableID');
			
			$insertPoolPosition = $poolPosition - $skippedFighters;
			$sql = "INSERT INTO eventGroupRoster
					(groupID, rosterID, poolPosition, tournamentTableID)
					VALUES
					({$groupID}, {$rosterID}, {$insertPoolPosition}, {$tournamentTableID})";
			mysqlQuery($sql, SEND);
			
			$fightersInList[$rosterID] = true;
			$lastGroupAdded = $groupID;
		}
		$_SESSION['checkEvent'][$tournamentID][$groupID] = true;
	}
	if(!isset($lastGroupAdded)){ return; }

	$sql = "SELECT groupType, groupSet
			FROM eventGroups
			WHERE groupID = {$lastGroupAdded}";
	$data = mysqlQuery($sql, SINGLE);
	
	if($data['groupType'] == 'round'){
		$_SESSION['groupSet'] = $data['groupSet'];
	}

	unset($_POST['groupAdditions']);
	
}

/******************************************************************************/

function addMatchWinner(){
	if(USER_TYPE < USER_STAFF){return;}
	
	$matchID = $_POST['matchID'];
	$matchInfo = getMatchInfo($matchID);
	$tournamentID = $matchInfo['tournamentID'];

	$winnerID = $_POST['matchWinnerID'];
	
	switch($winnerID){
		case 'doubleOut':
			$winnerID = 'null';
			insertLastExchange($matchInfo, 'doubleOut', 'null', 'null', 'null');
			break;
		case 'tie':
			$winnerID = 'null';
			insertLastExchange($matchInfo, 'tie', 'null', 'null', 'null');
			break;
		default:
			insertLastExchange($matchInfo, 'winner', $winnerID, 'null', 'null');
	}
	
	$sql = "UPDATE eventMatches
			SET winnerID = {$winnerID}, matchComplete = 1
			WHERE matchID = {$matchID}";
	mysqlQuery($sql, SEND);
	
	if(isLastMatch($tournamentID)){
		$_SESSION['askForFinalization'] = true;
	}

	$_SESSION['updatePoolStandings'][$tournamentID] = getGroupSetOfMatch($matchID);
	
}

/******************************************************************************/
function addNewEvent(){
	
	if(USER_TYPE < USER_SUPER_ADMIN){return;}
	
	$eventYear = substr($_POST['eventStartDate'],0,4);
	$num = mt_rand(100,999);
	$password = "temp{$num}";
	$passwordHash = password_hash($password, PASSWORD_DEFAULT);
	
	$sql = "INSERT INTO systemEvents
			(eventName, eventAbreviation, eventYear, eventStartDate,
			eventEndDate, eventCountry, eventProvince, eventCity,
			eventStatus, USER_STAFF, USER_ADMIN)
			VALUES
			(?,?,?,?,?,?,?,?,?,?,?)";
	
	$eventStartDate = $_POST['eventStartDate'];
	if($eventStartDate == null){$eventStartDate = date('Y-m-d H:i:s');}
	$eventEndDate = $_POST['eventEndDate'];
	if($eventEndDate == null){$eventEndDate = date('Y-m-d H:i:s');}
		
	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	$bind = mysqli_stmt_bind_param($stmt, "sssssssssss", 
			$_POST['eventName'], $_POST['eventAbreviation'],
			$eventYear, $eventStartDate,
			$eventEndDate, $_POST['eventCountry'], 
			$_POST['eventProvince'], $_POST['eventCity'],
			$_POST['eventStatus'],$passwordHash,$passwordHash);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	
	$eventID = mysqli_insert_id($GLOBALS["___mysqli_ston"]);
	
	$sql = "INSERT INTO eventDefaults
			(eventID)
			VALUES
			($eventID)";
	mysqlQuery($sql, SEND);

	$name = getEventName($eventID);
	$_SESSION['alertMessages']['userAlerts'][] = 
	"<div class='callout text-left'><p><i>{$name}</i> has been created in HEMA Scorecard and is good to go!</p>
	<p>The passwords for both staff and organizer have been set to '<strong>{$password}</strong>'.</p>

	<ul>
	<u>A few notes</u>:
	<li>When testing things out please only use real fighters. The roster is shared with all events, so I try to avoid a whole lot of people named 'Test Fighter' cluttering it up. Same thing goes for entering new schools, please make sure that the information is accurate.</li>
	<li>When it comes to tournament algorithms/weapons you need to contact me for anything that isn't already in there. If you know what you want I can create a tournament ranking for you that calculates the score & tie-breakers to your specifications, and displays whatever fighter stats you care about.</li>
	</ul>
	
	<p>Let me know if you have any questions! That aren't in the help. ;)<ul>
	 Sean Franklin<BR>
	 <i>HEMA Scorecard</i></ul></p></div>";
		
}

/******************************************************************************/

function addNewSchool(){
	if(USER_TYPE < USER_ADMIN){return;}
	
	
	$schoolFullName = $_POST['schoolFullName'];
	$schoolShortName = $_POST['schoolShortName'];
	$schoolAbreviation = $_POST['schoolAbreviation'];
	$schoolBranch = $_POST['schoolBranch'];
	$schoolCountry = $_POST['schoolCountry'];
	$schoolProvince = $_POST['schoolProvince'];
	$schoolCity = $_POST['schoolCity'];

	if($schoolShortName == null){$schoolShortName = $schoolFullName;}
	if($schoolFullName == null){$schoolFullName = $schoolShortName;}
	if($schoolFullName == null || $schoolShortName == null){return;}
	
	if($schoolAbreviation == null){
		$nameArray = str_split($schoolFullName);
		foreach($nameArray as $char){
			if(ctype_upper($char)){
				$schoolAbreviation .= $char;
			}
		}
	}
	
	$sql = "INSERT INTO systemSchools
			(schoolFullName, schoolShortName, schoolAbreviation, schoolBranch,
			schoolCountry, schoolProvince, schoolCity)
			VALUES
			(?,?,?,?,?,?,?)";

	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	$bind = mysqli_stmt_bind_param($stmt, "sssssss", $schoolFullName, $schoolShortName, $schoolAbreviation,
	 $schoolBranch, $schoolCountry, $schoolProvince, $schoolCity);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
}

/******************************************************************************/

function addToTournament(){

	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){return;}
	if(USER_TYPE < USER_STAFF){return;}

// Add New Participants
	foreach((array)$_POST['addToTournament'] as $rosterID){//insert data into table
		if($rosterID == null){continue;}
		
		//check if they are already entered
		$sql = "SELECT rosterID FROM eventTournamentRoster
				WHERE tournamentID = {$tournamentID}
				AND rosterID = {$rosterID}"; 
		$result = mysqlQuery($sql, SINGLE);
		if(isset($result)){continue;}
		
		$sql = "INSERT INTO eventTournamentRoster
				(tournamentID, rosterID)
				VALUES
				({$tournamentID}, {$rosterID})";
		mysqlQuery($sql, SEND);
	}

	updateTournamentFighterCounts($tournamentID);
}

/******************************************************************************/

function addTournamentType(){
	
	if(USER_TYPE < USER_SUPER_ADMIN){return;}
	
	$meta = $_POST['tournamentTypeMeta'];
	$type = $_POST['tournamentType'];
	
	if($meta == null || $type == null){
		echo "No Values Inserted";
		return;
	}
	
	$sql = "SELECT * FROM systemTournaments
			WHERE tournamentTypeMeta = '{$meta}'
			AND tournamentType = '{$type}'";
	$result = mysqlQuery($sql, SINGLE);
	
	if($result != null){
		echo "Already Exists";
		return;
	}
	
	$sql = "INSERT INTO systemTournaments
			(tournamentTypeMeta, tournamentType)
			VALUES
			(?,?)";
	
	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	// "s" means the database expects a string
	$bind = mysqli_stmt_bind_param($stmt, "ss", $meta, $type);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);		
			
}

/******************************************************************************/

function addNewDuplicateException(){
// Adds a new name to the table of fighters who are not the same person.
// These are names tha the sort algorithm identifies incorrectly.

	if(USER_TYPE < USER_SUPER_ADMIN){ return;}
	$rosterID1 = $_POST['rosterIDs'][0];
	$rosterID2 = $_POST['rosterIDs'][1];



	$sql = "INSERT INTO systemRosterNotDuplicate
			(rosterID1, rosterID2)
			VALUES
			({$rosterID1}, {$rosterID2})";


	mysqlQuery($sql, SEND);		

}

/******************************************************************************/

function checkRoundRoster($tournamentID, $groupID = null){
// Checks that all fighters are numbered sequentialy in the round roster
// Can only check one round if the groupID is provided	
	
	// Groups to check
	if($groupID == null){
		$sql = "SELECT groupID
				FROM eventGroups
				WHERE tournamentID = {$tournamentID}
				AND groupType = 'round'";
		$groups = mysqlQuery($sql, ASSOC);
	} else {
		$groups[0]['groupID'] = $groupID;
	}
	
	foreach($groups as $group){
		$groupID = $group['groupID'];
		
		//Get the round roster
		$sql = "SELECT rosterID, poolPosition, tableID
				FROM eventGroupRoster
				WHERE groupID = {$groupID}
				ORDER BY poolPosition ASC";
				
		$roster = mysqlQuery($sql, ASSOC);
		
		// Step through each and check the order
		$i = 0;
		foreach($roster as $fighter){
			$i++;
			
			if($fighter['poolPosition'] != $i){
				$sql = "UPDATE eventGroupRoster
						SET poolPosition = {$i}
						WHERE tableID = {$fighter['tableID']}";
				mysqlQuery($sql, SEND);
			}
		}
	}
	
}

/******************************************************************************/

function clearExchanges($matchID, $code){
// clears exchanges from a match	

	if(USER_TYPE < USER_STAFF){return;}
	
	if($matchID == null){$matchID = $_SESSION['matchID'];}
	if($matchID == null){return;}

// clear all exchanges
	if(strcasecmp($code,'all') == 0){

		$sql = "DELETE FROM eventExchanges 
				WHERE matchID = {$matchID}";
		mysqlQuery($sql, SEND);
		
		$sql = "UPDATE eventMatches
				SET fighter1Score = null,
				fighter2Score = null
				WHERE matchID = {$matchID}";
		mysqlQuery($sql, SEND);
		
		$sql = "UPDATE eventMatches
				SET matchTime = 0
				WHERE matchID = {$matchID}";
		mysqlQuery($sql, SEND);
		
// clear only the last exchange		
	}else if(strcasecmp($code,'last') == 0){
		
		$sql = "SELECT *
				FROM eventExchanges
				WHERE matchID = {$matchID}
				AND exchangeType = 'winner'";
		$winner = mysqlQuery($sql, SINGLE, 'matchID');
		
		$sql = "SELECT MAX(exchangeID) AS exchangeID 
				FROM eventExchanges
				WHERE matchID = {$matchID}";
		$result = mysqlQuery($sql, SINGLE);
		
		$exchangeID = $result['exchangeID'];
		
		if($exchangeID != null){
			$sql = "DELETE FROM eventExchanges
					WHERE exchangeID = {$exchangeID}";
			mysqlQuery($sql, SEND);
		}
		
		$sql = "DELETE FROM eventExchanges
			WHERE matchID = {$matchID}
			AND exchangeType = 'winner'";
		mysqlQuery($sql, SEND);

	}
	
	$sql = "UPDATE eventMatches
			SET winnerID = null, matchComplete = 0
			WHERE matchID = {$matchID}";
	mysqlQuery($sql, SEND);
	
}

/******************************************************************************/

function combineSystemRosterIDs($baseID, $rosterIDs){


	if($baseID == null){
		$_SESSION['alertMessages']['userErrors'][] = "No Fighter Selected<BR>No Changes Made";
		return;
	}

	foreach($rosterIDs as $systemRosterID){
		if($systemRosterID == $baseID){ continue; }

		$sql = "UPDATE eventRoster
				SET systemRosterID = {$baseID}
				WHERE systemRosterID = {$systemRosterID}";
		mysqlQuery($sql, SEND);
		$sql = "DELETE FROM systemRoster
				WHERE systemRosterID = {$systemRosterID}";
		mysqlQuery($sql, SEND);		

	}
	$_SESSION['alertMessages']['userAlerts'][] = "Fighters combined successfully";

}

/******************************************************************************/

function concludeMatchByExchanges($matchID, $exchanges, $maxExchanges){
	
	$matchInfo = getMatchInfo($matchID);
	if($matchInfo['matchComplete'] == 1 || $matchInfo['ignoreMatch'] == 1){ return;}
	$numExchanges = 0;
	$doubles = 0;

	foreach($exchanges as $exchange){
		switch($exchange['exchangeType']){
			case 'clean':
			case 'afterblow':
				$numExchanges++;
				break;
			case 'double':
				$doubles++;
				$numExchanges++;
				break;
			default:
		}
	
	}
	
	if($numExchanges < $maxExchanges){ return; }
	
	$_POST['matchID'] = $matchID;
	
	if($doubles >= $matchInfo['maxDoubles']){
		$_POST['matchID'] = $_POST['matchID'];
		$_POST['matchWinnerID'] = 'doubleOut';
	} elseif($matchInfo['fighter1score'] == $matchInfo['fighter2score']){
		if(!isTies()){
			$_SESSION['alertMessages']['userErrors'][] = "Tie match, can't conclude.";
			return;
		}
		$_POST['matchWinnerID'] = 'tie';
	} elseif($matchInfo['fighter1score'] > $matchInfo['fighter2score']){
		$_POST['matchWinnerID'] = $matchInfo['fighter1ID'];
	} elseif($matchInfo['fighter2score'] > $matchInfo['fighter1score']){
		$_POST['matchWinnerID'] = $matchInfo['fighter2ID'];
	} else {
		$_SESSION['alertMessages']['userErrors'][] = "Unable to determine how to conclude match";
		return;
	}
	
	addMatchWinner();
}

/******************************************************************************/

function createConsolationBracket($tournamentID, $numFighters){
	if(USER_TYPE < USER_ADMIN){return;}

	$bracketLevels = getBracketDepthByFighterCount($numFighters,2);	
	$sql = "INSERT INTO eventGroups
			(tournamentID, groupName, groupNumber, bracketLevels, groupType, numFighters)
			VALUES
			({$tournamentID}, 'loser', 2, {$bracketLevels}, 'elim', {$numFighters})";
	mysqlQuery($sql, SEND);
		
	$groupID = mysqli_insert_id($GLOBALS["___mysqli_ston"]);

// Single Elim
	if($numFighters == 2){ 
		$sql = "INSERT INTO eventMatches
				(groupID, bracketPosition, bracketLevel)
				VALUES
				({$groupID}, 1, 1)";
		mysqlQuery($sql, SEND);
		
// Double Elim
	} else {
			
		$matchesToSkip = getNumEntriesAtLevel_consolation($bracketLevels,'fighters') - $numFighters;
		
		for($bracketLevel=$bracketLevels;$bracketLevel>0;$bracketLevel--){
			$matchesInLevel = getNumEntriesAtLevel_consolation($bracketLevel,'matches');

			$bracketPosition = 0;
			for($currentMatch=1;$currentMatch<=$matchesInLevel;$currentMatch++){
				
				if($bracketLevel==$bracketLevels AND $currentMatch <= $matchesToSkip){
					continue;}
				
				$bracketPosition = getBracketPositionByRank($currentMatch,$matchesInLevel);
				//$bracketPosition++;

				$sql = "INSERT INTO eventMatches
						(groupID, bracketPosition, bracketLevel)
						VALUES
						({$groupID}, {$bracketPosition}, {$bracketLevel})";
				mysqlQuery($sql, SEND);
			}
		}
		
		
	}
}

/******************************************************************************/

function createNewPools(){
	if(USER_TYPE < USER_ADMIN){return;}
	$_SESSION['eventChanges']['poolsModified'] = true;
	
	$tournamentID = $_SESSION['tournamentID'];
	$numPoolsToAdd=$_POST['numPoolsToAdd'];
	$groupSet = $_SESSION['groupSet'];
	$pools = getPools($tournamentID, $groupSet);
	$numExistingPools = count($pools);
	$nextPoolNumber = ++$numExistingPools;
	$name = "Pool {$nextPoolNumber}";
	
	for($i=1;$i<=$numPoolsToAdd;$i++){
		
		$sql = "INSERT INTO eventGroups
				(tournamentID, groupType, groupNumber, groupName, groupSet)
				VALUES
				({$tournamentID}, 'pool', '{$nextPoolNumber}', '{$name}', {$groupSet})";
		mysqlQuery($sql, SEND);
		
		$nextPoolNumber++;
		$name = "Pool {$nextPoolNumber}";
	}

	//$_SESSION['checkEvent'][$tournamentID]['order'] = true;
}

/******************************************************************************/

function createNewRounds(){
	if(USER_TYPE < USER_ADMIN){return;}
	$_SESSION['eventChanges']['roundsModified'] = true;
	
	$tournamentID = $_SESSION['tournamentID'];
	$numRoundsToAdd=$_POST['numRoundsToAdd'];
	$groupSet = $_POST['setToAddRounds'];
	$rounds = getRounds($tournamentID, $groupSet);
	$numExistingRounds = count($rounds);
	$roundNumber = $numExistingRounds;
	
	for($i=1;$i<=$numRoundsToAdd;$i++){
		$roundNumber++;
		$name = "Round {$roundNumber}";
		$sql = "INSERT INTO eventGroups
				(tournamentID, groupType, groupNumber, groupName, groupSet)
				VALUES
				({$tournamentID}, 'round', '{$roundNumber}', '{$name}', '{$groupSet}')";
		mysqlQuery($sql, SEND);
	}
	
	$_SESSION['groupSet'] = $groupSet;

}

/******************************************************************************/

function createWinnersBracket($tournamentID, $numFighters){
// creates a winners bracket
	
	if(USER_TYPE < USER_ADMIN){return;}
	$bracketLevels = ceil(log($numFighters,2));
	$matchesToSkip = pow(2,$bracketLevels) - $numFighters;

	{// Create The Group
	$sql = "DELETE FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'";
	mysqlQuery($sql, SEND);
	
	$sql = "INSERT INTO eventGroups
				(tournamentID, groupName, groupNumber, bracketLevels, groupType,numFighters)
				VALUES
				({$tournamentID}, 'winner', 1, {$bracketLevels}, 'elim',{$numFighters})";
	mysqlQuery($sql, SEND);

	$groupID = mysqli_insert_id($GLOBALS["___mysqli_ston"]);
	}
	// Create By Matches

	for($bracketLevel=$bracketLevels;$bracketLevel>0;$bracketLevel--){
		$matchesInLevel = pow(2,$bracketLevel-1);
		
		for($currentMatch=1;$currentMatch<=$matchesInLevel;$currentMatch++){
			if($bracketLevel==$bracketLevels AND $currentMatch <= $matchesToSkip){
				continue;
			}
			
			$bracketPosition = getBracketPositionByRank($currentMatch,$matchesInLevel);

			$sql = "INSERT INTO eventMatches
					(groupID, bracketPosition, bracketLevel)
					VALUES
					({$groupID}, {$bracketPosition}, {$bracketLevel})";
			mysqlQuery($sql, SEND);
		}
	}
}


/******************************************************************************/

function deleteBracket(){
	
	if(USER_TYPE < USER_ADMIN){return;}
	
	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){return;}

	$sql = "DELETE FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'";
	mysqlQuery($sql, SEND);
	
}

/******************************************************************************/

function deleteExchanges(){
	
	if(USER_TYPE < USER_STAFF){return;}
	
	if(!isset($_POST['exchangesToDelete'])){return;}
	$matchID = $_POST['matchID'];

	if($matchID == null){
		$exchID = current(array_keys($_POST['exchangesToDelete']));

		$sql = "SELECT matchID
				FROM eventExchanges
				WHERE exchangeID = {$exchID}";
		$matchID = mysqlQuery($sql, SINGLE, 'matchID');
	}
	
	if((int)$matchID == 0){return;}

	if(isset($_POST['exchangesToDelete']['all'])){
		$sql = "DELETE FROM eventExchanges
				WHERE matchID = {$matchID}";
		mysqlQuery($sql, SEND);
	} else {
		foreach($_POST['exchangesToDelete'] as $exchangeID => $val){
			$sql = "DELETE FROM eventExchanges
					WHERE exchangeID = {$exchangeID}";
			mysqlQuery($sql, SEND);
		}
	}
	
	$matchInfo = getMatchInfo($matchID);
	updateMatch($matchInfo);
	
}

/******************************************************************************/

function deleteFromGroups(){

// Error Checking
	if(USER_TYPE < USER_ADMIN){return;}
	$eventID = $_SESSION['eventID'];
	$tournamentID = $_SESSION['tournamentID'];
	if($eventID == null || $tournamentID == null ){
		$_SESSION['alertMessages']['systemErrors'][] = "No eventID in deleteFromGroup()";
		return;
	}

// Delete Groups
	if(isset($_POST['deleteGroup'])){
		foreach($_POST['deleteGroup'] as $groupID => $fillerData){
			$checkID = $groupID;
			$sql = "DELETE FROM eventGroups
					WHERE groupID = {$groupID}";		
			mysqlQuery($sql, SEND);
			
			$_SESSION['checkEvent'][$tournamentID]['order'] = true;
		}
	}
		
// Delete Fighters from a Group
	if(isset($_POST['deleteFromGroup'])){
		foreach($_POST['deleteFromGroup'] as $groupID => $poolDeletions){
			$checkID = $groupID;
			foreach($poolDeletions as $rosterID => $true){	
				$sql = "DELETE FROM eventGroupRoster
						WHERE rosterID = {$rosterID}
						AND groupID = {$groupID}";
				mysqlQuery($sql, SEND);	
				$_SESSION['checkEvent'][$tournamentID][$groupID]['all'] = true;
			}
		}
	}

	if(isset($checkID)){
		$sql = "SELECT groupType, groupSet
				FROM eventGroups
				WHERE groupID = {$checkID}";
		$data = mysqlQuery($sql, SINGLE);
		
		if($data['groupType'] == 'round'){
			$_SESSION['groupSet'] = $data['groupSet'];
		}
	}
	
	
// Re-Calculate Scores
	if(isPools($_SESSION['tournamentID'])){
		$_SESSION['updatePoolStandings'][$tournamentID] = ALL_GROUP_SETS;
	}
	
}

/******************************************************************************/

function deleteFromTournament(){

	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){return;}
	if(USER_TYPE < USER_STAFF){return;}
	

	foreach((array)$_POST['deleteFromTournament'] as $rosterID => $true){
	
		$sql = "DELETE FROM eventTournamentRoster
				WHERE rosterID = {$rosterID}
				AND tournamentID = {$tournamentID}";
		mysqlQuery($sql, SEND);
		
		$_SESSION['checkEvent'][$tournamentID]['all'] = true;
		// Re-calculate the pool scores if a fighter who has alread fought it removed
		$_SESSION['updatePoolStandings'][$tournamentID] = ALL_GROUP_SETS; 
	}
	
	updateTournamentFighterCounts($tournamentID);
	
}

/******************************************************************************/

function deleteRounds(){
	
	if(USER_TYPE < USER_STAFF){return;}
	
	foreach((array)$_POST['roundIDtoDelete'] as $groupID => $stuff){
		$sql = "SELECT tournamentID
					FROM eventGroups
					WHERE groupID = {$groupID}";
		$tournamentID = mysqlQuery($sql, SINGLE, 'tournamentID');
		
		$sql = "DELETE FROM eventGroups
				WHERE groupID = {$groupID}";
		mysqlQuery($sql, SEND);
		
		$_SESSION['checkEvent'][$tournamentID]['order'] = true;
	}
	
}

/******************************************************************************/

function editEvent(){
	if(USER_TYPE < USER_SUPER_ADMIN){return;}

	if(isset($_POST['eventToEdit'])){
		$_SESSION['editEventID'] = $_POST['eventToEdit'];
		return;
	}
	
	$eventID = $_POST['eventID'];
	$name = getEventName($eventID);
	
	if(isset($_POST['deleteEvent'])){
		
		if($_POST['confirmDelete'] == $_POST['deleteCode']){
			$sql = "DELETE FROM systemEvents
					WHERE eventID = {$eventID}";
			mysqlQuery($sql, SEND);
			$_SESSION['alertMessages']['userAlerts'][] = "\"{$name}\" deleted";
			
		} else {
			$_SESSION['alertMessages']['userErrors'][] = "\"{$name}\" not deleted<BR>Confirmation number incorrect";
		}
		return;
	}

	$sql = "SELECT eventStatus FROM systemEvents
			WHERE eventID = {$eventID}";
	$oldStatus = mysqlQuery($sql, SINGLE, 'eventStatus');

	if($_POST['eventStatus'] == 'archived' && $oldStatus != 'archived'){
		setAlert(SYSTEM,"Remember to let HEMA Ratings know!");
	}
	
	$eventYear = substr($_POST['eventStartDate'],0,4);
	
	$sql = "UPDATE systemEvents
			SET
			eventName=?, 
			eventAbreviation=?,
			eventYear=?,
			eventStartDate=?,
			eventEndDate=?, 
			eventCountry=?, 
			eventProvince=?, 
			eventCity=?,
			eventStatus=?
			WHERE eventID = {$eventID}";

	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	$bind = mysqli_stmt_bind_param($stmt, "sssssssss", 
			$_POST['eventName'], $_POST['eventAbreviation'],
			$eventYear, $_POST['eventStartDate'],
			$_POST['eventEndDate'], $_POST['eventCountry'], 
			$_POST['eventProvince'], $_POST['eventCity'],
			$_POST['eventStatus']);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	
	setAlert(USER_ALERT,"<strong>{$name}</strong> updated");
	
}

/******************************************************************************/

function editEventStatus(){
	
	if(USER_TYPE < USER_ADMIN){ return; }
	$eventID = $_SESSION['eventID'];
	if($eventID == null){return;}
	
	$eventStatus = $_POST['eventStatus'];
	
	$sql = "UPDATE systemEvents SET eventStatus = ?
			WHERE eventID = {$eventID}";
	
	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	// "s" means the database expects a string
	$bind = mysqli_stmt_bind_param($stmt, "s", $eventStatus);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	
	$_SESSION['alertMessages']['userAlerts'][] = "Event Status updated";
	
}

/******************************************************************************/

function editEventParticipant(){


	$eventID = $_SESSION['eventID'];
	if($eventID == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No eventID in editEventParticipant";
		return;}
	if(USER_TYPE < USER_ADMIN){return;}
	
	// If the editing mode needs to be enabled
	if(!isset($_POST['editParticipantData'])){
		$_SESSION['editParticipant'] = $_POST['rosterID'];
		$_SESSION['alertMessages']['systemErrors'][] = "Editing mode not enables in editEventParticipant()";
		return;
	}	

	// Data recieved from editing mode
	$tournamentIDs = getEventTournaments();	
	
	$rosterID = $_POST['editParticipantData']['rosterID'];
	if($rosterID == null){
		$_SESSION['alertMessages']['userErrors'][] = "Can not make changes, no fighter specified";
		return;
	}

	$sql = "SELECT systemRosterID
			FROM eventRoster
			WHERE rosterID = {$rosterID}";
	$systemRosterID = mysqlQuery($sql, SINGLE, 'systemRosterID');

	$firstName = rtrim($_POST['editParticipantData']['firstName']);
	$lastName = rtrim($_POST['editParticipantData']['lastName']);
	$schoolID = $_POST['editParticipantData']['schoolID'];
	$tournaments = @$_POST['editParticipantData']['tournamentIDs']; // There may be no tournaments set

	$sql = "UPDATE systemRoster
			SET firstName = ?, lastName = ?
			WHERE systemRosterID = {$systemRosterID}";

	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	$bind = mysqli_stmt_bind_param($stmt, "ss", $firstName, $lastName);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	
	$sql = "UPDATE eventRoster
			SET schoolID = {$schoolID}
			WHERE rosterID = {$rosterID}";
	mysqlQuery($sql, SEND);

	$sql = "UPDATE systemRoster
			SET schoolID = {$schoolID}
			WHERE systemRosterID = {$systemRosterID}";
	mysqlQuery($sql, SEND);

	foreach($tournamentIDs as $tournamentID){

		if(isset($tournaments[$tournamentID])){
			// Only inserts if they aren't already entered
					
			$sql = "SELECT *
					FROM eventTournamentRoster
					WHERE tournamentID = {$tournamentID}
					AND rosterID = {$rosterID}";
			$result = mysqlQuery($sql, SINGLE);
			
			if($result == null){
				if(isFinalized($tournamentID)){
					$name = getFighterName($rosterID);
					$tName = getTournamentName($tournamentID);
					
					$_SESSION['alertMessages']['userErrors'][] = "<span class='red-text'>Edit Failed</span>
						- Tournament has already been finalized<BR>
					 	<strong>{$name}</strong> can not be added to <strong>{$tName}</strong>";
					continue;
				}
				
				$sql = "INSERT INTO eventTournamentRoster
						(tournamentID, rosterID)
						VALUES
						({$tournamentID}, {$rosterID})";
				mysqlQuery($sql, SEND);
			}		
				
		} else {
			$sql = "SELECT tableID
					FROM eventTournamentRoster
					WHERE rosterID = {$rosterID}
					AND tournamentID = {$tournamentID}";
					
			if(mysqlQuery($sql, SINGLE, 'tableID') != null){
				if(isFinalized($tournamentID)){
					$name = getFighterName($rosterID);
					$tName = getTournamentName($tournamentID);
					
					$_SESSION['alertMessages']['userErrors'][] = "<span class='red-text'>Edit Failed</span> 
						- Tournament has already been finalized<BR>
						<strong>{$name}</strong> can not be removed from <strong>{$tName}</strong>";
					continue;
				}
				
				
				$sql = "DELETE FROM eventTournamentRoster
						WHERE rosterID = {$rosterID}
						AND tournamentID = {$tournamentID}";
				mysqlQuery($sql, SEND);

				$_SESSION['checkEvent'][$tournamentID]['all'] = true;
			}
		
		}
		
	}
	
	$_SESSION['jumpTo'] = "anchor{$rosterID}";
	updateTournamentFighterCounts();
}

/******************************************************************************/

function generateTournamentPlacings($tournamentID){
	
	if(USER_TYPE < USER_STAFF){return;}
	
	if($tournamentID == null){
		displayAlert("No tournamentID in generateTournamentPlacings()");
		return;
	}
	if($tournamentID == 'cancel'){
		return;
	}
	
	if(isset($_POST['enableManualTournamentPlacing'])){
		$_SESSION['manualTournamentPlacing'] = $tournamentID;
		return;
	}
	
	$_SESSION['jumpTo'] = "anchor{$tournamentID}";
	
	// If tournament placings have been manualy specified by the used.
	if(isset($_POST['manualTournamentPlacing'])){
		$tournamentID = $_POST['tournamentID'];
		unset($_POST['tournamentID']);
		
		// Check if a fighter is attempting to be entered in multiple places
		// Cancel opperation and return with error message
		foreach((array)$_POST['placing'] as $place => $rosterID){
			if($rosterID == null || $tournamentID == null || $place== null){ continue;}
			if(@$inPlace[$rosterID] == true){ // not being set is the same logical case as false
				$_SESSION['manualPlacingMessage'][$tournamentID] = "The same fighter is entered in more than on place. Can not finalize results.";
				$_SESSION['alertMessages']['userErrors'][] = "Fighters entered in more than on location. Can not finalize results.";
				$_SESSION['lastManualPlacingAttempt'] = $_POST['placing'];
				if(isBrackets($tournamentID)){
					generateTournamentPlacings_bracket($tournamentID);
				} elseif (isRounds($tournamentID)){
					generateTournamentPlacings_round($tournamentID);
				} else{
					generateTournamentPlacings_set($tournamentID);
				}
				
				return;
			} else {
				$inPlace[$rosterID] = true;
			}
		}
		
		$type = 'final';
		$low = 'null';
		$high = 'null';
		
		// Step through and write all placings
		foreach((array)$_POST['placing'] as $place => $rosterID){
			if($rosterID == null || $tournamentID == null || $place== null){ continue;}
			
			// Handles ties
			if(isset($_POST['ties'][$place])){
				$type = 'tie';
				$low = $place;
				$high = $_POST['ties'][$place];
			} 
			
			writeTournamentPlacing($rosterID,$tournamentID,$place,$type,$low,$high);
			
			// Resets to normal after done entering tied fighters
			if($place == $high){
				$type = 'final';
				$low = 'null';
				$high = 'null';
			}
		}
		
		$sql = "UPDATE eventTournaments
				SET isFinalized = 1
				WHERE tournamentID = {$tournamentID}";
		mysqlQuery($sql, SEND);
		return;
	
	} elseif(isBrackets($tournamentID)){
		generateTournamentPlacings_bracket($tournamentID);
	} elseif (isRounds($tournamentID)){
		generateTournamentPlacings_round($tournamentID);
	} else{
		generateTournamentPlacings_set($tournamentID);
	}
	
}

/******************************************************************************/

function generateTournamentPlacings_set($tournamentID){
	
	$numSets = getNumGroupSets($tournamentID);
	
	$placeNum = 0;
	for($set = $numSets; $set >= 1; $set--){
		$sql = "SELECT  rosterID, rank, score
				FROM eventStandings
				WHERE tournamentID = {$tournamentID}
				AND groupSet = {$set}
				ORDER BY rank ASC";
		$roundData = mysqlQuery($sql, ASSOC);
	
		$oldScore = null;
		foreach($roundData as $placing){
			$rosterID = $placing['rosterID'];
			if(isset($assignedFighters[$rosterID])){ continue; }
			
			$score = $placing['score'];
			
			$assignedFighters[$rosterID] = true;
			$overallScores[$placeNum] = $rosterID;
			$placeNum++;
			
			// Check if the fighter is tied with the previous fighter
			if($score == $oldScore){
				$ties[count($overallScores)] = 'end';
				$ties[count($overallScores)-1] = true;
			}
			$oldScore = $score;
		}
	}
	
	// Return and ask for confirmation on what to do with ties
	if(isset($ties)){
		$_SESSION['overallScores'] = $overallScores;
		$_SESSION['ties'] = $ties;
		$_SESSION['manualTournamentPlacing'] = $tournamentID;
		$_SESSION['alertMessages']['userErrors'][] = "<span class='red-text'>Results could not be finalized.</span><BR>
			Ties detected, please 
			<a href='infoSummary.php#{$_SESSION['jumpTo']}'>
			confirm results manualy</a>";
		$_SESSION['manualPlacingMessage'][$tournamentID] = "Detected ties in the scoring. 
			Please confirm this list represents the final rankings. Fighters with tie scores are displayed in red.";
		return false;
	}
	
	// Delete old placings, if they exist, and write new
	$sql = "DELETE FROM eventPlacings
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);
	
	$place=0;
	foreach($overallScores as $rosterID){
		++$place;
		writeTournamentPlacing($rosterID,$tournamentID,$place);
	}
	
	$sql = "UPDATE eventTournaments
			SET isFinalized = 1
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);
}

/******************************************************************************/

function generateTournamentPlacings_round($tournamentID){
	
	$sql = "SELECT fighter1ID, fighter1Score, groupSet, groupNumber 
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'round'";
	$res = mysqlQuery($sql, ASSOC);
	
	foreach($res as $match){
		if($match['fighter1Score'] === null){ continue;	}
		$scores[$match['groupSet']][$match['groupNumber']][$match['fighter1ID']] = $match['fighter1Score'];
	}
	
	$overalScores = array();
	for($groupSet = count($scores); $groupSet >= 1; $groupSet--){
		for($groupNum = count($scores[$groupSet]); $groupNum >= 1; $groupNum--){
			
			$roundScores = [];
			foreach($scores[$groupSet][$groupNum] as $rosterID => $score){
				if(isset($fightersInList[$rosterID])){ continue; }
				$score = 0;
				foreach($scores[$groupSet] as $pieces){
					$score += $pieces[$rosterID];
				}
				$fightersInList[$rosterID] = true;
				
				$roundScores[$rosterID] = $score;
			}
			
			
			if(isset($roundScores)){
				arsort($roundScores);
				$oldScore = null;
				foreach($roundScores as $rosterID => $score){
					$overallScores[] = $rosterID;
					if($score == $oldScore){
						$ties[count($overallScores)] = 'end';
						$ties[count($overallScores)-1] = true;
					}
					$oldScore = $score;
				}
			}
		}
	}
	
	
	// Return and ask for confirmation on what to do with ties
	if(isset($ties)){
		$_SESSION['overallScores'] = $overallScores;
		$_SESSION['ties'] = $ties;
		$_SESSION['manualTournamentPlacing'] = $tournamentID;
		$_SESSION['alertMessages']['userErrors'][] = "<span class='red-text'>Results could not be finalized.</span><BR>
			Ties detected, please 
			<a href='infoSummary.php#{$_SESSION['jumpTo']}'>
			confirm results manualy</a></p>";
		$_SESSION['manualPlacingMessage'][$tournamentID] = "Detected ties in the scoring. 
			Please confirm this list represents the final rankings. Fighters with tie scores are displayed in red.";
		return;
	}
	
	// Delete old placings, if they exist, and write new
	$sql = "DELETE FROM eventPlacings
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);
	
	$place=0;
	foreach((array)$overallScores as $rosterID){
		++$place;
		writeTournamentPlacing($rosterID,$tournamentID,$place);
	}
	
	$sql = "UPDATE eventTournaments
			SET isFinalized = 1
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);

}

/******************************************************************************/

function generateTournamentPlacings_bracket($tournamentID){
	
	$sql = "SELECT groupID, groupName
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'";
	$groups = mysqlQuery($sql, KEY, 'groupName');
	
	$winnerBracketID = $groups['winner']['groupID'];
	$loserBracketID = @$groups['loser']['groupID']; // may not exist if there is no loser bracket

	$bracketInfo = getBracketInformation($tournamentID);
	
	$winnerMatches = getBracketMatchesByPosition($winnerBracketID);
	$loserMatches = getBracketMatchesByPosition($loserBracketID);
	
	$sql = "DELETE FROM eventPlacings
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);
	
	// Top 4
	writeTournamentPlacing($winnerMatches[1][1]['winnerID'],$tournamentID,1);
	writeTournamentPlacing($winnerMatches[1][1]['loserID'],$tournamentID,2);
	writeTournamentPlacing($loserMatches[1][1]['winnerID'],$tournamentID,3);
	writeTournamentPlacing($loserMatches[1][1]['loserID'],$tournamentID,4);
	
	if(isset($bracketInfo['loser'])){
		// Double Elim
		foreach($loserMatches as $bracketLevel => $levelData){
			if($bracketLevel == 1 ){continue;}
			foreach($levelData as $bracketPosition => $matchInfo){
				$high = getNumEntriesAtLevel_consolation($bracketLevel,'fighters')+2;
				//$max = $bracketInfo['loser']['numFighters']+2;
				//if($high > $max){$high = $max;}
				$low = getNumEntriesAtLevel_consolation($bracketLevel-1,'fighters')+3;
				writeTournamentPlacing($matchInfo['loserID'],$tournamentID,$high,'bracket',$low,$high);
			}
		}
	} else {
		// Single Elim
		
		foreach($winnerMatches as $bracketLevel => $levelData){
			if($bracketLevel == 1 || $bracketLevel == 2){continue;}
			foreach($levelData as $bracketPosition => $matchInfo){
				$high = pow(2,$bracketLevel);
				$low = pow(2,$bracketLevel-1)+1;
				writeTournamentPlacing($matchInfo['loserID'],$tournamentID,$high,'bracket',$low,$high);
			}
		}
	}
	
	$sql = "UPDATE eventTournaments
			SET isFinalized = 1
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);
	
}


/******************************************************************************/

function insertLastExchange($matchInfo, $exchangeType, $rosterID, $scoreValueIn, $scoreDeductionIn, 
							$refPrefix = null, $refType = null, $refTarget = null, $exchangeID = null){
// records a new exchange into the match	
	
	if(USER_TYPE < USER_STAFF){return;}
	
	$scoreValue = (int)$scoreValueIn;
	$scoreDeduction = abs((int)$scoreDeductionIn);
	
	$matchID = $matchInfo['matchID'];

	if($matchInfo['fighter1ID'] == $rosterID){
		$recievingID = $matchInfo['fighter2ID'];
	} else if($matchInfo['fighter2ID'] == $rosterID){
		$recievingID = $matchInfo['fighter1ID'];
	} else {
		$rosterID = $matchInfo['fighter1ID'];
		$recievingID = $matchInfo['fighter2ID'];
	}
	if(isset($_POST['matchTime']) && $_POST['matchTime'] !== null){
		$exchangeTime = (int)$_POST['matchTime'];
	} else{
		$exchangeTime = 'NULL';
	}
	
	if($refPrefix == null){
		$refPrefix = 'NULL';
	}
	if($refType == null){
		$refType = 'NULL';
	}
	if($refTarget == null){
		$refTarget = 'NULL';
	}

	if($exchangeID == null){
		$sql = "SELECT COUNT(exchangeID) AS numExchanges
				FROM eventExchanges
				WHERE matchID = {$matchID}";
		$exchangeNumber = mysqlQuery($sql, SINGLE, 'numExchanges');
		$exchangeNumber++;

		$sql = "INSERT INTO eventExchanges
				(matchID, exchangeType, scoringID, recievingID, scoreValue, 
				scoreDeduction, exchangeTime, refPrefix, refType, refTarget, exchangeNumber)
				VALUES
				({$matchID}, '{$exchangeType}', {$rosterID}, {$recievingID}, {$scoreValue}, 
				{$scoreDeduction}, {$exchangeTime}, {$refPrefix}, {$refType}, {$refTarget}, {$exchangeNumber})";
	} else {
		$sql = "UPDATE eventExchanges
				SET matchID 	= {$matchID}, 
				exchangeType	= '{$exchangeType}', 
				scoringID		= {$rosterID}, 
				recievingID		= {$recievingID}, 
				scoreValue 		= {$scoreValue}, 
				scoreDeduction  = {$scoreDeduction}, 
				exchangeTime	= {$exchangeTime}, 
				refPrefix		= {$refPrefix}, 
				refType			= {$refType}, 
				refTarget		= {$refTarget}
				WHERE exchangeID = {$exchangeID}";
	}

	mysqlQuery($sql, SEND);

}

/******************************************************************************/

function insertNewEventParticipant($firstName, $lastName, $schoolID, $tournamentIDs){
	
	if(USER_TYPE < USER_STAFF){return;}
	
	$eventID = $_SESSION['eventID'];
	
	if($eventID == null || $schoolID == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No eventID in insertNewEventParticipant()";
		return;
	}

	$sql = "INSERT INTO systemRoster 
			(firstName, lastName, schoolID)
			VALUES
			(?,?,?)
			";

	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	// "s" means the database expects a string
	$bind = mysqli_stmt_bind_param($stmt, "ssi", $firstName, $lastName, $schoolID);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	
	$systemRosterID = mysqli_insert_id($GLOBALS["___mysqli_ston"]);
	
	$sql = "INSERT INTO eventRoster 
			(systemRosterID, schoolID, eventID)
			VALUES
			({$systemRosterID}, {$schoolID}, {$eventID})
			";

	mysqlQuery($sql,SEND);
	$rosterID = mysqli_insert_id($GLOBALS["___mysqli_ston"]); 
	
	foreach((array)$tournamentIDs as $num => $tournamentID){
		if(!is_int($num)){continue;}

		$sql = "INSERT INTO eventTournamentRoster
				(tournamentID, rosterID)
				VALUES
				({$tournamentID}, {$rosterID})";
		mysqlQuery($sql, SEND);		
		
	}
}

/******************************************************************************/

function importRosterCSV(){
	if(USER_TYPE < USER_ADMIN){return;}
	
	$csv_mimetypes = array(
		'text/csv',
		'text/plain',
		'application/csv',
		'text/comma-separated-values',
		'application/excel',
		'application/vnd.ms-excel',
		'application/vnd.msexcel',
		'text/anytext',
		'application/octet-stream',
		'application/txt',
	);
	
	if (!in_array($_FILES['csv_file']['type'], $csv_mimetypes)
		|| substr($_FILES['csv_file']['name'], -4) != '.csv') {
		$_SESSION['alertMessages']['userErrors'][] = "That's not a .csv file!";
		return;
	}
	

	if(isset($_FILES['csv_file']) && is_uploaded_file($_FILES['csv_file']['tmp_name'])){
		//upload directory
		$upload_dir = "exports/";
		$tmpName = date("YmdHis").".csv";

		//create file name
		$filePath = $upload_dir . $tmpName;
		
		
		//move uploaded file to upload dir
		if (!move_uploaded_file($_FILES['csv_file']['tmp_name'], $filePath)) {  
			//error moving upload file
			$_SESSION['alertMessages']['systemErrors'][] = "Could not move uploaded file in importRosterCSV()";
			return;
		}
	}
	
	$file = fopen($filePath, 'r');
	
	$standardFormat = array('firstName', 'lastName', 'school');
	
	$a = fgetcsv($file, 1000, ',');
	foreach($a as $index => $name){
		if($index < sizeof($standardFormat)){
			// If it's a name or school header
			
			if($name != $standardFormat[$index]){
				$_SESSION['alertMessages']['userErrors'][] = "<strong>File could not be loaded</strong><BR>
					Incorrect file header row<BR>
					Use 'firstName','lastName','school'";
				$errorFlag = true;
			}
		} else {
			//If it's a tournament
			
			if(is_numeric($name)){
				$tournamentID = (int)$name;
				$sql = "SELECT eventID
						FROM eventTournaments
						WHERE tournamentID = {$tournamentID}";
				$eventID = mySqlQuery($sql, SINGLE, 'eventID');
				if($eventID != $_SESSION['eventID']){
					$_SESSION['alertMessages']['userErrors'][] = "<strong>File could not be loaded</strong><BR>
						tournamentID $tournamentID is not a tournament in this event.";
					$errorFlag = true;
				}
				$tournamentName = getTournamentName($tournamentID);
				
			} else {
				$allTournaments = getEventTournaments();
				$tournamentName = $name;
				foreach($allTournaments as $checkID){
					$checkName = getTournamentName($checkID);
					
					if($tournamentName === $checkName){
						
						$tournamentID = $checkID;
						$tournamentFound = true;
					}
				}
				
				if($tournamentFound != true){
					$_SESSION['alertMessages']['userErrors'][] = "<strong>File could not be loaded</strong><BR>
						<strong>'$tournamentName'</strong> does not match any tournament in the event.<BR>
						The spelling must be <u>exact</u> for a match.";
					$errorFlag = true;
				}
			}
			
			
			
			$tournamentList[$tournamentID] = $tournamentName;
			$name = $tournamentID;
		}
		
		
		if($errorFlag == true){
			fclose($file);
			unlink($filePath);
			return;
		}
	
		$fields[$index] = $name;
	}


    while (($data = fgetcsv($file, 1000, ',')) !== FALSE) {
		$fighter = [];
		foreach($data as $index => $fieldData){
			$fighter[$fields[$index]] = $fieldData;
		}
		$roster[] = $fighter;
	}
	
	fclose($file);
	unlink($filePath);
	
	$_SESSION['csvRosterAdditions'] = $roster;
	$_SESSION['csvTournamentList'] = $tournamentList;

}

/******************************************************************************/

function recordScores($allFighterStats, $tournamentID, $groupType, $groupSet = null){
	
	if(USER_TYPE < USER_STAFF){return;}
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	
	if($groupType == null){
		$groupType = 'pool';
	}
	if($groupSet == null){ $groupSet = $_SESSION['groupSet']; }
	if($groupSet == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No groupSet in recordScores()";
		return; 
	}


// Find out what exists in the DB so it is known what needs to be updated vs inserted
	$sql = "SELECT standingID, rosterID
			FROM eventStandings
			WHERE tournamentID = {$tournamentID}
			AND groupType = '{$groupType}'
			AND groupSet = {$groupSet}";
	$existingStandings = mysqlQuery($sql, ASSOC);
	
	$standingsToDelete = [];
	foreach((array)$existingStandings as $standing){
		$rosterID = $standing['rosterID'];
		if(isset($allFighterStats[$rosterID])){
			$standingsToUpdate[$rosterID] = $standing['standingID'];
		} else{
			$standingsToDelete[] = $standing['standingID'];
		}
	}



// Delete old standings
	foreach($standingsToDelete as $standingID){
		$sql = "DELETE FROM eventStandings
				WHERE standingID = {$standingID}";
		mysqlQuery($sql, SEND);
	}

// Go through each fighter and record their stats
	foreach((array)$allFighterStats as $rosterID => $fighterStats){

		// If the standings already exist
		if(isset($standingsToUpdate[$rosterID])){

			$standingID = $standingsToUpdate[$rosterID];
			$updateString = null;

			foreach($fighterStats as $field => $value){
				$updateString .= "{$field} = {$value}, ";
			}
			
			$updateString= rtrim($updateString,', \t\n');
			
			$sql = "UPDATE eventStandings
					SET
					{$updateString}
					WHERE standingID = $standingID";
			mysqlQuery($sql, SEND);

		// Insert new standing
		} else {

			$fieldString = "rosterID,";
			$valueString = "{$rosterID},";


			foreach($fighterStats as $field => $value){
				$fieldString .= "{$field}, ";
				$valueString .= "{$value}, ";
			}
			
			$fieldString = rtrim($fieldString,', \t\n');
			$valueString = rtrim($valueString,', \t\n');
			
			$sql = "INSERT INTO eventStandings
					(groupType, tournamentID, groupSet,{$fieldString})
					VALUES
					('{$groupType}', {$tournamentID}, {$groupSet},{$valueString})";
				
			mysqlQuery($sql, SEND);
		}
	}

	if(isCumulative($groupSet, $tournamentID) && $groupSet > 1){
		$lastGroupSet = $groupSet - 1;


		$sql = "SELECT rosterID, matches, wins, losses, ties, pointsFor, pointsAgainst,
					hitsFor, hitsAgainst, afterblowsFor, afterblowsAgainst, doubles,
					noExchanges, AbsPointsFor, AbsPointsAgainst, numPenalties, 
					penaltiesAgainstOpponents, penaltiesAgainst, doubleOuts
				FROM eventStandings
				WHERE tournamentID = {$tournamentID}
				AND groupType = 'pool'
				AND groupSet = {$lastGroupSet}";

		$lastSetScores = mysqlQuery($sql, ASSOC);

		foreach($lastSetScores as $score){
			
			$sql = "UPDATE eventStandings
					SET 
						matches 			= matches + {$score['matches']},
						wins 				= wins + {$score['wins']},
						losses 				= losses + {$score['losses']},
						ties 				= ties + {$score['ties']},
						pointsFor 			= pointsFor + {$score['pointsFor']},
						pointsAgainst 		= pointsAgainst + {$score['pointsAgainst']},
						hitsFor				= hitsFor + {$score['hitsFor']},
						hitsAgainst 		= hitsAgainst + {$score['hitsAgainst']},
						afterblowsFor 		= afterblowsFor + {$score['afterblowsFor']},
						afterblowsAgainst 	= afterblowsAgainst + {$score['afterblowsAgainst']},
						doubles 			= doubles + {$score['doubles']},
						noExchanges 		= noExchanges + {$score['noExchanges']},
						AbsPointsFor 		= AbsPointsFor + {$score['AbsPointsFor']},
						AbsPointsAgainst 	= AbsPointsAgainst + {$score['AbsPointsAgainst']},
						numPenalties 		= numPenalties + {$score['numPenalties']},
						penaltiesAgainstOpponents = penaltiesAgainstOpponents + {$score['penaltiesAgainstOpponents']},
						penaltiesAgainst	= penaltiesAgainst + {$score['penaltiesAgainst']},
						doubleOuts 			= doubleOuts + {$score['doubleOuts']}
					WHERE tournamentID = {$tournamentID}
					AND groupType = 'pool'
					AND groupSet = {$groupSet}
					AND rosterID = {$score['rosterID']}";

			mysqlQuery($sql, SEND);

		}

	}



}

/******************************************************************************/

function removeTournamentPlacings($tournamentID){
	
	if(USER_TYPE < USER_STAFF){return;}
	
	if($tournamentID == null){
		echo "No tournamentID in removeTournamentPlacings()";
		return;
	}
	
	$sql = "DELETE FROM eventPlacings
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);
	
	$sql = "UPDATE eventTournaments
			SET isFinalized = 0
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);
	
	$_SESSION['jumpTo'] = "anchor{$tournamentID}";
}

/******************************************************************************/

function renameGroup($renameData){
	
	$groupName = $renameData['groupName'];
	$groupID = $renameData['groupID'];
	
	$sql = "UPDATE eventGroups SET groupName = ?
			WHERE groupID = ?";

	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	// "s" means the database expects a string
	$bind = mysqli_stmt_bind_param($stmt, "si", $groupName,$groupID);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	
}

/******************************************************************************/

function renameGroups($maxGroupSets = null){
	
	foreach(@(array)$_POST['renameGroup'] as $groupID => $groupName){
		
		if($groupName == ''){
			$sql = "SELECT groupNumber, groupType
					FROM eventGroups
					WHERE groupID = {$groupID}";
			$groupStuff = mysqlQuery($sql, SINGLE);
			if($groupStuff['groupType'] == 'pool'){
				$groupName = "Pool ".$groupStuff['groupNumber'];	
			} elseif($groupStuff['groupType'] == 'round'){
				$groupName = "Round ".$groupStuff['groupNumber'];
			} else {
				$groupName = $groupStuff['groupNumber'];
			}
				
		}
		
		$sql = "UPDATE eventGroups SET groupName = ?
				WHERE groupID = {$groupID}";

		$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
		// "s" means the database expects a string
		$bind = mysqli_stmt_bind_param($stmt, "s", $groupName);
		$exec = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	}
	
	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No tournamentID in renameGroups()";
		return;
	}
	
	if($maxGroupSets == null){
		$maxGroupSets = getNumGroupSets($tournamentID);
	}
		
	foreach(@(array)$_POST['renameSet'] as $setNumber => $newName){
		if($setNumber > $maxGroupSets){ continue; }
		
		if($newName == null){
			
			$sql = "DELETE FROM eventAttributes
					WHERE tournamentID = {$tournamentID}
					AND attributeType = 'setName'
					AND attributeGroupSet = {$setNumber}";
			mysqlQuery($sql, SEND);
			
		} else {
			
			$sql = "SELECT attributeID
					FROM eventAttributes
					WHERE tournamentID = {$tournamentID}
					AND attributeType = 'setName'
					AND attributeGroupSet = {$setNumber}";
			$attributeID = mysqlQuery($sql, SINGLE, 'attributeID');
			
			if($attributeID == null){
				// Insert
				$sql = "INSERT INTO eventAttributes
						(tournamentID, attributeType, attributeGroupSet, attributeText)
						VALUES
						({$tournamentID}, 'setName', {$setNumber}, ?)";
				$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
				// "s" means the database expects a string
				$bind = mysqli_stmt_bind_param($stmt, "s", $newName);
				$exec = mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
				
			} else {
				
				// Update
				$sql = "UPDATE eventAttributes
						SET attributeText = ?
						WHERE attributeID = {$attributeID}";
				$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
				// "s" means the database expects a string
				$bind = mysqli_stmt_bind_param($stmt, "s", $newName);
				$exec = mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
			}
			
		}
	}
	
}

/******************************************************************************/

function reOrderGroups($groupList = null){
	
	if($groupList == null){
		$groupList = $_POST['newGroupNumber'];
	}
	
	foreach($groupList as $groupID => $groupNumber){
		$sql = "SELECT groupName, groupNumber, groupType
				FROM eventGroups
				WHERE groupID = {$groupID}";
		$data = mysqlQuery($sql, SINGLE);
		$oldName = $data['groupName'];
		$oldNumber = $data['groupNumber'];
		$type = $data['groupType'];
		
		$prefix = substr($oldName, 0, strpos($oldName, ' '));
		
		switch($type){
			case 'pool':
				if($oldName ==  "Pool {$oldNumber}"){
					$name = "Pool {$groupNumber}";
				} else {
					$name = $oldName;
				}
				break;
			case 'round':
				if($oldName ==  "Round {$oldNumber}"){
					$name = "Round {$groupNumber}";
				} else {
					$name = $oldName;
				}
				break;
			default:
				$name = $oldName;
		}
		
		$sql = "UPDATE eventGroups
				SET groupNumber = {$groupNumber}, groupName = '{$name}'
				WHERE groupID = {$groupID}";
		mysqlQuery($sql, SEND);
		
		$namesList[$groupID] = $name;
		
	}
	
	return $namesList;
	
}

/******************************************************************************/

function setLivestreamMatch($eventID = null){
	
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){ return; }
	

	$sql = "UPDATE eventLivestreams
			SET matchID = {$_POST['matchID']}
			WHERE eventID = {$eventID}";
	mysqlQuery($sql, SEND);
	
	$_SESSION['alertMessages']['userAlerts'][] = "This event is now showing on the livestream.";
		
}

/******************************************************************************/

function switchMatchFighters($matchID = null){

	if(USER_TYPE < USER_STAFF){return;}

	if($matchID == null){$matchID = $_SESSION['matchID'];}
	if($matchID == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No matchID in switchMatchFighters()";
		return;
	}
		
	$sql = "SELECT fighter1ID, fighter2ID, fighter1Score, fighter2Score, reversedColors
			FROM eventMatches
			WHERE matchID = {$matchID}";
	$info = mysqlQuery($sql, SINGLE);
	
	foreach((array)$info as $index => $data){
	// replaces null values with string for SQL insertion
		if($data === null){
			$info[$index] = 'null';
		}
	}
	
	$f1ID = $info['fighter1ID'];
	$f2ID = $info['fighter2ID'];
	$f1Score = $info['fighter1Score'];
	$f2Score = $info['fighter2Score'];
	
	if(@$info['reversedColors'] == 1){ 
		$rColors = 0;
	} else { 
		$rColors = 1; 
	}
	
	$sql = "UPDATE eventMatches
			SET fighter1ID = {$f2ID},
			fighter2ID = {$f1ID},
			fighter1Score = {$f2Score},
			fighter2Score = {$f1Score},
			reversedColors = {$rColors}
			WHERE matchID = {$matchID}";
	mysqlQuery($sql, SEND);

}

/******************************************************************************/

function updateContactEmail($email, $eventID = null){

	if(USER_TYPE < USER_ADMIN){return;}

	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		$_SESSION['alertMessages']['systemErrors'][] = "Invalid eventID in updateContactEmail";
		return;
	}

	if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
		$_SESSION['alertMessages']['userErrors'][] = "That does not appear to be a valid e-mail";
		return;
	}

	$sql = "UPDATE systemEvents
			SET organizerEmail = ?
			WHERE eventID = {$eventID}";

	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	$bind = mysqli_stmt_bind_param($stmt, "s", $email);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);

	$_SESSION['alertMessages']['userAlerts'][] = "Contact E-mail updated";
}


/******************************************************************************/

function updateDisplaySettings(){
	
	$eventID = $_SESSION['eventID'];
	if($eventID == null){ return;}
	
	foreach($_POST['displaySettings'] as $field => $value){
		$sql = "UPDATE eventDefaults
				SET $field = '$value'
				WHERE eventID = {$eventID}";

		mysqlQuery($sql, SEND);
		
	}
	
}

/******************************************************************************/

function updateEventDefaults(){
	
	if(USER_TYPE < USER_ADMIN){return;}
	
	$eventID = $_SESSION['eventID'];
	if($eventID == null){return;}
	
	$normPoolSize = $_POST['normalizePoolSize'];
	$maxDoubles = $_POST['maxDoubleHits'];
	$color1ID = $_POST['color1ID'];
	$color2ID = $_POST['color2ID'];
	$maxPoolSize = $_POST['maxPoolSize'];
	$allowTies = $_POST['allowTies'];
	$useTimer = $_POST['useTimer'];
	$controlPoint = $_POST['controlPoint'];
	
	$sql = "DELETE FROM eventDefaults
			WHERE eventID = {$eventID}";
	mysqlQuery($sql, SEND);
	
	$sql = "INSERT INTO eventDefaults
			(eventID, color1ID, color2ID, maxPoolSize, useControlPoint,
			maxDoubleHits, normalizePoolSize, allowTies, useTimer)
			VALUES
			($eventID, $color1ID, $color2ID, $maxPoolSize, $controlPoint,
			$maxDoubles, $normPoolSize, $allowTies, $useTimer)";
	mysqlQuery($sql, SEND);

	$_SESSION['alertMessages']['userAlerts'][] = "Event Defaults Updated";
}


/******************************************************************************/

function updateEventPasswords(){
	if(USER_TYPE < USER_ADMIN){return;}
	
	$eventID = $_SESSION['eventID'];
	if($eventID == null){return;}

	// Check if it is valid to change
	$isAdmin = checkPassword($_POST['passwordVerification'], USER_ADMIN, $eventID);
	$isSuperAdmin = checkPassword($_POST['passwordVerification'], USER_SUPER_ADMIN, $eventID);
	
	if(!$isAdmin && !$isSuperAdmin){
		$_SESSION['alertMessages']['userErrors'][] = "Incorrect Password<BR>Passwords not changed";
		return;	
	}
	
	// Update Database
	$staffPass = password_hash($_POST[USER_STAFF], PASSWORD_DEFAULT);
	$adminPass = password_hash($_POST[USER_ADMIN], PASSWORD_DEFAULT);
	
	if($_POST[USER_STAFF] != null){
		$sql = "UPDATE systemEvents
				SET USER_STAFF = '{$staffPass}'
				WHERE eventID = {$eventID}";
		mysqlQuery($sql, SEND);
	}
		
	if($_POST[USER_ADMIN] != null){
		$sql = "UPDATE systemEvents
				SET USER_ADMIN = '{$adminPass}'
				WHERE eventID = {$eventID}";
		mysqlQuery($sql, SEND);
	}
	
	$_SESSION['alertMessages']['userAlerts'][] = "Passwords Updated";
}

/******************************************************************************/

function updateEventTournaments(){
	if(USER_TYPE < USER_ADMIN){return;}
	
	$eventID = $_SESSION['eventID'];
	if($eventID == null){return;}

	$defaults = getEventDefaults($eventID);
	$info = $_POST['updateTournament'];

	switch($_POST['updateType']){
	// Add a new tournament
		case 'add':
			
			if(!isset($info['tournamentRankingID'])){ $info['tournamentRankingID'] = 'null'; }
			if(!isset($info['doubleTypeID'])){ $info['doubleTypeID'] = 'null'; }
			if($info['maximumExchanges'] == ''){$info['maximumExchanges'] = 'null';}
			if(!isset($info['isNotNetScore'])){$info['isNotNetScore'] = 0;}
			if($info['basePointValue'] == ''){$info['basePointValue'] = 0;}

			if(isset($info['color1ID'])){ $defaults['color1ID'] = $info['color1ID'];}
			if(isset($info['color2ID'])){ $defaults['color2ID'] = $info['color2ID'];}
			
			if($info['isReverseScore'] > REVERSE_SCORE_NO){
				if($info['doubleTypeID'] == DEDUCTIVE_AFTERBLOW){
					$info['doubleTypeID'] = FULL_AFTERBLOW;
					$info['isNotNetScore'] = 1;
					$_SESSION['alertMessages']['userErrors'][] = "Reverse Score mode is not compatable
					 with deductive afterblow scoring. 
					 <BR>Afterblow type has been changed to <u>Full Afterblow</u>
					 with <u>Use Net Points</u> option set to <i>No</i>.";
				} elseif ($info['doubleTypeID'] == FULL_AFTERBLOW){
					$info['isNotNetScore'] = 1;
					$_SESSION['alertMessages']['userErrors'][] = "Reverse Score mode only functions without
					<u>Use Net Points</u> enabled. <BR><u>Use Net Points</u> has been set to <i>No</i>.";
				}

			}

			$sql = "INSERT INTO eventTournaments (
					eventID, tournamentWeaponID, tournamentPrefixID, 
					tournamentGenderID,	tournamentMaterialID, doubleTypeID,
					normalizePoolSize, color1ID, color2ID, maxPoolSize, 
					maxDoubleHits, tournamentElimID, tournamentRankingID,
					maximumExchanges, isCuttingQual, useTimer, useControlPoint,
					isNotNetScore, basePointValue, isPrivate, isReverseScore
					) VALUES (
					{$eventID},
					{$info['tournamentWeaponID']},
					{$info['tournamentPrefixID']},
					{$info['tournamentGenderID']},
					{$info['tournamentMaterialID']},
					{$info['doubleTypeID']},
					{$info['normalizePoolSize']},
					{$info['color1ID']},
					{$info['color2ID']},
					{$info['maxPoolSize']},
					{$info['maxDoubleHits']},
					{$info['tournamentElimID']},
					{$info['tournamentRankingID']},
					{$info['maximumExchanges']},
					{$info['isCuttingQual']},
					{$info['useTimer']},
					{$info['useControlPoint']},
					{$info['isNotNetScore']},
					{$info['basePointValue']},
					{$info['isPrivate']},
					{$info['isReverseScore']}
					)";

			mysqlQuery($sql, SEND);
			$tournamentID = mysqli_insert_id($GLOBALS["___mysqli_ston"]);
			
			$newName = getTournamentName($tournamentID);
			$_SESSION['alertMessages']['userAlerts'][] = "Created tournament: '{$newName}'";
			
			$_SESSION['tournamentID'] = $tournamentID;
			break;
	
	// Update an existing tournament		
		case 'update':
		
			$tournamentID = $_POST['modifyTournamentID'];
			$_SESSION['tournamentID'] = $tournamentID;
			$_SESSION['updatePoolStandings'][$tournamentID] = ALL_GROUP_SETS;	

			if($info['isReverseScore'] > REVERSE_SCORE_NO){
				if($info['doubleTypeID'] == DEDUCTIVE_AFTERBLOW){
					$info['doubleTypeID'] = FULL_AFTERBLOW;
					$info['isNotNetScore'] = 1;
					$_SESSION['alertMessages']['userErrors'][] = "Reverse sScore mode is not compatable
					 with deductive afterblow scoring. 
					 <BR>Afterblow type has been changed to <u>Full Afterblow</u>
					 with <u>Use Net Points</u> option set to <i>No</i>.";
				} elseif ($info['doubleTypeID'] == FULL_AFTERBLOW && $info['isNotNetScore'] == 0){
					$info['isNotNetScore'] = 1;
					$_SESSION['alertMessages']['userErrors'][] = "Reverse Score mode only functions without
					<u>Use Net Points</u> enabled. <BR><u>Use Net Points</u> has been set to <i>No</i>.";
				}

			}


		// Delete groups if the elim type has changed		
			$elimTypeID = $info['tournamentElimID'];
			switch ($elimTypeID){
				case POOL_BRACKET:
					$whereStatement = "AND (groupType != 'pool' OR groupSet > 1) AND groupType != 'elim'";
					break;
				case SCORED_EVENT:
					$whereStatement = "AND groupType != 'round'";
					break;
				case POOL_SETS:	
					$whereStatement = "AND groupType != 'pool'";
					break;
				case DIRECT_BRACKET:
					$whereStatement = "AND groupType != 'elim'";
				default:
					break;
			}
			
			$sql = "DELETE FROM eventGroups
					WHERE tournamentID = {$tournamentID}
					{$whereStatement}";
					
			mysqlQuery($sql, SEND);
			
		// Construct SQL statement to do all updates

			$sql = "UPDATE eventTournaments SET ";
			foreach($info as $field => $data){
				if($data == null){continue;}
				$sql .= "{$field} = {$data}, ";
			}
			$sql = rtrim($sql,', \t\n');
			$sql .= " WHERE tournamentID = {$tournamentID}";
			mysqlQuery($sql, SEND);
			

			if($elimTypeID != POOL_SETS && $elimTypeID != SCORED_EVENT){
				$_SESSION['groupSet'] = 1;
				$sql = "UPDATE eventTournaments SET numGroupSets = 1 WHERE tournamentID = {$tournamentID}";
				mysqlQuery($sql, SEND);
			}

			$sql = "SELECT matchID
					FROM eventMatches
					INNER JOIN eventGroups USING(groupID)
					WHERE tournamentID = {$tournamentID}";
			$matchList = mysqlQuery($sql, SINGLES);

			foreach($matchList as $match){
				$matchInfo = getMatchInfo($match);
				updateMatch($matchInfo);
			}

			$name = getTournamentName($tournamentID);
			$_SESSION['alertMessages']['userAlerts'][] = "{$name} Updated";
			
			break;
		default:
			break;
	}
	
	// Update total tournament counts across all events
	$sql = "SELECT tournamentWeaponID
			FROM eventTournaments";
	$res = mysqlQuery($sql, ASSOC);

	foreach($res as $data){
		$ID = $data['tournamentWeaponID'];
		if(!isset($tournamentInstances[$ID])){
			$tournamentInstances[$ID] = 0;
		}
		$tournamentInstances[$ID] += 1;
	}
	
	foreach($tournamentInstances as $ID => $number){
		if($number == null){$number = 0;}
		$sql = "UPDATE systemTournaments
				SET numberOfInstances = $number
				WHERE tournamentTypeID = $ID";
		mysqlQuery($sql, SEND);
	}
	
	// Update number of instances for ranking algorthms
	$sql = "SELECT tournamentRankingID, COUNT(1) AS numInstances
			FROM eventTournaments
			GROUP BY tournamentRankingID";
	$res = mysqlQuery($sql, ASSOC);
	
	foreach($res as $rankingType){
		$ID = $rankingType['tournamentRankingID'];
		$num = $rankingType['numInstances'];
		if($ID == null){ 
			continue;
		}
		$sql = "UPDATE systemRankings
				SET numberOfInstances = {$num}
				WHERE tournamentRankingID = {$ID}";
		mysqlQuery($sql, SEND);
	}
	
	
}

/******************************************************************************/

function deleteEventTournament(){
	
// Delete an existing tournament		

	$tournamentID = $_POST['deleteTournamentID'];
	$sql = "DELETE FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);
			
}

/******************************************************************************/

function updateExistingSchool(){
	if(USER_TYPE < USER_ADMIN){return;}
	$schoolID = $_POST['schoolID'];
	
	if(isset($_POST['cancelUpdate'])){
		return;
	}
		
	if(isset($_POST['enableEditing'])){
		$_SESSION['editSchoolID'] = $schoolID;
		return;
	}
	
	$schoolFullName = $_POST['schoolFullName'];
	$schoolShortName = $_POST['schoolShortName'];
	$schoolAbreviation = $_POST['schoolAbreviation'];
	$schoolBranch = $_POST['schoolBranch'];
	$schoolCountry = $_POST['schoolCountry'];
	$schoolProvince = $_POST['schoolProvince'];
	$schoolCity = $_POST['schoolCity'];
	
	$sql = "UPDATE systemSchools SET
			schoolFullName = ?,
			schoolShortName = ?,
			schoolAbreviation = ?,
			schoolBranch = ?,
			schoolCountry = ?,
			schoolProvince = ?,
			schoolCity = ?
			WHERE schoolID = {$schoolID}";

	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	$bind = mysqli_stmt_bind_param($stmt, "sssssss", $schoolFullName, 
			$schoolShortName, $schoolAbreviation, $schoolBranch, 
			$schoolCountry, $schoolProvince, $schoolCity);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);

}

/******************************************************************************/

function updateFinalsBracket(){
	if(USER_TYPE < USER_STAFF){return;}
	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){return;}
	
	$groupID = $_POST['groupID'];
	
	// Clears the Match
	if($_POST['updateBracket'] == 'clearMatches'){
	
		foreach(@(array)$_POST['clearMatch'] as $matchID => $finalists){
		
			$notNull = [];
			$notNull[] = 'groupID';
			$notNull[] = 'bracketPosition';
			$notNull[] = 'bracketLevel';
			
			mysqlSetRecordToDefault('eventMatches',"WHERE matchID = {$matchID}", $notNull);
			
			clearExchanges($matchID,'all');
			
		}
	}
	
	// Adds new fighters to the match
	
	if($_POST['updateBracket'] == 'newFighters'){
		foreach((array)$_POST['newFinalists'] as $matchID => $finalists){
			if(!empty($finalists[1]) && !empty($finalists[2])){
				$sql = "DELETE FROM eventExchanges
						WHERE matchID = {$matchID}";
				mysqlQuery($sql, SEND);
			}
			
			if(!empty($finalists[1])){
				$sql = "UPDATE eventMatches
						SET fighter1ID = {$finalists[1]}
						WHERE matchID = {$matchID}";
				mysqlQuery($sql, SEND);
			}
			
			if(!empty($finalists[2])){
				$sql = "UPDATE eventMatches
						SET fighter2ID = {$finalists[2]}
						WHERE matchID = {$matchID}";
				mysqlQuery($sql, SEND);	
			}

		}
	}
	
}

/******************************************************************************/

function updateHemaRatingsInfo($fighters){

	if(USER_TYPE < USER_SUPER_ADMIN){ return;}


	foreach($fighters as $systemRosterID => $fighter){
		$HemaRatingsID = (int)$fighter['HemaRatingsID'];
		if($HemaRatingsID == 0){
			$HemaRatingsID = "NULL";
		}

		$sql = "SELECT systemRosterID
				FROM systemRoster
				WHERE HemaRatingsID = {$HemaRatingsID}
				AND systemRosterID != {$systemRosterID}";
		$duplicates = mysqlQuery($sql, ASSOC);

		if(count($duplicates) == 0){

			$sql = "UPDATE systemRoster
					SET HemaRatingsID = {$HemaRatingsID}, 
					firstName = ?,
					lastName = ?
					WHERE systemRosterID = ?";

			$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
			// "s" means the database expects a string
			$bind = mysqli_stmt_bind_param($stmt, "ssi", 
				$fighter['firstName'], $fighter['lastName'],$systemRosterID);
			$exec = mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);

			

		} else {
			// If someone with that Rating ID exists in the system.
			$errStr = "Can't add ".getFighterNameSystem($systemRosterID);
			$errStr .= "($systemRosterID)";
			$errStr .= " with HEMA Rating ID: {$HemaRatingsID}<BR>";

			$errStr .= "Conflicts with:";
			foreach($duplicates as $duplicate){
				$errStr .= getFighterNameSystem($duplicate['systemRosterID']);
				$errStr .= "({$duplicate['systemRosterID']})<BR>";
			}
			setAlert(USER_ALERT,$errStr);
		}

	}

	setAlert(USER_ALERT,"HEMA Ratings IDs updated");
}

/******************************************************************************/

function updateIgnoredFighters(){
// If a fighter is set to un-ignore it will unignore 
// all matches, even if they were ignored individualy
	
	if(USER_TYPE < USER_ADMIN){return;}
	$tournamentID = $_POST['tournamentID'];
	
	if($tournamentID == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No tournamentID in 'updateIgnoredFighters()'";
		return;
	}
	
	$mode = $_POST['ignoreMode'];
	
// Update groupRosters with ignored fighters 
	foreach((array)$_POST['ignoreFightersOld'] as $rosterID => $value){
		if((int)$value == @$_POST['ignoreFightersNew'][$rosterID]){ continue; }
		
		// Have to step through ignoreFightersOld rather than new because 
		// checkboxes only post data if checked
		$changedIDs[$rosterID] = true;
		$value = @$_POST['ignoreFightersNew'][$rosterID];
		
		if($mode == 'groupSet'){
			if($value != 0){ // Turn on
				$groupSet = $value;

				$sql = "UPDATE eventGroupRoster, eventGroups
						SET ignoreFighter = 1
						WHERE eventGroupRoster.groupID = eventGroups.groupID
						AND tournamentID = {$tournamentID}
						AND rosterID = {$rosterID}
						AND groupSet >= {$groupSet}";
				mysqlQuery($sql, SEND);
				
				$sql = "UPDATE eventGroupRoster, eventGroups
						SET ignoreFighter = 0
						WHERE eventGroupRoster.groupID = eventGroups.groupID
						AND tournamentID = {$tournamentID}
						AND rosterID = {$rosterID}
						AND groupSet < {$groupSet}";
				mysqlQuery($sql, SEND);
				
			} else { // Turn off
				$sql = "UPDATE eventGroupRoster, eventGroups
						SET ignoreFighter = 0
						WHERE eventGroupRoster.groupID = eventGroups.groupID
						AND tournamentID = {$tournamentID}
						AND rosterID = {$rosterID}";
				mysqlQuery($sql, SEND);
			}
		}
	}

// Step through all matches and see if they have to be ignored/un-ignored
	$sql = "SELECT matchID, fighter1ID, fighter2ID, groupSet
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}";
	$matchList = mysqlQuery($sql, ASSOC);
	
	$ignores = getIgnores($tournamentID);

	foreach((array)$matchList as $match){
		$fighter1ID = $match['fighter1ID'];
		$fighter2ID = $match['fighter2ID'];
		
		if(!isset($changedIDs[$fighter1ID]) && !isset($changedIDs[$fighter2ID])){ continue; }
		
		$groupSet = $match['groupSet'];
		$matchID = $match['matchID'];
	
		if(isset($ignores[$fighter1ID]) && $ignores[$fighter1ID] <= $groupSet){
			$status = 1;
		} elseif(isset($ignores[$fighter2ID]) && $ignores[$fighter2ID] <= $groupSet){
			$status = 1;
		} else {
			$status = 0;
		}
		
		$sql = "UPDATE eventMatches
				SET ignoreMatch = {$status}
				WHERE matchID = {$matchID}";
		mysqlQuery($sql, SEND);
	}
	
// Update which fighters are ignored from finals
	foreach((array)$_POST['finalsIgnores'] as $rosterID => $bool){
		$sql = "UPDATE eventTournamentRoster
				SET removeFromFinals = {$bool}
				WHERE rosterID = {$rosterID}
				AND tournamentID = {$tournamentID}";
		mysqlQuery($sql, SEND);
	}

	$_SESSION['updatePoolStandings'][$tournamentID] = ALL_GROUP_SETS;
	
	$_SESSION['alertMessages']['userAlerts'][] = "Updated";
	
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	
	return;
// Update groupRosters with ignored fighters 
	foreach((array)$_POST['poolIgnores'] as $rosterID => $info){
		if((int)$info['old'] == (int)$info['new']){ continue;}
		$changedIDs[$rosterID] = true;

		$state = $info['new'];
		if($state){
			
			$sql = "UPDATE eventGroupRoster, eventGroups
					SET ignoreFighter = 1
					WHERE eventGroupRoster.groupID = eventGroups.groupID
					AND tournamentID = {$tournamentID}
					AND rosterID = {$rosterID}
					AND groupSet >= {$groupSet}";
		} else {
			
			$sql = "UPDATE eventGroupRoster, eventGroups
					SET ignoreFighter = 0
					WHERE eventGroupRoster.groupID = eventGroups.groupID
					AND tournamentID = {$tournamentID}
					AND rosterID = {$rosterID}";
		}
		mysqlQuery($sql, SEND);
	}
	
	$sql = "SELECT matchID, fighter1ID, fighter2ID, groupSet
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}";
	$matchList = mysqlQuery($sql, ASSOC);
	
	$ignores = getIgnores($tournamentID);

// Check which matches need to be ignored/un-ignored	
	foreach((array)$matchList as $match){
		$fighter1ID = $match['fighter1ID'];
		$fighter2ID = $match['fighter2ID'];
		
		if(!isset($changedIDs[$fighter1ID]) && !isset($changedIDs[$fighter2ID])){ continue; }
		
		$groupSet = $match['groupSet'];
		$matchID = $match['matchID'];
		
		if((isset($ignores[$fighter1ID]) && $ignores[$fighter1ID] <= $groupSet) ||
			(isset($ignores[$fighter2ID]) && $ignores[$fighter2ID] <= $groupSet)){
			$status = 1;
		} else {
			$status = 0;
		}
		
		$sql = "UPDATE eventMatches
				SET ignoreMatch = {$status}
				WHERE matchID = {$matchID}";
		mysqlQuery($sql, SEND);
	}

// Update which fighters are ignored from finals

	foreach((array)$_POST['finalsIgnores'] as $rosterID => $bool){
		$sql = "UPDATE eventTournamentRoster
				SET removeFromFinals = {$bool}
				WHERE rosterID = {$rosterID}
				AND tournamentID = {$tournamentID}";
		mysqlQuery($sql, SEND);
	}

	$_SESSION['updatePoolStandings'][$tournamentID] = ALL_GROUP_SETS;
	
	$_SESSION['alertMessages']['userAlerts'][] = "Updated";
}

/******************************************************************************/

function updateLivestreamInfo(){
	
	$eventID = $_SESSION['eventID'];
	if($eventID == null){ return; }
	
	$chanelName = $_POST['chanelName'];
	$platform = $_POST['inputPlatform'];
	switch($platform){
		case 'twitch':
			break;
		case 'youtube':
		case 'link':
			/*$url = $chanelName;
			$headers = @get_headers($url);
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_NOBODY, true);
			$result = curl_exec($curl);
			
			if ($result !== false) {
				
				$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  
				if ($statusCode != 404 && filter_var($chanelName, FILTER_VALIDATE_URL) !== false){
					
					$exists = true;
				} 
			} 

			if($exists != true){
				$_SESSION['alertMessages']['userErrors'][] = "<span class='red-text'>Invalid url</span>. 
									Make sure you include the https://";
				return;
			}*/
			break;
		default:
			$_SESSION['alertMessages']['userErrors'][] = "<p><span class='red-text'>Invalid platform.</span> 
					Not updated</p>";
			return;
	}
	
	$useOverlay = $_POST['useOverlay'];
	
	$sql = "SELECT COUNT(*) AS numEntries
			FROM eventLivestreams
			WHERE eventID = {$eventID}";
	
	if(mysqlQuery($sql, SINGLE, 'numEntries') == 0){
	// No entry exists
	
		$sql = "INSERT INTO eventLivestreams
				(eventID, chanelName, platform, useOverlay)
				VALUES
				(?, ?, ?, ?)";
			
		$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
		// "s" means the database expects a string
		$bind = mysqli_stmt_bind_param($stmt, "issi", $eventID, $chanelName, $platform, $useOverlay);
		$exec = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	} else {
		$sql = "UPDATE eventLivestreams
				SET chanelName = ?, platform = ?, useOverlay = ?
				WHERE eventID = ?";
		$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
		// "s" means the database expects a string
		$bind = mysqli_stmt_bind_param($stmt, "ssii", $chanelName, $platform, $useOverlay, $eventID);
		$exec = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	}
	
	$_SESSION['alertMessages']['userAlerts'][] = "Updated";		

}

/******************************************************************************/

function updateMatch($matchInfo){
// updates the information pertaining to a match
// fighterIDs, score, ect...
	if(USER_TYPE < USER_STAFF){return;}

	$matchID = $matchInfo['matchID'];
	$tournamentID = $matchInfo['tournamentID'];
	
	$doubleTypes = getDoubleTypes($tournamentID);

	$sql = "SELECT scoringID, scoreValue, scoreDeduction, exchangeType
			FROM eventExchanges
			WHERE matchID = {$matchID}
			AND (scoreValue != 0 OR exchangeType = 'scored' OR exchangeType = 'noQuality' OR exchangeType = 'noExchange' OR exchangeType = 'double')";
	$result = mysqlQuery($sql, ASSOC);
	
	if(count($result) == 0){
		$fighter1Score = "NULL";
		$fighter2Score = "NULL";
	} else {

		$fighter1Score = 0;
		$fighter2Score = 0;
		$reverseScore = isReverseScore($matchInfo['tournamentID']);

		foreach($result as $exchange){
			if($exchange['scoringID'] == $matchInfo['fighter1ID']){

				$fighter1Score += $exchange['scoreValue'];
				if($doubleTypes['isNotNetScore'] == 1
					&& $doubleTypes['afterblowType'] == 'full') {
					$fighter2Score += $exchange['scoreDeduction'];
				} else {
					$fighter1Score -= $exchange['scoreDeduction'];
				}

				if($exchange['exchangeType'] == 'penalty'){
					if($reverseScore == REVERSE_SCORE_INJURY){
						$fighter1Score -= $exchange['scoreValue'];
						$fighter2Score -= $exchange['scoreValue'];
					} elseif ($reverseScore == REVERSE_SCORE_GOLF){
						$fighter1Score -= $exchange['scoreValue'];
						$fighter2Score += $exchange['scoreValue'];
					}
					
				}

			} else if($exchange['scoringID'] == $matchInfo['fighter2ID']){

				$fighter2Score += $exchange['scoreValue'];
				if($doubleTypes['isNotNetScore'] == 1
					&& $doubleTypes['afterblowType'] == 'full') {

					$fighter1Score += $exchange['scoreDeduction'];
				} else {
					$fighter2Score -= $exchange['scoreDeduction'];
				}

				if($exchange['exchangeType'] == 'penalty'){
					if($reverseScore == REVERSE_SCORE_INJURY){
						$fighter1Score -= $exchange['scoreValue'];
						$fighter2Score -= $exchange['scoreValue'];
					} elseif ($reverseScore == REVERSE_SCORE_GOLF){
						$fighter1Score += $exchange['scoreValue'];
						$fighter2Score -= $exchange['scoreValue'];
					}
					
				}


			}


		}

		
		if($reverseScore == REVERSE_SCORE_GOLF){
			$temp = $fighter1Score;
			$fighter1Score = $fighter2Score;
			$fighter2Score = $temp;
		} elseif($reverseScore == REVERSE_SCORE_INJURY){
			$temp = $fighter1Score;
			$fighter1Score = -$fighter2Score;
			$fighter2Score = -$temp;

			if(count($result) > 0){
				$basePointValue = getBasePointValue($tournamentID, null);
				$fighter1Score += $basePointValue;
				$fighter2Score += $basePointValue;
			}
		}
	}


show($fighter1Score);
	$sql = "UPDATE eventMatches
			SET fighter1Score = {$fighter1Score},
			fighter2Score = {$fighter2Score}
			WHERE matchID = {$matchID}";
	mysqlQuery($sql, SEND);
	
	
}

/******************************************************************************/

function updateRoundMatchList($tournamentID = null){
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	
	if(USER_TYPE < USER_STAFF){return;}
	$rounds = getRounds($tournamentID);
	
	foreach((array)$rounds as $num => $round){
		$name = $round['groupName'];
		$groupID = $round['groupID'];
		$groupSet = $round['groupSet'];
		
		$roundRoster = getPoolRosters($_SESSION['tournamentID'], $groupSet);
		$roundRoster = $roundRoster[$groupID];
		
		$i = 0;

		$goodMatchesInRound[$groupID] = [];
		
		foreach((array)$roundRoster as $positionNumber => $fighter){
			
			$rosterID = $fighter['rosterID'];
			$i++;
			
			$sql = "SELECT matchID
					FROM eventMatches
					WHERE fighter1ID = {$rosterID}
					AND groupID = {$groupID}";
					
			$matchID = mysqlQuery($sql, SINGLE, 'matchID');
			
			if($matchID != null){
				$goodMatchesInRound[$groupID][] = $matchID;
				
				if($positionNumber != $i){ // if out of order
					$sql = "UPDATE eventGroupRoster
							SET poolPosition = {$i}
							WHERE rosterID = {$rosterID}
							AND groupID = {$groupID}";
					mysqlQuery($sql, SEND);
				}
				continue;
			}
			
			//Create a new match
			$sql = "INSERT INTO eventMatches
					(groupID, matchNumber, fighter1ID, fighter2ID)
					VALUES
					({$groupID}, {$i}, {$rosterID}, {$rosterID})";
			$matchID = mysqlQuery($sql, INDEX);
				
			$goodMatchesInRound[$groupID][] = $matchID;
			
		}

		$whereStatement = '';
		foreach($goodMatchesInRound[$groupID] as $matchID){
			$whereStatement .= "AND matchID != {$matchID} ";
		}

		$sql = "DELETE FROM eventMatches
				WHERE groupID = {$groupID}
				{$whereStatement}";

		mysqlQuery($sql, SEND);

	}
	

}

/******************************************************************************/

function updatePoolMatchList($ID = null, $type = null){

	if(USER_TYPE < USER_STAFF){return;}
	
	switch($type){
		case 'group':
		case 'pool':
			$type = 'group';
			$groupID = $ID;
			if($groupID == null){return;}
			break;
		case 'tournament':
			$type = 'tournament';
			$tournamentID = $ID;
			if($tournamentID == null){return;}
			break;
		default:
			$type = 'event';
			$eventID = $ID;
			if($eventID == null){$eventID = $_SESSION['eventID'];}
			if($eventID == null){return;}
			break;
	}

	if($type == 'event'){
		$tournaments = getEventTournaments();
	} else if($type == 'tournament'){
		$tournaments[] = $tournamentID;
	} else {
		$tournaments[] = null;
	}

	foreach((array)$tournaments as $tournamentID){
		if($type == 'event'){
			if(!isPools($tournamentID)){continue;}
		}
		
		if($type == 'event' || $type == 'tournament'){
			$pools = getPools($tournamentID, 'all');
		} else {
			$pools = getGroupInfo($groupID);
		}
			
		$poolRosters = getPoolRosters($tournamentID, 'all');

		$goodMatchesInPool = null;
		
		foreach($pools as $pool){
			
			$groupID = $pool['groupID'];
			if(!isset($goodMatchesInPool[$groupID])){
				$goodMatchesInPool[$groupID] = [];
			}
			if(isset($poolRosters[$groupID])){
				$poolRoster = $poolRosters[$groupID];
			} else {
				$poolRoster = [];
			}

			$numFightersInPool = count($poolRoster);
			$sql = "UPDATE eventGroups
						SET numFighters = {$numFightersInPool}
						WHERE groupID = {$groupID}";
			mysqlQuery($sql, SEND);
			$matchOrder = getPoolMatchOrder($groupID, $poolRoster);
		
			foreach((array)$matchOrder as $matchNumber => $matchInfo){
				$fighter1ID = $matchInfo['fighter1ID'];
				$fighter2ID = $matchInfo['fighter2ID'];
				
			//Check if match already exists
				$sql = "SELECT matchID, matchNumber, winnerID
						FROM eventMatches
						WHERE groupID = {$groupID}
						AND fighter1ID = {$fighter1ID}
						AND fighter2ID = {$fighter2ID}";
				$matchAlreadyExists = mysqlQuery($sql, SINGLE);
				
				if($matchAlreadyExists != null){
					$matchID = $matchAlreadyExists['matchID'];
					if($matchAlreadyExists['matchNumber'] != $matchNumber){
						$sql = "UPDATE eventMatches
								SET matchNumber = $matchNumber
								WHERE matchID = {$matchID}";
						mysqlQuery($sql, SEND);
					}
					
					$sql = "SELECT scoringID
							FROM eventExchanges
							WHERE matchID = {$matchID}
							AND exchangeType = 'winner'";
					$result = mysqlQuery($sql, SINGLE);
					
					if($result['scoringID'] != $matchAlreadyExists['winnerID']){
						$sql = "UPDATE eventMatches
								SET winnerID = {$result['scoringID']}
								WHERE matchID = {$matchID}";
						mysqlQuery($sql, SEND);
					}
					
					$goodMatchesInPool[$groupID][] = $matchID;
					continue;
				}
				
			//Check if the match exists in a similar capacity
				$sql = "SELECT matchID, matchNumber, winnerID, fighter1Score, fighter2Score, reversedColors
						FROM eventMatches
						WHERE groupID = {$groupID}
						AND fighter1ID = {$fighter2ID}
						AND fighter2ID = {$fighter1ID}";
				$backwardsMatch = mysqlQuery($sql, SINGLE);
				
				$matchID = $backwardsMatch['matchID'];
				
				if($backwardsMatch != null){
					if($backwardsMatch['reversedColors'] == 1){
					// Doesn't correct the match if it should be reversed
						$goodMatchesInPool[$groupID][] = $matchID;
						continue;
					}
					
					if($backwardsMatch['winnerID'] == $fighter1ID){
						$winnerID = $fighter1ID;
					} else if($backwardsMatch['winnerID'] == $fighter2ID){
						$winnerID = $fighter2ID;
					} else {
						$winnerID = "null";
					}
					
					$fighter1Score = '';
					$fighter2Score = '';
					if($backwardsMatch['fighter1Score'] != null){
						$fighter2Score = ", fighter2Score = {$backwardsMatch['fighter1Score']}";
					}
					if($backwardsMatch['fighter2Score'] != null){
						$fighter1Score = ", fighter1Score = {$backwardsMatch['fighter2Score']}";
					}
						
					$sql = "UPDATE eventMatches
							SET matchNumber = $matchNumber,
							fighter1ID = {$fighter1ID},
							fighter2ID = {$fighter2ID},
							winnerID = {$winnerID}
							{$fighter1Score}
							{$fighter2Score}
							WHERE matchID = {$matchID}";
					mysqlQuery($sql, INDEX);
					$goodMatchesInPool[$groupID][] = $matchID;
					continue;
				}
				
				
			//Create a new match
				$sql = "INSERT INTO eventMatches
						(groupID, matchNumber, fighter1ID, fighter2ID)
						VALUES
						({$groupID}, {$matchNumber}, {$fighter1ID}, {$fighter2ID})";
				$matchID = mysqlQuery($sql, INDEX);
				$goodMatchesInPool[$groupID][] = $matchID;
			}
			
			$whereStatement = '';
			foreach((array)$goodMatchesInPool[$groupID] as $matchID){
				$whereStatement .= "AND matchID != {$matchID} ";
			}
			
			$sql = "DELETE FROM eventMatches
					WHERE groupID = {$groupID}
					{$whereStatement}";
			mysqlQuery($sql, SEND);

		}
	}

	
}

/******************************************************************************/

function updatePoolSets(){

	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){return;}
	
	
// Cumulative sets
	$sql = "DELETE FROM eventAttributes
			WHERE tournamentID = {$tournamentID}
			AND attributeType = 'cumulative'";
	mysqlQuery($sql, SEND);
	
	foreach((array)$_POST['cumulativeSet'] as $groupSet => $bool){
		if($groupSet > $_POST['numPoolSets']){break;}
		$sql = "INSERT INTO eventAttributes
				(tournamentID, attributeType, attributeGroupSet, attributeBool) 
				VALUES 
				({$tournamentID}, 'cumulative', {$groupSet}, {$bool})";
		mysqlQuery($sql, SEND);
		$_SESSION['updatePoolStandings'][$tournamentID] = ALL_GROUP_SETS;	
	}

// Set normalization
	$sql = "DELETE FROM eventAttributes
			WHERE tournamentID = {$tournamentID}
			AND attributeType = 'normalization'";
	mysqlQuery($sql, SEND);
	
	foreach((array)$_POST['normalizeSet'] as $groupSet => $normalization){
		if($normalization == ''){ continue; }
		if($groupSet > $_POST['numPoolSets']){break;}
		
		$sql = "INSERT INTO eventAttributes
				(tournamentID, attributeType, attributeGroupSet, attributeValue) 
				VALUES 
				({$tournamentID}, 'normalization', {$groupSet}, {$normalization})";
		mysqlQuery($sql, SEND);
		$_SESSION['updatePoolStandings'][$tournamentID] = ALL_GROUP_SETS;	
	}
	
// Change number of pool sets
	if(isset($_POST['numPoolSets'])){
		$sql = "UPDATE eventTournaments
				SET numGroupSets = {$_POST['numPoolSets']}
				WHERE tournamentID = {$tournamentID}";
		mysqlQuery($sql, SEND);
		
		if($_SESSION['groupSet'] > $_POST['numPoolSets']){
			$_SESSION['groupSet'] = 1;
		}
		
		$sql = "DELETE FROM eventGroups
				WHERE tournamentID = {$tournamentID}
				AND groupSet > {$_POST['numPoolSets']}";
		mysqlQuery($sql, SEND);
		
		$sql = "DELETE FROM eventAttributes
				WHERE tournamentID = {$tournamentID}
				AND attributeGroupSet > {$_POST['numPoolSets']}";
		mysqlQuery($sql, SEND);
	}
	
// Set names
	renameGroups($_POST['numPoolSets']);
		
}

/******************************************************************************/

function updateStageOptions(){
	if(USER_TYPE < USER_ADMIN){ return;}
	
	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){ return; }
	
// Base point values
	$sql = "DELETE FROM eventAttributes
			WHERE tournamentID = {$tournamentID}
			AND attributeType = 'basePointValue'";
	mysqlQuery($sql, SEND);
	
	foreach($_POST['baseScore'] as $groupSet => $basePointValue){
		if($basePointValue == ''){ continue; }
		$sql = "INSERT INTO eventAttributes
				(tournamentID, attributeType, attributeGroupSet, attributeValue)
				VALUES
				({$tournamentID}, 'basePointValue', {$groupSet}, {$basePointValue})";
		mysqlQuery($sql, SEND);
	}
		
}

/******************************************************************************/

function updateSystemPasswords(){
	
	if(USER_TYPE < USER_SUPER_ADMIN){
		$_SESSION['alertMessages']['userErrors'][] = "Not Logged in as Software Administrator";
		return;
	}
	
	if(checkPassword($_POST['adminPassword'], USER_SUPER_ADMIN, null) == false){
		$_SESSION['alertMessages']['userErrors'][] = "Incorrect Admin Password";
		return;
	}
	
	$type = $_POST['passwordType'];
	
	if($type == 'USER_SUPER_ADMIN' && $_POST[$type] != $_POST[$type.'_2']){
		$_SESSION['alertMessages']['userErrors'][] = "Unable to update <BR><BR>Two passwords do not match";
		return;
	}
	
	$password = password_hash($_POST[$type], PASSWORD_DEFAULT);

	$sql = "UPDATE systemUsers
			SET password = ?
			WHERE logInType = '{$type}'";
			
	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	$bind = mysqli_stmt_bind_param($stmt, "s", $password);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	
	$_SESSION['alertMessages']['userAlerts'][] = "Password updated sucessfully";
	
}

/******************************************************************************/

function updateTournamentCuttingStandard($tournamentID = null){
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	
	$standard = $_POST['qualStandard'];
	
	if($_POST['useDateType'] == 'absolute'){
		$date = $_POST['qualDate'];
	} elseif($_POST['useDateType'] == 'relative'){
		$eventID = $_SESSION['eventID'];
		if($eventID == null){
			setAlert(USER_ALERT,"Could not determine date range");
			setAlert(SYSTEM,"No eventID in updateTournamentCuttingStandard()");
			return;
		}
		
		$sql = "SELECT eventStartDate
				FROM systemEvents
				WHERE eventID = {$eventID}";
		$tournamentDate = mysqlQuery($sql, SINGLE, 'eventStartDate');
		$date = DateTime::createFromFormat('Y-m-d',$tournamentDate);
		

		if($_POST['qualYears'] != ''){
			$date->modify("-".$_POST['qualYears']." years");
		}
		if($_POST['qualMonths'] != ''){
			$date->modify("-".$_POST['qualMonths']." months");
		}
		if($_POST['qualDays'] != ''){
			$date->modify("-".$_POST['qualDays']." days");
		}
		
		$date = $date->format('Y-m-d');
	} else {
		$_SESSION['alertMessages']['systemErrors'][] =  "Invalid date mode in updateTournamentCuttingStandard()";
		return;
	}
	
	$sql = "DELETE FROM eventCuttingStandards
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);
	
	$sql = "INSERT INTO eventCuttingStandards
			(tournamentID, standardID, date)
			VALUES
			({$tournamentID}, {$standard}, '{$date}')";
	mysqlQuery($sql, SEND);
	
	
}

/******************************************************************************/

function updateTournamentFighterCounts($tournamentID = null){
	
	if(USER_TYPE < USER_STAFF){return;}
	if($tournamentID != null){
		$tournamentList[] = $tournamentID;
	} else {
		$tournamentList = getEventTournaments();
	}
	
	foreach((array)$tournamentList as $tournamentID){
		$sql = "SELECT rosterID 
				FROM eventTournamentRoster
				WHERE tournamentID = {$tournamentID}";
		$result = mysqlQuery($sql, ASSOC);

		$numFighters = count($result);
		
		$sql = "UPDATE eventTournaments
				SET numParticipants = $numFighters
				WHERE tournamentID = {$tournamentID}";
		mysqlQuery($sql, SEND);

	}
}

/******************************************************************************/

function updateNumberOfGroupSets(){
	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){return;}
	
	$num = $_POST['numGroupSets'];
	$sql = "UPDATE eventTournaments SET numGroupSets = {$num}
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);
	
	$sql = "DELETE FROM eventGroups 
			WHERE tournamentID = {$tournamentID}
			AND groupSet > {$num}";
	mysqlQuery($sql, SEND);
	
	$sql = "DELETE FROM eventAttributes
			WHERE tournamentID = {$tournamentID}
			AND attributeGroupSet > {$num}
			AND (attributeType = 'setName' OR attributeType = 'cumulative' )";
	mysqlQuery($sql, SEND);
}

/******************************************************************************/

function updateYouTubeLink($matchID = null){
	if($matchID == null){$matchID = $_SESSION['matchID'];}
	if($matchID == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No matchID in updateYourTubeLink()";
		return;
	}
	
	$url = $_POST['url'];
	
	$sql = "UPDATE eventMatches
			SET YouTubeLink = ?
			WHERE matchID = {$matchID}";
	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	// "s" means the database expects a string
	$bind = mysqli_stmt_bind_param($stmt, "s", $url);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);		
			
}

/******************************************************************************/

function writeTournamentPlacing($rosterID, $tournamentID, $placing, $type = 'final', $low = 'null', $high = 'null'){
	
	if(USER_TYPE < USER_STAFF){return;}
	
	if($rosterID == null || $tournamentID == null || $placing == null){
		$_SESSION['alertMessages']['systemErrors'][] = "Invalid parameters passed in writeTournamentPlacing()";
		return;
	}
		
	$sql = "INSERT INTO eventPlacings
			(tournamentID, rosterID, placing, placeType, lowBound, highBound)
			VALUES
			({$tournamentID},{$rosterID},{$placing},'{$type}',{$low},{$high})";
	mysqlQuery($sql, SEND);
	
}

/******************************************************************************/

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
