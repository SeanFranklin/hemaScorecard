<?php
/*******************************************************************************
	Database Write Functions
	
	Functions for writing to the HEMA Scorecard database
	
*******************************************************************************/


/******************************************************************************/

function deleteFromEvent(){
	
	$eventID = $_SESSION['eventID'];
	if($eventID == null){return;}
	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}
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

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}
	
	if($eventID == null){ $eventID = $_SESSION['eventID']; }
	if($eventID == null){return;}
	
	$status = (int)$_POST['livestreamStatus'];
	
	$sql = "UPDATE eventLivestreams
			SET isLive = {$status}";
	mysqlQuery($sql, SEND);
	
}

/******************************************************************************/

function addAttacksToTournament($tournamentID = null){
	
	if(ALLOW['EVENT_MANAGEMENT'] == false){ return; }
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

function logisticsDeleteScheduleBlocks($blocksToDelete){

	if(ALLOW['EVENT_MANAGEMENT'] != true){
		return;
	}

	if($blocksToDelete == null){
		return;
	}

	foreach($blocksToDelete as $blockID => $bool){
		$sql = "DELETE FROM logisticsScheduleBlocks
				WHERE blockID = {$blockID}";
		mysqlQuery($sql, SEND);
	}

	setAlert(USER_ALERT,"Blocks deleted from schedule");

}

/******************************************************************************/

function logisticsEditScheduleBlock($block){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

	$blockID = (int)$block['blockID'];
	$eventID = (int)$block['eventID'];
	$tournamentID = (int)$block['tournamentID'];
	if($tournamentID == 0){
		$tournamentID = null;
	}
	$blockTypeID = (int)$block['blockTypeID'];

	$dayNum = (int)$block['dayNum'];
	$startTime = 60*(int)$block['startTimeHour'] + (int)$block['startTimeMinute'];
	$endTime = 60*(int)$block['endTimeHour'] + (int)$block['endTimeMinute'];
	$suppressConflicts = (int)$block['suppressConflicts'];
	

	$errTxt = "<BR>Schedule Item not added.";
	if($blockTypeID == SCHEDULE_BLOCK_TOURNAMENT && $tournamentID == 0){
		setAlert(USER_ERROR,"Not a valid tournament.{$errTxt}");
		return;
	}
	if($blockTypeID != SCHEDULE_BLOCK_TOURNAMENT && $block['blockTitle'] == ''){
		setAlert(USER_ERROR,"Not a valid name.{$errTxt}");
		return;
	}
	if(!isset($block['locationIDs'])){
		setAlert(USER_ERROR,"No location provided.{$errTxt}");
		return;
	}

	if($blockID == 0){
		$sql = "INSERT INTO logisticsScheduleBlocks
				(eventID, blockTypeID, dayNum, startTime, endTime)
				VALUES 
				({$eventID}, {$blockTypeID}, {$dayNum}, {$startTime}, {$endTime})";
		mysqlQuery($sql, SEND);

		$opText = 'added';

		$blockID = mysqli_insert_id($GLOBALS["___mysqli_ston"]);
	} else {
		$opText = 'updated';
	}


	// Decide how many shifts need to be created for the schedule block,
	// and when they start and end.
	if($block['numShifts'] > 0){
		$shift = [];
		$blockLength = $endTime - $startTime;

		$shift['endTime'] = (int)$startTime;

		for($shiftNum = 1;$shiftNum<=$block['numShifts'];$shiftNum++){
			$shift['startTime'] = $shift['endTime'];

			if($shiftNum == $block['numShifts']){
				$shift['endTime'] = $endTime;
			} else {
				$shift['endTime'] = $startTime + round($blockLength * (($shiftNum)/$block['numShifts']),0);
			}
			$shiftsToCreate[] = $shift;
		}
	}

	$sql = "UPDATE logisticsScheduleBlocks
			SET eventID = {$eventID}, dayNum = {$dayNum}, startTime = {$startTime}, 
				endTime = {$endTime}, blockTypeID = {$blockTypeID}, 
				suppressConflicts = {$suppressConflicts},
				tournamentID = ?, blockTitle = ?, blockSubtitle = ?, blockDescription = ?,
				blockLink = ?, blockLinkDescription = ?
			WHERE blockID = {$blockID}";
	
	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	// "s" means the database expects a string
	$bind = mysqli_stmt_bind_param($stmt, "isssss", 
			$tournamentID, $block['blockTitle'], 
			$block['blockSubtitle'], $block['blockDescription'],
			$block['blockLink'],$block['blockLinkDescription']);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);


	foreach($block['locationIDs'] as $locationID => $use){

		$use = (bool)$use;
		$locationID = (int)$locationID;

		$sql = "SELECT blockLocationID
				FROM logisticsLocationsBlocks
				WHERE blockID = {$blockID}
				AND locationID = {$locationID}";
		$blockLocationID = (int)mysqlQuery($sql, SINGLE, 'blockLocationID');

		if($blockLocationID == 0 && $use != false){

			$sql = "INSERT INTO logisticsLocationsBlocks
				(blockID, locationID)
				VALUES 
				({$blockID},{$locationID})";
			mysqlQuery($sql,SEND);

		} elseif($blockLocationID != 0 && $use == false) {

			$sql = "DELETE FROM logisticsLocationsBlocks
					WHERE blockLocationID = {$blockLocationID}";
			mysqlQuery($sql,SEND);

		} 

		if($use == true){

			$sql = "SELECT shiftID, startTime, endTime
					FROM logisticsScheduleShifts
					WHERE blockID = {$blockID}
					AND locationID = {$locationID}
					ORDER BY startTime ASC";
			$shiftsAtLocation = mysqlQuery($sql, ASSOC);
			$numExistingShifts = count($shiftsAtLocation);
			$shiftsToLoop = max($numExistingShifts, $block['numShifts']);

			// Loop through to deal with three posibilites:
			// 1) Need to delete shifts that are over the requested amount.
			// 2) Need to update shifts that are bellow existing and requested amounts
			// 3) Insert shifts that are bellow the requested but above the existing amounts
			for($shiftNum = 1; $shiftNum <= $shiftsToLoop; $shiftNum++){
				$sIndex = $shiftNum-1;

				// These might not exist depending on the loop state.
				$shiftID = (int)@$shiftsAtLocation[$sIndex]['shiftID'];
				$shiftStart = (int)@$shiftsToCreate[$sIndex]['startTime']; 
				$shiftEnd = (int)@$shiftsToCreate[$sIndex]['endTime'];

				if($shiftNum > $block['numShifts']){

					$sql = "DELETE FROM logisticsScheduleShifts
							WHERE shiftID = {$shiftID}";
					mysqlQuery($sql, SEND);


				} elseif($shiftNum <= $numExistingShifts){

					$sql = "UPDATE logisticsScheduleShifts
							SET startTime = {$shiftStart}, endTime = {$shiftEnd}
							WHERE shiftID = {$shiftID}";
					mysqlQuery($sql, SEND);

				} else {

					$sql = "INSERT INTO logisticsScheduleShifts
							(blockID, locationID, startTime, endTime)
							VALUES 
							({$blockID}, {$locationID}, {$shiftStart}, {$shiftEnd})";
					mysqlQuery($sql, SEND);

				}
			}

			
		} else {

			$sql = "DELETE FROM logisticsScheduleShifts
					WHERE blockID = {$blockID}
					AND locationID = {$locationID}";
			mysqlQuery($sql, SEND);

		}

	}

	setAlert(USER_ALERT,"Schedule Block {$opText}");

	
}

/******************************************************************************/

function logisticsEditStaffList($staffInfo){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

	$eventID = (int)$staffInfo['eventID'];

	foreach($staffInfo['staffList'] as $staffInfo){

		$rosterID = (int)$staffInfo['rosterID'];
		$staffCompetency = (int)$staffInfo['staffCompetency'];
		$hours = (int)$staffInfo['staffHoursTarget'];

		if(isset($staffInfo['isStaff'])
			&& $staffInfo['isStaff'] == 0){
			$staffCompetency = 0;
			$hours = "NULL";
		}

		// If hours is empty it should be a null value in the table.
		// Zero is a valid int value, so they need to be distinguished.
		if($hours == 0){
			if(strlen($staffInfo['staffHoursTarget']) == 0){
				$hours = "NULL";
			}
		}

		if($rosterID == 0){
			continue;
		}

		$sql = "UPDATE eventRoster
				SET staffCompetency = {$staffCompetency},
				staffHoursTarget = {$hours}
				WHERE eventID = {$eventID}
				AND rosterID = {$rosterID}";
		mySqlQuery($sql, SEND);

	}
}


/******************************************************************************/

function logisticsDeleteStaffList($staffInfo){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

	$eventID = (int)$staffInfo['eventID'];

	foreach($staffInfo['deleteList'] as $rosterID){

		if($rosterID == 0){
			continue;
		}

		$sql = "UPDATE eventRoster
				SET staffCompetency = 0, staffHoursTarget = NULL
				WHERE eventID = {$eventID}
				AND rosterID = {$rosterID}";
		mySqlQuery($sql, SEND);

		$sql = "DELETE staff FROM logisticsStaffShifts AS staff
				LEFT JOIN logisticsScheduleShifts AS shifts ON shifts.shiftID = staff.shiftID
				LEFT JOIN logisticsScheduleBlocks AS blocks ON blocks.blockID = shifts.blockID
				WHERE rosterID = {$rosterID}
				AND eventID = {$eventID}";
		mysqlQuery($sql, SEND);

	}
}

/******************************************************************************/

function logisticsUpdateStaffTemplates($info){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

	foreach($info as $tournamentID => $roleInfo){

		$tournamentID = (int)$tournamentID;

		foreach($roleInfo as $logisticsRoleID => $numStaff){
			$logisticsRoleID = (int)$logisticsRoleID;

			if($numStaff === ''){
				$sql = "DELETE FROM logisticsStaffTemplates 
						WHERE tournamentID = {$tournamentID}
						AND logisticsRoleID = {$logisticsRoleID}";
				mysqlQuery($sql, SEND);
			} else {
				$numStaff = (int)$numStaff;

				$sql = "SELECT staffTemplateID
						FROM logisticsStaffTemplates
						WHERE tournamentID = {$tournamentID}
						AND logisticsRoleID = {$logisticsRoleID}";
				$staffTemplateID = (int)mysqlQuery($sql, SINGLE, 'staffTemplateID');

				if($staffTemplateID != 0){
					$sql = "UPDATE logisticsStaffTemplates
							SET numStaff = {$numStaff}
							WHERE staffTemplateID = {$staffTemplateID}";
					mysqlQuery($sql, SEND);
				} else {
					$sql = "INSERT INTO logisticsStaffTemplates
							(tournamentID, logisticsRoleID, numStaff)
							VALUES 
							({$tournamentID},{$logisticsRoleID},{$numStaff})";
					mysqlQuery($sql, SEND);
				}
			}
		}
	}

}

/******************************************************************************/

function logisticsEditStaffShifts($shiftList, $eventID){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

	$minimumCompetency = logistics_getRoleCompetencies($eventID);
	$overCompetency = [];

	foreach($shiftList as $shiftID => $shift){

		$shiftID = (int)$shiftID;

		foreach($shift as $staffShiftID => $staffShift){

			$staffShiftID = (int)$staffShiftID;
			$rosterID = (int)@$staffShift['rosterID']; // Might not exist. Treat as zero.
			$logisticsRoleID = (int)$staffShift['logisticsRoleID'];
			if($logisticsRoleID == 0){
				setAlert(SYSTEM,"No logisticsRoleID in logisticsEditStaffShifts()");
				continue;
			}

			if(    $minimumCompetency[$logisticsRoleID] != 0
				&& $rosterID != 0){

				$comp = logistics_getStaffCompetency($rosterID);

				if($comp < $minimumCompetency[$logisticsRoleID]){
					$tmp['rosterID'] = $rosterID;
					$tmp['logisticsRoleID'] = $logisticsRoleID;
					$tmp['competency'] = $comp;
					$overCompetency[] = $tmp;
				}
			}

			if($staffShiftID < 0){
				if($rosterID == 0){
					continue;
				}

				$sql = "INSERT INTO logisticsStaffShifts
						(shiftID, rosterID, logisticsRoleID)
						VALUES
						({$shiftID},{$rosterID},{$logisticsRoleID})";
				mySqlQuery($sql, SEND);
			} else {

				if($rosterID == 0){

					$sql = "DELETE FROM logisticsStaffShifts
							WHERE staffShiftID = {$staffShiftID}";
					mySqlQuery($sql, SEND);

				} else {

					$sql = "UPDATE logisticsStaffShifts
							SET shiftID = {$shiftID},
								rosterID = {$rosterID}, 
								logisticsRoleID = {$logisticsRoleID}
							WHERE staffShiftID = {$staffShiftID}";
					mySqlQuery($sql, SEND);

				}
			}

		}
	}

	if($overCompetency != []){

		$str = "<h4>Warning!</h4>";
		foreach($overCompetency as $entry){
			$str .= "<li><u>";
			$str .= getFighterName($entry['rosterID']);
			$str .= "</u> has been assigned to <strong>";
			$str .= logistics_getRoleName($entry['logisticsRoleID']);
			$str .= "</strong> with a competency of <strong>{$entry['competency']}</strong>.</li>";
		}

		setAlert(USER_ALERT,$str);
	}

}

/******************************************************************************/

function logisticsBulkStaffAssign($info){

	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}

	$shiftID = (int)$info['shiftID'];
	$eventID = (int)$info['eventID'];
	$roleID = (int)$info['logisticsRoleID'];

	if($shiftID == 0 && $eventID == 0){
		setAlert(SYSTEM,"Insufficient Info in logisticsBulkStaffAssign()");
		return;
	}


	if($info['type'] == 'staff' || $info['type'] == 'all'){
		if($info['type'] == 'staff'){
			$whereClause = 'AND staffCompetency != 0';
		} else {
			$whereClause = '';
		}

		$sql = "SELECT rosterID
				FROM eventRoster
				WHERE eventID = {$eventID}
				{$whereClause}";
		$listToAdd = mysqlQuery($sql, SINGLES);
	} else {
		$tournamentID = (int)$info['type'];

		$sql = "SELECT rosterID
				FROM eventTournamentRoster
				WHERE tournamentID = {$tournamentID}";
		$listToAdd = mysqlQuery($sql, SINGLES);
	}

	foreach($listToAdd as $rosterID){
		$rosterID = (int)$rosterID;

		$sql = "SELECT staffShiftID
				FROM logisticsStaffShifts
				WHERE shiftID = {$shiftID}
				AND rosterID = {$rosterID}";
		$isAlready = (bool)mysqlQuery($sql, SINGLE, 'staffShiftID');

		if($isAlready == true){
			continue;
		}

		$sql = "INSERT INTO logisticsStaffShifts
				(shiftID, rosterID, logisticsRoleID)
				VALUES
				({$shiftID},{$rosterID},{$roleID})";
		mysqlQuery($sql, SEND);

	}
}

/******************************************************************************/

function addEventParticipantsByID(){
	$eventID = (int)$_SESSION['eventID'];
	if($eventID == 0){return;}
	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}

	$defaults = getEventDefaults($eventID);
		
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

		$staffCompetency = 0;
		$staffHoursTarget = 'NULL';
		if(isset($fighter['staffCompetency'])){
			if($fighter['staffCompetency'] >= 0){
				$staffCompetency = (int)$fighter['staffCompetency'];
				$staffHoursTarget = (int)$defaults['staffHoursTarget'];

			}
		}

	
	// Adds fighter to the event
		$sql = "INSERT INTO eventRoster
				(systemRosterID, eventID, schoolID,staffCompetency, staffHoursTarget)
				VALUES
				({$systemRosterID}, {$eventID}, {$schoolID}, 
				{$staffCompetency}, {$staffHoursTarget})";
		mysqlQuery($sql, SEND);
		$rosterID = mysqli_insert_id($GLOBALS["___mysqli_ston"]);
		
	// Add fighters to tournaments
		if(isset($fighter['tournamentIDs'])){
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

function updateFighterRatings($ratingData){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

	$tournamentID = (int)$ratingData['tournamentID'];

	$updateString = '';
	foreach($ratingData['fighters'] as $id => $r){
		$rating = (int)$r['rating'];
		$subGroup = (int)$r['subGroupNum'];
		$rosterID = (int)$id;

		if($r['rating2'] == null){
			$rating2 = 'NULL';
		} else {
			$rating2 = (int)$r['rating2'];
		}

		$sql = "UPDATE eventTournamentRoster
				SET rating = {$rating}, subGroupNum = {$subGroup}, rating2 = {$rating2}
				WHERE tournamentID = {$tournamentID}
				AND rosterID = {$rosterID}";
		mysqlQuery($sql, SEND);
	}

	setAlert(USER_ALERT,"Ratings Updated");

}

/******************************************************************************/

function addEventParticipantsByName(){
	
	$eventID = $_SESSION['eventID'];
	if($eventID == null){return;}
	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}
	
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
			@$error['tournamentIDs'] = $fighter['tournamentIDs']; // This may not exist and should be null
			$_SESSION['rosterEntryConflicts']['alreadyExists'][] = $error;
			continue;
		}

	// Staff Properties
		$staffCompetency = 0;
		$staffHoursTarget = 'NULL';
		if(isset($fighter['staffCompetency'])){
			if($fighter['staffCompetency'] >= 0){
				$staffCompetency = (int)$fighter['staffCompetency'];

				$sql = "SELECT staffHoursTarget
						FROM eventDefaults
						WHERE eventID = {$eventID}";
				$staffHoursTarget = (int)mysqlQuery($sql, SINGLE, 'staffHoursTarget');
			}
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
				(systemRosterID, schoolID, eventID, staffCompetency, staffHoursTarget)
				VALUES
				({$systemRosterID}, {$schoolID}, {$eventID}, {$staffCompetency}, {$staffHoursTarget})
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
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
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
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
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
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
	$matchID = $_POST['matchID'];
	$matchInfo = getMatchInfo($matchID);

	if($matchInfo['matchComplete'] == 1){
		return;
	}

	$tournamentID = (int)$matchInfo['tournamentID'];

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

// Deal with sub-matches
	if($matchInfo['placeholderMatchID'] != null){
		$matchNumber = (int)$matchInfo['matchNumber'];
		$placeholderMatchID = (int)$matchInfo['placeholderMatchID'];
		$exchangeNumber = $matchNumber - 1;
		$placeholderMatchInfo = getMatchInfo($placeholderMatchID);

		$sql = "SELECT subMatchMode
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$subMatchMode = mysqlQuery($sql, SINGLE, 'subMatchMode');

		$sql = "SELECT exchangeID
				FROM eventExchanges
				WHERE matchID = {$placeholderMatchID}
				AND exchangeNumber = {$exchangeNumber}";
		$exchangeID = (int)mysqlQuery($sql, SINGLE, 'exchangeID');


		if($winnerID == 'null'){
			$id1 = (int)$matchInfo['fighter1ID'];
			$id2 = (int)$matchInfo['fighter2ID'];
			$exchangeType = 'afterblow';
			$score1 = 0;
			$score2 = 0;
		} elseif($matchInfo['fighter1ID'] == $winnerID){
			$id1 = (int)$matchInfo['fighter1ID'];
			$id2 = (int)$matchInfo['fighter2ID'];
			if($subMatchMode == SUB_MATCH_DIGITAL){
				$exchangeType = 'clean';
				$score1 = 1;
				$score2 = 0;
			} else {
				$exchangeType = 'afterblow';
				$score1 = (int)$matchInfo['fighter1score'];
				$score2 = (int)$matchInfo['fighter2score'];
			}
			
		} else {
			$id1 = (int)$matchInfo['fighter2ID'];
			$id2 = (int)$matchInfo['fighter1ID'];
			if($subMatchMode == SUB_MATCH_DIGITAL){
				$exchangeType = 'clean';
				$score1 = 1;
				$score2 = 0;
			} else {
				$exchangeType = 'afterblow';
				$score1 = (int)$matchInfo['fighter2score'];
				$score2 = (int)$matchInfo['fighter1score'];
			}
		}

		if($exchangeID == 0){
			$sql = "INSERT INTO eventExchanges
					(matchID, exchangeNumber, exchangeType, scoringID, 
					receivingID, scoreValue, scoreDeduction)
					VALUES 
					({$placeholderMatchID},{$exchangeNumber},'{$exchangeType}',{$id1},
					{$id2},{$score1},{$score2})";
			mysqlQuery($sql, SEND);
		} else {
			$sql = "UPDATE eventExchanges
					SET exchangeType = '{$exchangeType}', scoringID = {$id1},
						receivingID = {$id2}, scoreValue = {$score1}, scoreDeduction = {$score2}
					WHERE exchangeID = {$exchangeID}";
			mysqlQuery($sql, SEND);
		}
	

	// Auto Conclude match if necessary
		$sql = "DELETE FROM eventExchanges
				WHERE matchID = {$placeholderMatchID}
				AND exchangeType = 'winner'
				OR exchangeType = 'doubleOut'
				OR exchangeType = 'tie'";
		mysqlQuery($sql, SEND);

		$sql = "UPDATE eventMatches
				SET matchComplete = 0
				WHERE matchID = {$placeholderMatchID}";
		mysqlQuery($sql, SEND);


		$allParts = getSubMatchParts($placeholderMatchID);
		$readyToConclude = true;
		foreach($allParts as $match){
			if($match['isPlaceholder'] == 1){
				continue;
			}

			if($match['matchComplete'] == 0){
				$readyToConclude = false;
				break;
			}
		}

		

		updateMatch($placeholderMatchInfo);

		$placeholderMatchInfo = getMatchInfo($placeholderMatchID);

		if($readyToConclude == true){
			autoConcludeMatch($placeholderMatchInfo);
		}

		
	}

	$_SESSION['updatePoolStandings'][$tournamentID] = getGroupSetOfMatch($matchID);
	
}

/******************************************************************************/

function signOffFighters($signOffData){

	$matchID = (int)$signOffData['matchID'];

	if(isset($signOffData[1]) && $signOffData[1] == SQL_TRUE){
		$sql = "UPDATE eventMatches
				SET signOff1 = 1
				WHERE matchID = {$matchID}";
		mysqlQuery($sql, SEND);
	}

	if(isset($signOffData[2]) && $signOffData[2] == SQL_TRUE){
		$sql = "UPDATE eventMatches
				SET signOff2 = 1
				WHERE matchID = {$matchID}";
		mysqlQuery($sql, SEND);
	}

}

/******************************************************************************/
function addNewEvent(){
	
	if(ALLOW['SOFTWARE_ASSIST'] == false){return;}
	
	$eventYear = substr($_POST['eventStartDate'],0,4);
	$num = mt_rand(100,999);
	$password = "temp{$num}";
	$passwordHash = password_hash($password, PASSWORD_DEFAULT);
	
	$sql = "INSERT INTO systemEvents
			(eventName, eventAbreviation, eventYear, eventStartDate,
			eventEndDate, eventCountry, eventProvince, eventCity,
			eventStatus, staffPassword, organizerPassword)
			VALUES
			(?,?,?,?,?,?,?,?,?,?,?)";
	
	$eventStartDate = $_POST['eventStartDate'];
	if($eventStartDate == null){$eventStartDate = date('Y-m-d H:i:s');}
	$eventEndDate = $_POST['eventEndDate'];
	if($eventEndDate == null){$eventEndDate = $eventStartDate;}
		
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
	if(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['SOFTWARE_ASSIST'] == false){return;}
	
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
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}

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
	
	if(ALLOW['SOFTWARE_ASSIST'] == false){return;}
	
	$meta = $_POST['tournamentTypeMeta'];
	$type = $_POST['tournamentType'];
	
	if($meta == null || $type == null){
		setAlert(USER_ERROR,"No Values in addTournamentType()");
		return;
	}
	
	$sql = "SELECT * FROM systemTournaments
			WHERE tournamentTypeMeta = '{$meta}'
			AND tournamentType = '{$type}'";
	$result = mysqlQuery($sql, SINGLE);
	
	if($result != null){
		setAlert(USER_ERROR,"Type already exists in addTournamentType()");
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

	if(ALLOW['SOFTWARE_ASSIST'] == false){ return;}
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

	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
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
			SET winnerID = null, matchComplete = 0, signOff1 = 0, signOff2 = 0
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

function shouldMatchConcludeByPoints($matchInfo, $maxPoints){
// Returns true if the software should auto-conclude a match because it has reached
// the maximum number of scoring exchanges.	

	if($matchInfo['matchComplete'] == 1 
		|| $matchInfo['ignoreMatch'] == 1
		|| $maxPoints == 0
	){ 
		return false;
	}

	$shouldConclude = false;

	if(	$matchInfo['fighter1score'] >= $maxPoints || $matchInfo['fighter2score'] >= $maxPoints){
		$shouldConclude = true;
		setAlert(USER_ALERT,"Point cap reached.");
	}

	return $shouldConclude;
	
}

/******************************************************************************/

function shouldMatchConcludeBySpread($matchInfo,$maxSpread){
// Returns true if the software should auto-conclude a match because it has reached
// the maximum number of scoring exchanges.	

	if($matchInfo['matchComplete'] == 1 
		|| $matchInfo['ignoreMatch'] == 1
		|| $maxSpread == 0
	){ 
		return false;
	}

	$spread = abs($matchInfo['fighter1score'] - $matchInfo['fighter2score']);

	$shouldConclude = false;

	if($spread >= $maxSpread){
		$shouldConclude = true;
		setAlert(USER_ALERT,"Point spread reached.");
	}

	return $shouldConclude;
	
}

/******************************************************************************/

function shouldMatchConcludeByTime($matchInfo){
// Returns true if the software should auto-conclude a match because it has reached
// the maximum number of scoring exchanges.	
	
	if(    $matchInfo['matchComplete'] == 1 
		|| $matchInfo['ignoreMatch'] == 1 
		|| $matchInfo['timeLimit'] == 0)
	{ 
		return false;
	}

	if($matchInfo['matchTime'] >= $matchInfo['timeLimit']){
		setAlert(USER_ALERT,"Time limit reached.");
		return true;
	} else {
		return false;
	}
}

/******************************************************************************/

function shouldMatchConcludeByExchanges($matchInfo, $maxExchanges){
// Returns true if the software should auto-conclude a match because it has reached
// the maximum number of scoring exchanges.	
	
	if(		$matchInfo['matchComplete'] == 1 
		 || $matchInfo['ignoreMatch'] == 1
		 || $maxExchanges == 0){ 
		return false;
	}

	$sql = "SELECT COUNT(*) AS numExchanges
			FROM eventExchanges
			WHERE matchID = {$matchInfo['matchID']}
			AND exchangeType IN ('clean','afterblow','double')";
	$numExchanges = mysqlQuery($sql, SINGLE, 'numExchanges');

	if($numExchanges >= $maxExchanges){
		setAlert(USER_ALERT,"Exchange count reached.");
		return true;
	} else {
		return false;
	}
}
	
/******************************************************************************/

function autoConcludeMatch($matchInfo){


	if(isReverseScore($matchInfo['tournamentID']) == REVERSE_SCORE_GOLF){
		$reversedResult = true;
	} else {
		$reversedResult = false;
	}

	$_POST['matchID'] = $matchInfo['matchID'];
	
	if(getMatchDoubles($matchInfo['matchID']) >= $matchInfo['maxDoubles']){

		$_POST['matchWinnerID'] = 'doubleOut';

	} elseif($matchInfo['fighter1score'] == $matchInfo['fighter2score']){

		if(!isTies()){
			setAlert(USER_ERROR,"Tie match, can't conclude.");
			return;
		}
		$_POST['matchWinnerID'] = 'tie';

	} elseif($matchInfo['fighter1score'] > $matchInfo['fighter2score']){

		if($reversedResult == false){
			$_POST['matchWinnerID'] = $matchInfo['fighter1ID'];
		} else {
			$_POST['matchWinnerID'] = $matchInfo['fighter2ID'];
		}

	} elseif($matchInfo['fighter2score'] > $matchInfo['fighter1score']){

		if($reversedResult == false){
			$_POST['matchWinnerID'] = $matchInfo['fighter2ID'];
		} else {
			$_POST['matchWinnerID'] = $matchInfo['fighter1ID'];
		}

	} else {

		setAlert(USER_ERROR,"Unable to determine how to conclude match");
		return;

	}
	
	addMatchWinner();
}

/******************************************************************************/

function createNewPools(){
	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}
	$_SESSION['eventChanges']['poolsModified'] = true;
	
	$tournamentID = (int)$_SESSION['tournamentID'];
	$numPoolsToAdd=$_POST['numPoolsToAdd'];
	$groupSet = (int)$_SESSION['groupSet'];
	$pools = getPools($tournamentID, $groupSet);
	$numExistingPools = count($pools);
	$nextPoolNumber = ++$numExistingPools;
	$name = "Pool {$nextPoolNumber}";
	$createPoolRankingOrder = arePoolsRanked($tournamentID, $groupSet);
	if($createPoolRankingOrder == true){
		$sql = "SELECT MAX(groupRank) AS groupRank, MAX(overlapSize) AS overlapSize
				FROM eventGroupRankings
				INNER JOIN eventGroups USING(groupID)
				WHERE tournamentID = {$tournamentID}
				AND groupSet = {$groupSet}
				LIMIT 1";
		$data = mysqlQuery($sql, SINGLE);
		$groupRank = (int)$data['groupRank'] + 1;
		$overlapSize = (int)$data['overlapSize'];
	}

	for($i=1;$i<=$numPoolsToAdd;$i++){
		
		$sql = "INSERT INTO eventGroups
				(tournamentID, groupType, groupNumber, groupName, groupSet)
				VALUES
				({$tournamentID}, 'pool', '{$nextPoolNumber}', '{$name}', {$groupSet})";
		mysqlQuery($sql, SEND);
		
		$nextPoolNumber++;
		$name = "Pool {$nextPoolNumber}";

		if($createPoolRankingOrder == true){
			$groupID = (int)mysqli_insert_id($GLOBALS["___mysqli_ston"]);

			$sql = "INSERT INTO eventGroupRankings
					(groupID, groupRank, overlapSize)
					VALUES
					({$groupID},{$groupRank},{$overlapSize})";
			mysqlQuery($sql, SEND);
			$groupRank++;
		}

	}

}

/******************************************************************************/

function createNewTeam($teamInfo){

// Error checking
	if(ALLOW['EVENT_MANAGEMENT'] == false){ 
		setAlert(USER_ERROR,"You must be logged in as an Event Organizer to do this operation");
		return;
	}
	$eventID = (int)$_SESSION['eventID'];
	if($eventID == 0){
		setAlert(SYSTEM,"No eventID in createNewTeam()");
		return;
	}
	$tournamentID = (int)$_SESSION['tournamentID'];
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in createNewTeam()");
		return;
	}
	if($teamInfo == null){return;}

// Creating team
	$sql = "INSERT INTO eventRoster
			(eventID, isTeam) VALUES ({$eventID}, 1)";
	mysqlQuery($sql, SEND);
	$teamID = (int)mysqli_insert_id($GLOBALS["___mysqli_ston"]);
	$teamInfo['teamID'] = $teamID;

	$sql = "INSERT INTO eventTournamentRoster
			(tournamentID, rosterID) VALUES ({$tournamentID}, {$teamID})";
	mysqlQuery($sql, SEND);

	$sql = "INSERT INTO eventTeamRoster
			(teamID, memberRole, memberName) VALUES ({$teamID},'teamName',?)";

	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	// "s" means the database expects a string
	$bind = mysqli_stmt_bind_param($stmt, "s", $teamInfo['teamName']);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);

// Add team members
	addTeamMembers($teamInfo, $tournamentID);

}

/******************************************************************************/

function addTeamMembers($teamInfo, $tournamentID = null){



// Error checking
	if(ALLOW['EVENT_MANAGEMENT'] == false){ 
		setAlert(USER_ERROR,"You must be logged in as an Event Organizer to do this operation");
		return;
	}
	if($tournamentID == null){
		$tournamentID = (int)$_SESSION['tournamentID'];
	}
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in createNewTeam()");
		return;
	}


// Add members
	if(isset($teamInfo['newMembers'])){
		foreach($teamInfo['newMembers'] as $rosterID){
			if($rosterID == ''){
				continue;
			}

		// Check for a duplicate entry
			$sql = "SELECT COUNT(*) AS isDuplicate
					FROM eventTeamRoster t1
					INNER JOIN eventTournamentRoster t2 ON t1.tournamentRosterID = t2.tableID
					WHERE tournamentID = {$tournamentID}
					AND t1.rosterID = {$rosterID}";

			$isAlreadyEntered = (bool)mysqlQuery($sql, SINGLE, 'isDuplicate');

			if($isAlreadyEntered){
				continue;
			}

			$sql = "SELECT tableID AS tournamentRosterID
					FROM eventTournamentRoster
					WHERE rosterID = {$rosterID}
					AND tournamentID = {$tournamentID}";
			$tournamentRosterID = mysqlQuery($sql, SINGLE, 'tournamentRosterID');

		// Enter fighter into team
			$sql = "INSERT INTO eventTeamRoster
					(teamID, rosterID, tournamentRosterID, memberRole)
					VALUES
					({$teamInfo['teamID']},{$rosterID},{$tournamentRosterID},'member')";
			mysqlQuery($sql, SEND);
		}
	}

// Change the team name
	if(isset($teamInfo['teamName'])){
		$sql = "UPDATE eventTeamRoster
				SET memberName = ?
				WHERE teamID = ?
				AND memberRole = 'teamName'";
		$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
		$bind = mysqli_stmt_bind_param($stmt, "si",$teamInfo['teamName'], $teamInfo['teamID']);
		$exec = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	}

}

/******************************************************************************/

function deleteTeams($deleteInfo){

	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}

	if(isset($deleteInfo['teamsToDelete'])){
		foreach((array)$deleteInfo['teamsToDelete'] as $teamID => $data){
			$sql = "DELETE FROM eventRoster
					WHERE rosterID = {$teamID}";
			mysqlQuery($sql, SEND);
		}
	}

	if(isset($deleteInfo['membersToDelete'])){
		foreach((array)$deleteInfo['membersToDelete'] as $tableID => $data){
			$sql = "DELETE FROM eventTeamRoster
					WHERE tableID = {$tableID}";
			mysqlQuery($sql, SEND);
		}
	}
}


/******************************************************************************/

function createNewRounds(){
	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}
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

function createPrimaryBracket($tournamentID, $numFighters, $extendBracketBy = 0){
// creates a winners bracket
	
	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}
	$tournamentID = (int)$tournamentID;
	$numFighters = (int)$numFighters;

	$bracketLevels = (int)ceil(log($numFighters,2));
	$matchesToSkip = (int)pow(2,$bracketLevels) - $numFighters;
	$numSubMatches = (int)getNumSubMatches($tournamentID);

	// Create The Group
	$sql = "DELETE FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'";
	mysqlQuery($sql, SEND);
	
	$sql = "INSERT INTO eventGroups
				(tournamentID, groupName, groupNumber, bracketLevels, groupType, numFighters)
				VALUES
				({$tournamentID}, 'winner', 1, {$bracketLevels}, 'elim', {$numFighters})";
	mysqlQuery($sql, SEND);

	$groupID = (int)mysqli_insert_id($GLOBALS["___mysqli_ston"]);
	
	// Create By Matches

	for($bracketLevel=$bracketLevels;$bracketLevel>0;$bracketLevel--){
		$matchesInLevel = pow(2,$bracketLevel-1);
		
		for($currentMatch=1;$currentMatch<=$matchesInLevel;$currentMatch++){
			if($bracketLevel==$bracketLevels AND $currentMatch <= $matchesToSkip){
				continue;
			}
			
			$bracketPosition = getBracketPositionByRank($currentMatch,$matchesInLevel);


			createBracketMatch($groupID, $bracketLevel, $bracketPosition, $numSubMatches);

			
			// In a true double elim there are extra matches at the highest bracket level.
			if($bracketLevel == 1 & $extendBracketBy != 0){
				for($i=1;$i<=$extendBracketBy;$i++){
					$bracketPosition = $i + 1;

					createBracketMatch($groupID, $bracketLevel, $bracketPosition, $numSubMatches);

				}
			}

		}
	}
}

/******************************************************************************/

function createSecondaryBracket($tournamentID, $numFighters, $extendBracketBy = 0){

	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}

	$tournamentID = (int)$tournamentID;
	$numFighters = (int)$numFighters;
	$bracketLevels = (int)getBracketDepthByFighterCount($numFighters,2);
	$numSubMatches = (int)getNumSubMatches($tournamentID);

	$sql = "INSERT INTO eventGroups
			(tournamentID, groupName, groupNumber, bracketLevels, groupType, numFighters)
			VALUES
			({$tournamentID}, 'loser', 2, {$bracketLevels}, 'elim', {$numFighters})";
	mysqlQuery($sql, SEND);
		
	$groupID = (int)mysqli_insert_id($GLOBALS["___mysqli_ston"]);

// Single Elim
	if($numFighters == 2){

		createBracketMatch($groupID, 1, 1, $numSubMatches);
	
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
	
				createBracketMatch($groupID, $bracketLevel, $bracketPosition, $numSubMatches);

				if($bracketLevel == 1 && $extendBracketBy != 0){
					for($i=1;$i<=$extendBracketBy;$i++){
						$bracketPosition++;
						createBracketMatch($groupID, $bracketLevel, $bracketPosition, $numSubMatches);
					}
				}
			}
		}
		
		
	}
}

/******************************************************************************/

function createBracketMatch($groupID, $bracketLevel, $bracketPosition, $numSubMatches){

	$groupID = (int)$groupID;
	$bracketLevel = (int)$bracketLevel;
	$bracketPosition = (int)$bracketPosition;

	if($groupID == 0 || $bracketLevel == 0 || $bracketPosition == 0){
		setAlert(SYSTEM,"Invalid parameters passed to createBracketMatch().");
		return;
	}

	if($numSubMatches > 1){
		$isPlaceholder = 1;
	} else {
		$isPlaceholder = 0;
		$numSubMatches = 0;
	}

	$sql = "INSERT INTO eventMatches
			(groupID, bracketPosition, bracketLevel, isPlaceholder)
			VALUES
			({$groupID}, {$bracketPosition}, {$bracketLevel}, {$isPlaceholder})";
	mysqlQuery($sql, SEND);

	$matchID = (int)mysqli_insert_id($GLOBALS["___mysqli_ston"]);

	// Create sub-matches
	for($i = 1;$i <= $numSubMatches;$i++){
		$sql = "INSERT INTO eventMatches
				(groupID, matchNumber, placeholderMatchID)
				VALUES
				({$groupID}, {$i}, {$matchID})";
		mysqlQuery($sql, SEND);
	}
	

}

/******************************************************************************/

function extendFinalsBracket($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "SELECT groupID, bracketPosition, numSubMatches
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'
			AND groupNumber = 1
			AND bracketLevel = 1
			ORDER BY bracketPosition DESC
			LIMIT 1";

	$data = mysqlQuery($sql, SINGLE);

	$groupID = (int)$data['groupID'];
	$bracketPosition = (int)$data['bracketPosition'] + 1;
	$subMatches = (int)$data['numSubMatches'];

	if($groupID == 0 || $bracketPosition <= 1){
		setAlert(SYSTEM,"Invalid data returned from query in extendFinalsBracket()");
		return;
	}

	createBracketMatch($groupID, 1, $bracketPosition, $subMatches);

}

/******************************************************************************/

function contractFinalsBracket($tournamentID){

	$tournamentID = (int)$tournamentID;

	$sql = "DELETE eventMatches FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'
			AND groupNumber = 1
			AND bracketLevel = 1
			AND bracketPosition >= 3";

	mysqlQuery($sql, SEND);

	$_SESSION['matchID'] = 0;
	redirect('finalsBracket.php');
}


/******************************************************************************/

function deleteBracket(){
	
	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}
	
	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){return;}

	$sql = "DELETE FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'";
	mysqlQuery($sql, SEND);
	
}

/******************************************************************************/

function deleteExchanges(){
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
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
	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}
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
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	

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
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
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
	if(ALLOW['SOFTWARE_ASSIST'] == false){return;}

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
	if($_POST['eventEndDate'] == ''){
		$_POST['eventEndDate'] = $_POST['eventStartDate'];
	}
	
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
	
	if($exec == true){
		setAlert(USER_ALERT,"<strong>{$name}</strong> updated");
	} else {
		setAlert(SYSTEM,"SQL Query Fail in editEvent()");
	}
	
}

/******************************************************************************/

function editEventStatus(){
	
	if(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['SOFTWARE_ASSIST'] == false){ return; }
	$eventID = $_SESSION['eventID'];
	if($eventID == null){return;}
	
	if(!isset($_POST['eventStatus'])){
		setAlert(USER_ERROR,"No Event Status provided.");
		return;	
	}

	$eventStatus = $_POST['eventStatus'];

	switch($_POST['eventStatus']){
		case 'archived':
			$eventStatus = 'archived';
			break;
		case 'upcoming':
			$eventStatus = 'upcoming';
			break;
		case 'active':
			$eventStatus = 'active';
			break;
		case 'hidden':
			$eventStatus = 'hidden';
			break;
		default:
			setAlert(USER_ERROR,"Scorecard has encountered an unknown Event Status.");
			return;
			break;
	}

	
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
	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}
	
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
	
	$schoolID = $_POST['editParticipantData']['schoolID'];
	$tournaments = @$_POST['editParticipantData']['tournamentIDs']; // There may be no tournaments set
	$firstName = rtrim($_POST['editParticipantData']['firstName']);
	$lastName = rtrim($_POST['editParticipantData']['lastName']);

	$sql = "SELECT COUNT(*) AS numEvents
			FROM eventRoster 
			WHERE systemRosterID = {$systemRosterID}
			AND eventID != {$eventID}";
	$count = (int)mysqlQuery($sql, SINGLE, 'numEvents');

	if(($count == 0) || (ALLOW['SOFTWARE_ADMIN'] == true)){
		
		$sql = "UPDATE systemRoster
				SET firstName = ?, lastName = ?
				WHERE systemRosterID = {$systemRosterID}";

		$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
		$bind = mysqli_stmt_bind_param($stmt, "ss", $firstName, $lastName);
		$exec = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	} else {
		$oldName = getFighterName($rosterID,'split');

		if(strcmp($oldName['firstName'],$firstName) != 0
			|| strcmp($oldName['lastName'],$lastName) != 0){

			$oldName = getFighterName($rosterID);

			setAlert(USER_ERROR,
				"<u>{$oldName}</u> already exists in the system.<BR>
				You can not change their name.<BR>
				If there is an issue, please contact the HEMA Scorecard Staff."
				);
		}
	}
	
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

function recordTournamentPlacings($tournamentID,$input){

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}
	if($input['placings'] == null){
		return;
	}


	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in recordTournamentPlacings()");
		return;
	}

// Check for duplicates
	$duplicateCheck = [];
	$errorString = '';
	$index = 1;
	foreach($input['placings'] as $placing){
		$rosterID = (int)$placing['rosterID'];
		if($rosterID == 0){
			continue;
		}

		if(isset($duplicateCheck[$rosterID])){
			$p1 = $duplicateCheck[$rosterID];
			$name = getFighterName($rosterID);
			$errorString .= "<li>{$name} in [{$p1}] and [{$placing['place']}]</li>";
			continue;
		}

		$duplicateCheck[$rosterID] = $placing['place'];
	}

	if($errorString != ''){
		setAlert(USER_ERROR, "Fighters entered in more than on location. Can not finalize results.");
		$_SESSION['manualPlacing']['tournamentID'] = $tournamentID;
		$_SESSION['manualPlacing']['data'] = $input['placings'];
		$_SESSION['manualPlacing']['message'] = "<strong>Error</strong>: Same person in more than one place.
			(<em>Results not saved.</em>){$errorString}";
		return;
	}

	// Delete old placings, if they exist
	$sql = "DELETE FROM eventPlacings
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);

// Write the placings
	foreach($input['placings'] as $placing){

		$rosterID = (int)$placing['rosterID'];
		if($rosterID == 0){
			continue;
		}
		$rosterID = (int)$placing['rosterID'];

		if($placing['tie'] == 0){
			$type = 'final';
			$low = 'null';
			$high = 'null';
		} elseif(isset($placing['bracket']) == true) {
			$type = 'bracket';
			$low = $placing['place'];
			$high = $placing['place'] - $placing['tie'] + 1;
		} else {
			$type = 'tie';
			$low = $placing['place'];
			$high = $placing['place'] + $placing['tie'] - 1;
		}

		
		$sql = "INSERT INTO eventPlacings
				(tournamentID, rosterID, placing, placeType, lowBound, highBound)
				VALUES
				({$tournamentID},{$rosterID},{$placing['place']},'{$type}',{$low},{$high})";
		mysqlQuery($sql, SEND);
	}

	$sql = "UPDATE eventTournaments
			SET isFinalized = 1
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);


}


/******************************************************************************/

function generateTournamentPlacings($tournamentID, $specs){
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}
	
	$tournamentID = (int)$tournamentID;
	$formatID = getTournamentFormat($tournamentID);
	
	switch($formatID){
		case FORMAT_SOLO:
			generateTournamentPlacings_round($tournamentID);
			break;
		case FORMAT_MATCH:
			if(isBrackets($tournamentID) == true){
				$placings = generateTournamentPlacings_bracket($tournamentID, $specs);
			}
			// deliberate fall through
		case FORMAT_COMPOSITE:
			if(isset($placings) == false){
				$placings = [];
			}

			generateTournamentPlacings_set($tournamentID, $placings);
			break;
		default:
			displayAlert(USER_ERROR,"Automatic placing generation not supported for this mode.");
			break;
	}

}

/******************************************************************************/

function generateTournamentPlacings_set($tournamentID, $placings = []){

	$tournamentID = (int)$tournamentID;
	if(isset($placings['assignedFighters']) == true){
		$assignedFighters = $placings['assignedFighters'];
		unset($placings['assignedFighters']);
	} else {
		$assignedFighters = [];
	}
	
	$numSets = getNumGroupSets($tournamentID);

	$place = 1;
	$index = 1;
	foreach($placings['placings'] as $oldIndex => $data){
		if($data['place'] >= $place){
			$place = $data['place'] + 1;
			$index = $oldIndex + 1;
		}
	}

	$startOfTie = 0;
	$endOfTie = 0;
	$tieSize = 0;
	$hasTies = false;


	// Loop through all the sets backward, because the later results are more important.
	for($set = $numSets; $set >= 1; $set--){

		$sql = "SELECT rosterID, rank, score
				FROM eventStandings
				WHERE tournamentID = {$tournamentID}
				AND groupSet = {$set}
				ORDER BY rank ASC";
		$roundData = mysqlQuery($sql, ASSOC);
	
		$oldScore = null;
		$numScores = count($roundData);
		foreach($roundData as $placing){

			$rosterID = $placing['rosterID'];
			$thisScore = round($placing['score'],3);

			// Don't count the fight if their place was already recorded in a later round/bracket.
			if(isset($assignedFighters[$rosterID])){
				continue; 
			}
			
			// Record the place data
			$tmp['rosterID'] = $rosterID;
			$tmp['place'] = $place;
			$tmp['tie'] = 0;
			$placings['placings'][$index] = $tmp;
			$assignedFighters[$rosterID] = true;

			// Check if the fighter has tied with the previous one.
			if($thisScore === $oldScore){
				$hasTies = true;
				if($startOfTie == 0){
					$startOfTie = $index-1;
					$tieSize = 1;
				}
				$tieSize++;
				$endOfTie = $index;
			} else {
				$place++;
			}

			// If a tie has ended (past the last tied fighter), loop through and 
			// set the 'tie' value for all fighters involved in the tie.
			if($tieSize != 0 && ($endOfTie != $index || $index == $numScores)){

				for($i = $startOfTie; $i<=$endOfTie;$i++){
					$placings['placings'][$i]['tie'] = $tieSize;
				}

				$tieSize = 0;
				$endOfTie = 0;
				$startOfTie = 0;
			}

			// Set data for the next loop
			$index++;
			$oldScore = $thisScore;
		}
	}

	if($hasTies == true){

		setAlert(USER_ALERT, "Ties detected in tournament. Please confirm results. ");
		$_SESSION['manualPlacing']['tournamentID'] = $tournamentID;
		$_SESSION['manualPlacing']['data'] = $placings['placings'];
		$_SESSION['manualPlacing']['message'] = "Ties have been detected. Please confirm this list.
				<BR><em>Ties are shown in the right hand box with wblue arrows.</em>(<strong class='blue-text'>&#8624;</strong>)";
		redirect("infoSummary.php#anchor{$tournamentID}");

	} else {
		recordTournamentPlacings($tournamentID, $placings);
	}

}

/******************************************************************************/

function generateTournamentPlacings_round($tournamentID){

	$tournamentID = (int)$tournamentID;
	
	$sql = "SELECT fighter1ID, GREATEST(fighter1Score,fighter2Score) AS score, groupSet, groupNumber 
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'round'";
	$res = mysqlQuery($sql, ASSOC);
	
	$numScores = 0;
	foreach($res as $match){
		if($match['score'] === null){ continue;	}
		$scores[$match['groupSet']][$match['groupNumber']][$match['fighter1ID']] = $match['score'];
		$numScores++;
	}
	
	$overalScores = array();
	$index = 1;
	$startOfTie = 0;
	$endOfTie = 0;
	$tieSize = 0;
	$place = 1;
	$hasTies = false;

	// Loop through all the sets backward, because the later results are more important.
	for($groupSet = count($scores); $groupSet >= 1; $groupSet--){

		// Loop through all the groups backwards, because the later results are more important.
		for($groupNum = count($scores[$groupSet]); $groupNum >= 1; $groupNum--){
			
			$roundScores = [];
			// Sum up the group set scores for each fighter (points are cumulative across rounds.)
			foreach($scores[$groupSet][$groupNum] as $rosterID => $score){
				if(isset($fightersInList[$rosterID])){ continue; }
				$score = 0;
				foreach($scores[$groupSet] as $pieces){
					$score += @$pieces[$rosterID]; // Might not exist, score is same as zero
				}
				$fightersInList[$rosterID] = true;
				
				$roundScores[$rosterID] = $score;
			}
			
			if(isset($roundScores)){
				if(isReverseScore($tournamentID) == REVERSE_SCORE_NO){
					arsort($roundScores);
				} else {
					asort($roundScores);
				}
				$oldScore = null;
				foreach($roundScores as $rosterID => $score){

					// Record the place data
				$tmp['rosterID'] = $rosterID;
				$tmp['place'] = $place;
				$tmp['tie'] = 0;
				$placings['placings'][$index] = $tmp;
				$assignedFighters[$rosterID] = true;

				// Check if the fighter has tied with the previous one.
				if((int)$score === $oldScore){
					$hasTies = true;
					if($startOfTie == 0){
						$startOfTie = $index-1;
						$tieSize = 1;
					}
					$tieSize++;
					$endOfTie = $index;
				} else {
					$place++;
				}

				// If a tie has ended (past the last tied fighter), loop through and 
				// set the 'tie' value for all fighters involved in the tie.
				if($tieSize != 0 && ($endOfTie != $index || $index == $numScores)){

					for($i = $startOfTie; $i<=$endOfTie;$i++){
						$placings['placings'][$i]['tie'] = $tieSize;
					}

					$tieSize = 0;
					$endOfTie = 0;
					$startOfTie = 0;
				}

				// Set data for the next loop
				$index++;
				$oldScore = (int)$score;
				}
			}
		}
	}

	if($hasTies == true){

		setAlert(USER_ALERT, "Ties detected in tournament. Please confirm results. ");
		$_SESSION['manualPlacing']['tournamentID'] = $tournamentID;
		$_SESSION['manualPlacing']['data'] = $placings['placings'];
		$_SESSION['manualPlacing']['message'] = "Ties have been detected. Please confirm this list.
				<BR><em>Ties are shown in the right hand box with wblue arrows.</em>(<strong class='blue-text'>&#8624;</strong>)";
		redirect("infoSummary.php#anchor{$tournamentID}");

	} else {
		recordTournamentPlacings($tournamentID, $placings);
	}
	
}

/******************************************************************************/

function generateTournamentPlacings_bracket($tournamentID, $specs){

	$tournamentID = (int)$tournamentID;
	$unfinishedBracketMatches = false;

	if(isset($specs['breakTies']) == true && (int)$specs['breakTies'] != 0){
		$breakTies = true;
	} else {
		$breakTies = false;
	}
	$index = 1;

	$sql = "SELECT groupID, groupName
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'";
	$groups = mysqlQuery($sql, KEY, 'groupName');
	
	$primaryBracketID = $groups['winner']['groupID'];
	$secondaryBracketID = @$groups['loser']['groupID']; // may not exist if there is no loser bracket
	$bracketInfo = getBracketInformation($tournamentID);
	$primaryMatches = getBracketMatchesByPosition($primaryBracketID);
	$secondaryMatches = getBracketMatchesByPosition($secondaryBracketID);

	$winnerIndex =count($primaryMatches[1]);

// Winner of primary bracket
	if($primaryMatches[1][$winnerIndex]['winnerID'] != null && $primaryMatches[1][$winnerIndex]['loserID'] != null){

		$tmp['rosterID'] = $primaryMatches[1][$winnerIndex]['winnerID'];
		$tmp['place'] = 1;
		$tmp['tie'] = 0;
		$placings['placings'][$index] = $tmp;
		$assignedFighters[$tmp['rosterID']] = true;
		$index++;

		$tmp['rosterID'] = $primaryMatches[1][$winnerIndex]['loserID'];
		$tmp['place'] = 2;
		$tmp['tie'] = 0;
		$placings['placings'][$index] = $tmp;
		$assignedFighters[$tmp['rosterID']] = true;
		$index++;

	} else {
		$unfinishedBracketMatches = true;
	}

// Winner of secondary bracket
	if($secondaryMatches[1][1]['winnerID'] != null && $secondaryMatches[1][1]['loserID'] != null){

		$tmp['rosterID'] = $secondaryMatches[1][1]['winnerID'];
		$tmp['place'] = 3;
		$tmp['tie'] = 0;
		$placings['placings'][$index] = $tmp;
		$assignedFighters[$tmp['rosterID']] = true;
		$index++;

		$tmp['rosterID'] = $secondaryMatches[1][1]['loserID'];
		$tmp['place'] = 4;
		$tmp['tie'] = 0;
		$placings['placings'][$index] = $tmp;
		$assignedFighters[$tmp['rosterID']] = true;
		$index++;

	} else {
		$unfinishedBracketMatches = true;
	}


	if($unfinishedBracketMatches == true){
		// Don't try to update the rest of the matches.
	} elseif(isset($bracketInfo[BRACKET_SECONDARY]) == true){

		// Double Elim
		foreach($secondaryMatches as $bracketLevel => $levelData){

			if($bracketLevel == 1 ){continue;}

			foreach($levelData as $bracketPosition => $matchInfo){

				$high = getNumEntriesAtLevel_consolation($bracketLevel,'fighters')+2;
				$low = getNumEntriesAtLevel_consolation($bracketLevel-1,'fighters')+3;

				if($matchInfo['loserID'] != null){

					$tmp['rosterID'] = $matchInfo['loserID'];
					$tmp['place'] = $high;
					$tmp['tie'] = $high - $low + 1;
					$tmp['bracket'] = true;
					$placings['placings'][$index] = $tmp;
					$assignedFighters[$tmp['rosterID']] = true;
					$index++;

				} else {
					$unfinishedBracketMatches = true;
					break 2;
				}
			}
		}
	} else {
		// Single Elim
		
		foreach($primaryMatches as $bracketLevel => $levelData){
			if($bracketLevel == 1 || $bracketLevel == 2){continue;}
			foreach($levelData as $bracketPosition => $matchInfo){
				$high = pow(2,$bracketLevel);
				$low = pow(2,$bracketLevel-1)+1;
				if($high > $bracketInfo[BRACKET_PRIMARY]['numFighters']){
					$high = $bracketInfo[BRACKET_PRIMARY]['numFighters'];
				}

				if($matchInfo['loserID'] != null){
					
					$tmp['rosterID'] = $matchInfo['loserID'];
					$tmp['place'] = $high;
					$tmp['tie'] = $high - $low + 1;
					$tmp['bracket'] = true;
					$placings['placings'][$index] = $tmp;
					$assignedFighters[$tmp['rosterID']] = true;
					$index++;

				} else {
					$unfinishedBracketMatches = true;
					break 2;
				}
			}
		}
	}

	if($breakTies == true){
		$placings['placings'] = breakBracketTies($tournamentID, $placings['placings'], $specs);
	}

	// This needs to be returned and passed into the rating calculation for sets.
	$placings['assignedFighters'] = $assignedFighters;
	
	if($unfinishedBracketMatches == true){

		setAlert(USER_ALERT, "Ties detected in tournament. Please confirm results. ");
		$_SESSION['manualPlacing']['tournamentID'] = $tournamentID;
		$_SESSION['manualPlacing']['data'] = $placings['placings'];
		$_SESSION['manualPlacing']['message'] = "<p class='red-text'><strong>You seem to have matches with no winners in your bracket.</p></strong><p>Please fill in the results manually. (I did the best I could.)</p>";
		redirect("infoSummary.php#anchor{$tournamentID}");

	} else {

		return $placings;

	}

}

/******************************************************************************/

function breakBracketTies($tournamentID,$placings, $specs){

	$tournamentID = (int)$tournamentID;

	if(isset($specs['subMatchLimit']) == true){
		$subMatchLimit = $specs['subMatchLimit'];
	} else {
		$subMatchLimit = 0;
	}

	$sql = "SELECT fighter1ID, fighter2ID, winnerID, 
					fighter1score, fighter2score, matchNumber
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'elim'
			AND isPlaceholder = 0";
	$matchData = mysqlQuery($sql, ASSOC);

	$emptyScore['sort2'] 		= 0; 
	$emptyScore['sort3']		= 0;
	$emptyScore['numWins'] 		= 0;
	$emptyScore['numMatches'] 	= 0;

	foreach($matchData as $match){
		$f1ID = $match['fighter1ID'];
		$f2ID = $match['fighter2ID'];

		if(isset($scores[$f1ID]) == false){
			$scores[$f1ID] = $emptyScore;
		}
		if(isset($scores[$f2ID]) == false){
			$scores[$f2ID] = $emptyScore;
		}

		$scores[$f1ID]['numMatches']++;
		$scores[$f2ID]['numMatches']++;

		if($subMatchLimit == 0 || $match['matchNumber'] <= $subMatchLimit){
			$scores[$f1ID]['sort3'] += $match['fighter1score'] - $match['fighter2score'];
			$scores[$f2ID]['sort3'] += $match['fighter2score'] - $match['fighter1score'];
		}

		if($match['winnerID'] == $f1ID){
			$scores[$f1ID]['numWins']++;
		} elseif($match['winnerID'] == $f2ID){
			$scores[$f2ID]['numWins']++;
		}

		$scores[$f1ID]['sort2'] = $scores[$f1ID]['numWins']/$scores[$f1ID]['numMatches'];
		$scores[$f2ID]['sort2'] = $scores[$f2ID]['numWins']/$scores[$f2ID]['numMatches'];


		$sort2[$f1ID] = $scores[$f1ID]['sort2'];
		$sort2[$f2ID] = $scores[$f2ID]['sort2'];
		$sort3[$f1ID] = $scores[$f1ID]['sort3'];
		$sort3[$f2ID] = $scores[$f2ID]['sort3'];

	}


	foreach($placings as $index => $place){
		$rosterID = $place['rosterID'];

		if(isset($place['bracket']) == false){
			unset($scores[$rosterID]);
			unset($sort2[$rosterID]);
			unset($sort3[$rosterID]);
			continue;
		}

		$scores[$rosterID]['place'] = $place['place'] - $place['tie'] + 1;
		$scores[$rosterID]['sort1'] = $scores[$place['rosterID']]['place'];
		$sort1[$rosterID] = $place['place'];
		$scores[$rosterID]['index'] = $index;

	}


	array_multisort($sort1, SORT_ASC, $sort2, SORT_DESC, $sort3, SORT_DESC, $scores);


	$place == null;

	end($scores);
	$lastRosterID = key($scores);
	$last['sort1'] = null;
	$last['sort2'] = null;
	$last['sort3'] = null;
	$startOfTie = 0;
	foreach($scores as $rosterID => $item){

		$place = $item['place'];
		if(isset($placeOffset[$place]) == false){
			$placeOffset[$place] = 0;
		}

		$finalPlace = $place + $placeOffset[$place];
		$placeOffset[$place]++;

		$index = $item['index'];

		$placings[$index]['place'] = $finalPlace;
		$placings[$index]['tie'] = 0;
		unset($placings[$index]['bracket']);

		if(    $item['sort1'] == $last['sort1']
			&& $item['sort2'] == $last['sort2']
			&& $item['sort3'] == $last['sort3']){

			$endOfTie = $index;
			$tiePlace = $place;

			if($startOfTie == 0){
				$startOfTie = $lastIndex;
				$tieSize = 2;
			} else {
				$tieSize++;
			}

		} 


		if($startOfTie != 0 
			&& (($endOfTie != $index) || $rosterID == $lastRosterID) ){

			for($i = $startOfTie;$i<=$endOfTie;$i++){
				$placings[$i]['place'] = $tiePlace;
				$placings[$i]['tie'] = $tieSize;
			}

			$startOfTie = 0;
			$tieSize = 0;
		}

		$last['sort1'] = $item['sort1'];
		$last['sort2'] = $item['sort2'];
		$last['sort3'] = $item['sort3'];
		$lastIndex = $index;

	}

	return $placings;

}


/******************************************************************************/

function insertLastExchange($matchInfo, $exchangeType, $rosterID, $scoreValueIn, $scoreDeductionIn, 
							$refPrefix = null, $refType = null, $refTarget = null, $exchangeID = null){
// records a new exchange into the match	
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
	$scoreValue = (float)$scoreValueIn;
	$scoreDeduction = abs((float)$scoreDeductionIn);
	
	$matchID = $matchInfo['matchID'];

	if($matchInfo['fighter1ID'] == $rosterID){
		$receivingID = $matchInfo['fighter2ID'];
	} else if($matchInfo['fighter2ID'] == $rosterID){
		$receivingID = $matchInfo['fighter1ID'];
	} else {
		$rosterID = $matchInfo['fighter1ID'];
		$receivingID = $matchInfo['fighter2ID'];
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
				(matchID, exchangeType, scoringID, receivingID, scoreValue, 
				scoreDeduction, exchangeTime, refPrefix, refType, refTarget, exchangeNumber)
				VALUES
				({$matchID}, '{$exchangeType}', {$rosterID}, {$receivingID}, {$scoreValue}, 
				{$scoreDeduction}, {$exchangeTime}, {$refPrefix}, {$refType}, {$refTarget}, {$exchangeNumber})";
	} else {
		$sql = "UPDATE eventExchanges
				SET matchID 	= {$matchID}, 
				exchangeType	= '{$exchangeType}', 
				scoringID		= {$rosterID}, 
				receivingID		= {$receivingID}, 
				scoreValue 		= {$scoreValue}, 
				scoreDeduction  = {$scoreDeduction}, 
				
				refPrefix		= {$refPrefix}, 
				refType			= {$refType}, 
				refTarget		= {$refTarget}
				WHERE exchangeID = {$exchangeID}";
	}

	mysqlQuery($sql, SEND);

}

/******************************************************************************/

function insertNewEventParticipant($firstName, $lastName, $schoolID, $tournamentIDs){
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
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
	if(ALLOW['SOFTWARE_ASSIST'] == false){return;}
	
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

function logisticsDeleteLocations($locationsToDelete){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}
	
	if($locationsToDelete == null){
		return;
	}

	foreach($locationsToDelete as $locationID){
		$sql = "DELETE FROM logisticsLocations
				WHERE locationID = {$locationID}";
		mysqlQuery($sql, SEND);
	}
	
}

/******************************************************************************/

function logisticsEditLocations($locationInformation){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

	if($locationInformation == null){
		return;
	}


	$eventID = (int)$locationInformation['eventID'];

	foreach($locationInformation['locations'] as $locationID => $locationInfo){

		$locationID = (int)$locationID;
		if($locationInfo['locationName'] == ''){
			continue;
		}

		// If adding a new location
		if($locationID < 0){

			$sql = "INSERT INTO logisticsLocations
					(eventID, locationName, hasMatches, hasClasses)
					VALUES
					({$eventID},?,?,?)";

			$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
			// "s" means the database expects a string
			$bind = mysqli_stmt_bind_param($stmt, "sii", $locationInfo['locationName'],
										$locationInfo['hasMatches'],$locationInfo['hasClasses']);
			$exec = mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);	

		} else {

			$sql = "UPDATE logisticsLocations
					SET locationName = ?, hasMatches = ?, hasClasses = ?
					WHERE locationID = ?";

			$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
			// "s" means the database expects a string
			$bind = mysqli_stmt_bind_param($stmt, "siii", $locationInfo['locationName'],
										$locationInfo['hasMatches'],$locationInfo['hasClasses'],
										$locationID);
			$exec = mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);

			if($locationInfo['hasMatches'] == 0){
				$blockTypeID = SCHEDULE_BLOCK_TOURNAMENT;
				$sql = "DELETE shed
						FROM logisticsScheduleBlocks AS shed
						LEFT JOIN logisticsLocationsBlocks AS loc ON shed.blockID = loc.blockID
						WHERE locationID = {$locationID}
						AND eventID = {$eventID}
						AND blockTypeID = {$blockTypeID}";
				mysqlQuery($sql, SEND);

				$sql = "DELETE FROM logisticsLocationsMatches
						WHERE locationID = {$locationID}";
				mysqlQuery($sql, SEND);

				$sql = "UPDATE eventGroups
						SET locationID = NULL
						WHERE locationID = {$locationID}";
				mysqlQuery($sql, SEND);
			}

			if($locationInfo['hasClasses'] == 0){
				$blockTypeID = SCHEDULE_BLOCK_WORKSHOP;
				$sql = "DELETE shed
						FROM logisticsScheduleBlocks AS shed
						LEFT JOIN logisticsLocationsBlocks AS loc ON shed.blockID = loc.blockID
						WHERE locationID = {$locationID}
						AND eventID = {$eventID}
						AND blockTypeID = {$blockTypeID}";
				mysqlQuery($sql, SEND);
			}

		}
	
	}
	
}

/******************************************************************************/

function logisticsCheckInMatchStaffFromShift($info){

	$matchID = (int)$info['matchID'];
	$shiftID = (int)$info['shiftID'];

	$sql = "DELETE FROM logisticsStaffMatches
			WHERE matchID = {$matchID}";
	mysqlQuery($sql, SEND);

	$sql = "SELECT rosterID, logisticsRoleID
			FROM logisticsStaffShifts
			WHERE shiftID = {$shiftID}";
	$staffList = mysqlQuery($sql, ASSOC);

	if($staffList == null){
		setAlert(USER_ALERT,"This staffing shift is empty, no staff assigned to match.");
		return;
	}

	foreach($staffList as $staff){
		$rosterID = (int)$staff['rosterID'];
		$roleID = (int)$staff['logisticsRoleID'];

		$sql = "INSERT INTO logisticsStaffMatches
				(matchID, rosterID, logisticsRoleID)
				VALUES 
				({$matchID}, {$rosterID}, {$roleID})";
		mysqlQuery($sql, SEND);
	}

	
	setAlert(USER_ALERT,"Staff loaded into match.");
		
	
}

/******************************************************************************/

function logisticsCheckInMatchStaff($info){

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

	$matchID = (int)$info['matchID'];
	$anyErrors = false;

	foreach($info['umsInfo'] as $matchStaffID => $member){

		$matchStaffID = (int)$matchStaffID;
		$rosterID = (int)$member['rosterID'];
		$logisticsRoleID = (int)$member['logisticsRoleID'];

		if($matchStaffID < 0){
			if($rosterID != 0 && $logisticsRoleID != 0){

				$sql = "SELECT COUNT(*) AS numConflicts
						FROM logisticsStaffMatches
						WHERE matchID = {$matchID}
						AND rosterID = {$rosterID}";
				$isConflict = (bool)mysqlQuery($sql, SINGLE, 'numConflicts');
				
				if($isConflict){
					$name = getFighterName($rosterID);
					$role = logistics_getRoleName($logisticsRoleID);
					setAlert(USER_ERROR,"You can not assign the same person to a match twice.<BR>
						<strong>{$name}</strong> not added as <em>{$role}</em>.");
					$anyErrors = true;
					continue;
				}

				$sql = "INSERT INTO logisticsStaffMatches
						(matchID, rosterID, logisticsRoleID)
						VALUES 
						({$matchID},{$rosterID},{$logisticsRoleID})";
				mysqlQuery($sql, SEND);
			}
		} else {
			if($rosterID == 0 || $logisticsRoleID == 0){
				$sql = "DELETE FROM logisticsStaffMatches
						WHERE matchStaffID = {$matchStaffID}";
				mysqlQuery($sql, SEND);
			} else {
				$sql = "UPDATE logisticsStaffMatches
						SET rosterID = {$rosterID}, logisticsRoleID = {$logisticsRoleID}
						WHERE matchStaffID = {$matchStaffID}";
				mysqlQuery($sql, SEND);
			}
		}
	}

	if($anyErrors == false){
		setAlert(USER_ALERT,"Match staff updated.");
	}

	
}

/******************************************************************************/

function logisticsAssignTournamentToRing($assignInfo, $overidePlaceID = null){

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

// Assign places to groups
	if(isset($assignInfo['groupIDs'])){

		foreach($assignInfo['groupIDs'] as $groupID => $locationID){

			if($overidePlaceID !== null){
				$locationID = (int)$overidePlaceID;
			} else {
				$locationID = (int)$locationID;
			}
			$groupID = (int)$groupID;



			// Loop through all the group matches
			$sql = "SELECT matchID
					FROM eventMatches
					WHERE groupID = {$groupID}";
			$groupMatches = mysqlQuery($sql, SINGLES, 'matchID');

			if($groupMatches != null){
				foreach($groupMatches as $matchID){
					$matchID = (int)$matchID;

					if($locationID == 0){
						$sql = "DELETE FROM logisticsLocationsMatches
								WHERE matchID = {$matchID}";
						mysqlQuery($sql, SEND);
					} else {

						$sql = "SELECT matchLocationID
								FROM logisticsLocationsMatches
								WHERE matchID = {$matchID}";
						$matchLocationID = (int)mysqlQuery($sql, SINGLE, 'matchLocationID');
						if($matchLocationID == 0){
							$sql = "INSERT INTO logisticsLocationsMatches
									(locationID, matchID)
									VALUES
									({$locationID},{$matchID})";
							mysqlQuery($sql, SEND);
						} else {
							$sql = "UPDATE logisticsLocationsMatches
									SET locationID = {$locationID}
									WHERE matchLocationID = {$matchLocationID}";
							mysqlQuery($sql, SEND);
						}
					}
				}
			}


			// Modify the group informaiton
			if($locationID == 0){
				$locationID = 'NULL';
			}

			
			$sql = "UPDATE eventGroups
					SET locationID = {$locationID}
					WHERE groupID = {$groupID}";
			mysqlQuery($sql, SEND);

		}
	}


// Assign places to matches
	if(isset($assignInfo['matchIDs'])){

		foreach($assignInfo['matchIDs'] as $matchID => $locationID){

			if($overidePlaceID !== null){
				$locationID = (int)$overidePlaceID;
			} else {
				$locationID = (int)$locationID;
			}

			if($locationID == 0){
				$sql = "DELETE FROM logisticsLocationsMatches
						WHERE matchID = {$matchID}";
				mysqlQuery($sql, SEND);
				continue;
			}

			$sql = "SELECT matchLocationID
					FROM logisticsLocationsMatches
					WHERE matchID = {$matchID}";
			$matchLocationID = mysqlQuery($sql, SINGLE, 'matchLocationID');

			if($matchLocationID == null){
				$sql = "INSERT INTO logisticsLocationsMatches
						(locationID, matchID)
						VALUES
						($locationID, $matchID)";
				mysqlQuery($sql, SINGLE);
			} else {
				$sql = "UPDATE logisticsLocationsMatches
						SET locationID = {$locationID}
						WHERE matchID = {$matchID}";
				mysqlQuery($sql, SINGLE);
			}
		}
	}

}

/******************************************************************************/

function recordScores($allFighterStats, $tournamentID, $groupSet = null){
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	
	if($groupSet == null){ $groupSet = $_SESSION['groupSet']; }
	if($groupSet == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No groupSet in recordScores()";
		return; 
	}


	if(isTeams($tournamentID)){
		if(isMatchesByTeam($tournamentID) == false){
			$teamString = "AND isTeam = 0";
		} else {
			$teamString = '';
		}
	} else {
		$teamString = "AND isTeam = 0";
	}


// Find out what exists in the DB so it is known what needs to be updated vs inserted
	$sql = "SELECT standingID, rosterID, groupID
			FROM eventStandings
			INNER JOIN eventRoster USING(rosterID)
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'pool'
			AND groupSet = {$groupSet}
			{$teamString}";
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
					('pool', {$tournamentID}, {$groupSet},{$valueString})";

			mysqlQuery($sql, SEND);
		}
	}

	if(isCumulative($groupSet, $tournamentID) && $groupSet > 1){
		$lastGroupSet = $groupSet - 1;


		$sql = "SELECT rosterID, matches, wins, losses, ties, pointsFor, pointsAgainst,
					hitsFor, hitsAgainst, afterblowsFor, afterblowsAgainst, doubles,
					noExchanges, AbsPointsFor, AbsPointsAgainst, AbsPointsAwarded, numPenalties, 
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
						AbsPointsAwarded 	= AbsPointsAwarded + {$score['AbsPointsAwarded']},
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
	
	if(ALLOW['EVENT_SCOREKEEP'] == false && ALLOW['EVENT_MANAGEMENT'] == false){return;}
	
	if($tournamentID == null){
		setAlert(SYSTEM,"No tournamentID in removeTournamentPlacings()");
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

	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}

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

	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}

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

	if(ALLOW['EVENT_MANAGEMENT'] == FALSE){
		return;
	}
	
	$eventID = (int)$_SESSION['eventID'];
	if($eventID == 0){ 
		setAlert(SYSTEM, "No eventID in updateDisplaySettings()");
		return;
	}
	
	foreach($_POST['displaySettings'] as $field => $value){
		$sql = "UPDATE eventDefaults
				SET $field = '$value'
				WHERE eventID = {$eventID}";

		mysqlQuery($sql, SEND);
		
	}
	
}

/******************************************************************************/

function updateStaffRegistrationSettings($info){

	if(ALLOW['EVENT_MANAGEMENT'] == FALSE){
		return;
	}
	
	$eventID = (int)$info['eventID'];
	$addStaff= (int)$info['addStaff'];
	$staffCompetency = (int)$info['staffCompetency'];
	$staffHoursTarget = (int)$info['staffHoursTarget'];
	$limitStaffConflicts = (int)$info['limitStaffConflicts'];

	$sql = "UPDATE eventDefaults
			SET addStaff = {$addStaff}, staffCompetency = {$staffCompetency},
			staffHoursTarget = {$staffHoursTarget}, limitStaffConflicts = {$limitStaffConflicts}
			WHERE eventID = {$eventID}";
	mysqlQuery($sql, SEND);


	foreach($info['competencyCheck'] as $roleID => $competency){
		$roleID = (int)$roleID;
		$competency = (int)$competency;

		if($competency == 0){
			$sql = "DELETE FROM logisticsStaffCompetency
					WHERE eventID = {$eventID}
					AND logisticsRoleID = {$roleID}";
			mysqlQuery($sql, SEND);
		} else {
			$sql = "SELECT staffCompetencyID
					FROM logisticsStaffCompetency
					WHERE eventID = {$eventID}
					AND logisticsRoleID = {$roleID}";
			$ID = (int)mysqlQuery($sql, SINGLE, 'staffCompetencyID');

			if($ID == 0){
				$sql = "INSERT INTO logisticsStaffCompetency
						(eventID, logisticsRoleID, staffCompetency)
						VALUES 
						({$eventID},{$roleID},{$competency})";
				mysqlQuery($sql, SEND);
			} else {
				$sql = "UPDATE logisticsStaffCompetency
						SET staffCompetency = {$competency}
						WHERE eventID = {$eventID}
						AND logisticsRoleID = {$roleID}";
				mysqlQuery($sql, SEND);
			}
		}

	}


	
}

/******************************************************************************/

function updateEventDefaults(){
	
	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}
	
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

function updateEventInformation($newEventInfo,$eventID){

	if(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['SOFTWARE_ASSIST'] == false){
		return;
	}

	$startDate = $newEventInfo['startDate'];
	$endDate = $newEventInfo['endDate'];

	// Check that the start date isn't after the end date.
	$eStart = date_create($startDate);
	$eEnd= date_create($endDate);
	$diff = date_diff($eStart,$eEnd);
	$num = (int)$diff->format('%R%a');
	if($num < 0){
		$endDate = $startDate;
	}

	$sql = "UPDATE systemEvents SET
			eventName = ?, eventStartDate = ?, eventEndDate = ?
			WHERE eventID = ?";

	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	$bind = mysqli_stmt_bind_param($stmt, "sssi", 
									$newEventInfo['eventName'],
									$startDate,
									$endDate,
									$eventID);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);	

}


/******************************************************************************/

function updatePasswords($passwordData){

	// Check if a valid password was provided.
	if($passwordData['newPassword'] == ''){
		setAlert(USER_ERROR,"No password entered. <BR><strong>Password not updated.</strong>");
		return;
	}

	$eventID = $_SESSION['eventID'];
	$passwordValid = checkPassword($passwordData['passwordVerification'], 
									$_SESSION['userName'], $eventID);
	if($passwordValid == false){
		setAlert(USER_ERROR,"Incorrect Password<BR><strong>Password not changed</strong>");
		return;	
	}
	
	
	// Update Database
	$passHash = password_hash($passwordData['newPassword'], PASSWORD_DEFAULT);

	if(@$passwordData['userName'] == 'eventStaff' || @$passwordData['userName'] == 'eventOrganizer'){
		// If an event organizer is updating their password.
		// 'userName' field may not exist, treat as empty/null

		if($eventID == null){
			setAlert(USER_ERROR,"No event set.
								<BR><strong>Password not updated.</strong>");
			return;
		}

		if(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['SOFTWARE_ASSIST'] == false){
			setAlert(USER_ERROR,"You are not logged in to the correct account.
								<BR><strong>Password not updated.</strong>");
			return;
		}

		if($passwordData['userName'] == 'eventStaff'){
			$passField = 'staffPassword';
		} elseif($passwordData['userName'] == 'eventOrganizer') {
			$passField = 'organizerPassword';
		} else {
			setAlert(SYSTEM_ERROR,"Invalid userName in updatePasswords()
					<BR><strong>Password not updated.</strong>");
			return;
		}


		$sql = "UPDATE systemEvents
				SET {$passField} = '{$passHash}'
				WHERE eventID = {$eventID}";
	} else {
		// If a user is updating their password

		$userID = @(int)$passwordData['userID'];
		$sql = "SELECT userName
				FROM systemUsers
				WHERE userID = {$userID}";
		$tableUserName = mysqlQuery($sql, SINGLE, 'userName');

		if($tableUserName == null){
			setAlert(USER_ERROR,"Invalid user selected.<BR><strong>Password not changed</strong>");
			return;
		}

		if($tableUserName != $_SESSION['userName']
			&& ALLOW['SOFTWARE_ADMIN'] == false){
			setAlert(USER_ERROR,"Can't change password for that user.<BR><strong>Password not changed</strong>");
			return;
		}

		if($passwordData['newPassword'] != $passwordData['newPassword2']){
			setAlert(USER_ERROR,"Two passwords do not match.<BR><strong>Password not changed</strong>");
			return;
		}

		$sql = "UPDATE systemUsers
				SET password = '{$passHash}'
				WHERE userID = {$userID}";
		
	}

	mysqlQuery($sql, SEND);	
	setAlert(USER_ALERT,"Password Updated");
}

/******************************************************************************/

function updateEventTournaments(){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}
	
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
			if($info['maximumPoints'] == ''){$info['maximumPoints'] = 'null';}
			if(!isset($info['isNotNetScore'])){$info['isNotNetScore'] = 0;}
			if($info['basePointValue'] == ''){$info['basePointValue'] = 0;}
			if($info['logicMode'] != "NULL"){$info['logicMode'] = "'".$info['logicMode']."'";}
			if($info['formatID'] != FORMAT_MATCH){
				$info['numSubMatches'] = 0;
			}
			if($info['numSubMatches'] < 2){
				$info['numSubMatches'] = 0;
			}
			$info['timeLimit'] = (int)$info['timeLimit'];

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
					maxDoubleHits, formatID, tournamentRankingID,
					maximumExchanges, maximumPoints, maxPointSpread, 
					isCuttingQual, useTimer, useControlPoint,
					isNotNetScore, basePointValue, overrideDoubleType, isPrivate, 
					isReverseScore, isTeams, logicMode, poolWinnersFirst, limitPoolMatches,
					checkInStaff, numSubMatches, subMatchMode, timeLimit, requireSignOff
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
					{$info['formatID']},
					{$info['tournamentRankingID']},
					{$info['maximumExchanges']},
					{$info['maximumPoints']},
					{$info['maxPointSpread']},
					{$info['isCuttingQual']},
					{$info['useTimer']},
					{$info['useControlPoint']},
					{$info['isNotNetScore']},
					{$info['basePointValue']},
					{$info['overrideDoubleType']},
					{$info['isPrivate']},
					{$info['isReverseScore']},
					{$info['isTeams']},
					{$info['logicMode']},
					{$info['poolWinnersFirst']},
					{$info['limitPoolMatches']},
					{$info['checkInStaff']},
					{$info['numSubMatches']},
					{$info['subMatchMode']},
					{$info['timeLimit']},
					{$info['requireSignOff']}
					)";

			mysqlQuery($sql, SEND);
			$tournamentID = mysqli_insert_id($GLOBALS["___mysqli_ston"]);
			
			$newName = getTournamentName($tournamentID);
			$_SESSION['alertMessages']['userAlerts'][] = "Created tournament: <strong>{$newName}</strong>";
			
			$_SESSION['tournamentID'] = $tournamentID;
			break;
	
	// Update an existing tournament		
		case 'update':
		
			$tournamentID = $_POST['modifyTournamentID'];
			$_SESSION['tournamentID'] = $tournamentID;
			$_SESSION['updatePoolStandings'][$tournamentID] = ALL_GROUP_SETS;	

			if($info['isReverseScore'] > REVERSE_SCORE_NO){
				if(isset($info['doubleTypeID']) == false){
					$info['doubleTypeID'] = NO_AFTERBLOW;
				} elseif(@$info['doubleTypeID'] == DEDUCTIVE_AFTERBLOW){
					$info['doubleTypeID'] = FULL_AFTERBLOW;
					$info['isNotNetScore'] = 1;
					setAlert(USER_ERROR,"Reverse sScore mode is not compatable
					 with deductive afterblow scoring. 
					 <BR>Afterblow type has been changed to <u>Full Afterblow</u>
					 with <u>Use Net Points</u> option set to <i>No</i>.");
				} elseif (@$info['doubleTypeID'] == FULL_AFTERBLOW && $info['isNotNetScore'] == 0){
					$info['isNotNetScore'] = 1;
					setAlert(USER_ERROR,"Reverse Score mode only functions without
					<u>Use Net Points</u> enabled. <BR><u>Use Net Points</u> has been set to <i>No</i>.");
				}

			}

			$wasEntriesByTeam = isEntriesByTeam($tournamentID);
			$wasMatchesByTeam = isMatchesByTeam($tournamentID);
			$wasTeamTournament = isTeams($tournamentID);

		
			
		// Construct SQL statement to do all updates

			$sql = "UPDATE eventTournaments SET ";
			foreach($info as $field => $data){
				if($data == null){continue;}
				if(!is_numeric($data) && $data != "NULL"){
					$data = "'".$data."'";
				}
				$sql .= "{$field} = {$data}, ";
			}
			$sql = rtrim($sql,', \t\n');
			$sql .= " WHERE tournamentID = {$tournamentID}";
			mysqlQuery($sql, SEND);

		// Delete groups if the elim type has changed		
			$formatID = $info['formatID'];

			$whereStatement = '';
			if($formatID == FORMAT_MATCH){
				$whereStatement .= "AND groupType != 'pool' AND groupType != 'elim'";
			}
			if($formatID == FORMAT_SOLO){
				$whereStatement .= "AND groupType != 'round'";
			}
			
			$sql = "DELETE FROM eventGroups
					WHERE tournamentID = {$tournamentID}
					{$whereStatement}";
					
			mysqlQuery($sql, SEND);	

		// Clean up if switching number of pool sets
			if($formatID != FORMAT_MATCH && $formatID != FORMAT_SOLO){
				$_SESSION['groupSet'] = 1;
				$sql = "UPDATE eventTournaments 
						SET numGroupSets = 1 
						WHERE tournamentID = {$tournamentID}";
				mysqlQuery($sql, SEND);

				$sql = "DELETE FROM eventAttributes
						WHERE tournamentID = {$tournamentID}
						AND attributeGroupSet > 1";
				mysqlQuery($sql,SEND);
			}

	

		// Clean up data if switching between team modes
			if(isEntriesByTeam($tournamentID) == true && $wasEntriesByTeam == false){
				$sql = "DELETE  eventGroupRoster FROM eventGroupRoster
						INNER JOIN eventGroups USING(groupID)
						WHERE tournamentID = {$tournamentID}";
				mysqlQuery($sql, SEND);
			} elseif(isEntriesByTeam($tournamentID) == false && $wasEntriesByTeam == true){
				$sql = "DELETE  eventGroupRoster FROM eventGroupRoster
						INNER JOIN eventGroups USING(groupID)
						WHERE tournamentID = {$tournamentID}";
				mysqlQuery($sql, SEND);
			}
			if(isTeams($tournamentID) == false){
				$sql = "DELETE eventRoster FROM eventRoster
						INNER JOIN eventTournamentRoster USING(rosterID)
						WHERE tournamentID = {$tournamentID}
						AND isTeam = 1";
				mysqlQuery($sql, SEND);
			}

		// Update all the matches (ie if the score mode has changed)
			$sql = "SELECT matchID
					FROM eventMatches
					INNER JOIN eventGroups USING(groupID)
					WHERE tournamentID = {$tournamentID}";
			$matchList = mysqlQuery($sql, SINGLES);

			foreach($matchList as $match){
				$matchInfo = getMatchInfo($match);
				updateMatch($matchInfo);
			}

			$_SESSION['checkEvent'][$tournamentID]['all'] = true;

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

	if($_SESSION['tournamentID'] == $tournamentID){
		$_SESSION['tournamentID'] = '';
	}
			
}

/******************************************************************************/

function updateExistingSchool(){
	if(ALLOW['SOFTWARE_ASSIST'] == false){
		$_SESSION['alertMessages']['userErrors'] == "Sorry, only Software Administrators and Assistants can 
		edit existing school information.";
		return;
	}
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
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){return;}
	
	$groupID = $_POST['groupID'];
	
	// Clears the Match
	if($_POST['updateBracket'] == 'clearMatches'){
	
		foreach(@(array)$_POST['selectedBracketMatches']['matchIDs'] as $matchID => $finalists){
		
			$notNull = [];
			$notNull[] = 'groupID';
			$notNull[] = 'bracketPosition';
			$notNull[] = 'bracketLevel';
			$notNull[] = 'placeholderMatchID';
			$matchID = (int)$matchID;
			
			mysqlSetRecordToDefault('eventMatches',
				"WHERE matchID = {$matchID} OR placeholderMatchID = {$matchID}", $notNull);
			
			clearExchanges($matchID,'all');

			// Deal with sub-matches
			$sql = "SELECT matchID
					FROM eventMatches
					WHERE placeholderMatchID = {$matchID}";
			$matchList = mysqlQuery($sql, SINGLES);

			foreach((array)$matchList as $mID){
				clearExchanges($mID,'all');
			}

			$sql = "DELETE FROM logisticsStaffMatches
					WHERE matchID = {$matchID}";
			mysqlQuery($sql,SEND);
			
		}
	}
	
	// Adds new fighters to the match
	
	if($_POST['updateBracket'] == 'newFighters'){
		foreach((array)$_POST['newFinalists'] as $matchID => $finalists){
			if(!empty($finalists[1]) && !empty($finalists[2])){
				$sql = "DELETE eventExchanges FROM eventExchanges
						INNER JOIN eventMatches USING(matchID)
						WHERE matchID = {$matchID}
						OR placeholderMatchID = {$matchID}";
				mysqlQuery($sql, SEND);
			}
			
			if(!empty($finalists[1])){
				$sql = "UPDATE eventMatches
						SET fighter1ID = {$finalists[1]}
						WHERE matchID = {$matchID}
						OR placeholderMatchID = {$matchID}";
				mysqlQuery($sql, SEND);
			}
			
			if(!empty($finalists[2])){
				$sql = "UPDATE eventMatches
						SET fighter2ID = {$finalists[2]}
						WHERE matchID = {$matchID}
						OR placeholderMatchID = {$matchID}";
				mysqlQuery($sql, SEND);	
			}

		}
	}
	
}

/******************************************************************************/

function updateHemaRatingsInfo($fighters){

	if(ALLOW['SOFTWARE_ASSIST'] == false){ return;}


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

function updateIgnoredFighters($manageFighterData){
// If a fighter is set to un-ignore it will unignore 
// all matches, even if they were ignored individualy
	
	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}
	$tournamentID = $manageFighterData['tournamentID'];
	
	if($tournamentID == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No tournamentID in 'updateIgnoredFighters()'";
		return;
	}


	$rosterIDsToIgnore = [];
	foreach($manageFighterData['rosterList'] as $rosterID => $fighterData){
		$ignoreAtSet = (int)@$fighterData['ignoreAtSet']; // Might not be set. Treat this as 0
		$stopAtSet = (int)@$fighterData['stopAtSet']; // Might not be set. Treat this as 0
		$soloAtSet = (int)@$fighterData['soloAtSet']; // Might not be set. Treat this as 0

		if($ignoreAtSet == 0 && $stopAtSet == 0 && $soloAtSet == 0){
			// This means there is no ignores on the fighter.
			// Delete any entries they might have had.

			$sql = "DELETE FROM eventIgnores
					WHERE tournamentID = {$tournamentID}
					AND rosterID = {$rosterID}";
			mysqlQuery($sql, SEND);
			continue;
		}

		$rosterIDsToIgnore[$ignoreAtSet][] = $rosterID;

		if($ignoreAtSet != 0 && $stopAtSet == 0){
			$stopAtSet = 1;
		}

		$sql = "SELECT ignoreID
				FROM eventIgnores
				WHERE tournamentID = {$tournamentID}
				AND rosterID = {$rosterID}";
		$ignoreID = (int)mysqlQuery($sql, SINGLE, 'ignoreID');

		
		if($ignoreID == 0){
			$sql = "INSERT INTO eventIgnores
					(tournamentID, rosterID, ignoreAtSet, stopAtSet, soloAtSet)
					VALUES
					({$tournamentID},{$rosterID},{$ignoreAtSet},{$stopAtSet},{$soloAtSet})";	
		} else {
			$sql = "UPDATE eventIgnores
					SET ignoreAtSet = {$ignoreAtSet}, stopAtSet = {$stopAtSet}, soloAtSet = {$soloAtSet}
					WHERE ignoreID = {$ignoreID}";
		}
		mysqlQuery($sql, SEND);
	}

	$numGroupSets = getNumGroupSets($tournamentID);
	$checkTeams = false;
	if(isTeamLogic($tournamentID)){
		$checkTeams = true;
	}

	for($groupSet = $numGroupSets; $groupSet > 0; $groupSet--){

		if(isset($rosterIDsToIgnore[$groupSet])){
			$ignoreList = implode(",",$rosterIDsToIgnore[$groupSet]);

			if($checkTeams){
				$sql = "SELECT rosterID
						FROM  eventRoster
						WHERE rosterID IN ($ignoreList)
						AND isTeam = 1";
				$ignoresAreTeams = mysqlQuery($sql, SINGLES, 'rosterID');

				foreach($ignoresAreTeams as $teamID){
					$sql = "SELECT rosterID
							FROM eventTeamRoster
							WHERE teamID = {$teamID}
							AND memberRole = 'member'";
					$teamMembers = mysqlQuery($sql, SINGLES, 'rosterID');

					foreach((array)$teamMembers as $rosterID){
						$rosterIDsToIgnore[$groupSet][] = $rosterID;
					}
				}
				$ignoreList = implode(",",$rosterIDsToIgnore[$groupSet]);
			}
		} else {
			$ignoreList = '0';
		}

		$sql = "UPDATE eventMatches
				INNER JOIN eventGroups USING(groupID)
				SET ignoreMatch = 1
				WHERE tournamentID = {$tournamentID}
				AND groupSet >= {$groupSet}
				AND (groupType = 'pool' OR groupType = 'round')
				AND ((fighter1ID IN ({$ignoreList})) OR (fighter2ID IN ({$ignoreList})) )";
		mysqlQuery($sql, SEND);

		$sql = "UPDATE eventMatches
				INNER JOIN eventGroups USING(groupID)
				SET ignoreMatch = 0
				WHERE tournamentID = {$tournamentID}
				AND groupSet = {$groupSet}
				AND (groupType = 'pool' OR groupType = 'round')
				AND ((fighter1ID NOT IN ({$ignoreList})) AND (fighter2ID NOT IN ({$ignoreList})) )";
		mysqlQuery($sql, SEND);
	}

	$_SESSION['updatePoolStandings'][$tournamentID] = ALL_GROUP_SETS;
	
	$_SESSION['alertMessages']['userAlerts'][] = "Updated";

}

/******************************************************************************/

function checkInFighters($checkInData){

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

	if(isset($checkInData['event']) == true){
		foreach($checkInData['event'] as $eventData){

			foreach($eventData as $rosterID => $fighterData){
				$waiver = (int)$fighterData['waiver'];
				$checkin = (int)$fighterData['checkin'];
				$rosterID = (int)$rosterID;

				$sql = "UPDATE eventRoster
						SET eventWaiver = {$waiver}, eventCheckIn = {$checkin}
						WHERE rosterID = {$rosterID}";
				mysqlQuery($sql, SEND);
			}
		}
	}

	if(isset($checkInData['tournament']) == true){
		foreach($checkInData['tournament'] as $tournamentID => $tournamentData){
			$tournamentID = (int)$tournamentID;

			foreach($tournamentData as $rosterID => $fighterData){
				$gearcheck = (int)$fighterData['gearcheck'];
				$checkin = (int)$fighterData['checkin'];
				$rosterID = (int)$rosterID;

				$sql = "UPDATE eventTournamentRoster
						SET tournamentGearCheck = {$gearcheck}, 
							tournamentCheckIn = {$checkin}
						WHERE tournamentID = {$tournamentID}
						AND rosterID = {$rosterID}";
				mysqlQuery($sql, SEND);
			}
		}
	}

	if(isset($checkInData['group']) == true){
		foreach($checkInData['group'] as $groupID => $groupData){
			$groupID = (int)$groupID;

			foreach($groupData as $rosterID => $fighterData){
				$gearcheck = (int)$fighterData['gearcheck'];
				$checkin = (int)$fighterData['checkin'];
				$rosterID = (int)$rosterID;

				$sql = "UPDATE eventGroupRoster
						SET groupGearCheck = {$gearcheck}, 
							groupCheckIn = {$checkin}
						WHERE groupID = {$groupID}
						AND rosterID = {$rosterID}";
				mysqlQuery($sql, SEND);
			}

			$_SESSION['jumpTo'] = "group{$groupID}";
		}
	}

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
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}

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
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
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

function updatePoolMatchList($ID, $type, $tIdIn = null){

	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
	switch($type){
		case 'group':
		case 'pool':
			$type = 'group';
			$groupID = $ID;
			$tournamentID = getGroupTournamentID($groupID);
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
		$tournaments[] = $tournamentID;
	}

	foreach((array)$tournaments as $tournamentID){

		if($type == 'event'){
			if(!isPools($tournamentID)){continue;}
		}

		$tournamentID = (int)$tournamentID;

		// If the organizer wants it so that not everyone in the pool fights each other.
		$maxFights = (int)getTournamentPoolMatchLimit($tournamentID);

	// Initialize sub-matches
		$numSubMatches = (int)getNumSubMatches($tournamentID);

		if($numSubMatches < 2){
			$numSubMatches = 0;

			$sql = "UPDATE eventMatches
					INNER JOIN eventGroups USING(groupID)
					SET isPlaceholder = 0
					WHERE tournamentID = {$tournamentID}
					AND placeholderMatchID IS NULL";
			mysqlQuery($sql, SEND);

			$isPlaceholder = 0;
		} else {
			$sql = "UPDATE eventMatches
					INNER JOIN eventGroups USING(groupID)
					SET isPlaceholder = 1
					WHERE tournamentID = {$tournamentID}
					AND placeholderMatchID IS NULL";
			mysqlQuery($sql, SEND);

			$isPlaceholder = 1;
		}

		$sql = "DELETE eventMatches FROM eventMatches
				INNER JOIN eventGroups USING(groupID)
				WHERE placeholderMatchID IS NOT NULL
				AND matchNumber > {$numSubMatches}
				AND tournamentID = {$tournamentID}";
		mysqlQuery($sql, SEND);

	// Get pool data
		if($type == 'event' || $type == 'tournament'){
			$pools = getPools($tournamentID, 'all');
		} else {
			$pools = getGroupInfo($groupID);
			$tournamentID = $tIdIn;
		}

		$ignores = [];

		if(isEntriesByTeam($tournamentID) == true && isMatchesByTeam($tournamentID) == false){
			// In this case pools are populated by teams, but matches have to be constructed on
			// an individual basis.
			$matchOrderType = "TeamVsTeam";
			$ignores = getIgnores($tournamentID);
			$poolRosters = getPoolTeamRosters($tournamentID,'all');
		} elseif($maxFights > 0) {
			$matchOrderType = "LimitedFights";
			$poolRosters = getPoolRosters($tournamentID, 'all');
		} else {
			$matchOrderType = "Normal";
			$poolRosters = getPoolRosters($tournamentID, 'all');
		}
			
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

			switch($matchOrderType){
				case 'TeamVsTeam':
					$matchOrder = makePoolMatchOrderTeamVsTeam($poolRosters[$groupID], $ignores, $pool['groupSet']);
					break;
				case 'LimitedFights':
					$matchOrder = getPoolMatchOrder($groupID, $poolRoster);
					$matchOrder = whittlePoolMatchOrder($groupID, $poolRoster, $matchOrder, $maxFights);

					//$matchOrder = makePoolMatchOrderLimitedFights($groupID, $poolRoster, $maxFights);
					break;
				case 'Normal':
				default:
					$matchOrder = getPoolMatchOrder($groupID, $poolRoster);
					break;
			}
			
			foreach((array)$matchOrder as $matchNumber => $matchInfo){
				$fighter1ID = (int)$matchInfo['fighter1ID'];
				$fighter2ID = (int)$matchInfo['fighter2ID'];
				
			//Check if match already exists
				$sql = "SELECT matchID, matchNumber, winnerID
						FROM eventMatches
						WHERE groupID = {$groupID}
						AND fighter1ID = {$fighter1ID}
						AND fighter2ID = {$fighter2ID}
						AND placeholderMatchID IS NULL";
				$matchAlreadyExists = mysqlQuery($sql, SINGLE);
				
				if($matchAlreadyExists != null){
					$matchID = (int)$matchAlreadyExists['matchID'];
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

					if($result['scoringID'] == ''){
						$result['scoringID'] = 'null';
					}
					
					if($result['scoringID'] != $matchAlreadyExists['winnerID']){
						$sql = "UPDATE eventMatches
								SET winnerID = {$result['scoringID']}
								WHERE matchID = {$matchID}";
						mysqlQuery($sql, SEND); 
					}
					
					$goodMatchesInPool[$groupID][] = $matchID;

					updateSubMatches($matchID, $numSubMatches, $fighter1ID, $fighter2ID, $groupID);
					
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

					updateSubMatches($matchID, $numSubMatches, $fighter1ID, $fighter2ID, $groupID);
					continue;
				}
				
				
			//Create a new match
				$sql = "INSERT INTO eventMatches
						(groupID, matchNumber, fighter1ID, fighter2ID, isPlaceholder)
						VALUES
						({$groupID}, {$matchNumber}, {$fighter1ID}, {$fighter2ID}, {$isPlaceholder})";
				$matchID = mysqlQuery($sql, INDEX);
				$goodMatchesInPool[$groupID][] = $matchID;

				updateSubMatches($matchID, $numSubMatches, $fighter1ID, $fighter2ID, $groupID);
			}
			
			$whereStatement = '';
			foreach((array)$goodMatchesInPool[$groupID] as $matchID){
				$whereStatement .= "AND matchID != {$matchID} ";
			}
			
			$sql = "DELETE FROM eventMatches
					WHERE groupID = {$groupID}
					AND placeholderMatchID IS NULL
					{$whereStatement}";
			mysqlQuery($sql, SEND);

		}
	}

	
}

/******************************************************************************/

function updateSubMatches($matchID, $numSubMatches, $fighter1ID, $fighter2ID, $groupID){


	if($numSubMatches <= 1){
		return;
	}

	$matchID = (int)$matchID;
	$groupID = (int)$groupID;

	$sql = "SELECT matchID, matchNumber, fighter1ID, fighter2ID
			FROM eventMatches
			WHERE placeholderMatchID = {$matchID}
			ORDER BY matchNumber ASC";
	$subMatches = mysqlQuery($sql, ASSOC);

	$matchNum = 1;
	$goodMatches = 0;

	foreach((array)$subMatches as $match){

		while($matchNum < $match['matchNumber']){
			
			$sql = "INSERT INTO eventMatches
					(groupID, matchNumber, fighter1ID, fighter2ID, placeholderMatchID)
					VALUES
					({$groupID},{$matchNum},{$fighter1ID},{$fighter2ID},{$matchID})";
			mysqlQuery($sql, SEND);

			$goodMatches = $matchNum;
			$matchNum++;
		}

		$subMatchID = (int)$match['matchID'];

		if($matchNum > $match['matchNumber']){
			$sql = "DELETE FROM eventMatches
					WHERE matchID = {$subMatchID}";
			mysqlQuery($sql, SEND);
			continue;
		}

		if(    $fighter1ID == $match['fighter2ID']
			&& $fighter2ID == $match['fighter1ID']){

			$f1ID = (int)$match['fighter2ID'];
			$f2ID = (int)$match['fighter1ID'];

			$sql = "UPDATE eventMatches
					SET fighter1ID = {$f1ID}, fighter2ID = {$f2ID}
					WHERE matchID = {$subMatchID}";
			mysqlQuery($sql, SEND);

			$goodMatches = $matchNum;

		} elseif(   $fighter1ID == $match['fighter1ID']
				 && $fighter2ID == $match['fighter2ID']){

			// Good match. Do nothing.
			$goodMatches = $matchNum;

		} else {

			// Somthing weird. Delete the match and keep going.
			$sql = "DELETE FROM eventMatches
					WHERE matchID = {$subMatchID}";
			mysqlQuery($sql, SEND);

		}

		$matchNum++;
		
	}

	// Deal with any matches not created
	while($goodMatches < $numSubMatches){

		$goodMatches++;
		$sql = "INSERT INTO eventMatches
				(groupID, matchNumber, fighter1ID, fighter2ID, placeholderMatchID)
				VALUES
				({$groupID},{$goodMatches},{$fighter1ID},{$fighter2ID},{$matchID})";
		mysqlQuery($sql, SEND);
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

		$numExistingSets = getNumGroupSets($tournamentID);

		$sql = "UPDATE eventTournaments
				SET numGroupSets = {$_POST['numPoolSets']}
				WHERE tournamentID = {$tournamentID}";
		mysqlQuery($sql, SEND);
		
		if($_SESSION['groupSet'] > $_POST['numPoolSets']){
			$_SESSION['groupSet'] = 1;
		}

		for($groupSet = $numExistingSets + 1; $groupSet <= $_POST['numPoolSets']; $groupSet++){
			$sql = "INSERT INTO eventAttributes
					(attributeBool, tournamentID, attributeType, attributeGroupSet)
					VALUES
					(1,{$tournamentID},'cumulative',{$groupSet})";
			mysqlQuery($sql, SEND);
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

function updateRankByPool($info){

	$tournamentID = (int)$info['tournamentID'];
	$groupSet = (int)$info['groupSet'];
	$isUsed = (bool)$info['used'];
	$overlap = (int)$info['overlapSize'];

	$sql = "DELETE eventGroupRankings FROM eventGroupRankings
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND groupSet = {$groupSet}";
	mysqlQuery($sql, SEND);

	if($isUsed == true){
		foreach($info['groupIDs'] as $groupID => $groupRank){
			$groupID = (int)$groupID;
			$groupRank = (int)$groupRank;

			$sql = "INSERT INTO eventGroupRankings
					(groupID, groupRank, overlapSize)
					VALUES 
					({$groupID}, {$groupRank}, {$overlap})";
			mysqlQuery($sql, SEND);

		}
	}

	$_SESSION['updatePoolStandings'][$tournamentID] = $groupSet;


}

/******************************************************************************/

function updateStageOptions(){
	if(ALLOW['EVENT_MANAGEMENT'] == false){ return;}
	
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
	
	$sql = "DELETE FROM eventCutStandards
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);
	
	$sql = "INSERT INTO eventCutStandards
			(tournamentID, standardID, date)
			VALUES
			({$tournamentID}, {$standard}, '{$date}')";
	mysqlQuery($sql, SEND);
	
	
}

/******************************************************************************/

function updateTournamentComponents($componentData){

	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}

	$tournamentID = (int)$componentData['tournamentID'];
	$isExclusive = (int)$componentData['isExclusive'];

	$wasRosterComponents = (bool)count(getTournamentComponents($tournamentID,null,true));
	$isRosterComponents = false;

	foreach($componentData['tournamentIDs'] as $cTournamentID => $data){
		$cTournamentID = (int)$cTournamentID;
		// A tournament can't be a component of itself
		if($cTournamentID == $tournamentID){ continue;}
	
		$useResult = (int)$data['useResult'];
		$useRoster = (int)$data['useRoster'];

		if($useResult == 0 && $useRoster == 0){

			$sql = "DELETE FROM eventComponents
					WHERE tournamentID = {$tournamentID}
					AND componentTournamentID = {$cTournamentID}";
			mysqlQuery($sql, SEND);

		} else {

			$sql = "SELECT componentID
					FROM eventComponents
					WHERE tournamentID = {$tournamentID}
					AND componentTournamentID = {$cTournamentID}";
			$componentID = (int)mysqlQuery($sql, SINGLE, 'componentID');

			if($useRoster != 0){
				$isRosterComponents = true;
			}

			if($componentID == 0){
				$sql = "INSERT INTO eventComponents
						(tournamentID, componentTournamentID, useResult, useRoster, isExclusive)
						VALUES
						({$tournamentID}, {$cTournamentID}, {$useResult}, {$useRoster}, {$isExclusive})";
				mysqlQuery($sql, SEND);
			} else {
				$sql = "UPDATE eventComponents
						SET useRoster = {$useRoster}, 
							useResult = {$useResult}, 
							isExclusive = {$isExclusive}
						WHERE componentID = {$componentID}";
				mysqlQuery($sql, SEND);
			}

		}
	}

	if($wasRosterComponents == true && $isRosterComponents == false){
		$sql = "DELETE FROM eventTournamentRoster
				WHERE tournamentID = {$tournamentID}";
		mysqlQuery($sql, SEND);
	}

	updateTournamentFighterCounts();
	updateCompositeTournamentStandings($tournamentID);

	setAlert(USER_ALERT,"Tournament components updated.");

}

/******************************************************************************/

function updateTournamentFighterCounts($tournamentID = null){
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	if($tournamentID != null){
		$tournamentList[] = $tournamentID;
	} else {
		$tournamentList = getEventTournaments();
	}

	$tournamentID = (int)$tournamentID;
	
	foreach((array)$tournamentList as $tournamentID){

		$sql = "UPDATE eventTournaments
				SET numParticipants = (SELECT COUNT(*)
										FROM eventTournamentRoster
										WHERE tournamentID = {$tournamentID})
				WHERE tournamentID = {$tournamentID}";
		mysqlQuery($sql, SEND);
	}

// Check if there is a composite tournament
	$sql = "SELECT eventID
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$eventID = (int)mysqlQuery($sql, SINGLE, 'eventID');

	$compFormatNum = (int)FORMAT_COMPOSITE;
	$sql = "SELECT tournamentID
			FROM eventTournaments
			WHERE formatID = {$compFormatNum}
			AND eventID = {$eventID}";
	$compositeTournaments = mysqlQuery($sql, SINGLES, 'tournamentID');

	if($compositeTournaments == null){
		return;
	}

	// Process composite tournaments ------------------------
	foreach($compositeTournaments as $tournamentID){
		$tournamentID = (int)$tournamentID;

		if(isCompositeRosterManual($tournamentID) == true){
			continue;
		}

		$tournamentComponents = getTournamentComponents($tournamentID,null,true);
		
		if($tournamentComponents == null){
			// Delete any fighters not still in the tournaments
			$sql = "DELETE FROM eventTournamentRoster
					WHERE tournamentID = {$tournamentID}";
			mysqlQuery($sql, SEND);

			continue;
		}

		$tString = '';
		$numComponents = 0;
		$isExclusive = false;
		foreach($tournamentComponents as $component){
			$cTournamentID = (int)$component['cTournamentID'];
			if($tString != ''){
				$tString .= ", ";
			}
			$tString .= "{$cTournamentID}";
			$numComponents++;

			if($component['isExclusive'] != 0){
				$isExclusive = true;
			}
		}

		if($tString == ''){
			$tString = '0';
		}

		if($isExclusive == true){
			$compositeFormat = FORMAT_COMPOSITE;
			$exclusiveClause = "AND (	SELECT COUNT(*)
									FROM eventTournamentRoster as eTR3
									INNER JOIN eventTournaments USING(tournamentID)
									WHERE eTR1.rosterID = eTR3.rosterID 
									AND tournamentID != {$tournamentID}
									AND eventID = {$eventID}
									AND formatID != {$compositeFormat})
								= {$numComponents}";
		} else {
			$exclusiveClause = '';
		}

		$sql = "SELECT DISTINCT rosterID
				FROM eventTournamentRoster as eTR1
				WHERE (	SELECT COUNT(*)
						FROM eventTournamentRoster as eTR2
						WHERE eTR1.rosterID = eTR2.rosterID
						AND eTR2.tournamentID IN ({$tString}) )
					= {$numComponents}
				{$exclusiveClause}
				";

		$tournamentRoster = mysqlQuery($sql, SINGLES, 'rosterID');

		$rString = '';
		$numParticipants = 0;
		foreach($tournamentRoster as $rosterID){
			
			if($rString != ''){
				$rString .= ", ";
			}
			$rString .= "{$rosterID}";
			$numParticipants++;

			$sql = "SELECT COUNT(*) AS isIn
					FROM eventTournamentRoster
					WHERE tournamentID = {$tournamentID}
					AND rosterID = {$rosterID}";
			$isAlreadyEntered = (bool)mysqlQuery($sql, SINGLE, 'isIn');

			if($isAlreadyEntered == false){
				$sql = "INSERT INTO eventTournamentRoster
						(rosterID, tournamentID)
						VALUES
						({$rosterID}, {$tournamentID})";
				mysqlQuery($sql, SEND);
			}
		}

		if($rString == ''){
			$rString = '0';
		}

		// Delete any fighters not still in the tournaments
		$sql = "DELETE FROM eventTournamentRoster
				WHERE tournamentID = {$tournamentID}
				AND rosterID NOT IN ({$rString})";
		mysqlQuery($sql, SEND);

		// Finaly update the participant count.
		$sql = "UPDATE eventTournaments
		SET numParticipants = {$numParticipants}
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

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
