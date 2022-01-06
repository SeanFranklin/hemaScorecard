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
} elseif(!isTeams($tournamentID)){
	displayAlert("This is not a team based tournament");
} else {

	$teamsList = getTournamentTeams($_SESSION['tournamentID']);
	$teamRosters = getTeamRosters($_SESSION['tournamentID']);
	$addableFighters = getUngroupedRoster($tournamentID, $teamRosters);
	$numBlankEntries = 3;
	if(count($addableFighters) < $numBlankEntries){
		$numBlankEntries = count($addableFighters);
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?php createNewTeamInterface($addableFighters) ?>

	<?php if(count($teamRosters) == 0): ?>
		<?=displayAlert("No Teams Created")?>
	<? else: ?>
	<h3>Teams</h3>

	<form method='POST'>
	<table>
		<tr>
			<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
				<th></th>
			<?php endif ?>
			<th>Team Name</th>
			<th>Team Members</th>
		</tr>
		<?php foreach($teamRosters as $teamID => $team): ?>
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
					<?php foreach($team['members'] as $member): ?>
						<BR>
						<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
							<input type='checkbox' name='deleteTeamsInfo[membersToDelete][<?=$member['tableID']?>]'>
						<?php endif ?>
						<?=getFighterName($member['rosterID'])?>
					<?php endforeach ?>

					<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
						<?php for ($k = 1 ; $k <= $numBlankEntries; $k++): ?>
							<div class='input-group'>
							<span class='input-group-label'>
								New Member #:<?=$k?>
							</span>
							<select class='input-group-field' name='updateTeams[<?=$teamID?>][newMembers][<?=$k?>]''>
								<option></option>
								<?php foreach($addableFighters as $rosterID): ?>
									<option value='<?=$rosterID?>'><?=getFighterName($rosterID)?></option>
								<?php endforeach ?>
							</select>
							</div>
						<?php endfor ?>
					<?php endif ?>


				</td>
			</tr>
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

function teamName($teamID){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		echo getTeamName($teamID);
		return;
	} else {

		$DbName = htmlspecialchars(getTeamName($teamID, null, 'raw'),ENT_QUOTES);
		$placeholder = htmlspecialchars(getTeamName($teamID, null, 'members'),ENT_QUOTES);
		$placeholder = "placeholder='".$placeholder."'";

		echo "<input class='input-group-field' 
			name='updateTeams[{$teamID}][teamName]' 
			type='text' value='{$DbName}' {$placeholder}>";
			
	}
}

/******************************************************************************/

function createNewTeamInterface($addableFighters){
	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

?>
<!-- Visibility Button -->
	<button class='button' id='createTeamButton' onclick="$(this).hide();$(createTeamForm).show();">
		Create New Team
	</button>

<!-- Creation Form -->
	<fieldset class='fieldset hidden' id='createTeamForm'>
		<legend><h4>Create Team</h4></legend>

		<form method="POST">


		<!-- Team Name -->
			<div class='input-group'>
				<span class='input-group-label'>
					Team Name
				</span>
				<input class='input-group-field' name='newTeamInfo[teamName]'' type='text'>
			</div>
		<!-- Team Members -->

		
			<?php $numBlankEntries = 5;
				for ($k = 1 ; $k <= $numBlankEntries; $k++): ?>
					<div class='input-group'>
					<span class='input-group-label'>
						Member #:<?=$k?>
					</span>
					<select class='input-group-field' name='newTeamInfo[newMembers][<?=$k?>]''>
						<option></option>
						<?php foreach($addableFighters as $rosterID): ?>
							<option value='<?=$rosterID?>'><?=getFighterName($rosterID)?></option>
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