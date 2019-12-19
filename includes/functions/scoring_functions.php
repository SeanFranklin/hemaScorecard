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

	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}

	$matchID = $matchInfo['matchID'];
	if($matchID == null){return;}
	
	$sql = "SELECT exchangeID
			FROM eventExchanges
			WHERE matchID = {$matchID}";
	$res = mysqlQuery($sql, SINGLE);
	
	$basePointValue = getBasePointValue(null, $_SESSION['groupSet']);

	if($res == null){
		insertLastExchange($matchInfo, null, 'scored', 'null', $basePointValue, 0);
		if($numToAdd == 0){
			updateMatch($matchInfo);
			if(isLastPiece($_SESSION['tournamentID'])){
				$_SESSION['askForFinalization'] = true;
			}
		}
	}
	
	for($i=1;$i<=$numToAdd;$i++){
		insertLastExchange($matchInfo, null, 'pending', 'null', 0, 0);	
	}
	
}

/******************************************************************************/

function _DeductionBased_displayExchange($exchange = null,$exchangeNum = null){
// Displays an exchange for pieces using 'Deduction Based' scoring
// A request to generate a header may be passed instead of an exchange

	$basePoints = getBasePointValue(null, $_SESSION['groupSet']);
	?>
	
<!-- Table Headers -->
	<?php if($exchangeNum == 'header'): ?>
	
		<tr>
		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
			<th></th>
		<?php endif ?>
			<th></th>
			<th>Deduction</th>
		</tr>
		
		<?php return; ?>
	<?php endif ?>
	
	<?php
	
	// Code Block /////////////////////////////
	if($exchange == null){
		// Not a valid exchange
		return;
	}


	$exchangeNum--;
	$exchangeID = $exchange['exchangeID'];
	
	if($exchange['exchangeType'] == 'scored'){
		$placeholder = '';
		$against = $exchange['scoreDeduction'];
		$for = $exchange['scoreValue'];
	} else {
		if(ALLOW['EVENT_SCOREKEEP'] == false){
			return;
		}
		$placeholder = "placeholder='Enter Deduction'";
		$against = '';
		$for = '';
	}
	?>
	
	<tr>
		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
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
			<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
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

	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}

	foreach((array)$_POST['scores'] as $exchangeID => $data){
	
		// Unset values are treated as zero.
		$exchangesToUpdate[$exchangeID]['scoreValue'] = @(float)$data['value'];
		$exchangesToUpdate[$exchangeID]['scoreDeduction'] = @(float)$data['deduction'];
	}

	return $exchangesToUpdate;
	
}

/******************************************************************************/

function _JNCR_addExchanges($numToAdd, $matchInfo){
// Adds cuts to a piece using the 'Pure Score' format

	$groupSet = getGroupSetOfMatch($matchInfo['matchID']);
	$basePointValue = getBasePointValue($matchInfo['tournamentID'],$groupSet);

	for($i=1;$i<=$numToAdd;$i++){
		insertLastExchange($matchInfo, null, 'pending', 'null', $basePointValue, 0);	
	}
	
}

/******************************************************************************/

function _JNCR_displayExchange($exchange,$exchangeNum){
// Displays an exchange for pieces using 'Pure Score' scoring
// A request to generate a header may be passed instead of an exchange

	$basePointValue = getBasePointValue(null, $_SESSION['groupSet']);
	?>
	
<!--  Table Headers -->
	<?php if($exchangeNum == 'header'): ?>
		<tr>

			<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
				<th style='padding: 1px; width:6%;'>
					&nbsp;
				</th>
				<th class='text-center' style='padding: 1px; width:22%'>
					Cut
				</th>
				<th class='text-center' style='padding: 1px; width:22%'>
					Upper
				</th>
				<th class='text-center' style='padding: 1px; width:22%'>
					Lower
				</th>
				<th class='text-center' style='padding: 1px; width:17%'>
					Task
				</th>
				<th class='text-center' style='padding: 1px;'>
					Points
				</th>

			<?php else: ?>
				<th>
					<strong>Cut Num</strong>
				</th>
				<th class='text-center' style='padding: 1px;'>
					Score
				</th>
				<th class='text-center' style='padding: 1px;'>
					Deduction
				</th>
				<th class='text-center' style='padding: 1px;'>
					Points
				</th>
			<?php endif ?>
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
		$cutPoints = '';
		$upperPoints = '';
		$lowerPoints = '';
		
	} else {
		if(ALLOW['EVENT_SCOREKEEP'] == false){ return;}
		$for = $exchange['scoreValue'];
		$total = 'N/A';
		$against = 'N/A';
		$zeroVal = '';
		$placeholder = "placeholder='0'";
		$cutPoints = 0;
		$upperPoints = 0;
		$lowerPoints = 0;
	}
	?>
	
<!-- Table Row -->
	<tr class='text-center'>

		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
			<td>
				<strong><?=$exchangeNum?></strong>
				<input type='checkbox' class='no-bottom' name='exchangesToDelete[<?=$exchangeID?>]'>
				<input type='hidden' value='<?=$basePointValue?>' name='scores[<?=$exchangeID?>][scoreValue]'>
				<input type='hidden' value='<?=$against?>' name='scores[<?=$exchangeID?>][scoreDeduction]'>
			</td>

			<td style='padding: 2px;'>
				<select class='no-bottom' name='scores[<?=$exchangeID?>][cutPoints]'>
					<?php if($cutPoints==''):?>
						<option value=''></option>
					<?php endif ?>
					<option value='8'>8</option>
					<option value='6'>6</option>
					<option value='4'>4</option>
					<option value='2'>2</option>
					<option <?=optionValue(0,$cutPoints)?> >0</option>
					<option value='-5'>-5</option>
					<option value='-10'>-10</option>
					<option value='-30'>-30</option>
				</select>
			</td>
			<td style='padding: 2px;'>
				<select class='no-bottom' name='scores[<?=$exchangeID?>][upperPoints]'>
					<?php if($upperPoints==''):?>
						<option value=''></option>
					<?php endif ?>
					<option value='4'>4</option>
					<option value='3'>3</option>
					<option value='2'>2</option>
					<option value='1'>1</option>
					<option <?=optionValue(0,$upperPoints)?> >0</option>
					<option value='-10'>-10</option>
					<option value='-20'>-20</option>
				</select>
			</td>
			<td style='padding: 2px;'>
				<select class='no-bottom' name='scores[<?=$exchangeID?>][lowerPoints]'>
					<?php if($lowerPoints==''):?>
						<option value=''></option>
					<?php endif ?>
					<option value='4'>4</option>
					<option value='3'>3</option>
					<option value='2'>2</option>
					<option value='1'>1</option>
					<option <?=optionValue(0,$lowerPoints)?> >0</option>
					<option value='-10'>-10</option>
					<option value='-20'>-20</option>
				</select>
			</td>
			<td style='padding: 2px;'>
				<select class='no-bottom' name='scores[<?=$exchangeID?>][taskPoints]'>
					<option value='1'>Full</option>
					<option value='0.5'>1/2</option>
					<option value='0'>Null</option>
				</select'>
			</td>
			<td>
				<strong>
					<?=$total?>
				</strong>
			</td>

		<?php else: ?>
			<td>
				<strong><?=$exchangeNum?></strong>
			</td>

			<td>
				<?=$for;?>
			</td>

			<td>
				<?php if($against > 0): ?> 
					- <!-- Points against are displayed as negative-->
				<?php endif ?>
				<?=$against;?>
			</td>

			<td>
				<strong>
					<?=$total?>
				</strong>
			</td>
		<?php endif ?>

	</tr>
<?php }

/******************************************************************************/

function _JNCR_updateExchanges(){
// Calculates the score of each cut for pieces using 'Pure Score'	
	
	$basePointValue = getBasePointValue(null, $_SESSION['groupSet']);
	$exchangesToUpdate = [];

	foreach((array)$_POST['scores'] as $exchangeID => $data){

		if($data['cutPoints'] == '' && $data['upperPoints'] == '' && $data['lowerPoints'] == ''){
			continue;
		}

		// Convert all the input strings into numbers
		$cutPoints = (float)$data['cutPoints'];
		$upperPoints = (float)$data['upperPoints'];
		$lowerPoints = (float)$data['lowerPoints'];
		$taskPoints = (float)$data['taskPoints'];

		// Check if a penalty was assesed
		$penalty = 0;
		if($cutPoints < $penalty){
			$penalty = $cutPoints;
		}
		if($upperPoints < $penalty){
			$penalty = $upperPoints;
		}
		if($lowerPoints < $penalty){
			$penalty = $lowerPoints;
		}

		// Calculate the score and deductions
		if($penalty < 0){
			$score = $penalty;
			$deduction = $basePointValue - $score;
		}elseif($cutPoints == 0){
			$score = 0;
			$deduction = $basePointValue;
		} elseif(($upperPoints + $lowerPoints) == 0){
			$score = 0;
			$deduction = $basePointValue;
		} else {
			$score = (float)($cutPoints + $upperPoints + $lowerPoints);
			$score *= $taskPoints;
			$deduction = $basePointValue - $score;
		}

		// Record the Exchange
		$exchangesToUpdate[$exchangeID]['scoreValue'] = $basePointValue;
		if($score == $basePointValue){
			$exchangesToUpdate[$exchangeID]['scoreValue'] += 4;
		}

		$exchangesToUpdate[$exchangeID]['scoreDeduction'] = $deduction;

	}

	return $exchangesToUpdate;
	
}

/******************************************************************************/

function _PureScore_addExchanges($numToAdd, $matchInfo){
// Adds cuts to a piece using the 'Pure Score' format

	$groupSet = getGroupSetOfMatch($matchInfo['matchID']);
	$basePointValue = getBasePointValue($matchInfo['tournamentID'],$groupSet);

	for($i=1;$i<=$numToAdd;$i++){
		insertLastExchange($matchInfo, null, 'pending', 'null', $basePointValue, 0);	
	}
	
}

/******************************************************************************/

function _PureScore_displayExchange($exchange,$exchangeNum){
// Displays an exchange for pieces using 'Pure Score' scoring
// A request to generate a header may be passed instead of an exchange

	$basePointValue = getBasePointValue(null, $_SESSION['groupSet']);
	?>
	
<!--  Table Headers -->
	<?php if($exchangeNum == 'header'): ?>
		<tr>
			<th>
				<?php if(ALLOW['EVENT_SCOREKEEP'] == false): ?>
					<strong>Cut Num</strong>
				<?php endif ?>
			</th>
		
		
			<th class='text-center' style='padding: 1px;'>
				Score
			</th>
			<th class='text-center' style='padding: 1px;'>
				Deduction
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
		if(ALLOW['EVENT_SCOREKEEP'] == false){ return;}
		$for = $exchange['scoreValue'];
		$total = 'N/A';
		$against = 'N/A';
		$zeroVal = '';
		$placeholder = "placeholder='0'";
	}
	?>
	
<!-- Table Row -->
	<tr class='text-center'>
	
		<td>
		<strong><?=$exchangeNum?></strong>
		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
			<input type='checkbox' class='no-bottom' name='exchangesToDelete[<?=$exchangeID?>]'>
		<?php endif ?>
		</td>
	

		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
			<td style='padding: 1px;'>
				<input type='number' step='0.1' class='no-bottom' <?=$placeholder?>
					value='<?=$for?>' name='scores[<?=$exchangeID?>][scoreValue]' >
			</td>
			<td style='padding: 1px;'>
				<input type='number' step='0.1' class='no-bottom' <?=$placeholder?>
					value='<?=$against?>' name='scores[<?=$exchangeID?>][scoreDeduction]''>
			</td>
		<?php else: ?>
			<td>
				<?php if($for < 0): ?> 
					- <!-- Points against are displayed as negative-->
				<?php endif ?>
				<?=$for;?>
			</td>
			<td>
				<?php if($against > 0): ?> 
					- <!-- Points against are displayed as negative-->
				<?php endif ?>
				<?=$against;?>
			</td>

		<?php endif ?>

		<td>
			<?=$total?>
		</td>
	</tr>
<?php }

/******************************************************************************/

function _PureScore_updateExchanges(){
// Calculates the score of each cut for pieces using 'Pure Score'	
	
	foreach((array)$_POST['scores'] as $exchangeID => $data){
		
		$exchangesToUpdate[$exchangeID]['scoreValue'] = (float)$data['scoreValue'];
		$exchangesToUpdate[$exchangeID]['scoreDeduction'] = (float)$data['scoreDeduction'];

	}

	return $exchangesToUpdate;
	
}

/******************************************************************************/

function _RSScutting_addExchanges($numToAdd, $matchInfo){
// Adds cuts to a piece using the 'RSS Cutting' format

	$groupSet = getGroupSetOfMatch($matchInfo['matchID']);
	$basePointValue = getBasePointValue($matchInfo['tournamentID'],$groupSet);

	for($i=1;$i<=$numToAdd;$i++){
		insertLastExchange($matchInfo, null, 'pending', 'null', $basePointValue, 0);	
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
				<?php if(ALLOW['EVENT_SCOREKEEP'] == false): ?>
					<strong>Cut Num</strong>
				<?php endif ?>
			</th>
		
			<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
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
		if(ALLOW['EVENT_SCOREKEEP'] == false){ return;}
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
		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
			<input type='checkbox' class='no-bottom' name='exchangesToDelete[<?=$exchangeID?>]'>
			<input type='number' class='hidden' value=<?=$basePointValue?> name=scores[<?=$exchangeID?>][for]>
		<?php endif ?>
		</td>
	

		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
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
			<strong>
				<?=$total?>
			</strong>
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
			$a = (float)$data['form'];
			$b = (float)$data['cut'];
			
			if($a !== '' || $b !== ''){
				$a = (float)$a;
				$b = (float)$b;
			} else {
				$a = (float)$data['against'];
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

function _SwissScore_calculateScore($tournamentID, $groupSet = 1){
	
	$sql = "SELECT fighter1ID, fighter2ID, fighter1Score, fighter2Score
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'pool'
			AND groupSet = {$groupSet}
			AND ignoreMatch = 0
			AND matchComplete = 1";

	$matchResults = mysqlQuery($sql, ASSOC);

	foreach($matchResults as $match){
		if((int)$match['fighter1Score'] > (int)$match['fighter2Score']){
			$winnerID = $match['fighter1ID'];
			$winnerScore = $match['fighter1Score'];
			$loserID = $match['fighter2ID'];
			$loserScore = $match['fighter2Score'];
		} elseif((int)$match['fighter1Score'] < (int)$match['fighter2Score']) {
			$winnerID = $match['fighter2ID'];
			$winnerScore = $match['fighter2Score'];
			$loserID = $match['fighter1ID'];
			$loserScore = $match['fighter1Score'];
		} else {
			// These values may not exist, in which case they should be evaluated as zero.
			@$scores[$match['fighter1ID']] += 0;
			@$scores[$match['fighter2ID']] += 0;
			@$numMatches[$match['fighter1ID']]++;
			@$numMatches[$match['fighter2ID']]++;
			continue;
		}

		// Calculate the winner and loser scores
		$winnerCalc = ($winnerScore - $loserScore) / $winnerScore;
		$loserCalc = 0;

		// These values may not exist, in which case they should be evaluated as zero.
		@$scores[$winnerID] += $winnerCalc; 
		@$scores[$loserID] += $loserCalc;
		@$numMatches[$winnerID]++;
		@$numMatches[$loserID]++;
	}

	// Calculate the normalized size
	$normalizedMatches = getNormalization($tournamentID, $groupSet) - 1;

	// Write to DB

	foreach($scores as $rosterID => $score){

		if($normalizedMatches != $numMatches[$rosterID]){
			$normalizedScore = $score * ($normalizedMatches / $numMatches[$rosterID]);
		} else {
			$normalizedScore = $score;
		}

		$sql = "UPDATE eventStandings
				SET score = {$normalizedScore}
				WHERE tournamentID = {$tournamentID}
				AND groupType = 'pool'
				AND groupSet = {$groupSet}
				AND rosterID = {$rosterID}";
		mysqlQuery($sql, SEND);
	}	

}

/******************************************************************************/

function _FobDagger_calculateScore($tournamentID, $groupSet = 1){

	$tournamentID = (int)$tournamentID;
	$groupSet = (int)$groupSet;

	// Calculate the normalized size
	$normalizedMatches = getNormalization($tournamentID, $groupSet) - 1;

	$sql = "SELECT standingID, rosterID
			FROM eventStandings
			WHERE tournamentID = {$tournamentID}
			AND groupSet = {$groupSet}";
	$standingsToScore = mysqlQuery($sql, ASSOC);

	if($standingsToScore == null){
		return;
	}

	foreach($standingsToScore as $standing){

		$rosterID = $standing['rosterID'];
		$score = 0;

		$sql = "SELECT COUNT(*) AS numMatches
				FROM eventMatches
				INNER JOIN eventGroups USING(groupID)
				WHERE (fighter1ID= {$rosterID} OR fighter2ID = {$rosterID})
				AND tournamentID = {$tournamentID}
				AND groupType = 'pool'
				AND groupSet = {$groupSet}
				AND ignoreMatch = 0
				AND matchComplete = 1";
		$numMatches = (int)mysqlQuery($sql, SINGLE, 'numMatches');

		$controlPrefix = (int)ATTACK_CONTROL_DB;
		$sql = "SELECT COUNT(*) AS numControlPoints
				FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				WHERE scoringID = {$rosterID}
				AND tournamentID = {$tournamentID}
				AND refPrefix = {$controlPrefix}
				AND groupType = 'pool'
				AND groupSet = {$groupSet}
				AND ignoreMatch = 0
				AND matchComplete = 1";
		$score = (int)mysqlQuery($sql, SINGLE, 'numControlPoints');
		


		if($numMatches != 0){
			$score *= $normalizedMatches/$numMatches;
		} else {
			$score = 0;
		}
		
		$sql = "UPDATE eventStandings
				SET score = {$score}
				WHERE standingID = {$standing['standingID']}";
		mysqlQuery($sql, SEND);
	}
}

/******************************************************************************/

function _Wessex_calculateScore($tournamentID, $groupSet = 1){

	$tournamentID = (int)$tournamentID;
	$groupSet = (int)$groupSet;

	// Calculate the normalized size
	$normalizedMatches = getNormalization($tournamentID, $groupSet) - 1;

	$sql = "SELECT standingID, rosterID
			FROM eventStandings
			WHERE tournamentID = {$tournamentID}
			AND groupSet = {$groupSet}";
	$standingsToScore = mysqlQuery($sql, ASSOC);

	if($standingsToScore == null){
		return;
	}

	foreach($standingsToScore as $standing){

		$rosterID = $standing['rosterID'];
		$score = 0;

		$sql = "SELECT matchID, winnerID
				FROM eventMatches
				INNER JOIN eventGroups USING(groupID)
				WHERE (fighter1ID= {$rosterID} OR fighter2ID = {$rosterID})
				AND tournamentID = {$tournamentID}
				AND groupType = 'pool'
				AND groupSet = {$groupSet}
				AND ignoreMatch = 0
				AND matchComplete = 1";
		$matches = mysqlQuery($sql, ASSOC);

		$numMatches = 0;
		foreach($matches as $match){
			$matchID = $match['matchID'];

			$numMatches++;

			if($match['winnerID'] == $rosterID){
				$score += 3;
			} elseif($match['winnerID'] == null){
				$score += 1;
			} else {
				// Lost the match, no score.
			}

			$sql = "SELECT COUNT(*) AS numDoubles
					FROM eventExchanges
					WHERE matchID = {$matchID}
					AND exchangeType = 'double'";
			$numDoubles = (int)mysqlQuery($sql, SINGLE, 'numDoubles');

			$score -= floor($numDoubles/2); // -1 for every second double.
		}

		if($numMatches != 0){
			$score *= $normalizedMatches/$numMatches;
		} else {
			$score = 0;
		}
		
		$sql = "UPDATE eventStandings
				SET score = {$score}
				WHERE standingID = {$standing['standingID']}";
		mysqlQuery($sql, SEND);
	}
}

/******************************************************************************/

function _StandingPoints_calculateScore($tournamentID, $groupSet = 1){
	
	$tournamentComponents = getTournamentComponents($tournamentID,true);
	$tournamentRoster = getTournamentRoster($tournamentID);
	$basePointValue = getBasePointValue($tournamentID, null);

	$tString = '';
	foreach($tournamentComponents as $component){
		$cTournamentID = (int)$component['cTournamentID'];
		if($tString != ''){
			$tString .= ', ';
		}
		$tString .= "$cTournamentID";
	}

	$fString = '';
	foreach($tournamentRoster as $fighter){
		$rosterID = $fighter['rosterID'];
		$sql = "SELECT placing, highBound, lowBound
				FROM eventPlacings
				WHERE rosterID = {$rosterID}
				AND tournamentID IN ({$tString})";
		$tournamentPlacings = mysqlQuery($sql, ASSOC);

		$score = 0;
		foreach($tournamentPlacings as $placingData){
			$points = $basePointValue - $placingData['placing'] + 1;
			if($points > 0){
				$score += $points;
			}
		}

		$sql = "SELECT standingID
				FROM eventStandings
				WHERE tournamentID = {$tournamentID}
				AND rosterID = {$rosterID}";
		$standingID = mysqlQuery($sql, SINGLE, 'standingID');

		if($standingID == null){
			$sql = "INSERT INTO eventStandings
					(tournamentID, rosterID, groupType, score)
					VALUES
					({$tournamentID}, {$rosterID}, 'composite', {$score})";
			mysqlQuery($sql, SEND);
		} else {
			$sql = "UPDATE eventStandings
					SET score = {$score}
					WHERE standingID = {$standingID}";
			mysqlQuery($sql, SEND);
		}

		if($fString != ''){
			$fString .= ", ";
		}
		$fString .= "{$rosterID}";
	}

	// Delete any old standings hanging around from fighters without ranks
	$sql = "DELETE FROM eventStandings
			WHERE tournamentID = {$tournamentID}
			AND rosterID NOT IN ({$fString})";
	mysqlQuery($sql, SEND);
	

}

/******************************************************************************/

function _LpMeta_calculateScore($tournamentID, $groupSet = 1){
	
	$tournamentComponents = getTournamentComponents($tournamentID,true);
	$tournamentRoster = getTournamentRoster($tournamentID);
	$basePointValue = getBasePointValue($tournamentID, null);

	$tString = '';
	foreach($tournamentComponents as $component){
		$cTournamentID = (int)$component['cTournamentID'];
		if($tString != ''){
			$tString .= ', ';
		}
		$tString .= "$cTournamentID";
		$numEntries[$cTournamentID] = getNumTournamentEntries($cTournamentID);
	}

	if($tString == ''){
		$tString = 'NULL';
	}

	$fString = '';
	foreach($tournamentRoster as $fighter){

		$rosterID = $fighter['rosterID'];
		$sql = "SELECT placing, highBound, lowBound, tournamentID
				FROM eventPlacings
				WHERE rosterID = {$rosterID}
				AND tournamentID IN ({$tString})";
		$tournamentPlacings = mysqlQuery($sql, ASSOC);

		$score = 0;
		$pointsForFighter = [];

		foreach($tournamentPlacings as $placingData){
			$num = $numEntries[$placingData['tournamentID']];
			$points = ($num - ($placingData['placing'] - 1)) / $num;
			$points *= $basePointValue;

			if($points < 0){
				$points = 0;
			}
		
			$score += $points;
			$pointsForFighter[] = $points;
		}

		// Subtract the standard deviation of scores from the average tournament score
		$ptsFor = round($score,0);
		if(count($pointsForFighter) != 0){
			$ptsAgainst = round(standardDeviation($pointsForFighter),0);
		} else {
			$ptsAgainst = 0;
		}
		$score = $ptsFor - $ptsAgainst;


		$sql = "SELECT standingID
				FROM eventStandings
				WHERE tournamentID = {$tournamentID}
				AND rosterID = {$rosterID}";
		$standingID = mysqlQuery($sql, SINGLE, 'standingID');

		if($standingID == null){
			$sql = "INSERT INTO eventStandings
					(tournamentID, rosterID, groupType, score, pointsFor, pointsAgainst)
					VALUES
					({$tournamentID}, {$rosterID}, 'composite', {$score}, {$ptsFor}, {$ptsAgainst})";
			mysqlQuery($sql, SEND);
		} else {
			$sql = "UPDATE eventStandings
					SET score = {$score}, pointsFor = {$ptsFor}, pointsAgainst = {$ptsAgainst}
					WHERE standingID = {$standingID}";
			mysqlQuery($sql, SEND);
		}

		if($fString != ''){
			$fString .= ", ";
		}
		$fString .= "{$rosterID}";
	}

	if($fString == ''){
		$fString = 'NULL';
	}


	// Delete any old standings hanging around from fighters without ranks
	$sql = "DELETE FROM eventStandings
			WHERE tournamentID = {$tournamentID}
			AND rosterID NOT IN ({$fString})";
	mysqlQuery($sql, SEND);

}

/******************************************************************************/

function pool_ScoreFighters($tournamentID, $groupSet = 1){
// Calls the appropriate function to score fighters given the tournament
// scoring algorithm
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No tournamentID in pool_ScoreFighters()";
		return;
	}

	$formula = getScoreFormula($tournamentID);

	if(substr($formula,0,1) !== '#'){

		$sql = "UPDATE eventStandings
				SET score = ($formula)
				WHERE tournamentID = {$tournamentID}
				AND groupType = 'pool'
				AND groupSet = {$groupSet}";

		mysqlQuery($sql, SEND);
	} else {
		// If a function has been specified instead of an algorithm then it is 
		// prefixed with an '#';
		$funcName = "_".substr($formula,1)."_calculateScore";
		call_user_func($funcName,$tournamentID,$groupSet);
	}
	
}

/******************************************************************************/

function pool_DisplayResults($tournamentID, $groupSet = 1, $showTeams = false){
// Calls the appropriate function to display the fighters pool standings 
// given the tournament scoring algorithm

	$bracketInfo = getBracketInformation($tournamentID);
	$ignores = getIgnores($tournamentID);

	if(isset($bracketInfo[BRACKET_PRIMARY]['numFighters'])){
		$numToElims = (int)$bracketInfo[BRACKET_PRIMARY]['numFighters'];
	} else {
		$numToElims = 0;
	}
	
	$maxNumFields = 5;
	$showTeams = (int)((bool)$showTeams);
	$displayByPool = $_SESSION['displayByPool'];

	$sql = "SELECT poolWinnersFirst,displayTitle1, displayField1,
			displayTitle2, displayField2,
			displayTitle3, displayField3,
			displayTitle4, displayField4,
			displayTitle5, displayField5
			FROM eventTournaments
			INNER JOIN systemRankings USING(tournamentRankingID)
			WHERE tournamentID = {$tournamentID}";
	$displayMeta = mysqlQuery($sql, SINGLE);
	$numPoolWinners = $displayMeta['poolWinnersFirst'];
	unset($displayMeta['poolWinnersFirst']);

	// Check to see which fields are used
	$selectStr = '';
	for($i = 1; $i <= $maxNumFields; $i++){
		if($displayMeta['displayTitle'.$i] == '' || $displayMeta['displayField'.$i] == ''){
			$maxNumFields = $i - 1;
			break;
		}
		$tmpStr = $displayMeta['displayField'.$i];
		$selectStr .= ", {$tmpStr}";
	}

	
	$orderBy = "rank ASC";
	if($displayByPool == true){
		$orderBy = "groupNumber ASC, ".$orderBy;
		$numToElims = 0;
	}

	$sql1 = "SELECT rank, rosterID, groupID {$selectStr} ";
	$sql2 =	"FROM eventStandings
			INNER JOIN eventRoster USING(rosterID)
			LEFT JOIN eventGroups USING(groupID)
			WHERE eventStandings.tournamentID = {$tournamentID}
			AND eventStandings.groupSet = {$groupSet}
			AND isTeam = {$showTeams}
			ORDER BY {$orderBy}";
	$sql = $sql1.$sql2;
	$displayInfo = mysqlQuery($sql, ASSOC);

	$sql1 = "SELECT COUNT(DISTINCT(groupID)) AS numGroups ";
	$sql = $sql1.$sql2;
	$numGroups = mysqlQuery($sql, SINGLE, 'numGroups');
	$numPoolWinnerFirst = $numGroups * $numPoolWinners;

	foreach($displayInfo as $info){
		if($info['groupID'] == null){
			$displayByPool = false;
		}
	}
	

// Header row -----------------------------
	function tableHeader($displayMeta, $maxNumFields){
		echo "<tr>";
		echo "<th>Rank</th>";
		echo "<th>Name</th>";
		for($i = 1; $i <= $maxNumFields; $i++){
			$index = "displayTitle".$i;
			$name = $displayMeta[$index];

			echo "<th>{$name}</th>";	
		}
		echo "</tr>";
	}
// --------------------------------------------

	if($displayByPool == false){
		echo "<table>";
		tableHeader($displayMeta, $maxNumFields);
	}


	$stopAtSetText = false;
	if($numToElims != 0){
		$lastToElimSet = false;
	} else {
		$lastToElimSet = true;
	}

	$groupID = 0;
	foreach($displayInfo as $fighter){
		if(@$ignores[$fighter['rosterID']]['soloAtSet'] >= $groupSet
			|| @$ignores[$fighter['rosterID']]['ignoreAtSet'] >= $groupSet){
			continue;
		}

		if($displayByPool == true && $fighter['groupID'] != $groupID){
			$groupID = $fighter['groupID'];
			$groupName = getGroupName($groupID);

			echo "</table><table><tr><td class='text-center' colspan = 100%>";
			echo "<h5>{$groupName}</h5>";
			echo "</td></tr>";

			tableHeader($displayMeta, $maxNumFields);
			
		}

		$class = '';
		if(@$ignores[$fighter['rosterID']]['stopAtSet'] >= $groupSet){
			$class .= ' grey-text';
			$stopAtSetText = true;
			if($lastToElimSet == false){
				$numToElims = $numToElims + 1;
			}
		}

		if($fighter['rank'] <= $numPoolWinnerFirst){
			$class .= ' pool-winner';
		}

		if($fighter['rank'] == $numToElims && ($numToElims % 2) == 0){
			$class .= ' last-to-elims';
			$lastToElimSet = true;
		} elseif ($fighter['rank'] == ($numToElims + 1) && ($numToElims % 2) == 1){
			$class .= ' first-no-elims';
			$lastToElimSet = true;
		}


		if($showTeams){
			$name = getTeamName($fighter['rosterID']);
		} else {
			$name = getCombatantName($fighter['rosterID']);
		}

		echo "<tr class='text-center {$class}'>";
		echo "<td>".$fighter['rank']."</td>";
		echo "<td  class='text-left'>{$name}</td>";
		for($i = 1; $i <= $maxNumFields; $i++){
			$index = $displayMeta["displayField".$i];
			$value = round($fighter[$index],1) + 0;

			echo "<td>{$value}</td>";
		}

		echo "</tr>";
	}


	echo"</table>";

	pool_standingsExplanation($tournamentID, $stopAtSetText, $lastToElimSet);

}

/******************************************************************************/

function pool_standingsExplanation($tournamentID,$stopAtSetText = false, $bracketSize = false){

	$description = getRankingDescriptionByTournament($tournamentID);
	if($description['description'] == ''){
		$description['description'] = 'No description provided in Database.';
	} elseif((int)$description['basePointValue'] != 0) {
		$description['description'] .= "<BR><BR>Base Point Value: {$description['basePointValue']}";
	}

	$winnerText = '';
	if($description['poolWinnersFirst'] != 0){
		if($description['poolWinnersFirst'] == 1){
			$s = "Pool winners (names in italics) are ranked above all non pool winners.";
		} else {
			$s = "Top {$description['poolWinnersFirst']} fighters from each pool (names in italics) are ranked above the rest of the competitors.";
		}
		$winnerText = "<p><em>{$s}</em></p>";
	}

?>

	<i><a onclick="$('.standings-explanation').toggle()" class='standings-explanation'>
		How are the standings calculated?
	</a></i>

	<fieldset class='hidden standings-explanation fieldset '>
		<legend>
			<a onclick="$('.standings-explanation').toggle()">
				How are the standings calculated?
			</a>
		</legend>

		<?=$winnerText?>

		<?php if($bracketSize == true): ?>
			<p>The horizontal line represents the <u>size</u> of the bracket.<BR>
			The individuals advancing may be different depending on injuries, withdrawls, or tournament organizer whims.</p>
		<?php endif ?>

		<?php if($stopAtSetText == true): ?>
			<p class='grey-text'>
				<em>
					*names in grey have withdrawn and will not advance to the next stage.
				</em>
			</p>
		<?php endif ?>

	
		<pre><?=$description['description']?></pre>


	</fieldset>

<?php

}

/******************************************************************************/

function pool_GeneratePools($specifications){
// Calculate advancements to move onto the next pool set using the parameters specified.
// Parameters are the number of 

	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){return;}

	// This is a special mode
	if($specifications['seedMethod'] == 'polar'){
		pool_GeneratePolarPools($specifications);
		return;
	}

	$groupSet = $specifications['groupSet'];
	$lastGroupSet = $groupSet - 1;

	// determine which algorithim to use
	$maxPoolSize = maxPoolSize();
	$poolsInTier = $specifications['poolsInTier'];
	$numberOfPools = count(getPools($tournamentID, $groupSet));
	$numSpotsLeft = $maxPoolSize * $numberOfPools;

	if($specifications['avoidRefights'] == true){
		$useRefights = true;
	} else {
		$useRefights = false;
	}

	if($specifications['avoidSchoolFights'] == true){
		$useSchools = true;
	} else {
		$useSchools = false;
	}

	if($specifications['useSubGroups'] == true){
		$useSubGroups = true;
		$subGroupOrder = "subGroupNum DESC,";
	} else {
		$useSubGroups = false;
		$subGroupOrder = "";
	}

	$useRatings = false;

	switch($specifications['seedMethod']){
		case 'seedList':
			$sql = "SELECT rosterID, rating, schoolID, subGroupNum
					FROM eventTournamentRoster as eTR
					INNER JOIN eventRoster USING(rosterID)
					WHERE tournamentID = {$tournamentID}
					AND (	SELECT COUNT(*) 
							FROM eventIgnores eI 
							WHERE eI.rosterID = eTR.rosterID 
							AND eI.tournamentID = {$tournamentID} ) = 0
					ORDER BY {$subGroupOrder} rating DESC";
			$rankedList = mysqlQuery($sql, ASSOC);
			$useRatings = true;

			$sql = "SELECT MIN(rating) AS minRating, MAX(rating) AS maxRating, COUNT(*) AS numRated
					FROM eventTournamentRoster as eTR
					INNER JOIN eventRoster USING(rosterID)
					WHERE tournamentID = {$tournamentID}
					AND (	SELECT COUNT(*) 
							FROM eventIgnores eI 
							WHERE eI.rosterID = eTR.rosterID 
							AND eI.tournamentID = {$tournamentID} ) = 0
					AND rating != 0";
			$ratingData = mysqlQuery($sql, SINGLE);

			$ratingFix = false;
			if($ratingData != null && $ratingData['numRated'] != 0){

				$minRating = ($ratingData['maxRating'] - $ratingData['minRating']);
				$minRating /= $ratingData['numRated'];
				$minRating = $ratingData['minRating'] - $minRating;
				$minRating = round($minRating,0);

				if($minRating > 0) {
					foreach($rankedList as $index => $fighter){
						if($fighter['rating'] == 0){
							$rankedList[$index]['rating'] = $minRating;
							$ratingFix = true;
						}
					}

					if($ratingFix == true){
						setAlert(USER_ALERT,"<h5>You appear to have left some fighters unrated</h5>
							A value of <strong class='red-text'>{$minRating}</strong>
							has been applied to all unrated fighters.<BR><BR>
							(<em>You can not turn this off,
							having zero-rated fighters breaks things.</em>)");
					}
				}

			}
			

			break;

		case 'poolStanding':
			$sql = "SELECT rosterID, rank, schoolID, 0 AS subGroupNum
					FROM eventStandings as eS
					INNER JOIN eventRoster USING(rosterID)
					INNER JOIN eventTournamentRoster AS eTR USING(rosterID)
					WHERE eS.tournamentID = {$tournamentID}
					AND eTR.tournamentID = {$tournamentID}
					AND groupSet = {$lastGroupSet}
					AND (	SELECT COUNT(*) 
							FROM eventIgnores eI 
							WHERE eI.rosterID = eS.rosterID 
							AND eI.tournamentID = {$tournamentID}
							AND stopAtSet >= {$lastGroupSet}
						) = 0
					ORDER BY {$subGroupOrder} rank ASC";
			$rankedList = mysqlQuery($sql, ASSOC);
			$useRatings = true;

			$sql = "SELECT MAX(rank) AS maxRank, MAX(rank) AS maxRank
					FROM eventStandings as eS
					INNER JOIN eventRoster USING(rosterID)
					WHERE tournamentID = {$tournamentID}
					AND groupSet = {$lastGroupSet}
					AND (	SELECT COUNT(*) 
							FROM eventIgnores eI 
							WHERE eI.rosterID = eS.rosterID 
							AND eI.tournamentID = {$tournamentID}
							AND stopAtSet >= {$lastGroupSet}
						) = 0
					ORDER BY {$subGroupOrder} rank ASC";
			$maxRank = mysqlQuery($sql, SINGLE, 'maxRank');
			
			foreach($rankedList as $index => $fighter){
				$rankedList[$index]['rating'] = $maxRank - $fighter['rank'];
			}

			break;
		case 'random':
		default:
			$sql = "SELECT rosterID, 0 AS rating, schoolID, subGroupNum
					FROM eventTournamentRoster as eTR
					INNER JOIN eventRoster USING(rosterID)
					WHERE tournamentID = {$tournamentID}
					AND (	SELECT COUNT(*) 
							FROM eventIgnores eI 
							WHERE eI.rosterID = eTR.rosterID 
							AND eI.tournamentID = {$tournamentID} 
							AND stopAtSet >= {$lastGroupSet}
						) = 0
					ORDER BY {$subGroupOrder} RAND()";
			$rankedList = mysqlQuery($sql, ASSOC);
			break;
	}

// Sub Group Calculations
	$highestSubGroup = 0;

	if($useSubGroups == true){
		$numInSubGroup = [];

		foreach($rankedList as $fighter){
			if($fighter['subGroupNum'] > 0){
				@$numInSubGroup[$fighter['subGroupNum']]++;  // Might not exist, treat as zero.
				if($fighter['subGroupNum'] > $highestSubGroup){
					$highestSubGroup = $fighter['subGroupNum'] ;
				}
			} else {
				break;
			}
		}

		foreach($numInSubGroup as $subGroupNum => $num){
			$reservedPools[$subGroupNum] = (int)ceil($num/$maxPoolSize);
		}

		// Sub Groups modify the tier behavior to make each sub-group it's own tier
		$specifiedPoolsInTier = $poolsInTier;
		$poolsInTier = $reservedPools[$highestSubGroup];
		$workingSubGroup = $highestSubGroup;
	} else {
		$specifiedPoolsInTier = $poolsInTier;
		$workingSubGroup = 0;
	}

	if($rankedList == null){
		setAlert(USER_ERROR,"No seeding data found.<BR>Pools not generated");
		return;
	}

	$lowestRatingSet = false;
	$lowestRating = null;
	if($useRatings == true){
		foreach($rankedList as $data){
			if($lowestRatingSet == true){
				if($data['rating'] < $lowestRating){
					$data['rating'] = $lowestRating;
				}
			} else {
				$lowestRating = $data['rating'];
				$lowestRatingSet = true;
			}
		}
	}

	$tier = 1;

	if($useRefights == true){
		// Get list of how many times each fighter has fought each other
		foreach($rankedList as $f1){
			$rosterID1 = $f1['rosterID'];
			foreach($rankedList as $f2){
				$rosterID2 = $f2['rosterID'];
				$numberOfFightsTogether[$rosterID1][$rosterID2] = 
					getNumberOfFightsTogether($rosterID1, $rosterID2, $tournamentID);
			}
		}
	}
$a = false;
// Start going through list and assigning fighters
	while(count($rankedList) > 0 && $numSpotsLeft > 0){

		// Start and stop number for pools
		if(isset($endPoolNum)){
			$startPoolNum = $endPoolNum + 1;
			if($startPoolNum > $numberOfPools - 1){
				break;
			}
		} else {
			$startPoolNum = 0;
		}
		
		if($poolsInTier == 0){
			$endPoolNum = $numberOfPools - 1; // zero-indexed
		} else{
			$endPoolNum = $startPoolNum + $poolsInTier - 1;
			if($endPoolNum > ($numberOfPools - 1) ){
				$endPoolNum = $numberOfPools - 1;
			}
		}

		$numSpotsLeft = $maxPoolSize* ($numberOfPools - $startPoolNum + 1);
		if($numSpotsLeft <= 0){
			break;
		}

		unset($numInPools);
		unset($poolRatings);
		unset($poolSchools);
		unset($poolPoints);
		for($i = $startPoolNum;$i <= $endPoolNum; $i++){
			$numInPools[$i] = 0;
			$poolRatings[$i] = 0;
			$poolSchools[$i] = [];
			$poolRefights[$i] = 0;
		}

		foreach($rankedList as $index => $fighter){

			if($workingSubGroup != 0
				&& $workingSubGroup != $fighter['subGroupNum']){

				// Switch to a new sub-group. Break the loop and re-calculate the tiers.
				$workingSubGroup = $fighter['subGroupNum'];
				if($workingSubGroup == 0){
					$poolsInTier = $specifiedPoolsInTier;
				} else {
					$poolsInTier = $reservedPools[$workingSubGroup];
				}
				$tier++;
				break;
			}

			$sizePoints = generate_PoolSizePoints($numInPools,$maxPoolSize);
			if($useRatings == true){
				$ratingPoints = generate_PoolRatingPoints($poolRatings,$fighter['rating']);
			}
			if($useSchools == true){
				$schoolPoints = generate_SameSchoolPoints($poolSchools,$fighter['schoolID']);
			}
			if($useRefights == true){
				$refightPoints = generate_RefightPoints($poolRefights,
														$numberOfFightsTogether,
														$fighter['rosterID']);
			}
			
			// Add in points function for fighter rank
			// For using pool standings, assign each a rank based on their placing.
			// First place gets highest rank
			
			$maxPoolPoints = -1;
			$poolToAddTo = -1;


			for($i = $startPoolNum;$i <= $endPoolNum; $i++){
				
				// If sizePoints < 0 then the pool is full
				// You can't assign someone, no matter what the other metrics say.
				if($sizePoints[$i] < 0){
					$poolPoints[$i] = -99;
					continue;
				}

				// Add up all the different points 
				$poolPoints[$i] = $sizePoints[$i];
				if($useRatings == true){
					$poolPoints[$i] += $ratingPoints[$i];
				}
				if($useSchools == true){
					$poolPoints[$i] += $schoolPoints[$i];
				}
				if($useRefights == true){
					$poolPoints[$i] += $refightPoints[$i];
				}

				if($poolPoints[$i] > $maxPoolPoints){
					$maxPoolPoints = $poolPoints[$i];
					$poolToAddTo = $i;
				}

			}

			// If this is true there wasn't an avaliable pool to add to.
			// Break the loop.
			if($poolToAddTo < 0){
				$tier++;
				break;
			}

			$_SESSION['poolSeeds'][$poolToAddTo][] = $fighter['rosterID'];

			if(@$specifications['debug'] != false){
				show_poolGeneration($fighter['rosterID'],
									$poolPoints,
									$sizePoints,
									@$ratingPoints,
									@$schoolPoints,
									@$refightPoints);
			}

			$numInPools[$poolToAddTo]++;
			$poolRatings[$poolToAddTo] += $fighter['rating'];

			if(isset($poolSchools[$poolToAddTo][$fighter['schoolID']])){
				$poolSchools[$poolToAddTo][$fighter['schoolID']]++;
			} else {
				$poolSchools[$poolToAddTo][$fighter['schoolID']] = 1;
			}

			$numSpotsLeft--;
			unset($rankedList[$index]);
		}

	}

}

/******************************************************************************/

function pool_GeneratePolarPools($specifications){
// Calculate advancements to move onto the next pool set using the parameters specified.
// Parameters are the number of 
			
	$tournamentID = (int)$_SESSION['tournamentID'];
	if($tournamentID == 0){return;}
	$groupSet = $specifications['groupSet'];
	$lastGroupSet = $groupSet - 1;

	// determine which algorithim to use
	$maxPoolSize = maxPoolSize();
	if($maxPoolSize == 0){
		setAlert(SYSTEM, "No maxPoolSize in pool_GeneratePolarPools()");
		return;
	}
	$numberOfPools = count(getPools($tournamentID, $groupSet));
	
	$sql = "SELECT MAX(rating) AS maxRating1, MAX(rating2) AS maxRating2,
					MIN(rating) AS minRating1, MIN(rating2) AS minRating2
			FROM eventTournamentRoster
			WHERE tournamentID = {$tournamentID}";
	$limits = mysqlQuery($sql, SINGLE);
	$range1 = $limits['maxRating1'] - $limits['minRating1'];
	$range2 = $limits['maxRating2'] - $limits['minRating2'];

	if($range1 == 0 || $range2 == 0){
		setAlert(USER_ERROR,"Rating generation failure.
			<BR>Rating and/or Rating2 don't have a range of data. Ratings can not all be the same.");
		return;
	}

	$sql = "SELECT rosterID, rating, rating2, subGroupNum
			FROM eventTournamentRoster
			WHERE tournamentID = {$tournamentID}";
	$ratings = mysqlQuery($sql, ASSOC);

	$numToAssign = count($ratings);
	$numAssigned = 0;
	$maxSubGroup = 0;

	foreach($ratings as $rating){
		// Map the ratings on [-1,1]

		if($rating['rating'] == 0 || $rating['rating2'] == 0){
			setAlert(USER_ERROR,"Fighter(s) with zero ratings.<BR>
				<em>All fighters <u>must</u> have Rating and Rating2 set
				to use Polar Seeding.</em>");
			return;
		}

		$x = (($rating['rating'] - $limits['minRating1']) / ($range1/2)) - 1;
		$y = (($rating['rating2'] - $limits['minRating2']) / ($range2/2)) - 1;
		$mag = sqrt(($x * $x) + ($y * $y));
		if($mag == 0){
			$angle = 0;
		} elseif($y > 0) {
			$angle = acos($x/$mag);
		} else {
			$angle = (2 * M_PI) - acos($x/$mag);
		}

		$p['mag'] = $mag;
		$p['dir'] = $angle / (2 * M_PI); // Scale angle on [0,1]
		$p['rosterID'] = $rating['rosterID'];

		if($specifications['useSubGroups'] == true){
			$subGroupNum = $rating['subGroupNum'];
		} else {
			$subGroupNum = 0;
		}

		if($subGroupNum > $maxSubGroup){
			$maxSubGroup = $subGroupNum;
		}
		$polarList[$subGroupNum][] = $p;
		@$fightersInSubGroup[$subGroupNum]++; // might not exist, treat as zero.
		
	}

	$lowestPoolInBound = 0;

	for($subGroupNum = $maxSubGroup; $subGroupNum >= 0; $subGroupNum--){

		if(isset($polarList[$subGroupNum]) == false){
			continue;
		}
		$subGroupList = $polarList[$subGroupNum];

		if($subGroupNum != 0){
			$numPoolsToUse = ceil($fightersInSubGroup[$subGroupNum]/$maxPoolSize);
		} else {
			$numPoolsToUse = $numberOfPools;
		}
		
		// There are no more pools to use
		if($lowestPoolInBound > ($numberOfPools - 1)){
			break;
		}

		$highestPoolInBound = $lowestPoolInBound + $numPoolsToUse -1;
		if($highestPoolInBound > ($numberOfPools - 1)){
			$highestPoolInBound = ($numberOfPools - 1);
		}
		
		$numPoolsInRange = ($highestPoolInBound - $lowestPoolInBound) + 1;

		$poolPositions = [];
		$poolSizes = [];
		for($i = $lowestPoolInBound; $i <= $highestPoolInBound;$i++){

			// The 0.5 offset 'shifts' the relative position of the pool in the list so that
			// they are balanced between all the positions. eg: 4 pools, pool 0 is at 1/8 and
			// pool 3 is at 7/8, instead of one being right at the edge.
			$poolPositions[$i] = ($i+0.5)/$numPoolsInRange;
			$poolSizes[$i] = 0;
		}

		// Sort each sub-group from highest magnitude to lowest
		$sort1 = [];
		foreach($subGroupList as $key => $entry){
			$sort1[$key] = $entry['mag'];
		}
		array_multisort($sort1, SORT_DESC, $subGroupList);

		foreach($subGroupList as $polarRating){

			$closestPool = null;
			$minDistance = 1;

			foreach($poolPositions as $poolNum => $poolPosition){

				$distanceToPool = abs($poolPosition - $polarRating['dir']);

				// Handle 'wrap-arround'
				if($distanceToPool > 0.5){
					if($polarRating['dir'] > $poolPosition){
						$poolPosition += 1;
					} else {
						$poolPosition -= 1;
					}
					$distanceToPool = abs($poolPosition - $polarRating['dir']);
				}

				if($distanceToPool < $minDistance){
					$minDistance = $distanceToPool;
					$closestPool = $poolNum;
				}
				
			}

			if($closestPool !== null){
				$_SESSION['poolSeeds'][$closestPool][] = $polarRating['rosterID'];
				$numAssigned++;
				$poolSizes[$closestPool]++;
				if($poolSizes[$closestPool] == $maxPoolSize){
					unset($poolPositions[$closestPool]);
				}
			}

		}

		$lowestPoolInBound = $highestPoolInBound + 1;

	}

	if($numAssigned < $numToAssign){
		$leftOut = $numToAssign - $numAssigned;
		setAlert(USER_ERROR,"Could not find space for <strong>{$leftOut}</strong> fighters.");
	}

}


/******************************************************************************/

function pool_NormalizeSizes($fighterStats, $tournamentID, $groupSet = 1){
// Normalizes the raw exchange data of all fighters to the
// value that the normalize pool size has been set to	
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	
	if(isEntriesByTeam($tournamentID) && isMatchesByTeam($tournamentID) == false){
		$numberOfMatches = getNormalizationAveraged($tournamentID, $groupSet) - 1;
	} elseif(isCumulative($groupSet, $tournamentID)){
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

function pool_RankFighters($tournamentID, $groupSet = 1, $useTeams = false){
// Assigns a rank to all fighters in the given group set

	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No tournamentID in pool_RankFighters()";
		return;
	}
	$useTeams = (int)((bool)$useTeams);
	$rankedPools = getRankedPools($tournamentID, $groupSet);

// Load meta data on how to rank fighters
	$sql = "SELECT poolWinnersFirst, 
			orderByField1, orderBySort1,
			orderByField2, orderBySort2,
			orderByField3, orderBySort3,
			orderByField4, orderBySort4
			FROM eventTournaments
			INNER JOIN systemRankings USING(tournamentRankingID)
			WHERE tournamentID = {$tournamentID}";

	$meta = mysqlQuery($sql, SINGLE);
	$numWinners = (int)$meta['poolWinnersFirst'];
	unset($meta['poolWinnersFist']);

	// Check that there isn't a pool smaller than the number of pool winners.
	$sql = "SELECT MIN(numFighters) AS smallestGroup
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupType = 'pool'
			AND groupSet = {$groupSet}";
	$smallestGroup = mysqlQuery($sql, SINGLE, 'smallestGroup');
	if($smallestGroup < $numWinners && $smallestGroup > 0){
		setAlert(USER_ERROR,
			"There is a pool smaller than the number of pool winners to select.<BR>
			Rank pool winners first will behave unpredictably.");

	}


// Check which of the ORDER BY fields are valid/used	
	if($meta['orderByField1'] == '' 
		|| ($meta['orderBySort1'] != 'ASC' && $meta['orderBySort1'] != 'DESC')){
		$_SESSION['alertMessages']['systemErrors'][] = "Invalid sort data in sort_Simple()";
		return;
	}

	$orderBy = "{$meta['orderByField1']} {$meta['orderBySort1']}";

	if($meta['orderByField2'] != '' 
		&& ($meta['orderBySort2'] == 'ASC' || $meta['orderBySort2'] == 'DESC')){
			$orderBy .= ", {$meta['orderByField2']} {$meta['orderBySort2']}";
	}

	if($meta['orderByField3'] != '' 
		&& ($meta['orderBySort3'] == 'ASC' || $meta['orderBySort3'] == 'DESC')){
			$orderBy .= ", {$meta['orderByField3']} {$meta['orderBySort3']}";
	}

	if($meta['orderByField4'] != '' 
		&& ($meta['orderBySort4'] == 'ASC' || $meta['orderBySort4'] == 'DESC')){
			$orderBy .= ", {$meta['orderByField4']} {$meta['orderBySort4']}";
	}

// Retrieve List
	$sql = "SELECT standingID, groupID, rosterID
			FROM eventStandings
			INNER JOIN eventRoster USING(rosterID)
			WHERE tournamentID = {$tournamentID}
			AND groupSet = {$groupSet}
			AND isTeam = {$useTeams}
			ORDER BY {$orderBy}";

	$rankedList = mysqlQuery($sql, ASSOC);

	if($rankedPools != null){
		$rankedList = pool_RankFightersByPool($rankedPools, $rankedList, $tournamentID, 
											  $groupSet, $useTeams, $orderBy);
	}
		

	$ignores = getIgnores($tournamentID, 'soloAtSet');
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
	} else {
		$loserPlacing = 1;
	}


// Update each fighter with their placing
	foreach($rankedList as $standing){
		$standingID = $standing['standingID'];
		if(@$ignores[$standing['rosterID']] >= $groupSet){ // if ignores is unset it is a logical zero
			$sql = "UPDATE eventStandings
					SET rank = NULL
					WHERE standingID = {$standingID}";
			mysqlQuery($sql, SEND);
			continue;
		}

		$groupID = $standing['groupID'];
		if(!isset($winnersInGroup[$groupID])){
			$winnersInGroup[$groupID] = 0;
		}

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

function pool_RankFightersByPool($rankedPools, $rankedList, 
								 $tournamentID, $groupSet, 
								 $useTeams, $orderBy){


	$sql = "SELECT standingID, groupID, rosterID, groupRank, overlapSize, score
			FROM eventStandings eS
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN eventGroupRankings USING(groupID) 
			WHERE tournamentID = {$tournamentID}
			AND groupSet = {$groupSet}
			AND isTeam = {$useTeams}
			ORDER BY groupRank ASC, {$orderBy}";
	$rankedByPoolList = mysqlQuery($sql, ASSOC);
	
	$maxPosition = count($rankedByPoolList) - 1;

	$sql = "SELECT numFighters, groupID
			FROM eventGroups
			WHERE tournamentID = {$tournamentID}
			AND groupSet = {$groupSet}
			AND groupType ='pool'";
	$numInGroup = mysqlQuery($sql, KEY_SINGLES, 'groupID','numFighters');

	$oldGroupID = null;
	$poolPosition = 1;
	$absPosition = 0;
	$finalizedList = [];
	$inOverlap = false;

	foreach($rankedByPoolList as $index => $entry){
		$groupID = $entry['groupID'];

		if($oldGroupID != $groupID){
			$oldGroupID = $groupID;
			$poolPosition = 1;
		}

		if($inOverlap == true){

			if($poolPosition <= $entry['overlapSize'])
			{
				$overlapList[$entry['rosterID']] = true;
			} 

			if(    $poolPosition > $entry['overlapSize']
				|| $absPosition >= $maxPosition){

				$overlapPlace = $overlapStart;
				foreach($rankedList as $person){

					if(isset($overlapList[$person['rosterID']]) == true){
						$finalizedList[$overlapPlace] = $person;
						$overlapPlace++;
					}
				}

				if($poolPosition > $entry['overlapSize']){
					$inOverlap = false;
				unset($overlapList);
				}

			}

		}

		// Entering a new overlap region
		if(    $inOverlap == false
			&& $poolPosition > ($numInGroup[$groupID] - $entry['overlapSize'])
			&& $absPosition != $maxPosition
		){
			$overlapStart = $absPosition;
			$overlapList[$entry['rosterID']] = true;
			$inOverlap = true;

		}

		if($inOverlap == false){
			$finalizedList[$absPosition] = $entry;
		}

		$oldGroupID = $groupID;
		$absPosition++;
		$poolPosition++;

	}


	return $finalizedList;
}

/******************************************************************************/

function pool_CalculateTeamScores($tournamentID, $setNumber = 1){


	$rosters = getPoolRosters($tournamentID, $setNumber);
	$standingsToKeep = [];

	$ignores = getIgnores($tournamentID,'ignoreAtSet');
	$teamRosters = getTeamRosters($tournamentID);

	foreach($teamRosters as $teamID => $teamRoster){
		if(count($teamRoster) == 0){
			continue;
		}

		$inRange = '';
		foreach($teamRoster['members'] as $fighter){
			if($inRange != ''){
				$inRange .= ', ';
			}
			$inRange .= $fighter['rosterID'];
		}

	// Don't do anything if no one in the team has fought
		$sql = "SELECT COUNT(*) AS numRecords
				FROM eventStandings
				WHERE tournamentID = {$tournamentID}
				AND groupSet = {$setNumber}
				AND rosterID IN({$inRange})";
		$numRecords = mysqlQuery($sql, SINGLE, 'numRecords');
		
		if($numRecords < 1){
			continue;
		}


	// Find the standingID to update, or create a new one if it doesn't exist
		$sql = "SELECT standingID
				FROM eventStandings
				WHERE tournamentID = {$tournamentID}
				AND groupSet = {$setNumber}
				AND rosterID = {$teamID}";
		$standingID = mysqlQuery($sql, SINGLE, 'standingID');

		if($standingID == null){
			$sql = "INSERT INTO eventStandings
					(tournamentID, groupSet, rosterID, groupType)
					VALUES
					({$tournamentID}, {$setNumber}, {$teamID}, 'pool')";
			mysqlQuery($sql, SEND);

			$standingID = mysqli_insert_id($GLOBALS["___mysqli_ston"]);
		}

	// Prepare query to update team score
		$fields = ['score', 'matches', 'wins', 'losses','ties','pointsFor','pointsAgainst',
					'hitsFor','hitsAgainst','afterblowsFor','afterblowsAgainst',
					'doubles','noExchanges','AbsPointsFor','AbsPointsAgainst', 'AbsPointsAwarded',
					'numPenalties','penaltiesAgainstOpponents','penaltiesAgainst','doubleOuts'];
		$selectClause = implode("), SUM(", $fields);
		$selectClause = "SUM(".$selectClause.")";


		$sql = "SELECT {$selectClause}
				FROM eventStandings
				WHERE tournamentID = {$tournamentID}
				AND groupSet = {$setNumber}
				AND rosterID IN({$inRange})";
		$teamData = mysqlQuery($sql, SINGLE);
		
		$updateClause = '';
		foreach($fields as $field){
			if($updateClause != ''){
				$updateClause .= ', ';
			}
			$updateClause .= $field.'='.((float)$teamData["SUM(".$field.")"]);

		}

		$sql = "UPDATE eventStandings
				SET
				{$updateClause}
				WHERE standingID = {$standingID}";
		mysqlQuery($sql, SEND);

		$standingsToKeep[] = $standingID;


	}

	// Delete old standings
	if(count($standingsToKeep) > 0){
		$inString = " AND standingID NOT IN(".implode(',',$standingsToKeep).")";
	} else {
		$inString = "";
	}
 
	$sql = "DELETE eventStandings 
			FROM eventStandings
			INNER JOIN eventRoster ON eventStandings.rosterID = eventRoster.rosterID
			WHERE tournamentID = {$tournamentID}
			AND groupSet = {$setNumber}
			AND isTeam = 1
			{$inString}";
	mysqlQuery($sql, SEND);

}

/******************************************************************************/

function scored_AddExchanges(){
// Select the appropriate function to add exchanges to a piece given
// the tournament format
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}

	$matchID = $_SESSION['matchID'];
	if($matchID == null){
		return;
	}
	$matchInfo = getMatchInfo($matchID);
	$tournamentID = $matchInfo['tournamentID'];

	$numToAdd = (int)$_POST['exchangesToAdd'];

	
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

	$matchInfo = getMatchInfo($matchID);

// Determine which algorithim to use	
	$funcName = getDisplayFunctionName($matchInfo['tournamentID']);
	if($funcName == null){return;}
	$funcName = "_".$funcName."_displayExchange";

	$exchanges = getMatchExchanges($matchID);
	$i = 0;

	echo "<table>";
// Display Header
	call_user_func($funcName,'','header');

// Display Exchanges	
	foreach($exchanges as $exchange){
		call_user_func($funcName,$exchange,++$i);	
	}
	echo "</table>";

}

/******************************************************************************/

function scored_UpdateExchanges($tournamentID = null){
// Select the correct function to update the exchanges of a piece
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
	if(!isset($_POST['scores'])){return;}
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	
// determine which algorithim to use
	$funcName = getScoringFunctionName($tournamentID);
	if($funcName == null){return;}
	$funcName = "_".$funcName."_updateExchanges";

// Get Exchanges to Update & Score values
	$exchangesToUpdate = call_user_func($funcName);

	if($exchangesToUpdate == null){
		return;
	}

// Update Exchanges
	foreach((array)$exchangesToUpdate as $exchangeID => $data){
		$scoreValue = (float)$data['scoreValue'];
		$scoreDeduction = (float)$data['scoreDeduction'];
		$sampleExchangeID = $exchangeID;

		$sql = "UPDATE eventExchanges
				SET scoreValue = {$scoreValue}, 
				scoreDeduction = {$scoreDeduction},
				exchangeType = 'scored'
				WHERE exchangeID = {$exchangeID}";
		mysqlQuery($sql, SEND);
	}
	
	updateMatch(getMatchInfo($_POST['matchID']));
	
	if(isLastPiece($tournamentID)){
		$_SESSION['askForFinalization'] = true;
	}

}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
