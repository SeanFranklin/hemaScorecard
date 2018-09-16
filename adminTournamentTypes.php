<?php
/*******************************************************************************
	Add Tournament Types
	
	Page for adding new tournament meta types (weapons/classes/materials/etc...)
	LOGIN: 
		- SUPER ADMIN required for access
		
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Add Tournament Types';
include('includes/header.php');

if(USER_TYPE < USER_SUPER_ADMIN){
	pageError('user');
} else {


// THIS CODE GENRATES NEW IGNORE TABLES ///////////////////////

$sql = "SELECT eGR.rosterID, tournamentID, ignorePoolMatches, removeFromFinals, ignoreFighter
		FROM eventGroupRoster eGR
		INNER JOIN eventTournamentRoster eTR ON eGR.tournamentTableID = eTR.tableID
		WHERE (ignorePoolMatches > 0 OR removeFromFinals > 0)";
$allIgnores = mysqlQuery($sql, ASSOC);

foreach($allIgnores as $ignore){


	$sql = "SELECT *
			FROM eventIgnores
			WHERE tournamentID = {$ignore['tournamentID']}
			AND rosterID = {$ignore['rosterID']}";
	$result = (bool)mysqlQuery($sql, SINGLE);

	if($result == false){
		$ignoreAtSet = max((int)$ignore['ignorePoolMatches'],(int)$ignore['ignoreFighter']);

		$stopAtSet = (int)$ignore['removeFromFinals'];
		if($ignoreAtSet > 0 && $stopAtSet == 0){
			$stopAtSet = 1;
		}

		$sql = "INSERT INTO eventIgnores
				(tournamentID, rosterID, ignoreAtSet, stopAtSet)
				VALUES
				({$ignore['tournamentID']}, {$ignore['rosterID']}, {$ignoreAtSet}, {$stopAtSet})";
				show($sql);
		mysqlQuery($sql, SEND);
	}

}
show("IGNORES - TABLE POPULATED");
show("///////////////////////////////////////////////////////////////");

$sql = "SELECT tournamentID, groupWinnersFirst
		FROM eventTournaments eT
		INNER JOIN systemRankings sR USING(tournamentRankingID)";
$tList = mysqlQuery($sql, ASSOC);

foreach($tList as $tournament){
	$tournamentID = $tournament['tournamentID'];
	$numWinners = (int)$tournament['groupWinnersFirst'];

	$sql = "UPDATE eventTournaments
			SET poolWinnersFirst = {$numWinners}
			WHERE tournamentID = {$tournamentID}";
	mysqlQuery($sql, SEND);
}


show("POOL WINNERS - TABLE POPULATED");
show("///////////////////////////////////////////////////////////////");


















	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>
	
	<form method='POST'>
	<input type='hidden' name='formName' value='addTournamentType'>

	<a href='adminNewTournaments.php' class='button hollow'>Add New Tournaments</a>

	<div class='input-group'>
		<span class='input-group-label'>Type:</span>
		<select name='tournamentTypeMeta' class='input-group-field'>
			<option value='weapon'>Weapon</option>
			<option value='prefix'>Prefix</option>
			<option value='material'>Material</option>
			<option value='suffix'>Suffix</option>
			<option value='gender'>Gender</option>
		</select>
		
		<span class='input-group-label'>Name:</span>
		<input class='input-group-field' type='text' name='tournamentType'>

		<button class='button success input-group-button'>
			Add
		</button>
	</div>




	</form>

<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
