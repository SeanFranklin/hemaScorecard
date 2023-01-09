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
} elseif($_SESSION['formatID'] != FORMAT_MATCH){
	if($_SESSION['formatID'] == FORMAT_SOLO && ALLOW['VIEW_SETTINGS'] == false){
		// redirects to the rounds if they happen to go to the pools
		// page while in a rounds tournament
		redirect('roundMatches.php');
	}
	displayAlert("There are no pools for this tournament");
} elseif (ALLOW['VIEW_MATCHES'] == false){
	displayAlert("Event is still upcoming<BR>Pools not yet released");
} else {
		
	$poolSet = $_SESSION['groupSet'];
	$matchList = getPoolMatches($tournamentID, 'all', $poolSet);
	$tournamentName = getTournamentName($tournamentID);
	$matchScores = getAllPoolScores($tournamentID, $poolSet);
	$schoolIDs = getTournamentFighterSchoolIDs($tournamentID);

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

	$hide = getItemsHiddenByFilters($tournamentID, $_SESSION['filters']);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>	
	
	<?php poolSetNavigation(); ?>

	<?=activeFilterWarning()?>

<!-- Navigation to pools -->
	<a name='topOfPage'></a>
	<?php if($matchList != null): ?>
		<ul class='dropdown menu align-center pool-switcher' data-dropdown-menu>	  
			<li>
			<button class='dropdown button'>Jump to Pool : <?=$tournamentName?></button>
			<ul class='menu'>
			<?php foreach($matchList as $groupID => $group): ?>
				<?php $name = "{$group['groupName']}";
				if(isset($poolsInProgress[$groupID])){
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
		<?php displayAlert('No Scheduled Matches'); ?>
	<?php endif ?>	
		
<!-- Display pools -->	
	<?php foreach((array)$matchList as $groupID => $pool): 
		$locationName = logistics_getGroupLocationName($groupID);
		$matchNum = 0;
		if(isset($hide['group'][$groupID]) == true){
			continue;
		}
		?>
		
		<a name='group<?=$groupID?>'></a>
		<h5>
			<?=$pool['groupName']?>
			<?php if($locationName != null): ?>
				<em style="font-size:.8em">(<?=$locationName?>)</em>
			<?php endif ?>
			<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
				<a style="font-size:.8em" onclick="toggleClass('check-in-<?=$groupID?>')">
					Check Fighters In ↓
				</a>
			<?php endif ?>
		</h5>
		
		<div class='grid-x grid-margin-x' name='group<?=$groupID?>'>

			<?php  
			checkFightersIn($groupID);

			foreach ($pool as $matchID => $match){
				
				if(gettype($match) == 'array' && isset($hide['match'][$matchID]) == false){
					$matchNum++;
					displayMatch($matchID, $match, $matchScores, $matchNum, $schoolIDs);
				}	
			} ?>
		</div>

		<a href='#topOfPage'>Back to Top</a>
		<HR>

	<?php endforeach ?>    

	<?=changeParticipantFilterForm($_SESSION['eventID'])?>

	<?php // Auto refresh function if matches are inprogress
	$time = autoRefreshTime(isInProgress($tournamentID, 'pool')); ?>
	<script>window.onload = function(){autoRefresh(<?=$time?>);}</script>
	
	
<?php }	
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function checkFightersIn($groupID){

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

	$groupRoster = getGroupRoster($groupID);
	$startOfForm = "";

	foreach($groupRoster as $person){
		$tmp['name'] = getFighterName($person['rosterID']);
		$tmp['form'] = "checkInFighters[group][{$groupID}][{$person['rosterID']}]";

		if($person['groupCheckIn'] == SQL_TRUE){
			$tmp['checkin'] = 'checked';
		} else {
			$tmp['checkin'] = '';
		}
		if($person['groupGearCheck'] == SQL_TRUE){
			$tmp['gearcheck'] = 'checked';
		} else {
			$tmp['gearcheck'] = '';
		}
		$fighterList[] = $tmp;
	}


?>
	<div class='large-12 cell hidden check-in-<?=$groupID?>'>
		
		<form method='POST'>
		<table>
			<tr>
				<th>Name</th>
				<th>Check-In</th>
				<th>Gear Check</th>
				<th>Update</th>
			</tr>
			<?php foreach($fighterList as $fighter): ?>
				<tr>
					<td>
						<?=$fighter['name']?>
					</td>
				
				<td>

					<div class='switch text-center no-bottom'>
						<input type='hidden' name='<?=$fighter['form']?>[checkin]' value='0'>
						<input class='switch-input' type='checkbox' 
							id='<?=$fighter['form']?>[checkin]' <?=$fighter['checkin']?>
							name='<?=$fighter['form']?>[checkin]' value='1'>
						<label class='switch-paddle' for='<?=$fighter['form']?>[checkin]'>
						</label>
					</div>
				</td>

				<td>

					<div class='switch text-center no-bottom'>
						<input type='hidden' name='<?=$fighter['form']?>[gearcheck]' value='0'>
						<input class='switch-input' type='checkbox' 
							id='<?=$fighter['form']?>[gearcheck]' <?=$fighter['gearcheck']?>
							name='<?=$fighter['form']?>[gearcheck]' value='1'>
						<label class='switch-paddle' for='<?=$fighter['form']?>[gearcheck]'>
						</label>
					</div>
				</td>

				<td class='text-center'>
					<button class='button success hollow tiny no-bottom' 
						name='formName' value='checkInFighters'>
						<strong>✅</strong>
					</button>
				</td>

				</tr>
			<?php endforeach ?>

	</table>
		</form>


	</div>
	
<?php
}


/******************************************************************************/

function displayMatch($matchID,$match, $matchScores, $matchNum = null, $schoolIDs){
// displays the fighters and score of a match. Also button to go to the match	

	if(NAME_MODE == 'lastName'){
		$topName = 'lastName';
		$bottomName = 'firstName';
		$extra = ",";
	} else {
		$topName = 'firstName';
		$bottomName = 'lastName';
		$extra = '';
	}
	

	$id1 = $match['fighter1ID'];
	$id2 = $match['fighter2ID'];
	$schoolID1 = (int)@$schoolIDs[$id1]; // If they don't have a school then ID will be zero
	$schoolID2 = (int)@$schoolIDs[$id2]; // If they don't have a school then ID will be zero

	$nameData1 = getCombatantName($id1, 'split');
	$nameData2 = getCombatantName($id2, 'split');

	$topName1 = $nameData1[$topName].$extra;
	$bottomName1 = $nameData1[$bottomName];
	
	$topName2 = $nameData2[$topName].$extra;
	$bottomName2 = $nameData2[$bottomName];

	$winnerID = $match['winnerID'];
	
	// Weights of the fonts for fighter 1 and 2
	$t1 = "h6";
	$t2 = "h6";

	if(isset($match['endType'])){
		$endType = $match['endType'];
	} else {
		$endType = null;
	}

	$divClass = '';
	switch($endType){
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
		case null:
		default:
			$divClass = 'match-incomplete';
			break;
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

<?php 
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
