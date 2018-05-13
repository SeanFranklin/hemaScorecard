<?php
/*******************************************************************************
	Scoring Functions

	Functions used in scoring
	Displaying calculating scores, recording scores, displaying results
	Makes calls to the DB directly

*******************************************************************************/

/******************************************************************************/

function _2point_display($entry, $class = null){
// Displays an a fighter's score for tournaments using 'FNY 2017' scoring
// A request to generate a header may be passed instead of an exchange
// In this algorithm a win is denoted as a 'pointFor' and 
// 'pushes' are wins with pointFor = 0
	?>
	
<!-- Headers -->
	<?php if($entry == 'headers'): ?>
		<tr>
			<th>Rank</th>
			<th>Name</th>
			<th>Wins</th>
			<th>Ties</th>
			<th>Losses</th>
			<th>Score</th>
		</tr>
		<?php return; ?>
	<?php endif ?>

<!-- Data -->
	<tr class='text-center <?=$class?>'>
		<td><?=$entry['rank']?></td>
		<td class='text-left'>
			<?=getFighterName($entry['rosterID']);?>
		</td>
		<td><?=$entry['wins']?></td>
		<td><?=$entry['ties']?></td>
		<td><?=$entry['losses']?></td>
		<td><?=$entry['score']?></td>
	</tr>
	
<?php }

/******************************************************************************/

function _2point_scores($fighterStats, $poolSet = 1){
// Calculate scores for tournaments using the 'FNY 2017' algorthm.
// Cumulative scoring across all pools unless it has been specified as not

	unset($fighterStats);

	$tournamentID = TOURNAMENT_ID;
// Check if it is cumulative or not
	$sql = "SELECT attributeBool
			FROM eventAttributes
			WHERE tournamentID = {$tournamentID}
			AND attributeType = 'cumulative'
			AND attributeGroupSet = {$poolSet}";
	$res = mysqlQuery($sql, SINGLE, 'attributeBool');
	
	if($res === '0'){
		$lowBound = $poolSet;
	} else {
		$lowBound = 1;
	}
		
	for($i = $poolSet; $i >= $lowBound; $i--){

		$poolExchanges = getAllTournamentExchanges($tournamentID, 'pool', $i);
		$exchangesInSet = pool_normalizeSizes($poolExchanges,$tournamentID, $poolSet);
		
		foreach((array)$exchangesInSet as $fighterID => $fighter){
			// only calculate score if fighter has exchanges in the current pool set
			if($i < $poolSet AND $fighterStats[$fighterID]['rosterID'] == null){
				continue;
			}

			$score = 0;
			$score += 2*$fighter['wins'];
			$score += $fighter['ties'];
			
			foreach($fighter as $index => $value){
				if($index == 'score' || $index == 'rosterID'){continue;}
				$fighterStats[$fighterID][$index] += $value;
			}

			$fighterStats[$fighterID]['score'] += $score;
			$fighterStats[$fighterID]['rosterID'] = $fighterID;
			
		}
		
	}
	
	return $fighterStats;
	
}

/******************************************************************************/

function _BasicStats_display($entry, $class = null){
// Simple display of tournament standings
	?>
	
	<?php if($entry == 'headers'): ?>
		<tr>
			<th>Rank</th>
			<th>Name</th>
			<th>Wins</th>
			<th>For</th>
			<th>Against</th>
			<th>Doubles</th>
			<th>Score</th>
		</tr>
		<?php return; ?>
	<?php endif ?>
	
	<tr class='text-center <?=$class?>'>
		<td><?=$entry['rank']?></td>
		<td class='text-left'>
			<?=getFighterName($entry['rosterID']);?>
		</td>
		<td><?=$entry['wins']?></td>
		<td><?=$entry['pointsFor']?></td>
		<td><?=$entry['pointsAgainst']?></td>
		<td><?=$entry['doubles']?></td>
		<td><?=$entry['score']?></td>
	</tr>
	
<?php }

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

function _Franklin2014_scores($fighterStats){
// Calculate scores using 'Franklin 2014' algorithm
	
	foreach((array)$fighterStats as $fighterID => $fighter){

		$doubles = $fighter['doubles'];
		$doublesPenalty = ($doubles * ($doubles+1))/2;
		
		$score = 0;
		$score += $fighter['pointsFor'];
		$score -= $fighter['pointsAgainst'];
		$score += 5*$fighter['wins'];
		$score -= $doublesPenalty;
		
		$score = round($score,1);
		
		$fighterStats[$fighterID]['score'] = $score;
		$fighterStats[$fighterID]['rosterID'] = $fighterID;
	}

	return $fighterStats;

}

/******************************************************************************/

function _Franklin2014x25_scores($fighterStats){
// Calculate scores using 'Franklin 2014' algorithm
	
	foreach((array)$fighterStats as $fighterID => $fighter){
		$doubles = $fighter['doubles'];
		$doublesPenalty = ($doubles * ($doubles+1))/2;
		
		$score = 0;
		$score += $fighter['pointsFor'];
		$score -= $fighter['pointsAgainst'];
		$score += 5*$fighter['wins'];
		$score -= ($doublesPenalty*1.25);
		
		$score = round($score,1);
		
		$fighterStats[$fighterID]['score'] = $score;
		$fighterStats[$fighterID]['rosterID'] = $fighterID;
	}

	return $fighterStats;

}

/******************************************************************************/

function _Flowerpoint_advancements(){
// Calculate advancements to move onto the next pool set using the 'Flowerpoint'
// algorithm. Attempts to balance the pool size while minimizing the number 
// of times people fight people in the pool they have already fought
	
	$maxPoolSize = maxPoolSize();
	$poolsInTier = $_POST['poolsInTier'];
	if($poolsInTier == 0 || $poolsInTier == null){
		$poolsInTier = 9999;
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
		
		
		foreach($fightersInTier as $rosterIDtoAdd){
			
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

/******************************************************************************/

function _Flowerpoint_display($entry, $class = null){
// Displays an a fighter's score for tournaments using 'Flowerpoint' scoring
// A request to generate a header may be passed instead of an exchange
	?>
	
<!-- Headers -->
	<?php if($entry == 'headers'): ?>
		<tr>
			<th>Rank</th>
			<th>Name</th>
			<th>Number of Times Hit</th>
			<th>Doubles</th>
			<th>Score</th>
		</tr>
		<?php return; ?>
	<?php endif ?>
	
<!-- Data -->
	<tr class='text-center <?=$class?>'>
		<td><?=$entry['rank']?></td>
		<td class='text-left'>
			<?=getFighterName($entry['rosterID']);?>
		</td>
		<td><?=$entry['hitsAgainst']?></td>
		<td><?=$entry['doubles']?></td>
		<td><?=$entry['score']?></td>
	</tr>
	
<?php }

/******************************************************************************/

function _Flowerpoint_scores($fighterStats, $poolSet = 1){
// Calculate scores for tournaments using the 'Flowerpoint' algorthm.
// Cumulative scoring across all pools unless it has been specified as not

	unset($fighterStats);

	$tournamentID = TOURNAMENT_ID;
// Check if it is cumulative or not
	$sql = "SELECT attributeBool
			FROM eventAttributes
			WHERE tournamentID = {$tournamentID}
			AND attributeType = 'cumulative'
			AND attributeGroupSet = {$poolSet}";
	$res = mysqlQuery($sql, SINGLE, 'attributeBool');
	
	if($res === '0'){
		$lowBound = $poolSet;
	} else {
		$lowBound = 1;
	}
	
	
	$poolSize = getNormalization($tournamentID, $poolSet);

	for($i = $poolSet; $i >= $lowBound; $i--){

		$poolExchanges = getAllTournamentExchanges($tournamentID, 'pool', $i);
		$exchangesInSet = pool_normalizeSizes_static($poolExchanges,$poolSize);
		
		foreach((array)$exchangesInSet as $fighterID => $fighter){
			// only calculate score if fighter has exchanges in the current pool set
			if($i < $poolSet AND $fighterStats[$fighterID]['rosterID'] == null){
				continue;
			}
			
			$score = 0;
			$score -= $fighter['hitsAgainst'];
			$score -= $fighter['doubles'];
			
			foreach($fighter as $index => $value){
				if($index == 'score' || $index == 'rosterID'){continue;}
				$fighterStats[$fighterID][$index] += $value;
			}

			$fighterStats[$fighterID]['score'] += $score;
			$fighterStats[$fighterID]['rosterID'] = $fighterID;
			
		}
		
	}
	
	return $fighterStats;
	
}

/******************************************************************************/

/******************************************************************************/

function _FNY2017_advancements(){
// Calculate advancements to move onto the next pool set using the 'FNY 2017'
// algorithm. Attempts to balance the pool size while minimizing the number 
// of times people fight people in the pool they have already fought
	
	$maxPoolSize = maxPoolSize();
	$poolsInTier = $_POST['poolsInTier'];
	if($poolsInTier == 0 || $poolsInTier == null){
		$poolsInTier = 9999;
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
		
		
		foreach($fightersInTier as $rosterIDtoAdd){
			
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

/******************************************************************************/

function _FNY2017_display($entry, $class = null){
// Displays an a fighter's score for tournaments using 'FNY 2017' scoring
// A request to generate a header may be passed instead of an exchange
// In this algorithm a win is denoted as a 'pointFor' and 
// 'pushes' are wins with pointFor = 0
	?>
	
<!-- Headers -->
	<?php if($entry == 'headers'): ?>
		<tr>
			<th>Rank</th>
			<th>Name</th>
			<th>Wins</th>
			<th>Pushes</th>
			<th>Losses</th>
			<th>Doubles</th>
			<th>Score</th>
		</tr>
		<?php return; ?>
	<?php endif ?>
	
	<?php
		$pushes = $entry['matches'] - $entry['hitsFor'];
		$pushes += (-$entry['losses'] - $entry['doubles']);
		$pushes = round($pushes,1);
		if($pushes <= 0.2) {$pushes = 0; }
	?>

<!-- Data -->
	<tr class='text-center <?=$class?>'>
		<td><?=$entry['rank']?></td>
		<td class='text-left'>
			<?=getFighterName($entry['rosterID']);?>
		</td>
		<td><?=$entry['hitsFor']?></td>
		<td><?=$pushes?></td>
		<td><?=$entry['losses']?></td>
		<td><?=$entry['doubles']?></td>
		<td><?=$entry['score']?></td>
	</tr>
	
<?php }

/******************************************************************************/

function _FNY2017_scores($fighterStats, $poolSet = 1){
// Calculate scores for tournaments using the 'FNY 2017' algorthm.
// Cumulative scoring across all pools unless it has been specified as not

	unset($fighterStats);

	$tournamentID = TOURNAMENT_ID;
// Check if it is cumulative or not
	$sql = "SELECT attributeBool
			FROM eventAttributes
			WHERE tournamentID = {$tournamentID}
			AND attributeType = 'cumulative'
			AND attributeGroupSet = {$poolSet}";
	$res = mysqlQuery($sql, SINGLE, 'attributeBool');
	
	if($res === '0'){
		$lowBound = $poolSet;
	} else {
		$lowBound = 1;
	}
	
	$poolSize = getNormalization($tournamentID, $poolSet);

	for($i = $poolSet; $i >= $lowBound; $i--){

		$poolExchanges = getAllTournamentExchanges($tournamentID, 'pool', $i);
		$exchangesInSet = pool_normalizeSizes_static($poolExchanges,$poolSize);
		
		foreach((array)$exchangesInSet as $fighterID => $fighter){
			// only calculate score if fighter has exchanges in the current pool set
			if($i < $poolSet AND $fighterStats[$fighterID]['rosterID'] == null){
				continue;
			}

			$score = 0;
			$score += $fighter['pointsFor'];
			$score -= 2*$fighter['losses'];
			$score -= 2*$fighter['doubles'];
			
			foreach($fighter as $index => $value){
				if($index == 'score' || $index == 'rosterID'){continue;}
				$fighterStats[$fighterID][$index] += $value;
			}

			$fighterStats[$fighterID]['score'] += $score;
			$fighterStats[$fighterID]['rosterID'] = $fighterID;
			
		}
		
	}
	
	return $fighterStats;
	
}

/******************************************************************************/

function _PlusMinus_scores($fighterStats){ 
// Calculate scores using 'Plus Minus' scoring algorithm
// pointsFor - pointsAgains - doublesPenalty
	
	foreach($fighterStats as $fighterID => $fighter){
		$doubles = $fighter['doubles'];
		$doublesPenalty = ($doubles * ($doubles+1))/2;
		
		$score = 0;
		$score += $fighter['pointsFor'];
		$score -= $fighter['pointsAgainst'];
		$score -= $fighter['penaltiesAgainst'];
		$score -= $doublesPenalty;
		
		$score = round($score,1);
		
		$fighterStats[$fighterID]['score'] = $score;
		$fighterStats[$fighterID]['rosterID'] = $fighterID;
	}
	
	return $fighterStats;
	
}

/******************************************************************************/

function _PointsFor_scores($fighterStats){
// Returns the points for each fighter

	foreach($fighterStats as $fighterID => $fighter){
		$score = 0;
		$score += $fighter['pointsFor'];
		
		$score = round($score,1);
		
		$fighterStats[$fighterID]['score'] = $score;
		$fighterStats[$fighterID]['rosterID'] = $fighterID;
	}
	
	return $fighterStats;
	
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

function _Sandstorm_display($entry, $class = null){
// Displays an a fighter's score for tournaments using 'Sandstorm 2017' scoring
// A request to generate a header may be passed instead of an exchange
	?>
	
<!-- Headers -->
	<?php if($entry == 'headers'): ?>
		<tr>
			<th>Rank</th>
			<th>Name</th>
			<th>Control Wins</th>
			<th>Wins</th>
			<th>Afterblow Wins</th>
			<th>Doubles</th>
			<th>Score</th>
		</tr>
		<?php return; ?>
	<?php endif ?>
	
	<?php
	$wins = (3* $entry['wins'])-(2*$entry['afterblowsAgainst']);
	$wins -= ($entry['score'] + $entry['doubles']);
	$wins = round($wins,1); 
	$control = $entry['wins'] - $entry['afterblowsAgainst'] - $wins;
	
	if($control < 0.2 AND $control > -0.2){
		$control = 0;
	}
	?>
	
<!-- Data -->
	<tr class='text-center <?=$class?>' >
		<td><?=$entry['rank']?></td>
		<td class='text-left'>
			<?=getFighterName($entry['rosterID']);?>
		</td>
		<td><?=$control?></td>
		<td><?=$wins?></td>
		<td><?=$entry['afterblowsAgainst']?></td>
		<td><?=$entry['doubles']?></td>
		<td><?=$entry['score']?></td>
	</tr>
	
<?php }

/******************************************************************************/

function _Sandstorm_scores($fighterStats){
// Calculate scores using the 'Sandstorm 2017' algorithm	

	foreach((array)$fighterStats as $fighterID => $fighter){
		$score = 0;
		$score += $fighter['pointsFor'];
		$score -= $fighter['doubles'];

		$score = round($score,1);
		
		$fighterStats[$fighterID]['score'] = $score;
		$fighterStats[$fighterID]['rosterID'] = $fighterID;
	}

	return $fighterStats;

}

/******************************************************************************/

function _ScoreHitRatio_display($entry, $class = null){
// Displays an a fighter's score for tournaments using 'Hit Ratio' scoring
// A request to generate a header may be passed instead of an exchange
?>
	
<!-- Headers -->
	<?php if($entry == 'headers'): ?>
		<tr>
			<th>Rank</th>
			<th>Name</th>
			<th>Points For</th>
			<th>Times Hit</th>
			<th>Score</th>
		</tr>
		<?php return; ?>
	<?php endif ?>
	
	<?php
		$timesHit = $entry['hitsAgainst'] + $entry['afterblowsAgainst'];
		$timesHit += ($entry['doubles'])*($entry['doubles']+1)/2;
	?>
	
<!-- Data -->	
	<tr class='<?=$class?>'>
		<td class='text-center'><?=$entry['rank']?></td>
		<td class='text-left'>
			<?=getFighterName($entry['rosterID']);?>
		</td>
		<td><?=$entry['pointsFor']?></td>
		<td><?=$timesHit?></td>
		<td><?=$entry['score']?></td>
	</tr>
	
<?php }

/******************************************************************************/

function _ScoreHitRatio_scores($fighterStats){
// Calculates scores for the 'Hit Ratio' algorithm
// Total points earned devided by number of times hit
	
	foreach((array)$fighterStats as $fighterID => $fighter){
		
		$doubles = ($fighter['doubles'])*($fighter['doubles']+1)/2;
		
		$totalTimesHit = $fighter['hitsAgainst'] + $fighter['afterblowsAgainst'] + $doubles;
		$pointsFor = $fighter['pointsFor'];
		
		if($totalTimesHit > 0 ){
			$score = $pointsFor/$totalTimesHit;
		} else {
			$score = 9001;
		}

		$score = round($score,1);
		
		$fighterStats[$fighterID]['score'] = $score;
		$fighterStats[$fighterID]['rosterID'] = $fighterID;
	}
	
	return $fighterStats;
}

/******************************************************************************/

function _ranking_PoolScoreWins($fighterStats){
	return sort_PoolWinnersFirst($fighterStats,1,'score',SORT_DESC,'wins',SORT_DESC,'doubles',SORT_ASC);
}

/******************************************************************************/

function _ranking_ByScore($fighterStats){
	return sort_Simple($fighterStats,'score',SORT_DESC,'wins',SORT_DESC,'doubles',SORT_ASC);
}

/******************************************************************************/

function _ranking_SeededPool($fighterStats){
	return sort_SeededPool($fighterStats,1,1,'wins',SORT_DESC,'score',SORT_DESC,'doubles',SORT_ASC);
}

/******************************************************************************/

function _ranking_WinsScore($fighterStats){
	return sort_Simple($fighterStats,'wins',SORT_DESC,'score',SORT_DESC,'doubles',SORT_ASC);
}

/******************************************************************************/

function pool_RankFighters($fighterStats,$tournamentID){
// Calls the appropriate funciton to rank fighters given the tournament
// ranking priority
	
	$funcName = getRankingFunctionName($tournamentID);
	if($funcName == null){return;}
	$funcName = "_ranking_".$funcName; 
	
	$fighterScores = call_user_func($funcName,$fighterStats);
	
	return $fighterScores;
}

/******************************************************************************/

function pool_ScoreFighters($fighterStats,$tournamentID, $groupSet = 1){
// Calls the appropriate funciton to score fighters given the tournament
// scoring algorithm
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	
	$fighterStats = pool_normalizeSizes($fighterStats, $tournamentID, $groupSet);
	
// Call appropriate scoring function
	$funcName = getScoringFunctionName($tournamentID);
	if($funcName == null){return;}
	$funcName = "_".$funcName."_scores";
	$fighterScores = call_user_func($funcName,$fighterStats, $groupSet);
	
	return $fighterScores;
	
}

/******************************************************************************/

function pool_displayResults(){
// Calls the appropriate funciton to display the fighters pool standings 
// given the tournament scoring algorithm

	$orderedList = getTournamentStandings($tournamentID, $_SESSION['groupSet']);
	$bracketInfo = getBracketInformation();
	$numToElims = $bracketInfo['winner']['numFighters'];
	
	$funcName = getDisplayFunctionName($tournamentID);
	if($funcName == null){return;}
	$funcName = "_".$funcName."_display"; 
	
	echo "<table>";
	
	call_user_func($funcName,'headers');
	
	foreach((array)$orderedList as $entry){
				
		$class = isSelected($entry['rank'], $numToElims, "last-to-elims");	
		call_user_func($funcName,$entry, $class);
	}
	
	echo"</table>";
	
}

/******************************************************************************/

function pool_generateNextPools(){
// Calls the appropriate funciton to generate the advancements to the next 
// pool set given the tournament scoring algorithm
			
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}

	// determine which algorithim to use
	
	$funcName = getAdvancementFunctionName($tournamentID);
	if($funcName == null){return;}
	$funcName = "_".$funcName."_advancements"; 
	
	call_user_func($funcName);

}

/******************************************************************************/

function pool_getWinners($stats, $number){
// Returns the winner of the pool
// May return the top $number placings	
	
	if($number == null){$number = 1;};

	$i = 0;
	if($stats!=null){
		foreach($stats as $rosterID => $data){
			$wins[$i] = $data['wins'];
			$score[$i] = $data['score'];
			$doubles[$i] = $data['doubles'];
			$sortStats[$i] = $data;
			$sortStats[$i++]['rosterID'] = $rosterID;
		}
	}

	array_multisort($wins, SORT_DESC, $score, SORT_DESC, $doubles, SORT_ASC, $sortStats);
	
	$i = 1;
	if($sortStats!=null)
	{
		foreach($sortStats as $data){
			$returnValues[$i++] = $data['rosterID'];
			if($i > $number){return $returnValues;}
		}
	}
}

/******************************************************************************/

function pool_normalizeSizes($fighterStats, $tournamentID, $groupSet = null){
// Normalizes the raw exchange data of all fighters to the
// value that the normalize pool size has been set to	
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	
	$numberOfMatches = getNormalization($tournamentID, $groupSet) - 1;

	foreach((array)$fighterStats as $rosterID => $fighterData){

		$matchesFought = $fighterData['matches'];
		$correction = $numberOfMatches/$matchesFought;
		if($correction == 1){ continue; }

		foreach($fighterData as $dataIndex => $data){
			$fighterStats[$rosterID][$dataIndex] = round($data * $correction,1);
			
		}
	}

	return $fighterStats;
	
}

/******************************************************************************/

function pool_normalizeSizes_static($fighterStats, $poolSize){
// Normalizes the raw exchange data of all fighters to the
// number of matches that the given pool size has	
	
	$numberOfMatches = $poolSize - 1;

	foreach((array)$fighterStats as $rosterID => $fighterData){

		$matchesFought = $fighterData['matches'];
		$correction = $numberOfMatches/$matchesFought;
		if($correction == 1){ continue; }

		foreach($fighterData as $dataIndex => $data){
			$fighterStats[$rosterID][$dataIndex] = round($data * $correction,1);
			
		}
	}

	return $fighterStats;
	
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

function sort_PoolWinnersFirst($fighterStats,$numWinners,$param1,$order1,$param2,$order2,$param3,$order3){
// Sorts the pool results giving precedence to the winners of each pool
// Precedence can be given to the top $numWinners fighters of a pool
// Parameters define which sort criteria should be used, order is ASC or DESC
	
	$poolRosters = getPoolRosters(null);

	foreach((array)$poolRosters as $groupID => $groupFighters){
		foreach($groupFighters as $groupFighter){
			$rosterID = $groupFighter['rosterID'];
			$fighterStatsByPool[$groupID][$rosterID]['wins'] = $fighterStats[$rosterID]['wins'];
			$fighterStatsByPool[$groupID][$rosterID]['score'] = $fighterStats[$rosterID]['score'];
			$fighterStatsByPool[$groupID][$rosterID]['doubles'] = $fighterStats[$rosterID]['doubles'];
		}
		$poolWinners = pool_getWinners($fighterStatsByPool[$groupID],$numWinners);
		
		foreach($poolWinners as $rosterID){
			$isGroupWinner[$rosterID] = true;
		}
	}

	// Creates two separate arrays, one of pool winners, the other of everybody else

	foreach((array)$fighterStats AS $rosterID => $fighter){
		if($isGroupWinner[$rosterID] == true){
			$winnersGroup[$rosterID] = $fighter;
		} else {
			$notWinnersGroup[$rosterID] = $fighter;
		}
	}

	
	// Sort the two groups
	$winnersGroup = sort_Simple($winnersGroup,$param1,$order1,$param2,$order2,$param3,$order3);
	$notWinnersGroup = sort_Simple($notWinnersGroup,$param1, $order1,$param2,$order2,$param3,$order3);

	// Recombine the two groups
	unset($fighterStats);
	$i=0;

	foreach((array)$winnersGroup as $fighterData){
		$fighterStats[$i] = $fighterData;
		$fighterStats[$i]['rank'] = $i+1;
		$i++;
	}


	foreach((array)$notWinnersGroup as $fighterData){
		$fighterStats[$i] = $fighterData;
		$fighterStats[$i]['rank'] = $i+1;
		$i++;
	}

	
	return $fighterStats;
}

/******************************************************************************/

function sort_SeededPool($fighterStats,$numSeededPools,$numWinners,$param1,$order1,$param2,$order2,$param3,$order3){
// Sorts the pool results giving precedence all fighters from seeded pools,
// then to the to the winners of the non seeded pools
// $numSeededPools is the number of pools that have all fighters advance
// Precedence can be given to the top $numWinners fighters of a pool
// Parameters define which sort criteria should be used, order is ASC or DESC

	$poolRosters = getPoolRosters();

	foreach((array)$poolRosters as $groupID => $groupFighters){
		$groupNumber = getGroupNumber($groupID);

		foreach($groupFighters as $groupFighter){
			$rosterID = $groupFighter['rosterID'];
			$fighterStatsByPool[$groupID][$rosterID]['wins'] = $fighterStats[$rosterID]['wins'];
			$fighterStatsByPool[$groupID][$rosterID]['score'] = $fighterStats[$rosterID]['score'];
			$fighterStatsByPool[$groupID][$rosterID]['doubles'] = $fighterStats[$rosterID]['doubles'];
		}
		
		if($groupNumber > $numSeededPools){		
			$normalPoolWinners = pool_getWinners($fighterStatsByPool[$groupID],$numWinners);
			
			foreach($normalPoolWinners as $rosterID){
				$isGroupWinner[$rosterID] = true;
			}
		} else {
			$seededPoolIDs[] = $groupID;
		}
	}

	// Creates three separate arrays, one of seeded fighters
	// one of pool winners, the other of everybody else

		foreach((array)$seededPoolIDs as $groupID){
		foreach((array)$fighterStatsByPool[$groupID] as $rosterID => $fighter){
			$seededGroup[$rosterID] = $fighterStats[$rosterID];
		}
	}

	foreach((array)$fighterStats AS $rosterID => $fighter){
		if($isGroupWinner[$rosterID] == true){
			$winnersGroup[$rosterID] = $fighter;
		} else if(!isset($seededGroup[$rosterID])){
			$notWinnersGroup[$rosterID] = $fighter;
		}
	}

	// Sort the three groups
	$seededGroup = sort_Simple($seededGroup,$param1,$order1,$param2,$order2,$param3,$order3);
	$winnersGroup = sort_Simple($winnersGroup,$param1,$order1,$param2,$order2,$param3,$order3);
	$notWinnersGroup = sort_Simple($notWinnersGroup,$param1, $order1,$param2,$order2,$param3,$order3);

	// Recombine the three groups
	unset($fighterStats);
	$i=0;

	foreach((array)$seededGroup as $fighterData){
		$fighterStats[$i] = $fighterData;
		$fighterStats[$i]['rank'] = $i+1;
		$i++;
	}

	foreach((array)$winnersGroup as $fighterData){
		$fighterStats[$i] = $fighterData;
		$fighterStats[$i]['rank'] = $i+1;
		$i++;
	}


	foreach((array)$notWinnersGroup as $fighterData){
		$fighterStats[$i] = $fighterData;
		$fighterStats[$i]['rank'] = $i+1;
		$i++;
	}

	
	return $fighterStats;
}

/******************************************************************************/

function sort_Simple($fighterStats,$param1,$order1,$param2 = null,$order2 = null,$param3 = null,$order3 = null){
// Sorts fighters only based on the parameters supplied

	if($fighterStats==null){ return; }
	
	foreach($fighterStats as $key => $entry){
		$sort1[$key] = $entry[$param1];
		$sort2[$key] = $entry[$param2];
		$sort3[$key] = $entry[$param3];
	}
	
	if(isset($param1) && isset($order1)){
		if(isset($param2) && isset($order2)){
			if(isset($param3) && isset($order3)){
				array_multisort($sort1, $order1, $sort2, $order2, $sort3, $order3, $fighterStats);
			} else {
				array_multisort($sort1, $order1, $sort2, $order2, $fighterStats);
			}
		} else {
			array_multisort($sort1, $order1, $fighterStats);
		}
	}
		
	foreach($fighterStats as $key => $entry){
		$fighterStats[$key]['rank'] = ++$i;
	}

	return $fighterStats;
}

/******************************************************************************

function __SwissPairs(){ 
// Function to attempt to produce swiss pairs style advancements for a pool set
// Depreciated, behavior is not known at this time.
// Kept for reference if this type of algorithm is required in the future

	// Pool Advanaments
	
	$maxPoolSize = maxPoolSize();
	$poolsInTier = $_POST['poolsInTier'];
	if($poolsInTier == 0 || $poolsInTier == null){
		$poolsInTier = 9999;
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
		
		
		foreach($fightersInTier as $rosterIDtoAdd){
			
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
			
			if($eligiblePools_Size[$eligiblePools_Combined[0]] % 2 == 0){
				$defaultPosition = 0;
			} else {
				$defaultPosition = max(array_keys($eligiblePools_Combined));
			}
			
			$poolNum = $eligiblePools_Combined[$defaultPosition];
			$poolRefights[$poolNum] += $possiblePoolRefights[$poolNum];
			$poolPosition = $eligiblePools_Size[$poolNum]+1;
		
			$_SESSION['poolSeeds'][$poolNum][$poolPosition] = $rosterIDtoAdd;
		}
			
	}
	
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
