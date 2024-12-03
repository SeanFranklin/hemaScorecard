<?php
/*******************************************************************************
	Data Handling Functions

	Functions for HEMA Scorecard which deal with data, but do not
	access the database directly or display any information to a page

*******************************************************************************/

/******************************************************************************/

function setAlert($type,$message){

	switch($type){
		case USER_ERROR:
			$class = 'userErrors';
			break;
		case USER_WARNING:
			$class = 'userWarnings';
			break;
		case USER_ALERT:
			$class = 'userAlerts';
			break;
		case SYSTEM:
		default:
			$class = 'systemErrors';
			break;
	}

	$_SESSION['alertMessages'][$class][] = $message;
}

/******************************************************************************/

define("DEBUG",false);

function whittlePoolMatchOrder($groupID, $poolRoster, $matchOrder, $maxFights){
/*
$poolRoster format
$poolRoster[#spotNum]['rosterID'] = ???

Return format
$matchList[#matchNum]['fighter1ID'] = ???
$matchList[$matchNum]['fighter2ID'] = ???
*/
	$groupID = (int)$groupID;
	$fightersUnAssigned = 0;
	$newIndex = 1;
	$newMatchOrder = [];

	foreach($poolRoster as $fighter){
		$fightersUnAssigned++;
		$numMatches[$fighter['rosterID']] = 0;
	}


	$sql = "SELECT matchID, fighter1ID, fighter2ID, ignoreMatch
			FROM eventMatches AS eM
			WHERE groupID = {$groupID}
			AND (	SELECT COUNT(*)
					FROM eventExchanges AS eE
					WHERE eE.matchID = eM.matchID
				) != 0";
	$matchesStarted = mysqlQuery($sql, ASSOC);

	foreach($matchesStarted as $match){
		$f1ID = $match['fighter1ID'];
		$f2ID = $match['fighter2ID'];

		$tmp['fighter1ID'] = $f1ID;
		$tmp['fighter2ID'] = $f2ID;
		$newMatchOrder[$newIndex] = $tmp;

		if($match['ignoreMatch'] != 1){
			$numMatches[$f1ID]++;
			$numMatches[$f2ID]++;

			if($numMatches[$f1ID] == $maxFights){
				$fightersUnAssigned--;
			}
			if($numMatches[$f2ID] == $maxFights){
				$fightersUnAssigned--;
			}
		}

		$haveAlreadyFought[$f1ID][$f2ID] = true;
		$haveAlreadyFought[$f2ID][$f1ID] = true;

		$newIndex++;
	}




	$matchesInList = count($matchOrder);
	$indexOffset = floor($matchesInList/2); // Don't start at match 1 to make it seem more random.


	if(DEBUG){ echo "Max Fights: {$maxFights}<BR><table>"; }

	for($i = 1; $i<=$matchesInList; $i++){

		$matchIndex = $i + $indexOffset;
		if($matchIndex > $matchesInList){
			$matchIndex = $matchIndex - $matchesInList;
		}
		$match = $matchOrder[$matchIndex];

		$f1ID = $match['fighter1ID'];
		$f2ID = $match['fighter2ID'];

		if(DEBUG){
			echo "<tr><td>Match {$matchIndex}</td>";
			echo "<td><em>{$fightersUnAssigned}</em></td>";
			echo "<td>".getFighterName($f1ID)."({$numMatches[$f1ID]})</td>";
			echo "<td>".getFighterName($f2ID)."({$numMatches[$f2ID]})</td>";
		}

		if(   isset($haveAlreadyFought[$f1ID][$f2ID]) == true
		   || isset($haveAlreadyFought[$f1ID][$f2ID]) == true){

			if(DEBUG){ echo "<td></td><td>duplicate</td>"; }
			continue;
		}


		if(($numMatches[$f1ID] < $maxFights && $numMatches[$f2ID] < $maxFights)
			|| ($fightersUnAssigned == 1
				 && (   $numMatches[$f1ID] < $maxFights
					 || $numMatches[$f2ID] < $maxFights
					)
				)
			){

			$numMatches[$f1ID]++;
			$numMatches[$f2ID]++;

			if($numMatches[$f1ID] == $maxFights){
				$fightersUnAssigned--;
			}
			if($numMatches[$f2ID] == $maxFights){
				$fightersUnAssigned--;
			}

			if(DEBUG){ echo "<td>added</td>"; }

			$newMatchOrder[$newIndex] = $match;
			$newIndex++;

			if($fightersUnAssigned == 0){
				break;
			}

		}

		if(DEBUG){ echo "</tr>"; }
	}

	if(DEBUG){ echo "</table>"; }



	return $newMatchOrder;

}

/******************************************************************************/

function getRandomOpponent($groupSize, $positionsTaken){


	$avaliableSpots = $groupSize - sizeof($positionsTaken);
	if($avaliableSpots == 0){
		return null;
	}

	$findingOpponent = true;
	$opponentNum = mt_rand(1,$groupSize);
	do{
		if(!isset($positionsTake[$opponentNum])){
			return $opponentNum;
		}
		$opponentNum++;
		if($opponentNum > $groupSize){
			$opponentNum = 0;
		}
	} while($findingOpponent == true);
}

/******************************************************************************/

function makePoolMatchOrderTeamVsTeam ($groupRoster, $ignores = [], $groupSet = 1){

	$numTeams = count($groupRoster);

	$teamsSet = [];
	$matchOrder = [];
	$matchNumber = 1;

	// Step through sequentialy and do everyone vs everyone
	foreach($groupRoster as $team1ID => $team1Roster){
		$teamsSet[$team1ID] = true;

		foreach($groupRoster as $team2ID => $team2Roster){
			if(isset($teamsSet[$team2ID])){
				continue;
			}

			$team1RosterIndexed = [];
			$team1size = 0;
			foreach($team1Roster as $rosterID){
				$team1RosterIndexed[] = $rosterID;
				$team1size++;
			}


			$team2RosterIndexed = [];
			$team2size = 0;
			foreach($team2Roster as $rosterID){
				$team2RosterIndexed[] = $rosterID;
				$team2size++;
			}

			$maxTeamSize = max($team1size, $team2size);

			for($loopNum = 0; $loopNum < $maxTeamSize; $loopNum++){

				for($i = 0; $i < $team1size; $i++){

					$rosterID1 = $team1RosterIndexed[$i];

					// Skip if fighter 1 can't continue
					if( @(int)$ignores[$rosterID1]['stopAtSet'] < (int)$groupSet    // If unset treat it as zero
						&& @(int)$ignores[$rosterID1]['stopAtSet'] > 0){
						continue;
					}

					$index2 = $i + $loopNum;

					if($index2 >= $maxTeamSize){
						$index2 -= $maxTeamSize;
					}

					if($index2 >= $team2size){
						continue;
					}

					$rosterID2 = $team2RosterIndexed[$index2];

					// Skip if fighter 2 can't continue
					if( @(int)$ignores[$rosterID2]['stopAtSet'] < (int)$groupSet    // If unset treat it as zero
						&& @(int)$ignores[$rosterID2]['stopAtSet'] > 0){
						continue;
					}

					$matchOrder[$matchNumber]['fighter1ID'] = $rosterID1;
					$matchOrder[$matchNumber]['fighter2ID'] = $rosterID2;
					$matchNumber++;

				}

			}

		}
	}

	return $matchOrder;

}

/******************************************************************************/

function generate_RefightPoints($poolRefights,$numberOfFightsTogether,$fighterID){

	$SCALING_FACTOR = -0.25;
	$sumOfRefights = 0;

	foreach($poolRefights as $poolNum => $existingRefights){
		$totalRefights = $existingRefights;
		foreach((array)@$_SESSION['poolSeeds'][$poolNum] as $rosterID){
		// This could not exist, in this case there is no need to loop through
			$totalRefights += $numberOfFightsTogether[$fighterID][$rosterID];
		}
		$potentialRefights[$poolNum] = $totalRefights * $SCALING_FACTOR;
		$sumOfRefights += $potentialRefights[$poolNum];
	}


	if($sumOfRefights != 0){
		foreach($potentialRefights as $poolNum => $numRefights){
			$potentialRefights[$poolNum] = -$numRefights / $sumOfRefights;
		}
	}

	return $potentialRefights;

}

/******************************************************************************/

function generate_SameSchoolPoints($poolSchools,$schoolID){
// Takes away 0.5 points for each fighter of the same school already in the pool



	$numAlreadyIn = [];
	$sumAlreadyIn = 0;
	foreach($poolSchools as $poolNum => $schools){
		$numAlreadyIn[$poolNum] = @$poolSchools[$poolNum][$schoolID]; // Could be unset, treat as zero
		$sumAlreadyIn += $numAlreadyIn[$poolNum];
	}

	if($sumAlreadyIn != 0){
		foreach($numAlreadyIn as $poolNum => $num){
			$numAlreadyIn[$poolNum] = -$num /$sumAlreadyIn;
		}
	}

	return $numAlreadyIn;

}

/******************************************************************************/

function generate_PoolRatingPoints($poolRatings,$fighterRating){

	$highestRating = max($poolRatings);
	$ratingSum = 0;
	foreach($poolRatings as $index => $rating){
		$poolRatings[$index] = $highestRating - $rating;
		$ratingSum += $poolRatings[$index];
	}

	if($ratingSum != 0){
		foreach($poolRatings as $index => $rating){
			$poolRatings[$index] = $poolRatings[$index]/$ratingSum;
		}
	}

	return $poolRatings;

}

/******************************************************************************/

function generate_PoolSizePoints($numInPools,$maxPoolSize){

	$maxSpotsLeft = 0;
	$minSpotsLeft = (int)$maxPoolSize;
	$totalSpotsLeft = 0;
	$spotsLeft = [];
	foreach($numInPools as $poolNum => $numInPool){
		$spotsLeft[$poolNum] = $maxPoolSize - $numInPool;
		if($spotsLeft[$poolNum] > $maxSpotsLeft){
			$maxSpotsLeft = $spotsLeft[$poolNum];
		}
		if($spotsLeft[$poolNum] < $minSpotsLeft){
			$minSpotsLeft = $spotsLeft[$poolNum];
		}
		$totalSpotsLeft += $spotsLeft[$poolNum];
	}

	foreach($spotsLeft as $poolNum=> $numSpots)
	{
		if($numSpots == 0){
			$weightFunction[$poolNum] = -99; // Super low number ensures no one is put in this pool
		} else {
			$weightFunction[$poolNum] = $numSpots / $totalSpotsLeft;
		}
	}

	return($weightFunction);

}



/******************************************************************************/

function convertExchangeIntoText($exchangeInfo, $fighter1ID){

	if($exchangeInfo['rosterID'] == $fighter1ID){
		$fighter1 = true;
	} else {
		$fighter1 = false;
	}

	$appendName = false;
	switch($exchangeInfo['exchangeType']){
		case 'double':
			$text = 'Double Hit';
			break;
		case 'noExchange':
			$text = 'No Exchange';
			break;
		case 'noQuality':
			$text = 'No Quality';
			$appendName = true;
			break;
		case 'afterblow':
			$text = "Afterblow: {$exchangeInfo['scoreValue']} - {$exchangeInfo['scoreDeduction']}";
			$appendName = true;
			break;
		case 'clean':
			$text = "Clean Hit: {$exchangeInfo['scoreValue']}";
			$appendName = true;
			break;
		case 'penalty':
			$text = "Penalty: {$exchangeInfo['scoreValue']}";
			$appendName = true;
			break;
		default:
			$text = '';
			break;

	}

	if($appendName == true){
		if($exchangeInfo['rosterID'] == $fighter1ID){
			$color = COLOR_NAME_1;
		} else {
			$color = COLOR_NAME_2;
		}
		$text .= " for {$color}";

	}

	return $text;
}

/******************************************************************************/

function uploadCsvFile($fileName){
// Uploads a csv file to the user
// The file is deleted after upload.

// Provide the file to the user
	if($fileName == ''){
		$_SESSION['alertMessages']['systemErrors'][] =  'Invalid fileName in uploadCsvFile()';
		return;

	} else {

		// Upload the file to user
		header('Content-type: application/csv');
		header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
		header('Content-Transfer-Encoding: binary');
		readfile($fileName);

		// Delete the file from the server
		ignore_user_abort(true);
		if (connection_aborted()) {
			unlink($fileName);
		}

		unlink($fileName);

		exit;

	}

}

/******************************************************************************/

function uploadZipFile($filesToZip){

	$zip = new ZipArchive();
	$zipName = EXPORT_DIR."export.zip";

	//create the file and throw the error if unsuccessful
	if ($zip->open($zipName, ZipArchive::CREATE )!==TRUE) {
		exit("cannot open export\n");
	}


	//add each files of $file_name array to archive
	foreach($filesToZip as $files)
	{
		$zip->addFile(EXPORT_DIR.$files);
	}
	$zip->close();

	if (file_exists($zipName)) {
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="'.basename($zipName).'"');
		header('Content-Length: ' . filesize($zipName));

		flush();
		readfile($zipName);
		// delete file
		unlink($zipName);

   }

}

/******************************************************************************/

function base60($num, $leadingZero = false){
// Returns the number in base 60 format, divided by a colon.
// Would make sec -> min:sec or min -> hour:min

	$a = floor($num/60);
	$b = $num % 60;

	if($leadingZero == true && $a < 10){
		$a = "0".$a;
	}

	if($b < 10){
		$b = "0".$b;
	}

	$time = $a.':'.$b;

	return ($time);
}

/******************************************************************************/

function min2hr($num, $AmPm = true){
// Returns the number in base 60 format, divided by a colon.
// Would make sec -> min:sec or min -> hour:min

	$a = floor($num/60);
	$b = $num % 60;

	if($b < 10){
		$b = "0".$b;
	}

	if($_SESSION['viewMode']['time24hr'] == true){
		$type = "";
	} else {
		if($a == 0 || $a == 24){
			$a = 12;
			$type = 'am';
		} elseif($a < 12){
			$type = 'am';
		} elseif($a == 12){
			$type = 'pm';
		} else {
			$a = $a - 12;
			$type = 'pm';
		}
	}

	$retVal = ($a.':'.$b);
	if($AmPm == true){
		$retVal .= ' '.$type;
	}

	return $retVal;
}

/******************************************************************************/

function autoRefreshTime($poolsInProgress){
// Specifies the time interval that a page should be refreshed used for the match
// list pages. Returns 0 if no matches are in progress of if staff are logged in.

	if($poolsInProgress != true){
		return 0;
	}
	if(ALLOW['EVENT_SCOREKEEP'] == true){
		return 0;
	}

	return 60;

}

/******************************************************************************/

function getPrimaryBracketAdvancements($allBracketInfo, $finalists, $isTrueDoubleElim = false){
// Determines which fighters should be advanced into which spots in the bracket

	if($allBracketInfo == null || $_SESSION['bracketHelper'] != 'on'){
		return null;
	}

	$bracketID = $allBracketInfo[BRACKET_PRIMARY]['groupID'];
	$bracketLevels = $allBracketInfo[BRACKET_PRIMARY]['bracketLevels'];

	$bracketMatches = getBracketMatchesByPosition($bracketID);

	$fighterSeed = $numFighters = $allBracketInfo[BRACKET_PRIMARY]['numFighters'];

	$maxFightersAtLevel = pow(2,$bracketLevels);
	$numFightersAtLevel = 2*count($bracketMatches[$bracketLevels]);
	$currentLevel = $bracketLevels;

	// Fighter positions for winners bracket based on pool seeding
	$fightersCounted = 0;
	for($fighterSeed;$fighterSeed >= 1; $fighterSeed--){

		$fightersCounted++;
		if($fightersCounted > $numFightersAtLevel){
			$currentLevel--;
			$maxFightersAtLevel /= 2;
			$numFightersAtLevel = 99;
		}

		$position = getBracketPositionByRank($fighterSeed,$maxFightersAtLevel);
		$matchNumber = ceil($position/2);
		$fighterNumber = 2 - ($position % 2);


		$seed = '';
		if(isset($finalists[$fighterSeed-1]['rosterID']))
		{
			$seed = $finalists[$fighterSeed-1]['rosterID'];
		}

		$matchPositions[$currentLevel][$matchNumber][$fighterNumber]['rosterID'] = $seed;
	}

	// Fighter positions for winners bracket based on bracket advancement
	foreach((array)$bracketMatches as $bracketLevel => $levelMatches){
		foreach($levelMatches as $bracketPosition => $matchInfo){

			if($matchInfo['winnerID'] != null){
				$winnerID = $matchInfo['winnerID'];

				if($bracketLevel == 1 && $bracketPosition >= 2){
					if($winnerID != null){
						$matchPositions[1][$bracketPosition+1][1]['rosterID'] = $matchInfo['fighter1ID'];
						$matchPositions[1][$bracketPosition+1][2]['rosterID'] = $matchInfo['fighter2ID'];
					}
				} elseif($bracketLevel > 1){

					$nextLevel = $bracketLevel - 1;
					$nextPosition = ceil($bracketPosition/2);

					if($bracketPosition % 2 == 1){
						$matchPositions[$nextLevel][$nextPosition][1]['rosterID'] = $winnerID;

					} else {
						$matchPositions[$nextLevel][$nextPosition][2]['rosterID'] = $winnerID;

					}
				} elseif(isset($allBracketInfo[BRACKET_SECONDARY]['groupID']) == true) {
					// Used in ELIM_TYPE_TRUE_DOUBLE where there are multiple matches after the
					// end of the bracket.

					// May not be set yet. Treat as null.
					$secondaryMatches = getBracketMatchesByPosition(@$allBracketInfo[BRACKET_SECONDARY]['groupID']);
					$secondaryWinner = @$secondaryMatches[1][$bracketPosition+1]['winnerID'];

					$matchPositions[1][$bracketPosition+1][1]['rosterID'] = $winnerID;
					$matchPositions[1][$bracketPosition+1][2]['rosterID'] = $secondaryWinner;
				}

			}
		}

	}


	// Bronze Medal Match for Single Elim
	if(!isset($allBracketInfo[BRACKET_SECONDARY]) && $numFighters >= 4){ //is single elim
		// The loserID might not be set at the time of the call, in which case assign null
		@$matchPositions[1][2][1]['rosterID'] = $matchPositions[1][1][1]['rosterID'];
		@$matchPositions[1][2][2]['rosterID'] = $matchPositions[1][1][2]['rosterID'];
		@$matchPositions[1][1][1]['rosterID'] = $bracketMatches[2][1]['loserID'];
		@$matchPositions[1][1][2]['rosterID'] = $bracketMatches[2][2]['loserID'];
	}



	return ($matchPositions);

}

/******************************************************************************/

function getSecondaryBracketAdvancements($allBracketInfo, $finalists){
// Determines which fighters should be advanced into which spots in the bracket
//   IMPORTANT:	Behavior is undefined if the consolation bracket size
//				is not a power of 2. ie. top8, top16.

	if(!isset( $allBracketInfo[BRACKET_SECONDARY]) || $allBracketInfo[BRACKET_SECONDARY] == null){
		return [];
	}

	$bracketID = $allBracketInfo[BRACKET_SECONDARY]['groupID'];
	$bracketLevels = $allBracketInfo[BRACKET_SECONDARY]['bracketLevels'];

	$bracketMatches = getBracketMatchesByPosition($bracketID);
	$winnerBracketMatches = getBracketMatchesByPosition($allBracketInfo[BRACKET_PRIMARY]['groupID']);
	$changeOverBracket = false;

	$matchPositions = [];
	$numExtraMatchesAtEnd = 0;

	// Loop through an array of all the matches in the lower bracket by level first, then by match.
	foreach((array)$bracketMatches as $bracketLevel => $levelMatches){

		$matchesAtLevel = getNumEntriesAtLevel_consolation($bracketLevel,'matches');
		$matchesAtPrev = getNumEntriesAtLevel_consolation($bracketLevel + 1,'matches');

		// Size change means we aren't pulling anyone in from the upper bracket this level,
		// and the fights are just from taking prior lower bracket winners and having them fight.
		if($matchesAtLevel != $matchesAtPrev){
			$sizeChange = true;
		} else {
			$sizeChange = false;
		}

		// Loop through all the matches in the current level.
		foreach($levelMatches as $bracketPosition => $matchInfo){


			if($bracketLevel == 1 && $bracketPosition > 1){
				// Special handling for true double elimination brackets, as the last match of the lower
				// bracket is special.
				if((isset($winnerBracketMatches[1][1]['loserID']) == true) && ($winnerBracketMatches[1][1]['loserID'] != 0)){
					$matchPositions[$bracketLevel][$bracketPosition][1]['rosterID'] = $winnerBracketMatches[1][1]['loserID'];
				}

				if((isset($bracketMatches[1][1]['winnerID']) == true) && ($bracketMatches[1][1]['winnerID'] != 0)){
					$matchPositions[$bracketLevel][$bracketPosition][2]['rosterID'] = $bracketMatches[1][1]['winnerID'];
				}

				continue;
			}


			// Determine which of the fighters in this match should come from prior
			// lower bracket matches, and which from the upper bracket.
			if($bracketLevel == $bracketLevels){

				// On the lowest level of the bracket we must always pull from the upper bracket.
				$pullFromPrimary[1] = true;
				$pullFromPrimary[2] = true;

			} else if($sizeChange == false){

				// A match where one fighter comes from the prior lower bracket match,
				// and is against one person coming from the upper bracket.
				$pullFromPrimary[1] = true;

				// Check to see if there is a prior match to pull from in the secondary.
				// If not we have to pull from the primary.
				if(isset($bracketMatches[$bracketLevel+1][$bracketPosition]) == true){
					$pullFromPrimary[2] = false;
				} else {
					$pullFromPrimary[2] = true;
				}

			} else {

				// The match should have two lower bracket fighters who won their previous matches.
				$feederMatchPosition = ($bracketPosition * 2) - 1;

				// If the prior match doesn't exist to pull from, because we are at
				// the bottom of the bracket, then we need to grab from the upper bracket.
				if(isset($bracketMatches[$bracketLevel+1][$feederMatchPosition]) == true){
					$pullFromPrimary[1] = false;
				} else {
					$pullFromPrimary[1] = true;
				}

				if(isset($bracketMatches[$bracketLevel+1][$feederMatchPosition+1]) == true){
					$pullFromPrimary[2] = false;
				} else {
					$pullFromPrimary[2] = true;
				}
			}


			foreach($pullFromPrimary as $fighterNum => $primary){

				if($primary == true){

					$fighterID = getSecondarySeedFromPrimary( $bracketMatches,
															  $winnerBracketMatches,
															  $bracketLevel,
															  $bracketPosition,
															  $fighterNum,
															  $sizeChange);

					if($fighterID != 0){
						$matchPositions[$bracketLevel][$bracketPosition][$fighterNum]['rosterID'] = $fighterID;
					}

				} else {

					$fighterID = getSecondarySeedFromSecondary($bracketMatches,
															    $bracketLevel,
															    $bracketPosition,
															    $fighterNum);

					if($fighterID != 0){
						$matchPositions[$bracketLevel][$bracketPosition][$fighterNum]['rosterID'] = $fighterID;
					}
				}

			}

		}

	}

	return $matchPositions;

}

/******************************************************************************/

function getSecondarySeedFromPrimary($bracketMatches, $winnerBracketMatches, $bracketLevel, $bracketPosition, $fighterNum, $sizeChange){

	$bracketLevelSpots = getNumEntriesAtLevel_consolation($bracketLevel,'matches');
	$bracketLevelSize = sizeof($bracketMatches[$bracketLevel]);
	$numBracketLevels = sizeof($bracketMatches);

	// Every second time we have a mixed bracket level (one person from lower, one from upper)
	// we change the order we pull from the upper bracket to avoid refights.
	$lastCrossOverLevel = 0;
	for($level = $numBracketLevels; $level >= $bracketLevel; $level--){

		if(($level % 2) != 0){
			continue; // only even levels can be crossover levels
		}

		$spots = getNumEntriesAtLevel_consolation($level,'matches');
		$size = sizeof($bracketMatches[$level]);

		if($size != $spots){
			continue; // if it is not a full level we have special logic and can't do normal crossover.
		}

		if(($lastCrossOverLevel == 0) || ($lastCrossOverLevel - $level >= 4)){
			$lastCrossOverLevel = $level;
		}

	}


	if($bracketLevel == $lastCrossOverLevel){
		$isCrossOver = true;
	} else {
		$isCrossOver = false;
	}


	if($sizeChange == true){

		// the only reason we would be pulling from the upper bracket on a size change level
		// is if it's an odd size bracket. Special logic.

		$winBracketLevel = (($bracketLevel+1)/2) + 1;
		$winBracketPosition = ($bracketPosition*2);

		if($fighterNum == 1){
			$winBracketPosition--;
		}

	} elseif($fighterNum == 1) {

		// Normal use case.

		$winBracketLevel = floor((($bracketLevel+1)/2) + 1);

		if($isCrossOver == true){
			// Cross-over level. Read from the primary bottom to top.
			$matchesInWinLevel = pow(2, ($winBracketLevel-1));
			$winBracketPosition = $matchesInWinLevel - $bracketPosition + 1;
		} else {
			$winBracketPosition = $bracketPosition;
		}

	} else {

		// This is the position which would normally be advanced from the previous
		// level, but since it is the start of an odd size bracket we need to
		// pull someone from the upper bracket. The order is reversed or else
		// it would end up as a direct rematch of the people who had just fought.
		// Shuffle the match one row down.

		$bracketPosToUse = $bracketPosition;

		// If there is only a single match we can't shuffle down, and cross-over matches eliminate
		// the need for shuffling.
		if($bracketLevelSize != 1 && $isCrossOver == false){

			for($i = 1; $i <= $bracketLevelSpots; $i++){

				$posTest = $i + $bracketPosition;

				if($posTest > $bracketLevelSpots){
					$posTest -= $bracketLevelSpots; // Wrap around back to the start if we go over the end.
				}

				$matchExists = isset($bracketMatches[$bracketLevel][$posTest]);

				if($matchExists == true){
					// If we never get here then bracketPosToUse retains the initialized value of bracketPosition
					$bracketPosToUse = $posTest;
					break;
				}

			}

			$bracketPosition = $bracketPosToUse;

		}

		$winBracketLevel = floor((($bracketLevel+1)/2) + 1) + 1;

		$winBracketPosition = (2 * $bracketPosition) + ($bracketPosition % 2) - 1;

	}

	// If it doesn't exist then it will return zero.
	$fighterID = (int)@$winnerBracketMatches[$winBracketLevel][$winBracketPosition]['loserID'];

	return ($fighterID);

}

/******************************************************************************/

function getSecondarySeedFromSecondary($bracketMatches, $bracketLevel, $bracketPosition, $fighterNum){

	$prevBracketLevel = $bracketLevel+1;

	// Look for the next match down if we are filling the spot for fighter #2.
	if($bracketLevel % 2 == 0){
		$previousPosition = $bracketPosition;
	} else {
		$previousPosition = ($bracketPosition*2) - 1;
		$previousPosition += ($fighterNum - 1);
	}

	// If it doesn't exist then it will return zero.
	$fighterID =	(int)@$bracketMatches[$prevBracketLevel][$previousPosition]['winnerID'];

	return ($fighterID);

}


/******************************************************************************/

function checkPassword($input, $userName, $eventID = null){
// Checks if the password provided matches the password in the database
// Returns true if the passwords match, false if they don't

// Get password to compare
	switch($userName){
		case 'eventStaff':
			if($eventID === null){
				return true;
			}
			$password = getEventStaffPassword($eventID);
			break;
		case 'eventOrganizer':
			if($eventID === null){
				return true;
			}
			$password = getEventOrganizerPassword($eventID);
			break;
		default:

			$sql = "SELECT password
					FROM systemUsers
					WHERE userName = ?";

			$stmt = $GLOBALS["___mysqli_ston"]->prepare($sql);
			$stmt->bind_param("s",$userName);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($password);
			$stmt->fetch();

			if($stmt->num_rows == 0){
				return false;
			}

			if($password === null){
				return true;
			}
			break;
	}

// Compare password
	if($password == null){
		return true;
	}

	return password_verify($input, $password);

}

/******************************************************************************/

function getBracketDepthByFighterCount($numFighters, $bracketType){
// return the how many brackets levels there should be based on the number of
// fighters in the bracket. Winner and Loser brackets have different geometry.

	if($bracketType == 'winner'){$bracketType = 1;}
	if($bracketType == 'loser'){$bracketType = 2;}

	if($bracketType == 1){
		return ceil(log($numFighters,2));
	} else if($bracketType == 2){
		$bracketLevel = 1;
		$fighterCount = 2;
		while($fighterCount < $numFighters){
			$bracketLevel++;
			$fighterCount += pow(2,floor($bracketLevel/2));
		}
		return($bracketLevel);
	}
}

/******************************************************************************/

function getEventStats($stats){
// Returns a breakdown of stats for each tournament (doubles, clean hits, etc..)
// Also returns a total list for the event

	if($stats == null){ return; }

	$stats['overall']['clean'] = 0;
	$stats['overall']['double'] = 0;
	$stats['overall']['afterblow'] = 0;
	$stats['overall']['noExchange'] = 0;
	$stats['overall']['noQuality']= 0;
	$stats['overall']['all'] = 0;

	foreach($stats as $tournamentID => $data){
		$stats['overall']['clean'] += $data['clean'];
		$stats['overall']['double'] += $data['double'];
		$stats['overall']['afterblow'] += $data['afterblow'];
		$stats['overall']['noExchange'] += $data['noExchange'];
		$stats['overall']['noQuality'] += $data['noQuality'];

		$stats[$tournamentID]['total'] = $data['clean'] +
			$data['double'] + $data['afterblow'] + $data['noExchange'] + $data['noQuality'];
		$stats['overall']['all'] += $stats[$tournamentID]['total'];
		$stats[$tournamentID]['tournamentID'] = $tournamentID;

		$bilaterals = $data['double'] + $data['afterblow'];
		if($bilaterals + $data['clean'] != 0){
			$stats[$tournamentID]['BpE'] =
				round($bilaterals/
				($bilaterals + $data['clean']),2)*100;
		} else {
			$stats[$tournamentID]['BpE'] = NULL;
		}
	}

	// Sort by total number of exchanges
	foreach($stats as $index => $data){
		if($data['total'] == 0 && $index != 'overall'){
			unset($stats[$index]);
			continue;
		}
		$key[] = $data['total'];
	}

	if($stats != null){ array_multisort($key, SORT_DESC, $stats); }

	return $stats;

}

/******************************************************************************/

function getNumEntriesAtLevel_consolation($bracketLevel,$mode){
// Returns either matches or number of fighters for a given consolation bracket level
// matches mode: 	the number of matches at the brackets level
// default mode: 	the number of fighters which the bracket can accomodate
//					this is just the number of fighters in the consolation bracket
//					and does not include the 2 who remain in the winners bracket

	if($mode == 'matches' || $mode == 'match'){
		return (int)pow(2,floor($bracketLevel/2));
	} else {
		if($bracketLevel % 2 == 0){ //even
			$maxLowestLevelFighters = pow(2,($bracketLevel/2)+2)-2-pow(2,$bracketLevel/2);
		} else { // odd
			$maxLowestLevelFighters = pow(2,(($bracketLevel+1)/2)+1)-2;
		}
		return $maxLowestLevelFighters;
	}
}

/******************************************************************************/

function getBracketPositionByRank($rank, $numPositions){
// returns where in the winners bracket a fighter should be seeded based on
// their rank and the total number of fighters being entered in the bracket.
// the return position is the position measured down from the top of the bracket

	if($rank == 1){return 1;}
	if($rank > $numPositions){return;}

	$jumpSize = $numPositions - 1 ;
	$currentRank = 1;
	$newPosition = 1;
	$ranksCounted[1] = 1;

	while($currentRank<=$numPositions){
		for($j=$currentRank;$j>0;$j--){

			$currentRank++;
			$oldPosition = $ranksCounted[$j];
			if($j % 2 ==0){
				$jump = -$jumpSize;
			} else {
				$jump = $jumpSize;
			}

			$newPosition = $oldPosition + $jump;
			$ranksCounted[$currentRank] = $newPosition;

			if($currentRank == $rank){
				return $newPosition;
			}
		}
		$jumpSize = (($jumpSize +1) / 2) -1;

	}

	// Error message
	$_SESSION['alertMessages']['systemErrors'][] = "getBracketPositionByRank() - Could Not Find Bracket Position For Values: <BR>
	Rank: {$rank}, Bracket Level: {$bracketLevels}<BR>";
	return;

}

/******************************************************************************/

function updatePoolStandings($tournamentID, $groupSet = 1){
// Calls the functions in poolScoring.php required to update the pool standings
// If called with groupSet == 0 it does them all
// If called with a groupSet number it does that set and all the ones after

	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	$tournamentID = (int)$tournamentID;
	if($tournamentID == 0){return;}

	// Check to catch non-pool events
	$formatID = getTournamentFormat($tournamentID);
	if($formatID != FORMAT_MATCH){
		return;
	}

	$setNumber = $groupSet;
	$numberOfGroupSets = getNumGroupSets($tournamentID);
	if($setNumber < 1){
		$setNumber = 1;
	}

	if(isTeams($tournamentID) == false){
		$entriesAreTeams = false;
		$calculateTeamScores = false;
	} elseif(isMatchesByTeam($tournamentID) == true){
		$entriesAreTeams = true;
		$calculateTeamScores = false;
	} else {
		$entriesAreTeams = false;
		$calculateTeamScores = true;
	}

	for(; $setNumber <= $numberOfGroupSets; $setNumber++){

		$fighterStats = getAllTournamentExchanges($tournamentID, 'pool', $setNumber);
		$fighterStats = pool_NormalizeSizes($fighterStats, $tournamentID, $setNumber);

		recordScores($fighterStats, $tournamentID, $setNumber);
		unset($fighterStats);

		pool_ScoreFighters($tournamentID, $setNumber);

		pool_RankFighters($tournamentID, $setNumber, $entriesAreTeams);

		if($calculateTeamScores == true){
			pool_CalculateTeamScores($tournamentID, $setNumber);
			pool_RankFighters($tournamentID, $setNumber, true);

		}

	}

	unset($_SESSION['updatePoolStandings'][$tournamentID]);

}

/******************************************************************************/

function updateMetaTournamentStandings($mTournamentID){

	meta_ScoreFighters($mTournamentID);
	pool_RankFighters($mTournamentID); // Once fighters have been scored they are ranked just the same as they would be in a regular pool.

}


/******************************************************************************/

function groupList($list, $key){
// accepts an array of arrays, $list, where each item in $list contains an element
// with indexed by $key. Return an array indexed by the values in $key.

	foreach ($list as $entry){
		$index = $entry[$key];
		unset($entry[$key]);
		$orderedList[$index] = $entry;
	}

	return $orderedList;
}

/******************************************************************************/

function implode2int($array){
// Creates a comma separated string of all array values, and typecasts all values
// to an integer type (to protect against SQL injection).
// Returns 0 if the array is empty.

	if($array == null){
		return 0;
	}

	foreach($array as $key => $item){
		$array[$key] = (int)$item;
	}


	return (implode(",",$array));
}

/******************************************************************************/

function shouldMatchConcludeByPoints($matchInfo){
// Returns true if the software should auto-conclude a match because it has reached
// the maximum number of scoring exchanges.

	$maximumPoints = (int)$matchInfo['maximumPoints'];

	if(    $matchInfo['matchComplete'] == 1
		|| $matchInfo['ignoreMatch'] == 1
	){
		return false;
	}

	$shouldConclude = false;

	$reverseScore = isReverseScore($matchInfo['tournamentID']);

	switch($reverseScore){
		case REVERSE_SCORE_INJURY:

			if(    ($matchInfo['lastExchange'] != 0)
				&& (   $matchInfo['fighter1score'] <= 0
					|| $matchInfo['fighter2score'] <= 0)
			  ){

				$shouldConclude = true;
			}
			break;

		case REVERSE_SCORE_NO:
		case REVERSE_SCORE_GOLF:
		default:

			if(    ($maximumPoints != 0)
				&& (   $matchInfo['fighter1score'] >= $maximumPoints
					|| $matchInfo['fighter2score'] >= $maximumPoints)
			  ){

				$shouldConclude = true;
			}
			break;
	}

	if($shouldConclude == true){
		setAlert(USER_ALERT,"Point cap reached.");
	}

	return $shouldConclude;

}

/******************************************************************************/

function shouldMatchConcludeBySpread($matchInfo){
// Returns true if the software should auto-conclude a match because it has reached
// the maximum number of scoring exchanges.

	$maxSpread = (int)$matchInfo['maxPointSpread'];

	if($matchInfo['matchComplete'] == 1
		|| $matchInfo['ignoreMatch'] == 1
		|| $maxSpread == 0
	){
		return false;
	}

	$spread = abs($matchInfo['fighter1score'] - $matchInfo['fighter2score']);

	$shouldConclude = false;

	if($spread >= $maxSpread){
		$shouldConclude = true;
		setAlert(USER_ALERT,"Point spread reached.");
	}

	return $shouldConclude;

}

/******************************************************************************/

function shouldMatchConcludeByTime($matchInfo){
// Returns true if the software should auto-conclude a match because it has reached
// the maximum number of scoring exchanges.

	$timeLimit = (int)$matchInfo['timeLimit'];

	if(    $matchInfo['matchComplete'] == 1
		|| $matchInfo['ignoreMatch'] == 1
		|| $matchInfo['timeLimit'] == 0)
	{
		return false;
	}

	$softClock = (int)readOption('T', $matchInfo['tournamentID'], 'MATCH_SOFT_CLOCK_TIME');


	if($matchInfo['matchTime'] >= $matchInfo['timeLimit']){

		setAlert(USER_ALERT,"Time limit reached.");
		return true;

	} else if($softClock != 0 && $matchInfo['matchTime'] >= $softClock){

		$matchID = (int)$matchInfo['matchID'];

		$sql = "SELECT scoringID, exchangeType
				FROM eventExchanges
				WHERE matchID = {$matchID}
				ORDER BY exchangeNumber DESC
				LIMIT 1";
		$exch = (array)mysqlQuery($sql, SINGLE);

		if($exch == []){
			return (false);
		}

		$alertString = "Soft time limit reached.";


		if($matchInfo['fighter1score'] > $matchInfo['fighter2score']){
			$winning = 1;
		} else if ($matchInfo['fighter2score'] > $matchInfo['fighter1score']) {
			$winning = 2;
		} else {
			$winning = 0;
		}

		$validExchange = false;
		if($exch['exchangeType'] == 'clean' || $exch['exchangeType'] == 'afterblow'){
			$validExchange = true;
		}


		if($winning == 0){

			$shouldConclude = false;
			$alertString .= "<BR>Tie match. One fighter must score to win.";

		} else if($validExchange == true && $exch['scoringID'] == $matchInfo["fighter{$winning}ID"]) {

			$shouldConclude = true;

		} else {

			$shouldConclude = false;
			$alertString .= "<BR><b>Fighter {$winning}</b> must score to end the match.";

		}

		setAlert(USER_ALERT, $alertString);

		return ($shouldConclude);

	} else {
		return false;
	}
}

/******************************************************************************/

function shouldMatchConcludeByExchanges($matchInfo){
// Returns true if the software should auto-conclude a match because it has reached
// the maximum number of scoring exchanges.

	$maximumExchanges = (int)$matchInfo['maximumExchanges'];

	if(		$matchInfo['matchComplete'] == 1
		 || $matchInfo['ignoreMatch'] == 1
		 || $maximumExchanges == 0){
		return false;
	}

	$numExchanges = getNumMatchScoringExchanges($matchInfo['matchID']);

	if($numExchanges >= $maximumExchanges){
		setAlert(USER_ALERT,"Exchange count reached.");
		return true;
	} else {
		return false;
	}
}

/******************************************************************************/

function shouldMatchConcludeByDoubles($matchInfo){
// Returns true if the software should auto-conclude a match because it has reached
// the maximum number of double hits.

	$maximumDoubles = (int)$matchInfo['maxDoubles'];

	if(	   $matchInfo['matchType'] != 'pool'
		|| $maximumDoubles == 0){
		return false;
	}

	$numDoubles = getMatchDoubles($matchInfo['matchID']);

	if($numDoubles >= $maximumDoubles){
		setAlert(USER_ALERT,"Double hit limit count reached.");
		return true;
	} else {
		return false;
	}
}

/******************************************************************************/

function autoConcludeMatch($matchInfo){


	if(isReverseScore($matchInfo['tournamentID']) == REVERSE_SCORE_GOLF){
		$reversedResult = true;
	} else {
		$reversedResult = false;
	}

	$_POST['matchID'] = $matchInfo['matchID'];
	$_POST['lastExchangeID'] = $matchInfo['lastExchange'];

	if(    $matchInfo['maxDoubles'] != 0
		&& getMatchDoubles($matchInfo['matchID']) >= $matchInfo['maxDoubles']
	    && $matchInfo['matchType'] == 'pool'){

		$_POST['matchWinnerID'] = 'doubleOut';

	} elseif($matchInfo['fighter1score'] == $matchInfo['fighter2score']){

		if(readOption('T',$matchInfo['tournamentID'],'MATCH_TIE_MODE') == MATCH_TIE_MODE_NONE){
			setAlert(USER_ERROR,"Tie match, can't conclude.");
			return;
		}
		$_POST['matchWinnerID'] = 'tie';

	} elseif($matchInfo['fighter1score'] > $matchInfo['fighter2score']){

		if($reversedResult == false){
			$_POST['matchWinnerID'] = $matchInfo['fighter1ID'];
		} else {
			$_POST['matchWinnerID'] = $matchInfo['fighter2ID'];
		}

	} elseif($matchInfo['fighter2score'] > $matchInfo['fighter1score']){

		if($reversedResult == false){
			$_POST['matchWinnerID'] = $matchInfo['fighter2ID'];
		} else {
			$_POST['matchWinnerID'] = $matchInfo['fighter1ID'];
		}

	} else {

		setAlert(USER_ERROR,"Unable to determine how to conclude match");
		return;

	}

	addMatchWinner();
}

/******************************************************************************/

function createMatchScoresheet($matchInfo)
{

	$eventID = (int)getTournamentEventID($matchInfo['tournamentID']);
	$eventName = getEventName($eventID);

	$tournamentName = getTournamentName($matchInfo['tournamentID']);
	$groupName = getGroupName($matchInfo['groupID']);

	if($groupName == 'Bracket'){
		$groupInfo = "{$matchInfo['groupName']} bracket (#{$matchInfo['groupID']})";
		$groupInfo .= "\nLevel: ".$matchInfo['bracketLevel'].", Position: ".$matchInfo['bracketPosition'];
	} else {
		$groupInfo = "{$matchInfo['groupName']} (#{$matchInfo['groupID']})";
		$groupInfo .= ", Set ".getGroupSetOfMatch($matchInfo['matchID']);
	}

	$groupInfo .= "\n";

	$colors = getTournamentColors($matchInfo['tournamentID']);

	$id2color[$matchInfo['fighter1ID']] = $colors[1];
	$id2color[$matchInfo['fighter2ID']] = $colors[2];

	$scoresheet = "(#".$matchInfo['matchID'].")\n";
	$scoresheet .= "{$eventName} (#{$eventID})\n";
	$scoresheet .= "{$tournamentName} (#{$matchInfo['tournamentID']})\n";
	$scoresheet .= $groupInfo;
	$scoresheet .= $colors[1].": ".getFighterName($matchInfo['fighter1ID'])." [".$matchInfo['fighter1score']."]\n";
	$scoresheet .= $colors[2].": ".getFighterName($matchInfo['fighter2ID'])." [".$matchInfo['fighter2score']."]\n";

	$exchanges = getMatchExchanges($matchInfo['matchID']);


	foreach($exchanges as $e){
		$scoresheet .= "\n".$e['exchangeTime']." ".$id2color[$e['rosterID']]." ". $e['exchangeType'];
		$scoresheet .= " [".$e['scoreValue']."|".$e['scoreDeduction']."]";
		if((int)$e['refPrefix'] != 0)
		{
			$scoresheet .= ", ".GetAttackName($e['refPrefix']);
		}
		if((int)$e['refTarget'] != 0)
		{
			$scoresheet .= ", ".GetAttackName($e['refTarget']);
		}
		if((int)$e['refType'] != 0)
		{
			$scoresheet .= ", ".GetAttackName($e['refType']);
		}
	}


	return ($scoresheet);
}

/******************************************************************************/

function countNumDataSeries(){

	$numDataSeries = 0;	if(isset($_SESSION['activeStatsItems']['tournamentIDs'][0]) == false || (int)$_SESSION['activeStatsItems']['tournamentIDs'][0] == 0){
		$_SESSION['activeStatsItems']['tournamentIDs'][0] = (int)$_SESSION['tournamentID'];
		if($_SESSION['activeStatsItems']['tournamentIDs'][0] != 0){
			$numDataSeries++;
		}

	} else {
		$numDataSeries++;
	}

	for($i = 1; $i < DATA_SERIES_MAX; $i++){
		if(isset($_SESSION['activeStatsItems']['tournamentIDs'][$i]) == false){

			$_SESSION['activeStatsItems']['tournamentIDs'][$i] = 0;

		} else if($_SESSION['activeStatsItems']['tournamentIDs'][$i] != 0){

			$numDataSeries++;
		}
	}

	return $numDataSeries;
}

/******************************************************************************/

function selectDataSeriesTournaments(){

	echo "<form method='POST'>";

	$eventList = getEventList('matchesVisible',0,0,"eventName ASC, eventStartDate DESC");

	for($i = 0; $i < DATA_SERIES_MAX; $i++):

		if(isset($_SESSION['activeStatsItems']['tournamentIDs'][$i]) == true){
			$activeTournamentID = (int)$_SESSION['activeStatsItems']['tournamentIDs'][$i];
		} else {
			$activeTournamentID = 0;
		}

		$activeEventID = (int)getTournamentEventID($activeTournamentID);
		$tournamentIDs = (array)getEventTournaments($activeEventID);

?>

		<div>
			<div class='cell input-group'>
				<span class='input-group-label'>Event</span>

				<select class='input-group-field' onchange="matchLengthEventSelect(<?=$i?>)"
					id="match-length-event-<?=$i?>">
					<option value='0' selected></option>
					<?php foreach($eventList as $eventID => $event):?>
						<option <?=optionValue($eventID, $activeEventID)?>>
							<?=$event['eventName']?> <?=$event['eventYear']?>
						</option>
					<?php endforeach ?>
				</select>

				<span class='input-group-label'>Tournament <?=($i+1)?></span>

				<select class='input-group-field' name="activeStatsItems[tournamentIDs][<?=$i?>]"
					id="match-length-tournament-<?=$i?>">
					<option value='0'></option>
					<?php foreach($tournamentIDs as $tournamentID):?>
						<option <?=optionValue($tournamentID, $activeTournamentID)?>>
							<?=getTournamentName($tournamentID)?>
						</option>
					<?php endforeach ?>

				</select>
			</div>
		</div>
	<?php endfor ?>

	<button class='button' name='formName' value='updateActiveStatsItems'>
		Update
	</button>

	</form>

<?php
}

/******************************************************************************/

function getVideoSourceType($sourceLink){

	if($sourceLink == ''){
		$sourceType = VIDEO_SOURCE_NONE;
	} elseif(   (substr($sourceLink, 0, strlen("https://www.youtube.com")) === "https://www.youtube.com")
			 || ( substr($sourceLink, 0, strlen("https://youtu.be")) === "https://youtu.be"))
	{
		$sourceType = VIDEO_SOURCE_YOUTUBE;
	} elseif(substr($sourceLink, 0, strlen("https://drive/google.com/file")) === "https://drive/google.com/file") {
		$sourceType = VIDEO_SOURCE_GOOGLE_DRIVE;
	} else {
		$sourceType = VIDEO_SOURCE_UNKNOWN;
	}

	return($sourceType);
}

/******************************************************************************/

function calculateBurgeePoints($burgeeID){

	$burgeeID = (int)$burgeeID;

	$info = getBurgeeInfo($burgeeID);
	$tournamentIDs = implode2int($info['components']);
	$burgeePoints = [];
	$burgeePoints['schools'] = [];
	$rankings = [];

	$paramList = getBurgeeRankingParameters($info['burgeeRankingID']);

// Find who earned points for each team

	foreach($paramList as $i => $params){

		if($params['type'] == 'place'){
			$burgeePoints = burgeeFightersByPlace($params, $burgeePoints, $tournamentIDs);
		} elseif($params['type'] == 'percent') {
			$burgeePoints = burgeeFightersByPercent($params, $burgeePoints, $tournamentIDs);
		} else {
			// Why are we here?
		}

	}

// Rank the teams based on points

	foreach($burgeePoints['schools'] as $schoolID => $data){
		$rankings[$schoolID] = (int)$data['score'];
	}

	arsort($rankings);

	$burgeePoints['ranking'] = $rankings;

	return ($burgeePoints);
}



/******************************************************************************/

function burgeeFightersByPlace($params, $burgeeFighters, $tournamentIDs){

	$priority = (int)$params['priority'];
	$place = (int)$params['value'];


// Solo
	$sql = "SELECT rosterID, schoolID, tournamentID, placing, sortOrder
			FROM eventPlacings
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN eventTournaments USING(tournamentID)
			LEFT JOIN eventTournamentOrder USING(tournamentID)
			WHERE tournamentID IN ({$tournamentIDs})
			AND placing <= {$place}
			AND isTeam = 0
			AND schoolID > 2
			ORDER BY sortOrder ASC";
	$soloPlacings = mysqlQuery($sql, ASSOC);

	$burgeeFighters = processBurgeeFighters($burgeeFighters, $soloPlacings, $priority, $params['weight']);

// Teams
	$sql = "SELECT eventTeamRoster.rosterID, schoolID, tournamentID
			FROM eventTeamRoster
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN eventTournamentRoster USING(tournamentRosterID)
			WHERE teamID IN
				(SELECT rosterID
				FROM eventPlacings
				INNER JOIN eventRoster USING(rosterID)
				WHERE tournamentID IN ({$tournamentIDs})
				AND placing <= {$place}
				AND isTeam = 1
				AND schoolID > 2)
			ORDER BY teamOrder ASC";
	$teamPlacings = mysqlQuery($sql, ASSOC);

	$burgeeFighters = processBurgeeFighters($burgeeFighters, $teamPlacings, $priority, $params['weight']);

	return ($burgeeFighters);
}

/******************************************************************************/

function burgeeFightersByPercent($params, $burgeeFighters, $tournamentIDs){

	$percent = (float)$params['value'];
	$priority = (int)$params['priority'];

// Solo
	$sql = "SELECT rosterID, schoolID, tournamentID, placing
			FROM eventPlacings AS eP
			INNER JOIN eventRoster USING(rosterID)
			WHERE tournamentID IN ({$tournamentIDs})
			AND placing <= (SELECT (numParticipants * {$percent}) AS cuttofCount
							FROM eventTournaments AS eT2
							WHERE eP.tournamentID = eT2.tournamentID)
			AND isTeam = 0
			AND schoolID > 2";
	$soloPercent = mysqlQuery($sql, ASSOC);

	$burgeeFighters = processBurgeeFighters($burgeeFighters, $soloPercent, $priority, $params['weight']);

// Teams
	$sql = "SELECT eventTeamRoster.rosterID, schoolID, tournamentID
			FROM eventTeamRoster
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN eventTournamentRoster USING(tournamentRosterID)
			WHERE teamID IN
				(SELECT rosterID
				FROM eventPlacings AS eP2
				INNER JOIN eventRoster USING(rosterID)
				WHERE tournamentID IN ({$tournamentIDs})
				AND placing <= (SELECT ((SELECT count(*) AS numTeams
										FROM eventTournamentRoster AS eTR4
										INNER JOIN eventRoster USING(rosterID)
										WHERE isTeam = 1
										AND eTR4.tournamentID = eP2.tournamentID) * {$percent}) AS cuttofCount
								FROM eventTournaments AS eT3
								WHERE eP2.tournamentID = eT3.tournamentID)
				AND isTeam = 1
				AND schoolID > 2)
			ORDER BY teamOrder ASC";
	$teamPercent = mysqlQuery($sql, ASSOC);

	$burgeeFighters = processBurgeeFighters($burgeeFighters, $teamPercent, $priority, $params['weight']);

	return($burgeeFighters);
}

/******************************************************************************/

function sortTournamentAndDivisions($eventID){
// Returns a list that has all the tournaments, with the
// tournaments nested into divisions if applicable.
// Right now it doesn't respect tournament sorting for divisions, this is TODO.

	$tournamentIDs 	= getEventTournaments($eventID);
	$divisionsList	= getTournamentDivisions($eventID);

	if($tournamentIDs == null){
		return [];
	}

	$tournamentList = [];
	$isInDivision = [];


	foreach($divisionsList as $div){

		$divInfo = [];
		$divInfo['name'] = $div['divisionName'];
		$divInfo['divisionID'] = $div['divisionID'];

		$divItems = getTournamentDivisionItems($div['divisionID']);

		foreach(@(array)$divItems['items'] as $tournamentID){
			$isInDivision[$tournamentID] = true;
			$tmp['tournamentID'] = $tournamentID;
			$tmp['name'] = getTournamentName($tournamentID);
			$divInfo['tournaments'][] = $tmp;
		}

		$tournamentList[] = $divInfo;
	}


	foreach($tournamentIDs as $tournamentID){

		if(isset($isInDivision[$tournamentID]) == true){
			continue;
		}

		$tmp['tournamentID'] = $tournamentID;
		$tmp['name'] = getTournamentName($tournamentID);
		$tournamentList[] = $tmp;
	}

	return ($tournamentList);
}


/******************************************************************************/

function calculateRatingForUnrated($tournamentMax, $tournamentMin, $numRated){
// The intent is to be one person bellow the lowest rated perons.

	if($numRated <= 1){
		$defaultRating = 0;
	} else {
		$avgRatingGap = ((int)$tournamentMax - (int)$tournamentMin) /  (int)$numRated;

		$defaultRating = (int)$tournamentMin - $avgRatingGap;
		$defaultRating = round($defaultRating,0);
	}

	return($defaultRating);

}

/******************************************************************************/


function importEventRatingCSV(){

	$fileExtension = strtolower(pathinfo(basename($_FILES["eventRatingCSV"]["name"]),PATHINFO_EXTENSION));


	$originalName = basename($_FILES["eventRatingCSV"]["name"]);

	// Check if image file is a actual image or fake image

	$fileSizeKb = round($_FILES["eventRatingCSV"]["size"]/1024,0);
	$maxSizeKb = 1500;

	if ($fileSizeKb > $maxSizeKb) {
		setAlert(USER_ERROR, "File Size Exceeds Limit. {$fileSizeKb}/{$maxSizeKb} KB.");
		return;
	}

	switch($fileExtension){
		case 'txt':
		case 'csv':
			break;
		default:
			setAlert(USER_ERROR, "Only <b>txt</b>, and <b>csv</b>, files are supported.");
			return;
			break;
	}

	$csvFilePath = "exports/";
	$csvFileName = $csvFilePath.date("YmdHis");  // Temporary name
	$csvFileFull = $csvFileName.".".$fileExtension;

	$uploadSuccess = move_uploaded_file($_FILES["eventRatingCSV"]["tmp_name"], $csvFileFull);

	if ($uploadSuccess == true) {

		$eventList = [];
		$file = fopen($csvFileFull, 'r');

		$a = fgetcsv($file, 1000, ',');


		while (($data = fgetcsv($file, 1000, ',')) !== FALSE) {

			$eventID = (int)$data[0];

			if($eventID == 0){
				continue;
			}

			$eventName = trim($data[1]);

			$fighterRating = max((int)$data[2],EVENT_RATING_MIN_RATING);

			if(isset($eventList[$eventID]) == false){

				$year = (int)substr($eventName, -4);

				if($year > 2000 && $year < 2030){
					$eventName = trim(substr($eventName,0,-4));
					$eventList[$eventID]['year'] = $year;
				} else if(isset($data[3]) == true) {

					$date = date_parse($data[3]);
					if(isset($date['year']) == true && (int)$date['year'] > 2000 && (int)$date['year'] < 2030){
						$eventList[$eventID]['year'] = $date['year'];
					} else {
						$eventList[$eventID]['year'] = "??";
					}

				} else {
					$eventList[$eventID]['year'] = '??';
				}

				$eventList[$eventID]['name'] = $eventName;
			}

			$eventList[$eventID]['ratings'][] = $fighterRating;

		}

		$_SESSION['eventRating']['cvsData'] = $eventList;

		fclose($file);

		setAlert(USER_ALERT, "The file <b>{$originalName}</b> has been parsed succesfully.");


	} else {


		unset($_SESSION['eventRating']['cvsData']);
		setAlert(USER_ALERT, "Unknown error in file upload.");
		if(ALLOW['SOFTWARE_ADMIN'] == TRUE){
			setAlert(SYSTEM, "Not uploaded because of error #".$_FILES["file"]["error"]);
		}

		return;
	}

	unlink($csvFileFull);

}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
