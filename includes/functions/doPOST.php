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
	if(ALLOW['SOFTWARE_ADMIN'] == true){
		$refreshPage = false;
		define('SHOW_POST', true);
		define('SHOW_URL_NAV', false);
		$_SESSION['post'] = $_POST;
	}
	//define('SHOW_SESSION', true);
//////////////////////////////////////////////////////////////////////*/

	$urlComponents = parse_url(basename($_SERVER['REQUEST_URI']));

// Evaluate POST form submitted
	if(isset($_POST['formName']) != false){

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
				changeEvent((int)$_POST['changeEventTo'], false);
				break;
			case 'changeTournament':
				changeTournament($_POST['newTournament']);
				if(isset($_POST['newPage'])){
					refreshPage($_POST['newPage']);
				}
				break;
			case 'navigatePage':
				refreshPage($_POST['newPage']);
				break;
			case 'goToMatch':
				$_SESSION['matchID'] = $_POST['matchID'];
				refreshPage('scoreMatch.php');
				break;
			case 'goToPiece':
				$_SESSION['matchID'] = $_POST['matchID'];
				refreshPage('scorePiece.php');
				break;
			case 'changeSortType':
				$_SESSION[$_POST['sortWhat']] = $_POST['sortHow'];
				break;
			case 'personalSchedule':
				$_SESSION['rosterID'] = (int)$_POST['rosterID'];
				refreshPage('participantsSchedules.php');
				break;
			case 'displayByPoolsToggle':
				$_SESSION['displayByPool'] = !$_SESSION['displayByPool'];
				break;
			case 'eventNavigation':
				refreshPage($_POST['eventNavigation']);
				break;
			case 'changeRosterID':
				$_SESSION['rosterID'] = (int)$_POST['rosterID'];
				break;
			case 'changeRulesID':
				$_SESSION['rulesID'] = (int)$_POST['changeRules']['rulesID'];
				break;
			case 'filterForSystemRosterID':
				$_SESSION['filterForSystemRosterID'] = (int)$_POST['systemRosterID'];
				break;
			case 'setDataFilters':
				setDataFilters($_POST['filters']);
				break;

	// Roster Management Cases
			case 'addEventParticipants':
				if(empty($_POST['newParticipants']) == false){
					addEventParticipants($_SESSION['eventID'], $_POST['newParticipants']);
				}

				break;
			case 'addAdditionalParticipants':
				addAdditionalParticipants($_POST['addAdditional']);
				break;
			case 'updateAdditionalParticipants':
				updateAdditionalParticipants($_POST['updateAdditional']);
				break;
			case 'deleteAdditionalParticipants':
				deleteAdditionalParticipants($_POST['deleteAdditional']);
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
			case 'importTournamentRoster':
				importTournamentRoster($_POST['importTournamentRoster']);
				break;
			case 'updateFighterRatings':
				updateFighterRatings($_POST['updateRatings']);
				break;
			case 'editEventParticipant':
				editEventParticipant();
				break;
			case 'editSystemParticipant':
				editSystemParticipant($_POST['editSystemParticipant']);
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
					addTeamMembers($teamInfo, $_SESSION['tournamentID']);
				}
				break;
			case 'divSeedingByRating':
				divSeedingByRating($_POST['divSeeding']);
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
			case 'swapMatchFighters':
				swapMatchFighters($_POST['swapMatchFighters']['matchID']);
				break;
			case 'switchActiveFighters':
				switchActiveFighters($_POST['activeFighters']);
				break;
			case 'updateSubMatchesByMatch':
				updateSubMatchesByMatch($_POST['updateSubMatchesByMatch']);
				break;
			case 'updateVideoSource':
				updateVideoSource($_POST['updateVideoSource']);
				break;
			case 'ignorePastIncompletes':
				$_SESSION['clearOnLogOut']['ignorePastIncompletes'] = true;
				if(isset($_POST['matchID'])){$_SESSION['matchID'] = $_POST['matchID'];}
				break;
			case 'signOffFighters':
				signOffFighters($_POST['signOffInfo']);
				break;
			case 'flipMatchSides':
				$_SESSION['flipMatchSides'] = !(@(bool)$_SESSION['flipMatchSides']); //If it doesn't exist it is logically the same as false
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
					refreshPage("scoreMatch.php");
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


	// Meta-Tournament Cases
			case 'changeSourceMetaEvent':
				$_SESSION['metaTournamentComponentSource'] = $_POST['changeEventSource']['eventID'];
				break;
			case 'updateMetaTournamentComponents':
				updateMetaTournamentComponents($_POST['sourceTournaments']);
				break;
			case 'updateMetaTournamentSettings':
				updateMetaTournamentSettings($_POST['metaTournament']);
				break;
			case 'updateMetaStandings':
				updateMetaTournamentStandings($_POST['updateMetaStandings']['tournamentID']);
				break;
			case 'addTournamentComponentGroup':
				addTournamentComponentGroup($_POST['addComponentGroup']);
				break;
			case 'updateTournamentComponentGroups':
				updateTournamentComponentGroups($_POST['updateTournamentComponentGroups']);
				break;
			case 'deleteTournamentComponentGroups':
				deleteTournamentComponentGroups($_POST['deleteTournamentComponentGroups']);
				break;
			case 'burgeeInfo':
				updateBurgeeInfo($_POST['burgeeInfo']);
				break;
			case 'deleteBurgee':
				deleteBurgee($_POST['deleteBurgee']);
				break;

	// Event Organzer Cases
			case 'ignoreFightersInTournament':
				updateIgnoredFighters($_POST['manageFighters']);
				break;
			case 'checkInFighters':
				checkInFighters($_POST['checkInFighters']);
				break;
			case 'checkInFighter':
				checkInFighters($_POST['checkInFighter']);
				break;
			case 'bulkCheckIn':
				bulkCheckIn($_POST['bulkCheckIn']);
				break;
			case 'addNewSchool':
				addNewSchool();
				break;
			case 'updatePasswords':
				updatePasswords($_POST['changePasswords']);
				break;
			case 'eventDefaultUpdate':
				updateEventDefaults();
				break;
			case 'updateTournamentInfo':
				updateEventTournaments(@$_POST['modifyTournamentID'], $_POST['updateType'], $_POST['updateTournament']);
				break;
			case 'deleteTournament':
				deleteEventTournament();
				break;
			case 'importTournamentSettings':
				importTournamentSettings($_POST['importTournamentSettings']);
				break;
			case 'importTournamentAttacks':
				importTournamentAttacks($_POST['importTournamentAttacks']);
				break;
			case 'editTournamentPlacings':
				$_SESSION['manualPlacing']['tournamentID'] = $_POST['tournamentID'];
				$_SESSION['jumpTo'] = "anchor{$_POST['tournamentID']}";
				break;
			case 'finalizeTournament':
			case 'autoFinalizeTournament':
				$tID = (int)$_POST['tournamentID'];
				if($tID == 0){
					// Do nothing. It is a request to exit editing mode.
					break;
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
				$_SESSION['jumpTo'] = "anchor{$_POST['tournamentID']}";
				break;
			case 'finalizeTournament-no':
				$_SESSION['jumpTo'] = "anchor{$_POST['tournamentID']}";
				break;
			case 'removeTournamentPlacings':
				removeTournamentPlacings($_POST['tournamentID']);
				$_SESSION['jumpTo'] = "anchor{$_POST['tournamentID']}";
				break;
			case 'goToPointsPage':
				changeTournament($_POST['modifyTournamentID']);
				refreshPage('adminExchangeTypes.php');
				break;
			case 'addAttackTypes':
				addAttacksToTournament();
				break;
			case 'tournamentAttackModifiers':
				tournamentAttackModifiers($_POST['tournamentAttackModifiers'],$_SESSION['tournamentID']);
				break;
			case 'switchAttackDefinitionMode':
				switchAttackDefinitionMode($_POST['attackDefinitionMode'],$_SESSION['tournamentID']);
				break;
			case 'updateEventPublication':
				updateEventPublication($_POST['publicationSettings'],$_SESSION['eventID']);
				break;
			case 'displaySettings':
				updateDisplaySettings($_POST['displaySettings']);
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
			case 'setEventDescription':
				updateEventDescription($_POST['eventDescription'],$_SESSION['eventID']);
				break;
			case 'SubmitToS':
				processToS($_POST['ToS']);
				break;
			case 'hemaRatings_UpdateEventInfo':
				hemaRatings_updateEventInfo($_POST['eventHemaRatings']);
				break;
			case 'updateRules':
				updateRules($_POST['updateRules']);
				break;
			case 'deleteRules':
				deleteRules($_POST['deleteRules']['rulesID']);
				break;
			case 'orderRules':
				orderRules($_POST['orderRules']);
				break;
			case 'updateEventSponsors':
				updateEventSponsors($_POST['sponsorList']);
				break;
			case 'updateTournamentDivisions':
				updateTournamentDivisions($_POST['divisionInfo']);
				break;
			case 'deleteTournamentDivision':
				deleteTournamentDivision($_POST['divisionInfo']);
				break;
			case 'suppressDirectEntry':
				updateSuppressDirectEntry($_POST['suppressDirectEntry']);
				break;


	// Admin Cases
			case 'editExistingSchool':
				updateExistingSchool();
				break;
			case 'deleteSchool':
				deleteSchool($_POST['schoolID']);
				break;
			case 'addNewEvent':
				addNewEvent($_POST['eventInfo']);
				break;
			case 'editEvent':
				editEvent($_POST['eventInfo']);
				break;
			case 'deleteEvent':
				deleteEvent($_POST['deleteEvent']);
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
			case 'hemaRatings_UpdateFighterIDs':
				hemaRatings_UpdateFighterIDs(@$_POST['hemaRatings']); // may be empty, that's ok.
				break;
			case 'plaintextMode':
				if (ALLOW['SOFTWARE_ADMIN'] == true && (int)$_POST['plaintextMode'] != 0){$_SESSION['forcePlainText'] = true;}
					else {unset($_SESSION['forcePlainText']);}
				break;
			case 'disablePenalties':
				disableEventPenalties($_POST['disablePenalties'], $_SESSION['eventID']);
				break;


	// Logistics Cases
			case 'editLocations':
				logisticsEditLocations($_POST['editLocationInformation']);
				break;
			case 'deleteLocations':
				logisticsDeleteLocations($_POST['locationsToDelete']);
				break;
			case 'uploadFloorplan':
				logisticsUploadFloorplan();
				break;
			case 'deleteFloorplan':
				logisticsdeleteEventFloorplan();
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
			case 'setFighterListColumns':
				$_SESSION['fighterListColumns'] = $_POST['fighterListColumns'];
				break;
			case 'setFighterQueueColumns':
				$_SESSION['fighterQueueColumns'] = $_POST['setFighterQueueColumns'];
				break;
			case 'updateAnnouncement':
				logisticsUpdateAnnouncement($_POST['announcement']);
				break;
			case 'hideAnnouncement':
				$_SESSION['hideAnnouncement'][(int)$_POST['announcementID']] = true;
				break;
			case 'instructorBio':
				InstructorBioUpdate($_POST['instructorBio']);
				break;
			case 'instructorDelete':
				InstructorDelete($_POST['instructorBio']);
				break;


	// Stats Cases
			case 'hemaRatings_ExportCsv':
				hemaRatings_ExportCsv($_POST['HemaRatingsExport']);
				break;
			case 'ferrotas_ExportCsv':
				ferrotas_ExportCsv($_POST['FerrotasExportTournamentID']);
				break;
			case 'toggleDataModes':
				if(isset($_POST['dataModes']['percent']) == true){
					$_SESSION['dataModes']['percent'] = (bool)(int)$_POST['dataModes']['percent'];
				}
				if(isset($_POST['dataModes']['extendedExchangeInfo']) == true){
					$_SESSION['dataModes']['extendedExchangeInfo'] = (bool)(int)$_POST['dataModes']['extendedExchangeInfo'];
				}
				break;
			case 'tournamentDataFilters':
				$_SESSION['tDataFilters'] = $_POST['tDataFilter'];
				break;
			case 'statsFilterData':
				$_SESSION['statsIDs']['systemRosterID'] 		= @(int)$_POST['statsIDs']['systemRosterID'];
				$_SESSION['statsIDs']['eventID'] 				= @(int)$_POST['statsIDs']['eventID'];
				$_SESSION['statsIDs']['tournamentWeaponID'] 	= @(int)$_POST['statsIDs']['tournamentWeaponID'];
				$_SESSION['statsIDs']['tournamentPrefixID'] 	= @(int)$_POST['statsIDs']['tournamentPrefixID'];
				$_SESSION['statsIDs']['tournamentGenderID']	= @(int)$_POST['statsIDs']['tournamentGenderID'];
				$_SESSION['statsIDs']['tournamentMaterialID'] 	= @(int)$_POST['statsIDs']['tournamentMaterialID'];
				$_SESSION['statsIDs']['tournamentRankingID'] 	= @(int)$_POST['statsIDs']['tournamentRankingID'];
				break;
			case 'updateActiveStatsItems':
				updateActiveStatsItems($_POST['activeStatsItems']);
				break;
			case 'statsAttendanceFilters':
				updateStatsAttendanceFilters($_POST['statsAttendanceFilters']);
				break;
			case 'eventRating':
				$_SESSION['eventRating']['textInput'] = $_POST['eventRating']['textInput'];
				break;
			case 'importEventRatingCSV':
				importEventRatingCSV();
				break;
			case 'statsYear':
				$_SESSION['stats']['year'] = (int)$_POST['stats']['year'];
				break;
			case 'updateSoftwareUpdates':
				updateSoftwareUpdates($_POST['updateSoftwareUpdates']);
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
				updateTournamentCuttingStandard($_SESSION['tournamentID']);
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


	// Video Cases
			case 'videoStreamSetLocations':
				videoStreamSetLocations($_POST['videoStreamSetLocations']);
				break;
			case 'videoStreamSetMatch':
				videoStreamSetMatch($_POST['videoStreamSetMatch']);
				break;


	// Default Cases
			case null:
				break;
			default:
				break;
		}

	} else if(isset($urlComponents['query']) == true) {

		// Check if they landed on a page with an event specified

		parse_str($urlComponents['query'], $urlParams);

		if(isset($urlComponents['path'])){
			$path = $urlComponents['path'];
		} else {
			$path = '';
		}

		$_SESSION['urlNav'] = $urlParams;

		$sessionUpdated = updateSessionByUrl($urlParams, $path);

		checkSession();

		if(!isset($refreshPage) && $sessionUpdated == true){
			$refreshPage = true;
		}

	} else {

		refreshPage();
		// No user input to change state, do nothing.
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
		//refreshPage();
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
				updateRoundMatchList($tournamentID);
			}
		}
		updatePoolMatchList(null, 'event');
		unset($_SESSION['checkEvent']);
		return;
	}

	if(isset($_SESSION['checkEvent']['placings']) && $_SESSION['checkEvent']['placings'] === true){
		unset($_SESSION['checkEvent']['placings']);
	}

	// If it has been specified to check only certain tournament in the event
	$mTournamentIDs = [];
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
					updateRoundMatchList($tournamentID);

				} elseif($formatID == FORMAT_META){

					$mTournamentIDs[] = $tournamentID;

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
						updateRoundMatchList($tournamentID);
					}

				}
			}

			updatePoolStandings($tournamentID, ALL_GROUP_SETS);
		}
	}
	updateTournamentFighterCounts(null, $_SESSION['eventID']);

	unset($_SESSION['checkEvent']);

}

/******************************************************************************/

function changeTournament($tournamentID, $matchID = 0){

	$_SESSION['tournamentID'] = (int)$tournamentID;
	$_SESSION['matchID'] = (int)$matchID;
	$_SESSION['bracketHelper'] = '';
	$_SESSION['groupSet'] = 1;


	if(isset($_SESSION['hideAnnouncement']) == true && $tournamentID != 0){

		foreach($_SESSION['hideAnnouncement'] as $announcementID => $on){

			$aID = (int)$announcementID;

			if((int)$aID < 0 && $aID != -$tournamentID){

				unset($_SESSION['hideAnnouncement'][$announcementID]);
			}

		}
	}


}

/******************************************************************************/

function updateSessionByUrl($urlParams, $urlPath = null){

	$urlEventID = @(int)$urlParams['e'];
	$urlTournamentID = @(int)$urlParams['t'];
	$urlMatchID = @(int)$urlParams['m'];
	$rulesID = @(int)$urlParams['r'];
	$systemRosterID = @(int)$urlParams['s'];
	if($rulesID != 0){
		$_SESSION['rulesID'] = $rulesID;
	}
	if($systemRosterID != 0){
		$_SESSION['filterForSystemRosterID'] = $systemRosterID;
	}

	if(isset($urlParams['e']) && $urlEventID != $_SESSION['eventID']){

		if(   isEventPublished($urlEventID) == true
		   || doesUserHavePermission($_SESSION['userID'],$urlEventID,'VIEW_HIDDEN')){

			changeEvent($urlEventID, false, $urlPath, $urlTournamentID, $urlMatchID);
		}

		$updated = true;

	} else if(isset($urlParams['t']) && $urlTournamentID != $_SESSION['tournamentID']) {

		changeTournament($urlTournamentID, $urlMatchID);
		$updated = true;

	} else if(isset($urlParams['m']) &&  $urlMatchID != $_SESSION['matchID']) {

		$_SESSION['matchID'] = $urlMatchID;
		$updated = true;

	} else {

		// Nothing to update
		$updated = false;
	}

	return $updated;

}

/******************************************************************************/

function ferrotas_ExportCsv($tournamentID){

	$tournamentID = (int)$tournamentID;

	if($tournamentID != 0){

		$fileName = ferrotas_createTournamentResultsCsv($tournamentID, EXPORT_DIR);
		uploadCsvFile($fileName);

	} else {
		$_SESSION['alertMessages']['systemErrors'][] = 'Invalid tournamentID provided to ferrotas_ExportCsv()';
	}

}

/******************************************************************************/

function hemaRatings_ExportCsv($informationType){

	if($informationType == 'all'){

		$csvNames[0] = hemaRatings_createEventRosterCsv($_SESSION['eventID'], EXPORT_DIR);
		uploadZipFile($csvNames);

	} elseif($informationType == 'eventInfo'){

		$fileName = hemaRatings_createEventInfoCsv($_SESSION['eventID'], EXPORT_DIR);
		uploadCsvFile($fileName);

	} elseif($informationType == 'roster'){

		$fileName = hemaRatings_createEventRosterCsv($_SESSION['eventID'], EXPORT_DIR);
		uploadCsvFile($fileName);

	} elseif(is_numeric($informationType)){

		$fileName = hemaRatings_createTournamentResultsCsv($informationType, EXPORT_DIR);
		uploadCsvFile($fileName);

	} else{

		$_SESSION['alertMessages']['systemErrors'][] = 'Invalid informationType provided to hemaRatings_ExportCsv()';
	}

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

	$eventID = (int)$_SESSION['eventID'];

	$sql = "UPDATE eventSettings
			SET termsOfUseAccepted = 1, organizerEmail = ?
			WHERE eventID = {$eventID}";

	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	// "s" means the database expects a string
	$bind = mysqli_stmt_bind_param($stmt, "s", $ToS['email']);
	$exec = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);


	$_SESSION['tosConfirmed'] = true;

	refreshPage("adminEvent.php");

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

	$incompletes = getTournamentPoolIncompletes($tournamentID);

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

function setDataFilters($filters){
// Sets the data filters for fighter analytics display

	if(isset($filters['schoolID']) == true){

		$_SESSION['filters']['school'] = false;
		unset($_SESSION['filters']['schoolID']);

		$alreadySet = [];
		foreach($filters['schoolID'] as $schoolID){
			$schoolID = (int)$schoolID;

			if($schoolID != 0 && isset($alreadySet[$schoolID]) == false){
				$_SESSION['filters']['schoolID'][] = $schoolID;
				$_SESSION['filters']['school'] = true;
				$alreadySet[$schoolID] = true;
			}
		}

	}

}

/******************************************************************************/

function updateActiveStatsItems($activeStatsItems){

	$firstEntry = true;
	$oldIndex = 0;

	$lastIndex = count($activeStatsItems['tournamentIDs']) - 1;

	for($index = 0; $index <= $lastIndex; $index++){
		$_SESSION['activeStatsItems']['tournamentIDs'][$index] = 0;
	}

	$index = $lastIndex;

	for($index = $lastIndex; $index >= 0; $index--){

		$_SESSION['activeStatsItems']['tournamentIDs'][$index] = (int)$activeStatsItems['tournamentIDs'][$index];

		if($index != $lastIndex){

			if($_SESSION['activeStatsItems']['tournamentIDs'][$index] == 0
				&& $_SESSION['activeStatsItems']['tournamentIDs'][$index+1] != 0){

				$_SESSION['activeStatsItems']['tournamentIDs'][$index] = $_SESSION['activeStatsItems']['tournamentIDs'][$index+1];
				$_SESSION['activeStatsItems']['tournamentIDs'][$index+1] = 0;
			}

		}
	}

}

/******************************************************************************/

function updateStatsAttendanceFilters($filters){

	$countryList = getCountryList();

	if(isset($filters['countryIso2']) && isset($countryList[$filters['countryIso2']]) == true){
		$_SESSION['statsAttendanceFilters']['countryIso2'] = $filters['countryIso2'];
	} else {
		$_SESSION['statsAttendanceFilters']['countryIso2'] = null;
	}

	$y = (int)substr($filters['startDate'],0,4);
	$m = (int)substr($filters['startDate'],5,7);;
	$d = (int)substr($filters['startDate'],8,10);
	$_SESSION['statsAttendanceFilters']['startDate'] = $y.'-'.sprintf('%02d',$m).'-'.sprintf('%02d',$d);

	$y = (int)substr($filters['endDate'],0,4);
	$m = (int)substr($filters['endDate'],5,7);
	$d = (int)substr($filters['endDate'],8,10);
	$_SESSION['statsAttendanceFilters']['endDate'] = $y.'-'.sprintf('%02d',$m).'-'.sprintf('%02d',$d);

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
	$previousScores[1] = $matchInfo['fighter1score'];
	$previousScores[2] = $matchInfo['fighter2score'];

	$exchangeID = $scoring['exchangeID'];
	$lastExchangeID = $_POST['lastExchangeID'];

	//return;
	switch ($_POST['lastExchange']){
		case 'scoringHit':
			calculateLastExchange($matchInfo,$scoring, $lastExchangeID);
			break;
		case 'penalty':
			insertPenalty($matchInfo,$scoring, $lastExchangeID);
			break;
		case 'noQuality':
			if($scoring[$f1ID]['hit'] == 'noQuality'){	$id = $f1ID;}
			if($scoring[$f2ID]['hit'] == 'noQuality'){	$id = $f2ID;}
			if($id == null){ break; }
			insertLastExchange($matchInfo, $lastExchangeID, 'noQuality', $id, 'null', 'null', null, null, null, $exchangeID);
			break;
		case 'doubleHit':
			insertLastExchange($matchInfo, $lastExchangeID, 'double', 'null', 'null', 'null', null, null, null, $exchangeID);
			break;
		case 'noExchange':
			insertLastExchange($matchInfo, $lastExchangeID, 'noExchange', 'null', 'null', 'null', null, null, null, $exchangeID);
			break;
		case 'clearLastExchange':
			clearExchangeLast($matchID, $lastExchangeID);
			break;
		case 'clearAllExchanges':
			clearExchangeAll($matchID);
			break;
		default:
			$_SESSION['alertMessages']['systemErrors'][] = "Could not figure out the exchange type in doUpdateMatch.php()";
			break;

	}
	updateMatch($matchInfo);

	// Check if it is the type of tournament which has a set number of exchanges

	if($_POST['lastExchange'] != 'clearLastExchange'){


		$matchInfo		= getMatchInfo($matchID);
		$matchConcluded	= false;

		// Exchange Cap
		if($matchConcluded == false){
			$matchConcluded = shouldMatchConcludeByExchanges($matchInfo);
		}

		// Point Cap
		if($matchConcluded == false){
			$matchConcluded = shouldMatchConcludeByPoints($matchInfo);
		}

		// Point Spread
		if($matchConcluded == false){
			$matchConcluded = shouldMatchConcludeBySpread($matchInfo);
		}

		// Time Limit
		if($matchConcluded == false){
			$matchConcluded = shouldMatchConcludeByTime($matchInfo);
		}

		if($matchConcluded == true){
			autoConcludeMatch($matchInfo);
			updateMatch($matchInfo);
			setAlert(USER_ALERT,"<strong>Match automatically concluded.</strong>");
		} else {
			shouldTeamsSwitch($matchInfo, $previousScores);
		}

	}


}

/******************************************************************************/

function shouldTeamsSwitch($matchInfo, $previousScores){

	if(isTeams($matchInfo['tournamentID']) == false){
		return;
	}

	$teamSwitchPoints = (int)readOption('T',$matchInfo['tournamentID'],'TEAM_SWITCH_POINTS');

	if($teamSwitchPoints == 0){
		return;
	}

	$updateSession = false;
	$shouldSwitch = [1 => false, 2 => false];

	if((int)$matchInfo['lastExchange'] == 0){
		$shouldSwitch = [1 => true, 2 => true];
		$updateSession = true;
	}

	$prev = (int)floor((int)$previousScores[1] / $teamSwitchPoints);
	$now = (int)floor((int)$matchInfo['fighter1score'] / $teamSwitchPoints);

	if($prev != $now){
		$shouldSwitch[2] = true;
		$updateSession = true;
	}

	$prev = (int)floor((int)$previousScores[2] / $teamSwitchPoints);
	$now = (int)floor((int)$matchInfo['fighter2score'] / $teamSwitchPoints);

	if($prev != $now){
		$shouldSwitch[1] = true;
		$updateSession = true;
	}

	if($updateSession == true){
		$_SESSION['shouldSwitchFighters'] = $shouldSwitch;
	}

}

/******************************************************************************/

function calculateLastExchange($matchInfo, $scoring, $lastExchangeID){
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
		noAfterblowScoring($matchInfo, $scoring, $lastExchangeID);
	} else if($doubleTypes['afterblowType'] == 'deductive'){
		deductiveAfterblowScoring($matchInfo, $scoring, $lastExchangeID);
	} else if($doubleTypes['afterblowType'] == 'full'){
		fullAfterblowScoring($matchInfo, $scoring, $lastExchangeID);
	}


	return;

}

/******************************************************************************/

function deductiveAfterblowScoring($matchInfo,$scoring, $lastExchangeID){
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

	} elseif($_POST['scoreLookupMode'] == 'grid'){

		$scoreValue = $scoring[$rosterID]['hit'];
		$rType = $scoring[$rosterID]['attackType'];
		$rTarget = $scoring[$rosterID]['attackTarget'];
		$rPrefix = $scoring[$rosterID]['attackPrefix'];

		if($rPrefix == ATTACK_CONTROL_DB){
			$_POST['attackModifier'] = ATTACK_CONTROL_DB;
		}

	} else {

		$_SESSION['alertMessages']['systemErrors'][] = "No scoreLookupMode set in deductiveAfterblowScoring()";
		return;
	}

	if(isset($_POST['attackModifier']) && $_POST['attackModifier'] == ATTACK_CONTROL_DB){
		$rPrefix = (int)$_POST['attackModifier'];
		$scoreValue += getControlPointValue($_SESSION['tournamentID']);
	}

// Afterblow deduction
	$scoreDeduction = 0;
	// checks for clean or afterblow
	if($scoring[$rosterID]['afterblow'] == 0){
		$exchangeType = 'clean';
	} else {
		$exchangeType = 'afterblow';
		$scoreDeduction = $scoring[$rosterID]['afterblow'];

		if($scoreDeduction > $scoreValue){
			$scoreDeduction = $scoreValue;
		}
	}

	if(isReverseScore() != REVERSE_SCORE_NO){
		$rosterID = $otherID;
	}

	insertLastExchange($matchInfo, $lastExchangeID, $exchangeType, $rosterID, $scoreValue,
					$scoreDeduction, $rPrefix, $rType, $rTarget, $exchangeID);
}

/******************************************************************************/

function insertPenalty($matchInfo, $scoring, $lastExchangeID){
// inserts an exchange as a penalty

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

	$matchID 	= $matchInfo['matchID'];
	$exchangeID = $scoring['exchangeID'];
	$scoreValue = $scoring['penalty']['value'];
	$card 		= $scoring['penalty']['card'];
	$action		= $scoring['penalty']['action'];
	$rosterID 	= $scoring['penalty']['rosterID'];

	insertLastExchange($matchInfo, $lastExchangeID, 'penalty', $rosterID, $scoreValue,
						0, null, $card, $action, $exchangeID);

}


/******************************************************************************/

function fullAfterblowScoring($matchInfo,$scoring, $lastExchangeID){
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

	$fighter1Hit = false;
	// If raw score
	if($_POST['scoreLookupMode'] == 'rawPoints'){

		$score1 = (int)$scoring[$id1]['hit'];
		$score2 = (int)$scoring[$id2]['hit'];

	} elseif ($_POST['scoreLookupMode'] == 'ID'){
		if((int)$scoring[$id1]['hit'] != 0 && (int)$scoring[$id2]['hit'] == 0){
			$fighter1Hit = true;
		}

		$at1 = getAttackAttributes($scoring[$id1]['hit']);
		$score1 = @$at1['attackPoints'];
		$scoring[$id1]['hit'] = $score1;


		$at2 = getAttackAttributes($scoring[$id2]['hit']);
		$score2 = @$at2['attackPoints']; // Passes null on purpose
		$scoring[$id2]['hit'] = $score2;

		if((int)$score1 > (int)$score2){
			$rType = @$at1['attackType'];
			$rTarget = @$at1['attackTarget'];
			$rPrefix = @$at1['attackPrefix'];
		} elseif((int)$score2 > (int)$score1) {
			$rType = @$at2['attackType'];
			$rTarget = @$at2['attackTarget'];
			$rPrefix = @$at2['attackPrefix'];
		} else {
			// Don't add attack info on equal scores
		}

	} else {
		$_SESSION['alertMessages']['systemErrors'][] = "No scoreLookupMode in fullAfterblowScoring()";
		return;
	}

	//checks if only one fighter hit
	if(xorWithZero($score1,$score2)){//only one hitter
		if($score1 || $fighter1Hit == true){
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
			$scoreValue += getControlPointValue($_SESSION['tournamentID']);
		}

		// These could both not exist, which is functionally the same as being null
		if(    @$at1['attackPrefix'] == ATTACK_CONTROL_DB
			|| @$at2['attackPrefix'] == ATTACK_CONTROL_DB){

			$rPrefix = (int)ATTACK_CONTROL_DB;
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

	if(isReverseScore($matchInfo['tournamentID']) > REVERSE_SCORE_NO){
		$rosterID = $otherID;
	}


	insertLastExchange($matchInfo, $lastExchangeID, $exchangeType, $rosterID, $scoreValue,
		$scoreDeduction, $rPrefix, $rType, $rTarget, $exchangeID);
}

/******************************************************************************/

function noAfterblowScoring($matchInfo,$scoring, $lastExchangeID){
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
		insertLastExchange($matchInfo, $lastExchangeID, 'double', 'null', 'null', 'null', null, null, null, $exchangeID);
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
		$scoreValue += getControlPointValue($_SESSION['tournamentID']);
	}

	if(isReverseScore($matchInfo['tournamentID']) > REVERSE_SCORE_NO){
		$rosterID = $otherID;
	}

	insertLastExchange($matchInfo, $lastExchangeID, 'clean', $rosterID, $scoreValue,
						$scoreDeduction, $rPrefix, $rType, $rTarget, $exchangeID);

}

/******************************************************************************/

function checkSession(){
// Corrects and possible error conditions in the current session
// Checks that the event/tournament/match referenced by SESSION actualty exists


	if(ALLOW['EVENT_SCOREKEEP'] == true && ALLOW['SOFTWARE_ADMIN'] == false){
		$_SESSION['filters']['school'] = false;
		$_SESSION['filters']['roster'] = false;
	}

	if($_SESSION['filters']['school'] == false){
		unset($_SESSION['filters']['schoolID']);
	}

	if($_SESSION['filters']['roster'] == false){
		unset($_SESSION['filters']['rosterID']);
	}

	$eventID = (int)$_SESSION['eventID'];
	$tournamentID = (int)$_SESSION['tournamentID'];
	$matchID = (int)$_SESSION['matchID'];

	$blockID = (int)@$_SESSION['blockID'];
	$sql = "SELECT blockID
			FROM logisticsScheduleBlocks
			WHERE blockID = {$blockID}";
	$_SESSION['blockID'] = (int)mysqlQuery($sql, SINGLE, 'blockID');


// Checks if the event in SESSION exists
	if($eventID == 0){
		$_SESSION['tournamentID'] = null;
		$_SESSION['matchID'] = null;
		return;
	}

	$sql = "SELECT eventID
			FROM systemEvents
			WHERE eventID = {$eventID}";
	$validEventID = (bool)mysqlQuery($sql, SINGLE, null);

	if($validEventID == false){
		$_SESSION['eventID'] = null;
		$_SESSION['tournamentID'] = null;
		$_SESSION['matchID'] = null;
		return;
	}


// Checks if the ruleset in SESSION is valid. Set to zero if invalid.
	$rulesID = (int)$_SESSION['rulesID'];
	$sql = "SELECT rulesID
			FROM eventRules
			WHERE eventID = {$eventID}
			AND rulesID = {$rulesID}";
	$_SESSION['rulesID'] = (int)mysqlQuery($sql, SINGLE, 'rulesID');

// Checks if the tournament in SESSION exists
	if($tournamentID == 0){
		$_SESSION['matchID'] =null;
		return;
	}


	$sql = "SELECT tournamentID
			FROM eventTournaments
			WHERE eventID = {$eventID}
			AND tournamentID = {$tournamentID}";
	$validTournamentID = (bool)mysqlQuery($sql, SINGLE, null);

	if($validTournamentID == false){
		$_SESSION['tournamentID'] = null;
		$_SESSION['matchID'] = null;
		return;
	}



// Checks if the match in SESSION exists
	if($matchID == 0){
		return;
	}

	$sql = "SELECT matchID
			FROM eventMatches
			INNER JOIN eventGroups USING(groupID)
			WHERE matchID = {$matchID}
			AND tournamentID = {$tournamentID}";
	$validMatchID = (bool)mysqlQuery($sql, SINGLE, null);

	if($validMatchID == false){
		$_SESSION['matchID'] = null;
		return;
	}

	return;

}

/******************************************************************************/

function changeEvent($eventID, $logoutInhibit = false, $landingPage = null, $tournamentID = 0, $matchID = 0){
// Changes event to the parameter provided and redirects to a
// landing page determined by the login type

// Is valid change
	if($_SESSION['eventID'] == $eventID){
		$eventChanged = false;
	} else {

		if(   isDescriptionPublished($eventID) == true
		   || isRosterPublished($eventID) == true
		   || isSchedulePublished($eventID) == true
		   || isMatchesPublished($eventID) == true
		   || isRulesPublished($eventID) == true
		   || doesUserHavePermission($_SESSION['userID'],$eventID,'VIEW_HIDDEN')
		   || $logoutInhibit == true ){
			$eventChanged = true;
		} else {
			$eventChanged = false;
		}
	}

	if($eventChanged == true){

		$_SESSION['eventID'] = $eventID;
		$_SESSION['eventName'] = getEventName($eventID);
		$_SESSION['isMetaEvent'] = isMetaEvent($eventID);


		$IDs = getEventTournaments();
		$tournamentID = (int)$tournamentID;

		if($tournamentID != 0 && in_array($tournamentID, $IDs)){
			changeTournament($tournamentID, $matchID);
		} else if($IDs != null && count($IDs) == 1){
			// If there is only one tournament in the event it is selected by deafult
			changeTournament($IDs[0], $matchID);
		} else {
			changeTournament(0, $matchID);
		}

		$_SESSION['scheduleID'] = '';
		$_SESSION['rosterID'] = '';
		$_SESSION['blockID'] = '';
		$_SESSION['metaTournamentComponentSource'] = '';
		$_SESSION['dayNum'] = logistics_getCurrentDayNum($_SESSION['eventID']);

		// Log user out if switching event
		if($logoutInhibit == true || ALLOW['SOFTWARE_EVENT_SWITCHING'] == true){
			// User can stay logged in
		} else {
			logUserOut(false);
		}

	}

// Page re-directs

	if($landingPage === null){
		// Default landing page
		if($_SESSION['eventID'] == null){
			$landingPage = 'infoWelcome.php';
		} else {
			$landingPage = 'infoSummary.php';
		}

		$originalPage = basename($_SERVER['PHP_SELF']);

		if(ALLOW['SOFTWARE_EVENT_SWITCHING'] == true){
			if(basename($_SERVER['PHP_SELF']) == "adminLogin.php"){
				$landingPage = 'infoSelect.php';
			} elseif($originalPage == "infoSelect.php"){
				$landingPage = 'infoSummary.php';
			} else {
				$landingPage = null;
			}
		} elseif (   ($originalPage == 'statsFighterSummary.php')
				  || ($originalPage == 'statsTournaments.php')
				  || ($originalPage == 'statsEvent.php')){
			$landingPage = null;
		}
	}

	checkSession();

	refreshPage($landingPage);
}

/******************************************************************************/

function logUserIn($logInData){
// Attempts to log a user in

	$type = $logInData['type'];
	$passwordInput = $logInData['password'];

	if($type == 'logInUser'){
		$eventID = $_SESSION['eventID'];
		$userName = $logInData['userName'];
	} elseif($type == 'logInStaff'){
		$userName = "eventStaff";
		$eventID = (int)@$logInData['eventID'];
	} elseif($type == 'logInOrganizer'){
		$userName = "eventOrganizer";
		$eventID = (int)@$logInData['eventID'];
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
		refreshPage("infoWelcome.php");
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
				if($poolPosition == $i){
					continue;
				}

				$tableID = (int)$fighter['tableID'];

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

		$poolNum = (int)$groupNumbers[$poolSet];
		$groupID = (int)$poolData['groupID'];

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

		$groupSet = (int)$roundData['groupSet'];
		if(!isset($groupNumbers[$groupSet])){
			$groupNumbers[$groupSet] = 0;
		}

		$groupNumbers[$groupSet]++;
		$groupNumber = $groupNumbers[$groupSet];
		$groupID = (int)$roundData['groupID'];

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

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

