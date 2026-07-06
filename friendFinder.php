<?php
/*******************************************************************************


*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Friend Finder';
include('includes/header.php');
{

	$systemRosterID = (int)$_SESSION['filterForSystemRosterID'];

	if(isset($_SESSION['stats']['oppositesAttract']) == false){
		$_SESSION['stats']['oppositesAttract'] = 0;
	}
	$oppositesAttract = (bool)$_SESSION['stats']['oppositesAttract'];


// Stats ----------------------------------------------------------------
	$params['stealth']['name'] = "Stealth";
	$params['cunning']['name'] = "Cunning";
	$params['toughness']['name'] = "Toughness";
	$params['antagonism']['name'] = "Antagonism";
	$params['wisdom']['name'] = "Wisdom";
	$params['accuracy']['name'] = "Accuracy";
	$params['versatility']['name'] = "Versatility";

	$params['stealth']['text'] = "Stealthy fighters make their movements indiscernible, leaving judges with no choice but to call No Exchange.";
	$params['cunning']['text'] = "Cunning fighters makes sure to always get the advantage, coming away with a point even when both fighters hit.";
	$params['toughness']['text'] = "Tough fighters are always ready to duke it out, having lots of exchanges where both fighter hit each other.";
	$params['antagonism']['text'] = "Antagonists sow the seeds of their own demise, engaging in exchanges where both hit, yet their opponents are the ones getting the points.";
	$params['wisdom']['text'] = "Wise fighters are able to maximize their effectiveness, preferring exchanges where they receive multiple points.";
	$params['accuracy']['text'] = "Accurate fighters are able to pinpoint their attacks, and land hits only when they won't be struck.";
	$params['versatility']['text'] = "Versatile fighters are at home with every weapon, and diversify rather than specialize.";

	$params['stealth']['breakpoints'] 		= [  0,  3,    8, 13, 19, 101];
	$params['cunning']['breakpoints'] 		= [  0,  0.01, 2,  4,  7, 101];
	$params['toughness']['breakpoints'] 	= [  0,  1, 13, 20, 35, 101];
	$params['antagonism']['breakpoints'] 	= [  0,  0.01, 2,  5,  7, 101];
	$params['wisdom']['breakpoints'] 		= [  0, 13, 20, 26, 36, 101];
	$params['accuracy']['breakpoints'] 		= [  0, 40, 47, 53, 62, 101];
	$params['versatility']['breakpoints'] 	= [  0,  0.01, 5, 10, 15, 101];


// Loop ----------------------------------------------------------------

	$stats1 = calculateStats($systemRosterID, $params);

	$numToTest = 800;
	$maxYearsSinceActive = 5;

	$sql = "SELECT systemRosterID, (SELECT eventStartDate
									FROM eventRoster AS eR2
									INNER JOIN systemEvents USING(eventID)
									WHERE eR2.systemRosterID = sR.systemRosterID
									ORDER BY eventStartDate DESC
									LIMIT 1) AS lastDate
			FROM systemRoster AS sR
			HAVING lastDate > DATE_SUB(NOW(),INTERVAL {$maxYearsSinceActive} YEAR)
			ORDER BY RAND()
			LIMIT {$numToTest}";
	$sysIDs = mysqlQuery($sql, SINGLES, 'systemRosterID');

	if($oppositesAttract == false){
		$bestError = 9999;
	} else {
		$bestError = 0;
	}

	if((int)$systemRosterID != 0){
		foreach($sysIDs as $sysID_test){

			if($sysID_test == $systemRosterID){continue;}

			$stats_test = calculateStats($sysID_test, $params);
			$error_test = calculateDifference($stats1, $stats_test);

			$sum = 0;
			if($oppositesAttract == true){
				// If we are matching opposites it's too easy to just pair them with someone
				// who has 1 star across the board. This scales the error_test of anyone who has less
				// than 14 score total to help get more interesting results.
				$sum += $stats_test['stealth']['score'];
				$sum += $stats_test['cunning']['score'];
				$sum += $stats_test['toughness']['score'];
				$sum += $stats_test['antagonism']['score'];
				$sum += $stats_test['wisdom']['score'];
				$sum += $stats_test['accuracy']['score'];
				$sum += $stats_test['versatility']['score'];

				if($sum < 280){
					$scale = $sum / 280;
					$error_test *= $scale;
				}

			}

			if($error_test < $bestError && $oppositesAttract == false){
				$systemRosterID2 = $sysID_test;
				$stats2 = $stats_test;
				$bestError = $error_test;
			} else if($error_test > $bestError && $oppositesAttract == true){
				$systemRosterID2 = $sysID_test;
				$stats2 = $stats_test;
				$bestError = $error_test;
			} elseif ($error_test == $bestError) {

				// If they are the same error it is a 50/50 chance to swap.
				if(mt_rand(0,1) == 0){
					$systemRosterID2 = $sysID_test;
					$stats2 = $stats_test;
					$bestError = $error_test;
				}

			} else {
				// They previous person is a better match.
			}
		}
	}

	$oppositesChecked = "";
	if($oppositesAttract == true){
		$oppositesChecked = 'checked';
	}



// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?=changeRosterFilterDropdown()?>

	<?php if($systemRosterID != 0): ?>

		<a class="button small success" href="friendFinder.php">
			Find Another Friend!
		</a>


		<div class='grid-x grid-margin-x'>

			<div class='cell large-4 medium-5'>
				<?=showFighterStats($systemRosterID, $stats1)?>
			</div>

			<div class='cell large-2 medium-2 align-self-middle text-center'>
				<BR>
				<b>Your new friend is:</b>

				<BR><BR><BR>
				<span class='hide-for-small-only'><BR></span>
				<span style='font-size:0.85em'>Powered by</span><BR>
				<img src='includes/images/logo_square.jpg' width='100px'>
				<BR><i><span style='font-size:0.6em; line-height: 1.0em; display:block'>(look, I was smart enough to include branding this year)</span></i>
				<BR><BR>

			</div>

			<div class='cell large-4  medium-5'>
				<?=showFighterStats($systemRosterID2, $stats2)?>
			</div>

			<div class='cell large-12'>

				<form method='POST'>
					<input type='hidden' name='oppositesAttract' value='0'>
					<input type='checkbox' name='oppositesAttract' value='1' <?=$oppositesChecked?>> Opposites Attract
					<button class='button tiny hollow no-bottom' name='formName' value='oppositesAttract'>Go!</button>
				</form>
			</div>

		</div>

	<?php endif ?>




<?php

}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function calculateDifference($stats1, $stats2){

	$error = 0;
	foreach($stats1 as $index => $s){
		$error += pow(abs($stats1[$index]['score'] - $stats2[$index]['score']),2);

	}

	return $error;
}

/******************************************************************************/

function showFighterStats($systemRosterID, $stats){
?>

	<fieldset class='callout fieldset'>
		<legend>
			<span style='font-size:1.5em'><?=getFighterNameSystem($systemRosterID)?></span>
		</legend>
		<u>Club</u>: <b><?=getFighterSchoolNameSystem($systemRosterID)?></b>
		<BR><BR>
		<table>
			<?php foreach($stats as $s):?>
				<tr>
					<td  class='text-right' style='border-right: solid 1px black'><?=$s['name']?>  </td>
					<td><b><?=showScore($s['score'])?></b></td>
					<td><?=tooltip($s['text'])?></td>
				</tr>
			<?php endforeach ?>

		</table>

	</fieldset>

<?php
}

/******************************************************************************/

function calculateStats($systemRosterID, $params){

	$stats = $params;
	$fighterStats = getFighterStats($systemRosterID);

	if($fighterStats != []){

		$stats['stealth']['raw'] = round(100 * @$fighterStats['noExchange'] / $fighterStats['totalExchange']);
		$stats['cunning']['raw'] = round(100 * @$fighterStats['abFor'] / $fighterStats['totalExchange']);
		$stats['toughness']['raw'] = round(100 * @$fighterStats['bilaterals'] / $fighterStats['totalExchange']);
		$stats['antagonism']['raw'] = round(100 * @$fighterStats['abAgainst'] / $fighterStats['totalExchange']);
		$stats['wisdom']['raw'] = round(100 * @$fighterStats['multiPointExch'] / $fighterStats['totalExchange']);
		$stats['accuracy']['raw'] = round(100 * @$fighterStats['cleanFor'] / $fighterStats['totalExchange']);
		$stats['versatility']['raw'] = round(@$fighterStats['weaponScore']);

		foreach($stats as $i => $s){
			$stats[$i]['score'] = normalizeStats($s['raw'], $s['breakpoints'] );
		}

	}

	return ($stats);
}

/******************************************************************************/

function getFighterStats($systemRosterID,  $minExchanges = 0){

	$fighterStats = [];

	$weaponScore = 0;
	$sql = "SELECT COUNT(*) AS numEntries
			FROM eventTournamentRoster
			INNER JOIN eventTournaments USING(tournamentID)
			INNER JOIN eventRoster USING(rosterID)
			WHERE systemRosterID = {$systemRosterID}
			GROUP BY tournamentWeaponID";
	$tournamentWeapons = (array)mysqlQuery($sql, SINGLES, 'numEntries');

	$weaponScore = 0;
	if($tournamentWeapons != []){

		$numWeapons = count($tournamentWeapons);
		$sumOfSquares = 0;
		$highestWeapon = 0;

		foreach($tournamentWeapons as $w){

			$weaponScore += min($w, 3);
			$sumOfSquares += ($w * $w);
			if($w > $highestWeapon){
				$highestWeapon = $w;
			}
		}


		$weaponScore /= $highestWeapon;
		$weaponScore *= 5;

		$weaponScore = min($weaponScore, 100);
	}


	$fighterStats['weaponScore'] = $weaponScore;

	$sql = "SELECT exchangeType, scoreValue, scoreDeduction, 1 AS isFighter
			FROM eventExchanges AS eE
			INNER JOIN eventRoster AS eR ON eE.scoringID = eR.rosterID
			WHERE systemRosterID = {$systemRosterID}
			AND exchangeType IN('noExchange','clean','afterblow','double')";
	$exchFor = (array)mysqlQuery($sql, ASSOC);

	if(count($exchFor) < $minExchanges/2){
		return [];
	}

	$sql = "SELECT exchangeType, scoreValue, scoreDeduction, 0 AS isFighter
			FROM eventExchanges AS eE
			INNER JOIN eventRoster AS eR ON eE.receivingID = eR.rosterID
			WHERE systemRosterID = {$systemRosterID}
			AND exchangeType IN('noExchange','clean','afterblow','double')";
	$exchAgainst = (array)mysqlQuery($sql, ASSOC);

	$allExchanges = array_merge(@(array)$exchFor, @(array)$exchAgainst);



	foreach($allExchanges as $e){

		switch($e['exchangeType']){

			case 'noExchange': {
				@$fighterStats['noExchange']++;
				@$fighterStats['totalExchange']++;
				break;
			}

			case 'afterblow':
			{

				if($e['scoreValue'] > $e['scoreDeduction'] && $e['isFighter'] == 1){
					@$fighterStats['abFor']++;

					if($e['scoreValue'] - $e['scoreDeduction'] > 1){
						@$fighterStats['multiPointExch']++;
					}
				}

				if($e['scoreValue'] > $e['scoreDeduction'] && $e['isFighter'] == 0){
					@$fighterStats['abAgainst']++;
				}

				@$fighterStats['bilaterals']++;

				@$fighterStats['totalExchange']++;
				break;
			}

			case 'clean':
			{

				if($e['isFighter'] == 1){
					if($e['scoreValue'] > 1){
						@$fighterStats['multiPointExch']++;
					}
					@$fighterStats['cleanFor']++;
					@$fighterStats['totalExchange']++;
				}

				break;
			}

			case 'double':
			{
				@$fighterStats['Bilaterals']++;
				@$fighterStats['totalExchange']++;
				break;
			}
		}

	}

	if(@$fighterStats['totalExchange'] == 0){
		$fighterStats['totalExchange'] = 1;
	}

	return $fighterStats;
}

/******************************************************************************/

function showScore($value){

	echo "|";
	for($i = 0; $i < $value; $i += 3){
		echo "|";
	}
	//echo $value;

}

/******************************************************************************/


function normalizeStats($value, $brkPts){

	$numPts = sizeof($brkPts);

	for($i = 0; $i < ($numPts - 1); $i++){

		if($value <= $brkPts[$i+1]){

			$span = $brkPts[$i+1] - $brkPts[$i];
			$pct = ($value - $brkPts[$i])/$span;

			$score = $i * 20 + $pct * 20;
			$score = round($score);


			break;
		}

	}

/*
	echo "<HR>";
	echo "Value: <b>{$value}</b> | Score: <b>{$score}</b>";
	echo "<BR>";
	foreach($brkPts as $i => $v){
		echo "{$v} -> {$i}  || ";
	}
*/


	return $score;
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
