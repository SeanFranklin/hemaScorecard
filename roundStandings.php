<?php
/*******************************************************************************
	Round Standings
	
	Shows the ranked scores of all fighters in the rounds
	LOGIN: N/A
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Round Standings';
$includeTournamentName = true;
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif($tournamentID == null){
	pageError('tournament');
} elseif(!isRounds($tournamentID)){
	if(USER_TYPE < USER_SUPER_ADMIN){
		if(isPools($tournamentID)){redirect('poolStandings.php');}
		if(isBrackets($tournamentID)){redirect('finalsBracket1.php');}
	}
	displayAlert('This is not a scored event<BR>Please navigate to a pool or bracket');
} elseif ((getEventStatus() == 'upcoming' || getEventStatus() == 'hidden') && USER_TYPE < USER_STAFF){
	displayAlert("Event is still upcoming<BR>Rounds not yet released");
} else {
	
	$numGroupSets = getNumGroupSets($tournamentID);
	
	// Omits the accordion menu if there is only one round per set
	$showMultiple = isCumulativeRounds($tournamentID); 
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>	
	
<!--Accordion start -->
	<?php if($showMultiple): ?>
		<ul class='accordion' data-accordion  data-allow-all-closed='true'>
	<?php else: ?>
		<div class='grid-x grid-padding-x grid-margin-x' >
	<?php endif ?>

	<?php for($groupSet = 1; $groupSet <= $numGroupSets; $groupSet++): 
		if($_SESSION['groupSet'] == $groupSet){
			$active = 'is-active';
		} else {
			unset($active);
		}
		
		$rounds = getRounds($tournamentID, $groupSet);
		?>
		
		<!--Accordion item start-->
		<?php if($showMultiple):
			$setName = getSetName($groupSet, $tournamentID); ?>
			<li class='accordion-item <?=$active?>' data-accordion-item>
			<a class='accordion-title'>
				<h4><?=$setName?></h4>
			</a>
			<div class='accordion-content' data-tab-content>
		<?php endif ?>
		
		<?php showRoundStandings($groupSet, $showMultiple); ?>


		<!--Accordion item end-->
		<?php if($showMultiple): ?>
			</div>
			</li>
		<?php endif ?>
	<?php endfor ?>
	
	<!--Accordion end -->
	<?php if($showMultiple): ?>
		</ul>
	<?php else: ?>
		</div>
	<?php endif ?>

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

function showRoundStandings($groupSet, $ownDiv = true){
	$rounds = getRounds($tournamentID, $groupSet);
	$ignores = getIgnores($tournamentID);
	$numRounds = count($rounds);
	?>

	<?php if($ownDiv): ?>
		<div class='grid-x grid-padding-x grid-margin-x'>
	<?php endif ?>
		
	<?php foreach($rounds as $roundInfo):
		
		$groupID = $roundInfo['groupID'];
		$roundName = $roundInfo['groupName'];
		$roundNumber = $roundInfo['groupNumber'];
		$scores = getRoundScores($groupID);
		?>
		
	<!-- Display the standings -->
		<div class='large-4 medium-6 cell'>
		<table class='data_table'>
			
		<!-- Headers -->
			<tr>
				<th colspan='100%'>
					<?=$roundName?>
				</th>
			</tr>
			<tr>
				<th>Place</th>
				<th>Name</th>
				<th>Score</th>
			</tr>
		
		<!-- Data -->
			<?php foreach((array)$scores as $num => $fighter):
				$rosterID = $fighter['rosterID'];
				if($ignores[$rosterID] <= $roundNumber && isset($ignores[$rosterID])){
					continue;
				}
				
				$name = getFighterName($rosterID);	
				$place = $num + 1;
				$score = $fighter['score'];
				
				$temp = $cumulativeScores[$roundNumber-1][$rosterID] + $score;
				$cumulativeScores[$roundNumber][$rosterID] = $temp;
				$highestRound = $roundNumber;
				?>
				
				<tr>
					<td><?=$place?></td>
					<td><?=$name?></td>
					<td><?=$score?></td>
				</tr>
				
			<?php endforeach ?>

		</table>
		
	<!-- Show fighters who haven't completed the round -->
		<?php showRoundIncompleted($groupID); ?>

		</div>
	<?php endforeach ?>
	

<!--  Cumulative scores at end of round -->
	<?php
	unset($scores);
	if($numRounds > 1 && isset($cumulativeScores)):
		foreach($cumulativeScores[$highestRound] as $rosterID => $score){
			$fighterData['rosterID'] = $rosterID;
			$fighterData['score'] = $score;
			$scores[] = $fighterData;
		}
		
		foreach($scores as $key => $entry){
			$sort1[$key] = $entry['score'];
		}
		
		array_multisort($sort1, SORT_DESC, $scores);
		?>
		
		<div class='large-4 medium-6 cell'>
		<table class='data_table'>
			
		<!-- Headers -->
			<tr>
				<th colspan='100%'>
					Round Total
				</th>
			</tr>
			<tr>
				<th>Place</th>
				<th>Name</th>
				<th>Score</th>
			</tr>
		
		<!-- Data -->
			<?php foreach((array)$scores as $num => $fighter):
				$place = $num + 1;
				$name = getFighterName($fighter['rosterID']);
				$score = $fighter['score'];
				?>
				<tr>
					<td><?=$place?></td>
					<td><?=$name?></td>
					<td><?=$score?></td>
				</tr>
			<?php endforeach ?>
		
		</table>
		</div>
		
	<?php endif ?>

	<?php if($ownDiv): ?>
		</div>
	<?php endif ?>

<?php }

/******************************************************************************/

function showRoundIncompleted($groupID){
	$matches= getRoundMatches($groupID);
	
	foreach($matches as $match){
		$rosterID = $match['fighter1ID'];
		$score = $match['fighter1Score'];
		
		if($score === null){ // Has not competed yet
			$notCompleted[] = $rosterID;
			continue;
		}
	}
	?>
	
	<?php if(isset($notCompleted)): ?>
		<u>Round not completed:</u>
		<ul>
		<?php foreach($notCompleted as $rosterID):
			$name = getFighterName($rosterID) ?>
			<li><?=$name?></li>
		<?php endforeach ?>
		<?php unset($notCompleted); ?>
		</ul>
	<?php endif ?>
	
	
<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
