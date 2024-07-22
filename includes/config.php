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

	define("DEPLOYMENT_UNKNOWN",0);
	define("DEPLOYMENT_PRODUCTION",1);
	define("DEPLOYMENT_TEST",2);
	define("DEPLOYMENT_LOCAL",3);

// Database Connection
	if(!defined('BASE_URL')){
		define('BASE_URL' , $_SERVER['DOCUMENT_ROOT'].'/');
	}
	include(BASE_URL.'includes/database.php');

	if(!defined('DEPLOYMENT')){
		define('DEPLOYMENT' , DEPLOYMENT_UNKNOWN);
	}

// Program Related Constants

	// User Types
	define("NO_LOGIN",0);

	// Alert Codes
	define("SYSTEM",1);
	define("USER_ERROR",2);
	define("USER_ALERT",3);
	define("USER_WARNING",4);

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

	define("SQL_FALSE",0);
	define("SQL_TRUE",1);

	define("DEFAULT_EVENT",1);
	define("DEFAULT_TOURNAMENT_ID",0);
	define("TEST_EVENT_ID",2);

	define("FINALS","0");
	define("ALL_GROUP_SETS",0);

	define("EXPORT_DIR",'exports/');

// Tournament Related Constants

	define("DEFAULT_COLOR_NAME_1",'RED');
	define("DEFAULT_COLOR_CODE_1",'#F66');
	define("DEFAULT_COLOR_NAME_2",'BLUE');
	define("DEFAULT_COLOR_CODE_2",'#66F');

	define("DEFAULT_MAX_DOUBLES",3);
	define("POOL_SIZE_LIMIT",16);	// If you raise this you also need to add the match order to the table.
	define("STAFF_COMPETENCY_MAX",9);

	define("PENALTY_CARD_NONE",		null);
	define("PENALTY_CARD_YELLOW",	34);
	define("PENALTY_CARD_RED",		35);
	define("PENALTY_CARD_BLACK",	38);

	// The types of tournaments
	define("FORMAT_NONE",0);
	define("FORMAT_RESULTS",1);
	define("FORMAT_MATCH",2);
	define("FORMAT_SOLO",3);
	define("FORMAT_META",4);

	define("NO_AFTERBLOW",1);
	define("DEDUCTIVE_AFTERBLOW",2);
	define("FULL_AFTERBLOW",3);

	define("REVERSE_SCORE_NO",0);
	define("REVERSE_SCORE_GOLF",1);
	define("REVERSE_SCORE_INJURY",2);

	define("ATTACK_CONTROL_DB",9);
	define("ATTACK_AFTERBLOW_DB",13);

	define("SUB_MATCH_ANALOG",0);
	define("SUB_MATCH_DIGITAL",1);

// Bracket Constants

	define("BRACKET_PRIMARY",1);
	define("BRACKET_SECONDARY",2);

	define("ELIM_TYPE_SINGLE",1);
	define("ELIM_TYPE_CONSOLATION",2);
	define("ELIM_TYPE_LOWER_BRACKET",3);
	define("ELIM_TYPE_TRUE_DOUBLE",4);

// Display Related Constants

	define("EVENT_ACTIVE_LIMIT",6);
	define("EVENT_UPCOMING_LIMIT",1);
	define("DATA_SERIES_MAX",4);

// Logistics Constants

	define("STAFF_CHECK_IN_NONE",0);
	define("STAFF_CHECK_IN_ALLOWED",1);
	define("STAFF_CHECK_IN_MANDATORY",2);

	define("SCHEDULE_BLOCK_TOURNAMENT",1);
	define("SCHEDULE_BLOCK_WORKSHOP",2);
	define("SCHEDULE_BLOCK_STAFFING",3);
	define("SCHEDULE_BLOCK_MISC",4);

	define("SCHEDULE_COLOR_TOURNAMENT",'#1779ba');
	define("SCHEDULE_COLOR_WORKSHOP","#3adb76");
	define("SCHEDULE_COLOR_STAFFING","#ffae00");
	define("SCHEDULE_COLOR_MISC","#BF5FFF");
	define("SCHEDULE_COLOR_CONFLICT","#cc4b37");

	define("LOGISTICS_ROLE_DIRECTOR",1);
	define("LOGISTICS_ROLE_JUDGE",2);
	define("LOGISTICS_ROLE_TABLE",3);
	define("LOGISTICS_ROLE_UNKONWN",4);
	define("LOGISTICS_ROLE_INSTRUCTOR",5);
	define("LOGISTICS_ROLE_GENERAL",6);
	define("LOGISTICS_ROLE_PARTICIPANT",7);

	define("STAFF_CONFLICTS_NO",0);     // Don't check staff conflicts
	define("STAFF_CONFLICTS_HARD",100); // Limit everything

// Video Constants
	define("VIDEO_SOURCE_UNKNOWN",0);
	define("VIDEO_SOURCE_YOUTUBE",1);
	define("VIDEO_SOURCE_NONE",2);
	define("VIDEO_SOURCE_GOOGLE_DRIVE",3);

	define("VIDEO_STREAM_UNKNOWN",0);
	define("VIDEO_STREAM_MATCH",1);
	define("VIDEO_STREAM_LOCATION",2);
	define("VIDEO_STREAM_VIRTUAL",3);


// Event Overal Rating

	define('EVENT_RATING_MIN_RATING',800);

	define('GLICKO_CONSTANT', 173.7178);
	define('ESTIMATE_DEVIATION_M', -.1278);
	define('ESTIMATE_DEVIATION_B', 294);

	define('PROBABILITY_THRESHOLD',0.25);
	define('RATING_STEP',1);


// Options Defines

	// Match Options
	$options['M']['NUM_SUB_MATCHES'] 	= 2;
	$options['M']['SWAP_FIGHTERS'] 		= 3;

	// Tournament Options
	$options['T']["META_ROSTER_MODE"]				= 1;
		define("META_ROSTER_MODE_INCLUSIVE",0);
		define("META_ROSTER_MODE_EXCLUSIVE",1);
		define("META_ROSTER_MODE_EXTENDED",2);
	$options['T']['ATTACK_DISPLAY_MODE'] 			= 4;
	$options['T']['AFTERBLOW_POINT_VALUE'] 			= 5;
	$options['T']['MATCH_TIE_MODE'] 				= 6;
		define("MATCH_TIE_MODE_NONE",0);
		define("MATCH_TIE_MODE_EQUAL",1);
		define("MATCH_TIE_MODE_UNEQUAL",2);
	$options['T']['TEAM_SWITCH_POINTS'] 			= 7;
	$options['T']['DOUBLES_ARE_NOT_SCORING_EXCH'] 	= 8;
	$options['T']['CONTROL_POINT_VALUE'] 	      	= 9;
	$options['T']["TEAM_SIZE"] 						= 10;
	$options['T']["DOUBLES_CARRY_FORWARD"] 			= 11;
	$options['T']["SUPPRESS_DIRECT_ENTRY"] 			= 12;
	$options['T']["PRIORITY_NOTICE_ON_NON_SCORING"] = 15;
	$options['T']["DENOTE_FIGHTERS_WITH_OPTION_CHECK"] = 16;
	$options['T']["MATCH_SOFT_CLOCK_TIME"] 		= 17;

	// Event Options
	$options['E']["PENALTY_ACTION_IS_MANDATORY"]	= 13;
	$options['E']["HIDE_WHITE_CARD_PENALTIES"] 		= 14;

	define('OPTION',$options);

// Includes ////////////////////////////////////////////////////////////////////

require_once(BASE_URL.'includes/function_lib.php');

// Database Connection /////////////////////////////////////////////////////////

$conn = connectToDB();

// Set Session Values //////////////////////////////////////////////////////////

// Set the Permissions
	setPermissions();

// Set tournament ID if there is only one tournament in the event
	if($_SESSION['eventID'] != null){

		$_SESSION['eventName'] = getEventName($_SESSION['eventID']);

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
	$defaults = getEventDefaults($_SESSION['eventID']);

	switch(@$defaults['nameDisplay']){
		case 'lastName':
			$nameMode = 'lastName';
			$nameMode2 = 'firstName';
			break;
		case 'firstName':
		default:
			$nameMode = 'firstName';
			$nameMode2 = 'lastName';
			break;
	}

	define("NAME_MODE", $nameMode);
	define("NAME_MODE_2", $nameMode2);

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
		$sql = "SELECT isFinalized, isTeams, logicMode, formatID
				FROM eventTournaments
				WHERE tournamentID = {$tournamentID}";
		$tSettings = mysqlQuery($sql, SINGLE);

	// Tournament Concluded
		if($tSettings['isFinalized'] == 1){
			define("LOCK_TOURNAMENT", 'disabled');
		}

	// Use timer in the matches
		if($tSettings['logicMode'] != ''){
			define("LOGIC_MODE", $tSettings['logicMode']);
		}

	// Tournament format
		$_SESSION['formatID'] = $tSettings['formatID'];

	}
	if(!defined('LOCK_TOURNAMENT')){ define("LOCK_TOURNAMENT", ''); }
	if(!defined('LOGIC_MODE')){ define("LOGIC_MODE", 'normal'); }


// Event Display Modes
	$defaults = getEventDefaults($_SESSION['eventID']); // Have to re-load as it could change with POST
	$_SESSION['dataModes']['tournamentDisplay'] = $defaults['tournamentDisplay'];
	$_SESSION['dataModes']['tournamentSort'] = $defaults['tournamentSorting'];


// Match Colors
	if($_SESSION['tournamentID'] != null){
		$tournamentID = (int)$_SESSION['tournamentID'];
		$sql = "SELECT colorName, colorCode, contrastCode
				FROM eventTournaments, systemColors
				WHERE eventTournaments.tournamentID = {$tournamentID}
				AND color1ID = colorID";
		$result = mysqlQuery($sql, SINGLE);

		define("COLOR_NAME_1",$result['colorName']);
		define("COLOR_CODE_1",$result['colorCode']);
		define("COLOR_CONTRAST_CODE_1",$result['contrastCode']);

		$sql = "SELECT colorName, colorCode, contrastCode
				FROM eventTournaments, systemColors
				WHERE tournamentID = {$tournamentID}
				AND color2ID = colorID";
		$result = mysqlQuery($sql, SINGLE);

		define("COLOR_NAME_2",$result['colorName']);
		define("COLOR_CODE_2",$result['colorCode']);
		define("COLOR_CONTRAST_CODE_2",$result['contrastCode']);
	}

	if(!defined('COLOR_NAME_1')){ define("COLOR_NAME_1", null); }
	if(!defined('COLOR_NAME_2')){ define("COLOR_NAME_2", null); }
	if(!defined('COLOR_CODE_1')){ define("COLOR_CODE_1", null); }
	if(!defined('COLOR_CODE_2')){ define("COLOR_CODE_2", null); }
	if(!defined('COLOR_CONTRAST_CODE_1')){ define("COLOR_CONTRAST_CODE_1", '#000'); }
	if(!defined('COLOR_CONTRAST_CODE_2')){ define("COLOR_CONTRAST_CODE_2", '#000'); }



// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function setPermissions(){
// Intialize the permissions constant with what the current user can and can't do.

	$permissionsList =
		['EVENT_VIDEO','EVENT_SCOREKEEP','EVENT_MANAGEMENT',
		'SOFTWARE_EVENT_SWITCHING','SOFTWARE_ASSIST','SOFTWARE_ADMIN',
		'STATS_EVENT','STATS_ALL',
		'VIEW_HIDDEN','VIEW_SETTINGS','VIEW_EMAIL'];

	foreach($permissionsList as $permisionType){
		$permissionsArray[$permisionType] = false;
	}
	$permissionsArray['VIEW_ROSTER']		= false;
	$permissionsArray['VIEW_SCHEDULE']		= false;
	$permissionsArray['VIEW_MATCHES'] 		= false;
	$permissionsArray['VIEW_RULES'] 		= false;

	switch($_SESSION['userName']){

		case 'eventOrganizer':

			$permissionsArray['EVENT_MANAGEMENT'] 	= true;
			$permissionsArray['STATS_EVENT'] 		= true;
			// Deliberate fall-thought

		case 'eventStaff':

			$permissionsArray['EVENT_SCOREKEEP'] 	= true;
			$permissionsArray['VIEW_ROSTER']		= true;
			$permissionsArray['VIEW_SCHEDULE']		= true;
			$permissionsArray['VIEW_MATCHES'] 		= true;
			$permissionsArray['VIEW_RULES'] 		= true;

		case '':

			if(isRosterPublished($_SESSION['eventID']) == true){
				$permissionsArray['VIEW_ROSTER'] = true;
			}

			if(isSchedulePublished($_SESSION['eventID']) == true){
				$permissionsArray['VIEW_SCHEDULE'] = true;
			}

			if(isMatchesPublished($_SESSION['eventID']) == true){
				$permissionsArray['VIEW_MATCHES'] = true;
			}

			if(isRulesPublished($_SESSION['eventID']) == true){
				$permissionsArray['VIEW_RULES'] = true;
			}

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

			$_SESSION['userID'] = (int)$permData['userID'];
			$eventID = (int)$_SESSION['eventID'];
			unset($permData['userID']);

			foreach($permData as $field => $bool){
				if($bool == true){
					$permissionsArray[$field] = true;
				}
			}

			if(isRosterPublished($_SESSION['eventID']) == true){
				$permissionsArray['VIEW_ROSTER'] = true;
			}

			if(isSchedulePublished($_SESSION['eventID']) == true){
				$permissionsArray['VIEW_SCHEDULE'] = true;
			}

			if(isMatchesPublished($_SESSION['eventID']) == true){
				$permissionsArray['VIEW_MATCHES'] = true;
			}

			if(isRulesPublished($_SESSION['eventID']) == true){
				$permissionsArray['VIEW_RULES'] = true;
			}

			if($permissionsArray['EVENT_MANAGEMENT'] == false){
				$sql = "SELECT 1
						FROM systemUserEvents
						WHERE userID = {$_SESSION['userID']}
						AND eventID = {$eventID}";
				$isAttached = (bool)mysqlQuery($sql, SINGLE);

				if($isAttached == true){
					$permissionsArray['EVENT_SCOREKEEP'] 	= true;
					$permissionsArray['EVENT_MANAGEMENT'] 	= true;
					$permissionsArray['STATS_EVENT'] 		= true;
				}
			}

			if($permissionsArray['STATS_ALL'] == true){
				$permissionsArray['STATS_EVENT'] = true;
			}

			if(    $permissionsArray['VIEW_HIDDEN'] == true
				|| $permissionsArray['VIEW_SETTINGS'] == true
				|| $permissionsArray['STATS_EVENT'] == true){

				$permissionsArray['VIEW_ROSTER']	= true;
				$permissionsArray['VIEW_SCHEDULE']	= true;
				$permissionsArray['VIEW_MATCHES']	= true;
				$permissionsArray['VIEW_RULES'] 	= true;
			}

	}

	define("ALLOW",$permissionsArray);

	if(ALLOW['SOFTWARE_ADMIN'] == false){
		unset($_SESSION['adminOptions']);
	}

/*
EVENT_VIDEO
	- Can add video links to fights
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
		$_SESSION['alertMessages']['userWarnings'] = [];
	}
	if(!isset($_SESSION['eventID'])){
		$_SESSION['eventID'] = 0;
	}
	if(!isset($_SESSION['isMetaEvent'])){
		$_SESSION['isMetaEvent'] = false;
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
	if(!isset($_SESSION['formatID']) || $_SESSION['formatID'] == null){
		$_SESSION['formatID'] = '';
	}
	if(!isset($_SESSION['userID'])){
		$_SESSION['userID'] = 0;
	}
	if(!isset($_SESSION['rulesID'])){
		$_SESSION['rulesID'] = 0;
	}

	if(!isset($_SESSION['userName'])){
		$_SESSION['userName'] = '';
	}
	if(!isset($_SESSION['rosterID'])){
		$_SESSION['rosterID'] = 0;
	}
	if(!isset($_SESSION['dayNum'])){
		$_SESSION['dayNum'] = 1;
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
	if(!isset($_SESSION['alertMessages']['userWarnings'])){
		$_SESSION['alertMessages']['userAlerts'] = [];
	}

	if(!isset($_SESSION['rosterViewMode'])){
		$_SESSION['rosterViewMode'] = [];
	}
	if(!isset($_SESSION['ratingViewMode'])){
		$_SESSION['ratingViewMode'] = [];
	}
	if(!isset($_SESSION['viewMode']['time24hr'])){
		$_SESSION['viewMode']['time24hr'] = false;
	}
	if(!isset($_SESSION['displayByPool'])){
		$_SESSION['displayByPool'] = false;
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
	if(!isset($_SESSION['dataModes']['percent'])){
		$_SESSION['dataModes']['percent'] = true;
	}
	if(!isset($_SESSION['dataModes']['extendedExchangeInfo'])){
		$_SESSION['dataModes']['extendedExchangeInfo'] = false;
	}

	if(!isset($_SESSION['filterForSystemRosterID'])){
		$_SESSION['filterForSystemRosterID'] = 0;
	}
	if(!isset($_SESSION['filters']['school'])){
		$_SESSION['filters']['school'] = false;
	}
	if(!isset($_SESSION['filters']['roster'])){
		$_SESSION['filters']['roster'] = false;
	}

}

/******************************************************************************/


// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////


