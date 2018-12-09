<?php
/*******************************************************************************
	Configuration File
	
	Defines constants
	Includes function libraries
	Connects to database
	Establishes proper session values
	Runs the POST processing function
	
*******************************************************************************/

// Initialize Session //////////////////////////////////////////////////////////

	initializeSession();

// System Constants ////////////////////////////////////////////////////////////
	
	define("DEBUGGING", 0);
	date_default_timezone_set("UTC");

// Database Connection
	if(!defined('BASE_URL')){
		define('BASE_URL' , $_SERVER['DOCUMENT_ROOT'].'/');
	}
	include(BASE_URL.'includes/database.php');

// Program Related Constants

	// User Types
	define("NO_LOGIN",0);

	// Alert Codes
	define("SYSTEM",1);
	define("USER_ERROR",2);
	define("USER_ALERT",3);

	// mysqlQuery() function codes
	define("SEND",0);
	define("INDEX",1);
	define("RAW",2);
	define("NUM_ROWS",3);
	define("ASSOC",4);
	define("SINGLE",5);
	define("KEY",6);
	define("KEY_SINGLES",7);
	define("SINGLES",8);

	define("FINALS","0");

	define("DEFAULT_EVENT",1);
	define("DEFAULT_TOURNAMENT_ID",0);
	
	define("DEFAULT_NAME_MODE", 'firstName');

	define("ALL_GROUP_SETS",0);

// Tournament Related Constants

	define("DEFAULT_COLOR_NAME_1",'RED');
	define("DEFAULT_COLOR_CODE_1",'#F66');
	define("DEFAULT_COLOR_NAME_2",'BLUE');
	define("DEFAULT_COLOR_CODE_2",'#66F');

	define("DEFAULT_MAX_DOUBLES",3);
	define("POOL_SIZE_LIMIT",10);
	
	define("RESULTS_ONLY",1);
	define("POOL_BRACKET",2);
	define("DIRECT_BRACKET",3);
	define("POOL_SETS",4);
	define("SCORED_EVENT",5);

	define("NO_AFTERBLOW",1);
	define("DEDUCTIVE_AFTERBLOW",2);
	define("FULL_AFTERBLOW",3);

	define("REVERSE_SCORE_NO",0);
	define("REVERSE_SCORE_GOLF",1);
	define("REVERSE_SCORE_INJURY",2);

	define("ATTACK_CONTROL_DB",9);
	define("ATTACK_AFTERBLOW_DB",13);

// Display Related Constants

	define("EVENT_ACTIVE_LIMIT",6);
	define("EVENT_UPCOMING_LIMIT",1);

// Includes ////////////////////////////////////////////////////////////////////

require_once(BASE_URL.'includes/function_lib.php');

// Database Connection /////////////////////////////////////////////////////////

$conn = connectToDB();

// Set Session Values //////////////////////////////////////////////////////////

// Set the Permissions
	setPermissions();

// Set the event ID to the Default Event if there is one
	if(!isset($_SESSION['eventID']) || ((int)$_SESSION['eventID']) <= 0){
		$defaultEvent = getDefaultEvent();
		if($defaultEvent != null){
			$_SESSION['eventID'] = $defaultEvent;
			$_SESSION['eventName'] = getEventName($eventID);
		} else {
			$_SESSION['eventID'] = '';
			$_SESSION['eventName'] = '';
		}
	}

// Set tournament ID if there is only one tournament in the event
	if($_SESSION['eventID'] != null){
		if($_SESSION['tournamentID'] == null){
			$sql = "SELECT tournamentID
					FROM eventTournaments
					WHERE eventID = {$_SESSION['eventID']}";
			$tournamentIDs = mysqlQuery($sql, SINGLES, 'tournamentID');
			
			if(count($tournamentIDs) == 1){
				$_SESSION['tournamentID'] = $tournamentIDs[0];
			}
		}
	} else {
		$_SESSION['tournamentID'] == null;
	}

// Pool Set
	if(!isset($_SESSION['groupSet'])){$_SESSION['groupSet'] = 1;}
	
// Name mode  -- this MUST go before processPostData
	$defaults = getEventDefaults();
	$nameMode = $defaults['nameDisplay'];
	if($nameMode == ''){
		$nameMode = DEFAULT_NAME_MODE;
	}
	define("NAME_MODE", $nameMode);

// Is Teams Mode -- MUST go before processPostData
	if(isset($_SESSION['tournamentID']) && $_SESSION['tournamentID'] != null){
		if(isTeams($_SESSION['tournamentID'])){
			define("IS_TEAMS",true);
		}
	}
	if(!defined('IS_TEAMS')){ define("IS_TEAMS", false); }

// Process POST Data ///////////////////////////////////////////////////////////

	processPostData(); 

// Define Constants Based on DB ////////////////////////////////////////////////

// Tournament Specific Constants
	if($_SESSION['tournamentID'] != null){
		$tournamentID = $_SESSION['tournamentID'];
		$sql = "SELECT isFinalized, useTimer, isTeams, logicMode
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$tSettings = mysqlQuery($sql, SINGLE);
		
	// Tournament Concluded	
		if($tSettings['isFinalized'] == 1){
			define("LOCK_TOURNAMENT", 'disabled');
		}
		
	// Use timer in the matches
		if($tSettings['useTimer'] == 1){
			define("IS_TIMER", true);
		}

	// Use timer in the matches
		if($tSettings['logicMode'] != ''){
			define("LOGIC_MODE", $tSettings['logicMode']);
		}
		

	}
	if(!defined('IS_TIMER')){ define("IS_TIMER", false); }
	if(!defined('LOCK_TOURNAMENT')){ define("LOCK_TOURNAMENT", ''); }
	if(!defined('LOGIC_MODE')){ define("LOGIC_MODE", 'normal'); }
	
	
// Event Display Modes
	$defaults = getEventDefaults(); // Have to re-load as it could change with POST
	$_SESSION['dataModes']['tournamentDisplay'] = $defaults['tournamentDisplay'];
	$_SESSION['dataModes']['tournamentSort'] = $defaults['tournamentSorting'];


// Match Colors
	if($_SESSION['tournamentID'] != null){
		$tournamentID = $_SESSION['tournamentID'];
		$sql = "SELECT colorName, colorCode
				FROM eventTournaments, systemColors
				WHERE eventTournaments.tournamentID = {$tournamentID}
				AND color1ID = colorID";
		$result = mysqlQuery($sql, SINGLE);

		define("COLOR_NAME_1",$result['colorName']);
		define("COLOR_CODE_1",$result['colorCode']);
		
		$sql = "SELECT colorName, colorCode
				FROM eventTournaments, systemColors
				WHERE tournamentID = {$tournamentID}
				AND color2ID = colorID";
		$result = mysqlQuery($sql, SINGLE);

		define("COLOR_NAME_2",$result['colorName']);
		define("COLOR_CODE_2",$result['colorCode']);
	}
	
	if(!defined('COLOR_NAME_1')){ define("COLOR_NAME_1", null); }
	if(!defined('COLOR_NAME_2')){ define("COLOR_NAME_2", null); }
	if(!defined('COLOR_CODE_1')){ define("COLOR_CODE_1", null); }
	if(!defined('COLOR_CODE_2')){ define("COLOR_CODE_2", null); }


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function setPermissions(){
// Intialize the permissions constant with what the current user can and can't do.

	$permissionsList = 
		['EVENT_YOUTUBE','EVENT_SCOREKEEP','EVENT_MANAGEMENT',
		'SOFTWARE_EVENT_SWITCHING','SOFTWARE_ASSIST','SOFTWARE_ADMIN',
		'STATS_EVENT','STATS_ALL',
		'VIEW_HIDDEN','VIEW_SETTINGS','VIEW_EMAIL'];

	foreach($permissionsList as $permisionType){
		$permissionsArray[$permisionType] = false;
	}

	switch($_SESSION['userName']){
		case 'eventStaff':
			$permissionsArray['EVENT_SCOREKEEP'] 	= true;
			break;
		case 'eventOrganizer':
			$permissionsArray['EVENT_SCOREKEEP'] 	= true;
			$permissionsArray['EVENT_MANAGEMENT'] 	= true;
			$permissionsArray['STATS_EVENT'] 		= true;
			break;
		case '':
			// No user name, no permissions.
			break;
		default:
			$selectFields = '';
			foreach($permissionsList as $permisionType){
				if($selectFields != ''){
					$selectFields .= ", ";
				}
				$selectFields .= $permisionType;
			}

			$sql = "SELECT userID, {$selectFields}
					FROM systemUsers
					WHERE userName = '{$_SESSION['userName']}'";
			$permData = mysqlQuery($sql, SINGLE);

			$_SESSION['userID'] = $permData['userID'];
			unset($permData['userID']);

			foreach($permData as $field => $bool){
				if($bool == true){
					$permissionsArray[$field] = true;
				}
			}
	}

	define("ALLOW",$permissionsArray);

/*
EVENT_YOUTUBE
	- Can add youtube links to fights
EVENT_SCOREKEEP
	- Can score matches/pieces.
	- Can advance fighters in brackets
	- Can finalize tournaments
EVENT_MANAGEMENT
	- Can add fighters to events/tournaments
	- Can add schools to the DB
	- Can create/populate pools & sets.
	- Can create brackets.

SOFTWARE_EVENT_SWITCHING
	- Can go between events without loging out
SOFTWARE_ASSIST
	- Can change software related settings, such as adding events or changing school names
	- Can reset event passwords
SOFTWARE_ADMIN
	- Can assign passwords to all users

STATS_EVENT
	- Can view statistics of the current event.
STATS_ALL
	- Can view agregate stats across multiple events.

VIEW_HIDDEN
	- Can see hidden events
VIEW_SETTINGS
	- Can view all the EVENT_MANAGEMENT functionality, but not change any settings.
VIEW_EMAIL
	- Can view event organizer e-mail addresses.
*/

}

/******************************************************************************/

function initializeSession(){
// Starts the session and initializes any session variables 
// that are not set to null values.

	session_start();

	if(!isset($_SESSION['alertMessages'])){
		$_SESSION['alertMessages']['systemErrors'] = [];
		$_SESSION['alertMessages']['userErrors'] = [];
		$_SESSION['alertMessages']['userAlerts'] = [];
	}
	if(!isset($_SESSION['eventID'])){
		$_SESSION['eventID'] = '';
	}
	if(!isset($_SESSION['tournamentID'])){
		$_SESSION['tournamentID'] = '';
	}
	if(!isset($_SESSION['matchID'])){
		$_SESSION['matchID'] = '';
	}
	if(!isset($_SESSION['groupSet']) || $_SESSION['groupSet'] == null){
		$_SESSION['groupSet'] = 1;
	}

	if(!isset($_SESSION['userName'])){
		$_SESSION['userName'] = '';
	}

	if(!isset($_SESSION['alertMessages']['systemErrors'])){
		$_SESSION['alertMessages']['systemErrors'] = [];
	}
	if(!isset($_SESSION['alertMessages']['userErrors'])){
		$_SESSION['alertMessages']['userErrors'] = [];
	}
	if(!isset($_SESSION['alertMessages']['userAlerts'])){
		$_SESSION['alertMessages']['userAlerts'] = [];
	}

	if(!isset($_SESSION['rosterViewMode'])){
		$_SESSION['rosterViewMode'] = [];
	}
	if(!isset($_SESSION['bracketHelper'])){
		$_SESSION['bracketHelper'] = [];
	}

	if(!isset($_SESSION['dataModes']['tournamentDisplay'])){
		$_SESSION['dataModes']['tournamentDisplay'] = '';
	}
	if(!isset($_SESSION['dataModes']['tournamentSort'])){
		$_SESSION['dataModes']['tournamentSort'] = '';
	}

}

/******************************************************************************/	
	

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////


