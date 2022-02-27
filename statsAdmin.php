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

	echo "<div class='cell'>";

	timeByDirector();	
	echo "</div>";

}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function timeByDirector(){

	$eventID = 209;
	$weaponID = 1;

	$sql = "SELECT tournamentID
			FROM eventTournaments
			WHERE eventID = {$eventID}
			AND tournamentWeaponID = {$weaponID}";
	$tournamentIDs = implode2int(mysqlQuery($sql, SINGLES, 'tournamentID'));

	$MAX_MATCH_LENGTH = 1000;

	$sql = "SELECT exchangeTime, UNIX_TIMESTAMP(timestamp) AS realTime, exchangeType, matchID, groupID, groupType, lSM.rosterID AS directorID
			FROM eventExchanges
			INNER JOIN logisticsStaffMatches AS lSM USING(matchID)
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID IN ({$tournamentIDs})
			AND matchComplete = 1
			AND logisticsRoleID = 1 
			ORDER BY groupID ASC, matchID ASC, matchNumber ASC, exchangeNumber ASC";
	$exchanges = mysqlQuery($sql, ASSOC);

	$matchID = 0;
	$groupID = 0;
	$matchExchangeCount = 0;
	

	foreach($exchanges as $index => $exchange){

		if($matchID != $exchange['matchID']){

			$lastRealTime = $exchange['realTime'] - $exchange['exchangeTime'];
			$lastExchangeTime = 0;
			
			$matchID = $exchange['matchID'];
		}

		if(isScoringExchange($exchange['exchangeType']) == true){
			
			$matchExchangeCount++;

			$exchangeTime = $exchange['realTime'] - $lastRealTime;
			$fightTime = $exchange['exchangeTime'] - $lastExchangeTime;
			$judgeTime = $exchangeTime - $fightTime;


			if($judgeTime > 0){

				@$judge[$exchange['directorID']]['total'] += $judgeTime;
				@$judge[$exchange['directorID']]['count']++;
			}

			$lastExchangeTime = $exchange['exchangeTime'];
			$lastRealTime = $exchange['realTime'];
		}

	}

	echo "<table>";
	echo "<tr>
			<th>Name</th>
			<th>Judge Time [sec]</th>
			<th>Num Exchanges</th>
			<th>Average Judge Time</th>
			</tr>";

	foreach($judge as $judgeID => $data){

		$avg = round($data['total']/$data['count'],1);

		echo "<tr>";
			echo "<td>".getFighterName($judgeID)."</td>";
			echo "<td>".$data['total']."</td>";
			echo "<td>".$data['count']."</td>";
			echo "<td>".$avg."</td>";
		echo "</tr>";
	}

	echo "</table>";


}

/******************************************************************************/

function doublesAndFinalScore(){


	$sql = "SELECT tournamentID 
			FROM eventTournaments
			INNER JOIN systemEvents USING(eventID)
			WHERE countryIso2 = 'PL'
			AND doubleTypeID = 1
			AND maxDoubleHits = 0";
	$tournamentIDs = mysqlQuery($sql, SINGLES, 'tournamentID');
	echo "numTournaments: ".count($tournamentIDs);
	$tournamentIDs = implode2int($tournamentIDs);

	$sql = "SELECT ABS(fighter1Score - fighter2Score) AS pointSpread,
					(SELECT COUNT(*)
					FROM eventExchanges AS eE2
					WHERE exchangeType = 'double'
					AND eE2.matchID = eM.matchID) AS numDoubles
			FROM eventMatches AS eM
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID IN ($tournamentIDs)";
	$matches = mysqlQuery($sql, ASSOC);
	
	echo"<table><tr><th>Point Spread</th><th>Num Doubles</th></tr>";
	foreach($matches as $match){
		echo "<tr><td>".$match['pointSpread']."</td><td>".$match['numDoubles']."</td></tr>";
	}
	echo "</table>";

}

/******************************************************************************/

function compareDoublesAndAfterblows(){

	/*
	// This part only needs to be run once, to generate the list of tournamentIDs copied in below
	$sql = "SELECT tournamentID
			FROM eventTournaments AS eT
			WHERE (SELECT COUNT(*) AS numAfterblow
					FROM eventExchanges
					INNER JOIN eventMatches USING(matchID)
					INNER JOIN eventGroups AS eG2 USING(groupID)
					WHERE exchangeType = 'afterblow'
					AND eT.tournamentID = eG2.tournamentID
					AND doubleTypeID = 2) > 10
			AND (SELECT COUNT(*) AS numDouble
					FROM eventExchanges
					INNER JOIN eventMatches USING(matchID)
					INNER JOIN eventGroups AS eG3 USING(groupID)
					WHERE exchangeType = 'double'
					AND eT.tournamentID = eG3.tournamentID
					AND doubleTypeID = 2) > 10";

	$tournamentIDs = mysqlQuery($sql, SINGLES, 'tournamentID');
	echo implode2int($tournamentIDs);
	*/


	$tournamentIDs = "3,4,5,7,9,20,21,22,24,28,31,32,37,39,42,43,47,48,49,51,52,62,63,64,67,68,69,70,89,93,97,100,105,106,107,108,109,114,123,124,126,132,133,139,141,142,144,174,175,176,178,187,189,190,192,194,195,204,205,206,207,208,209,210,216,251,252,304,312,338,358,359,360,361,362,364,373,375,399,400,403,404,423,460,461,462,464,465,467,468,471,475,476,481,484,485,502,503,506,507,508,533,534,535,627,684,685,686,687,688,690,691,775,778,779,825,826,827,833,835,836,837,839,840,841,849,850,852,857,858,859,860,861,863,927";
	
	$sql = "SELECT systemRosterID
			FROM eventTournamentRoster
			INNER JOIN eventRoster USING(rosterID)
			WHERE tournamentID IN ({$tournamentIDs})
			GROUP BY systemRosterID";
	$systemRosterIDs = mysqlQuery($sql, SINGLES, 'systemRosterID');
	$systemRosterIDs = implode2int($systemRosterIDs);

	
	$sql = "SELECT (	
						SELECT COUNT(*)
						FROM eventExchanges AS eE2
						INNER JOIN eventMatches USING(matchID)
						INNER JOIN eventGroups USING(groupID)
						INNER JOIN eventRoster AS eR2 ON eE2.scoringID = eR2.rosterID
						WHERE exchangeType = 'clean'
						AND eR2.systemRosterID = sR.systemRosterID
						AND tournamentID IN ({$tournamentIDs})
					) AS numCleanFor,
					(	
						SELECT COUNT(*)
						FROM eventExchanges AS eE3
						INNER JOIN eventMatches USING(matchID)
						INNER JOIN eventGroups USING(groupID)
						INNER JOIN eventRoster AS eR3 ON eE3.receivingID = eR3.rosterID
						WHERE exchangeType = 'clean'
						AND eR3.systemRosterID = sR.systemRosterID
						AND tournamentID IN ({$tournamentIDs})
					) AS numCleanAgainst,
					(	
						SELECT COUNT(*)
						FROM eventExchanges AS eE4
						INNER JOIN eventMatches USING(matchID)
						INNER JOIN eventGroups USING(groupID)
						INNER JOIN eventRoster AS eR4 ON eE4.scoringID = eR4.rosterID
						WHERE exchangeType = 'double'
						AND eR4.systemRosterID = sR.systemRosterID
						AND tournamentID IN ({$tournamentIDs})
					) AS numDouble1,
					(	
						SELECT COUNT(*)
						FROM eventExchanges AS eE5
						INNER JOIN eventMatches USING(matchID)
						INNER JOIN eventGroups USING(groupID)
						INNER JOIN eventRoster AS eR5 ON eE5.receivingID = eR5.rosterID
						WHERE exchangeType = 'double'
						AND eR5.systemRosterID = sR.systemRosterID
						AND tournamentID IN ({$tournamentIDs})
					) AS numDouble2,
					(	
						SELECT COUNT(*)
						FROM eventExchanges AS eE6
						INNER JOIN eventMatches USING(matchID)
						INNER JOIN eventGroups USING(groupID)
						INNER JOIN eventRoster AS eR6 ON eE6.scoringID = eR6.rosterID
						WHERE exchangeType = 'afterblow'
						AND eR6.systemRosterID = sR.systemRosterID
						AND tournamentID IN ({$tournamentIDs})
					) AS numAfterblowHitBy,
					(	
						SELECT COUNT(*)
						FROM eventExchanges AS eE7
						INNER JOIN eventMatches USING(matchID)
						INNER JOIN eventGroups USING(groupID)
						INNER JOIN eventRoster AS eR7 ON eE7.receivingID = eR7.rosterID
						WHERE exchangeType = 'afterblow'
						AND eR7.systemRosterID = sR.systemRosterID
						AND tournamentID IN ({$tournamentIDs})
					) AS numAfterblowLanded
			FROM systemRoster AS sR
			WHERE systemRosterID IN ({$systemRosterIDs})";
	$dataPoints = mysqlQuery($sql, ASSOC);

	echo "<table>";

	echo "<tr>
			<td>Clean Hit %</td>
			<td>Hit %</td>
			<td>AB Def %</td>
			<td>AB Return %</td>
			<td>Double %</td>
			<td>BpE</td>
		</tr>";

	foreach($dataPoints as $data){

		$numDoubles = $data['numDouble1'] + $data['numDouble2'];

		$numExch = $data['numCleanFor'] + $data['numCleanAgainst'] 
					+ $data['numAfterblowHitBy'] + $data['numAfterblowLanded']
					+ $numDoubles;
		if($numExch < 15){
			continue;
		}

		$hitPct = ($data['numCleanFor'] + $data['numAfterblowHitBy']) 
						/ ($data['numCleanFor'] + $data['numCleanAgainst']
							+ $data['numAfterblowHitBy'] + $data['numAfterblowLanded']);
		$cleanHitPct = $data['numCleanFor'] / ($data['numCleanFor'] + $data['numCleanAgainst']);
		$abDefencePct = $data['numCleanFor']/($data['numCleanFor'] + $data['numAfterblowHitBy']);
		$abReturnPct = $data['numAfterblowLanded']/($data['numAfterblowLanded'] + $data['numCleanAgainst']);

		$BpE = ($numExch - $data['numCleanFor'] - $data['numCleanAgainst'])/$numExch;

		echo "<tr>";
			echo "<td>".$cleanHitPct."</td>";
			echo "<td>".$hitPct."</td>";
			echo "<td>".$abDefencePct."</td>";
			echo "<td>".$abReturnPct."</td>";
			echo "<td>".$numDoubles/$numExch."</td>";
			echo "<td>".$BpE."</td>";

		echo "</tr>";

	}
	echo "</table>";

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

