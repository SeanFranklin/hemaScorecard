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

<!-- Search fields and filters -->
	Total Number of Fighters in System: <?=$inSystem['total']?> |
	Number Rated: <?=$inSystem['rated']?> |
	Number Unrated: <?=$inSystem['unrated']?>

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

		<tr>
			<td>
				<button class='button success tiny no-bottom hollow'
					name='formName' value='HemaRatingsUpdate'>
					✓
				</button>

			<td><?=$name?></td>
			<td><?=getSchoolName($fighter['schoolID'])?></td>
			<td><?=$fighter['schoolCountry']?></td>
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