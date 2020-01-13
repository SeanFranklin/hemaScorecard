<?php
/*******************************************************************************
	Tournament Components
	
	View and change the tournament components.
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Tournament Components';
$includeTournamentName = true;
$lockedTournamentWarning = true;
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];
if($tournamentID == null){
	pageError('tournament');
} elseif($_SESSION['formatID'] != FORMAT_META){
	redirect('participantsTournament.php');
} else {

	// Get components of the current tournament
	$tournamentComponents = getMetaTournamentComponents($tournamentID);

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		foreach($tournamentComponents as $index => $component){

			$tournamentComponents[$index]['name'] = getTournamentName($component['cTournamentID']);

			if($component['eventID'] != $_SESSION['eventID']){
				$eName = "<strong>[".getEventName($component['eventID'])."]</strong> ";
				$tournamentComponents[$index]['name'] = $eName.$tournamentComponents[$index]['name'];
			}

		}

		$componentGroups = getComponentGroups($_SESSION['tournamentID']);

	} else {


	// Get all tournaments which could be components
		if(    (isset($_SESSION['metaTournamentComponentSource']) == false)
			|| ($_SESSION['metaTournamentComponentSource'] == null)
			|| (isMetaEvent($_SESSION['eventID']) == false) ){

			$_SESSION['metaTournamentComponentSource'] = $_SESSION['eventID'];
		}

		$sourceTournaments = getEventComponentTournaments(
										$_SESSION['metaTournamentComponentSource'], 
										$_SESSION['tournamentID']);

	// Get tournaments which are already components
		foreach($tournamentComponents as $index => $info){

			if($_SESSION['metaTournamentComponentSource'] != $_SESSION['eventID']){

				$sourceTournaments[$info['cTournamentID']] = $info['tournamentComponentID'];
			}
		}

		

	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Page Structure -->
	<?php if(ALLOW['EVENT_MANAGEMENT'] == false): ?>
		
		The standings of 
		<strong><?=getTournamentName($_SESSION['tournamentID'])?></strong> 
		are based on the final placings of:

		<ul>
			<?php foreach($componentGroups as $group):?>
				<li>
					<?php if($group['usedComponents'] == 1){
						echo "Best placing in:";
					} else {
						echo "Best {$group['usedComponents']} placings in:";
					}
					?>
					
					<ul>
					<?php foreach($group['items'] as $item): ?>
						<li><?=getEventAndTournamentName($item)?></li>
						<?php $alreadyDisplayed[$item] = true; ?>
					<?php endforeach ?>
					</ul>
				</li>
			<?php endforeach ?>

			<?php foreach($tournamentComponents as $component):
				if(isset($alreadyDisplayed[$component['cTournamentID']]) == true){
					continue;
				}
				?>
				<li><?=$component['name']?></li>
			<?php endforeach ?>
		</ul>



	<?php else: ?>

		<?=manageTournamentComponents($sourceTournaments)?>

		<?=manageComponentSettings($tournamentComponents)?>

		<?=manageTournamentComponentsGroups($tournamentComponents)?>

	<?php endif ?>
		
<?php 		
	
}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////


/******************************************************************************/

function manageTournamentComponents($sourceTournaments){
	$sourceEventID = $_SESSION['metaTournamentComponentSource'];
?>

	<fieldset <?=LOCK_TOURNAMENT?> class='fieldset'>
	<legend>
		<h4 class='no-bottom'>Tournament Components</h4>
	</legend>

<!-- If it is a meta-event then the user is able to choose which event to select tournament components from -->
	<?php if(isMetaEvent($_SESSION['eventID']) == true): ?>
		<form method='POST'>
		
			<input type='hidden' name='formName' value='changeSourceMetaEvent'>

			<div class='input-group no-bottom'>

				<span class='input-group-label no-bottom'>
					Event:
				</span>
			
				<select class='input-group-field no-bottom' name='changeEventSource[eventID]'
					onchange="this.form.submit()">
					<?php foreach(getEventListSmall() as $eventID => $name): ?>
						<option value='<?=$eventID?>' <?=isSelected($eventID, $sourceEventID)?> >
							<?=$name?>
						</option>
					<?php endforeach ?>
				</select>

			</div>

		</form>
		<BR>
	<?php endif ?>

<!-- Configuration for tournament components -->

	<form method='POST' id='tournamentComponentsForm'>

	<input type='hidden' name='sourceTournaments[metaTournamentID]' 
		value='<?=$_SESSION['tournamentID']?>' >

		<?php foreach($sourceTournaments as $tournamentID => $componentID):?>

			
			<input type='hidden' value='0'
				name='sourceTournaments[componentTournamentIDs][<?=$tournamentID?>]'>
			<input type='checkbox' class='no-bottom' value='1' <?=chk($componentID)?>
				name='sourceTournaments[componentTournamentIDs][<?=$tournamentID?>]'>
				
			<?=getEventAndTournamentName($tournamentID)?>
			<BR>

		<?php endforeach ?>

		<BR>
		<button class='button success no-bottom' name='formName' value='updateMetaTournamentComponents'>
			Update Tournament Components
		</button>


	</form>
	</fieldset>

<?php
}

/******************************************************************************/

function manageComponentSettings($tournamentComponents){
	$rosterMode = readOption('T',$_SESSION['tournamentID'],'META_ROSTER_MODE');

?>

	<fieldset <?=LOCK_TOURNAMENT?> class='fieldset'>
	<legend>
		<h4 class='no-bottom'>Component Settings</h4>
	</legend>

	<form method='POST' id='tournamentComponentsForm'>
	
	<input type='hidden' name='metaTournament[mTournamentID]' 
		value=<?= $_SESSION['tournamentID']?>>

		<table>
		<tr>
			<th>
			</th>
			<th>
				Inclusive
				<?php tooltip("
					Roster is made up of fighters who are entered in <b>all</b> of the 
					tournaments with <i>Use Roster</i> selected.
					")?>
			</th>
			<th>
				Exclusive
				<?php tooltip("
					Roster is made up of fighters who are entered in <b>all</b> of the 
					tournaments with <i>Use Roster</i> selected, and in <b>no other</b> 
					tournaments in this event.
					")?>
			</th>
			<th>
				Any
				<?php tooltip("
					Roster is made up of fighters who are entered in <b>any</b> of the 
					tournaments with <i>Use Roster</i> selected.
					")?>
			</th>
		</tr>
		<tr>
			<td>
				Roster Mode
			</td>
			<td>
				<div class='switch no-bottom'>
					<input type='radio' 
						class='switch-input no-bottom'
						name='metaTournament[rosterMode]'
						value='<?=META_ROSTER_MODE_INCLUSIVE?>' 
						id='roster-mode-<?=META_ROSTER_MODE_INCLUSIVE?>'
						<?=chk($rosterMode,META_ROSTER_MODE_INCLUSIVE)?>
					>
					<label class='switch-paddle' for='roster-mode-<?=META_ROSTER_MODE_INCLUSIVE?>'>
					</label>
				</div>
			</td>
			<td>
				<div class='switch no-bottom'>
					<input  type='radio' 
						class='switch-input no-bottom'
						name='metaTournament[rosterMode]' 
						value='<?=META_ROSTER_MODE_EXCLUSIVE?>' 
						id='roster-mode-<?=META_ROSTER_MODE_EXCLUSIVE?>'
						<?=chk($rosterMode,META_ROSTER_MODE_EXCLUSIVE)?>
					>
					<label class='switch-paddle' for='roster-mode-<?=META_ROSTER_MODE_EXCLUSIVE?>'>
					</label>
				</div>
			</td>
			<td>
				<div class='switch no-bottom'>
					<input type='radio'
						class='switch-input no-bottom'  
						name='metaTournament[rosterMode]'
						value='<?=META_ROSTER_MODE_EXTENDED?>' 
						id='roster-mode-<?=META_ROSTER_MODE_EXTENDED?>'
							<?=chk($rosterMode,META_ROSTER_MODE_EXTENDED)?>
					>
					<label class='switch-paddle' for='roster-mode-<?=META_ROSTER_MODE_EXTENDED?>'>
					</label>
				</div>
			</td>
		</tr>

		<tr  style='border-top:1px solid'>
			<th>Tournament</th>
			<th>
				Use Results?
				<?=tooltip("Scores from selected events will be used in the final placings for
							the meta-event.")?>
			</th>
			<th>
				Use Roster?
				<?=tooltip("Use roster of this component to generate the meta-tournament roster.
							Exact implementation depends on <em>Roster Mode</em> setting.")?>
			</th>
			<th>
				Ignore Roster?
				<?=tooltip("Ignores the roster of this tournament when calculating the exclusive roster for the meta-tournament")?>
			</th>
		</tr>

		<?php foreach($tournamentComponents as $info):
			$cTournamentID = $info['cTournamentID'];
			$fName = "metaTournament[components][{$info['tournamentComponentID']}]";
			?>

			<tr>



			<!-- Name -->
				<td>
					<?=getEventAndTournamentName($cTournamentID)?>
					<input class='hidden' name='<?=$fName?>[cTournamentID]' 
						value='<?=$cTournamentID?>'>
				</td>

			<!-- Use result -->
				<td>
					<div class='switch no-bottom input-group-field'>
						<input class='switch-input' type='hidden'  
							name='<?=$fName?>[useResult]' value=0>
						<input class='switch-input polar-disables' type='checkbox' 
							id='<?=$fName?>[useResult]' name='<?=$fName?>[useResult]' 
							value=1 <?=chk($info['useResult'])?> >
						<label class='switch-paddle' for='<?=$fName?>[useResult]'>
						</label>
					</div>
				</td>

			<!-- Use roster -->
				<td>
					<div class='switch no-bottom input-group-field'>
						<input class='switch-input' type='hidden'  
							name='<?=$fName?>[useRoster]' value=0>
						<input class='switch-input polar-disables' type='checkbox' 
							id='<?=$fName?>[useRoster]' name='<?=$fName?>[useRoster]' 
							value=1 <?=chk($info['useRoster'])?> >
						<label class='switch-paddle' for='<?=$fName?>[useRoster]'>
						</label>
					</div>
				</td>

			<!-- Ignore roster -->
				<td>
					<div class='switch no-bottom input-group-field'>
						<input class='switch-input' type='hidden'  
							name='<?=$fName?>[ignoreRoster]' value=0>
						<input class='switch-input polar-disables' type='checkbox' 
							id='<?=$fName?>[ignoreRoster]' name='<?=$fName?>[ignoreRoster]' 
							value=1 <?=chk($info['ignoreRoster'])?> >
						<label class='switch-paddle' for='<?=$fName?>[ignoreRoster]'>
						</label>
					</div>
				</td>

			</tr>
		<?php endforeach ?>
	</table>

	<button class='button success no-bottom' name='formName' value='updateMetaTournamentSettings'>
		Update Component Settings
	</button>
		
	
	</form>	
	</fieldset>

<?php
}


/******************************************************************************/

function manageTournamentComponentsGroups($tournamentComponents){
	$maxGroupSize = 20; //arbitrary upper limit
	$compGroups = getComponentGroups($_SESSION['tournamentID']);
?>

	<fieldset <?=LOCK_TOURNAMENT?> class='fieldset'>

	<legend>
		<h4 class='no-bottom'>
			Component Groups
			<?=tooltip("Example uses:<BR>
			- Take top 3 best results from league tournaments.<BR>
			- Take the top result between Women's and Open Longsword")?>
		</h4>
	</legend>

<!-- Add a new group -->
	<form method='POST' id='tournamentComponentsForm'>

		<div class='grid-x'>

		<input type='hidden' name='addComponentGroup[mTournamentID]' 
			value='<?=$_SESSION['tournamentID']?>' >

		<div class='input-group large-5 medium-8 cell'>

			<span class='input-group-label'>Best </span>

			<select class='input-group-field' name='addComponentGroup[usedComponents]'>
				<?=numberSelectMenu(1,$maxGroupSize)?>
			</select>

			<span class='input-group-label'> out of </span>

			<select class='input-group-field' name='addComponentGroup[numComponents]'>
				<?=numberSelectMenu(2,$maxGroupSize)?>
			</select>
			
			<button class='input-group-button button success hollow' name='formName'
				value='addTournamentComponentGroup'>
				Add New Group
			</button>
		</div>

		</div>
		<em><strong>Note</strong>: Groups work on the basis of <u>placing</u>. Finishing 4th in a 4 person tournament will be considered 'better' than 5th in a 100 person tournament.</em>

	</form>

<!-- Modify existing groups -->


	<form method="POST">
	<input type='hidden' name='updateTournamentComponentGroups[mTournamentID]' 
		value='<?=$_SESSION['tournamentID']?>' >
	<input type='hidden' name='deleteTournamentComponentGroups[mTournamentID]' 
		value='<?=$_SESSION['tournamentID']?>' >

	<?php foreach($compGroups as $componentGroupID => $data):?>

		<fieldset class='fieldset'>
			<legend>
				<div class='input-group no-bottom'>

				<span class='input-group-label '>Best </span>

				<select class='input-group-field' 
					name='updateTournamentComponentGroups[groups][<?=$componentGroupID?>][usedComponents]'>
					<?=numberSelectMenu(1,$maxGroupSize,$data['usedComponents'])?>
				</select>

				<span class='input-group-label'> out of </span>

				<select class='input-group-field' 
					name='updateTournamentComponentGroups[groups][<?=$componentGroupID?>][numComponents]'>
					<?=numberSelectMenu(2,$maxGroupSize,$data['numComponents'])?>
				</select>
				
				<span class='input-group-label'>
					<input type='checkbox' class='no-bottom' name='deleteTournamentComponentGroups[componentGroupIDsToDelete][<?=$componentGroupID?>]' value='<?=$componentGroupID?>'>
				</span>
				</div>
			</legend>

			<div class='grid-x grid-margin-x'>
			<?php for($i = 0; $i < $data['numComponents']; $i++): ?>

				<select class='no-bottom large-6 cell'
					name='updateTournamentComponentGroups[groups][<?=$componentGroupID?>][items][<?=$i?>]'>
					<option value=''>

					</option>
					<?php foreach($tournamentComponents as $component): ?>
						<option value='<?=$component['cTournamentID']?>'
							<?=isSelected($component['cTournamentID'], @$compGroups[$componentGroupID]['items'][$i])?> >

							<?=getEventAndTournamentName($component['cTournamentID'])?>
						</option>
					<?php endforeach ?>
				</select>

			<?php endfor ?>
			</div>

		</fieldset>

	<?php endforeach ?>


	<button class='button success' name='formName' value='updateTournamentComponentGroups'>
		Update Component Groups
	</button>

	<button class='button alert' name='formName' value='deleteTournamentComponentGroups'>
		Delete Selected
	</button>

	</form>


	</fieldset>

<?php
}

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
