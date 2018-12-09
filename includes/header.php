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

$livestreamInfo = getLivestreamInfo();

?>

<!doctype html>
<html class="no-js" lang="en" dir="ltr">

<script>
	<?php
		// Output base URL of site for Javascript use
		$b = BASE_URL;
		echo "var BASE_URL = '$b';";
	?>
</script>

<head>
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
    <link href="https://fonts.googleapis.com/css?family=Chivo:300,400,700" rel="stylesheet">
    <!--<link rel="stylesheet" href="includes/foundation/css/foundation.css">-->
    <link rel="stylesheet" href="includes/foundation/css/app.css">
    <link rel="stylesheet" href="includes/foundation/css/custom.css">

    <link rel='icon' href='includes\images\favicon.png'>
    
    <!-- Jumps to section on page if $_SESSION['jumpTo'] is set -->
	<?php if(isset($_SESSION['jumpTo'])): ?>
		<script>window.onload = window.location.hash='<?=$_SESSION['jumpTo']?>';</script>
		<?php unset($_SESSION['jumpTo']); ?>
	<?php endif ?>
    
    
</head>

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
	
<body>
 <!-- START Upper Navigation ------------------------------------------>
 
	<?php debugging(); ?>

	<!-- Mobile Navigation -->
    <div class="title-bar" data-responsive-toggle="tourney-animated-menu" data-hide-for="large" style='display:none'>
		<form method='POST' name='logOutForm1'>
        <button class="menu-icon" type="button" data-toggle></button>
        <div class="title-bar-title">Menu</div>
        <?php //tournamentListForHeader(); ?>
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
				<li><?php tournamentListForHeader(); ?></li>
                

			<!-- Fighter Management -->
			<?php if($_SESSION['eventID'] != null):?>
				<?php if(    ALLOW['EVENT_SCOREKEEP'] == true 
						  || ALLOW['VIEW_SETTINGS'] == true):?>
					<li><a href='#'>Manage Fighters</a>
						<ul class='menu vertical'>
							<li><a href='participantsEvent.php'>Event Roster</a></li>
							<li><a href='adminFighters.php'>Withdraw Fighters</a></li>
						</ul>
					</li>
				<?php else: ?>
					<li>
						<a href='participantsEvent.php'>Event Participants</a>
					</li>
				<?php endif ?>
			<?php endif ?>
				
			<!-- Event Management -->
			<?php if($_SESSION['eventID'] != null):?>
				<?php if(    ALLOW['EVENT_MANAGEMENT'] == true 
						  || ALLOW['VIEW_SETTINGS'] == true):?>
					<li><a href='#'>Manage Event</a>
						<ul class='menu vertical'>
							<li><a href='adminTournaments.php'>Tournament Settings</a></li>
							<li><a href='adminNewTournaments.php'>Add New Tournaments</a></li>
							<li><a href='adminEvent.php'>Event Settings</a></li>
							<!--<li><a href='livestreamManagement.php'>Livestream</a></li>-->
						</ul>
					</li>
				<?php else: ?>
					<!-- Empty -->
				<?php endif ?>
			<?php endif ?>

			<!-- Event Information -->
			<?php if($_SESSION['eventID'] != null):?>
				<?php if(    ALLOW['EVENT_MANAGEMENT'] == true 
						  || ALLOW['VIEW_SETTINGS'] == true
						  || ALLOW['STATS_EVENT'] == true):?>
					<li><a href='#'>Event Status</a>
						<ul class='menu vertical'>
							<li><a href='statsEvent.php'>Participants/Schools</a></li>
							<li><a href='statsTournaments.php'>Tournament Stats</a></li>
							<li><a href='infoSummary.php'>Final Results</a></li>
						</ul>
					</li>
				<?php else: ?>
					<li>
						<a href='infoSummary.php'>Final Results</a>
					</li>
				<?php endif ?>
			<?php endif ?>

			<!-- Analytics -->
				<?php if(ALLOW['STATS_ALL'] == true):?>
					<li><a href='#'>Analytics</a>
						<ul class='menu vertical'>
							<li><a href='statsFighters.php'>Fighter Histories</a></li>
							<li><a href='statsMultiEvent.php'>Tournament Summaries</a></li>
							<li><a href='statsResultsDump.php'>Export Results</a></li>
						</ul>
					</li>
				<?php else: ?>
					<li>
						<!-- Show nothing -->
					</li>
				<?php endif ?>

			<!-- Change Event -->
				<a href='infoSelect.php'>Change Event</a></li>

			<!-- Software Admin -->
				<?php if(  ALLOW['SOFTWARE_ADMIN'] == true
						|| ALLOW['SOFTWARE_ASSIST'] == true):?>
					<li><a href='#'>ADMIN</a>
						<ul class='menu vertical'>
							<li><a href='masterEvents.php'>Manage Events</a></li>
							<li><a href='participantsSchools.php'>Edit School List</a></li>
							<li><a href='adminTournamentTypes.php'>Tournament Types</a></li>
							<li><a href='cutQuals.php'>Cutting Qualifications</a></li>
							<li><a href='masterPasswords.php'>Manage Passwords</a></li>
							<?php if(  ALLOW['SOFTWARE_ADMIN'] == true): ?>
								<HR>
								<li><a href='masterHemaRatings.php'>HEMA Ratings</a></li>
								<li><a href='masterDuplicates.php'>Duplicate Names</a></li>
							<?php endif ?>
						</ul>
					</li>
				<?php else: ?>
					<?php if($_SESSION['userName'] != null
						&& $_SESSION['userName'] != 'eventStaff'
						&& $_SESSION['userName'] != 'eventAdmin'): ?>

						<li><a href='masterPasswords.php'>Change Password</a></li>

					<?php endif ?>
				<?php endif ?>


			<!-- Help Page -->
				<li><a href='adminHelp.php'>Help/About</a></li>
				
			<!-- Livestream -->
				<?php if($livestreamInfo['isLive'] == 1): ?>
					<li><a class='button warning hollow' href='livestream.php'>Livestream</a></li>
				<?php endif ?>
				
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
    <!-- END Upper Navigation ----------------------------------------->	
	
	<!-- START Page Title --------------------------------------------->
	
	<?php if($_SESSION['eventID'] != null && !isset($hidePageTitle)): ?>
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

							
		// Tournament has pools
		if(isPools($_SESSION['tournamentID'])){
			$navBarString .= "<li><a href='poolRosters.php'>Pool Rosters</a></li>
								<li><a href='poolMatches.php'>Pool Matches</a></li>
								<li><a href='poolStandings.php'>Pool Standings</a></li>";
		}
		
		// Tournament has brackets
		if(isBrackets($_SESSION['tournamentID'])){
			if(isDoubleElim()){
				$navBarString .= "<li><a href='finalsBracket1.php'>Winners Bracket</a></li>
									<li><a href='finalsBracket2.php'>Consolation Bracket</a></li>";
			} else {
				$navBarString .= "<li><a href='finalsBracket1.php'>Finals Bracket</a></li>";
			}

		}
		
		// Tournament has rounds
		if(isRounds($_SESSION['tournamentID'])){
			$navBarString .= "<li><a href='roundRosters.php'>Round Rosters</a></li>
								<li><a href='roundMatches.php'>Round Scores</a></li>
								<li><a href='roundStandings.php'>Round Standings</a></li>";
		}
		
		// Tournament has cutting quallification
		if(isCuttingQual()){ 				
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
	
	<?php
	
	livestreamAlert($livestreamInfo, $pageName);
	if(isset($lockedTournamentWarning)){
		tournamentLockedAlert($lockedTournamentWarning);
	}
	displayPageAlerts();


// FUNCTIONS //////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

function livestreamAlert($info, $pageName){
// Display an alert notifying people about the livestream
	
	if(strpos($pageName, 'Livestream') !== false) { return; }
	if(isset($_SESSION['hideLivestreamAlert']) && $_SESSION['hideLivestreamAlert'] == true){ return; }
	if($info['isLive'] != 1){ return; }
	
	?>
	

	<div class='callout success text-center pointer' data-closable 
		onclick="javascript:location.href='livestream.php'"
		style='hover: pointer'>
		
		<div class='grid-x align-center'>
			<div class='small-11'>
				There is an active livestream for this event.
				<a href='livestream.php'>Check it out</a>
			</div>
		</div>
		
		<form method='POST'>
		<button class='close-button' aria-label='Dismiss alert' data-close
			name='formName' value='hideLivestreamAlert'>
			<span aria-hidden='true'>&times;</span>
		</button>
		</form>
	</div>


<?php }

/******************************************************************************/
function eventNameForHeader(){
// Add the event name or prompty to select an event

	$eventID = $_SESSION['eventID'];
	if(ALLOW['SOFTWARE_EVENT_SWITCHING'] == true): ?>
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

	
	$eventList[] = getEventList('hidden');
	$eventList[] = getEventList('upcoming');
	$eventList[] = getEventList('active');
	$eventList[] = getEventList('archived');

	if($eventID == null){
		echo "<option selected disabled></option>";
	} else {
		foreach($eventList as $listPart){
			foreach((array)$listPart as $listEventID => $data){ ?>
				<option <?=optionValue($listEventID, $eventID)?> >
					<?=$data['eventName']?> <?=$data['eventYear']?>
				</option>
			<?php }
		}
	}
}

/******************************************************************************/

function tournamentListForHeader(){

	$tournamentList = getEventTournaments($_SESSION['eventID']);

	if($tournamentList == null){
		return;
	}

	$currentTournamenID = $_SESSION['tournamentID'];
	$currentTournamentName = '';
	if($currentTournamenID != null){
		$currentTournamentName = getTournamentName();
	}
	
	
	?>
	
	<li>
		
	<?php if($_SESSION['tournamentID'] == null): ?>
		<span class='button success hollow' style='margin-bottom: 0px'>Select Tournament</span>
	<?php else: ?>
		<a href='#' class='button hollow'><?= $currentTournamentName ?></a>
	<?php endif ?>
	
	<?php if(count($tournamentList) > 1): ?>
		<ul>
		
		<?php foreach($tournamentList as $tournament):
			$tournamentID = $tournament;
			if($tournamentID == $currentTournamenID){
				continue;
			}
			$tournamentName = getTournamentName($tournamentID);
			$link = "<a href='javascript:document.goToTournament{$tournamentID}.submit();'>{$tournamentName}</a>";
			?>
			
			
			<form method='POST' name='goToTournament<?= $tournamentID; ?>'>
			<input type='hidden' name='formName' value='changeTournament'>
			<input type='hidden' name='newTournament' value=<?= $tournamentID; ?>>
			
			<?php if(isInProgress($tournamentID)): ?>
				<strong><?= $link ?></strong>
			<?php else: ?>
				<?= $link ?>
			<?php endif ?>	
				
			</form>
		<?php endforeach ?>
		</ul>
	<?php endif ?>
	</li>
			
			
<?php }

/******************************************************************************/

function debugging(){

	if(defined("SHOW_POST") && SHOW_POST === true){
		echo "---- POST -------------------------------------------------------";
		show($_SESSION['post']);
	}
	unset($_SESSION['post']);
	if(defined("SHOW_SESSION") && SHOW_SESSION === true){
		echo "---- SESSION ----------------------------------------------------";
		show($_SESSION);
	}
	
}

/******************************************************************************/

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
