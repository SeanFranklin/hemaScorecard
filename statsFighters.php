<?php
/*******************************************************************************
	Fighter History
	
	Displays the agregate historical data of all fighters matching 
	the filters applied.
	Also an example of bad design because all the back-end is on this page too
	LOGIN:
		- SUPER ADMIN and STATS users can view/use
		
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Fighter Data';
$hideEventNav = true;
$hidePageTitle = true;
include('includes/header.php');

if(USER_TYPE <= USER_ADMIN && USER_TYPE != USER_STATS){
	pageError('user');
} else {
	
	DEFINE(DEFAULT_THRESHOLD, 60);
	$weaponID = $_SESSION['dataFilters']['weaponID'];

	$filterFields = getFilterFields();
	filterBoxes($filterFields);

	$name = getTournamentAttributeName($weaponID);
	
	$order = $_SESSION['dataFilters']['sortOrder'];

	if($order == SORT_DESC){
		$order = 'descending';
	} elseif($order == SORT_ASC){
		$order = 'ascending';
	}
	
	$isNew = $_SESSION['dataFilters']['newQuery'];
	unset($_SESSION['dataFilters']['newQuery']);
	
	?>
	
	<div class='grid-x'>
		<div class='small-12 medium-10 text-center align-self-middle'>
			<?php if($isNew): ?>
				Showing results for <strong><?=$name?></strong>
				with a minimum of <em><?=$_SESSION['dataFilters']['threshold']?></em> exchanges.
				Results sorted by <?=$order?>
				<em><?=$filterFields[$_SESSION['dataFilters']['sortKey']]?></em>
			<?php endif ?>
		</div>
		<div class='small-12 medium-2'>
			<button class='button expanded' data-open='filterBoxes'>
				New Query
			</button>
		</div>
	</div>

<?php
	if($isNew){
		if($_SESSION['dataFilters']['threshold'] == null){
			$_SESSION['dataFilters']['threshold'] = DEFAULT_THRESHOLD;
		}

		$fighters = calculateFighterStats($weaponID); // Needs to know how many fighters to search
		$fighters = sortFighters($fighters);
		displayFighters($fighters, $weaponID);
	}
	
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function filterBoxes($filterFields){
	
	$threshold = $_SESSION['dataFilters']['threshold'];
	$weaponID = $_SESSION['dataFilters']['weaponID'];
	$weaponList = getTournamentWeaponsList();
	
	if($_SESSION['dataFilters']['sortOrder'] == SORT_ASC){
		$ascSelected = "selected";
	}
	if($_SESSION['dataFilters']['sortOrder'] == SORT_DESC){
		$descSelected = "selected";
	}
	
	?>
	

	
	<div class='reveal large' id='filterBoxes' data-reveal >
	<form method='POST'>
	<div class='grid-x grid-margin-x'>
			
<!-- Weapon select -->	
	<div class='input-group small-12 medium-6 large-3 cell'>
		<span class='input-group-label inline'>Weapon:</span>
		<select class='input-group-field' name='weaponID'>
			<?php foreach($weaponList as $weapon):
				if($weapon['numberOfInstances'] == 0){continue;}
				$id = $weapon['tournamentTypeID'];
				$name = $weapon['tournamentType'];
				$selected = isSelected($id, $weaponID);
				?>
				<option value='<?=$id?>' <?=$selected?>><?=$name?></option>
			<?php endforeach ?>
		</select>
	</div>
		
<!-- Minimum exchange threshold -->
	<div class='input-group small-12 medium-6 large-3 cell'>
		<span class='input-group-label inline'>Threshold:
			<?php tooltip("Minimum number of exchanges a fighter must have to be included"); ?>
		</span>
		<input class='input-group-field' type='number' size='1' name='threshold' value='<?=$threshold?>' placeholder='60'>
	</div>	

	
<!-- Sort By -->
	<div class='input-group large-6 medium-12 small-12 cell'>
		<span class='input-group-label inline'>Sort By:</span>
		<select class='input-group-field ' name='sortKey'>
			<option></option>
			<?php foreach($filterFields as $id => $name): 
				if($_SESSION['dataFilters']['sortKey'] === $id){
					$selected = 'selected';
				} else {
					$selected = null;
				}?>
				<option value='<?=$id?>' <?=$selected?>>
					<?=$name?>
				</option>
			<?php endforeach ?>
		</select>

		<select  class='input-group-field' name='sortOrder'>
			<option value='asc' <?=$ascSelected?>>Low -> High</option>
			<option value='desc' <?=$descSelected?>>High -> Low</option>
		</select>
	</div>
		
<!-- Filter Fields -->
	
	<div class='medium-10 cell'>
	<div class='grid-x'>
		
	<?php for($j=1;$j<=7;$j++):
		//Hides certain filter fields after a certain number for smaller displays
		if($j > 4){
			$extra = 'show-for-large';
		} elseif($j > 2){
			$extra = 'hide-for-small-only';
		} else {
			unset($extra);
		}
		?>
		
		<div class='small-12 medium-6 large-3 <?=$extra?>'>
		
		<select name='filterField[<?=$j?>]'>
			<option></option>
			<?php foreach($filterFields as $id => $name):
				if($_SESSION['dataFilters']['filters'][$j] === $id){
					$selected = 'selected';
				} else {
					$selected = null;
				} ?>
				<option value='<?=$id?>' <?=$selected?>>
					<?=$name?>
				</option>
			<?php endforeach ?>
		</select>
		</div>
		
	<?php endfor ?>
	
	</div>
	</div>
	
<!-- Submit button -->
	<div class='medium-2 cell'>
		<button class='button expanded' name='formName' value='dataFilters'>
			Lookup
		</button>
	</div>
	
	
	</div>
	</form>
	
	<?php closeRevealButton(); ?>
	
	</div>
	

	
<?php }

/******************************************************************************/

function displayFighters($fighters){
	if($fighters == null){
		echo "<HR>No Results to Display<HR>";
		return;
	}
	
	$filterFields = getFilterFields();
	
	$fieldID = $_SESSION['dataFilters'][1];
	?>
	
	<table>
		<tr>
			<th>Name</th>
	
			<?php foreach((array)$_SESSION['dataFilters']['filters'] as $id): ?>
				<th>
					<?=$filterFields[$id]?>
				</th>
			<?php endforeach ?>

		</tr>

	<?php foreach($fighters as $data):
		$name = getFighterNameSystem($data['systemRosterID']);
		?>
		
		<tr>
			<td><?=$name?></td>
			<?php foreach((array)$_SESSION['dataFilters']['filters'] as $id): ?>
				<td>
					<?=$data[$id]?>
				</td>
			<?php endforeach ?>
		</tr>
	<?php endforeach ?>
	
	</table>
	
<?php }

/******************************************************************************/

function sortFighters($fighters){
	if($fighters == null){return;}
	
	$sortIndex = $_SESSION['dataFilters']['sortKey'];
	$sortOrder = $_SESSION['dataFilters']['sortOrder'];
	$threshold = $_SESSION['dataFilters']['threshold'];
	
	foreach($fighters as $systemRosterID => $data){
		if($data['totalScoring'] < $threshold){
			unset($fighters[$systemRosterID]);
			continue;
		}
		$key[] = $data[$sortIndex];
	}
	if($sortIndex == null || $sortOrder == null){return $fighters;}
	if($key == null){return;}
	
	
	array_multisort($key, $sortOrder, $fighters);
	return $fighters;
	
}

/******************************************************************************/

function calculateFighterStats($weaponID){

	$systemRosterIDs = getSystemRoster();

	foreach($systemRosterIDs as $systemRosterID){
	
		$isScoringFighter = true;
		$a = getFighterExchanges($systemRosterID,$weaponID);
		foreach($a as $ex){
			switch($ex['exchangeType']){
				case 'clean':
					if($isScoringFighter){
						$fighters[$systemRosterID]['cleanHitsFor'] += 1;
						$fighters[$systemRosterID]['totalHitsFor'] += 1;
						$fighters[$systemRosterID]['pointsFor'][$ex['scoreValue']] += 1;
					} else {
						$fighters[$systemRosterID]['cleanHitsAgainst'] += 1;
						$fighters[$systemRosterID]['totalHitsAgainst'] += 1;
						$fighters[$systemRosterID]['pointsAgainst'][$ex['scoreValue']] += 1;
					}
					$fighters[$systemRosterID]['clean'] += 1;
					$fighters[$systemRosterID]['totalExchanges'] += 1;
					$fighters[$systemRosterID]['totalScoring'] += 1;
					break;
				case 'double':
					$fighters[$systemRosterID]['double'] += 1;
					$fighters[$systemRosterID]['bilateral'] += 1;
					$fighters[$systemRosterID]['totalExchanges'] += 1;
					$fighters[$systemRosterID]['totalScoring'] += 1;
					break;
				case '*';
					$isScoringFighter = false;
					break;
				case 'afterblow':
					if($isScoringFighter){
						$fighters[$systemRosterID]['afterblowsHitWith'] += 1;
						$fighters[$systemRosterID]['totalHitsFor'] += 1;
						$fighters[$systemRosterID]['pointsFor'][$ex['scoreValue']] += 1;
					} else {
						$fighters[$systemRosterID]['afterblowsLanded'] += 1;
						$fighters[$systemRosterID]['totalHitsAgainst'] += 1;
						$fighters[$systemRosterID]['pointsAgainst'][$ex['scoreValue']] += 1;
					}
					$fighters[$systemRosterID]['afterblow'] += 1;
					$fighters[$systemRosterID]['bilateral'] += 1;
					$fighters[$systemRosterID]['totalExchanges'] += 1;
					$fighters[$systemRosterID]['totalScoring'] += 1;
					break;
				case 'noExchange':
					$fighters[$systemRosterID]['noExchange'] += 1;
					$fighters[$systemRosterID]['totalExchanges'] += 1;
					break;
				case 'winner':
					if($isScoringFighter){
						$fighters[$systemRosterID]['wins'] += 1;
					} else {
						$fighters[$systemRosterID]['losses'] += 1;
					}
					break;
				default:
					break;
			}
		}
		
		
		foreach((array)$fighters as $systemRosterID => $data){
			$fighters[$systemRosterID]['systemRosterID'] = $systemRosterID;
			if($data['totalScoring'] == 0 || $data['cleanHitsAgainst'] == 0
				|| $data['cleanHitsFor'] == 0){continue;}
		
		// Percentage Calculations
				
			// Clean Hit Percent - [Clean / Scoring]	
			$fighters[$systemRosterID]['cleanHitPercentage'] = round(
				$data['cleanHitsFor'] / $data['totalScoring'],2)*100;
				
			// Hits For Percent - [Clean + Afterblow For / Scoring]
			$fighters[$systemRosterID]['hitsForPercentage'] = 100* round(
				$data['totalHitsFor'] / $data['totalScoring'],2);
				
			// Hit Ratio Percent - [Clean + Afterblow For / Total Clean + Afterblow]
			$fighters[$systemRosterID]['hitsForRatio'] = 100* round(
				$data['totalHitsFor'] / ($data['totalHitsFor'] + $data['totalHitsAgainst']),2);
				
			// Afterblow Percent - [Afterblow Landed / Clean Against + Afterblow Landed]
			$fighters[$systemRosterID]['afterblowPercentage'] = 100* round(
				$data['afterblowsLanded'] / ($data['cleanHitsAgainst'] + $data['afterblowsLanded']),2);
			
			// Failed Withdraw percent - [Afterblows Hit With / Clean For + Afterblows Hit By]
			$fighters[$systemRosterID]['failedWithdrawPercentage'] = 100 * round(
				$data['afterblowsHitWith'] /($data['cleanHitsFor'] + $data['afterblowsHitWith']),2);
			
			// Double Hit Percent - [Doubles / Scoring]
			$fighters[$systemRosterID]['doubleHitPercentage'] = 100*round(	
				$data['double']/$data['totalScoring'],2);
				
			// No Exchange Percen - [No Exchange / Total]
			$fighters[$systemRosterID]['noExchangePercentage'] = 100*round(
				$data['noExchange'] / $data['totalExchanges'],2);
		
		// Targeting Calculations	
			
			// Percentage of Points Awarded at each value
			$pointsAwarded = 0;
			foreach($data['pointsFor'] as $pointValue => $count){
				$fighters[$systemRosterID]["{$pointValue}ptsFor"] = 
					100*round($count / $data['totalHitsFor'],2);
				$pointsAwarded += $pointValue * $count;
			}
			
			// Average points awarded
			$fighters[$systemRosterID]['averagePointsAwarded'] = round(
				$pointsAwarded / $data['totalHitsFor'],2);
			
			
			
			// Percentage of Points Against at each value
			$pointsAgainst = 0;
			foreach($data['pointsAgainst'] as $pointValue => $count){
				$fighters[$systemRosterID]["{$pointValue}ptsAgainst"] = 
					100*round($count / $data['totalHitsAgainst'],2);
				$pointsAgainst += $pointValue * $count;
			}
			
			// Average points against
			$fighters[$systemRosterID]['averagePointsAgainst'] = round(
				$pointsAgainst / $data['totalHitsAgainst'],2);
		
			// Win Percentage
			$fighters[$systemRosterID]['winPercentage'] = 100*round(
				$data['wins']/($data['wins'] + $data['losses']),2);
		
		// Metrics	
		
			// DpE - [Bilateral / Total]	
			$fighters[$systemRosterID]['DpE'] = 100* round(
				$data['bilateral']/	($data['totalScoring']),2);		
				
			// Bilateral Ratio - [Bilateral Hits / Clean Hits]	
			$fighters[$systemRosterID]['bilateralPercentage'] = 100*round(
				$data['bilateral']/$data['cleanHitsFor'],2);
	
		}
	}

	
	return $fighters;
}

/******************************************************************************/

function getFilterFields(){
	
	$filterItems['winPercentage'] = "Win %";
	$filterItems['hitsForRatio'] = "% Hits for Ratio";
	$filterItems['cleanHitPercentage'] = "% Clean Hits For";
	$filterItems['hitsForPercentage'] = "% Scoring For";
	
	$filterItems[] = "----------------";
	
	$filterItems['doubleHitPercentage'] = "% Double Hits";
	$filterItems['afterblowPercentage'] = "% Afterblows Landed";
	$filterItems['failedWithdrawPercentage'] = "% Afterblows Hit With";
	$filterItems['noExchangePercentage'] ="% No Exchange";
	$filterItems['DpE'] = "% Bilateral Hits";
	$filterItems['bilateralPercentage'] = "% Bilateral vs Clean";
	
	$filterItems[] = "----------------";
	
	$filterItems['averagePointsAwarded'] = "Average Points Awarded";
	$filterItems['averagePointsAgainst'] = "Average Points Against";
	for($i = 1;$i<=5;$i++){
		$filterItems["{$i}ptsFor"] = "{$i}pt For %";
		$filterItems["{$i}ptsAgainst"] = "{$i}pt Against %";
	}
	
	
	$filterItems[] = "----------------";
	
	
	$filterItems['clean'] = "Total Clean Exchanges";
	$filterItems['double'] = "Total Double Hits";
	$filterItems['bilateral'] = "Total Bilateral Hits";
	$filterItems['afterblow'] = "Total Afterblow Exchanges";
	$filterItems['noExchange'] = "No Exchanges";
	
	$filterItems['cleanHitsFor'] = "Clean Hits For";
	$filterItems['cleanHitsAgainst'] = "Clean Hits Against";
	$filterItems['afterblowsHitWith'] = "Afterblows Against";
	$filterItems['afterblowsLanded'] = "Afterblows For";
	
	$filterItems['totalExchanges'] = "Total Exchanges";
	$filterItems['totalScoring'] = "Total Scoring Exchanges";
	$filterItems['totalHitsFor'] = "Total Hits For";
	
	return $filterItems;
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
