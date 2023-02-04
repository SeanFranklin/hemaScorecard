<?php
/*************************************
	Function Library
	Cutting Qualification Functions
	
	Functions relating to cuting qualification
**************************************/

/**********************************************************************/

function addNewCuttingQual_event(){
	
	$date = getEventEndDate($_SESSION['eventID']);
	$systemRosterID = $_POST['systemRosterID'];
	
	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){ return null;	}
	
	$standard = getCuttingStandard($tournamentID);
	$standardID = $standard['standardID'];
	$qualValue = 1;
	
	$sql = "INSERT INTO systemCutQualifications
			(systemRosterID, standardID, date, qualValue)
			VALUES
			(?,?,?,?)";
	
	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	$bind = mysqli_stmt_bind_param($stmt, "iisi", $systemRosterID, $standardID, $date, $qualValue);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	
}

/**********************************************************************/

function removeCuttingQual_event(){
	
	$qualID = (int)$_POST['qualID'];

	$sql = "DELETE FROM systemCutQualifications
			WHERE qualID = {$qualID}";
	mysqlQuery($sql, SEND);
	
}

/**********************************************************************/

function addNewCuttingQuals(){

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

	foreach($_POST['newQuals'] as $newQual){
		
		$systemRosterID = (int)$newQual['systemRosterID'];
		if($systemRosterID == 0){continue;}
		
		$date = $newQual['qualDate'];
		if($date == null){$date = date('Y-m-d H:i:s');}
		
		$sql = "INSERT INTO systemCutQualifications
			(systemRosterID, standardID, date, qualValue)
			VALUES
			(?,?,?,?)";
	
		$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
		$bind = mysqli_stmt_bind_param($stmt, "iisi", 
				$systemRosterID, $newQual['standardID'], $date, $newQual['qualValue']);
		$exec = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		
		
		
	}
	
}

/******************************************************************************/

function getCuttingQualificationsStandards(){

	$sql = "SELECT * FROM systemCutStandards";
	return mysqlQuery($sql, ASSOC);
}

/******************************************************************************/

function getCuttingQualificationsList($standardID, $date){
	
	$standardID = (int)$standardID;
	$nameOrder = NAME_MODE;
	
	// Returns the most recent quallification
	$sql = "SELECT Q.qualID, Q.systemRosterID, Q.date, Q.qualValue,
			S.standardName, S.standardCode
			FROM systemCutQualifications as Q
			
			INNER JOIN systemCutStandards as S ON Q.standardID = S.standardID
			INNER JOIN systemRoster as roster ON Q.systemRosterID = roster.systemRosterID

			WHERE date > '$date'
			AND Q.standardID = {$standardID}
			AND Q.qualID = (SELECT Q2.qualID
							FROM systemCutQualifications as Q2
							WHERE Q.systemRosterID = Q2.SystemRosterID
							AND Q2.standardID = {$standardID}
							ORDER BY Q2.date DESC
							LIMIT 1)
			
			ORDER BY Q.date DESC, roster.{$nameOrder} ASC";
	$list = mysqlQuery($sql, KEY, 'systemRosterID');
	
	
// For West Coast Qualification fighters with a qualValue of 5 should be added
// to the quals list regardless of date
	$sql = "SELECT standardCode
			FROM systemCutStandards
			WHERE standardID = {$standardID}";
	$code = mysqlQuery($sql, SINGLE, 'standardCode');
	
	if($code == 'westCoast'){
		$sql = "SELECT Q.qualID, Q.systemRosterID, Q.date, Q.qualValue,
				S.standardName, S.standardCode
				FROM systemCutQualifications as Q
				
				INNER JOIN systemCutStandards as S ON Q.standardID = S.standardID
				INNER JOIN systemRoster as roster ON Q.systemRosterID = roster.systemRosterID
				
				WHERE Q.standardID = {$standardID}
				AND qualValue = 5";
		$lifetimeList = mysqlQuery($sql, KEY, 'systemRosterID');
		
		
		foreach($lifetimeList as $systemRosterID => $data){
			if(!isset($list[$systemRosterID])){
				$list[$systemRosterID] = $data;
			}
			
		}
		
	}
	
	return $list;			
}

/******************************************************************************/

function getTournamentSystemRosterIDs(){
	
	$tournamentID = (int)$_SESSION['tournamentID'];
	if($tournamentID == 0){
		return;
	}
	
	$orderName = NAME_MODE;
	
	$sql = "SELECT sR.systemRosterID, eR.rosterID 
		FROM systemRoster as sR
		INNER JOIN eventRoster as eR ON eR.systemRosterID = sR.systemRosterID
		INNER JOIN eventTournamentRoster as eTR ON eTR.rosterID = eR.rosterID
		WHERE eTR.tournamentID = {$tournamentID}
		ORDER BY sR.{$orderName} ASC";
	return mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

function isCuttingQual($tournamentID){

	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){ return null;	}
	
	$sql = "SELECT isCuttingQual
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	return (bool)mysqlQuery($sql, SINGLE, 'isCuttingQual');
	
}

/******************************************************************************/

?>
