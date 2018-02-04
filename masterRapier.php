<?php
/*************************************
	adminLogin
	
	Log in interface
	- log in processed in 'includes/conditionalConstants.php'
**************************************/

$pageName = 'Log In';
include('includes/header.php');

if(USER_TYPE < USER_SUPER_ADMIN){
	echo "Go Away!";
	
} else {
	
	$sql = "SELECT e.scoringID, m.winnerID
			FROM eventExchanges AS e
			INNER JOIN eventMatches AS m ON m.matchID = e.matchID
			INNER JOIN eventGroups AS g ON g.groupID = m.groupID
			INNER JOIN eventTournaments AS t ON t.tournamentID = g.tournamentID
			WHERE e.scoreValue = -2
			AND t.tournamentWeaponID = 4";
	$result = mysqlQuery($sql, ASSOC);
	
	$daggerWins = 0;
	$singleWins = 0;
	
	foreach($result as $match){
		if($match['winnerID'] == null){
			echo "!";
			continue;
		}
		
		if($match['scoringID'] == $match['winnerID']){
			$daggerWins++;
		} else {
			$singleWins++;
		}
	}
	
	$totalWins = $daggerWins + $singleWins;
	$dp = round($daggerWins/$totalWins,2)*100;
	$sp = round($singleWins/$totalWins,2)*100;
	
	echo "
	<fieldset class='fieldset'>
	<legend>Rapier & Dagger vs Single Rapier</legend>
	Win rates for a 2 point penalty<BR>
	Dagger: {$daggerWins} ({$dp}%)<BR>
	Single: {$singleWins} ({$sp}%)<BR>
	Total: {$totalWins}
	</fieldset>"; 
	
	
	
	
	
}


include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
