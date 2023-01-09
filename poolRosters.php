<?php
/*******************************************************************************
	Pool Rosters
	
	View and update the pool rosters
	Create pools, pool sets, change pool order, rename pools
	Login:
		- ADMIN or above can create, delete and add fighters to pools
		- ADMIN or above can change pool orders and names

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Pool Rosters';
$includeTournamentName = true;
$lockedTournamentWarning = true;
$jsIncludes[] = 'group_management_scripts.js';
include('includes/header.php');

$tournamentID = (int)$_SESSION['tournamentID'];
if($tournamentID != 0){
	$pools = getPools($tournamentID, $_SESSION['groupSet']);
} else {
	$pools = 0;
}

if($tournamentID == 0){
	pageError('tournament');	
} elseif($pools == null){
	poolSetNavigation();
	if($_SESSION['formatID'] != FORMAT_MATCH){
		if($_SESSION['formatID'] == FORMAT_SOLO && ALLOW['VIEW_SETTINGS'] == false){
			// redirects to the rounds if they happen to go to the pools
			// page while in a rounds tournament
			redirect('roundRosters.php');
		}
		displayAlert("There are no pools for this tournament");
	} else {
		displayAlert("No Pools Created");
		poolManagement(); 
	}
} elseif (ALLOW['VIEW_MATCHES'] == false){
	displayAlert("Event is still upcoming<BR>Pools not yet released");
} else { // Main Program ///////////

	$numPools = count($pools);
	$ringsInfo = (array)logistics_getEventLocations($_SESSION['eventID'],'ring');
	
//fetch information from tables
	$poolRosters = (array)getPoolRosters($tournamentID, $_SESSION['groupSet']);
	if(isEntriesByTeam($tournamentID) == false){
		$isTeams = false;
		$tournamentRoster = getTournamentFighters($tournamentID);
	} else {
		$isTeams = true;
		$tournamentRoster = getTournamentTeams($tournamentID);
	}

	
	$assignedFighters = array();

	$ignores = getIgnores($tournamentID);
	foreach($ignores as $rosterID => $status){
		if($status['stopAtSet'] > 0){
			foreach($tournamentRoster as $index => $fighter){
				if($fighter['rosterID'] == $rosterID){
					unset($tournamentRoster[$index]);
					break;
				}
			}
		}
	}
	
//gets list of fighters already in a pool
	$arePoolsEmpty = true;
	foreach($poolRosters as $poolEntry){
		foreach($poolEntry as $assignedFighter){
			$assignedFighters[] = $assignedFighter['rosterID'];
			$arePoolsEmpty = false;
		}
	}



//gets a list of fighters not already in a pool
	$freeFighters = [];
	foreach($tournamentRoster as $fighter){
		$rosterID = $fighter['rosterID'];
		if (!in_array($rosterID, $assignedFighters)){
			$freeFighters[] = $fighter;
		}
	}

	$hide = getItemsHiddenByFilters($tournamentID, $_SESSION['filters']);


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>

<!-- Pool Set Navigation -->
	<div id='poolRosterDiv'>
	<div class='grid-x grid-padding-x'>
		<div class='medium-shrink small-12 cell'>
			<?php poolSetNavigation(); ?>
		</div>
		<div class='auto cell'>
			<?php autoPopluateButton($numPools, $arePoolsEmpty); ?>
		</div>
	</div>

	<?=activeFilterWarning()?>
	
<!-- Pool Displays -->
	<form method='POST' name='poolRosterForm' id='poolRosterForm'>
	<fieldset <?=LOCK_TOURNAMENT?>>
		<div class='grid-x grid-padding-x' id='list-of-pools'>
		<?php foreach($pools as $pool){
			if(isset($hide['group'][$pool['groupID']]) == true){
				continue;
			}
			$pool['hide'] = $hide;
			poolEntryField($pool, $poolRosters[$pool['groupID']], $freeFighters, $isTeams, $ringsInfo); 
			
		} ?>
		</div>

	<!-- Submit Buttons -->
		<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
			<?php confirmDeleteReveal('poolRosterForm', 'deleteFromPools'); ?>
			<button class='button success' name='formName' value='addFightersToPool' <?=LOCK_TOURNAMENT?>>
				Add Fighters
			</button> 
			<span id='deleteButtonContainer'>
				<button class='button alert hollow' name='formName' value='deleteFromPools' 
					id='deleteButton' <?=LOCK_TOURNAMENT?>>
					Delete Selected
				</button>
			</span>
			<a class='button warning hollow' <?=LOCK_TOURNAMENT?> data-open="clear-pools-box">
				Clear Pools
			</a>
		<?php endif ?>

		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
			<?php if($ringsInfo != null): ?>
				<button class='button hollow' name='formName' value='assignGroupsToRings' <?=LOCK_TOURNAMENT?>>
					Assign Rings
				</button>
			<?php endif ?>
		<?php endif ?>
	
	</fieldset>
	</form>
	</div>

<!-- Pool Management -->
	<?=clearPoolsForm()?>
	<?=changeParticipantFilterForm($_SESSION['eventID'])?>
	<?php poolManagement(count($pools)); ?>
	
<?php }

if(isset($_SESSION['poolSeeds'])){
	unset($_SESSION['poolSeeds']);
}

include('includes/footer.php');

sortableScript();


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function clearPoolsForm(){

	if(ALLOW['EVENT_MANAGEMENT'] == false || LOCK_TOURNAMENT != ""){
		return;
	}

?>

	<div class='reveal tiny' id='clear-pools-box' data-reveal>

	
		<i>After selecting all use the "Delete Selected" button to delete.</i>
		<BR>

		<HR>

		<a onclick="selectAllPoolFighters()" class='button hollow expanded warning'>
			Select All Fighters
		</a>

		<HR>

		<a onclick="selectAllPools()" class='button hollow expanded warning'>
			Select All Pools
		</a>

		<HR>

		<a onclick="unselectAllPools()" class='button hollow expanded secondary'>
			Unselect All
		</a>

		<!-- Reveal close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>


	</div>

<?php
}

/******************************************************************************/

function sortableScript(){
?>

	<script>

		$(function() {
		    $("#group-rank-sortable").sortable({
		        stop: function () {

		            $("input.grs-input").each(function (index){
		                $(this).val(index+1);
		            });
		        }
		    });
		});

	</script>


<?php
}

/******************************************************************************/

function poolManagement($numPools = 0){
// Controls for event organizers to manage the pools themselves
// Numbers, order, name, and sets

	if(ALLOW['EVENT_MANAGEMENT'] == false){  return; }
	$tournamentID = $_SESSION['tournamentID'];
	
	$noReOrder = '';
	if($numPools <= 1){
		$noReOrder = "disabled";
	} 

	$rankedPools = getRankedPools($tournamentID, $_SESSION['groupSet']);
	if($rankedPools != null){
		$rankedClass = '';
	} else {
		$rankedClass = 'hollow';
	}

	if(getNumGroupSets($tournamentID) > 1){
		$groupSetClass = '';
	} else {
		$groupSetClass = 'hollow';
	}

	if($numPools == 0){
		$numPoolsClass = '';
	} else {
		$numPoolsClass = 'hollow';
	}
	
	?>
	
	<fieldset class='fieldset'>
	<legend><h4>Manage Pools</h4></legend>
	
	<div class='grid-x grid-margin-x'>
	
	
	<div class='large-12 cell'>
	<div class='grid-x grid-margin-x'>
	
	<!-- Create new pools -->
		<div class='large-3 medium-4 text-center cell'>
			<button class='button expanded <?=$numPoolsClass?>' data-open='addPoolsBox' <?=LOCK_TOURNAMENT?>>
				Add New Pools
			</button>
			<?php addPoolsBox($tournamentID); ?>
		</div>
		
	<!-- Pool set management -->
		<div class='large-3 medium-4 text-center cell'>
			<button class='button expanded <?=$groupSetClass?>' data-open='poolSetBox' <?=LOCK_TOURNAMENT?>>
				Manage Pool Sets
			</button>
			<?php poolSetBox($tournamentID); ?>
		</div>

	<?php if($numPools != 0): ?>
		
	<!-- Show More Options -->
		<div class='large-3 medium-4 text-center cell'>
			<a  onclick="toggleClass('pool-management-hidden')">
				<em class='pool-management-hidden'>( More Options )</em>
				<em class='hidden pool-management-hidden'>( Less Options )</em>
			</a>
		</div>

		<div class='large-12 cell hidden pool-management-hidden'>
		<div class='grid-x grid-margin-x'>

	<!-- Re-order pools -->
		<div class='large-3 medium-4 cell'>
			<button class='button expanded hollow dont-disable' <?=$noReOrder?> 
				onclick="reOrderPools(this)" <?=LOCK_TOURNAMENT?>>
				Re-Order Pools
			</button>
		</div>
		<div class='large-3 medium-4 cell hide-toggle ' style='display:none'>
			<button class='button expanded secondary dont-disable hollow'  onclick="safeReload()">
				Cancel
			</button>
		</div>
	
	<!-- Rename pools -->
		<div class='large-3 medium-4 cell '>
			<button class='button expanded hollow ' data-open='renamePools'>
				Re-Name Pools
			</button>
			<?php renamePoolsBox(); ?>
		</div>
		
	

	<!-- Pool set management -->
		<div class='large-3 medium-4 text-center cell'>
			<button class='button expanded <?=$rankedClass?>' <?=$noReOrder?>
				data-open='pool-standings-box' <?=LOCK_TOURNAMENT?>>
				Manage Pool Rankings
			</button>
			<?php poolStandingsSetBox($tournamentID, $rankedPools); ?>
		</div>

		</div>
		</div>

	<?php endif ?>
		
	</div>
	</div>
	</fieldset>
	
<?php }


/******************************************************************************/

function poolStandingsSetBox($tournamentID, $allPools){

	$maxOverlap = ceil(maxPoolSize($tournamentID)/2);

	if($allPools == null){
		$allPools = getPools($tournamentID, $_SESSION['groupSet']);
		$isUsed = '';
		$overlap = 0;
	} else {
		$first = array_values($allPools)[0];
		$overlap = $first['overlapSize'];
		$isUsed = 'checked';
	}

	$position = 0;

	?>
	
	<div class='reveal tiny' id='pool-standings-box' data-reveal>

	<form method='POST'>
	<fieldset <?=LOCK_TOURNAMENT?>>

		<h4>Pool Ranking Options</h4>

		<div class='grid-x grid-margin-x'>
			<div class='cell large-6 medium-12 small-6'>
				For: <strong class='red-text'><?=getSetName($_SESSION['groupSet'], $_SESSION['tournamentID'])?></strong>
			</div>
			<div class='cell large-6 medium-12 small-6 text-right'>
				<a onclick="toggle('rank-by-pool-explanation')"><em>What is this?</em></a>
			</div>
		</div>

		<input class='hidden' name='rankByPool[tournamentID]' value='<?=$tournamentID?>'>
		<input class='hidden' name='rankByPool[groupSet]' value='<?=$_SESSION['groupSet']?>'>

		
	<!-- Is used -->
		<div class='input-group'>
			<span class='input-group-label'>
				Order by Pools?
			</span>

			<div class='switch no-bottom input-group-field'>
				<input class='switch-input' type='hidden'  
					name='rankByPool[used]' value=0>
				<input class='switch-input polar-disables' type='checkbox' id='use-rank-by-pool'
					name='rankByPool[used]' value=1 <?=$isUsed?> >
				<label class='switch-paddle' for='use-rank-by-pool'>
				</label>
			</div>
		</div>


	<!-- Overlap Size -->
		<div class='input-group'>
			<span class='input-group-label'>
				Overlap Size
				<?=tooltip("Pools will be ranked in order shown. In the overlap regions score will be used between the two pools on either side.<BR><BR>
					<u><em>Example</em></u>: Pools of 4, Overlap of 1.<BR>
					- Paces 1-3 from Pool 1<BR>
					- Places 4&5 from Pools 1 & 2 sorted by score<BR>
					- Places 6-7 from Pool 2<BR>
					etc...")?>
			</span>

			<select class='input-group-field' name='rankByPool[overlapSize]'>
				<?php for($i = 0; $i<=$maxOverlap; $i++): ?>
					<option <?=optionValue($i,$overlap)?>>
						<?=$i?>
					</option>
				<?php endfor ?>

			</select>
		</div>

	<!-- Explanation -->
		<div class='callout secondary hidden' id='rank-by-pool-explanation'>

			<u>Order By Pools</u> - Allows you to define the standings order based on which pool fighters are in. If you group all of your best fighters in Pool 1, second best in Pool 2, (etc...) using this option will rank all fighters in Pool 1 above those in Pool 2, and all fighters in Pool 2 above Pool 3, etc..
			<BR><u>Overlap</u> - If you set the overlap size to 1 the last ranked fighter in Pool 1 will be compared against the first ranked fighter in Pool 2, and their position determined based on the scoring algorithm. This will alow for some people to move slightly above their pool's order in the list.

			<div class='text-right'>
				<a onclick="toggle('rank-by-pool-explanation')"><em>Hide</em></a>
			</div>
		</div>
	
	<!-- Group list -->
		<fieldset class='fieldset'>

		<legend><em>(Drag and Drop)</em></legend>

		<ol id='group-rank-sortable'>
			
			<?php foreach($allPools as $pool): 
				$position++;
				?>

				<li>
					<div class='button expanded hollow no-padding grab'>
						<strong><?=getGroupName($pool['groupID'])?></strong>
					</div>
					<input type='hidden' class='grs-input' name='rankByPool[groupIDs][<?=$pool['groupID']?>]'
						value='<?=$position?>'>
				</li>

			<?php endforeach ?>
		</ol>
		</fieldset>
			
		
		<!-- Submit buttons -->
		<div class='grid-x grid-margin-x'>
			<button class='success button small-6 cell' name='formName' 
				value='updateRankByPool'>
				Update Rank Order
			</button>
			<button class='secondary button small-6 cell' 
				data-close aria-label='Close modal' type='button'>
				Cancel
			</button>
		</div>

		<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>

	</fieldset>
	</form>
		
	</div>
	
<?php }

/******************************************************************************/

function addPoolsBox(){
	$maxPoolsToAdd = 40;	// Arbitrary
	?>
	
	<div class='reveal tiny' id='addPoolsBox' data-reveal>
	<form method='POST'>
		<h5>Create New Pools</h5>
		
		<form method='POST'>
		<fieldset <?=LOCK_TOURNAMENT?>>
			
			<div class='input-group shrink grid-x'>
				<span class='input-group-label small-6 medium-12 large-6'>New Pools:</span>
				<select class='input-group-field' name='numPoolsToAdd'>
					<?php for($i=1;$i<=$maxPoolsToAdd;$i++): ?>
						<option value='<?=$i?>'><?=$i?></option>
					<?php endfor ?>
				</select>
			</div>

	<!-- Submit buttons -->
		<div class='grid-x grid-margin-x'>
			<button class='button success small-6 cell' name='formName' value='createNewPools'>
				Add
			</button>
			<button class='button secondary small-6 cell' data-close aria-label='Close modal' type='button'>
				Cancel
			</button>
		</div>
		
		</fieldset>
		</form>
		
		<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
		
	</div>
	
<?php }

/******************************************************************************/

function poolSetBox($tournamentID){
// Creates a form to change the number of pool sets, the names of the
// sets and if the set is cumulative or not
	
	$currentNum = getNumGroupSets($tournamentID);
	$attributes = getSetAttributes($tournamentID);
	$tNorm = getNormalization($tournamentID);
	if($tNorm < 2){
		$tNorm = 'Auto';
	}
	$tournamentNormalization = "Default ({$tNorm})";

	?>
	
	<div class='reveal large' id='poolSetBox' data-reveal>

	<h5>Manage Pool Sets</h5>
	<form method='POST'>
	<fieldset <?=LOCK_TOURNAMENT?>>
						
<!-- Number of Pool Sets -->
	<div class='grid-x'>
	<div class='cell input-group shrink'>
		<span class='input-group-label'>Number of Pool Sets:</span>
			
		<select name='numPoolSets' class='input-group-field'>
			<?php for($i = 1; $i <= 10; $i++):
				$selected = isSelected ($i == $currentNum);
				?>
				
				<option value='<?=$i?>' <?=$selected?> >
					<?=$i?>
				</option>
			<?php endfor ?>
		</select>
		
	</div>
	</div>	
		
	
<!-- Set name and cumulative status -->
	<?php foreach($attributes as $setNumber => $setData):
		if((int)@$setData['cumulative'] != 0){
			$checked = 'checked';
		} else {
			$checked = '';
		} 
		$setName = @$setData['name'];
		?>
		
	<!-- Name -->
		<div class='grid-x grid-margin-x align-middle' style='margin-bottom: 15px;'>
			<div class='small-2 cell text-center'>
				<h5>Set <?=$setNumber?></h5>
			</div>
			<div class='small-10 cell'>
		<div class='grid-x'>
		<div class='input-group no-bottom'>
			<span class='input-group-label'>Name:</span>
			<input type='text' class='input-group-field' value='<?=$setName?>'
				 name='renameSet[<?=$setNumber?>]' placeholder='Pool Set <?=$setNumber?>'>
		</div>	 
		
	<!-- Is cumulative -->
		<div class='input-group medium-6 small-12 no-bottom'> 
			<span class='input-group-label'>
				Is Cumulative &nbsp;
				<?php tooltip("A cumulative pool will combine the stats of all prior cumulative pools, and the first non-cumulative pool preceding a block of cumulative pools")?>:
			</span>
			<div class='switch input-group-button large no-bottom'>
				<input type='hidden' name='cumulativeSet[<?=$setNumber?>]' value='0'>
				<input class='switch-input' type='checkbox' id='cumulativeSet-<?=$setNumber?>' 
					name='cumulativeSet[<?=$setNumber?>]' value='1' <?=$checked?> >
				<label class='switch-paddle' for='cumulativeSet-<?=$setNumber?>'>
				</label>
			</div>
		</div>
		
	<!-- Normalization size -->
		<div class='input-group medium-6 small-12 no-bottom'> 
			<span class='input-group-label'>
				Normalization Size &nbsp;
				<?php tooltip("Regardless of the option selected, cumulative pools will all have the same normalization as the first pool that they combine results from.")?>:
			</span>
			<select type='number' class='input-group-field' 
				 name='normalizeSet[<?=$setNumber?>]'>
				 <option value=0> <?= $tournamentNormalization?></option>
				 <?php for($i=2;$i<=20;$i++): 
					$selected = isSelected($i, @$setData['normalization']);
					?>
				 
					<option value='<?=$i?>' <?=$selected?>><?=$i?></option>
				 <?php endfor ?>
				</select>
			
		</div>
		</div>
		
		</div>
		</div>
			 
		
	<?php endforeach ?>		
			
<!-- Submit buttons -->	
	<div class='grid-x grid-margin-x'>
		<button class='button success small-6 cell' name='formName' value='updatePoolSets' <?=LOCK_TOURNAMENT?>>
			Update Pool Sets
		</button>
		<a class='button secondary small-6 cell' data-close aria-label='Close modal' type='button'>
			Cancel
		</a>
	</div>
		
	</fieldset>			
	</form>
	
<!-- Reveal close button -->
	<button class='close-button' data-close aria-label='Close modal' type='button'>
		<span aria-hidden='true'>&times;</span>
	</button>
	
	</div>
	
<?php }

/******************************************************************************/

function renamePoolsBox(){
// Form to changethe name of pools in the current pool set
	
	$pools = getPools($_SESSION['tournamentID'], $_SESSION['groupSet']);
	?>
	
	<div class='reveal tiny' id='renamePools' data-reveal>
	<form method='POST'>
	<h5>Rename Pools:</h5>
	
<!-- Pool names -->

	<?php 
	$i = 0;
	foreach($pools as $pool):
		$i++; ?>
		<div class='input-group'>
		<span class='input-group-label'><?=$i?>:</span>
		<input class='input-group-field' 
			type='text' 
			name='renameGroup[<?=$pool['groupID']?>]' 
			value='<?=$pool['groupName']?>' 
			placeholder='Pool <?=$pool['groupNumber']?>'>
		</div>
	<?php endforeach ?>
	
<!-- Submit buttons -->
	<div class='grid-x grid-margin-x'>
		<button class='success button small-6 cell' name='formName' value='renameGroups'>
			Update
		</button>
		<button class='secondary button small-6 cell' data-close aria-label='Close modal' type='button'>
			Cancel
		</button>
	</div>
	</form>
	
<!-- Reveal close button -->
	<button class='close-button' data-close aria-label='Close modal' type='button'>
		<span aria-hidden='true'>&times;</span>
	</button>
	
	</div>
	
<?php }


/******************************************************************************/

function autoPopluateButton($numPools, $enabled){
// Generate pool seedings based on the previous pool sets and the
// scoring algorithm of the tournament
	
	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

	$tournamentID = (int)$_SESSION['tournamentID'];
	$incompleteMatches = getTournamentPoolIncompletes($tournamentID, $_SESSION['groupSet']-1);
	?>

	<?php if($enabled == true): ?>
		<button class='button hollow' data-open='autoPopulateBox' <?=LOCK_TOURNAMENT?>>
			Generate Pools
		</button>
	<?php else: ?>
		<a class='button hollow disabled' data-tooltip aria-haspopup='true' class='has-tip' 
				data-disable-hover='false' tabindex='2' data-position='bottom' data-allow-html='true' 
				title="Pools generation is only configured to work with for empty pools." >
				
			Generate Pools
		</a>
		<?php return ?>
	<?php endif ?>


	<div class='reveal tiny' id='autoPopulateBox' data-reveal>
	<h5>Auto-Generate Pools</h5>
	<em>This will try it's best to meet all the conditions you specify. No gaurantees!</em>
	<form method='POST'>

		<input type='hidden' name='generatePools[groupSet]' value='<?=$_SESSION['groupSet']?>'>
		<input type='hidden' name='generatePools[tournamentID]' value='<?=$tournamentID?>'>

		<?php if($incompleteMatches != null): ?>
			<div id='incompleteMatchesDiv' class='callout'>
				<?php displayIncompleteMatches($incompleteMatches);?>
			</div>
		<?php endif ?>


	<!-- Seed Method -->
		<div class='input-group'>
			<span class='input-group-label'>
				<strong>Seeding Source
					<?=tooltip("'Seed List' data is manualy entered in <strong>
						Manage Fighters -> Set Fighter Ratings</strong><BR><BR>
						")?>
					<!-- Suppressed Option: "Polar Seeding uses rating & rating2 to group people of similar
						ratings together. Don't use it unless you know what you are doing. -->
				:</strong>
			</span>
			<select class='input-group-field' type='text' size='1' required
				name='generatePools[seedMethod]' value='' placeholder='all'
				onchange="togglePolarGeneration(this)"> 
				<option selected disabled></option>
				<?php if($_SESSION['groupSet'] > 1): ?>
					<option value='poolStanding'>Previous Pool Ranking</option>
				<?php endif ?>
				<option value='random'>Random</option>
				<option value='seedList'>Seed List</option>
				<!--<option value='polar'>Polar Seeding</option>-->
			</select>
		</div>

		<HR>

	<!-- Separate Schools -->
		<div class='input-group'>
			<span class='input-group-label polar-disables'>
				Separate Schools? &nbsp;
				<?php tooltip("The software will attempt to create pools that 
						avoids people from the same school fighting each other, 
						while also seeding based on rank."); ?>
			</span>
			<div class='switch no-bottom input-group-field'>
				<input class='switch-input' type='hidden'  
					name='generatePools[avoidSchoolFights]' value=0>
				<input class='switch-input polar-disables' type='checkbox' id='avoidSchoolFights' 
					name='generatePools[avoidSchoolFights]' value=1>
				<label class='switch-paddle' for='avoidSchoolFights'>
				</label>
			</div>
		</div>

	<!-- Avoid Re-fights -->
		<div class='input-group'>
			<span class='input-group-label polar-disables'>
				Avoid Re-Fights? &nbsp;
					<?php tooltip("The software will attempt to create pools with 
						the least number of fighters facing fighters
						they have fought before, while also seeding based on rank."); ?>
			</span>
			<div class='switch no-bottom input-group-field'>
				<input class='switch-input' type='hidden'  
					name='generatePools[avoidRefights]' value=0>
				<input class='switch-input polar-disables' type='checkbox' id='avoidRefights' 
					name='generatePools[avoidRefights]' value=1>
				<label class='switch-paddle' for='avoidRefights'>
				</label>
			</div>
		</div>

	<!-- Pools per tier -->
		<div class='input-group'>
			<span class='input-group-label polar-disables'>
				Pools per tier: &nbsp;
				<?php tooltip("Specifying a number allows you to create 
					&#39;groupings&#39; of fighters.<BR>
					Leave blank to distribute fighters between all the pools.
					<BR><BR>
					eg: 2 Pools per tier will fill the first 2 pools up
					 with the highest ranked, and so on."); ?>
			</span>
			<select class='input-group-field polar-disables' type='text' size='1' 
				name='generatePools[poolsInTier]' value='' placeholder='all'> 
				<option value=0>- all -</option>
				<?php for($i=1;$i<=$numPools;$i++): ?>
					<option value=<?=$i?>><?=$i?></option>
				<?php endfor ?>

			</select>
		</div>

	<!-- Use Sub Groups -->
		<div class='input-group'>
			<span class='input-group-label'>
				Use Sub Groups? &nbsp;
					<?php tooltip("Separate participants based on Sub Group Number<BR>
						<strong><u>Do Not Use</u></strong> unless you know what you are doing.</strong>"); ?>
			</span>
			<div class='switch no-bottom input-group-field'>
				<input class='switch-input' type='hidden'  
					name='generatePools[useSubGroups]' value=0>
				<input class='switch-input' type='checkbox' id='useSubGroups' 
					name='generatePools[useSubGroups]' value=1>
				<label class='switch-paddle' for='useSubGroups'>
				</label>
			</div>
		</div>

	<!-- Show Debug -->
		<div class='input-group'>
			<span class='input-group-label polar-disables'>
				Show Generation Data? &nbsp;
					<?php tooltip("This will show you the calculations to generate the pools.<BR>
					<u>Warning</u>: This will flood your screen with numbers."); ?>
			</span>
			<div class='switch no-bottom input-group-field'>
				<input class='switch-input' type='hidden'  
					name='generatePools[debug]' value=0>
				<input class='switch-input polar-disables' type='checkbox' id='debug' 
					name='generatePools[debug]' value=1>
				<label class='switch-paddle' for='debug'>
				</label>
			</div>
		</div>

		<HR>

	<!-- Submit buttons -->
		<?php if($incompleteMatches != null): ?>
			<span class='red-text'>
				<strong>There are incomplete pool matches.</strong><BR>
			</span>
				Advancements will be generated based on incomplete information.<BR>
			<em class='grey-text'>
				(If fighters are injured/disqualifiedthey should be removed using Manage Fighters -> Withdraw Fighters.)
			</em>
		<?php endif ?>
		<div class='grid-x grid-margin-x'>
			<button class='success button small-6 cell' name='formName' value='generatePools'>
				Populate
			</button>
			<button class='secondary button small-6 cell' 
				data-close aria-label='Close modal' type='button'>
				Cancel
			</button>
		</div>
	
	</form>

	<!-- Reveal close button -->
	<button class='close-button' data-close aria-label='Close modal' type='button'>
		<span aria-hidden='true'>&times;</span>
	</button>

	</div>
	
<?php }


/******************************************************************************/

function poolEntryField($poolInfo, $poolRoster, $tournamentRoster, $isTeams, $ringsInfo){
// Displays the current pool roster and fields to add fighters

	$groupID = $poolInfo['groupID'];
	$poolName = $poolInfo['groupName'];
	$poolNum = $poolInfo['groupNumber'];
	$hide = $poolInfo['hide'];

	$schoolList = getSchoolList();
	$maxPoolSize = max(count($poolRoster),maxPoolSize($_SESSION['tournamentID']));
	$numPools = getNumPools($_SESSION['groupSet'], $_SESSION['tournamentID']);
	$index = $poolNum - 1;
	?>
	
	<div class='large-12 small-12 callout' id='divFor<?=$groupID?>'>
	<div class='grid-x '>
	
<!-- Pool Name -->
	<div class='large-1 small-12 medium-12 cell hide-toggle' >
		
	<?php if(ALLOW['EVENT_MANAGEMENT'] == true):
		// checkbox to delete pool ?>
		<input type='checkbox' name='deleteGroup[<?=$groupID?>]' 
		id='<?=$groupID?>' onchange="checkIfFought(this)"
		 class='pool-group-checkbox'>
	<?php endif ?>	
	

	<strong><?=$poolName?></strong>

	<?php if($ringsInfo != null): ?>
		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
			<select name="assignToLocations[groupIDs][<?=$groupID?>]">
				<option>N/A</option>
				<?php foreach($ringsInfo as $ring): ?>
					<option <?=optionValue($ring['locationID'],$poolInfo['locationID'])?> >
						<?=logistics_getLocationName($ring['locationID'])?>
					</option>
				<?php endforeach ?>	
			</select>
		<?php else: ?>
			<BR><em><?=logistics_getLocationName($poolInfo['locationID'])?></em>
		<?php endif ?>
	<?php endif ?>

	</div>
	
	

<!-- Hidden options for re-ordering pools -->
	<div class='large-1 small-12 medium-12 hide-toggle hidden' >
	<div class='black-border'>
		<select class='pool-number-select' id='group<?=$groupID?>' 
			name='newGroupNumber[<?=$groupID?>]' onchange="poolNumberChange(this)"
			data-groupID='<?=$groupID?>' data-current-index='<?=$index?>'>
		<?php for($i = 1; $i <= $numPools; $i++):
			$selected = isSelected ($i == $poolNum);
			?>
			<option value=<?=$i?> <?=$selected?>><?=$i?></option>
		<?php endfor ?>
		</select>
	</div>
	</div>

	
<!-- Pool fighters -->	
	<div class='large-11 medium-12 cell'>
	<div class='grid-x grid-padding-x'>

	
	<?php for($i=1;$i<=$maxPoolSize;$i++): ?>

		<!-- Empty pool position -->
		<?php if(!isset($poolRoster[$i])): ?>
			<?php if(ALLOW['EVENT_MANAGEMENT'] == true):?>
			<div class='large-2 medium-3 small-6'>	
				<select name='groupAdditions[<?=$groupID?>][<?=$i?>]' class='opacity-toggle'>
					<option value=''></option>
					<?php foreach((array)$tournamentRoster as $entry):
						if(isset($_SESSION['poolSeeds'][$poolNum-1][$i-1])){
							$seedID = $_SESSION['poolSeeds'][$poolNum-1][$i-1];
						} else {
							$seedID = false;
						}
						$selected = isSelected($seedID,$entry['rosterID']);
						if($isTeams == false){
							$name = getFighterName($entry['rosterID'])." ";
							$name .= "(".getSchoolName($entry['schoolID'],'abbreviation').")";
						} else {
							$name = getTeamName($entry['rosterID']);
						}
						?>

						<option value='<?=$entry['rosterID']?>' <?=$selected?>>
							<?=$name?>
						</option>
					<?php endforeach ?>
				</select>
			</div>	
			<?php endif ?>
		
		<!-- Fighter Already entered in position -->	
		<?php else:?>
			<?php $rosterID = $poolRoster[$i]['rosterID']; 
				if($isTeams == false){
					$name = getFighterName($rosterID)." ";
					$name .= "<em>(".getSchoolName($poolRoster[$i]['schoolID'],'abbreviation').")</em>";
				} else {
					$name = getTeamName($rosterID);
				}

				if(isset($hide['school'][$poolRoster[$i]['schoolID']]) == true){
					continue;
				}

			?>

			<div class='medium-shrink small-6 cell opacity-toggle <?=$class?>' id='divFor<?=$rosterID?>'>
			
			<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
				<input type='checkbox' id=<?=$rosterID?>
					onchange="checkIfFought(this)" class='pool-fighter-checkbox'
					name='deleteFromGroup[<?=$groupID?>][<?=$rosterID?>]'>
			<?php endif ?>
			<?=$name?>
			</div>
		<?php endif ?>
		
	<?php endfor ?>
	</div>
	</div>
	</div>
	</div>
	
<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
