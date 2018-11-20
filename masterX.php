<?php
/*************************************
	adminLogin
	
	Log in interface
	- log in processed in 'includes/conditionalConstants.php'
**************************************/

$pageName = 'Data Queries';
include('includes/header.php');



if(USER_TYPE < USER_SUPER_ADMIN){
	pageError('user');
	
} else {

phpinfo();
/*
	// tounrnamentID 204, 205

	$sql = "SELECT tournamentID, fighter1ID AS rosterID, groupID, scoreValue, scoreDeduction
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID IN (204, 205)
			ORDER BY tournamentID ASC, groupID ASC, fighter1ID ASC";
	$allExchanges = mysqlQuery($sql, ASSOC);

	$lastTournament = null;
	$lastGroup = null;
	$lastFighter = null; 



	echo "<table>";
	foreach($allExchanges as $exchange){
		
		if($lastTournament != $exchange['tournamentID']){
			$tName = getTournamentName($exchange['tournamentID']);
			echo "<tr><td colspan='100%'><h3>{$tName}</h3></td></tr>";
			$lastTournament = $exchange['tournamentID'];
		}
		if($lastGroup != $exchange['groupID']){
			$gName = getGroupName($exchange['groupID']);
			echo "<tr><td colspan='100%'><h5>{$gName}</h5></td></tr>";
			$lastGroup = $exchange['groupID'];
		}
		if($lastFighter != $exchange['rosterID']){
			$fName = getFighterName($exchange['rosterID']);
			echo "<tr><td colspan='100%'>{$fName}</td></tr>";
			$lastFighter = $exchange['rosterID'];
			$totalPoints = 0;
			$showHeader = true;
		}

		if($showHeader){
			echo "<tr>
					<th>Score</th>
					<th>Deduction</th>
					<th>Points</th>
					<th>Total</th>
				</tr>";
			$showHeader = false;
		}

		$exchangePoints = $exchange['scoreValue'] - $exchange['scoreDeduction'];
		$totalPoints += $exchangePoints;

		echo "<tr>
				<td>{$exchange['scoreValue']}</td>
				<td>{$exchange['scoreDeduction']}</td>
				<td>{$exchangePoints}</td>
				<td>{$totalPoints}</td>";
		echo "</tr>";

	}
	echo "</table>";
	*/

/* So Cal Singlestick Leg Shots

	$all_tournamentIDs = "9, 22, 32, 39, 70, 109, 126, 142, 190";
	$SoCal_tournamentID = 178;
	$BnI_schoolID = "3, 4";


	$sql = "SELECT exchangeType, refTarget, refType
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID IN ($SoCal_tournamentID)
			AND exchangeType IN ('clean', 'afterblow')";
	$allExchanges = mysqlQuery($sql, ASSOC);

	foreach($allExchanges as $exchange){

		$allResults[$exchange['refTarget']][$exchange['refType']][$exchange['exchangeType']]++;
		
	}
	show($allResults);

	echo "<table>";
	for($refTarget = 1;$refTarget<= 4;$refTarget++){
		if($refTarget == 1){$target = 'Head';}
		if($refTarget == 2){$target = 'Torso';}
		if($refTarget == 3){$target = 'Arm';}
		if($refTarget == 4){$target = 'Leg';}

		foreach($allResults[$refTarget] as $refType => $exchangeType){
			if($refType == 5){$type = 'Cut';
			}elseif($refType == 6){$type = 'Thrust';
			}else{$type = 'unk';}

			echo "
			<tr>
				<td>{$schoolCodeName}</td>
				<td>{$target}</td>
				<td>{$type}</td>
				<td>{$exchangeType['clean']}</td>
				<td>{$exchangeType['afterblow']}</td>
			</tr>";

		}

	}
	echo "</table>";

/*
	$sql = "SELECT scoreValue, exchangeType, scoringID, recievingID
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID IN ($all_tournamentIDs)
			AND exchangeType IN ('clean', 'afterblow')";
	$allExchanges = mysqlQuery($sql, ASSOC);


	foreach($allExchanges as $exchange){

		
		$schoolCode = fighterSchoolType($exchange['scoringID'], $exchange['recievingID']);

		$allResults[$schoolCode][$exchange['scoreValue']][$exchange['exchangeType']]++;

		
	}
	


echo "<table>";
	for($schoolCode = 0;$schoolCode<= 3;$schoolCode++){
		for($scoreValue = 1; $scoreValue <= 4; $scoreValue++){
			if($schoolCode == 0){$schoolCodeName = 'NON hit NON';}
			if($schoolCode == 1){$schoolCodeName = 'BnI hit NON';}
			if($schoolCode == 2){$schoolCodeName = 'NON hit BnI';}
			if($schoolCode == 3){$schoolCodeName = 'BnI hit BnI';}
			echo "
			<tr>
				<td>{$schoolCodeName}</td>
				<td>{$scoreValue}</td>
				<td>{$allResults[$schoolCode][$scoreValue]['clean']}</td>
				<td>{$allResults[$schoolCode][$scoreValue]['afterblow']}</td>
			</tr>";

		}


	}
echo "</table>";
	
*/




	
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////

/**********************************************************************/

function fighterSchoolType($scoringID, $recievingID){

	$returnCode = 0;
	$BnI_scoring = 1;
	$BnI_recieving = 2;

	$sql = "SELECT rosterID, schoolID
			FROM eventRoster
			INNER JOIN systemSchools USING(schoolID)
			WHERE rosterID = {$scoringID}";
	$res = mysqlQuery($sql, SINGLE);
	
	if($res == null){
		return null;
	}

	if($res['schoolID'] == 3 || $res['schoolID'] == 4){
		$returnCode += $BnI_scoring;
	}

	$sql = "SELECT rosterID, schoolID
			FROM eventRoster
			INNER JOIN systemSchools USING(schoolID)
			WHERE rosterID = {$recievingID}";
	$res = mysqlQuery($sql, SINGLE);
	
	if($res == null){
		return null;
	}

	if($res['schoolID'] == 3 || $res['schoolID'] == 4){
		$returnCode += $BnI_recieving;
	}

	return $returnCode;
}

/**********************************************************************/

function poolResultsFromSeeding(){
	define("BRACKET_SIZE", 32);
	define("SIMULATION_COUNT",30000);
	define("POOL_SIZE",8);


	$sql = "SELECT tournamentID, groupName, numFighters
			FROM eventGroups
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE groupType = 'elim'
			AND tournamentElimID = 2
			AND numFighters = 8
			AND groupName = 'winner'";
	$tournamentList = mysqlQuery($sql, ASSOC);

	foreach($tournamentList as $index => $data){
		if(isDoubleElim($data['tournamentID'])){
			unset($tournamentList[$index]);
		}
	}

	$numBrackets = count($tournamentList);
	show($numBrackets);





	echo "
	<table><tr><th>Rank</th>
	<th>Gold</th>
	<th>Silver</th>
	<th>Bronze</th>
	<th>4th</th></tr>";
		for($i=1;$i<=8;$i++){
			echo "<tr><td>{$i}</td>";
			for($j=1;$j<=4;$j++){
				$val = (int)$binnedResults[$i][$j];
				echo "<td>{$val}</td>";
			}
			echo "</tr>";


	}
	echo "</table>";



	// Finals Pool //
	/******************************************************
	for($q=1;$q<=SIMULATION_COUNT;$q++){
		unset($numWins);
		unset($numPlacings);
		unset($endResult);

		for($i = 1;$i<=POOL_SIZE;$i++){
			$numWins[$i] += 0;
			for($j = $i+1;$j<=POOL_SIZE;$j++){
				$winner = getMatchWinner($i,$j);
				$numWins[$winner]++;
			}
		}


		$numPlacings= array_count_values($numWins);
		krsort($numPlacings);

		$place = 1;
		foreach($numPlacings as $wins => $occurances){
			$endResult[$wins] = $place;
			$place += $occurances;
		}

		foreach($numWins as $fighter => $wins){
			$placing = floor($endResult[$wins]);

			$numTies = $numPlacings[$wins];

			$r = mt_rand(0,$numTies-1);
			$placing += $r;


			$results[$fighter][$placing]++;
		}
	}
	/***********************************************************************/


	// Bracket Calculation //
	/***************************************************************************/


	// Seeded Bracket
	/*******
	for($i = 1;$i <= BRACKET_SIZE; $i++){
		$position = getBracketPositionByRank($i,BRACKET_SIZE);
		$bracket[1][$position] = $i;
	}
	/*******/


	/***************************************************************
	for($q=1;$q<=SIMULATION_COUNT;$q++){

		$bracketLevel = 1;
		$matchesAtLevel = BRACKET_SIZE/(2*(int)$bracketLevel);

		// Random bracket
		/******************
		unset($temp);
		for($i = 1;$i <= BRACKET_SIZE; $i++){
			$temp[mt_rand(0,1000)] = $i;
		}

		ksort($temp);

		$position = 1;
		foreach($temp as $fighter){
			$bracket[1][$position] = $fighter;
			$position++;
		}
		/*********************


		while($matchesAtLevel >= 1){

			for($matchNum = 1;$matchNum <=$matchesAtLevel;$matchNum++){
				$rank1 = $bracket[$bracketLevel][($matchNum*2)-1];
				$rank2 = $bracket[$bracketLevel][($matchNum*2)];
				$winner = getMatchWinner($rank1, $rank2);
				$bracket[$bracketLevel+1][$matchNum] = $winner;
				if($winner == $rank1){
					$loser = $rank2;
				} else {
					$loser = $rank1;
				}

				if($matchesAtLevel == 2){
					$bronze[$matchNum] = $loser;
				}

				if($matchesAtLevel == 1){
					$result[$winner][1]++;

					$third = getMatchWinner($bronze[1],$bronze[2]);
					$results[$third][4]--;
					$results[$third][3]++;
					$results[$winner][1]++;

				}
				
				$results[$loser][$matchesAtLevel*2]++;

			}

			$bracketLevel++;
			$matchesAtLevel = BRACKET_SIZE/(pow(2,$bracketLevel));
		}
	}


	$positions = [1,2,3];
	$s = 4;
	while($s <= BRACKET_SIZE){
		$positions[] = $s;
		$s *= 2;
	}

	/***************************************************************************



	echo "<table><tr><th>Rank</th>";
	foreach((array)$positions as $num){
		echo "<th>{$num}</th>";
	}

	echo "</tr>";

	for($rank = 1; $rank <= BRACKET_SIZE; $rank++){


		echo "<tr><td>{$rank}</td>";
		foreach((array)$positions as $place){
			$val = $results[$rank][$place];
			$val /= SIMULATION_COUNT;
			echo "<td>{$val}</td>";

		}

		echo "</tr>";


	}

	echo "</table>";

	*/



}

/**********************************************************************/

function getMatchWinner($rank1, $rank2){
	$diff = $rank2 - $rank1;

	if($diff < 0){
		$diff *= -1;
		$fighter1 = $rank2;
		$fighter2 = $rank1;
		$isSwapped = true;
	} else {
		$fighter1 = $rank1;
		$fighter2 = $rank2;
	}

	$winPercent = 0.1122 * (log($diff))+ 0.5285;
	$randVal = mt_rand(0,100)/100;

	if($winPercent > $randVal){
		$winner = $fighter1;
	} else {
		$winner = $fighter2;
	}

	return $winner;

}

/**********************************************************************/
