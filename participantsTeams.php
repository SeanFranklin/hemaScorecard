<?php 
/*******************************************************************************
	Manage Tournament Teams
	
	Administrator page to add/edit/remove events
	LOGIN
		- SUPER ADMIN can access, no others can
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Tournament Teams";
include('includes/header.php');
$tournamentID = $_SESSION['tournamentID'];

if($tournamentID == null){
	pageError('tournament');
} elseif(ALLOW['VIEW_ROSTER'] == false) {
	displayAlert("Event is still upcoming<BR>Roster not yet released");
} elseif(!isTeams($tournamentID)){
	displayAlert("This is not a team based tournament");
} else {

	$teamsList = (array)getTournamentTeams($_SESSION['tournamentID']);
	$teamRostersRaw = (array)getTeamRosters($_SESSION['tournamentID']);
	$addableFighters = (array)getUngroupedRoster($tournamentID, $teamRostersRaw);

	$teamSize = (int)readOption('T',$tournamentID,'TEAM_SIZE');
	if($teamSize != 0){
		$numBlankEntries = min($teamSize, count($addableFighters));
	} else {
		$numBlankEntries = -1;
	}
	

	$teamRostersSorted = [];
	foreach($teamRostersRaw as $teamID => $team){
		$teamRostersSorted[$teamID] = getTeamName($teamID);
	}
	asort($teamRostersSorted);

	foreach($teamRostersSorted as $teamID => $team){
		$teamRostersSorted[$teamID] = $teamRostersRaw[$teamID];
	}

	$numTeams = (int)count(@$teamRostersSorted);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?php createNewTeamInterface($addableFighters, $numBlankEntries) ?>

	<?php if(count($teamRostersSorted) == 0): ?>
		<?=displayAlert("No Teams Created")?>
	<? else: ?>
	<h3><?=$numTeams?> Teams</h3>

	<form method='POST'>
	<table class='stack'>
		<tr>
			<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
				<th></th>
			<?php endif ?>

			<th>Team Name</th>

			<th>
				Team Members
				<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
					<?=tooltip("The number to the left of the name is their order in the team, if you're wanting to specify fighter order." )?>
				<?php endif ?>
			</th>

		</tr>
		<?php foreach($teamRostersSorted as $teamID => $team): ?>
			<?=displayTeam($team, $teamID, $addableFighters, $numBlankEntries)?>
		<?php endforeach ?>
	</table>

<!-- Buttons to update teams -->
	<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
		<button class='button no-bottom success' name='formName' value='addToTeams'>
			Update Teams
		</button>


		<button class='button no-bottom alert hollow' name='formName' value='deleteTeams'>
			Delete Selected
		</button>
	<?php endif ?>

	</form>
	<?php endif ?>

<?php
}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayTeam($team, $teamID, $addableFighters, $numBlankEntries){

	$numInTeam = 0;
	$teamMembers = [];
	foreach($team['members'] as $member){
		$numInTeam++;
		$m['rosterID'] = $member['rosterID'];
		$m['name'] = getFighterName($member['rosterID']);
		$m['school'] = getFighterSchoolName($member['rosterID'],'short');
		$m['teamOrder'] = (int)$member['teamOrder'];
		$m['tableID'] = $member['tableID'];
		$teamMembers[] = $m;
	}

	if($numBlankEntries >= 0){
		$numBlankEntries -= $numInTeam;
	} else {
		$numBlankEntries = 2;
	}

?>
	<tr>
		<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
			<td>
				<input type='checkbox' name='deleteTeamsInfo[teamsToDelete][<?=$teamID?>]'>
			</td>
		<?php endif ?>

		<td>
			<?=teamName($teamID)?>
		</td>

		<td>

			<input type='hidden' name='updateTeams[<?=$teamID?>][teamID]' value='<?=$teamID?>'>

			<?php foreach($teamMembers as $member): ?>
				<li>
					<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>

						<input type='number' value=<?=$member['teamOrder']?>
							name='updateTeams[<?=$teamID?>][order][<?=$member['tableID']?>]' 
							style="width: 4em; display:inline;" class='no-bottom'  min=0 max=10>

					<?php elseif($member['teamOrder'] != 0):?>

						<?=$member['teamOrder']?>) 
						
					<?php endif ?>


				<?=$member['name']?>  [<?=$member['school']?>]

				
				<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
					<input type='checkbox' name='deleteTeamsInfo[membersToDelete][<?=$member['tableID']?>]'>
				<?php endif ?>
				</li>
			<?php endforeach ?>

			<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
				<?php for ($k = 1 ; $k <= $numBlankEntries; $k++): ?>
					<div class='input-group'>

						<span class='input-group-label'>
							New Member #:<?=$k?>
						</span>

						<select class='input-group-field' name='updateTeams[<?=$teamID?>][newMembers][<?=$k?>]'>
							<option></option>
							<?php foreach($addableFighters as $f): ?>
								<option value='<?=$f['rosterID']?>'>
									<?=$f['name']?> [<?=$f['school']?>]
								</option>
							<?php endforeach ?>
						</select>

					</div>
				<?php endfor ?>
			<?php endif ?>

		</td>

	</tr>
<?php
}


/******************************************************************************/

function teamName($teamID){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		echo "<b>".getTeamName($teamID)."</b>";
		echo "<BR>";
	} else {

		$DbName = htmlspecialchars(getTeamName($teamID, null, 'raw'),ENT_QUOTES);
		$placeholder = htmlspecialchars(getTeamName($teamID, null, 'members'),ENT_QUOTES);
		$placeholder = "placeholder='".$placeholder."'";

		echo "<input 
			name='updateTeams[{$teamID}][teamName]' 
			type='text' value='{$DbName}' {$placeholder}>";
			
	}

	echo "<i>".getFighterSchoolName($teamID)."</i>";

	return;

}

/******************************************************************************/

function createNewTeamInterface($addableFighters, $numBlankEntries){
	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

?>
<!-- Visibility Button -->
	<button class='button' id='createTeamButton' onclick="$(this).hide();$(createTeamForm).show();">
		Create New Team
	</button>
	<button class='button hollow' id='soloFightersButton' onclick="$(this).hide();$(soloFightersDisplay).show();">
		See Un-Assigned Fighters
	</button>

<!-- Creation Form -->

	<fieldset class='fieldset hidden' id='soloFightersDisplay'>
		<legend><h4>
			Un-Assigned Fighters &nbsp;
			<a class='button secondary no-bottom hollow small' 
				id='createTeamShow' style='float:right'
				onclick="$(soloFightersButton).show();$(soloFightersDisplay).hide();">
				Close
			</a>
		</h4></legend>

		<table>
			<?php foreach($addableFighters as $f): ?>
				<tr>
					<td><?=$f['name']?></td>
					<td><?=$f['school']?></td>
				</tr>
			<?php endforeach ?>
		</table>
			
	</fieldset>


<!-- Creation Form -->
	<fieldset class='fieldset hidden' id='createTeamForm'>
		<legend><h4>
			Create Team &nbsp;
			<a class='button secondary no-bottom hollow small' 
				id='createTeamShow' style='float:right'
				onclick="$(createTeamButton).show();$(createTeamForm).hide();">
				Close
			</a>
		</h4></legend>

		<form method="POST">


		<!-- Team Name -->
			<div class='input-group'>
				<span class='input-group-label'>
					Team Name
				</span>
				<input class='input-group-field' name='newTeamInfo[teamName]' type='text' required>
			</div>
		<!-- Team Members -->

		
			<?php
				for ($k = 1 ; $k <= $numBlankEntries; $k++): ?>
					<div class='input-group'>
					<span class='input-group-label'>
						Member #:<?=$k?>
					</span>
					<select class='input-group-field' name='newTeamInfo[newMembers][<?=$k?>]'>
						<option></option>
						<?php foreach($addableFighters as $f): ?>
							<option value='<?=$f['rosterID']?>'>
								<?=$f['name']?> [<?=$f['school']?>]
							</option>
						<?php endforeach ?>
					</select>
					</div>
				<?php endfor ?>
		

		<!-- Sumbit Buttons -->
			<button class='button no-bottom success' name='formName' value='createNewTeam'>
				Create Team
			</button>

			<a class='button secondary no-bottom align-right' 
				id='createTeamShow' style='float:right'
				onclick="$(createTeamButton).show();$(createTeamForm).hide();">
				Cancel Team Creation
			</a>

		</form>
			
	</fieldset>
<?php
}


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////