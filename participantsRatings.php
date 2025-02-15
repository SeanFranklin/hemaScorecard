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
$createSortableDataTable[] = ['fighterRatingTable',100];
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

	$ratingsNumbers = [];
	foreach($tournamentRoster as $fighter){
		$ratingsNumbers[] = (int)$fighter['rating'];
	}

	arsort($ratingsNumbers);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>


<!-- Page Structure -->

	<a class='rating-faq button small secondary hollow' onclick="$('.rating-faq').toggle()">
		What's going on here? ↓
	</a>



	<div class='callout primary hidden rating-faq'>


		<p><a class='button small secondary hollow no-bottom' onclick="$('.rating-faq').toggle()">
			[I'm done, hide this again. ↑]
		</a></p>

		<p><i>How does this work?</i><BR>
		Enter a rating for your participants to have the auto-seeding features for the pools work. You can enter anything you want for a rating. While HEMA Ratings would be the most common, you could also just rate people from 1-5. (Higher is better).</p>

		<p><i>How do I specify they don't have a rating?</i><BR>
		Both "0" and a blank entry will be considered unrated. So you can enter in zero if you want to denote that you have confirmed that you will not use a rating for them.</p>

		<p><i>Why is there a second rating column?</i><BR>
		Glad you asked. First of all the table won't sort the order based on the input column, second this updates when the data you entered has been saved in the database. However the sorting of the table will not update with the new rating, and you'll have to refresh the page to sort based on the new numbers.</p>

		<p><i>Can I see more information?</i><BR>
		Yes!
		<BR><a onclick="$('.school-name-id').toggle()">
			Show School Name
		</a>
		<BR><a onclick="$('.hema-ratings-id').toggle()">
			Show HEMA Ratings ID
		</a>	<?=tooltip('Shows the ID from the HEMA Ratings database so you can look fighters up more easily. (Ability to import ratings directly is a work in progress.) No HEMA Ratings ID means that there is none saved in the HEMA Scorecard database.')?>
		</p>


	</div>

	&nbsp;&nbsp;&nbsp;
	<a class='button small primary' href='participantsRatings.php'>Reload Page</a>


	<div class='grid-x grid-padding-x'>
	<div class='large-8 medium-12 cell'>

		<input type='hidden' name='updateRatings[tournamentID]' value=<?= $tournamentID ?> >

		<table id="fighterRatingTable" class="display">

	<!-- Table headers -->

		<thead>
		<tr>
			<th  class='hidden hema-ratings-id'>
				HR Id
			</th>

			<th>
				<a>Name</a>
			</th>

			<th  class='hidden school-name-id'>
				School
			</th>

			<th>
				<a>Data</a>
				<?=tooltip("Ranked high to low.<BR><BR>
							<em>aka</em>: High Score for good fighters, Low Score for un-good fighters.")?>
			</th>

			<th>
				<a>Rating</a>
			</th>

			<!----
			This feature is disabled for now
			<th class='sub-group-input 'onclick="changeParticipantOrdering('ratingViewMode','subGroup')">
				<a>SubGroup</a>
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
			--->
		</tr>
		</thead>

		<tbody>

		<?php foreach($tournamentRoster as $fighter):
			$tRosterID = $fighter['tournamentRosterID'] ?>
			<tr>
				<td  class='hidden hema-ratings-id'>
					<?=hemaRatings_getFighterIDfromRosterID($fighter['rosterID'])?>
				</td>

				<td  style='white-space:nowrap;'>
					<?=getFighterName($fighter['rosterID'])?>
				</td>

				<td  class='hidden school-name-id'>
					<i><?=getSchoolName($fighter['schoolID'])?></i>
				</td>

				<td>
					<input type='number' class='no-bottom' <?=LOCK_TOURNAMENT?>
						placeholder='unknown' min=0 step=1
						id='rating-input-<?=$tRosterID?>'
						onchange="updateFighterRating(<?=$tRosterID?>)"
						value='<?=$fighter['rating']?>' style='width:8em'>
				</td>

				<td id='rating-output-<?=$tRosterID?>'>
					<?=$fighter['rating']?>
				</td>
<!---- This feature is disabled for now
				<td class='sub-group-input '>
					<input type='number' class='no-bottom'
						name='updateRatings[fighters][?=$tRosterID?>][subGroupNum]'
						value = '$fighter['subGroupNum']'>
				</td>


					<td class='rating2 hidden'>
						<input type='number' name='updateRatings[fighters][?=$tRosterID?>][rating2]]'
							value = '?=$fighter['rating2']?>'>
					</td>
				---->
			</tr>
		<?php endforeach ?>

	<!-- Page Structure -->
		</tbody>
		</table>

		<!----
		<div class='text-right large-12 hidden'>
			<a onclick="$('.sub-group-input').toggle()">
				Use Sub-Group Feature
			</a>
			?=tooltip("This option is for keeping a number of people separate when pools are assigned.<BR>
							Ask for directions if you are planning on using this feature.")?>
		</div>
		---->

	</div>
	</div>


	<p>
	<a onclick="$('#rating-list-text').toggle()">Show a copy-pasteable table ↓</a>
	<table id='rating-list-text' class='hidden'>
		<tr><th>Name</th><th>School</th><th>Rating</th></tr>
		<?php foreach($tournamentRoster as $fighter):
			$tRosterID = $fighter['tournamentRosterID'] ?>
			<tr>
				<td  style='white-space:nowrap;'>
					<?=getFighterName($fighter['rosterID'])?>
				</td>
				<td>
					<?=getSchoolName($fighter['schoolID'])?>
				</td>
				<td>
					<?=$fighter['rating']?>
				</td>
			</tr>
		<?php endforeach ?>
	</table>

	</p><p>
	<a onclick="$('#rating-list-number').toggle()">Show raw numbers ↓</a>
	<div id='rating-list-number' class='hidden callout'>

		<?php foreach($ratingsNumbers as $r):?>
			<?=$r?>,
		<?php endforeach ?>
	</p>

<!-- Navigate pool sets -->

<?php
}

include('includes/footer.php');

/******************************************************************************/





/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////