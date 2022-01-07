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
} elseif($_SESSION['formatID'] != FORMAT_SOLO){
	if(ALLOW['VIEW_SETTINGS'] != true){
		redirect('participantsTournament.php');
	}
	displayAlert('This is not a scored event<BR>Please navigate to a pool or bracket');
} elseif (ALLOW['VIEW_MATCHES'] == false){
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
			$active = '';
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


	$rounds = getRounds($_SESSION['tournamentID'], $groupSet);
	$ignores = getIgnores($_SESSION['tournamentID'],'ignoreAtSet');
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
				if(isset($ignores[$rosterID]) && $roundNumber >= $ignores[$rosterID]){
					continue;
				}
				
				$name = getEntryName($rosterID);	
				$place = $num + 1;
				$score = $fighter['score'];
				
				$cumulativeScores[$roundNumber][$rosterID] = $score;
				if($roundNumber > 1){
					$cumulativeScores[$roundNumber][$rosterID] += $cumulativeScores[$roundNumber-1][$rosterID];
				}
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
	$scores = [];
	if($numRounds > 1 && isset($cumulativeScores)):
		foreach($cumulativeScores[$highestRound] as $rosterID => $score){
			$fighterData['rosterID'] = $rosterID;
			$fighterData['score'] = $score;
			$scores[] = $fighterData;
		}
		
		foreach($scores as $key => $entry){
			$sort1[$key] = $entry['score'];
		}
		
		if(isReverseScore($_SESSION['tournamentID']) == REVERSE_SCORE_NO){
			array_multisort($sort1, SORT_DESC, $scores);
		} else {
			array_multisort($sort1, SORT_ASC, $scores);
		}
		?>
		
		<div class='large-4 medium-6 cell'>
		<table class='data_table'>
			
		<!-- Headers -->
			<tr>
				<th colspan='100%'>
					Stage Total
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
				$name = getEntryName($fighter['rosterID']);
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
			$name = getEntryName($rosterID) ?>
			<li><?=$name?></li>
		<?php endforeach ?>
		<?php unset($notCompleted); ?>
		</ul>
	<?php endif ?>
	
	
<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
