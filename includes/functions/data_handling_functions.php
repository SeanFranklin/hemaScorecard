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
			
			foreach($team1Roster as $rosterID1){

				// Skip if fighter 1 can't continue
				if( @(int)$ignores[$rosterID1]['stopAtSet'] < (int)$groupSet    // If unset treat it as zero
					&& @(int)$ignores[$rosterID1]['stopAtSet'] > 0){
					continue;
				}

				foreach($team2Roster as $rosterID2){

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

function isLivestreamValid($eventID=null){
	
	$info = getLivestreamInfo($eventID);
	
	if($info['isLive'] == 1){
		return 'live';
	}
	if($info['platform'] != '' && $info['chanelName'] != ''){
		return 'ready';
	}
	
	return false;
	
	
}

/******************************************************************************/

function base60($num){
// Returns the number in base 60 format, divided by a colon.
// Would make sec -> min:sec or min -> hour:min

	$a = floor($num/60);
	$b = $num % 60;

	if($b < 10){
		$b = "0".$b;
	}
	return ($a.':'.$b);
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
	foreach((array)$bracketMatches as $bracketLevel => $levelMatches){

		if($bracketLevel % 2 == 0 AND $bracketLevel != $bracketLevels){
			// Crosses fighters over to the other side of the bracket every second time they are added
			$changeOverBracket = !$changeOverBracket;
		} 

		$matchesInLevel = count($levelMatches);

		foreach($levelMatches as $bracketPosition => $matchInfo){
			$pos = $bracketPosition;

			if($bracketLevel == $bracketLevels){
				// First column in the bracket, import all from primary
				$method = "allPrimary";
			} elseif(($bracketLevel + $numExtraMatchesAtEnd) % 2 == 0){
				$method = "mixed";
			} else {
				$method = "allSecondary";
			} 

			if($bracketLevel == 1){
				$numExtraMatchesAtEnd++;
			}

			switch($method){
				case 'allPrimary':
					$winBLevel = (($bracketLevel+1)/2) + 1;
					$winPos = ($pos*2) -1;
					if(isset($winnerBracketMatches[$winBLevel][$winPos]['loserID'])){
						$matchPositions[$bracketLevel][$pos][1]['rosterID'] = 
							$winnerBracketMatches[$winBLevel][$winPos]['loserID'];
					}

					if(isset($winnerBracketMatches[$winBLevel][$winPos + 1]['loserID'])){
						$matchPositions[$bracketLevel][$pos][2]['rosterID'] = 
							$winnerBracketMatches[$winBLevel][$winPos + 1]['loserID'];	
					}	
					break;

				case 'allSecondary':
					$oldPos = ($pos*2) - 1;

					// winnerID could not exist
					$matchPositions[$bracketLevel][$pos][1]['rosterID'] = 
						@$bracketMatches[$bracketLevel+1][$oldPos]['winnerID'];
					
					$matchPositions[$bracketLevel][$pos][2]['rosterID'] = 
						@$bracketMatches[$bracketLevel+1][$oldPos+1]['winnerID'];
					break;

				case 'mixed':
					$winBLevel = floor($bracketLevel/2) + 1;
					$winPos = $pos;
					$fetchBracketLevel = $bracketLevel + 1;
					$fetchPos = $pos;

					if($numExtraMatchesAtEnd != 0){
						$newPos = $numExtraMatchesAtEnd;
						$winPos = $numExtraMatchesAtEnd - 1;
						$fetchBracketLevel = 1;
						$fetchPos = $pos - 1;
					} elseif($changeOverBracket){
						$newPos = $matchesInLevel - $pos + 1;
					} else {
						$newPos = $pos;
					}
						
					// Might not exist. Treat as null.
					$fighter1 = @$winnerBracketMatches[$winBLevel][$winPos]['loserID'];
					$fighter2 = @$bracketMatches[$fetchBracketLevel][$fetchPos]['winnerID'];
					
					$matchPositions[$bracketLevel][$pos][1]['rosterID'] = $fighter1;
					$matchPositions[$bracketLevel][$pos][2]['rosterID'] = $fighter2;


					break;

				default:
					break;

			}
			
		}
			
	}
	
	return $matchPositions;
	
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

function shouldMatchConcludeByPoints($matchInfo, $maxPoints){
// Returns true if the software should auto-conclude a match because it has reached
// the maximum number of scoring exchanges.	

	if($matchInfo['matchComplete'] == 1 
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

			if(    ($maxPoints != 0)
				&& (   $matchInfo['fighter1score'] >= $maxPoints 
					|| $matchInfo['fighter2score'] >= $maxPoints)
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

function shouldMatchConcludeBySpread($matchInfo,$maxSpread){
// Returns true if the software should auto-conclude a match because it has reached
// the maximum number of scoring exchanges.	

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
	
	if(    $matchInfo['matchComplete'] == 1 
		|| $matchInfo['ignoreMatch'] == 1 
		|| $matchInfo['timeLimit'] == 0)
	{ 
		return false;
	}

	if($matchInfo['matchTime'] >= $matchInfo['timeLimit']){
		setAlert(USER_ALERT,"Time limit reached.");
		return true;
	} else {
		return false;
	}
}

/******************************************************************************/

function shouldMatchConcludeByExchanges($matchInfo, $maxExchanges){
// Returns true if the software should auto-conclude a match because it has reached
// the maximum number of scoring exchanges.	
	
	if(		$matchInfo['matchComplete'] == 1 
		 || $matchInfo['ignoreMatch'] == 1
		 || $maxExchanges == 0){ 
		return false;
	}

	$numExchanges = getNumMatchScoringExchanges($matchInfo['matchID']);

	if($numExchanges >= $maxExchanges){
		setAlert(USER_ALERT,"Exchange count reached.");
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
	
	if($matchInfo['maxDoubles'] != 0 
		&& getMatchDoubles($matchInfo['matchID']) >= $matchInfo['maxDoubles']){

		$_POST['matchWinnerID'] = 'doubleOut';

	} elseif($matchInfo['fighter1score'] == $matchInfo['fighter2score']){

		if(!isTies($matchInfo['tournamentID'])){
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

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
