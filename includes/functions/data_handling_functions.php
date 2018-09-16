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

function makePoolMatchOrder($groupRoster, $ignores = [], $groupSet = 1){

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

function autoRefreshTime($poolsInProgress){
// Specifies the time interval that a page should be refreshed used for the match 
// list pages. Returns 0 if no matches are in progress of if staff are logged in.

	if($poolsInProgress != true){
		return 0;
	}
	if(USER_TYPE != USER_GUEST){
		return 0;
	}

	return 60;
	
}

/******************************************************************************/

function getBracketAdvancements($allBracketInfo, $finalists){
// Determines which fighters should be advanced into which spots in the bracket

	if($allBracketInfo == null || $_SESSION['bracketHelper'] != 'on'){
		return null;
	}
	
	$bracketID = $allBracketInfo['winner']['groupID'];
	$bracketLevels = $allBracketInfo['winner']['bracketLevels'];

	$bracketMatches = getBracketMatchesByPosition($bracketID);
	
	$fighterSeed = $numFighters = $allBracketInfo['winner']['numFighters'];
	
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
		
		$seed = [];
		if(isset($finalists[$fighterSeed-1]['rosterID']))
		{
			$seed = $finalists[$fighterSeed-1]['rosterID'];
		}
		$matchPositions['winners'][$currentLevel][$matchNumber][$fighterNumber]['rosterID'] = $seed;
	}
	
	// Fighter positions for winners bracket based on bracket advancement
	foreach((array)$bracketMatches as $bracketLevel => $levelMatches){
		foreach($levelMatches as $bracketPosition => $matchInfo){
			if($matchInfo['winnerID'] != null){
				$winnerID = $matchInfo['winnerID'];
				
				$nextLevel = $bracketLevel - 1;
				$nextPosition = ceil($bracketPosition/2);
				
				if($bracketPosition % 2 == 1){
					$matchPositions['winners'][$nextLevel][$nextPosition][1]['rosterID'] = $winnerID;
				} else {
					$matchPositions['winners'][$nextLevel][$nextPosition][2]['rosterID'] = $winnerID;
				}
			}
		}
		
	}
	

	// Bronze Medal Match for Single Elim
	if(!isset($allBracketInfo['loser'])){ //is single elim
		// The loserID might not be set at the time of the call, in which case assign null
		@$matchPositions['losers'][1][1][1]['rosterID'] = $bracketMatches[2][1]['loserID'];
		@$matchPositions['losers'][1][1][2]['rosterID'] = $bracketMatches[2][2]['loserID'];
	}
	
	
	
	return ($matchPositions);
	
}

/******************************************************************************/

function getLoserBracketAdvancements($allBracketInfo, $finalists){
// Determines which fighters should be advanced into which spots in the bracket
//   IMPORTANT:	Behavior is undefined if the consolation bracket size 
//				is not a power of 2. ie. top8, top16. 
	
	if(!isset( $allBracketInfo['loser']) || $allBracketInfo['loser'] == null){
		return [];
	}

	$bracketID = $allBracketInfo['loser']['groupID'];
	$bracketLevels = $allBracketInfo['loser']['bracketLevels'];

	$bracketMatches = getBracketMatchesByPosition($bracketID);
	$winnerBracketMatches = getBracketMatchesByPosition( $allBracketInfo['winner']['groupID']);
	$changeOverBracket = false;
	
	
	$matchPositions = [];
	for($bracketLevel = $bracketLevels; $bracketLevel >= 1; $bracketLevel--){
		$matchesInLevel = pow(2, floor($bracketLevel/2));
		
		if($bracketLevel % 2 == 0 AND $bracketLevel != $bracketLevels){
			// Crosses fighters over to the other side of the bracket every second time they are added
			$changeOverBracket = !$changeOverBracket;
		} 
		
		for($pos = 1; $pos <= $matchesInLevel; $pos++){
			if($bracketLevel == $bracketLevels){
				$winBLevel = (($bracketLevel+1)/2) + 1;
				$winPos = ($pos*2) -1;
				if(isset($winnerBracketMatches[$winBLevel][$winPos]['loserID'])){
					$matchPositions['losers'][$bracketLevel][$pos][1]['rosterID'] = 
						$winnerBracketMatches[$winBLevel][$winPos]['loserID'];
				}

				if(isset($winnerBracketMatches[$winBLevel][$winPos + 1]['loserID'])){
					$matchPositions['losers'][$bracketLevel][$pos][2]['rosterID'] = 
						$winnerBracketMatches[$winBLevel][$winPos + 1]['loserID'];	
				}	
				continue;
			}
			
			if($bracketLevel % 2 == 0){ 
				$winBLevel = ($bracketLevel/2) + 1;
				if($changeOverBracket){
					$newPos = $matchesInLevel - $pos + 1;
				} else {
					$newPos = $pos;
				}
					
				if(isset($winnerBracketMatches[$winBLevel][$pos]['loserID'])){
					$matchPositions['losers'][$bracketLevel][$newPos][1]['rosterID'] = 
						$winnerBracketMatches[$winBLevel][$pos]['loserID'];
				}
				
				if(isset($bracketMatches[$bracketLevel+1][$pos]['winnerID'])){	
					$matchPositions['losers'][$bracketLevel][$pos][2]['rosterID'] = 
						$bracketMatches[$bracketLevel+1][$pos]['winnerID'];
				}

			} else { // Odd levels just get info from match before
				
				$oldPos = ($pos*2) - 1;
				
				$matchPositions['losers'][$bracketLevel][$pos][1]['rosterID'] = 
					$bracketMatches[$bracketLevel+1][$oldPos]['winnerID'];
				
				$matchPositions['losers'][$bracketLevel][$pos][2]['rosterID'] = 
					$bracketMatches[$bracketLevel+1][$oldPos+1]['winnerID'];
				
			}
			
		}
			
	}
	
	return $matchPositions;
	
}

/******************************************************************************/

function checkPassword($input, $type, $eventID = null){
// Checks if the password provided matches the password in the database
// Returns true if the passwords match, false if they don't
// 

// Checks
	// No password required for guests
	if($type == USER_GUEST){return true;}
	
	// Admin and staff logins are tied to events. If they try to log in without
	// and event then it fails
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){
		if($type == USER_ADMIN || $type == USER_STAFF){
			return false;
		}
	}

// Get password to compare
	switch ((int)$type){
		case USER_VIDEO:
			$password = getPassword('USER_VIDEO');
			break;
		case USER_STAFF:
			$password = getPassword('USER_STAFF',$eventID);
			break;
		case USER_ADMIN:
			$password = getPassword('USER_ADMIN',$eventID);
			break;
		case USER_SUPER_ADMIN:
			$password = getPassword('USER_SUPER_ADMIN');
			break;
		case USER_STATS:
			$password = getPassword('USER_STATS');
			break;
		default:
			return false;
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
		echo ceil(log($numFighters,2));
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

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
