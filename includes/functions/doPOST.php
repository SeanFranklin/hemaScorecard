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


	///////////////////////////////////////////////////////////////////////
	/* For debugging, commented out for regular use ///
	$refreshPage = false;
	define('SHOW_POST', true);	$_SESSION['post'] = $_POST;
	//define('SHOW_SESSION', true);
	//////////////////////////////////////////////////////////////////////*/

	if(isset($_POST['formName'])){

		// Refresh page after POST processing complete to prevent resubmits
		if(!isset($refreshPage)){
			$refreshPage = true;
		}
	
		$formName = $_POST['formName'];
		unset($_POST['formName']);

		checkSession();
		switch($formName){
		
	// Navigation Cases
			case 'logUserIn':
				logUserIn($_POST['logInData']);
				break;
			case 'logUserOut':
				logUserOut();
				break;
			case 'selectEvent':
				changeEvent((int)$_POST['changeEventTo']);
				break;
			case 'changeTournament':
				changeTournament($_POST['newTournament']);
				if(isset($_POST['newPage'])){
					changePage($_POST['newPage']);
				}
				break;
			case 'navigatePage':
				changePage($_POST['newPage']);
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
			case 'changeSortType':
				$_SESSION[$_POST['sortWhat']] = $_POST['sortHow'];
				break;
			case 'personalSchedule':
				$_SESSION['rosterID'] = (int)$_POST['rosterID'];
				header("Location: participantsSchedules.php");
				exit;
				break;
			case 'displayByPoolsToggle':
				$_SESSION['displayByPool'] = !$_SESSION['displayByPool'];
				break;
			case 'eventNavigation':
				$link = $_POST['eventNavigation'];
				header("Location: {$link}");
				exit;
				break;
			case 'changeRosterID':
				$_SESSION['rosterID'] = (int)$_POST['rosterID'];
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
			case 'updateFighterRatings':
				updateFighterRatings($_POST['updateRatings']);
				break;
			case 'editEventParticipant':
				editEventParticipant();
				break;
			case 'importRosterCSV':
				importRosterCSV();
				break;
			case 'createNewTeam':
				createNewTeam($_POST['newTeamInfo']);
				break;
			case 'deleteTeams':
				deleteTeams($_POST['deleteTeamsInfo']);
				break;
			case 'addToTeams':
				foreach($_POST['updateTeams'] as $teamInfo){
					addTeamMembers($teamInfo);
				}
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
			case 'changePoolSet':
				$_SESSION['groupSet'] = $_POST['groupSet'];
				break;
			case 'generatePools':
				pool_GeneratePools($_POST['generatePools']);
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
			case 'updateRankByPool':
				updateRankByPool($_POST['rankByPool']);
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
			case 'ignorePastIncompletes':
				$_SESSION['clearOnLogOut']['ignorePastIncompletes'] = true;
				if(isset($_POST['matchID'])){$_SESSION['matchID'] = $_POST['matchID'];}
				break;
			
					
	// Finals
			case 'changeBracketView':
				$_SESSION['bracketView'] = (int)$_POST['bracketView'];
				break;
			case 'createBracket':
				createTournamentBrackets($_POST['createBracket']);
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
			case 'createTrueDoubleElim':
				extendFinalsBracket($_POST['tournamentID']);
				break;
			case 'removeTrueDoubleElim':
				contractFinalsBracket($_POST['tournamentID']);
				break;
			case 'deleteBracket':
				deleteBracket();
				break;
			case 'toggleBracketHelper':
				toggleBracketHelper();
				break;
				
				
	// Event Organzer Cases
			case 'ignoreFightersInTournament':
				updateIgnoredFighters($_POST['manageFighters']);
				break;
			case 'checkInFighters':
				checkInFighters($_POST['checkInFighters']);
				break;
			case 'addNewSchool':
				addNewSchool();
				break;
			case 'updatePasswords':
				updatePasswords($_POST['changePasswords']);
				break;
			case 'updateTournamentComponents':
				updateTournamentComponents($_POST['compositeTournament']);
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
			case 'editTournamentPlacings':
				$_SESSION['manualPlacing']['tournamentID'] = $_POST['tournamentID'];
				$_SESSION['jumpTo'] = "anchor{$_POST['tournamentID']}";
				break;
			case 'finalizeTournament':
			case 'autoFinalizeTournament':
				$tID = $_POST['tournamentID'];
				if($tID == 0){
					break;
					// Do nothing. It is a cancel request.
				} elseif($formName == 'finalizeTournament'){
					recordTournamentPlacings($tID,$_POST['tournamentPlacings']);
				} else {
					if(isset($_POST['autoFinalizeSpecs']) == true){
						$specs = $_POST['autoFinalizeSpecs'];
					} else {
						$specs = [];
					}
					generateTournamentPlacings($tID, $specs);
				}
				checkCompositeTournaments($tID);
				$_SESSION['jumpTo'] = "anchor{$_POST['tournamentID']}";
				break;
			case 'finalizeTournament-no':
				$_SESSION['jumpTo'] = "anchor{$_POST['tournamentID']}";
				break;
			case 'removeTournamentPlacings':
				removeTournamentPlacings($_POST['tournamentID']);
				checkCompositeTournaments($_POST['tournamentID']);
				$_SESSION['jumpTo'] = "anchor{$_POST['tournamentID']}";
				break;
			case 'goToPointsPage':
				changeTournament($_POST['modifyTournamentID']);
				changePage('adminPoints.php');
				break;
			case 'addAttackTypes':
				addAttacksToTournament();
				break;
			case 'eventStatusUpdate':
				editEventStatus();
				break;
			case 'displaySettings':
				updateDisplaySettings();
				break;
			case 'staffRegistrationSettings':
				updateStaffRegistrationSettings($_POST['eventSettings']['staffRegistration']);
				break;
			case 'setContactEmail':
				updateContactEmail($_POST['contactEmail'],$_SESSION['eventID']);
				break;
			case 'setEventInfo':
				updateEventInformation($_POST['newEventInfo'],$_SESSION['eventID']);
				break;
			case 'SubmitToS':
				processToS($_POST['ToS']);
				break;


	// Admin Cases
			case 'editExistingSchool':
				updateExistingSchool();
				break;
			case 'addNewEvent':
				addNewEvent();
				break;
			case 'editEvent':
				editEvent();
				break;
			case 'addTournamentType':
				addTournamentType();
				break;
			case 'addNewDuplicateException':
				addNewDuplicateException();
				break;
			case 'combineDuplicateFighters':
				combineSystemRosterIDs($_POST['combineInto'],$_POST['rosterIDs']);
				break;
			case 'duplicateNameSearchType':
				$_SESSION['duplicateNameSearchType'] = 	$_POST['searchType'];
				break;
			case 'HemaRatingsList':
				$_SESSION['HemaRatingsBounds'] = $_POST['HemaRatingsBounds'];
				break;
			case 'HemaRatingsUpdate':
				updateHemaRatingsInfo($_POST['systemRosterID']);
				break;

	// Logistics Cases
			case 'editLocations':
				logisticsEditLocations($_POST['editLocationInformation']);
				break;
			case 'deleteLocations':
				logisticsDeleteLocations($_POST['locationsToDelete']);
				break;
			case 'assignGroupsToRings':
				logisticsAssignTournamentToRing($_POST['assignToLocations']);
				break;
			case 'assignMatchesToLocations':
				logisticsAssignTournamentToRing($_POST['selectedBracketMatches'],$_POST['locationID']);
				break;
			case 'editScheduleBlock':	
				logisticsEditScheduleBlock($_POST['editScheduleBlock']);		
				break;
			case 'deleteScheduleBlocks':
				logisticsDeleteScheduleBlocks(@$_POST['deleteScheduleBlocks']);
				break;
			case 'selectScheduleBlock':
				$_SESSION['blockID'] = (int)$_POST['blockID'];
				break;
			case 'editStaffList':
				logisticsEditStaffList($_POST['editStaffList']);
				break;
			case 'deleteStaffList':
				logisticsDeleteStaffList($_POST['editStaffList']);
				break;
			case 'editStaffShifts':
				logisticsEditStaffShifts($_POST['editStaffShifts'], $_SESSION['eventID']);
				break;
			case 'bulkStaffAssign':
				logisticsBulkStaffAssign($_POST['bulkStaffAssign']);
				break;
			case 'updateMatchStaff':
				logisticsCheckInMatchStaff($_POST['updateMatchStaff']);
				break;
			case 'updateMatchStaffFromShift':
				logisticsCheckInMatchStaffFromShift($_POST['updateMatchStaffFromShift']);
				break;
			case 'updateStaffTemplates':
				logisticsUpdateStaffTemplates($_POST['staffTemplateInfo']);
				break;
				
	// Stats Cases
			case 'dataFilters':
				setDataFilters();
				break;
			case 'HemaRatingsExport':
				exportHemaRatings($_POST['HemaRatingsExport']);
				break;
			case 'toggleStatsType':
				$_SESSION['StatsInfo']['displayType'] = $_POST['statsType']['display'];
				break;
			case 'tournamentDataFilters':
				$_SESSION['tDataFilters'] = $_POST['tDataFilter'];
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
	}

	if(isset($_SESSION['checkEvent'])){
		checkEvent();
		unset($_SESSION['checkEvent']);
	}
	
	if(isset($_SESSION['updatePoolStandings'])){
		
		foreach($_SESSION['updatePoolStandings'] as $tournamentID => $groupSet){
			updatePoolStandings($tournamentID, $groupSet);   //doPOST.php
		}
		unset($_SESSION['updatePoolStandings']);
	}

	unset($_POST);

	if(empty($refreshPage) == false){
		refreshPage();
	}

// Check that terms of use have been signed
	checkForTermsOfUse();

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
	if(!isset($_SESSION['checkEvent'])){
		return;
	}

	if(isset($_SESSION['checkEvent']['all']) && $_SESSION['checkEvent']['all'] === true){
		$tournamentIDs = getEventTournaments();
		foreach((array)$tournamentIDs as $tournamentID){
			$formatID = getTournamentFormat($tournamentID);

			checkGroupOrders($tournamentID);
			if(isPools($tournamentID)){
				updatePoolStandings($tournamentID, ALL_GROUP_SETS);
			}
			if($formatID == FORMAT_SOLO){
				updateRoundMatchList();
			}
		}
		updatePoolMatchList(null, 'event');
		unset($_SESSION['checkEvent']);
		return;
	}
	
	// If it has been specified to check only certain tournament in the event
	foreach($_SESSION['checkEvent'] as $tournamentID => $tournament){
		$formatID = getTournamentFormat($tournamentID);


		if(ctype_digit($tournamentID) || is_int($tournamentID)){
			$name = getTournamentName($tournamentID);
			
			// Check everything in the tournament
			if(isset($tournament['all']) && $tournament['all'] === true){
				if(isPools($tournamentID)){
					checkGroupOrders($tournamentID);
					updatePoolMatchList($tournamentID, 'tournament');
				}
				if($formatID == FORMAT_SOLO){
					
					checkGroupOrders($tournamentID);
					checkRoundRoster($tournamentID);
					updateRoundMatchList();
				}
				
			// Only check the pool orders/names	
			} elseif(isset($tournament['order']) && $tournament['order'] === true){
				checkGroupOrders($tournamentID);
			
			// Only check specified groups
			} else {
				foreach($tournament as $groupID => $groupOp){				
					if(isPools($tournamentID)){
						checkGroupOrders($tournamentID, $groupID);
						updatePoolMatchList($groupID, 'pool', $tournamentID);
					}
					if($formatID == FORMAT_SOLO){
						checkRoundRoster($tournamentID, $groupID);
						updateRoundMatchList();
					}
					
				}
			}	
			
			updatePoolStandings($tournamentID, ALL_GROUP_SETS);
		}
	}
	unset($_SESSION['checkEvent']);
	
}

/******************************************************************************/

function changeTournament($tournamentID){
	$_SESSION['tournamentID'] = $tournamentID;
	$_SESSION['matchID'] = '';
	$_SESSION['bracketHelper'] = '';
	$_SESSION['groupSet'] = 1;
}

/******************************************************************************/

function changePage($newPage){
	$newUrl = "Location: ".$newPage."#";
	header($newUrl);
	exit;
}

/******************************************************************************/

function exportHemaRatings($informationType){
	if($informationType == 'roster'){
		
		$fileName = createEventRoster_HemaRatings($_SESSION['eventID'], "exports/");

	} elseif(is_numeric($informationType)){

		$fileName = createTournamentResults_HemaRatings($informationType,"exports/");

	} else{

		$_SESSION['alertMessages']['systemErrors'][] = 'Invalid informationType provided to exportHemaRatings()';
		return;
	}

	uploadCsvFile($fileName);

}

/******************************************************************************/

function processToS($ToS){

	if(ALLOW['EVENT_MANAGEMENT'] == false){ return;}

	if(count($ToS['checkboxes']) < $ToS['numCheckboxes']){
		$_SESSION['alertMessages']['userErrors'][] = "Please completely fill in the Terms of Service agreement";
		return;
	}

	if(!filter_var($ToS['email'], FILTER_VALIDATE_EMAIL)){
		$_SESSION['alertMessages']['userErrors'][] = "That does not appear to be a valid e-mail";
		return;
	}

	$eventID = $_SESSION['eventID'];


	$sql = "UPDATE systemEvents
			SET termsOfUseAccepted = 1, organizerEmail = ?
			WHERE eventID = {$eventID}";

	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	// "s" means the database expects a string
	$bind = mysqli_stmt_bind_param($stmt, "s", $ToS['email']);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	

	$_SESSION['tosConfirmed'] = true;
	header("Location: adminEvent.php");
	exit;


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
		$_SESSION['bracketHelper'] = '';
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

function createTournamentBrackets($bracketSpecs){
// Creates a tournament bracket depending on the elimination type used
// Overwrites any existing brackets

	if(ALLOW['EVENT_MANAGEMENT'] == false){return;}

	// Import data
	$tournamentID 	= (int)$bracketSpecs['tournamentID'];
	$sizePrimary 	= (int)$bracketSpecs['sizePrimary'];
	$sizeSecondary 	= (int)$bracketSpecs['sizeSecondary'];
	$elimType	= (int)$bracketSpecs['elimType'];
	$extraPrimary = 0;
	$extraSecondary = 0;

	// Data validation
	if($tournamentID == 0){
		setAlert(SYSTEM,"No tournamentID in createTournamentBrackets().");
		return;
	}
	if($sizePrimary < 2){ 
		setAlert(USER_ERROR,"Can not create a bracket with less than 2 people!");
		return;
	}
	if($sizeSecondary > $sizePrimary){ 
		setAlert(USER_ERROR,"Secondary Bracket can no be bigger than the primary bracket.<BR>
							Secondary Bracket size has been adjusted to 
							<strong>Top {$sizePrimary}</strong> fighters.");
		$sizeSecondary = $sizePrimary;
		return;
	}

	if($elimType != ELIM_TYPE_SINGLE && $sizeSecondary == 0){
		$sizeSecondary = $sizePrimary;
	}


	if($elimType == ELIM_TYPE_SINGLE){
		if($sizePrimary >= 4){
			$sizeSecondary = 2;
		} else {
			$sizeSeocndary = 0;
		}
	} elseif($elimType == ELIM_TYPE_CONSOLATION){
		$sizeSecondary -= 2; // Remove the top two from the winners bracket

		if($sizeSecondary == 2){
			setAlert(USER_ALERT,"<strong>Note:</strong> A double elim bracket for Top 4 is the
									same as a single elim bracket with a Bronze Medal Match.");
		}

	} else {
		if($sizeSecondary != $sizePrimary){
			$sizeSecondary = $sizePrimary;
			setAlert(USER_ERROR,"For a True Double Elim bracket the size of the Secondary Bracket must 
								be the same as the size of the Primary Bracket.
								<BR>This has been automatically corrected for you.");
		}
		$sizeSecondary -= 2;
		$extraSecondary = 1;
		if($elimType == ELIM_TYPE_LOWER_BRACKET){
			$extraPrimary = 1;
		} else {
			$extraPrimary = 2;
		}

	}


	if($sizeSecondary != 0 && $sizeSecondary < 2){
		setAlert(USER_ERROR,"You can not create a Double Elimination Bracket for less than the
							Top 4 fighters.<BR>
							A <u>single elimination</u> bracket has been created.");
		$sizeSecondary = 0;
		$elimType = ELIM_TYPE_SINGLE;
	}

	// Create brackets
	createPrimaryBracket($tournamentID, $sizePrimary, $extraPrimary);
	if($sizeSecondary >= 2){
		createSecondaryBracket($tournamentID, $sizeSecondary, $extraSecondary);
	}
	
}

/******************************************************************************/

function addNewExchange(){
// Add a new exchange to a match
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
	$tournamentID = $_SESSION['tournamentID'];
	if($tournamentID == null){return;}
	
	// Easter egg for anyone who gets the the Score Match page while they are in
	// event that has rounds and pieces rather than matches
	$formatID = getTournamentFormat($tournamentID);
	if($formatID == FORMAT_SOLO){
		$_SESSION['alertMessages']['userAlerts'][] = "<p>If you go against yourself with a 
			sharp sword and come out alive, are you a winner or a loser?</p>";
		return;
	} elseif($formatID != FORMAT_MATCH) {
		setAlert(SYSTEM,"Invalid formatID in addNewExchange()");
		return;
	}
	
	$matchID = $_SESSION['matchID'];
	if($matchID == null){return;}

	if(@$_POST['restartTimer'] == 1){ // May not exist. This is logical '0'.
		$_SESSION['restartTimer'] = 1;
	}
	
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
	
	if($_POST['lastExchange'] != 'clearLastExchange'){
		
		$matchCap = getMatchCaps($tournamentID);
		$matchInfo = getMatchInfo($matchID);

		$matchConcluded = false;
		if($matchCap['exchanges'] != 0){
			if(shouldMatchConcludeByExchanges($matchInfo, $matchCap['exchanges']) == true){
				autoConcludeMatch($matchInfo);
				$matchConcluded = true;
			}
		}
		if($matchConcluded == false && $matchCap['points'] != 0){
			if(shouldMatchConcludeByPoints($matchInfo, $matchCap['points']) == true){
				autoConcludeMatch($matchInfo);
				$matchConcluded = true;
			}
		}

		if($matchConcluded == false && $matchInfo['matchTime'] >= $matchCap['timeLimit']){
			if(shouldMatchConcludeByTime($matchInfo) == true){
				autoConcludeMatch($matchInfo);
				$matchConcluded = true;
			}
		}

		if($matchConcluded == true){
			updateMatch($matchInfo);
		}
	}

	
}

/******************************************************************************/

function calculateLastExchange($matchInfo, $scoring){
// determine which type of scoring is in effect so the exchange 
// can be added appropriately

	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
	$tournamentID = $matchInfo['tournamentID'];
	
	$scoring = array_filter_recursive($scoring);

	$matchID = $matchInfo['matchID'];
	$id1 =$matchInfo['fighter1ID'];
	$id2 = $matchInfo['fighter2ID'];
	
	// Return if there was no initial hit for either fighter
	// Suppress error because not existing is logically the same as 0 or null.
	if(@!$scoring[$id1]['hit'] AND @!$scoring[$id2]['hit']){
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

	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
	$matchID = $matchInfo['matchID'];
	$id1 =$matchInfo['fighter1ID'];
	$id2 = $matchInfo['fighter2ID'];
	$exchangeID = $scoring['exchangeID'];
	$rPrefix = null;
	$rType = null;
	$rTarget = null;
	
// Doesn't do anything if a score was entered in both boxes
	if($scoring[$id1]['hit'] && $scoring[$id2]['hit']){
		return;
	}

// Determine which fighter hit
	if($scoring[$id1]['hit'] != ''){
		if($scoring[$id2]['hit'] != ''){
			// Score entered for both fighters. Not a valid exchange.
			return;
		} else {
			$rosterID = $id1;
			$otherID = $id2;
		}
	} elseif($scoring[$id2]['hit']){
		$rosterID = $id2;
		$otherID = $id1;
	} else {
		return;
	}
	
// Base score value
	if($_POST['scoreLookupMode'] == 'rawPoints'){
		$scoreValue = abs($scoring[$rosterID]['hit']);
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
	
	if(isset($_POST['attackModifier']) && $_POST['attackModifier'] == ATTACK_CONTROL_DB){
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

	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}

	$matchID = $matchInfo['matchID'];
	$id1 =$matchInfo['fighter1ID'];
	$id2 = $matchInfo['fighter2ID'];
	$exchangeID = $scoring['exchangeID'];
	
	if($scoring[$id1]['penalty'] != 0){
		$scoreValue = $scoring[$id1]['penalty'];
		$rosterID = $id1;
		insertLastExchange($matchInfo, 'penalty', $rosterID, $scoreValue, 
							0, null, null, null, $exchangeID);
	}
	
	if($scoring[$id2]['penalty'] != 0){
		$scoreValue = $scoring[$id2]['penalty'];
		$rosterID = $id2;
		insertLastExchange($matchInfo, 'penalty', $rosterID, $scoreValue, 
							0, null, null, null, $exchangeID);
	}
}


/******************************************************************************/

function fullAfterblowScoring($matchInfo,$scoring){
// scoring calculations for Full Afterblow	
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}
	
	$matchID = $matchInfo['matchID'];
	$id1 =$matchInfo['fighter1ID'];
	$id2 = $matchInfo['fighter2ID'];
	$exchangeID = $scoring['exchangeID'];

	$scoreValue = null;
	$scoreDeduction = null;
	$rPrefix = null;
	$rTarget = null;
	$rType = null;
	$afterblowPrefix = null;
	
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

		if($at1['attackPrefix'] == ATTACK_AFTERBLOW_DB){
			$afterblowPrefix = 1;
		} elseif($at2['attackPrefix'] == ATTACK_AFTERBLOW_DB){
			$afterblowPrefix = 2;
		}

	} else {
		$_SESSION['alertMessages']['systemErrors'][] = "No scoreLookupMode in fullAfterblowScoring()";
		return;
	}

	//checks if only one fighter hit
	if(xorWithZero($score1,$score2)){//only one hitter
		if($score1){
			$rosterID = $id1;
			$otherID = $id2;
		} else {
			$rosterID = $id2;
			$otherID = $id1;
		}
		$scoreValue = 	$scoring[$rosterID]['hit'];
		$scoreDeduction = 'null';
		$exchangeType = 'clean';

		if(@$_POST['attackModifier'] == ATTACK_CONTROL_DB){
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

		// Manages afterblows
		// If the higher scoring fighter has the afterblow prefix it adds
		// it to the exchange. If the lower scoring fighter had it, they 
		// would have had the afterblow anyways.
		if($rPrefix == null){
			if($rosterID == $id1 && $afterblowPrefix == 1){
				$rPrefix = ATTACK_AFTERBLOW_DB;
			} elseif($rosterID == $id2 && $afterblowPrefix == 2){
				$rPrefix = ATTACK_AFTERBLOW_DB;
			}
		}

		$scoreValue = 	$scoring[$rosterID]['hit'];
		$scoreDeduction = $scoring[$otherID]['hit'];
		$exchangeType = 'afterblow';
		
		// sets the score deduction to the string 'null' for SQL storage
		if($scoreDeduction == ""){$scoreDeduction = 'null';}
		
	}

	if(isReverseScore($matchInfo['tournamentID']) > REVERSE_SCORE_NO){
		$rosterID = $otherID;
	}
	
	
	insertLastExchange($matchInfo, $exchangeType, $rosterID, $scoreValue, 
		$scoreDeduction, $rPrefix, $rType, $rTarget, $exchangeID);
}

/******************************************************************************/

function noAfterblowScoring($matchInfo,$scoring){
// scoring calculations for No Afterblow

	if(ALLOW['EVENT_SCOREKEEP'] == false){return;}

	$matchID = $matchInfo['matchID'];
	$id1 = $matchInfo['fighter1ID'];
	$id2 = $matchInfo['fighter2ID'];

	$exchangeID = $scoring['exchangeID'];
	$scoreValue = null;
	$scoreDeduction = null;
	$rPrefix = null;
	$rType = null;
	$rTarget = null;
	
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
			$otherID = $id2;
		}
	} elseif($scoring[$id2]['hit']){
		$rosterID = $id2;
		$otherID = $id1;
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

	if(isset($_POST['attackModifier']) && $_POST['attackModifier'] == ATTACK_CONTROL_DB){
		$rPrefix = (int)$_POST['attackModifier'];
		$scoreValue += getControlPointValue();
	}

	if(isReverseScore($matchInfo['tournamentID']) > REVERSE_SCORE_NO){
		$rosterID = $otherID;
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
	$matchID = (int)$matchID;
	
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

function changeEvent($eventID, $logoutInhibit = false){
// Changes event to the parameter provided and redirects to a
// landing page determined by the login type
	
	if($_SESSION['eventID'] == $eventID){
		$eventChanged = false;
	} else {

		$eventChanged = true;
		$_SESSION['eventID'] = $eventID;
		$_SESSION['eventName'] = getEventName($eventID);
		
		// If there is only one tournament in the event it is selected by deafult
		$IDs = getEventTournaments();
		if(count($IDs) == 1){
			$_SESSION['tournamentID'] = $IDs[0];
		} else {
			$_SESSION['tournamentID'] = '';
		}
		$_SESSION['matchID'] = '';
		$_SESSION['groupSet'] = '';
		$_SESSION['scheduleID'] = '';
		$_SESSION['rosterID'] = '';
		$_SESSION['blockID'] = '';
		$_SESSION['dayNum'] = logistics_getCurrentDayNum($_SESSION['eventID']);

		// Log user out if switching event
		if($logoutInhibit == true || ALLOW['SOFTWARE_EVENT_SWITCHING'] == true){
			// User can stay logged in
		} else {
			logUserOut(false);
		}

	}

// Page re-directs

	// Default landing page
	if($_SESSION['eventID'] == null){
		$landingPage = 'infoWelcome.php';
	} else {
		$landingPage = 'infoSummary.php';
	}

	if(ALLOW['SOFTWARE_EVENT_SWITCHING'] == true){
		if(basename($_SERVER['PHP_SELF']) == "adminLogin.php"){
			$landingPage = 'infoSelect.php';
		} elseif(basename($_SERVER['PHP_SELF']) == "infoSelect.php"){
			$landingPage = 'infoSummary.php';
		} else {
			$landingPage = null;
		}
	}


	if($landingPage != null){
		header("Location: {$landingPage}");
		exit;
	} else {
		refreshPage();
	}

}

/******************************************************************************/

function logUserIn($logInData){
// Attempts to log a user in	
	
	$type = $logInData['type'];
	$passwordInput = $logInData['password'];
	
	if($type == 'logInUser'){
		$eventID = null;
		$userName = $logInData['userName'];
	} elseif($type == 'logInStaff'){
		$userName = "eventStaff";
		$eventID = $logInData['eventID'];
	} elseif($type == 'logInOrganizer'){
		$userName = "eventOrganizer";
		$eventID = $logInData['eventID'];
	} else {
		logUserOut();
	}

	if(checkPassword($passwordInput, $userName, $eventID) === true){

		unset($_SESSION['clearOnLogOut']);
		$_SESSION['userName'] = $userName;

		changeEvent($eventID, true);
	} else {
		if($type == 'logInUser'){
			setAlert(USER_ERROR,"Invalid Username/Password combination<BR>
									<strong>Failed to Log In.</strong>");
		} else {
			setAlert(USER_ERROR,"Incorrect Password<BR>
								<strong>Failed to Log In</strong>");
		}
		$_SESSION['failedLogIn']['type'] = $type;
		$_SESSION['failedLogIn']['eventID'] = $eventID;
	}

	return;
}

/******************************************************************************/

function logUserOut($refreshPage = true){
	unset($_SESSION['clearOnLogOut']);

	$_SESSION['userName'] = null;
	$_SESSION['userID'] = null;

	if($refreshPage == true){
		header("Location: infoWelcome.php");
		exit;
	}
}

/******************************************************************************/

function checkGroupOrders($tournamentID, $groupID = null){
// Checks that groups are ordered correctly. This includes checking that the
// fighters are ordered within their pools (no gaps) and that pools and
// rounds are sequential, with no gaps inbetween, and have an appropriate name
// for their position.

	$pools = [];
	$rounds = [];

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

	foreach($pools as $pool){
		
		$groupID = $pool['groupID'];
		if(isset($poolRosters[$groupID])){
			$poolRoster = $poolRosters[$groupID];
			$i=0;
			foreach($poolRoster as $poolPosition => $fighter){
				$i++;
				if($poolPosition == $i){continue;}
				$tableID = $fighter['tableID'];
				$sql = "UPDATE eventGroupRoster
						SET poolPosition = {$i}
						WHERE tableID = {$tableID}";
				mysqlQuery($sql, SEND, null);
				
			}	
		}
	}	
	
	$i = 0;
	if($groupID != null){
		$pools = getPools($tournamentID, 'all');
	}
	
	$itemNumber = 0;
//Check the pools are numbered sequentialy.
	foreach($pools as $poolData){
		
		$poolSet = $poolData['groupSet'];

		if(!isset($groupNumbers[$poolSet])){
			$groupNumbers[$poolSet] = 0;
		}
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

	if(!isset($rounds)){
		$rounds = [];
	}

	foreach($rounds as $roundData){
		
		$groupSet = $roundData['groupSet'];
		if(!isset($groupNumbers[$groupSet])){
			$groupNumbers[$groupSet] = 0;
		}
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

