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

comparingRulesTypes();

}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

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

