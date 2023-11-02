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

	$matchID = (int)$matchInfo['matchID'];
	$tournamentID = (int)$matchInfo['tournamentID'];

	if($matchID == 0 || $tournamentID == 0){
		return;
	}

	$sql = "SELECT exchangeID
			FROM eventExchanges
			WHERE matchID = {$matchID}";
	$res = mysqlQuery($sql, SINGLE);

	$basePointValue = getBasePointValue($tournamentID, $_SESSION['groupSet']);

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

function _DeductionBased_displayExchange($exchange = null, $exchangeNum = null){
// Displays an exchange for pieces using 'Deduction Based' scoring
// A request to generate a header may be passed instead of an exchange

	$basePoints = getBasePointValue($_SESSION['tournamentID'], $_SESSION['groupSet']);
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

	$basePointValue = getBasePointValue($_SESSION['tournamentID'], $_SESSION['groupSet']);
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

	$basePointValue = getBasePointValue($_SESSION['tournamentID'], $_SESSION['groupSet']);
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

	$basePointValue = getBasePointValue($_SESSION['tournamentID'], $_SESSION['groupSet']);
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

	$basePointValue = getBasePointValue($_SESSION['tournamentID'], $_SESSION['groupSet']);
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

	$tournamentID = (int)$tournamentID;
	$groupSet = (int)$groupSet;
	$scores = [];

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

		$score = (int)$score;
		$rosterID = (int)$rosterID;

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

		$rosterID = (int)$standing['rosterID'];
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

function _PhoMatchPoints_calculateScore($tournamentID, $groupSet = 1){

	$tournamentID = (int)$tournamentID;
	$groupSet = (int)$groupSet;

	// Calculate the normalized size
	$normalizedMatches = getNormalization($tournamentID, $groupSet) - 1;

	$sql = "SELECT standingID, rosterID, matches, wins, losses, ties
			FROM eventStandings
			WHERE tournamentID = {$tournamentID}
			AND groupSet = {$groupSet}";
	$standingsToScore = mysqlQuery($sql, ASSOC);

	if($standingsToScore == null){
		return;
	}

	foreach($standingsToScore as $standing){

		$rosterID = (int)$standing['rosterID'];
		$standingID = (int)$standing['standingID'];

		$score = 0;
		$score += 9 * $standing['wins'];
		$score += 6 * $standing['ties'];
		$score += 3 * $standing['losses'];

		$sql = "SELECT matchID,
					(
						SELECT COUNT(*) AS numDoubles
						FROM eventExchanges AS eE2
						WHERE eE2.matchID = eM.matchID
						AND exchangeType = 'double'
					) AS numDoubles
				FROM eventMatches AS eM
				INNER JOIN eventGroups USING(groupID)
				WHERE (fighter1ID= {$rosterID} OR fighter2ID = {$rosterID})
				AND tournamentID = {$tournamentID}
				AND groupType = 'pool'
				AND groupSet = {$groupSet}
				AND ignoreMatch = 0
				AND matchComplete = 1";
		$matches = mysqlQuery($sql, ASSOC);

		$numMatches = 0;
		$doublesPenalty = 0;

		foreach($matches as $match){
			$numMatches++;

			if($match['numDoubles'] > 1){
				$doublesPenalty -= ($match['numDoubles'] - 1);
			}
		}

		if($numMatches != 0){
			$doublesPenalty *= $normalizedMatches/$numMatches;
		} else {
			$doublesPenalty = 0;
		}

		$score += $doublesPenalty;

		$sql = "UPDATE eventStandings
				SET score = {$score}
				WHERE standingID = {$standingID}";
		mysqlQuery($sql, SEND);
	}
}

/******************************************************************************/

function _Schnegel_calculateScore($tournamentID, $groupSet = 1){

	$tournamentID = (int)$tournamentID;
	$groupSet = (int)$groupSet;

	// Calculate the normalized size
	$normalizedMatches = getNormalization($tournamentID, $groupSet) - 1;

	$sql = "SELECT standingID, rosterID, matches, wins, losses, ties
			FROM eventStandings
			WHERE tournamentID = {$tournamentID}
			AND groupSet = {$groupSet}";
	$standingsToScore = mysqlQuery($sql, ASSOC);

	if($standingsToScore == null){
		return;
	}

	foreach($standingsToScore as $standing){

		$rosterID = (int)$standing['rosterID'];
		$standingID = (int)$standing['standingID'];

		$score = 0;

		$sql = "SELECT matchID, winnerID, fighter1ID, fighter2ID, fighter1Score, fighter2Score
				FROM eventMatches AS eM
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
			if((int)$match['winnerID'] == $rosterID){
				$score += 10;
			} else if((int)$match['winnerID'] == 0) {

				if($match['fighter1ID'] == $rosterID){
					$score += $match['fighter1Score'];
				} else {
					$score += $match['fighter2Score'];
				}

			} else {
				// No points.
			}

			$numMatches++;
		}

		if($numMatches != 0 && $numMatches != $normalizedMatches){
			$score *= $normalizedMatches/$numMatches;
		}

		$sql = "UPDATE eventStandings
				SET score = {$score}
				WHERE standingID = {$standingID}";
		mysqlQuery($sql, SEND);
	}
}

/******************************************************************************/

function _Schnegel2_calculateScore($tournamentID, $groupSet = 1){

	$tournamentID = (int)$tournamentID;
	$groupSet = (int)$groupSet;

	// Calculate the normalized size
	$normalizedMatches = getNormalization($tournamentID, $groupSet) - 1;

	$sql = "SELECT standingID, rosterID, matches, wins, losses, ties
			FROM eventStandings
			WHERE tournamentID = {$tournamentID}
			AND groupSet = {$groupSet}";
	$standingsToScore = mysqlQuery($sql, ASSOC);

	if($standingsToScore == null){
		return;
	}

	foreach($standingsToScore as $standing){

		$rosterID = (int)$standing['rosterID'];
		$standingID = (int)$standing['standingID'];

		$score = 0;

		$sql = "SELECT
					(
						SELECT COUNT(*) AS numClean
						FROM eventExchanges AS eE2
						WHERE eE2.matchID = eM.matchID
						AND scoringID = {$rosterID}
						AND exchangeType = 'clean'
					) AS numClean,
					(
						SELECT SUM(scoreValue) AS pointsFor1
						FROM eventExchanges AS eE3
						WHERE eE3.matchID = eM.matchID
						AND scoringID = {$rosterID}
						AND (exchangeType = 'clean' OR exchangeType = 'afterblow')
					) AS pointsFor1,
					(
						SELECT SUM(scoreDeduction) AS pointsFor2
						FROM eventExchanges AS eE4
						WHERE eE4.matchID = eM.matchID
						AND receivingID = {$rosterID}
						AND exchangeType = 'afterblow'
					) AS pointsFor2,
					(
						SELECT SUM(scoreValue) AS penalties
						FROM eventExchanges AS eE5
						WHERE eE5.matchID = eM.matchID
						AND scoringID = {$rosterID}
						AND exchangeType = 'penalty'
					) AS penalties
				FROM eventMatches AS eM
				INNER JOIN eventGroups USING(groupID)
				WHERE (fighter1ID= {$rosterID} OR fighter2ID = {$rosterID})
				AND tournamentID = {$tournamentID}
				AND groupType = 'pool'
				AND groupSet = {$groupSet}
				AND ignoreMatch = 0
				AND matchComplete = 1";
		$matches = (array)mysqlQuery($sql, ASSOC);

		$cleanHits = 0;
		$pointsAwarded = 0;
		$numMatches = 0;

		foreach($matches as $match){

			$cleanHits += $match['numClean'];
			$pointsAwarded += $match['pointsFor1'];
			$pointsAwarded += $match['pointsFor2'];
			$pointsAwarded += $match['penalties'];

			$numMatches++;
		}

		$score =  0.01 * $cleanHits * $pointsAwarded;

		if($numMatches != 0 && $numMatches != $normalizedMatches){
			$score *= $normalizedMatches/$numMatches;
		}

		$sql = "UPDATE eventStandings
				SET score = {$score}
				WHERE standingID = {$standingID}";
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

		$rosterID = (int)$standing['rosterID'];
		$standingID = (int)$standing['standingID'];
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
			$matchID = (int)$match['matchID'];

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
				WHERE standingID = {$standingID}";
		mysqlQuery($sql, SEND);
	}
}

/******************************************************************************/

function _MidWinter_calculateScore($tournamentID, $groupSet = 1){

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

	$sql = "SELECT matchID, COUNT(*) AS numDoubles
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			WHERE exchangeType = 'double'
			AND tournamentID = {$tournamentID}
			AND groupType = 'pool'
			AND groupSet = {$groupSet}
			AND ignoreMatch = 0
			AND matchComplete = 1
			GROUP BY matchID";
	//$numDoublesInMatch = (array)mysqlQuery($sql, KEY_SINGLES, 'matchID', 'numDoubles');

	foreach($standingsToScore as $standing){

		$rosterID = (int)$standing['rosterID'];
		$standingID = (int)$standing['standingID'];
		$score = 0;

		$sql = "SELECT matchID, winnerID,
					(
						SELECT COUNT(*) AS num
						FROM eventExchanges AS eE2
						WHERE eE2.matchID = eM.matchID
						AND (      (	 (scoringID != {$rosterID})
									 AND (    exchangeType = 'clean'
										   OR exchangeType = 'afterblow'))
								OR (exchangeType = 'double')
							)


					) AS numExchangesLost,
					(
						SELECT COUNT(*) AS num
						FROM eventExchanges AS eE3
						WHERE eE3.matchID = eM.matchID
						AND exchangeType = 'double'
					) AS numDoubles
				FROM eventMatches AS eM
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
			$matchID = (int)$match['matchID'];

			$numMatches++;

			$sql = "SELECT scoringID, exchangeType
					FROM eventExchanges
					WHERE matchID = {$matchID}
					AND exchangeType IN ('double','clean','afterblow')
					ORDER BY exchangeNumber ASC
					LIMIT 1";
			$firstHit = (array)mysqlQuery($sql, SINGLE);

			if($match['winnerID'] == $rosterID){
				$score += 2;
			}


			if($firstHit != [] && $firstHit['scoringID'] == $rosterID && $firstHit['exchangeType'] != 'double' ){
				$score += 1;
			}

			if($match['numExchangesLost'] == 0){
				$score += 1;
			}

			if($match['numDoubles'] != 0){
				$score -= 1;
			}

		}

		if($numMatches != 0){
			$score *= $normalizedMatches/$numMatches;
		} else {
			$score = 0;
		}

		$sql = "UPDATE eventStandings
				SET score = {$score}
				WHERE standingID = {$standingID}";
		mysqlQuery($sql, SEND);
	}
}

/******************************************************************************/

function _PlacingPercent_calculateScore($tournamentPlacings, $basePointValue, $numEntries){

	$scoreData['score'] = 0;
	$scoreData['pointsFor'] = 0;
	$scoreData['pointsAgainst'] = 0;
	$pointsForFighter = [];

// Score the fighters from 0 to [BasePointValue] in each component based on their
// overall placing in each component, and sum the values
	foreach($tournamentPlacings as $placingData){
		$num = $numEntries[$placingData['tournamentID']];
		if($num != 0){
			$points = ($num - ($placingData['placing'] - 1)) / $num;
			$points *= $basePointValue;

			if($points < 0){
				$points = 0;
			}
		} else {
			$points = $basePointValue;
		}

		$scoreData['score'] += $points;
		$scoreData['pointsFor']++;
	}

// Subtract the standard deviation of scores from the average tournament score
	$scoreData['score'] = round($scoreData['score'],0);

	return($scoreData);

}

/******************************************************************************/

function _LpDeviation_calculateScore($tournamentPlacings, $basePointValue, $numEntries){

	$scoreData['score'] = 0;
	$pointsForFighter = [];

// Score the fighters from 0 to [BasePointValue] in each component based on their
// overall placing in each component, and sum the values
	foreach($tournamentPlacings as $placingData){
		$num = $numEntries[$placingData['tournamentID']];
		if($num != 0){
			$points = ($num - ($placingData['placing'] - 1)) / $num;
			$points *= $basePointValue;

			if($points < 0){
				$points = 0;
			}

		} else {
			$points = $basePointValue;
		}

		$scoreData['score']+= $points;
		$pointsForFighter[] = $points;
	}

// Subtract the standard deviation of scores from the average tournament score
	$scoreData['pointsFor'] = round($scoreData['score'],0);
	if(count($pointsForFighter) != 0){
		$scoreData['pointsAgainst'] = round(standardDeviation($pointsForFighter),0);
	} else {
		$scoreData['pointsAgainst'] = 0;
	}
	$scoreData['score'] = $scoreData['pointsFor'] - $scoreData['pointsAgainst'];

	return($scoreData);

}

/******************************************************************************/

function _PlacingCountdown_calculateScore($tournamentPlacings, $basePointValue, $numEntries){

	$scoreData['score'] = 0;
	$pointsForFighter = [];

// Score the fighters from 0 to [BasePointValue] in each component based on their
// overall placing in each component, and sum the values
	$scoreData['pointsFor'] = 0;
	$scoreData['pointsAgainst'] = 0;
	$scoreData['score'] = 0;

	foreach((array)$tournamentPlacings as $placingData){
		$scoreData['pointsFor'] += $basePointValue;
		$against = ($placingData['placing'] - 1);
		if($against < 0){
			$against = 0;
		}
		$scoreData['pointsAgainst'] += $against;
	}

	$scoreData['score'] = $scoreData['pointsFor'] - $scoreData['pointsAgainst'];

	return($scoreData);

}

/******************************************************************************/

function _WessexLeagueStandings_calculateScore($tournamentPlacings, $basePointValue, $numEntries){

// This is a meta tournament scoring algorithm.
// Lookup table base on the placing in each tournament.
	$scoreData['pointsFor'] = 0;
	$scoreData['pointsAgainst'] = 0;
	$scoreData['score'] = 0;
	$eventScore = 0;

	foreach((array)$tournamentPlacings as $placingData){

		if($placingData['placing'] == 1){
			$eventScore = 22;
		} elseif($placingData['placing'] == 2){
			$eventScore = 18;
		} elseif($placingData['placing'] == 3){
			$eventScore = 14;
		} elseif($placingData['placing'] == 4){
			$eventScore = 10;
		} elseif($placingData['placing'] <= 8){
			$eventScore = 6;
		} elseif($placingData['placing'] <= 16){
			$eventScore = 3;
		} else {
			$eventScore = 1;
		}

		$scoreData['pointsFor']++;
		$scoreData['score'] += $eventScore;
	}

	return($scoreData);
}

/******************************************************************************/

function meta_ScoreFighters($mTournamentID){
// Calls the appropriate function to score fighters given the tournament
// scoring algorithm

	$mTournamentID = (int)$mTournamentID;

	$formula = getScoreFormula($mTournamentID);
	$scoreFuncName = "_".substr($formula,1)."_calculateScore";

	$tournamentComponents = getMetaTournamentComponents($mTournamentID,true);
	$basePointValue = (int)getBasePointValue($mTournamentID, null);

// Get all fighters and standings
	$sql = "SELECT rosterID, systemRosterID
			FROM eventTournamentRoster
			INNER JOIN eventRoster USING(rosterID)
			WHERE tournamentID = {$mTournamentID}";
	$tournamentRoster = (array)mysqlQuery($sql, KEY_SINGLES, 'systemRosterID','rosterID');

	$sql = "SELECT standingID, rosterID
			FROM eventStandings
			WHERE tournamentID = {$mTournamentID}";
	$standingIDs = mysqlQuery($sql, KEY_SINGLES, 'rosterID','standingID');

// Query string for all component tournaments
	$notFinalized = [];
	$cTournamentIDs = [];
	$rosterIDs = [];

	foreach($tournamentComponents as $component){
		$cTournamentID = (int)$component['cTournamentID'];

		if($component['isFinalized'] == 0){
			$notFinalized[] = $cTournamentID;
		} else {

			if(isEntriesByTeam($cTournamentID) == false){
				$numEntries[$cTournamentID] = getNumTournamentEntries($cTournamentID);
			} else {
				$numEntries[$cTournamentID] = getNumTournamentGroups($cTournamentID);
			}

			$cTournamentIDs[] = $cTournamentID;

		}

	}

	if(count($cTournamentIDs) != 0){
		$tString = implode2int($cTournamentIDs);

		$sql = "UPDATE eventTournamentComponents
				SET resultsCalculated = CASE
										WHEN componentTournamentID IN ({$tString}) THEN 1
										ELSE 0 END
				WHERE metaTournamentID = {$mTournamentID}";
		mysqlQuery($sql, SEND);

		if($notFinalized != null){
			$str = "Tournament standings for
				<strong>".getTournamentName($mTournamentID)."</strong> recalculated.
				The following components are not finalized and have not been included:";

			foreach($notFinalized as $cTournamentID){
				$eventName = getEventName(getTournamentEventID($cTournamentID));
				$tournamentName = getTournamentName($cTournamentID);



				$str .= "<li>".
					"<strong>[{$eventName}]</strong>: ".
					" <em>{$tournamentName}</em>".
					"</li>";
			}

			setAlert(USER_ALERT,$str);

			if(isFinalized($mTournamentID) == true){
				setAlert(USER_ALERT,"<span class='red-text'><strong>WARNING!!!!</strong>
					You have just changed the standings for a meta-tournament that is <u>already finalized</u>!!<BR> This is going to mess everything up, you should remove the final results, and re-generate the placings once all standings are complete.</span>");
			}
		}


	// Get all the tournament placings
		$sql = "SELECT systemRosterID, placing, highBound, lowBound, tournamentID
				FROM eventPlacings
				INNER JOIN eventRoster USING(rosterID)
				WHERE tournamentID IN ({$tString})";
		$tournamentPlacings = mysqlQuery($sql, ASSOC);

		$placingByFighter = [];
		foreach($tournamentPlacings as $placing){
			$placingByFighter[$placing['systemRosterID']][$placing['tournamentID']] = $placing;
		}

		// get the results of all teams participating
		$sql = "SELECT sR.systemRosterID, placing, highBound, lowBound, eP.tournamentID
				FROM eventPlacings AS eP
				INNER JOIN eventTeamRoster AS eTR ON eP.rosterID = eTR.teamID
					AND memberRole = 'member'
				INNER JOIN eventRoster AS eR ON eTR.rosterID = eR.rosterID
				INNER JOIN systemRoster AS sR ON eR.systemRosterID = sR.systemRosterID
				WHERE eP.tournamentID IN ({$tString})";
		$tournamentPlacings = mysqlQuery($sql, ASSOC);
		foreach($tournamentPlacings as $placing){
			$placingByFighter[$placing['systemRosterID']][$placing['tournamentID']] = $placing;
		}





	// Loop through all fighters and generate their scores
		$placingByFighter = meta_AdjustForComponentGroups($placingByFighter, $mTournamentID, $tString);

		foreach($tournamentRoster as $systemRosterID => $rosterID){

			if(isset($placingByFighter[$systemRosterID]) == false){
				continue;
			}

			$rosterID = (int)$rosterID;
			$rosterIDs[] = $rosterID;
			$systemRosterID = (int)$systemRosterID;

		// Calculate score for the tournament algorithm
			$scoreData = call_user_func($scoreFuncName,
										$placingByFighter[$systemRosterID],
										$basePointValue,
										$numEntries);

			//If any of the fields don't exist it is the same as being zero.
			$score = (int)@$scoreData['score'];
			$pointsFor = (int)@$scoreData['pointsFor'];
			$pointsAgainst = (int)@$scoreData['pointsAgainst'];


		// Update standings table
			if(isset($standingIDs[$rosterID]) == false){

				$sql = "INSERT INTO eventStandings
						(tournamentID, rosterID, groupType, score,
							pointsFor, pointsAgainst, basePointValue)
						VALUES
						({$mTournamentID}, {$rosterID}, 'meta', {$score},
							{$pointsFor}, {$pointsAgainst}, {$basePointValue})";
				mysqlQuery($sql, SEND);

			} else {

				$standingID = (int)$standingIDs[$rosterID];
				$sql = "UPDATE eventStandings
						SET score = {$score}, pointsFor = {$pointsFor},
							pointsAgainst = {$pointsAgainst}, basePointValue = {$basePointValue}
						WHERE standingID = {$standingID}";
				mysqlQuery($sql, SEND);

			}

		}
	} // if(count($cTournamentIDs) != 0)

// Delete any old standings hanging around from fighters without ranks
	$fString = implode2int($rosterIDs);

	$sql = "DELETE FROM eventStandings
			WHERE tournamentID = {$mTournamentID}
			AND rosterID NOT IN ({$fString})";
	mysqlQuery($sql, SEND);

}

/******************************************************************************/

function meta_AdjustForComponentGroups($placingByFighter, $mTournamentID, $tString){
// Looks for any tournaments which are part of component groups, which means
// that only the top <x> placings from a group should be used.

	$conflicts = [];
	$componentGroups = getComponentGroups($mTournamentID);

	foreach($componentGroups as $cGroupID => $cGroup){
		if(isset($cGroup['items']) == false || count($cGroup['items']) <= 1){
			continue;
		}
		$cString = implode2int($cGroup['items']);
		$usedComponents = (int)$cGroup['usedComponents'];

		$sql = "SELECT sR.systemRosterID, COUNT(*) AS numInGroup,
					'{$cString}' AS cString, '{$usedComponents}' AS usedComponents
				FROM eventPlacings AS eP
				INNER JOIN eventRoster AS eR USING(rosterID)
				INNER JOIN systemRoster AS sR USING(systemRosterID)
				WHERE eP.tournamentID IN ({$cString})
				GROUP BY sR.systemRosterID
					HAVING numInGroup > {$usedComponents}";
		$groupConflicts = mysqlQuery($sql, ASSOC);

		if($groupConflicts != null){
			$conflicts = array_merge($conflicts,$groupConflicts);
		}
	}

	foreach($conflicts as $conflict){

		$systemRosterID = (int)$conflict['systemRosterID'];
		$usedComponents = (int)$conflict['usedComponents'];
		$cString = $conflict['cString'];

		$sql = "SELECT tournamentID, placing, systemRosterID
				FROM eventPlacings
				INNER JOIN eventRoster USING(rosterID)
				WHERE systemRosterID = {$systemRosterID}
					AND tournamentID IN ({$cString})
				ORDER BY placing ASC
				LIMIT 9999 OFFSET {$usedComponents}";
		$tournamentsToNotInclude = mysqlQuery($sql, SINGLES, 'tournamentID');

		foreach($tournamentsToNotInclude as $tournamentID){
			unset($placingByFighter[$systemRosterID][$tournamentID]);
		}
	}

	return $placingByFighter;

}

/******************************************************************************/

function pool_ScoreFighters($tournamentID, $groupSet = 1){
// Calls the appropriate function to score fighters given the tournament
// scoring algorithm

	$groupSet = (int)$groupSet;
	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){
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

function poolTableHeader($displayMeta, $maxNumFields){

	echo "<tr>";
	echo "<th>Rank</th>";
	echo "<th>Name</th>";
	echo "<th class='hidden school-name'>School</th>";
	for($i = 1; $i <= $maxNumFields; $i++){
		$index = "displayTitle".$i;
		$name = $displayMeta[$index];

		echo "<th>{$name}</th>";
	}
	echo "</tr>";

}

/******************************************************************************/

function pool_DisplayResults($tournamentID, $groupSet = 1, $showTeams = false){
// Calls the appropriate function to display the fighters pool standings
// given the tournament scoring algorithm

	$tournamentID = (int)$tournamentID;
	$bracketInfo = getBracketInformation($tournamentID);
	$ignores = getIgnores($tournamentID);
	$schoolIDs = getTournamentFighterSchoolIDs($tournamentID);
	$hide = getItemsHiddenByFilters($tournamentID,$_SESSION['filters'],'roster');

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


	$orderBy = "eS.rank ASC";
	if($displayByPool == true){
		$orderBy = "groupNumber ASC, ".$orderBy;
		$numToElims = 0;
	}

	$sql1 = "SELECT eS.rank, rosterID, groupID, schoolID {$selectStr} ";
	$sql2 =	"FROM eventStandings AS eS
			INNER JOIN eventRoster USING(rosterID)
			LEFT JOIN eventGroups USING(groupID)
			WHERE eS.tournamentID = {$tournamentID}
			AND eS.groupSet = {$groupSet}
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






	if($displayByPool == false){
		echo "<table>";
		poolTableHeader($displayMeta, $maxNumFields);
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

			poolTableHeader($displayMeta, $maxNumFields);

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

		if(isset($hide['roster'][$fighter['rosterID']]) == true){
			continue;
		}


		if($showTeams){
			$name = getTeamName($fighter['rosterID']);
		} else {
			$name = getCombatantName($fighter['rosterID']);
		}
		$school = getSchoolName($fighter['schoolID']);

		echo "<tr class='text-center {$class}'>";
		echo "<td>".$fighter['rank']."</td>";
		echo "<td  class='text-left'>{$name}</td>";
		echo "<td class='hidden school-name text-left'>{$school}</td>";
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

		<b>Algorithm Name: <u><b><?=$description['name']?></b></u></b>
		<pre><?=$description['description']?></pre>

	</fieldset>

<?php

}

/******************************************************************************/

function pool_GeneratePools($specifications){
// Calculate advancements to move onto the next pool set using the parameters specified.
// Parameters are the number of

	$tournamentID = (int)$specifications['tournamentID'];
	if($tournamentID == 0){
		return;
	}

	// This is a special mode
	if($specifications['seedMethod'] == 'polar'){
		pool_GeneratePolarPools($specifications);
		return;
	}

	$groupSet = (int)$specifications['groupSet'];
	$lastGroupSet = $groupSet - 1;

	// determine which algorithim to use
	$maxPoolSize = (int)maxPoolSize($tournamentID);
	$poolsInTier = (int)$specifications['poolsInTier'];
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
					LEFT JOIN eventRatings USING(tournamentRosterID)
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
					LEFT JOIN eventRatings USING(tournamentRosterID)
					WHERE tournamentID = {$tournamentID}
					AND (	SELECT COUNT(*)
							FROM eventIgnores eI
							WHERE eI.rosterID = eTR.rosterID
							AND eI.tournamentID = {$tournamentID} ) = 0
					AND rating != 0
					AND rating IS NOT NULL";
			$ratingData = mysqlQuery($sql, SINGLE);

			$ratingFix = false;
			if($ratingData != null && (int)$ratingData['numRated'] != 0){

				$minRating = ((int)$ratingData['maxRating'] - (int)$ratingData['minRating']);
				$minRating /= (int)$ratingData['numRated'];
				$minRating = (int)$ratingData['minRating'] - $minRating;
				$minRating = round($minRating,0);

				if($minRating > 0) {
					foreach($rankedList as $index => $fighter){
						if((int)$fighter['rating'] == 0){
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
					LEFT JOIN eventRatings USING(tournamentRosterID)
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
			if((int)$fighter['subGroupNum'] > 0){
				@$numInSubGroup[(int)$fighter['subGroupNum']]++;  // Might not exist, treat as zero.
				if((int)$fighter['subGroupNum'] > $highestSubGroup){
					$highestSubGroup = (int)$fighter['subGroupNum'] ;
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
				if((int)$data['rating'] < $lowestRating){
					$data['rating'] = $lowestRating;
				}
			} else {
				$lowestRating = (int)$data['rating'];
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
				&& $workingSubGroup != (int)$fighter['subGroupNum']){

				// Switch to a new sub-group. Break the loop and re-calculate the tiers.
				$workingSubGroup = (int)$fighter['subGroupNum'];
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
				$ratingPoints = generate_PoolRatingPoints($poolRatings,(int)$fighter['rating']);
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
			$poolRatings[$poolToAddTo] += (int)$fighter['rating'];

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
	$groupSet = (int)$specifications['groupSet'];
	$lastGroupSet = $groupSet - 1;

	// determine which algorithim to use
	$maxPoolSize = maxPoolSize($tournamentID);
	if($maxPoolSize == 0){
		setAlert(SYSTEM, "No maxPoolSize in pool_GeneratePolarPools()");
		return;
	}
	$numberOfPools = count(getPools($tournamentID, $groupSet));

	$sql = "SELECT MAX(rating) AS maxRating1, MAX(rating2) AS maxRating2,
					MIN(rating) AS minRating1, MIN(rating2) AS minRating2
			FROM eventTournamentRoster
			INNER JOIN eventRatings USING(tournamentRosterID)
			WHERE tournamentID = {$tournamentID}";
	$limits = mysqlQuery($sql, SINGLE);
	$range1 = (int)$limits['maxRating1'] - (int)$limits['minRating1'];
	$range2 = (int)$limits['maxRating2'] - (int)$limits['minRating2'];

	if($range1 == 0 || $range2 == 0){
		setAlert(USER_ERROR,"Rating generation failure.
			<BR>Rating and/or Rating2 don't have a range of data. Ratings can not all be the same.");
		return;
	}

	$sql = "SELECT rosterID, rating, rating2, subGroupNum
			FROM eventTournamentRoster
			INNER JOIN eventRatings USING(tournamentRosterID)
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

		$x = (((int)$rating['rating'] - (int)$limits['minRating1']) / ($range1/2)) - 1;
		$y = (((int)$rating['rating2'] - (int)$limits['minRating2']) / ($range2/2)) - 1;
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
			$subGroupNum = (int)$rating['subGroupNum'];
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

	$tournamentID = (int)$tournamentID;
	if($tournamentID == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No tournamentID in pool_RankFighters()";
		return;
	}
	$groupSet = (int)$groupSet;

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
		$standingID = (int)$standing['standingID'];
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

		$sql = "UPDATE eventStandings AS eS
				SET eS.rank = {$placing}
				WHERE standingID = {$standingID}";

		mysqlQuery($sql, SEND);

	}

}

/******************************************************************************/

function pool_RankFightersByPool($rankedPools, $rankedList,
								 $tournamentID, $groupSet,
								 $useTeams, $orderBy){

	$tournamentID = (int)$tournamentID;
	$groupSet = (int)$groupSet;
	$useTeams = (int)$useTeams;

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

	$tournamentID = (int)$tournamentID;
	$setNumber = (int)$setNumber;
	$rosters = getPoolRosters($tournamentID, $setNumber);

	$standingsToKeep = [];

	$ignores = getIgnores($tournamentID,'ignoreAtSet');
	$teamRosters = getTeamRosters($tournamentID);

	foreach($teamRosters as $teamID => $teamRoster){
		if(count($teamRoster) == 0){
			continue;
		}
		$teamID = (int)$teamID;

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
		$standingID = (int)mysqlQuery($sql, SINGLE, 'standingID');

		if($standingID == 0){
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
					'numPenalties','numYellowCards','numRedCards','penaltiesAgainstOpponents',
					'penaltiesAgainst','doubleOuts'];
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
