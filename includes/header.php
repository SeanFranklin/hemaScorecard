<?php
/*******************************************************************************
	Page Header

	Links to stylesheets and config as well as header content
	and navigation links
	LOGIN:
		- Menu items change depending on login level
		- Login button becomes 'Log Out' when loged in

*******************************************************************************/

include_once('includes/config.php');

$vJ = '?=1.6.0'; // Javascript Version
$vC = '?=1.2.4'; // CSS Version

if(    ALLOW['EVENT_MANAGEMENT'] == true
	|| ALLOW['VIEW_SETTINGS'] == true
	|| ALLOW['STATS_EVENT'] == true){

	$adminStatsDisplay = true;
} else {
	$adminStatsDisplay = false;
}

?>

<!doctype html>
<html class="no-js" lang="en" dir="ltr">

<head>

	<!-- Output base URL of site for Javascript use-->
	<script>
		<?php echo "var BASE_URL = '".BASE_URL."';";?>
	</script>

	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="
		HEMA Scorecard is a free online software application for running
		Historical European Martial Arts tournaments and making the information
		easily accessible.
	">
	<meta name="keywords" content="HEMA, Tournament, Historical European Martial Arts, Martial Arts, Sword">
	<title>HEMA Scorecard</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/6.4.3/css/foundation.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.foundation.min.css">

	<link href="https://fonts.googleapis.com/css?family=Chivo:300,400,700" rel="stylesheet">
	<link rel="stylesheet" href="includes/foundation/css/app.css">
	<link rel="stylesheet" href="includes/foundation/css/custom.css<?=$vC?>">

	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script>google.charts.load('current', {'packages':['corechart']});</script>
	<script src="https://cdn.tiny.cloud/1/ctrvec03t4hztqmygiaf7d6mtiod1qat9px92nlsxdq2mat3/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

	<link rel='icon' href='includes\images\favicon.png'>

	<!-- Jumps to section on page if $_SESSION['jumpTo'] is set -->
	<?php if(isset($_SESSION['jumpTo'])): ?>
		<script>window.onload = window.location.hash='<?=$_SESSION['jumpTo']?>';</script>
		<?php unset($_SESSION['jumpTo']); ?>
	<?php endif ?>

	<?php if(isset($refreshPageTimer) == true && (int)$refreshPageTimer != 0):?>
		<meta http-equiv="refresh" content="<?=(int)$refreshPageTimer?>">
	<?php endif ?>

	<style>
		li.fighter_1_color {
			border-bottom-color: <?= COLOR_CODE_1 ?>;
		}
		li.fighter_2_color {
			border-bottom-color: <?= COLOR_CODE_2 ?>;
		}
		.f1-BG {
			background-color: <?= COLOR_CODE_1 ?>;
		}
		.f2-BG {
			background-color: <?= COLOR_CODE_2 ?>;
		}
	</style>
</head>

<body>
 <!-- START Upper Navigation ------------------------------------------>

	<?php debugging(); ?>

	<!-- Mobile Navigation -->
	<div class="title-bar" data-responsive-toggle="tourney-animated-menu" data-hide-for="large" style='display:none'>
		<form method='POST' name='logOutForm1'>
		<button class="menu-icon" type="button" data-toggle></button>
		<div class="title-bar-title">Menu</div>

		<?php if($_SESSION['userName'] == null): ?>
			<a href='adminLogIn.php' class='login-link'>Login</a>
		<?php else: ?>
			<input type='hidden' name='formName' value='logUserOut'>
			<a href='javascript:document.logOutForm1.submit();' class='login-link'>Log Out</a>
		<?php endif ?>

		</form>
	</div>

	<!-- Full Navigation -->
	<div class="top-bar" id="tourney-animated-menu" data-animate="hinge-in-from-top hinge-out-from-top" style='display:none'>
		<div class="top-bar-left">
			<ul class="dropdown menu vertical medium-horizontal" data-dropdown-menu>

				<?php tournamentListForHeader(); ?>
				<?=menuEvent()?>
				<?=menuTournament()?>
				<?=menuEventOrg()?>
				<?=menuAnalytics()?>
				<?=activeLivestream()?>
				<li><a href='infoSelect.php'>Change Event</a></li>
				<li><a href='adminHelp.php'>Help/About</a></li>
				<?=menuAdmin()?>
			</ul>

		</div>

		<div class="top-bar-right show-for-large">
			<?php if($_SESSION['userName'] == null): ?>
				<a href='adminLogIn.php' style='color:white'>Login</a>
			<?php else: ?>
				<form method='POST' name='logOutForm2'>
					<input type='hidden' name='formName' value='logUserOut'>
					<a href='javascript:document.logOutForm2.submit();' style='color:white'>Log Out</a>
				</form>
			<?php endif ?>
		</div>

	</div>

	<?=DisplayServerVersion()?>

<!-- END Upper Navigation ----------------------------------------->

<!-- START Page Title --------------------------------------------->

	<?php if(   ($_SESSION['eventID'] != null && !isset($hidePageTitle))
			 || (isset($forcePageTitle)) ): ?>
		<div class='hero-title'>

		<!-- Event Name -->
		<h1>
			<?php eventNameForHeader(); ?>
		</h1>


		<!-- Tournament Name -->
		<?php  if(isset($includeTournamentName) && $_SESSION['tournamentID'] != null):
			$tName = getTournamentName(); ?>

			<div class='hide-for-large'>
				<i><?= $tName ?></i>
			</div>
		<?php endif ?>

		<!-- Page Name -->
		<h2><?= $pageName ?></h2>

		</div>
	<?php else: ?>
		<BR>
	<?php endif ?>

<!-- END Page Title ----------------------------------------------->

	<div id='page-wrapper' class='grid-container'>

<!-- START Lower Navigation --------------------------------------->

	<?php
	if(($_SESSION['eventID'] != null && $_SESSION['tournamentID'] != null)
		&& (!isset($hideEventNav) || ALLOW['SOFTWARE_ADMIN'] == true)):
		/* This is the lower navigational bar
		 * It will not show up if there is no event or tournament selected,
		 * or if the pagerequests it to be hidden. It will always be shown
		 * if logged in as a super admin.
		 * The items that appear in the navigation bar change depending on
		 * the type of tournament which is active. */

		$navBarString = '';

		if($_SESSION['tournamentID'] != null){
			if(!isTeams($_SESSION['tournamentID'])){
				$navBarString .= "<li><a href='participantsTournament.php'>Tournament Roster</a></li>";
			} elseif(ALLOW['EVENT_SCOREKEEP'] == true || ALLOW['VIEW_SETTINGS'] == true) {
				$navBarString .= "<li><a href='participantsTournament.php'>Tournament Roster</a></li>";
				$navBarString .= "<li><a href='participantsTeams.php'>Team Rosters</a></li>";
			} else {
				$navBarString .= "<li><a href='participantsTeams.php'>Team Rosters</a></li>";
			}
		}

		// Tournament is a meta-tournament
		if($_SESSION['formatID'] == FORMAT_META){
			$navBarString .= "<li><a href='participantsComponents.php'>Tournament Components</a></li>
								<li><a href='poolStandings.php'>Standings</a></li>";
		}


		// Tournament has pools
		if(isPools($_SESSION['tournamentID'])){
			$navBarString .= "<li><a href='poolRosters.php'>Pool Rosters</a></li>
								<li><a href='poolMatches.php'>Pool Matches</a></li>
								<li><a href='poolStandings.php'>Pool Standings</a></li>";
		} elseif ($_SESSION['formatID'] == FORMAT_MATCH
					&& ALLOW['EVENT_MANAGEMENT'] == true){

			$navBarString .= "<li><a href='poolRosters.php'>Create Pools</a></li>";
		}

		// Tournament has brackets
		if(isBrackets($_SESSION['tournamentID'])){

			$navBarString .= "<li><a href='finalsBracket.php'>Finals Bracket</a></li>";

		} elseif ($_SESSION['formatID'] == FORMAT_MATCH
					&& ALLOW['EVENT_MANAGEMENT'] == true){

			$navBarString .= "<li><a href='finalsBracket.php'>Create Bracket</a></li>";
		}

		// Tournament has rounds
		if($_SESSION['formatID'] == FORMAT_SOLO){
			$navBarString .= "<li><a href='roundRosters.php'>Round Rosters</a></li>
								<li><a href='roundMatches.php'>Round Scores</a></li>
								<li><a href='roundStandings.php'>Round Standings</a></li>";
		}

		// Tournament has cutting quallification
		if(isCuttingQual($_SESSION['tournamentID'])){
			$navBarString .= "<li><a href='cutQualsTournament.php'>Cutting Qualification</a></li>";
		}

		?>

		<?php if(isset($navBarString)): ?>
			<ul class='menu align-left tourney-menu-large show-for-medium'>
				<?= $navBarString ?>
			</ul>
			<ul class='dropdown menu tourney-menu-mobile
				show-for-small-only align-center' data-dropdown-menu>
				<li>
					<a href='#'>Browse Tournament</a>
					<ul class='menu'>
						<?= $navBarString ?>
					</ul>
				</li>
			</ul>
		<?php endif ?>

	<?php endif ?>


<!-- END Lower Navigation ----------------------------------------->

<!-- START Page Alerts -------------------------------------------->
	<?php

	if(isset($lockedTournamentWarning)){
		tournamentLockedAlert($lockedTournamentWarning);
	}
	displayPageAlerts();
	displayEventAnnouncements();


// FUNCTIONS //////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function DisplayServerVersion(){

	switch(DEPLOYMENT){
		case DEPLOYMENT_PRODUCTION: { return; break; } // don't display anything
		case DEPLOYMENT_LOCAL: 		{$color="#39FF14";	$text = "Local Server";	break;}
		case DEPLOYMENT_TEST: 		{$color="#FFAD00";	$text = "Test Server";	break;}
		case DEPLOYMENT_UNKNOWN:
		default:					{$color="#FF69B4";	$text = "Unknown Deployment";	break;}
	}

?>

	<div class='text-center' style='font-size:0.7em;background-color: <?=$color?>;'>
		<i><?=$text?></i>
	</div>

<?php
}

/******************************************************************************/

function menuEvent(){

	if($_SESSION['eventID'] == null){
		return;
	}

	if(    ALLOW['EVENT_MANAGEMENT'] == false
		&& ALLOW['VIEW_SETTINGS'] == false
		&& ALLOW['STATS_EVENT'] == false
		&& isAnyEventInfoViewable() == false){

		return;
	}

	$isInstructors = logistics_isEventInstructors($_SESSION['eventID']);

?>
	<li>
		<a href='#'>Event Information</a>
		<ul class='menu vertical'>
			<li><a href='infoSummary.php?t=0'>Information/Results</a></li>
			<li><a href='participantsEvent.php?t=0'>Event Roster</a></li>
			<li><a href='logisticsSchedule.php?t=0'>Schedule</a></li>
			<li><a href='infoRules.php?t=0'>Tournament Rules</a></li>

			<li>
				<a href='#'>Event Stats</a>
				<ul class='menu vertical'>
					<li><a href='statsEvent.php?t=0'>Attendance/Schools</a></li>
					<li><a href='statsTournaments.php?t=0'>Tournament Exchanges</a></li>
					<li><a href='statsWorkshops.php?t=0'>Workshops</a></li>
				</ul>
			</li>

			<li>
				<a href='#'>Custom Schedules</a>
				<ul class='menu vertical'>
					<li><a href='logisticsSchedule.php?t=0'>Main Schedule</a></li>
					<li><a href='participantsFiltered.php?t=0'>Matches by School</a></li>
					<li><a href='infoScheduleWorkshops.php?t=0'>Class Schedule</a></li>
					<li><a href='participantsSchedules.php?t=0'>Individual Schedules</a></li>
				</ul>
			</li>

			<?php if($isInstructors == true): ?>
				<li><a href='logisticsInstructors.php?t=0'>Event Instructors</a></li>
			<?php endif ?>

		</ul>
	</li>
<?php }

/******************************************************************************/

function menuTournament(){

	if($_SESSION['tournamentID'] == null){
		return;
	}

?>
	<li>
		<a href='#'>Tournament Information</a>
		<ul class='menu vertical'>
			<li><a href='participantsTournament.php'>Roster</a></li>
			<li><a href='videoMatchList.php'>Video Links</a></li>

			<?php if( $_SESSION['formatID'] == FORMAT_MATCH): ?>
				<li><a href='statsFighterSummary.php'>Exchanges By Fighter</a></li>
			<?php endif ?>

			<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
				<li><div class="drop-down-separator">During</div></li>
				<li><a href='adminFighters.php'>Withdraw Fighters</a></li>

				<li><div class="drop-down-separator">Before</div></li>
				<li>
					<a href='#'><b>Settings</b></a>
					<ul class='menu vertical'>
						<li><a href='adminTournaments.php'>Settings for: <b><?=getTournamentName($_SESSION['tournamentID'])?></b></a></li>
						<li><a href='adminNewTournaments.php'>Create New Tournament</a></li>
					</ul>
				</li>
				<li><a href='participantsRatings.php'>Fighter Ratings</a></li>

				<li><a href='statsScoresheets.php'>Scoresheets</a></li>
			<?php endif ?>

		</ul>
	</li>
<?php }

/******************************************************************************/
function menuEventOrg(){

	if(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false){
		return;
	}
	if($_SESSION['eventID'] == 0){
		return;
	}

	$eventDates = getEventDates($_SESSION['eventID']);

	if(compareDates($eventDates['eventStartDate']) < 0){
		$menuOrder = ['menuEventOrgBefore','menuEventOrgDuring','menuEventOrgAfter'];
	} elseif (compareDates($eventDates['eventEndDate']) <= 0) {
		$menuOrder = ['menuEventOrgDuring','menuEventOrgBefore','menuEventOrgAfter'];
	} else {
		$menuOrder = ['menuEventOrgAfter','menuEventOrgDuring','menuEventOrgBefore'];
	}
?>
	<li>
		<a href='#'>Event Organization</a>
		<ul class='menu vertical'>

			<?php
				foreach($menuOrder as $functionName){
					call_user_func($functionName);
				}
			?>

		</ul>
	</li>
<?php }

/******************************************************************************/

function menuEventOrgBefore(){
?>

	<div class="drop-down-separator">Before</div>

	<li>
		<a href='#'><b>Event Settings</b></a>
		<ul class='menu vertical'>
			<li><a href='adminEvent.php?t=0'><b>Event Settings</b></a></li>
			<li><a href='adminNewTournaments.php?t=0'>Create New Tournament</a></li>
			<li><a href='adminDivisions.php?t=0'>Tournament Divisions</a></li>
		</ul>


	<li>
		<a href='#'>Schedule</a>
		<ul class='menu vertical'>
			<li><a href='logisticsSchedule.php?t=0'>Edit Schedule</a></li>
			<li><a href='logisticsLocations.php?t=0'>Edit Locations</a></li>
		</ul>
	</li>

	<li><a href='adminBurgees.php?t=0'>School Standings</a></li>

	<?php if(ALLOW['SOFTWARE_ADMIN'] == true): ?>
		<li><a href='adminSponsors.php?t=0'>Sponsors</a></li>
	<?php endif ?>

	<li>
		<a href='#'>Staffing</a>
		<ul class='menu vertical'>

			<div class="drop-down-separator">Setup</div>
			<li><a href='logisticsStaffRoster.php?t=0'>Staff Roster</a></li>
			<li><a href='logisticsInstructors.php?t=0'>Instructors</a></li>
			<li><a href='logisticsStaffShifts.php?t=0'>Shifts</a></li>
			<li><a href='logisticsStaffTemplates.php?t=0'>Shift Templates</a></li>

			<div class="drop-down-separator">Summary</div>
			<li><a href='logisticsParticipantHours.php?t=0'>Hours</a></li>
			<li><a href='logisticsStaffConflicts.php?t=0'>Conflicts</a></li>
			<li><a href='logisticsStaffGrid.php?t=0'>Full Grid</a></li>

		</ul>
	</li>


<?php }

/******************************************************************************/

function menuEventOrgDuring(){
?>
	<li><div class="drop-down-separator">During</div></li>

	<li><a href='logisticsAnnouncements.php?t=0'>Announcements</a><li>

	<li><a href='participantsCheckIn.php?t=0'>Check-In Participants</a></li>

	<li><a href='adminFighterPenalties.php?t=0'>Penalties By Fighter</a></li>

	<li><a href='videoLivestream.php?t=0'>Livestream</a></li>

	<li>
		<a href='#'>Views</a>
		<ul class='menu vertical'>
			<li><a href='infoRingAssignments.php'>Fighter Pool Assignment</a></li>
			<li><a href='infoLocationMatchQueue.php'>Match Queue by Location</a></li>
		</ul>
	</li>
<?php }

/******************************************************************************/

function menuEventOrgAfter(){
	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}
?>
	<div class="drop-down-separator">After</div>

	<li><a href='adminHemaRatings.php?t=0'>HEMA Ratings Submission Form</a></li>
<?php }


/******************************************************************************/

function menuAnalytics(){
?>
	<li>
		<a href='#'>Stats/Analytics</a>
		<ul class='menu vertical'>
			<li><a href='statsMatchLength.php?t=0'>Match Timings</a></li>
			<li><a href='statsScheduleAssistant.php?t=0'>Tournament Time Calculator</a></li>
			<li><a href='participantsAttendance.php?t=0'>Attendance By Fighter</a></li>
			<li><a href='statsResultsDump.php?t=0'>Export Results</a></li>
			<li><a href='participantsSystem.php?t=0'>Full System Roster</a></li>
			<li><a href='statsPlacings.php?t=0'>Placings By Country</a></li>
			<?php if(ALLOW['STATS_ALL'] == true):?>
				<!-- These are ones that don't really work anymore -------->
				<li>
					<a href='#'>DEVEL Views</a>
					<ul class='menu vertical'>
						<li><a href='statsFighters.php?t=0'>Fighter Histories</a></li>
						<li><a href='statsMultiEvent.php?t=0'>Exchange Types By Weapon</a></li>
						<li><a href='statsIndividual.php?t=0'>Fighter Exchanges By Filter</a></li>
					</ul>
				</li>
			<?php endif ?>

		</ul>
	</li>

<?php }

/******************************************************************************/

function menuAdmin(){

	if(ALLOW['SOFTWARE_ADMIN'] == false && ALLOW['SOFTWARE_ASSIST'] == false){

		if(    $_SESSION['userName'] != null
			&& $_SESSION['userName'] != 'eventStaff'
			&& $_SESSION['userName'] != 'eventOrganizer'){
			echo "<li><a href='masterPasswords.php'>Change Password</a></li>";
		}

		return;
	}

?>
	<li>
		<a href='#'>ADMIN</a>
		<ul class='menu vertical'>
			<li><a href='masterEvents.php?t=0'>Manage Events</a></li>
			<li>
				<a href='#'>Database</a>
				<ul class='menu vertical'>
					<li><a href='adminSchools.php?t=0'>Edit School List</a></li>
					<li><a href='masterTournamentTypes.php?t=0'>Tournament Types</a></li>
					<li><a href='cutQuals.php'>Cutting Qualifications</a></li>
				</ul>
			</li>

			<?php if(  ALLOW['SOFTWARE_ADMIN'] == true): ?>
			<li>
				<a href='#'>Data Integrity</a>
				<ul class='menu vertical'>
					<li><a href='masterHemaRatings.php?t=0'>HEMA Ratings</a></li>
					<li><a href='masterDuplicates.php?t=0'>Duplicate Names</a></li>
				</ul>
			</li>
			<?php endif ?>
			<li><a href='masterPasswords.php?t=0'>Change Password</a></li>

		</ul>
	</li>

<?php }

/******************************************************************************/

function activeLivestream(){

	if(isVideoStreamingForEvent($_SESSION['eventID']) == false){
		return;
	}

?>

	<li><a class='button warning hollow no-bottom' href='videoLivestream.php'>Livestream</a></li>

<?php
}

/******************************************************************************/

function displayEventAnnouncements(){

	$eventID = (int)$_SESSION['eventID'];
	if($eventID == 0){
		return;
	}

	if(ALLOW['EVENT_SCOREKEEP'] == true || ALLOW['SOFTWARE_ASSIST'] == true || ALLOW['VIEW_SETTINGS'] == true){
		$showStaffAnnouncements = true;
	} else {
		$showStaffAnnouncements = false;
	}

	if(isset($_SESSION['hideAnnouncement']) == false){
		$_SESSION['hideAnnouncement']= [];
	}

	$announcements 	= (array)logistics_getEventAnnouncments($_SESSION['eventID']);
	$currentTime 	= time();


// - Add a warning if there are ties in the standings that can't be resolved --/
	if(ALLOW['EVENT_SCOREKEEP'] == TRUE){

		$tournamentID = $_SESSION['tournamentID'];
		$tiedFighters = findTiedFighters($tournamentID);
		$poolsActive = isInProgress($tournamentID,'pool');
		$bracketPopulated = isBracketPopulated($tournamentID);

		if($tiedFighters != [] && $poolsActive == false && $bracketPopulated == false){

			$a['message'] = 'There are ties between the following fighters:<ul>';
			foreach($tiedFighters as $fighter){
				$a['message'] .= "<li>{$fighter}</li>";
			}
			$a['message'] .= "</ul>Scorecard has used all specified tiebreakers
				and can not resolve the results. Place has been assigned randomly,
				please take any necessary measures to break the tie if you intend
				to seed a bracket based on this.";

			$a['announcementID'] = -($tournamentID);

			$a['displayUntil'] = $currentTime + 99999;
			$a['visibility'] = 'staff';

			$announcements[] = $a;

		}
	}


// -- Display announcements ---------------------------------------------------/

	foreach($announcements as $a){
		$timeLeft = $a['displayUntil'] - $currentTime;

		if($timeLeft <= 0){
			continue;
		}
		if($a['visibility'] != 'all' && $showStaffAnnouncements == false){
			continue;
		}
		if(isset($_SESSION['hideAnnouncement'][$a['announcementID']]) == true){
			continue;
		}
?>
		<div class='cell callout warning' data-closable>
			<b>Announcement</b><BR>
			<?=$a['message']?>

			<form method="POST">
				<input type='hidden' name='announcementID' value='<?=$a['announcementID']?>'>
				<button class='button hollow no-bottom' name='formName' value='hideAnnouncement'>
					Got it. Stop showing me this.
				</button>
			</form>

			<button class='close-button' aria-label='Dismiss alert' type='button' data-close>
				<span aria-hidden='true'>&times;</span>
			</button>


		</div>
<?

	}


}

/******************************************************************************/

function tournamentLockedAlert($isWarning){
	if(!$isWarning){ return; }
	if(LOCK_TOURNAMENT == null){ return; }
	if(ALLOW['EVENT_SCOREKEEP'] == false && ALLOW['EVENT_MANAGEMENT'] == false){ return; }
	?>

	<div class='callout alert text-center' data-closeable>
		Results for this tournament have been finalized, most changes have been disabled.
		<a href='infoSummary.php#anchor<?=$_SESSION['tournamentID']?>'>Remove final results</a> to edit.
	</div>

<?php }

/******************************************************************************/

function eventNameForHeader(){
// Add the event name or prompty to select an event
	$eventID = $_SESSION['eventID'];
	$page = basename($_SERVER['PHP_SELF']);

	if((ALLOW['SOFTWARE_EVENT_SWITCHING'] == true)
		|| (($_SESSION['userName'] == ''))
			&& (   ($page == 'statsFighterSummary.php')
				|| ($page == 'statsTournaments.php')
				|| ($page == 'statsEvent.php') )): ?>
		<form method='POST'>
		<input type='hidden' name='formName' value='selectEvent'>
		<div class='grid-x align-center'>
		<select class='shrink' name='changeEventTo' onchange='this.form.submit()'>
			<?php eventNameListSelectOptions($eventID) ?>
		</select>
		</div>
		</form>

	<?php elseif($_SESSION['eventName'] != null AND $_SESSION['eventName'] != ' '): ?>
		<?=$_SESSION['eventName']?>
	<?php else: ?>
		No Event Selected
	<?php endif ?>

<?php
}

/******************************************************************************/

function eventNameListSelectOptions($eventID){

	if($eventID == null){
		echo "<option selected disabled>* No Event Selected *</option>";
	}

	$newList = getEventListByPublication(ALLOW['VIEW_HIDDEN'], 'date');
	$allList = getEventListByPublication(ALLOW['VIEW_HIDDEN']);

	// This makes it so when tournaments are twice in the list the top option
	// is the one that is selected.
	$notAlreadySelected = 1;

	echo "<option disabled>-- Recent & Upcoming -------------------------------</option>";

	foreach($newList as $event){
		if(compareDates($event['eventStartDate']) > 14){ continue; }
		if($event['eventID'] == $eventID){
			$notAlreadySelected = 0;
		}
	?>
			<option <?=optionValue($event['eventID'], $eventID)?> >
				&nbsp;&nbsp;<?=$event['eventName']?> <?=$event['eventYear']?>
			</option>
	<?php
	}

	echo "<option disabled>-- Full List ---------------------------------------</option>";

	foreach($allList as $event){
	?>
			<option <?=optionValue($event['eventID'], $eventID * $notAlreadySelected)?> >
				&nbsp;&nbsp;<?=$event['eventName']?> <?=$event['eventYear']?>
			</option>
	<?php
	}

}

/******************************************************************************/

function tournamentListForHeader(){


	$currentTournamenID = $_SESSION['tournamentID'];

	$currentTournamentName = '';
	if($currentTournamenID != null){
		$currentTournamentName = getTournamentName($currentTournamenID);
	}

	$tournamentsToDisplay = sortTournamentAndDivisions($_SESSION['eventID']);

	?>

	<li>

	<?php if($_SESSION['tournamentID'] == null): ?>
		<span class='button success hollow' style='margin-bottom: 0px'>Select Tournament</span>
	<?php else: ?>
		<a href='#' class='button hollow'><?= $currentTournamentName ?></a>
	<?php endif ?>

	<?php if($tournamentsToDisplay != []):?>

		<form method='POST' name='goToTournamentForm' id='goToTournamentForm'>
			<input type='hidden' name='formName' value='changeTournament'>
		</form>

		<ul class='menu vertical'>

		<?php foreach($tournamentsToDisplay as $t):?>

			<li>
				<?php if(isset($t['divisionID']) == false):?>
					<?=headerFormat($t)?>
				<?php else: ?>
					<a><i><?=$t['name']?></i></a>
					<ul class='menu vertical'>
						<?php foreach($t['tournaments'] as $item):?>
							<li><?=headerFormat($item)?></li>
						<?php endforeach?>
					</ul>
				<?php endif ?>

			</li>

		<?php endforeach ?>

		</ul>

	<?php endif ?>

	</li>


<?php }

/******************************************************************************/

function headerFormat($tournament){

	$tournamentID = (int)$tournament['tournamentID'];
	$tournamentName = $tournament['name'];

	$t['isInProgress'] = isInProgress($tournamentID);

	$linkClass = '';
	if($t['isInProgress'] == true){
		$linkClass .= 'bold';
	}

	$format = getTournamentFormat($tournamentID);

	$isMeta = ($format == FORMAT_META);
	$isStarted = isFightingStarted($tournamentID, $isMeta);


	$t['landingPage'] = '';

	// If we are already in a tournament and switching to a new one stay on
	// the same page. But if we aren't in a tournament the landing page will
	// be set to whatever makes the most sense based on the current state
	// of the tournament and it's type.
	if($_SESSION['tournamentID'] == null){

		$t['landingPage'] = 'participantsTournament.php';

		if(ALLOW['VIEW_MATCHES'] == false){
			$format = FORMAT_NONE;
		}

		switch($format){
			case FORMAT_SOLO:{

				if($isStarted == true){
					$t['landingPage'] = 'roundStandings.php';
				} else {
					$t['landingPage'] = 'roundRosters.php';
				}

				break;
			}
			case FORMAT_META:{

				if($isStarted == true){
					$t['landingPage'] = 'poolStandings.php';
				} else {
					$t['landingPage'] = 'participantsComponents.php';
				}

				break;
			}
			case FORMAT_MATCH:{

				if (isBracketPopulated($tournamentID) == true){
					$t['landingPage'] = 'finalsBracket.php';
				} elseif ($isStarted == true){
					$t['landingPage'] = 'poolMatches.php';
				} elseif (isPools($tournamentID) == true){
					$t['landingPage'] = 'poolRosters.php';
				} else {
					$t['landingPage'] = 'participantsTournament.php';
				}

				break;
			}
			case FORMAT_RESULTS:{

				$t['landingPage'] = 'infoSummary.php';
				break;

			}
			default: {

				$t['landingPage'] = 'participantsTournament.php';
				break;

			}
		}

	}

	$linkText = "<a class='{$linkClass}' onclick=\"changeTournamentJs({$tournamentID},'{$t['landingPage']}')\">";
	$linkText .= $tournamentName;
	$linkText .= "</a>";

	return ($linkText);
}

/******************************************************************************/

function debugging(){

	if(defined("SHOW_POST") && SHOW_POST === true){

		if(isset($_SESSION['urlNav']) && defined("SHOW_URL_NAV") && SHOW_URL_NAV === true){

			echo "---- URL_NAV ----------------------------------------------------";
			show($_SESSION['urlNav']);

		} else {
			echo "---- POST -------------------------------------------------------";
			show($_SESSION['post']);
		}
	}

	unset($_SESSION['post']);
	unset($_SESSION['urlNav']);

	if(defined("SHOW_SESSION") && SHOW_SESSION === true){
		echo "---- SESSION ----------------------------------------------------";
		show($_SESSION);
	}

}

/******************************************************************************/

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
