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
$jsIncludes[] = 'roster_management_scripts.js';
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];
if($tournamentID == null){
	pageError('tournament');
} else{

	$eventRoster = getEventRoster();

	if($_SESSION['rosterViewMode'] == 'school'){
		$sortString = 'school';
	} else {
		$sortString = 'name';
	}


	if(ALLOW['EVENT_MANAGEMENT'] != true && $_SESSION['formatID'] != FORMAT_COMPOSITE){
		define("ALLOW_EDITING", false);
	} else {
		if(    $_SESSION['formatID'] != FORMAT_COMPOSITE
			|| isCompositeRosterManual($tournamentID) == true){
			define("ALLOW_EDITING", true);
		} else {
			define("ALLOW_EDITING", false);
		}
	}

	$tournamentRoster = getTournamentFighters($tournamentID,$sortString);
	$numFighters = count($tournamentRoster);

	if($_SESSION['rosterViewMode'] == 'school'){
		$schoolArrow = "&#8595";
		$nameArrow = '';
	} else {
		$schoolArrow = "";
		$nameArrow = "&#8595";
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Page Structure -->
	
	<form method='POST' id='tournamentRosterForm'>
	<fieldset <?=LOCK_TOURNAMENT?>>
	
	<div class='grid-x grid-padding-x'>
	<div class='large-6 medium-8 cell'>
	<h4>Number of Fighters: <?=$numFighters?></h4>					
	<input type='hidden' name='tournamentID' value=<?= $tournamentID ?> id='tournamentID'>

	<table>
		
<!-- Table headers -->
	<thead>
	<tr>
		<th onclick="changeParticipantOrdering('rosterViewMode','name')">
			<a>Name <?=$nameArrow?></a>
		</th>

		<th onclick="changeParticipantOrdering('rosterViewMode','school')">
			<a>School <?=$schoolArrow?></a>
		</th>

		<?php if(ALLOW_EDITING == true): //only event organizers ?>
			<th>Remove</th>
		<?php endif ?>
	</tr>
	</thead>
	<tbody>

<!-- Display existing participants -->
	<?php foreach ($tournamentRoster as $person):
		$namesEntered[$person['rosterID']] = 'entered';
		$rosterID = $person['rosterID'];
		$schoolID = $person['schoolID'];
	?>
		<tr id='divFor<?= $rosterID ?>'>
			<td><?=getFighterName($rosterID)?></td>
			<td><?=getSchoolName($schoolID)?></td>
			<?php if(ALLOW_EDITING == true): ?>
				<td>
					<input type='checkbox' name='deleteFromTournament[<?= $rosterID ?>]'
						id='<?= $rosterID ?>' onchange="checkIfFought(this)">
				</td>
			<?php endif ?>
		</tr>
	<?php endforeach ?>


	<?php if(ALLOW_EDITING == true): ?>
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

	<?php if(ALLOW_EDITING == true): ?>
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
