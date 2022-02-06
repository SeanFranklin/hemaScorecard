<?php 
/*******************************************************************************
	Manage System Events
	
	Administrator page to add/edit/remove events
	LOGIN
		- SUPER ADMIN can access, no others can
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Stats";
include('includes/header.php');

if(ALLOW['SOFTWARE_ASSIST'] == false){
	pageError('user');
} else {

doublesByExchCount();

}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function doublesByExchCount(){

	$eventID = 139;
	$eventIDs = [31,99,86,6,3,14,35,41,173,158,171,17,47,170,68,104,46,102,176,110,19,123,65,28,7,21,131,11,13,25,51,139,89,27];
	$eventIDs = implode2int($eventIDs);

	$sql = "SELECT matchID, exchangeType
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE eventID IN ($eventIDs)
			AND tournamentWeaponID = 1
			AND exchangeType IN('clean','double','afterblow','noExchange','noQuality')";
	$exchanges = mysqlQuery($sql, ASSOC);

	$matchID = 0;
	$a['double'] = 0;
	$a['other'] = 0;
	$doubleCount = [$a,$a,$a,$a,$a,$a,$a];
	$matchesAt = [-1,0,0,0,0,0,0];
	$numDoubles = 0;
	$numMatches = -1;
	$numExchanges = 0;
	$noExchCount = [0,0,0,0,0,0,0];
	$numNoExchange = 0;
	$scoringCount = [0,0,0,0,0,0,0];

	foreach($exchanges as $exchange){

		if($exchange['matchID'] != $matchID){
			$matchID = $exchange['matchID'];
			$matchesAt[$numDoubles]++;
			$numDoubles = 0;
			$numMatches++;
		}
		

		if($exchange['exchangeType'] == 'double'){
			$doubleCount[$numDoubles]['double']++;
			$numDoubles++;
			$numExchanges++;
			$scoringCount[$numDoubles]++;
		} elseif($exchange['exchangeType'] == 'noExchange' || $exchange['exchangeType'] == 'noQuality'){
			$noExchCount[$numDoubles]++;
			$numNoExchange++;
		} elseif($exchange['exchangeType'] == 'clean' || $exchange['exchangeType'] == 'afterblow'){
			$doubleCount[$numDoubles]['other']++;
			$numExchanges++;
			$scoringCount[$numDoubles]++;
		} else {

		}

	}

	$d0 = round(100 * $doubleCount[0]['double'] / ($scoringCount[0]),1);
	$d1 = round(100 * $doubleCount[1]['double'] / ($scoringCount[1]),1);
	$d2 = round(100 * $doubleCount[2]['double'] / ($scoringCount[2]),1);
	$m0 = round(100 * $matchesAt[0] / ($numMatches),1);
	$m1 = round(100 * $matchesAt[1] / ($numMatches),1);
	$m2 = round(100 * $matchesAt[2] / ($numMatches),1);
	$m3 = round(100 * $matchesAt[3] / ($numMatches),1);
	$e0 = round(100 * $noExchCount[0] / ($scoringCount[0]),1);
	$e1 = round(100 * $noExchCount[1] / ($scoringCount[1]),1);
	$e2 = round(100 * $noExchCount[2] / ($scoringCount[2]),1);


	echo "{$numExchanges} Scoring Exchanges<BR>";
	echo "0 doubles: {$d0}% (".$doubleCount[0]['double']."/".$scoringCount[0].")<BR>";
	echo "1 doubles: {$d1}% (".$doubleCount[1]['double']."/".$scoringCount[1].")<BR>";
	echo "2 doubles: {$d2}% (".$doubleCount[2]['double']."/".$scoringCount[2].")<BR>";

	echo "<HR>";

	echo "{$numMatches} Matches<BR>";
	echo "0 Doubles: {$m0}% (".$matchesAt[0].")<BR>";
	echo "1 Doubles: {$m1}% (".$matchesAt[1].")<BR>";
	echo "2 Doubles: {$m2}% (".$matchesAt[2].")<BR>";
	echo "3 Doubles: {$m3}% (".$matchesAt[3].")<BR>";

	echo "<HR>";

	echo "{$numNoExchange} No Exchanges<BR>";
	echo "0 Doubles: {$e0}% (".$noExchCount[0]."/".$scoringCount[0].")<BR>";
	echo "1 Doubles: {$e1}% (".$noExchCount[1]."/".$scoringCount[1].")<BR>";
	echo "2 Doubles: {$e2}% (".$noExchCount[2]."/".$scoringCount[2].")<BR>";

}


/******************************************************************************/

function comparingRulesTypes(){



	//$sql = "SELECT tournamentID FROM eventTournaments";
	//$allTournamentIDs = mysqlQuery($sql, SINGLES, 'tournamentID');

	$sel = "SELECT count(*) AS num FROM eventExchanges
					INNER JOIN eventMatches USING(matchID)
					INNER JOIN eventGroups USING(groupID)
					WHERE tournamentID = eT.tournamentID
					AND exchangeType";

	$sql = "SELECT
				eventID, tournamentID,
				({$sel} = 'clean') as numClean,
				({$sel} = 'double') as numDoubles,
				({$sel} = 'afterblow') as numAfterblow
			FROM eventTournaments AS eT";
	$doubleData = mysqlQuery($sql, ASSOC);

	$totals['double']['clean'] = 0;
	$totals['double']['bilateral'] = 0;
	$totals['afterblow']['clean'] = 0;
	$totals['afterblow']['bilateral'] = 0;
	$totals['both']['clean'] = 0;
	$totals['both']['bilateral'] = 0;

	foreach($doubleData as $data){
		if($data['numClean'] < 10){ continue;}


		if($data['numDoubles'] != 0 && $data['numAfterblow'] != 0){
			$totals['both']['clean'] += $data['numClean'];
			$totals['both']['bilateral'] += ($data['numDoubles'] + $data['numAfterblow']);
		} else if($data['numDoubles'] != 0 && $data['numAfterblow'] == 0){
			$totals['double']['clean'] += $data['numClean'];
			$totals['double']['bilateral'] += ($data['numDoubles'] + $data['numAfterblow']);
		} else if($data['numDoubles'] == 0 && $data['numAfterblow'] != 0){
			$totals['afterblow']['clean'] += $data['numClean'];
			$totals['afterblow']['bilateral'] += ($data['numDoubles'] + $data['numAfterblow']);
		} else {
			// Invalid type
		}

	}


	foreach($totals as $type => $total)
	{
		echo "<hr>{$type} | Clean: {$total['clean']} | Bilateral: {$total['bilateral']} | BpE: ".round($total['bilateral']/($total['bilateral']+$total['clean']),2);
	}
	



}

/******************************************************************************/

function dpeAndDoubleOut(){
// Do tournaments with double outs cause people to double more?

	$with = 0;
	$without = 0;

	$sql = "SELECT eventID
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE exchangeType = 'double'
			AND tournamentWeaponID = 1
			GROUP BY eventID";

	$eventIDs = mysqlQuery($sql, SINGLES,'eventID');
	$eventData = [];


	foreach($eventIDs as $eventID){

		$eventData[$eventID]['numDoubleOuts'] = 0;

		$sql = "SELECT count(*) AS num
				FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				WHERE exchangeType = 'doubleOut'
				AND tournamentWeaponID = 1
				AND eventID = {$eventID}";
		$num = (int)mysqlQuery($sql, SINGLE, 'num');
		$eventData[$eventID]['numDoubleOuts'] += $num;
		

		$sql = "SELECT matchID
				FROM eventMatches AS eM
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				WHERE tournamentWeaponID = 1
				AND eventID = {$eventID}
				AND winnerID IS NULL 
				AND matchComplete = 1
				AND (SELECT count(*) AS num
						FROM eventExchanges as eE
						WHERE exchangeType = 'double'
						AND eE.matchID = eM.matchID) > 1
				AND (SELECT count(*) AS num
						FROM eventExchanges as eE
						WHERE exchangeType = 'tie'
						AND eE.matchID = eM.matchID) = 0";
		$matchIDs = mysqlQuery($sql, SINGLES, 'matchID');
		$eventData[$eventID]['numDoubleOuts'] += (int)count($matchIDs);

		$type = ['clean','afterblow','double'];

		foreach($type as $exchangeType){
			$sql = "SELECT count(*) AS num
					FROM eventExchanges
					INNER JOIN eventMatches USING(matchID)
					INNER JOIN eventGroups USING(groupID)
					INNER JOIN eventTournaments USING(tournamentID)
					WHERE exchangeType = '{$exchangeType}'
					AND tournamentWeaponID = 1
					AND eventID = {$eventID}";
			$eventData[$eventID][$exchangeType] = (int)mysqlQuery($sql, SINGLE, 'num');
		}
	}

	foreach($eventData as $eventID => $data){

		if($eventData[$eventID]['numDoubleOuts'] == 0){
			$noDoubleOuts[$eventID] = $data; 
		} else {
			$withDoubleOuts[$eventID] = $data; 
		}
		
	}


	$headers = ['clean','afterblow','double','numDoubleOuts'];

	echo "<table>";
	echo "<tr><th>Event</th>";
	foreach($headers as $name){
		echo "<th>".$name."</th>";
	}
	echo "</tr>";

	foreach($withDoubleOuts as $eventID => $data){
		echo "<tr><td>".getEventName($eventID)."</td>";
		foreach($headers as $name){
			echo "<td>".$data[$name]."</td>";
		}
		echo "</tr>";
	}

	foreach($noDoubleOuts as $eventID => $data){
		echo "<tr><td>".getEventName($eventID)."</td>";
		foreach($headers as $name){
			echo "<td>".$data[$name]."</td>";
		}
		echo "</tr>";
	}



	echo "</table>";
}

/******************************************************************************/

function afterblowByTarget(){
	$sql = "SELECT tournamentID
			FROM eventExchanges AS eE
			INNER JOIN systemAttacks AS sA ON eE.refTarget = sA.attackID 
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE attackID IN (1,2,3,4)
			GROUP BY tournamentID";
	$tournamentIDs = mysqlQuery($sql, SINGLES, 'tournamentID');

	$attackIDs = [1,2,3,4];

	echo "<table>
			<tr>
				<th colspan='2'>Head</th>
				<th colspan='2'>Torso</th>
				<th colspan='2'>Arm</th>
				<th colspan='2'>Leg</th>
			</th>";


	foreach($tournamentIDs as $tournamentID){

		echo "<tr>";


		foreach($attackIDs as $attackID){
			$sql = "SELECT count(*) AS num
					FROM eventExchanges AS eE
					INNER JOIN eventMatches USING(matchID)
					INNER JOIN eventGroups USING(groupID)
					INNER JOIN eventTournaments USING(tournamentID)
					WHERE tournamentID = {$tournamentID}
					AND refTarget = {$attackID}
					AND exchangeType = 'clean'";
			$numClean = (int)mysqlQuery($sql, SINGLE, 'num');

			$sql = "SELECT count(*) AS num
					FROM eventExchanges AS eE
					INNER JOIN eventMatches USING(matchID)
					INNER JOIN eventGroups USING(groupID)
					INNER JOIN eventTournaments USING(tournamentID)
					WHERE tournamentID = {$tournamentID}
					AND refTarget = {$attackID}
					AND exchangeType = 'afterblow'";
			$numAfterblow = (int)mysqlQuery($sql, SINGLE, 'num');

			echo "<td>".$numClean."</td><td>".$numAfterblow."</td>";

		}

		echo "</tr>";

	}

	echo "</table>";
}

/******************************************************************************/

function afterblowByTargetAndAttack(){
	$attackIDs = [1,2,3,4];


	$sql = "SELECT exchangeType, refTarget, refType
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			WHERE eventID IN (51,139,173)
			AND tournamentWeaponID = 1
			AND refTarget IN (1,2,3,4)
			AND refType IN (5,6)";
	$exchanges = mysqlQuery($sql, ASSOC);

	$idName = [0,'Head','Torso','Arm','Leg','Cut','Thrust'];

	$s['clean'] = 0;
	$s['afterblow'] = 0;

	$t['Cut'] = $s;
	$t['Thrust'] = $s;

	$data['Head'] = $t;
	$data['Torso'] = $t;
	$data['Arm'] = $t;
	$data['Leg'] = $t;


	echo "<table>
			<tr>
			<th>Target</th>
			<th>Attack</th>
			<th>Count</th>
			<th>Afterblow %</th>
			</tr>";

	foreach($exchanges as $exchange)
	{
		$type = $idName[$exchange['refType']];
		$target = $idName[$exchange['refTarget']];
		$data[$target][$type][$exchange['exchangeType']]++;
	}

	foreach($data as $target => $more){

		foreach($more as $attack => $stat){
			echo "<tr><td>".$target."</td><td>".$attack."</td>";
			$count = $stat['clean'] + $stat['afterblow'];
			$percent = round(100*$stat['afterblow']/$count,1);

			echo "<td>".$count."</td><td>".$percent."%</td><tr>";


		}

	}

	echo "</table>";
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

