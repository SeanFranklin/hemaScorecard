<?php
/*************************************
	masterHemaRatings

	WORK IN PROGRESS
	Page for facilitating the HEMA Rating IDs

**************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'HEMA Ratings Interface';
$jsIncludes[] = 'misc_scripts.js';
include('includes/header.php');

if(ALLOW['SOFTWARE_ADMIN'] == false){
	pageError('user');

} else {

	$inSystem = hemaRatings_getSystemCount();
	$unratedFighters = hemaRatings_getUnrated();

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>
	<script>
		<?php if(ALLOW['SOFTWARE_ADMIN'] == true):?>
			var HEMA_RATINGS_TOKEN = "<?=HEMA_RATINGS_TOKEN?>";
			var HEMA_RATINGS_BY_NAME = "<?=HEMA_RATINGS_BY_NAME?>";
			var HEMA_RATINGS_BY_NAME = "<?=HEMA_RATINGS_BY_ID?>";
		<?php endif ?>
	</script>


<!-- Search fields and filters -->
	Total Number of Fighters in System: <?=$inSystem['total']?> |
	Number Rated: <?=$inSystem['rated']?> |
	Number Unrated: <?=$inSystem['unrated']?>

	<div class='hidden callout alert' id='hema-ratings-unidentifed-warning'>
		<strong>Warning!</strong> Some name could not be found on HEMA Ratings and have been hidden.<BR>
		<em>You need to refresh the page to get them back. </em>
	</div>

	<a class='button hollow success' onclick="hemaRatings_getByNameAll()">
		Check All
	</a>

	<form method='POST'>
	<table>
		<thead>
			<tr>
				<th></th>
				<th>Name</th>
				<th>School</th>
				<th>Country</th>
				<th>Check</th>
			</tr>
		</thead>

		<?=displayUnratedFighters($unratedFighters)?>

	</table>
	</form>


<?php 
	
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayUnratedFighters($fighterList){
?>
	<?php foreach($fighterList as $fighter): 
		$name = getFighterNameSystem($fighter['systemRosterID']);
		?>

		<tr id='unrated-row-<?=$fighter['systemRosterID']?>'>
			<td>
				<button class='button success tiny no-bottom hollow'
					name='formName' value='hemaRatings_UpdateFighterIDs'>
					✓
				</button>

			<td><?=$name?></td>
			<td><?=getSchoolName($fighter['schoolID'])?></td>
			<td><?=$fighter['countryName']?></td>
			<td id='divFor-<?=$fighter['systemRosterID']?>'>
				<a class='button hollow warning tiny no-bottom hemaRatingsGetInfo' 
					onclick="hemaRatings_getByName(this,`<?=$name?>`,<?=$fighter['systemRosterID']?>)">
					<strong>?</strong>
				</a>
			</td>
		</tr>

	<?php endforeach ?>

<?php
}

/******************************************************************************/