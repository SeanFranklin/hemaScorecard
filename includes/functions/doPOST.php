<?php
/*******************************************************************************
	doPOST
	
	Landing platform for POST form submissions.
	Every form submision contains a 'formName' which directs the 
	appropriate action to handle POST data.
	
*******************************************************************************/

function _select_above(){
	//this is just a naviagtion placeholder
}

/******************************************************************************/

function processPostData(){

	if(count($_POST) > 0){
		// Refresh page after POST processing complete to prevent resubmits
		$refreshPage = true;
	}

	/* For debugging, commented out for regular use ///
	$refreshPage = false;
	define(SHOW_POST, true);	$_SESSION['post'] = $_POST;
	//define(SHOW_SESSION, true);
	/////////////////////////////*/
	
	$formName = $_POST['formName'];
	unset($_POST['formName']);

	checkSession();
	switch($formName){
	
// Navigation Cases
		case 'logUserIn':
			logUserIn();
			break;
		case 'selectEvent':
			changeEvent();
			break;
		case 'changeTournament':
			$_SESSION['tournamentID'] = $_POST['newTournament'];
			unset($_SESSION['matchID']);
			unset($_SESSION['bracketHelper']);
			$_SESSION['groupSet'] = 1;
			break;
		case 'navigatePage':
			$newUrl = "Location: ".$_POST['newPage']."#";
			header($newUrl);
			exit;
			break;
		case 'goToMatch':
			$_SESSION['matchID'] = $_POST['matchID'];
			header("Location: scoreMatch.php");
			exit;
			break;
		case 'goToPiece':
			$_SESSION['matchID'] = $_POST['matchID'];
			header("Location: scorePiece.php");
			exit;
			break;
		case 'rosterViewMode':
			if(isset($_POST['rosterViewMode'])){
				$_SESSION['rosterViewMode'] = $_POST['rosterViewMode'];
			}
			break;
		case 'eventNavigation':
			$link = $_POST['eventNavigation'];
			header("Location: {$link}");
			exit;
			break;
			
// Roster Management Cases
		case 'addEventParticipants':
			addEventParticipantsByName();
			addEventParticipantsByID();	
			break;
		case 'deleteFromEvent':
			deleteFromEvent();
			break;
		case 'changeSchool':
			$_SESSION['newParticipantsSchoolID'] = $_POST['schoolID'];
			break;
		case 'addEventParticipantsMode':
			if(isset($_POST['newParticipantsMode'])){
				$_SESSION['addEventParticipantsMode'] = $_POST['newParticipantsMode'];
				unset($_SESSION['jumpTo']);
			}
			break;
		case 'addToTournamentRoster':
			addToTournament();
			break;
		case 'deleteFromTournamentRoster':
			deleteFromTournament();
			break;
		case 'editEventParticipant':
			editEventParticipant();
			break;
		case 'importRosterCSV':
			importRosterCSV();
			break;
		
	
// Pool Management Cases
		case 'createNewPools':
			createNewPools();
			break;
		case 'addFightersToPool':
			addFightersToGroup();
			break;
		case 'deleteFromPools':
			deleteFromGroups();
			break;
		case 'manualMatchSet':
			$_SESSION['manualMatchSet'] = $_POST['manualMatchSet'];
			updatePoolMatchList();
			break;
		case 'changePoolSet':
			$_SESSION['groupSet'] = $_POST['groupSet'];
			break;
		case 'generateNextPoolSet':
			pool_generateNextPools();  //scoringFunctions.php
			break;
		case 'changeGroupOrder':
			reOrderGroups($_POST['newGroupNumber']);
			break;
		case 'updatePoolSets':
			updatePoolSets();
			break;
		case 'renameGroups':
			renameGroups();
			break;
			
// Scored Rounds
		case 'numberOfGroupSets':
			updateNumberOfGroupSets();
			break;
		case 'createNewRounds':
			createNewRounds();
			break;
		case 'deleteFromRounds':
			deleteFromGroups();
			break;
		case 'addFightersToRound':
			addFightersToGroup();
			break;
		case 'addMultipleFighterToRound':
			addMultipleFightersToRound();
			break;
		case 'addExchanges':
			scored_AddExchanges();
			break;
		case 'updateScores':
			scored_UpdateExchanges();
			break;
		case 'deleteExchanges':
			deleteExchanges();
			break;
		case 'stageOptions':
			updateStageOptions();
			break;
			
		
// Match Scoring
		case 'newExchange':
			addNewExchange();
			break;
		case 'matchWinner':
			addMatchWinner();
			break;
		case 'switchFighters':
			switchMatchFighters();
			break;
		case 'YouTubeLink':
			updateYouTubeLink();
			break;
		
				
// Finals
		case 'createBracket':
			createTournamentBrackets(null, null);
			break;
		case 'updateBracket':
			if(isset($_POST['goToMatch'])){
				$_SESSION['matchID'] = $_POST['goToMatch'];
				header("Location: scoreMatch.php");
				exit;
			} else if(isset($_POST['updateBracket'])){
				updateFinalsBracket();
			}
			break;
		case 'deleteBracket':
			deleteBracket();
			break;
		case 'toggleBracketHelper':
			toggleBracketHelper();
			break;
			
			
// Admin Cases
		case 'newSystemPasswords':
			updateSystemPasswords();
			break;
		case 'newPasswords':
			updateEventPasswords();
			break;
		case 'eventDefaultUpdate':
			updateEventDefaults();
			break;
		case 'updateTournamentInfo':
			updateEventTournaments();
			break;
		case 'deleteTournament':
			deleteEventTournament();
			break;
		case 'ignoreFightersInTournament':
			updateIgnoredFighters();
			break;
		case 'addNewSchool':
			addNewSchool();
			break;
		case 'editExistingSchool':
			updateExistingSchool();
			break;
		case 'addNewEvent':
			addNewEvent();
			break;
		case 'editEvent':
			editEvent();
			break;
		case 'eventStatusUpdate':
			editEventStatus();
			break;
		case 'addTournamentType':
			addTournamentType();
			break;
		case 'displaySettings':
			updateDisplaySettings();
			break;
		case 'finalizeTournament':
			if($_POST['finalizeTournament'] == 'revoke'){
				removeTournamentPlacings($_POST['tournamentID']);
			} else {
				generateTournamentPlacings($_POST['tournamentID']);
			}
			break;
		case 'addAttackTypes':
			addAttacksToTournament();
			break;
			
			
			
// Stats Cases
		case 'dataFilters':
			setDataFilters();
			break;
		case 'resultsDump':
			exportResultsToCSV($_POST['CsvDump']);
			break;
			
			
// Cutting Qualification Cases
		case 'newCutQuals':
			addNewCuttingQuals();
			break;
		case 'newCutQualMode':
			$_SESSION['newCutQualMode'] = $_POST['newCutQualMode'];
			break;
		case 'changeCutQualDisplay':
			$_SESSION['cutQualDisplayMode'] = $_POST['cutQualDisplayMode'];
			break;
		case 'setCutQualStandards':
			updateTournamentCuttingStandard();
			break;
		case 'addQualledFighterEvent':
			addNewCuttingQual_event();
			break;
		case 'removeQualledFighterEvent':
			removeCuttingQual_event();
			break;
		case 'changeCuttingStandard':
			$_SESSION['cuttingQualStandard'] = $_POST['standardID'];
			$_SESSION['cuttingQualDate'] = $_POST['cuttingQualDate'];
			break;
	
	
// Livestream Cases
		case 'livestreamInfo':
			updateLivestreamInfo();
			break;
		case 'activateLivestream':
			activateLivestream();
			break;
		case 'hideLivestreamAlert':
			$_SESSION['hideLivestreamAlert'] = true;
			break;
		case 'livestreamOrder':
			setLivestreamMatchOrder();
			break;
		case 'setLivestreamMatch':
			setLivestreamMatch();
			break;
			
			
// Default Cases
		case null:
			break;
		default:
			break;
	}

	if(isset($_SESSION['checkEvent'])){
		checkEvent();
		unset($_SESSION['checkEvent']);
	}
	
	if(isset($_SESSION['updatePoolStandings'])){
		foreach($_SESSION['updatePoolStandings'] as $tournamentID => $data){
			updatePoolStandings($tournamentID);   //doPOST.php
		}
		unset($_SESSION['updatePoolStandings']);
	}

	unset($_POST);

	if($refreshPage){
		$url = strtok($_SERVER['PHP_SELF'], "#");
		$url .= "#".$_SESSION['jumpTo'];
		unset($_SESSION['jumpTo']);
		header('Location: '.$url);
		exit;
	}
	
}


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

function _select_bellow(){
	//this is just a naviagtion placeholder
}

/******************************************************************************/

function checkEvent(){
// Checks that the event is in proper order after changes have been made
// Checks include:	Thatthe order of pools and rounds
//					That the matches reflect the pool rosters
//					That the standings have been properly updated

	// If it has been specified to check everything in the event
	if($_SESSION['checkEvent']['all'] === true){
		$tournamentIDs = getEventTournaments();
		foreach((array)$tournamentIDs as $tournamentID){
			checkGroupOrders($tournamentID);
			updatePoolStandings($tournamentID);
			if(isRounds($tournamentID)){
				updateRoundMatchList();
			}
		}
		updatePoolMatchList(null, 'event');
		unset($_SESSION['checkEvent']);
		return;
	}
	
	// If it has been specified to check only certain tournament in the event
	foreach($_SESSION['checkEvent'] as $tournamentID => $tournament){

		if(ctype_digit($tournamentID) || is_int($tournamentID)){
			$name = getTournamentName($tournamentID);
			
			// Check everything in the tournament
			if($tournament['all'] === true){
				if(isPools($tournamentID)){
					checkGroupOrders($tournamentID);
					updatePoolMatchList($tournamentID, 'tournament');
					updatePoolStandings($tournamentID);
				}
				if(isRounds($tournamentID)){
					
					checkGroupOrders($tournamentID);
					checkRoundRoster($tournamentID);
					updateRoundMatchList();
				}
				
			// Only check the pool orders/names	
			} elseif($tournament['order'] === true){
				checkGroupOrders($tournamentID);
			
			// Only check specified groups
			} else {
				foreach($tournament as $groupID => $groupOp){				
					if(isPools($tournamentID)){
						checkGroupOrders($tournamentID, $groupID);
						updatePoolMatchList($groupID, 'pool');
					}
					if(isRounds($tournamentID)){
						checkRoundRoster($tournamentID, $groupID);
						updateRoundMatchList();
					}
					
				}
			}	
			
			updatePoolStandings($tournamentID);
		}
	}
	unset($_SESSION['checkEvent']);
	
}


/******************************************************************************/

function toggleBracketHelper(){
// Attempts to toggle the status of the bracket helper

	// Turns the helper on if the user has instigated a manual overide
	if(isset($_POST['bracketHelperOverride'])){
		$_SESSION['bracketHelper'] = 'on';
		return;
	}
	
	// Turns the helper off it is on
	if($_SESSION['bracketHelper'] == 'on' || $_SESSION['bracketHelper'] == 'try'){
		unset($_SESSION['bracketHelper']);
		return;
	}
	
	// Error checking
	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){
		$_SESSION['alertMessages']['systemErrors'][] = "No tournamentID in toggleBracketHelper()";
		return;
	}
	
	$incompletes = getTournamentIncompletes($tournamentID, 'pool');
	
	// Turns the bracket helper on if there are no incomplete pool matches
	if($incompletes == null){
		$_SESSION['bracketHelper'] = 'on';
		return;
	} else {
		// Enters a 'try' state to inform the user there are still incomplete matches
		$_SESSION['incompletePoolMatches'] = $incompletes;
		$_SESSION['bracketHelper'] = 'try';
	}
	
}

/******************************************************************************/

function updatePoolStandings($tournamentID = null){
// Calls the functions in poolScoring.php required to update the pool standings
		
	if(USER_TYPE < USER_STAFF){return;}
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	define("TOURNAMENT_ID", $tournamentID);
	
	// Check to catch non-pool events
	$elimID = getElimID($tournamentID);
	if($elimID != POOL_BRACKET && $elimID != POOL_SETS){
		return; 
	}	
	
	$sql = "SELECT numGroupSets
			FROM eventTournaments
			WHERE tournamentID = {$tournamentID}";
	$numberOfPoolSets = mysqlQuery($sql, SINGLE, 'numGroupSets');
	
	for($setNumber = 1; $setNumber <= $numberOfPoolSets; $setNumber++){
		
		$poolExchanges = getAllTournamentExchanges($tournamentID, 'pool', $setNumber);
	
		// Calculate Scores
		$fighterScores = pool_ScoreFighters($poolExchanges, $tournamentID, $setNumber);
		$fighterRanks = pool_RankFighters($fighterScores, $tournamentID);

		// Save List
		recordScores($fighterRanks, $tournamentID, 'pool', $setNumber);
	}
	
}


/******************************************************************************/

function setDataFilters(){
// Sets the data filters for fighter analytics display
	
	$_SESSION['dataFilters']['weaponID'] = $_POST['weaponID'];
	$_SESSION['dataFilters']['threshold'] = $_POST['threshold'];
	$_SESSION['dataFilters']['sortKey'] = $_POST['sortKey'];
	$_SESSION['dataFilters']['newQuery'] = true;
	
	if($_POST['sortOrder'] == 'asc'){
		$_SESSION['dataFilters']['sortOrder'] = SORT_ASC;
	} else if($_POST['sortOrder'] == 'desc'){
		$_SESSION['dataFilters']['sortOrder'] = SORT_DESC;
	}
	
	foreach($_POST['filterField'] as $num => $id){
		if($id == null || $id == '0'){
			unset($_SESSION['dataFilters']['filters'][$num]);
		} else {
			$_SESSION['dataFilters']['filters'][$num] = $_POST['filterField'][$num];
		}
	}

}

/******************************************************************************/

function createTournamentBrackets($tournamentID, $numWinnerBracketFighters){
// Creates a tournament bracket depending on the elimination type used
// Overwrites any existing brackets

	if(USER_TYPE < USER_ADMIN){return;}

	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}

	if($numWinnerBracketFighters == null){$numWinnerBracketFighters = (int)$_POST['numWinnerBracketFighters'];}
	if($numWinnerBracketFighters < 2){ 
		$_SESSION['alertMessages']['userErrors'][] = "Can not create a bracket with less than 2 people";
		return;
	}
	
	$numLooserBracketFighters = (int)$_POST['numLooserBracketFighters'];

	if($numLooserBracketFighters == null){
		if($numWinnerBracketFighters < 4){
			$numLooserBracketFighters = 0;
		} else {
			$numLooserBracketFighters = 2;
		}
	} elseif($numLooserBracketFighters > $numWinnerBracketFighters - 2){
		$numLooserBracketFighters = $numWinnerBracketFighters - 2;
	} else {
		$numLooserBracketFighters -= 2;
	}

	createWinnersBracket($tournamentID, $numWinnerBracketFighters);
	if($numLooserBracketFighters >= 2){
		createConsolationBracket($tournamentID, $numLooserBracketFighters);
	}
}

/******************************************************************************/

function addNewExchange(){
// Add a new exchange to a match
	
	if(USER_TYPE < USER_STAFF){return;}
	
	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){return;}
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	
	// Easter egg for anyone who gets the the Score Match page while they are in
	// event that has rounds and pieces rather than matches
	if(isRounds($tournamentID)){
		$_SESSION['alertMessages']['userAlerts'][] = "<p>If you go against yourself with a 
			sharp sword and come out alive, are you a winner or a loser?</p>";
		return;
	}
	
	if($matchID == null){$matchID = $_SESSION['matchID'];}
	if($matchID == null){return;}
	
// updates the last exchange

	$matchInfo = getMatchInfo($matchID);
	$scoring = $_POST['score'];

	$f1ID = $matchInfo['fighter1ID'];
	$f2ID = $matchInfo['fighter2ID'];

	$exchangeID = $scoring['exchangeID'];

	//return;
	switch ($_POST['lastExchange']){
		case 'scoringHit':
			calculateLastExchange($matchInfo,$scoring);
			break;
		case 'penalty':
			insertPenalty($matchInfo,$scoring);
			break;
		case 'noQuality':
			if($scoring[$f1ID]['hit'] == 'noQuality'){	$id = $f1ID;}
			if($scoring[$f2ID]['hit'] == 'noQuality'){	$id = $f2ID;}
			if($id == null){ break; }
			insertLastExchange($matchInfo, 'noQuality', $id, 'null', 'null', null, null, null, $exchangeID);
			break;
		case 'doubleHit':
			insertLastExchange($matchInfo, 'double', 'null', 'null', 'null', null, null, null, $exchangeID);
			break;
		case 'noExchange':
			insertLastExchange($matchInfo, 'noExchange', 'null', 'null', 'null', null, null, null, $exchangeID);
			break;
		case 'clearLastExchange':
			clearExchanges($matchID,'last');
			break;
		case 'clearAllExchanges':
			clearExchanges($matchID,'all');
			break;
		default:
			$_SESSION['alertMessages']['systemErrors'][] = "Could not figure out the exchange type in doUpdateMatch.php()";
			break;
		
	} 
	
	updateMatch($matchInfo);
	
	// Check if it is the type of tournament which has a set number of exchanges
	$maxExchanges = getMaxExchanges();
	if($maxExchanges >= 1 && $_POST['lastExchange'] != 'clearLastExchange'){
		$exchanges = getMatchExchanges($matchID);
		if(count($exchanges) >= $maxExchanges){
			concludeMatchByExchanges($matchID, $exchanges, $maxExchanges);
			updateMatch($matchInfo);
		}
	}
	
}

/******************************************************************************/

function calculateLastExchange($matchInfo, $scoring){
// determine which type of scoring is in effect so the exchange 
// can be added appropriately

	if(USER_TYPE < USER_STAFF){return;}

	if($eventID == null){$eventID = $_SESSION['eventID'];}
	if($eventID == null){return;}
	
	if($tournamentID == null){$tournamentID = $_SESSION['tournamentID'];}
	if($tournamentID == null){return;}
	
	$scoring = array_filter_recursive($scoring);

	$matchID = $matchInfo['matchID'];
	$id1 =$matchInfo['fighter1ID'];
	$id2 = $matchInfo['fighter2ID'];
	
	// Return if there was no initial hit for either fighter
	if(!$scoring[$id1]['hit'] AND !$scoring[$id2]['hit']){
		return;
	}
	
	// Get the afterblow Type
	$doubleTypes = getDoubleTypes($tournamentID);
	
	if($doubleTypes['afterblowDisabled'] == true){
		noAfterblowScoring($matchInfo, $scoring);
	} else if($doubleTypes['afterblowType'] == 'deductive'){
		deductiveAfterblowScoring($matchInfo, $scoring);
	} else if($doubleTypes['afterblowType'] == 'full'){
		fullAfterblowScoring($matchInfo, $scoring);
	}

	
	return;
	
}

/******************************************************************************/

function deductiveAfterblowScoring($matchInfo,$scoring){
// scoring calculations for Deductive Afterblow

	if(USER_TYPE < USER_STAFF){return;}
	
	$matchID = $matchInfo['matchID'];
	$id1 =$matchInfo['fighter1ID'];
	$id2 = $matchInfo['fighter2ID'];
	$exchangeID = $scoring['exchangeID'];
	
// Doesn't do anything if a score was entered in both boxes
	if($scoring[$id1]['hit'] && $scoring[$id2]['hit']){
		return;
	}
	
// Records the exchange as a penalty if the score is negative
	if($scoring[$id1]['hit'] < 0 OR $scoring[$id1]['hit'] < 0){
		insertPenalty($matchInfo, $scoring);
		return;
	}

// Determine which fighter hit
	if($scoring[$id1]['hit']){
		if($scoring[$id2]['hit']){
			// Score entered for both fighters. Not a valid exchange.
			return;
		} else {
			$rosterID = $id1;
		}
	} elseif($scoring[$id2]['hit']){
		$rosterID = $id2;
	} else {
		return;
	}
	

// Base score value
	if($_POST['scoreLookupMode'] == 'rawPoints'){
		$scoreValue = $scoring[$rosterID]['hit'];
	} elseif ($_POST['scoreLookupMode'] == 'ID'){
		$at = getAttackAttributes($scoring[$rosterID]['hit']);
		$scoreValue = $at['attackPoints'];
		$rType = $at['attackType'];
		$rTarget = $at['attackTarget'];
		$rPrefix = $at['attackPrefix'];
	} else {
		$_SESSION['alertMessages']['systemErrors'][] = "No scoreLookupMode set in deductiveAfterblowScoring()";
		return;
	}
	
	if($_POST['attackModifier'] == 9){
		$rPrefix = (int)$_POST['attackModifier'];
		$scoreValue += getControlPointValue();
	}

// Afterblow deduction
	$scoreDeduction = 0;
	// checks for clean or afterblow
	if($scoring[$rosterID]['afterblow'] == null){
		$exchangeType = 'clean';	
	} else {
		$exchangeType = 'afterblow';
		$scoreDeduction = $scoring[$rosterID]['afterblow'];
	}
	
	insertLastExchange($matchInfo, $exchangeType, $rosterID, $scoreValue, 
					$scoreDeduction, $rPrefix, $rType, $rTarget, $exchangeID);
}

/******************************************************************************/

function insertPenalty($matchInfo, $scoring){
// inserts an exchange as a penalty

	if(USER_TYPE < USER_STAFF){return;}

	$matchID = $matchInfo['matchID'];
	$id1 =$matchInfo['fighter1ID'];
	$id2 = $matchInfo['fighter2ID'];
	$exchangeID = $scoring['exchangeID'];
	
	if ($scoring[$id1]['penalty'] < 0 && $scoring[$id2]['penalty'] < 0){
		$_SESSION['alertMessages']['userErrors'][] =  "<span class='red-text'>Exchange can not be added.</span><BR>
				You can not apply a penalty to both fighters in the same exchange.";
		return;
	}
	
	if($scoring[$id1]['penalty'] < 0){
		$scoreValue = $scoring[$id1]['penalty'];
		$rosterID = $id1;
		insertLastExchange($matchInfo, 'penalty', $rosterID, $scoreValue, 0, null, null, null, $exchangeID);
		return;
	}
	
	if($scoring[$id2]['penalty'] < 0){
		$scoreValue = $scoring[$id2]['penalty'];
		$rosterID = $id2;
		insertLastExchange($matchInfo, 'penalty', $rosterID, $scoreValue, 0, null, null, null, $exchangeID);
		return;
	}
}


/******************************************************************************/

function fullAfterblowScoring($matchInfo,$scoring){
// scoring calculations for Full Afterblow	
	
	if(USER_TYPE < USER_STAFF){return;}
	
	$matchID = $matchInfo['matchID'];
	$id1 =$matchInfo['fighter1ID'];
	$id2 = $matchInfo['fighter2ID'];
	$exchangeID = $scoring['exchangeID'];
	
	// If raw score
	if($_POST['scoreLookupMode'] == 'rawPoints'){
		$score1 = (int)$scoring[$id1]['hit'];
		$score2 = (int)$scoring[$id2]['hit'];
	} elseif ($_POST['scoreLookupMode'] == 'ID'){
		$at1 = getAttackAttributes($scoring[$id1]['hit']);
		$score1 = $at1['attackPoints'];
		$scoring[$id1]['hit'] = $score1;
		
		$at2 = getAttackAttributes($scoring[$id2]['hit']);
		$score2 = $at2['attackPoints'];
		$scoring[$id2]['hit'] = $score2;
	} else {
		$_SESSION['alertMessages']['systemErrors'][] = "No scoreLookupMode in fullAfterblowScoring()";
		return;
	}

	// records the exchange as a penalty if the score is negative
	if($score1 < 0 OR $score2 < 0){
		insertPenalty($matchInfo, $scoring);
		return;
	} 
	
	//checks if only one fighter hit
	if(xorWithZero($score1,$score2)){//only one hitter
		if($score1){
			$rosterID = $id1;
		} else {
			$rosterID = $id2;
		}
		$scoreValue = 	$scoring[$rosterID]['hit'];
		$scoreDeduction = 'null';
		$exchangeType = 'clean';

		if($_POST['attackModifier'] == 9){
			$rPrefix = (int)$_POST['attackModifier'];
			$scoreValue += getControlPointValue();
		}
		
	} else {//both hit

		//attributes the strike to the fighter with the higher value hit
		if($score1 > $score2){
			$rosterID = $id1;
			$otherID = $id2;
		} else if($score2 > $score1){
			$rosterID = $id2;
			$otherID = $id1;
		} else {
			if($matchInfo['fighter2score'] > $matchInfo['fighter1score']){
				$rosterID = $id2;
				$otherID = $id1;
			} else {
				$rosterID = $id1;
				$otherID = $id2;
			}
			
		}
		
		$scoreValue = 	$scoring[$rosterID]['hit'];
		$scoreDeduction = $scoring[$otherID]['hit'];
		$exchangeType = 'afterblow';
		
		// sets the score deduction to the string 'null' for SQL storage
		if($scoreDeduction == ""){$scoreDeduction = 'null';}
		
	}
	
	
	insertLastExchange($matchInfo, $exchangeType, $rosterID, $scoreValue, $scoreDeduction, $rPrefix, null,null,$exchangeID);
}

/******************************************************************************/

function noAfterblowScoring($matchInfo,$scoring){
// scoring calculations for No Afterblow

	if(USER_TYPE < USER_STAFF){return;}

	$matchID = $matchInfo['matchID'];
	$id1 = $matchInfo['fighter1ID'];
	$id2 = $matchInfo['fighter2ID'];

	$exchangeID = $scoring['exchangeID'];
	
	// records the exchange as a penalty if the score is negative
	if($scoring[$id1]['hit'] < 0 OR $scoring[$id2]['hit'] < 0){
		insertPenalty($matchInfo, $scoring);
		return;
	} 

	// Checks if points are entered for both fighters
	if(!($scoring[$id1]['hit'] xor $scoring[$id2]['hit'])){
		insertLastExchange($matchInfo, 'double', 'null', 'null', 'null', null, null, null, $exchangeID);
		return;
	}
	
	// Determine which fighter hit
	if($scoring[$id1]['hit']){
		if($scoring[$id2]['hit']){
			// Score entered for both fighters. Not a valid exchange.
			return;
		} else {
			$rosterID = $id1;
		}
	} elseif($scoring[$id2]['hit']){
		$rosterID = $id2;
	} else {
		return;
	}
	
	// Base score value
	if($_POST['scoreLookupMode'] == 'rawPoints'){
		$scoreValue = $scoring[$rosterID]['hit'];
	} elseif ($_POST['scoreLookupMode'] == 'ID'){
		$at = getAttackAttributes($scoring[$rosterID]['hit']);
		$scoreValue = $at['attackPoints'];
		$rType = $at['attackType'];
		$rTarget = $at['attackTarget'];
		$rPrefix = $at['attackPrefix'];
	} else {
		$_SESSION['alertMessages']['systemErrors'][] = "No scoreLookupMode set in noAfterblowScoring()";
		return;
	}
	
	if($_POST['attackModifier'] == 9){
		$rPrefix = (int)$_POST['attackModifier'];
		$scoreValue += getControlPointValue();
	}
	
	insertLastExchange($matchInfo, 'clean', $rosterID, $scoreValue,
						$scoreDeduction, $rPrefix, $rType, $rTarget, $exchangeID);

}

/******************************************************************************/

function checkSession(){
// Corrects and possible error conditions in the current session
// Checks that the event/tournament/match referenced by SESSION actualty exists
	
	$eventID = $_SESSION['eventID'];
	$tournamentID = $_SESSION['tournamentID'];
	$matchID = $_SESSION['matchID'];
	
// Checks if the event in SESSION exists
	if($eventID == null){
		$_SESSION['tournamentID'] = null;
		$_SESSION['matchID'] = null;
		return;
	}		
	
	$sql = "SELECT eventID
			FROM systemEvents
			WHERE eventID = {$eventID}";
	$result = mysqlQuery($sql, SINGLE, null);

	if($result == null){
		$_SESSION['eventID'] = null;
		$_SESSION['tournamentID'] = null;
		$_SESSION['matchID'] = null;
		return;
	}
	
// Checks if the tournament in SESSION exists
	if($tournamentID == null){
		$_SESSION['matchID'] =null;
		return;
	}	
	
	$sql = "SELECT tournamentID
			FROM eventTournaments
			WHERE eventID = {$eventID}
			AND tournamentID = {$tournamentID}";
	$result = mysqlQuery($sql, SINGLE, null);

	if($result == null){
		$_SESSION['tournamentID'] = null;
		$_SESSION['matchID'] = null;
		return;
	}

// Checks if the match in SESSION exists
	if($matchID == null){
		return;
	}
	
	$sql = "SELECT matchID
			FROM eventMatches
			WHERE matchID = {$matchID}";
	$result = mysqlQuery($sql, SINGLE, null);
	
	if($result == null){
		$_SESSION['matchID'] = null;
		return;
	}
	
}

/******************************************************************************/

function changeEvent($eventID = null, $loggingIn = false){
// Changes event to the parameter provided and redirects to a
// landing page determined by the login type
	
	if($eventID == null){ $eventID =$_POST['changeEventTo']; }
	if($_SESSION['eventID'] != $eventID){ $eventChanged = true; }

	$_SESSION['eventID'] = $eventID;
	$_SESSION['eventName'] = getEventName($eventID);
		
	unset($_SESSION['tournamentID']);
	unset($_SESSION['matchID']);
	unset($_SESSION['groupSet']);
	
	// If there is only one tournament in the event it is selected
	$IDs = getEventTournaments();
	if(count($IDs) == 1){
		$_SESSION['tournamentID'] = $IDs[0];
	}

// Page re-directs
	
	if($loggingIn == false){
		// These user-types retain page focus when switching events
		// unless useLandingPage is enabled
		if($_SESSION['userType'] == USER_SUPER_ADMIN 
		|| $_SESSION['userType'] == USER_VIDEO
		|| $_SESSION['userType'] == USER_STATS){
			return;
		} elseif($eventChanged) {
			$_SESSION['userType'] = USER_GUEST;
		}
	}
	
	
	switch ($_SESSION['userType']) {
		case USER_ADMIN:
			$landingPage = 'statsEvent.php';
			break;
		case USER_STAFF:
			$landingPage ='participantsRoster.php';
			break;
		case USER_VIDEO:
			//$landingPage = VIDEO PAGE PLACEHOLDER
			return;
			break;
		case USER_STATS:
			$landingPage = 'statsFighters.php';
			break;
		default:
			if($_SESSION['eventID'] == null){$landingPage = 'infoSelect.php';
			} else {$landingPage = 'infoSummary.php';}
			break;
	}

	header("Location: {$landingPage}");
	exit;

}

/******************************************************************************/

function logUserIn(){
// Attempts to log a user in	
	
	$type = $_POST['logInType'];
	$passwordInput = $_POST['password'];
	$eventID = $_POST['logInEventID'];
	if($type == USER_STATS || $type == USER_VIDEO || $type == USER_SUPER_ADMIN){
		$eventID = null;
	}
	
	if(checkPassword($passwordInput, $type, $eventID)){
		$_SESSION['userType'] = $type;
		//define("USER_TYPE", $_SESSION['userType']);
		changeEvent($eventID, true);
	} else {
		$_SESSION['alertMessages']['userErrors'][] = "Incorrect Password<BR>Failed to Log In";
	}
	
	return;
}

/******************************************************************************/

function checkGroupOrders($tournamentID, $groupID = null){
// Checks that groups are ordered correctly. This includes checking that the
// fighters are ordered within their pools (no gaps) and that pools and
// rounds are sequential, with no gaps inbetween, and have an appropriate name
// for their position.

	if($groupID == null){
		if(isPools($tournamentID)){
			$pools = getPools($tournamentID, 'all');
		} else {
			$rounds = getRounds($tournamentID);
		}
	} else {
		$pools = getGroupInfo($groupID);
	}
	
// Check that fighters are ordered sequentialy in their pools

	$poolRosters = getPoolRosters($tournamentID, 'all');

	foreach((array)$pools as $pool){
		
		$groupID = $pool['groupID'];
		$poolRoster = $poolRosters[$groupID];
		$i=0;
		foreach((array)$poolRoster as $poolPosition => $fighter){
			$i++;
			if($poolPosition == $i){continue;}
			$tableID = $fighter['tableID'];
			$sql = "UPDATE eventGroupRoster
					SET poolPosition = {$i}
					WHERE tableID = {$tableID}";
			mysqlQuery($sql, SEND, null);
			
		}	
	}	
	
	unset($i);
	if($groupID != null){
		$pools = getPools($tournamentID, 'all');
	}
	
	$itemNumber = 0;
//Check the pools are numbered sequentialy.
	foreach((array)$pools as $poolData){
		
		$poolSet = $poolData['groupSet'];
		$groupNumbers[$poolSet]++;
		$poolNum = $groupNumbers[$poolSet];
		$groupID = $poolData['groupID'];

		$sql = "SELECT groupNumber, groupName
				FROM eventGroups
				WHERE groupID = {$groupID}";
		$data = mysqlQuery($sql, SINGLE);
		$oldNumber = $data['groupNumber'];
		$oldName = $data['groupName'];

		if($oldName ==  "Pool {$oldNumber}"){
			$name = "Pool {$poolNum}";
		} else {
			$name = $oldName;
		}
			
		$sql = "UPDATE eventGroups 
				SET groupNumber = {$poolNum}, groupName = '{$name}'
				WHERE groupID = {$groupID}";
		mysqlQuery($sql,SEND);
	}
	
// Check if rounds are numbered sequentialy.
	$groupNumber = 0;

	foreach((array)$rounds as $roundData){
		
		$groupSet = $roundData['groupSet'];
		$groupNumbers[$groupSet]++;
		$groupNumber = $groupNumbers[$groupSet];
		$groupID = $roundData['groupID'];

		$sql = "SELECT groupNumber, groupName
				FROM eventGroups
				WHERE groupID = {$groupID}";
		$data = mysqlQuery($sql, SINGLE);
		$oldNumber = $data['groupNumber'];
		$oldName = $data['groupName'];
		
		if($oldName ==  "Round {$oldNumber}"){
			$name = "Round {$groupNumber}";
		} else {
			$name = $oldName;
		}
		
		$sql = "UPDATE eventGroups 
				SET groupNumber = {$groupNumber}, groupName = '{$name}'
				WHERE groupID = {$groupID}";
		mysqlQuery($sql,SEND);
		
		// Check 
		
		
	}

}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

