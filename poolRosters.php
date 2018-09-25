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

$tournamentID = $_SESSION['tournamentID'];
$pools = getPools($tournamentID, $_SESSION['groupSet']);

if($tournamentID == null){
	pageError('tournament');	
} elseif($pools == null){
	poolSetNavigation();
	if(!isPools($tournamentID)){
		if(isRounds($tournamentID) && USER_TYPE < USER_SUPER_ADMIN){
			// redirects to the rounds if they happen to go to the pools
			// page while in a rounds tournament
			redirect('roundRosters.php');
		}
		displayAlert("There are no pools for this tournament");
	} else {
		displayAlert("No Pools Created");
		poolManagement(); 
	}
} elseif ((getEventStatus() == 'upcoming' || getEventStatus() == 'hidden') && USER_TYPE < USER_STAFF){
	displayAlert("Event is still upcoming<BR>Pools not yet released");
} else { // Main Program ///////////
	
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
	foreach($poolRosters as $poolEntry){
		foreach($poolEntry as $assignedFighter){
			$assignedFighters[] = $assignedFighter['rosterID'];
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
			<?php autoPopluateButton(); ?>
		</div>
	</div>
	
<!-- Pool Displays -->
	<form method='POST' name='poolRosterForm' id='poolRosterForm'>
	<fieldset <?=LOCK_TOURNAMENT?>>
	<div class='grid-x grid-padding-x' id='list-of-pools'>
	<?php foreach($pools as $pool): ?>
		<?php 			
			poolEntryField($pool, $poolRosters[$pool['groupID']], $freeFighters, $isTeams); 
		?>
	<?php endforeach ?>
	</div>

<!-- Submit Buttons -->
	<?php if(USER_TYPE >= USER_ADMIN): ?>
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
	<?php endif ?>
	
	</fieldset>
	</form>
	</div>

<!-- Pool Management -->
	<?php poolManagement(count($pools)); ?>
	
<?php }

if(isset($_SESSION['poolSeeds'])){
	unset($_SESSION['poolSeeds']);
}

include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function poolManagement($numPools = 0){
// Controls for event organizers to manage the pools themselves
// Numbers, order, name, and sets
	
	if(USER_TYPE < USER_ADMIN){  return; }
	$tournamentID = $_SESSION['tournamentID'];
	
	$noReOrder = '';
	if($numPools <= 1){
		$noReOrder = "disabled";
	} 
	
	?>
	
	<fieldset class='fieldset'>
	<legend><h4>Manage Pools</h4></legend>
	
	<div class='grid-x grid-margin-x'>
	
	
	<div class='large-12 cell'>
	<div class='grid-x grid-margin-x'>
	
	<!-- Create new pools -->
		<div class='large-3 medium-4 text-center cell'>
			<button class='button expanded' data-open='addPoolsBox' <?=LOCK_TOURNAMENT?>>
				Add New Pools
			</button>
		</div>
		<?php addPoolsBox($tournamentID); ?>
	
	<!-- Re-order pools -->
		<div class='large-3 medium-4 cell'>
			<button class='button expanded dont-disable' <?=$noReOrder?> 
				onclick="reOrderPools(this)" <?=LOCK_TOURNAMENT?>>
				Re-Order Pools
			</button>
		</div>
		<div class='large-3 medium-4 cell hide-toggle ' style='display:none'>
			<button class='button expanded secondary dont-disable'  onclick="safeReload()">
				Cancel
			</button>
		</div>
	
	<!-- Rename pools -->
		<div class='large-3 medium-4 cell'>
			<button class='button expanded' data-open='renamePools'>
				Re-Name Pools
			</button>
			<?php renamePoolsBox(); ?>
		</div>
		
	<!-- Pool set management -->
		<?php if(isPoolSets($tournamentID)): ?>
			<div class='large-3 medium-4 text-center cell'>
				<button class='button expanded' data-open='poolSetBox' <?=LOCK_TOURNAMENT?>>
					Manage Pool Sets
				</button>
			</div>
			<?php poolSetBox($tournamentID); ?>
		<?php endif ?>
	
	</div>
	</div>
	</fieldset>
	
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
	$normalizeSize = getNormalization($tournamentID);
	if($normalizeSize < 2){
		$normalizeSize = 'Auto';
	}
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
	<?php foreach($attributes as $setNumber => $setData): ?>
		<?php if($setData['cumulative'] !== '0'){
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
				 <option value=0>Auto</option>
				 <?php for($i=2;$i<=10;$i++): 
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

function autoPopluateButton($tournamentID = null){
// Generate pool seedings based on the previous pool sets and the
// scoring algorithm of the tournament
	
	if($tournamentID == ''){$tournamentID = $_SESSION['tournamentID'];}

	if(USER_TYPE < USER_STAFF){ 				return;}
	if($_SESSION['groupSet'] <= 1){				return;}
	if(!isPoolSets($tournamentID)){	return;}
	?>

	<form method='POST'>
	<div class='grid-x grid-margin-x grid-padding-x'>
		<input type='hidden' name='advancementsForSetNum' value='<?=$_SESSION['groupSet']?>'>


		<div class='shrink align-self-middle opacity-toggle'>
			Pools per tier:
			<?php tooltip("Specifying a number allows you to create &#39;groupings&#39; of fighters.<BR>
				Leave blank to distribute fighters between all the pools.
				<BR><BR>
				eg: 2 Pools per tier will fill the first 2 pools up with the highest ranked, and so on."); ?>
		</div>
		&nbsp;
		<div class='shrink'>
			<input type='text' size='1' name='poolsInTier' value='' placeholder='all'> 
		</div>
		<div class='shrink align-self-middle'>
			 <button class='button secondary hollow' name='formName' value='generateNextPoolSet'>
				Populate Advancements
			</button>
		</div>
		&nbsp;
		<div>
		Avoid Re-Fights?
		<?php tooltip("The software will attempt to create pools with the least number of fighters facing fighters
				they have fought before, while also seeding based on rank."); ?>
		<div class='switch text-center no-bottom'>
			<input class='switch-input' type='checkbox' id='avoidRefights' 
				name='avoidRefights' value='true'>
			<label class='switch-paddle' for='avoidRefights'>
			</label>
		</div>

		</div>

	</div>
	
	</form>
	
<?php }


/******************************************************************************/

function poolEntryField($poolInfo, $poolRoster, $tournamentRoster, $isTeams){
// Displays the current pool roster and fields to add fighters

	$groupID = $poolInfo['groupID'];
	$poolName = $poolInfo['groupName'];
	$poolNum = $poolInfo['groupNumber'];

	$schoolList = getSchoolList();
	$maxPoolSize = maxPoolSize();
	$numPools = getNumPools($_SESSION['groupSet']);
	$index = $poolNum - 1;
	?>
	
	<div class='large-12 small-12 callout' id='divFor<?=$groupID?>'>
	<div class='grid-x '>
	
<!-- Pool Name -->
	<div class='large-1 small-12 medium-12 cell hide-toggle' >
		
	<?php if(USER_TYPE >= USER_ADMIN):
		// checkbox to delete pool ?>
		<input type='checkbox' name='deleteGroup[<?=$groupID?>]' id='<?=$groupID?>' onchange="checkIfFought(this)">
	<?php endif ?>	
	
	<strong><?=$poolName?></strong>
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
			<?php if(USER_TYPE >= USER_ADMIN):?>
			<div class='large-2 medium-3 small-6'>	
				<select name='groupAdditions[<?=$groupID?>][<?=$i?>]' class='opacity-toggle'>
					<option value=''></option>
					<?php foreach((array)$tournamentRoster as $entry):
						if(isset($_SESSION['poolSeeds'][$poolNum][$i])){
							$seedID = $_SESSION['poolSeeds'][$poolNum][$i];
						} else {
							$seedID = false;
						}
						$selected = isSelected($seedID,$entry['rosterID']);
						if($isTeams == false){
							$name = getFighterName($entry['rosterID'])." ";
							$name .= "(".getSchoolName($entry['schoolID'],'abrev').")";
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
					$name .= "<em>(".getSchoolName($poolRoster[$i]['schoolID'],'abrev').")</em>";
				} else {
					$name = getTeamName($rosterID);
				}

			?>
			<div class='medium-shrink small-6 cell opacity-toggle' id='divFor<?=$rosterID?>'>
			
			<?php if(USER_TYPE >= USER_ADMIN): ?>
				<input type='checkbox' id=<?=$rosterID?>
					onchange="checkIfFought(this)" 
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
