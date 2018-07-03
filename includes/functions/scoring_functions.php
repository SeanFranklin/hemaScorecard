<?php
/*******************************************************************************
	Scoring Functions

	Functions used in scoring
	Displaying calculating scores, recording scores, displaying results
	Makes calls to the DB directly

*******************************************************************************/



/*******************************************************************************/

function _DeductionBased_addExchanges($numToAdd, $matchInfo){
// Add exchanges using 'Deduction Based' scoring algorithm

	if(USER_TYPE < USER_STAFF){return;}

	$matchID = $matchInfo['matchID'];
	if($matchID == null){return;}
	
	$sql = "SELECT exchangeID
			FROM eventExchanges
			WHERE matchID = {$matchID}";
	$res = mysqlQuery($sql, SINGLE);
	
	$basePointValue = getBasePointValue(null, $_SESSION['groupSet']);

	if($res == null){
		insertLastExchange($matchInfo, 'scored', 'null', $basePointValue, 0);
		if($numToAdd == 0){
			updateMatch($matchInfo);
			if(isLastPiece($_SESSION['tournamentID'])){
				$_SESSION['askForFinalization'] = true;
			}
		}
	}
	
	for($i=1;$i<=$numToAdd;$i++){
		insertLastExchange($matchInfo, 'pending', 'null', 0, 0);	
	}
	
	
	
}

/******************************************************************************/

function _DeductionBased_displayExchange($exchange,$exchangeNum = null){
// Displays an exchange for pieces using 'Deduction Based' scoring
// A request to generate a header may be passed instead of an exchange

	$basePoints = getBasePointValue(null, $_SESSION['groupSet']);
	?>
	
<!-- Table Headers -->
	<?php if($exchangeNum == 'header'): ?>
	
		<tr>
		<?php if(USER_TYPE >= USER_STAFF): ?>
			<th></th>
		<?php endif ?>
			<th></th>
			<th>Deduction</th>
		</tr>
		
		<?php return; ?>
	<?php endif ?>
	
	<?php
	
	// Code Block /////////////////////////////
	
	$exchangeNum--;
	$exchangeID = $exchange['exchangeID'];
	
	if($exchange['exchangeType'] == 'scored'){
		$against = $exchange['scoreDeduction'];
		$for = $exchange['scoreValue'];
	} else {
		if(USER_TYPE < USER_STAFF){
			return;
		}
		$placeholder="placeholder='Enter Deduction'";
	}
	?>
	
	<tr>
		<?php if(USER_TYPE >= USER_STAFF): ?>
			<?php if($exchangeNum == 0): ?>
				<td><input type='checkbox' name='exchangesToDelete[all]'></td>
			<?php else: ?>
				<td><input type='checkbox' name='exchangesToDelete[<?=$exchangeID?>]'></td>
			<?php endif ?>
		<?php endif ?>
	
		<td class='text-right'>
			<?php if($exchangeNum == 0): ?>
				<strong>Base Points:</strong>
			<?php else: ?>
				#<?=$exchangeNum?>
			<?php endif ?>
		</td>
	
	<!-- Exchanges -->
		<td class='text-center'>
			<?php if(USER_TYPE >= USER_STAFF): ?>
				<?php if($exchangeNum == 0): ?>
					<input type='number' step='0.1' class='no-bottom' 
						value='<?=$for?>' name=scores[<?=$exchangeID?>][value]>
				<?php else: ?>
					<input type='number' step='0.1' class='no-bottom' <?=$placeholder?>
						value='<?=$against?>' name=scores[<?=$exchangeID?>][deduction] >
				<?php endif ?>
				
			<?php else: ?> 
				<?php if($exchangeNum == 0): ?>
					<strong><?=$for?></strong>
				<?php else: ?>
					<?php if($against != 0): ?>
						- <!-- against scores are displayed as negative values -->
					<?php endif ?>
					<?=$against?> 
				<?php endif ?>
			<?php endif ?>
		</td>
	
	</tr>
	
<?php }

/******************************************************************************/

function _DeductionBased_updateExchanges(){

	if(USER_TYPE < USER_STAFF){return;}

	foreach((array)$_POST['scores'] as $exchangeID => $data){
	
		$exchangesToUpdate[$exchangeID]['scoreValue'] = (float)$data['value'];
		$exchangesToUpdate[$exchangeID]['scoreDeduction'] = (float)$data['deduction'];
	}

	return $exchangesToUpdate;
	
}

/******************************************************************************/

function _RSScutting_addExchanges($numToAdd, $matchInfo){
// Adds cuts to a piece using the 'RSS Cutting' format

	for($i=1;$i<=$numToAdd;$i++){
		insertLastExchange($matchInfo, 'pending', 'null', 20, 0);	
	}
	
}

/******************************************************************************/

function _RSScutting_displayExchange($exchange,$exchangeNum){
// Displays an exchange for pieces using 'RSS Cutting' scoring
// A request to generate a header may be passed instead of an exchange

	$basePointValue = getBasePointValue(null, $_SESSION['groupSet']);
	?>
	
<!--  Table Headers -->
	<?php if($exchangeNum == 'header'): ?>
		<tr>
			<th>
				<?php if(USER_TYPE < USER_STAFF): ?>
					<strong>Cut Num</strong>
				<?php endif ?>
			</th>
		
			<?php if(USER_TYPE >= USER_STAFF): ?>
				<th class='text-center' style='padding: 1px;'>
					Form
				</th>
				<th class='text-center' style='padding: 1px;'>
					&nbsp;&nbsp;Cut&nbsp;&nbsp;
				</th>
			<?php endif ?>
		
			<th class='text-center'>
				Penalty
			</th>
			<th class='text-center'>
				Points
			</th>
		</tr>
		
		<?php return; ?>
	<?php endif ?>
<?php

// Calculations
	$exchangeID = $exchange['exchangeID'];
	
	if($exchange['exchangeType'] == 'scored'){
		$for = $exchange['scoreValue'];
		$against = $exchange['scoreDeduction'];
		$total = $for - $against;
		$zeroVal = '';
		$placeholder = "placeholder='No Change'";
	} else {
		if(USER_TYPE < USER_STAFF){ return;}
		$total = 'N/A';
		$against = 'N/A';
		$zeroVal = '';
		$placeholder = "placeholder='0'";
	}
	?>
	
<!-- Table Row -->
	<tr class='text-center'>
	
		<td>
		<?=$exchangeNum?>
		<?php if(USER_TYPE >= USER_STAFF): ?>
			<input type='checkbox' class='no-bottom' name='exchangesToDelete[<?=$exchangeID?>]'>
			<input type='number' class='hidden' value=<?=$basePointValue?> name=scores[<?=$exchangeID?>][for]>
		<?php endif ?>
		</td>
	

		<?php if(USER_TYPE >= USER_STAFF): ?>
			<td style='padding: 1px;'>
				<input type='number' step='0.1' class='no-bottom' <?=$placeholder?>
					value='<?=$zeroVal?>' name=scores[<?=$exchangeID?>][form] >
			</td>
			<td style='padding: 1px;'>
				<input type='number' step='0.1' class='no-bottom' <?=$placeholder?>
					value='<?=$zeroVal?>' name=scores[<?=$exchangeID?>][cut]>
			</td>
		<?php endif ?>

		<td>
			<?php if($against > 0): ?> 
				- <!-- Points against are displayed as negative-->
			<?php endif ?>
			<?=$against;?>
			<input type='number' step='0.1' style='width: 100%; display:none' 
				value='<?=$against?>' name=scores[<?=$exchangeID?>][against]>
		</td>
	
		<td>
			<?=$total?>
		</td>
	</tr>
<?php }

/******************************************************************************/

function _RSScutting_updateExchanges(){
// Calculates the score of each cut for pieces using 'RSS Cutting'	
	
	foreach((array)$_POST['scores'] as $exchangeID => $data){
		
		$for = (float)$data['for'];
		
		if($data['against'] == 'N/A'){
			$a = (float)$data['form'];
			$b = (float)$data['cut'];
		} else {
			$a = $data['form'];
			$b = $data['cut'];
			
			if($a !== '' || $b !== ''){
				$a = (float)$a;
				$b = (float)$b;
			} else {
				$a = $data['against'];
				$b = 0;
			}
		}

		$deduction = round(sqrt($a*$a + $b*$b),1);
		
		if($deduction > $for){
			if($a <= $for AND $b <= $for){
				$deduction = $for;
			} else {
				$deduction = max($a,$b);
			}
		}
		
		$exchangesToUpdate[$exchangeID]['scoreValue'] = $for;
		$exchangesToUpdate[$exchangeID]['scoreDeduction'] = $deduction;

	}

	return $exchangesToUpdate;
	
}

/******************************************************************************/

function pool_ScoreFighters($tournamentID, $groupSet = 1){
// Calls the appropriate funciton to score fighters given the tournament
// scoring algorithm
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No tournamentID in pool_ScoreFighters()";
		return;
	}
	
	$formula = getScoreFormula($tournamentID);

	$sql = "UPDATE eventStandings
			SET score = ($formula)
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'pool'
			AND groupSet = {$groupSet}";

	mysqlQuery($sql, SEND);
	
}


/******************************************************************************/

function pool_DisplayResults($tournamentID, $groupSet = 1){
// Calls the appropriate funciton to display the fighters pool standings 
// given the tournament scoring algorithm

	$bracketInfo = getBracketInformation($tournamentID);
	$numToElims = $bracketInfo['winner']['numFighters'];
	$maxNumFields = 5;

	$sql = "SELECT displayTitle1, displayField1,
			displayTitle2, displayField2,
			displayTitle3, displayField3,
			displayTitle4, displayField4,
			displayTitle5, displayField5
			FROM eventTournaments
			INNER JOIN systemRankings USING(tournamentRankingID)
			WHERE tournamentID = {$tournamentID}";
	$displayMeta = mysqlQuery($sql, SINGLE);

	// Check to see which fields are used
	for($i = 1; $i <= $maxNumFields; $i++){
		if($displayMeta['displayTitle'.$i] == '' || $displayMeta['displayField'.$i] == ''){
			$maxNumFields = $i - 1;
			break;
		}
		$tmpStr = $displayMeta['displayField'.$i];
		$selectStr .= ", {$tmpStr}";
	}

	$sql = "SELECT rank, rosterID {$selectStr}
			FROM eventStandings
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'pool'
			AND groupSet = {$groupSet}
			ORDER BY rank ASC";
	$displayInfo = mysqlQuery($sql, ASSOC);


	echo "<table>";

	// Header row
	echo "<tr>";
	echo "<th>Rank</th>";
	echo "<th>Name</th>";
	for($i = 1; $i <= $maxNumFields; $i++){
		$index = "displayTitle".$i;
		$name = $displayMeta[$index];

		echo "<th>{$name}</th>";	
	}
	echo "</tr>";

	foreach($displayInfo as $fighter){
		if($fighter['rank'] == $numToElims){
			$class = 'last-to-elims';
		} else {
			$class = null;
		}

		echo "<tr class='text-center {$class}'>";
		echo "<td>".$fighter['rank']."</td>";
		echo "<td  class='text-left'>".getFighterName($fighter['rosterID'])."</td>";
		for($i = 1; $i <= $maxNumFields; $i++){
			$index = $displayMeta["displayField".$i];
			$value = round($fighter[$index],1) + 0;

			echo "<td>{$value}</td>";
		}

		echo "</tr>";
	}


	echo"</table>";

}

/******************************************************************************/

function pool_GenerateNextPools($poolSet){
// Calculate advancements to move onto the next pool set using the parameters specified.
// Parameters are the number of 
			
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}

	// determine which algorithim to use
	$maxPoolSize = maxPoolSize();
	$poolsInTier = $_POST['poolsInTier'];
	if($poolsInTier == 0 || $poolsInTier == null){
		$poolsInTier = 9999;
	}

	if(isset($_POST['avoidRefights'])){
		$avoidRefights = true;
	}

	$poolSet = $_SESSION['groupSet'];
	$lastPoolSet = $poolSet - 1;
	
	$standings = getTournamentStandings($tournamentID, $lastPoolSet, 'pool', 'advance');

	$poolNum = 1;
	$poolPosition = 0;
	
	$tierSize = $poolsInTier * $maxPoolSize;
	
	// Generate Tiers
	$tier = 1;
	$numFightersInTier = 0;
	foreach($standings as $fighter){
		$numFightersInTier++;
		if($numFightersInTier > $tierSize){
			$tier++;
			$numFightersInTier = 1;
		}
		$poolTier[$tier][$numFightersInTier] = $fighter['rosterID'];
	}
	
	if($poolTier == null){ return; }
	$tournamentID = $_SESSION['tournamentID'];

	// Split Tiers into Pools
	foreach($poolTier as $tier => $fightersInTier){
		
		// Get list of how many times each fighter has fought each other
		foreach($fightersInTier as $rosterID1){
			foreach($fightersInTier as $rosterID2){
				$numberOfFightsTogether[$rosterID1][$rosterID2] = 
					getNumberOfFightsTogether($rosterID1, $rosterID2, $tournamentID);
			}
		}
		

		$tierRank = 1;
		$startPoolNum = 1 + $poolsInTier*($tier-1);
		$maxPoolNum = $startPoolNum + $poolsInTier - 1;
		$MAX = count(getPools($tournamentID, $_SESSION['groupSet']));
		if($maxPoolNum > $MAX){
			$maxPoolNum = $MAX;
		}
		$poolNum = $startPoolNum;
		$poolPosition = 1;
		$poolNumIncrement = 1;
		show($avoidRefights);
		
		foreach($fightersInTier as $rosterIDtoAdd){
			

			if(!$avoidRefights){

				$_SESSION['poolSeeds'][$poolNum][$poolPosition] = $rosterIDtoAdd;
				$poolNum += $poolNumIncrement;
				if($poolNum > $maxPoolNum){
					$poolNum = $maxPoolNum;
					$poolPosition++;
					$poolNumIncrement = -1;
				} elseif($poolNum < $startPoolNum){
					$poolNum = $startPoolNum;
					$poolPosition++;
					$poolNumIncrement = 1;
				}

			} else {

				// Determine which pools are eligible on account of not being full
				unset($eligiblePools_Size);
				for($i = $startPoolNum; $i <= $maxPoolNum; $i++){
					$fightersInPool = count($_SESSION['poolSeeds'][$i]);
					if($fightersInPool < $maxPoolSize){
						$eligiblePools_Size[$i] = $fightersInPool;
					}
				}
				if($eligiblePools_Size == null){continue;}

				// Check for conflict levels in each pool
				unset($possiblePoolRefights);
				foreach($eligiblePools_Size as $poolNum => $fightersInPool){
					for($i=1;$i <= $fightersInPool; $i++){
						$existingRosterID = $_SESSION['poolSeeds'][$poolNum][$i];
						$possiblePoolRefights[$poolNum] += $numberOfFightsTogether[$rosterIDtoAdd][$existingRosterID];
					}
					$possiblePoolRefights[$poolNum] += $poolRefights[$poolNum];
				}
				
				
				
				// Find possible pools with lowest total refights
				$minRefights = 9999;
				unset($eligiblePools_Conflicts);
				foreach($possiblePoolRefights as $poolNum => $numRefights){
					if($numRefights == $minRefights){
						$eligiblePools_Conflicts[$poolNum] = $possiblePoolRefights[$poolNum];
					} else if($numRefights < $minRefights){
						unset($eligiblePools_Conflicts);
						$eligiblePools_Conflicts[$poolNum] = $possiblePoolRefights[$poolNum];
						$minRefights = $numRefights;
					}
				}
				
				// Chose Most Empty Pools
				$mostEmptyPool = 9999;
				unset($eligiblePools_Combined);
				foreach($eligiblePools_Conflicts as $poolNum => $possibleRefights){
					$fightersInPool = $eligiblePools_Size[$poolNum];
					if($fightersInPool == $mostEmptyPool){
						$eligiblePools_Combined[] = $poolNum;
					} else if($fightersInPool < $mostEmptyPool){
						unset($eligiblePools_Combined);
						$eligiblePools_Combined[] = $poolNum;
						$mostEmptyPool = $fightersInPool;
					}
				}
				
				
				// Stick in first pool if not resolved
				$poolNum = $eligiblePools_Combined[0];
				$poolRefights[$poolNum] += $possiblePoolRefights[$poolNum];
				$poolPosition = $eligiblePools_Size[$poolNum]+1;
			
				$_SESSION['poolSeeds'][$poolNum][$poolPosition] = $rosterIDtoAdd;
			}
		}
			
	}

}


/******************************************************************************/

function pool_NormalizeSizes($fighterStats, $tournamentID, $groupSet = 1){
// Normalizes the raw exchange data of all fighters to the
// value that the normalize pool size has been set to	
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	
	if(isCumulative($groupSet, $tournamentID)){
		$numberOfMatches = getNormalizationCumulative($tournamentID, $groupSet) - 1;
	} else {
		$numberOfMatches = getNormalization($tournamentID, $groupSet) - 1;
	}
	


	foreach((array)$fighterStats as $rosterID => $fighterData){

		$matchesFought = $fighterData['matches'];
		$correction = $numberOfMatches/$matchesFought;
		if($correction == 1){ continue; }

		foreach($fighterData as $dataIndex => $data){
			if($dataIndex == 'groupID'){ continue; }

			$fighterStats[$rosterID][$dataIndex] = round($data * $correction,3);
			
		}
	}

	return $fighterStats;
	
}

/******************************************************************************/

function pool_RankFighters($tournamentID, $groupSet = 1){
// Assigns a rank to all fighters in the given group set

	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No tournamentID in pool_RankFighters()";
		return;
	}

// Load meta data on how to rank fighters
	$sql = "SELECT groupWinnersFirst, 
			orderByField1, orderBySort1,
			orderByField2, orderBySort2,
			orderByField3, orderBySort3
			FROM eventTournaments
			INNER JOIN systemRankings USING(tournamentRankingID)
			WHERE tournamentID = {$tournamentID}";

	$meta = mysqlQuery($sql, SINGLE);

// Check which of the ORDER BY fields are valid/used	
	if($meta['orderByField1'] == '' 
		|| ($meta['orderBySort1'] != 'ASC' && $meta['orderBySort1'] != 'DESC')){
		$_SESSION['alertMessages']['systemErrors'][] = "Invalid sort data in sort_Simple()";
		return;
	}

	$orderBy = "ORDER BY {$meta['orderByField1']} {$meta['orderBySort1']}";

	if($meta['orderByField2'] != '' 
		&& ($meta['orderBySort2'] == 'ASC' || $meta['orderBySort2'] == 'DESC')){
			$orderBy .= ", {$meta['orderByField2']} {$meta['orderBySort2']}";

	}

	if($meta['orderByField3'] != '' 
		&& ($meta['orderBySort3'] == 'ASC' || $meta['orderBySort3'] == 'DESC')){
			$orderBy .= ", {$meta['orderByField3']} {$meta['orderBySort3']}";

	}

// Retrieve List
	$sql = "SELECT standingID, groupID
			FROM eventStandings
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'pool'
			AND groupSet = {$groupSet}
			{$orderBy}";

	$rankedList = mysqlQuery($sql, ASSOC);

	$numWinners = (int)$meta['groupWinnersFirst'];
	$winnerPlacing = 1;
	$loserPlacing = 1;

// Initialize variables for placing pool winners first (if used)
	if($numWinners >= 1){
		$sql = "SELECT COUNT(DISTINCT(groupID)) AS numGroups
				FROM eventStandings
				WHERE tournamentID = {$tournamentID}
				AND groupType = 'pool'
				AND groupSet = {$groupSet}";

		$numGroups = mysqlQuery($sql, SINGLE, 'numGroups');

		$loserPlacing = 1 + ($numGroups * $numWinners);
	}

	
// Update each fighter with their placing
	foreach($rankedList as $standing){
		$groupID = $standing['groupID'];
		$standingID = $standing['standingID'];

		if($winnersInGroup[$groupID] < $numWinners){
			$winnersInGroup[$groupID]++;
			$placing = $winnerPlacing;
			$winnerPlacing++;
		} else {
			$placing = $loserPlacing;
			$loserPlacing++;
		}

		$sql = "UPDATE eventStandings
				SET rank = {$placing}
				WHERE standingID = {$standingID}";
			
		mysqlQuery($sql, SEND);

	}
	
}

/******************************************************************************/

function scored_AddExchanges(){
// Select the appropriate function to add exchanges to a piece given
// the tournament format
	
	if(USER_TYPE < USER_STAFF){return;}

	if($matchID == null){$matchID = $_SESSION['matchID'];}
	if($matchID == null){return;}

	$numToAdd = (int)$_POST['exchangesToAdd'];

	$matchInfo = getMatchInfo($matchID);

	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	
// determine which algorithim to use
	$funcName = getScoringFunctionName($tournamentID);
	if($funcName == null){return;}
	$funcName = "_".$funcName."_addExchanges";
	
	call_user_func($funcName,$numToAdd,$matchInfo);
	
}

/******************************************************************************/

function scored_DisplyExchanges($matchID = null){
// Select the correct function to display the exchanges of a piece
	
	if($matchID == null){$matchID = $_SESSION['matchID'];}
	if($matchID == null){return;}
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	

// Determine which algorithim to use	
	$funcName = getDisplayFunctionName($tournamentID);
	if($funcName == null){return;}
	$funcName = "_".$funcName."_displayExchange";

	$exchanges = getMatchExchanges($matchID);
	$i = 0;

	echo "<table>";
// Display Header
	call_user_func($funcName,$exchange,'header');

// Display Exchanges	
	foreach($exchanges as $exchange){
		call_user_func($funcName,$exchange,++$i);	
	}
	echo "</table>";

}

/******************************************************************************/

function scored_UpdateExchanges($tournamentID = null){
// Select the correct function to update the exchanges of a piece
	
	if(USER_TYPE < USER_STAFF){return;}
	
	if(!isset($_POST['scores'])){return;}
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	
// determine which algorithim to use
	$funcName = getScoringFunctionName($tournamentID);
	if($funcName == null){return;}
	$funcName = "_".$funcName."_updateExchanges";

// Get Exchanges to Update & Score values
	$exchangesToUpdate = call_user_func($funcName);

// Update Exchanges
	foreach((array)$exchangesToUpdate as $exchangeID => $data){
		$scoreValue = (float)$data['scoreValue'];
		$scoreDeduction = (float)$data['scoreDeduction'];
		
		$sql = "UPDATE eventExchanges
				SET scoreValue = {$scoreValue}, 
				scoreDeduction = {$scoreDeduction},
				exchangeType = 'scored'
				WHERE exchangeID = {$exchangeID}";
		mysqlQuery($sql, SEND);
	}
	
	updateMatch(getMatchInfo($matchID));
	
	if(isLastPiece($tournamentID)){
		$_SESSION['askForFinalization'] = true;
	}

}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
