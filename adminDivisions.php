<?php
/*******************************************************************************
	Event Details

	Change event passwords and set defaults for newly created tournaments
	LOGIN:
		- ADMIN or higher required to view

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Tournament Divisions';
$jsIncludes[] = "roster_management_scripts.js";
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false){
	pageError('user');
} else {

	$defaults = getEventDefaults($_SESSION['eventID']);

	if(ALLOW['SOFTWARE_ASSIST'] == true){
		$canChangeSettings = true;
	} elseif($_SESSION['eventID'] == TEST_EVENT_ID){
		// user loged in to test event for test purposes
		$canChangeSettings = false;
	} elseif(ALLOW['EVENT_MANAGEMENT'] == true) {
		$canChangeSettings = true;
	} else {
		// VIEW_SETTINGS case
		$canChangeSettings = false;
	}

	$tournamentIDs = getEventTournaments($_SESSION['eventID']);
	$tournamentDivisions = getTournamentDivisions($_SESSION['eventID']);




// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<ul class="tabs" data-tabs id="admin-divisions-tabs">


	<li class="tabs-title is-active">
		<a data-tabs-target="panel-div-management">
			Create/Manage Divisions
		</a>
	</li>


	<li class="tabs-title">
		<a data-tabs-target="panel-div-registration">
			Division Tournament Entries
		</a>
	</li>

	<li class="tabs-title">
		<a data-tabs-target="panel-div-split">
			Sort Fighters in Divisions
		</a>
	</li>

</ul>

	<div class="tabs-content" data-tabs-content="admin-divisions-tabs">

		<div class="tabs-panel is-active" id="panel-div-management">
			<div class='grid-x grid-margin-x'>

			<p class='cell'>Tournament divisions allow you to group tournaments together, such as creating on "Open Longsword" tournament with "Longsword Tier-A" and "Longsword Tier-B" grouped underneath. This doesn't affect how the tournaments are run, but makes it easier to keep track when you have long tournament lists that you want to keep organized.</p>



			<?php foreach($tournamentDivisions as $div):?>
				<?=tournamentDivisions($div, $tournamentIDs)?>
			<?php endforeach ?>

			<?=tournamentDivisions(null, $tournamentIDs)?>

			</div>
		</div>

		<div class="tabs-panel" id="panel-div-registration">
			<?=tournamentDivRegistrations($tournamentIDs)?>
		</div>

		<div class="tabs-panel " id="panel-div-split">

			<div class='grid-x grid-margin-x'>
				<?=tournamentDivSplit($tournamentDivisions)?>
			</div>

		</div>

	</div>


<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function tournamentDivSplit($tournamentDivisions){
?>

	<div class='cell large-12'>
		<p>This tool exists to make seeding people into tiers easier. Instructions at bottom of page.</p>
	</div>


<!-- Division select ---------------------------------------->
	<div class='medium-6 cell input-group'>
		<span class='input-group-label'>
			Division To Seed
		</span>
		<select class='input-group-field' onchange="divSeedingPickDiv()" id='split-div-id'>
			<option></option>
			<?php foreach($tournamentDivisions as $div): ?>
				<option value='<?=$div['divisionID']?>'><?=$div['divisionName']?></option>
			<?php endforeach ?>
		</select>
	</div>


<!-- Donor tournament select ---------------------------------->
	<div class='medium-6 cell input-group'>
		<span class='input-group-label'>
			Donor Tournament
		</span>
		<select class='input-group-field' onchange="divSeedingPickDonor(this)" id='split-donor-id'>
			<option></option>
		</select>
	</div>


	<div class='cell large-12'>
	</div>


<!-- Ratings entry ---------------------------------->
	<div class='cell large-6'>

		<div class='callout success' id='donor-info-div'></div>

		<form method="POST">
			<input class='hidden' name='divSeeding[donorID]' id='donor-id-form'>
			<table >
				<thead>
					<tr>
						<th>Tournament</th>
						<th colspan=2>Fighters</th>
						<th>Min Rating</th>
					</tr>
				</thead>
				<tbody id='split-items-table'>
				</tbody>
			</table>
			<div class='text-right'>
				Remove fighters from donor tournament <br>(only works if donor hasn't started):
				<input type='checkbox' class='ratings-form-submit' name='divSeeding[removeFromDonor]' value=1 disabled>
			</div>
			<button class='button submit success ratings-form-submit' name='formName' value='divSeedingByRating' disabled>
				Submit
			</button>
		</form>
	</div>


<!-- Ratings plot ---------------------------------->
	<div class='cell large-6'>
		<div id="ratings-chart" style="height: 500px; border:black solid 1px"></div>
	</div>


<!-- Documentation ---------------------------------->
	<div class='cell large-12'>
		<h4>Instructions</h4>
		<p>Select a division and a donor tournament to split into the other tournaments in the division. The ratings are based on what was entered into Tournament Information > Fighter Ratings.
		The order the tournaments appear is Highest -> Lowest. To have this tool work please make sure you have manually sorted your tournaments in Event Organization > Event Settings. In the Tournament Order field of the Display Settings order the tournaments in each division from highest to lowest. (You can have them next to each other or spread out. They just have to be in the correct order with the highest tier at the top.)<BR>
		<u>Note</u>: There is nothing preventing someone from being in multiple tiers in a division, and this tool only adds to tiers (not removes from). If you run it and someone is already in a tier it will just update the rating they have in the tier to match the donor. If you run it and they get seeded into a tier that is different from one they are already in they will be entered in both.</p>
	</div>



<?php
}

/******************************************************************************/

function tournamentDivRegistrations($tournamentIDs){
?>

<p>Suppress direct entry into the following tournaments on the <b>Event Roster</b> page. You can still move people into these tournaments manually after entry into the event, this option is only to reduce the clutter of options on that page if your are registering them into a limited number of tournaments, and then seeding them later.</p>

	<form method="POST">

	<button class='button success' name='formName' value='suppressDirectEntry'>
		Update List
	</button>


	<?php foreach($tournamentIDs as $tournamentID):?>
		<BR>
		<input type='hidden' class='radio no-bottom' name='suppressDirectEntry[tournamentIDs][<?=$tournamentID?>]' value=0>
		<input type='checkbox' class='radio no-bottom' name='suppressDirectEntry[tournamentIDs][<?=$tournamentID?>]' value=1
			<?=chk(readOption('T', $tournamentID, 'SUPPRESS_DIRECT_ENTRY'),1)?>>
		<?=getTournamentName($tournamentID)?>

	<?php endforeach ?>

</form>

<?php
}

/******************************************************************************/

function tournamentDivisions($divInfo, $tournamentIDs){

	if($divInfo == null){
		$divisionID = -1;
		$divisionName = '';
		$placeholder = 'Add new division';
	} else {
		$divisionID = $divInfo['divisionID'];
		$divisionName = $divInfo['divisionName'];
		$placeholder = '';
	}

	$divisionItems = (array)getTournamentDivisionItems($divisionID);

	$toDisplay = [];
	foreach($tournamentIDs as $tournamentID){

		$tmp = [];
		$tmp['ID'] = $tournamentID;
		$tmp['name'] = getTournamentName($tournamentID);

		if(isset($divisionItems['items'][$tournamentID]) == true){
			$tmp['check'] = "checked";
		} else {
			$tmp['check'] = "";
		}

		$toDisplay[] = $tmp;

	}


?>

	<form method='POST' class='cell large-7'>
	<fieldset class='fieldset cell'>

		<legend>
			<div class='input-group no-bottom'>

				<span class='input-group-label no-bottom'>Division Name:</span>

				<input type='text' class='input-group-field no-bottom'
					name='divisionInfo[divisionName]' value='<?=$divisionName?>'
					placeholder='<?=$placeholder?>' required>

				<span class='input-group-label no-bottom'>
					<b><a onclick="$('#division-form-for-<?=$divisionID?>').toggleClass('hidden')">↓</a></b>
				</span>

			</div>
		</legend>

		<input class='hidden' name='formName' value='updateTournamentDivisions'>
		<input class='hidden' name='divisionInfo[eventID]' value='<?=$_SESSION['eventID']?>'>

		<div class='hidden' id='division-form-for-<?=$divisionID?>'>

			<button class='button success' name='divisionInfo[divisionID]' value=<?=$divisionID?>>
				Update Division
			</button>

			<?php foreach($toDisplay as $t):?>
				<BR>
				<input type='checkbox' class='radio no-bottom' value=<?=$t['ID']?><?=$t['check']?>
					name='divisionInfo[tournamentIDs][<?=$t['ID']?>]' >
				<?=$t['name']?>

			<?php endforeach ?>

			<?php if($divisionID >= 0): ?>

				<HR>
				<a class='button alert hollow' onclick="$('#delete-division-<?=$divisionID?>').toggle('hidden')">
					Delete Division
				</a>
			<?php endif ?>

		</div>

	</fieldset>
	</form>


<!-- Delete button ------------------------------------>
	<?php if($divisionID >= 0): ?>

		<form method='POST' class='cell large-7'>

		<input class='hidden' name='formName' value='deleteTournamentDivision'>

		<div class='hidden text-right' id='delete-division-<?=$divisionID?>'>
			<button class='button alert' name='divisionInfo[divisionID]' value=<?=$divisionID?>>
				Yeah, I'm really sure I want to delete "<b><?=$divisionName?></b>"
			</button>
		</div>

		</form>
	<?php endif ?>

<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
