<?php
/*******************************************************************************
	Tournament Roster
	
	View tournament roster and add fighters
	Login:
		- ADMIN or above can add or remove fighters from the tournament
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Tournament Roster';
$includeTournamentName = true;
$lockedTournamentWarning = true;
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];
if($tournamentID == null){
	displayAnyErrors("No Tournament Selected",1);
} else{

	toggleFighterListSort();

	$eventRoster = getEventRoster();
	if($_SESSION['rosterViewMode'] == 'school'){
		$sortString = 'school';
	} else {
		$sortString = 'name';
	}

	$tournamentRoster = getTournamentRoster($tournamentID,$sortString);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Page Structure -->
	<form method='POST' id='tournamentRosterForm'>
	<fieldset <?=LOCK_TOURNAMENT?>>
	
	<div class='grid-x grid-padding-x'>
	<div class='large-6 medium-8 cell'>
							
	<input type='hidden' name='tournamentID' value=<?= $tournamentID ?> id='tournamentID'>

	<table>
		
<!-- Table headers -->
	<thead>
	<tr>
		<th><a onclick="changeRosterOrderType('name')">Name</a></th>
		<th><a onclick="changeRosterOrderType('school')">School</a></th>
		<?php if(USER_TYPE >= USER_ADMIN): //only admins can delete participants ?>
			<th>Remove</th>
		<?php endif ?>
	</tr>
	</thead>
	<tbody>

<!-- Display existing participants -->
	<?php foreach ($tournamentRoster as $person):
		$namesEntered[$person['rosterID']] = 'entered';
		$rosterID = $person['rosterID']; ?>
		<tr id='divFor<?= $rosterID ?>'>
			<td><?=getFighterName($rosterID)?></td>
			<td><?= $person['schoolShortName'] ?></td>
			<?php if(USER_TYPE >= USER_ADMIN): ?>
				<td>
					<input type='checkbox' name='deleteFromTournament[<?= $rosterID ?>]'
						id='<?= $rosterID ?>' onchange="checkIfFought(this)">
				</td>
			<?php endif ?>
		</tr>
	<?php endforeach ?>


	<?php if(USER_TYPE >= USER_ADMIN): ?>
<!-- Add new participants -->
		<?php $numBlankEntries = 5;
		for ($k = 1 ; $k <= $numBlankEntries; $k++): ?>
			<tr>
			<td>
				<select name='addToTournament[<?= $k ?>]'>
				<option value=''></option>
				<?php foreach($eventRoster as $entry): ?>
					<?php if(!isset($namesEntered[$entry['rosterID']])): ?>
						<option value='<?= $entry['rosterID'] ?>'>
							<?=getFighterName($entry['rosterID']) ?>
						</option>
					<?php endif ?>
				<?php endforeach ?>
				</select>
			</td>
			<td></td>
			<td></td>
			</tr>
	
		<?php endfor ?>
	<?php endif ?>

<!-- Page Structure -->
	</tbody>
	</table>
	
	</div>
	</div>

	<?php if(USER_TYPE >= USER_ADMIN): ?>
<!-- Add / Delete Fighter Buttons -->
		<button class='button success' name='formName' 
			value='addToTournamentRoster' <?=LOCK_TOURNAMENT?>>
			Add Fighters
		</button>
		
		<?php confirmDeleteReveal('tournamentRosterForm', 'deleteFromTournamentRoster', 'large'); ?>
		<span id='deleteButtonContainer'>
			<button class='button alert hollow' name='formName' value='deleteFromTournamentRoster' 
				id='deleteButton' <?=LOCK_TOURNAMENT?>>
				Delete Selected
			</button>
			
		</span>
	
	<?php endif ?>	
		
	</fieldset>
	</form>	
		
<?php 		
	
}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
