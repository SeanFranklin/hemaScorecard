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
$createSortableDataTable[] = ['fightersWithoutTeam',25];
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

	$teamRostersSorted = [];
	foreach($teamsList as $team){
		$teamRostersSorted[$team['teamID']] = getTeamName($team['teamID']);
	}
	asort($teamRostersSorted);

	$blank['members'] = [];

	foreach($teamRostersSorted as $teamID => $team){

		if(isset($teamRostersRaw[$teamID]) == true){
			$teamRostersSorted[$teamID] = $teamRostersRaw[$teamID];
		} else {
			$teamRostersSorted[$teamID] = $blank;
		}

	}

	$numTeams = (int)count(@$teamRostersSorted);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?php createNewTeamInterface($addableFighters, $teamSize) ?>
	<?php showUnAssignedFighters($addableFighters)?>
	<?php teamsAutoCreateForm($addableFighters, $teamSize)?>

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
			<?=displayTeam($team, $teamID, $addableFighters, $teamSize)?>
		<?php endforeach ?>
	</table>

	<?=displayTeamRatingsTable($teamRostersSorted)?>

	<hr>

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

function displayTeam($team, $teamID, $addableFighters, $teamSize){

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


	$emptySpots = $teamSize - $numInTeam;

	if(count($addableFighters) < $emptySpots){
		$emptySpots = count($addableFighters);
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
				<?php for ($k = 1 ; $k <= $emptySpots; $k++): ?>
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

				<?php if($teamSize == 0): ?>
					<div class='callout warning text-center'>
						Max Team Size is currently set to zero, and you can not add fighters.<BR>Please use the Tournament Settings to specify your team size.
					</div>
				<?php endif ?>

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

function createNewTeamInterface($addableFighters, $teamSize){
	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

	$numUnassigned = count($addableFighters);
	if($numUnassigned < $teamSize){
		$emptySpots = $numUnassigned;
	} else {
		$emptySpots = $teamSize;
	}

?>
<!-- Visibility Button -->
	<button class='button' id='createTeamButton' onclick="$(this).hide();$(createTeamForm).show();">
		Create New Team
	</button>


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
				for ($k = 1 ; $k <= $emptySpots; $k++): ?>
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

				<?php if($teamSize == 0): ?>
					<div class='callout warning text-center'>
						Max Team Size is currently set to zero, and you can not add fighters.<BR>Please use the Tournament Settings to specify your team size.
					</div>
				<?php endif ?>


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

function showUnAssignedFighters($addableFighters){

	$numUnassigned = count($addableFighters);

?>

	<button class='button hollow' id='soloFightersButton' onclick="$(this).hide();$(soloFightersDisplay).show();">
		See Un-Assigned Fighters (<?=$numUnassigned?>)
	</button>

	<fieldset class='fieldset hidden' id='soloFightersDisplay'>
		<legend><h4>
			Un-Assigned Fighters (<?=$numUnassigned?>) &nbsp;
			<a class='button secondary no-bottom hollow small'
				id='createTeamShow' style='float:right'
				onclick="$(soloFightersButton).show();$(soloFightersDisplay).hide();">
				Close
			</a>
		</h4></legend>

		<table id="fightersWithoutTeam" class="display">

			<thead><tr>
				<th>Name</th>
				<th>School</th>
			</tr></thead>
			<tbody>
			<?php foreach($addableFighters as $f): ?>
				<tr>
					<td><?=$f['name']?></td>
					<td><?=$f['school']?></td>
				</tr>
			<?php endforeach ?>
			</tbody>
		</table>

	</fieldset>
<?php
}


/******************************************************************************/

function displayTeamRatingsTable($teamRosters){

	$showRating = (bool)readOption('E',$_SESSION['eventID'],'SHOW_FIGHTER_RATINGS');
	if(ALLOW['STATS_EVENT'] == FALSE && ALLOW['EVENT_SCOREKEEP'] == false && $showRating == false){
		return;
	}

	$teamList = [];
	foreach($teamRosters as $teamID => $team){
		$tmp = [];
		$tmp['name'] = getTeamName($teamID);
		$tmp['rating'] = getTeamRating($teamID);
		$teamList[] = $tmp;
	}

	usort($teamList, function($a, $b) {
	    return $b['rating'] <=> $a['rating'];
	});


?>


	<a onclick="$('.team-ratings-table').toggle()">Show Team Ratings ↓ </a>

	<div class='grid-x grid-margin-x'>
	<div class='large-5 team-ratings-table hidden'>
	<table class='data-table'>

		<?php foreach($teamList as $team): ?>
			<tr>
				<td><?=$team['name']?></td>
				<td><?=$team['rating']?></td>
			</tr>
		<?php endforeach ?>
	</table>
	</div>
	</div>
<?php

}

/******************************************************************************/

function teamsAutoCreateForm($addableFighters, $teamSize){
	if(ALLOW['EVENT_MANAGEMENT'] == false || $teamSize < 1){
		return;
	}

	$numUnassigned = count($addableFighters);
	if($numUnassigned < $teamSize){
		$emptySpots = $numUnassigned;
	} else {
		$emptySpots = $teamSize;
	}

	$teamsToMake = (int)($numUnassigned / $teamSize);
	$leftovers = $numUnassigned % $teamSize;

	if($leftovers != 0){
		$leftoverText = "<BR>However <b>{$leftovers}</b> fighter(s), picked at random, will be left without a team. (sorry)";
	} else {
		$leftoverText = "";
	}

?>
<!-- Visibility Button -->
	<button class='button hollow secondary' id='teamsAutoCreateButton' onclick="$(this).hide();$(teamsAutoCreateForm).show();">
		Auto Create Teams
	</button>



<!-- Creation Form -->
	<fieldset class='fieldset hidden' id='teamsAutoCreateForm'>
		<legend><h4>
			Auto Create &nbsp;
			<a class='button secondary no-bottom hollow small'
				id='createTeamShow' style='float:right'
				onclick="$(teamsAutoCreateButton).show();$(teamsAutoCreateForm).hide();">
				Close
			</a>
		</h4></legend>

		<p>This will automatically create teams out of the un-assigned fencers, based on their Rating. (Tournament Information > Fighter Ratings) The teams will be named "Pickup-A", "Pickup-B", etc. You can change the names of the teams manually after they are created.</p>
		<p>There are <b><?=$numUnassigned?></b> fighters not in a team. With a team size of <b><?=$teamSize?></b> this will result in <b><?=$teamsToMake?></b> teams. <?=$leftoverText?></p>

		<form method="POST">

			<input type='hidden' name='teamsAutoCreate[tournamentID]' value=<?=$_SESSION['tournamentID']?>>

		<!-- Sumbit Buttons -->
			<button class='button no-bottom success' name='formName' value='teamsAutoCreate'>
				Make It So
			</button>

			<a class='button secondary no-bottom align-right'
				id='createTeamShow' style='float:right'
				onclick="$(teamsAutoCreateButton).show();$(teamsAutoCreateForm).hide();">
				Cancel Team Creation
			</a>

		</form>

	</fieldset>

<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////