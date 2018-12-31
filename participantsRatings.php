<?php
/*******************************************************************************
	Fighter Management
	
	Withdraw fighters if they are injured and can no longer compete
	LOGIN:
		- ADMIN or higher required to access
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Manage Fighters';
$lockedTournamentWarning = true;
$includeTournamentName = true;
$lockedTournamentWarning = true;
$jsIncludes[] = 'roster_management_scripts.js';
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false){
	pageError('user');
} else if($tournamentID == null){
	pageError('tournament');
} else {

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		$formLock = 'disabled';
	} else {
		$formLock = '';
	}

	if($_SESSION['formatID'] != FORMAT_MATCH){
		displayAlert("This isn't a sparing tournament, entering information here does nothing.");
	}

	if($_SESSION['ratingViewMode'] == 'rating'){
		$sortString = 'rating';
	} else {
		$sortString = 'name';
	}

	$tournamentRoster = getTournamentFighters($tournamentID,$sortString);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>	

<!-- Page Structure -->
	
	<form method='POST' id='tournamentRatingsForm'>
	<fieldset <?=LOCK_TOURNAMENT?>>
	
	<div class='grid-x grid-padding-x'>
	<div class='large-6 medium-8 cell'>			
	<input type='hidden' name='updateRatings[tournamentID]' value=<?= $tournamentID ?> >

	<table>

<!-- Table headers -->
	<thead>
	<tr>
		<th onclick="changeParticipantOrdering('ratingViewMode','name')"><a>Name</a></th>
		<th onclick="changeParticipantOrdering('ratingViewMode','rating')"><a>Rating</a></th>
	</tr>
	</thead>
	<tbody>

	<?php foreach($tournamentRoster as $fighter): ?>
		<tr>
			<td>
				<?=getFighterName($fighter['rosterID'])?>
			</td>
			<td>
				<input type='number' name='updateRatings[fighters][<?=$fighter['rosterID']?>]'
					value = '<?=$fighter['rating']?>'>
					
			</td>
		</tr>
	<?php endforeach ?>

<!-- Page Structure -->
	</tbody>
	</table>
	
	</div>
	</div>

	<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
<!-- Add / Delete Fighter Buttons -->
		<button class='button success' name='formName' 
			value='updateFighterRatings' <?=LOCK_TOURNAMENT?>>
			Update Ratings
		</button>
		
	<?php endif ?>	
		
	</fieldset>
	</form>	
	
<!-- Navigate pool sets -->

<?php
}

include('includes/footer.php');	

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////