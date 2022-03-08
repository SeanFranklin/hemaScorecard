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
	
	<a onclick="$('.hema-ratings-id').toggle()">
		Show HEMA Ratings ID
	</a>
	<?=tooltip('Shows the ID from the HEMA Ratings database so you can look fighters up more easily. (Ability to import ratings directly is a work in progress.) No HEMA Ratings ID means that there is none saved in the HEMA Scorecard database.')?>


	<form method='POST' id='tournamentRatingsForm'>
	<fieldset <?=LOCK_TOURNAMENT?>>
	
	<div class='grid-x grid-padding-x'>
	<div class='large-7 medium-9 cell'>			
	<input type='hidden' name='updateRatings[tournamentID]' value=<?= $tournamentID ?> >

	<table>

<!-- Table headers -->
	
	<tr>
		<th onclick="changeParticipantOrdering('ratingViewMode','name')">
			<a>Name</a>
		</th>
		<th onclick="changeParticipantOrdering('ratingViewMode','rating')">
			<a>Rating</a>
			<?=tooltip("Ranked high to low.<BR><BR>
						<em>aka</em>: High Score for good fighters, Low Score for un-good fighters.")?>
		</th>
		<th onclick="changeParticipantOrdering('ratingViewMode','subGroup')"><a>SubGroup</a>
			<?=tooltip("This option is for keeping a number of people separate when pools are assigned.<BR>
						Ask for directions if you are planning on using this feature.")?>
		</th>
		<td>
			<a onclick="toggleClass('rating2')" class='rating2' style='white-space:nowrap;' >
				Use Rating 2 &#8594;
				<?=tooltip("DO NOT USE unless you know what this does already.")?>
			</a>
			<a onclick="toggleClass('rating2')" class='rating2 hidden'>Rating 2 &#8592;</a>
		</td>
	</tr>
	
	<tbody>

	<?php foreach($tournamentRoster as $fighter):
		$tRosterID = $fighter['tournamentRosterID'] ?>
		<tr>
			<td  style='white-space:nowrap;'>
				<span class='hidden hema-ratings-id'>
					| <?=hemaRatings_getFighterIDfromRosterID($fighter['rosterID'])?> | 
				</span>
				<?=getFighterName($fighter['rosterID'])?>
				<input type='hidden' name='updateRatings[fighters][<?=$tRosterID?>][ratingID]]'
					value = '<?=$fighter['ratingID']?>'>
			</td>
			<td>
				<input type='number' name='updateRatings[fighters][<?=$tRosterID?>][rating]]'
					value = '<?=$fighter['rating']?>'>
			</td>
			<td>
				<input type='number' name='updateRatings[fighters][<?=$tRosterID?>][subGroupNum]]'
					value = '<?=$fighter['subGroupNum']?>'>
			</td>
			<td class='rating2 hidden'>
				<input type='number' name='updateRatings[fighters][<?=$tRosterID?>][rating2]]'
					value = '<?=$fighter['rating2']?>'>
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





/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////