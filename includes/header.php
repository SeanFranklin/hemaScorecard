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
    <link rel="stylesheet" href="includes/foundation/css/foundation.css">
    <link rel="stylesheet" href="includes/foundation/css/app.css">
    <link rel="stylesheet" href="includes/foundation/css/custom.css">
    
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
        <?php if(USER_TYPE == USER_GUEST): ?>
			<a href='adminLogIn.php' class='login-link'>Login</a>
		<?php else: ?>
			<input type='hidden' name='logInType' value='1'>
			<input type='hidden' name='formName' value='logUserIn'>
			<a href='javascript:document.logOutForm1.submit();' class='login-link'>Log Out</a>	
		<?php endif ?>
		</form>
    </div>
    
    <!-- Full Navigation -->
    <div class="top-bar" id="tourney-animated-menu" data-animate="hinge-in-from-top hinge-out-from-top" style='display:none'>
        <div class="top-bar-left">
            <ul class="dropdown menu vertical medium-horizontal" data-dropdown-menu>
				<li><?php tournamentListForHeader(); ?></li>
                
                
                
                <?php if(USER_TYPE < USER_ADMIN): ?>
					<li><a href='participantsRoster.php'>Event Participants</a></li>
				<?php endif ?>
				
				<?php if(USER_TYPE < USER_ADMIN && $_SESSION['eventID'] != null): ?>
					<li><a href='infoSummary.php'>Final Results</a></li>
				<?php endif;
				
				
                
                $manageEvent = "
					<li><a href='#'>Manage Fighters</a>
						<ul class='menu vertical'>
							<li><a href='participantsRoster.php'>Event Roster</a></li>
							<li><a href='adminFighters.php'>Withdraw Fighters</a></li>
							<li><a href='participantsImport.php'>Import Roster</a></li>
						</ul>
					</li>
					<li><a href='#'>Manage Event</a>
						<ul class='menu vertical'>
							<li><a href='adminTournaments.php'>Tournament Settings</a></li>
							<li><a href='adminNewTournaments.php'>Add New Tournaments</a></li>
							<li><a href='adminEvent.php'>Event Settings</a></li>
							<!--<li><a href='livestreamManagement.php'>Livestream</a></li>-->
						</ul>
					</li>";
					
				$eventStatus = "
					<li><a href='#'>Event Status</a>
						<ul class='menu vertical'>
							<li><a href='statsEvent.php'>Participants/Schools</a></li>
							<li><a href='statsTournaments.php'>Tournament Stats</a></li>
							<li><a href='infoSummary.php'>Final Results</a></li>
						</ul>
					</li>";
				
				$analytics = "
					<li><a href='#'>Analytics</a>
						<ul class='menu vertical'>
							<li><a href='statsFighters.php'>Fighter Histories</a></li>
							<li><a href='masterResultsDump.php'>Export Results</a></li>
						</ul>
					</li>";
				$masterAdmin = "
					<li><a href='#'>ADMIN</a>
						<ul class='menu vertical'>
							<li><a href='masterEvents.php'>Manage Events</a></li>
							<li><a href='masterPasswords.php'>Manage Passwords</a></li>
							<li><a href='masterResultsDump.php'>Export Results</a></li>
							<li><a href='adminTournamentTypes.php'>Tournament Types</a></li>
							<li><a href='cutQuals.php'>Cutting Qualifications</a></li>
						</ul>
					</li>";
				$videoManager = "
					<li><a href='#'>Video Manager (placeholder)</a></li>
				";
				
				if($_SESSION['eventID'] == null){
					$manageEvent = null;
					$eventStatus = null;
				}
                
                if(USER_TYPE == USER_ADMIN){
					echo "$manageEvent $eventStatus";
				}
				
				if(USER_TYPE == USER_STATS){
					echo "$eventStatus $analytics";
				}
				
				if(USER_TYPE == USER_SUPER_ADMIN){
					echo "$manageEvent $eventStatus $analytics $masterAdmin";
				}
				if(USER_TYPE == USER_VIDEO){
					echo "$videoManager";
				}
				
                ?>
                
				<a href='infoSelect.php'>Change Event</a></li>
				<li><a href='adminHelp.php'>Help/About</a></li>
				
				<?php 	$livestreamInfo = getLivestreamInfo(); 
					if($livestreamInfo['isLive'] == 1): ?>
					<li><a class='button warning hollow' href='livestream.php'>Livestream</a></li>
				<?php endif ?>
				
            </ul>
           
        </div>
        <div class="top-bar-right show-for-large">
			<?php if(USER_TYPE == USER_GUEST): ?>
				<a href='adminLogIn.php' style='color:white'>Login</a>
			<?php else: ?>
				<form method='POST' name='logOutForm2'>
				<input type='hidden' name='logInType' value='1'>
				<input type='hidden' name='formName' value='logUserIn'>
				<a href='javascript:document.logOutForm2.submit();' style='color:white'>Log Out</a>
				</form>	
			<? endif ?>
		</div>
        
    </div>
    <!-- END Upper Navigation ----------------------------------------->	
	
	<!-- START Page Title --------------------------------------------->
	
	<?php if($_SESSION['eventID'] != null && $hidePageTitle != true): ?>
		<div class='hero-title'>
			
		<!-- Event Name -->
		<h1> 
		<?php eventNameForHeader(); ?>
		</h1>
		
		
		<!-- Tournament Name -->
		<?php  if($includeTournamentName == true):
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
		&& ($hideEventNav !== true || USER_TYPE == USER_SUPER_ADMIN)):
		/* This is the lower navigational bar
		 * It will not show up if there is no event or tournament selected,  
		 * or if the pagerequests it to be hidden. It will always be shown
		 * if logged in as a super admin. 
		 * The items that appear in the navigation bar change depending on
		 * the type of tournament which is active. */
		
		// Moved to upper navigation
		//$navBarString = "<li><a href='participantsRoster.php'>Event Participants</a></li>";
		
		if($_SESSION['tournamentID'] != null){
			$navBarString .= "<li><a href='participantsTournament.php'>Tournament Roster</a></li>";
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
		
	<? endif ?>	


	<!-- END Lower Navigation ----------------------------------------->
	
	<?php
	
	livestreamAlert($livestreamInfo, $pageName);
	tournamentLockedAlert($lockedTournamentWarning);
	displayAnyErrors();


// FUNCTIONS //////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function tournamentLockedAlert($isWarning){
	if(!$isWarning){ return; }
	if(LOCK_TOURNAMENT == null){ return; }
	if(USER_TYPE < USER_STAFF){ return; }
	?>
	
	<div class='callout alert text-center' data-closeable>
		Results for this tournament have been finalized, most changes have been disabled.
		<a href='infoSummary.php'>Remove final results</a> to edit.
	</div>
	
<?php }


/******************************************************************************/

function livestreamAlert($info, $pageName){
// Display an alert notifying people about the livestream
	
	if(USER_TYPE >= USER_STAFF){ return; }
	if(strpos($pageName, 'Livestream') !== false) { return; }
	if($_SESSION['hideLivestreamAlert'] == true){ return; }
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
	if(USER_TYPE == USER_SUPER_ADMIN || USERR_TYPE == USER_STATS){
		$eventList = getEventList();
		?>
		<form method='POST'>
		<input type='hidden' name='formName' value='selectEvent'>	
		<div class='grid-x align-center'>
		<select class='shrink' name='changeEventTo' onchange='this.form.submit()'>
			<?php if($eventID == null): ?>
				<option selected disabled></option>
			<?php endif ?>
			<?php foreach($eventList as $listEventID => $data): 
				$selected = isSelected($eventID, $listEventID);
			?>
				<option value=<?=$listEventID?> <?=$selected?>>
					<?=$data['eventName']?> <?=$data['eventYear']?>
				</option>
			<?php endforeach ?>
		</select>
		</div>
		</form>
		<?php
		return;
	}

	if($_SESSION['eventName'] != null AND $_SESSION['eventName'] != ' '){
		echo $_SESSION['eventName'];
	} else {
		echo "No Event Selected";
	}

}

/******************************************************************************/

function tournamentListForHeader(){
	$tournamentList = getEventTournaments();
	$currentTournamentName = getTournamentName();
	$currentTournamenID = $_SESSION['tournamentID'];
	if($tournamentList == null){
		return;
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

	if(SHOW_POST === true){
		echo "---- POST -------------------------------------------------------";
		show($_SESSION['post']);
	}
	unset($_SESSION['post']);
	if(SHOW_SESSION === true){
		echo "---- SESSION ----------------------------------------------------";
		show($_SESSION);
	}
	
}

/******************************************************************************/

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
