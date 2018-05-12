<?php
/*******************************************************************************
	Pool Matches
	
	Displays all pool matches
	LOGIN: N/A
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Pool Matches';
include('includes/header.php');
$tournamentID = $_SESSION['tournamentID'];

if($tournamentID == null){
	pageError('tournament');
} elseif(!isPools($tournamentID)){
	if(isRounds($tournamentID) && USER_TYPE < USER_SUPER_ADMIN){
		// redirects to the rounds if they happen to go to the pools
		// page while in a rounds tournament
		redirect('roundMatches.php');
	}
	displayAnyErrors("There are no pools for this tournament");
} elseif ((getEventStatus() == 'upcoming' || getEventStatus() == 'hidden') && USER_TYPE < USER_STAFF){
	displayAnyErrors("Event is still upcoming<BR>Pools not yet released");
} else {
		
	$poolSet = $_SESSION['groupSet'];
	$matchList = getMatches($tournamentID, 'all', $poolSet);
	$tournamentRoster = getTournamentRoster($tournamentID, '','full');
	$schoolList = getSchoolList('schoolID');
	$tournamentName = getTournamentName($tournamentID);
	$matchScores = getAllPoolScores($tournamentID, $poolSet);
	
	foreach((array)$matchList as $groupID => $pool){
		$incompletes = false;
		$completes = false;
		
		foreach ($pool as $matchID => $match){
			
			if(gettype($match) != 'string'){
				if($match['ignoreMatch'] == 1){continue;}
				if($match['isComplete'] == true){ $completes = true; }
				if($match['isComplete'] == false) { $incompletes = true; }
			}	
		}

		if($incompletes && $completes){
			$poolsInProgress[$groupID] = true;
		}	
	}
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>	
	
	<?php poolSetNavigation(); ?>
	
<!-- Navigation to pools -->
	<a name='topOfPage'></a>
	<?php if($matchList != null): ?>
		<ul class='dropdown menu align-center pool-switcher' data-dropdown-menu>	  
			<li>
			<button class='dropdown button'>Jump to Pool : <?=$tournamentName?></button>
			<ul class='menu'>
			<?php foreach($matchList as $groupID => $group): ?>
				<?php $name = "{$group['groupName']}";
				if($poolsInProgress[$groupID] == true){
					$inProgress = '- In Progress';
				} else { 
					$inProgress = null;
				} ?>
				<li><a href='#group<?=$groupID?>'><?=$name?> <?=$inProgress?></a></li>
			<?php endforeach ?>		
			</ul>
			</li>
		</ul>

	<?php else: ?>
		<?php displayAnyErrors('No Scheduled Matches'); ?>
	<?php endif ?>	
		
<!-- Display pools -->	
	<?php foreach((array)$matchList as $groupID => $pool): ?>
		
		<a name='group<?=$groupID?>'></a>
		<h5><?=$pool['groupName']?></h5>
		
		<div class='grid-x grid-margin-x' name='group<?=$groupID?>'>	
			<?php $matchNum = 0;
			foreach ($pool as $matchID => $match){
				
				if(gettype($match) == 'array'){
					$matchNum++;
					displayMatch($matchID, $match, $matchScores, $matchNum);	
				}	
			} ?>
		</div>
		<a href='#topOfPage'>Back to Top</a>
		<HR>
	<?php endforeach ?>    

	<?php // Auto refresh function if matches are inprogress
	$time = autoRefreshTime(isInProgress($tournamentID, 'pool')); ?>
	<script>window.onload = function(){autoRefresh(<?=$time?>);}</script>
	
	
<?php }	
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayMatch($matchID,$match, $matchScores, $matchNum = null){
// displays the fighters and score of a match. Also button to go to the match	

	if(NAME_MODE == 'lastName'){
		$topName = 'lastName';
		$bottomName = 'firstName';
		$extra = ",";
	} else {
		$topName = 'firstName';
		$bottomName = 'lastName';
	}
	
	$id1 = $match['fighter1ID'];
	$nameData = getFighterName($id1, 'split');
	$topName1 = $nameData[$topName].$extra;
	$bottomName1 = $nameData[$bottomName];
	$school1 = $names[$id1]['school'];
	
	$id2 = $match['fighter2ID'];
	$nameData = getFighterName($id2, 'split');
	$topName2 = $nameData[$topName].$extra;
	$bottomName2 = $nameData[$bottomName];
	$school2 = $names[$id2]['school'];
	
	$winnerID = $match['winnerID'];
	
	// Weights of the fonts for fighter 1 and 2
	$t1 = "h6";
	$t2 = "h6";

	switch($match['endType']){
		case null:
		
			$divClass = 'match-incomplete';
			break;
		case 'ignore':
			$divClass = 'match-ignore';
			break;
		case 'doubleOut':
			$divClass = 'match-doubleOut';
			break;
		case 'tie':
			break;
		case 'winner':
			if($winnerID == $id1){
				$t1 = "h5";
			} else if ($winnerID == $id2){
				$t2 = "h5";
			}
			break;
		default:
	}
	
	$code1 = COLOR_CODE_1;
	$code2 = COLOR_CODE_2;
	
	// The scores to display
	if($matchScores[$matchID]['fighter1Score'] != null){
		$score1 = $matchScores[$matchID]['fighter1Score'];
	} else {
		$score1 = '-';
	}
	if($matchScores[$matchID]['fighter2Score'] != null){
		$score2 = $matchScores[$matchID]['fighter2Score'];
	} else {
		$score2 = '-';
	}
	
	
// MATCH DISPLAY /////////////////////////////////////
	?>
	
	<div class='small-12 medium-6 large-3 cell match-item <?=$divClass?>'>
		<a name='anchor<?=$matchID?>' style='display:inline'></a>
		<form method='POST' name='goToMatch<?=$matchID?>'>
			<input type='hidden' name='formName' value='goToMatch'>
			<input type='hidden' name='matchID' value='<?=$matchID?>'>
			<a href='javascript:document.goToMatch<?=$matchID?>.submit();'>
				Match <?=$matchNum?>
			</a>
		</form>
		
		<ul>
			<li class='person fighter_1_color'>
				<<?=$t1?>><?=$topName1?><BR><?=$bottomName1?></<?=$t1?>>
				<<?=$t1?>><?=$score1?></<?=$t1?>>	
			</li>
			<li class='versus-item'>
				vs</li>
			<li class='person fighter_2_color'>
				<<?=$t2?>><?=$topName2?><BR><?=$bottomName2?></<?=$t2?>>
				<<?=$t2?>><?=$score2?></<?=$t2?>>
			</li>
		</ul>
	</div>

<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
