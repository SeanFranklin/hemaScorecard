<?php
/*************************************
	masterHemaRatings

	WORK IN PROGRESS
	Page for facilitating the HEMA Rating IDs

**************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'HEMA Ratings Interface';
include('includes/header.php');

if(USER_TYPE < USER_SUPER_ADMIN){
	pageError('user');

} else {

	$systemFighters = getNumberOfFightersInSystem();
	$numberOfFightersInSystem = $systemFighters['numTotalFighters'];
	$numRated = $systemFighters['numRatedFighters'];
	$numUnrated = $numberOfFightersInSystem - $numRated;

	$ratingsInfo = '';
	$searchIDs = '';
	$searchNoIDs = '';
	if(isset($_SESSION['HemaRatingsBounds'])){
		$ratingsInfo = getHemaRatingsInfo($_SESSION['HemaRatingsBounds']);

		$lowBound = (int)$_SESSION['HemaRatingsBounds']['lowBound'];
		$highBound = (int)$_SESSION['HemaRatingsBounds']['highBound'];

		if(isset($_SESSION['HemaRatingsBounds']['searchIDs'])){
		$searchIDs = 'checked';
		}

		if(isset($_SESSION['HemaRatingsBounds']['searchNoIDs'])){
			$searchNoIDs = 'checked';
		}

		if(!isset($_SESSION['HemaRatingsBounds'])){
			$searchIDs = 'checked';
			$searchNoIDs = 'checked';
		}
	}

	
	

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>

<!-- Search fields and filters -->
	Total Number of Fighters in System: <?=$numberOfFightersInSystem?> |
	Number Rated: <?=$numRated?> |
	Number Unrated: <?=$numUnrated?>

	<form method='POST'>
	<div class='grid-x grid-margin-x'>

	<!-- Search Fields -->
		<div class='medium-6 large-3 cell input-group no-bottom'>
			<span class='input-group-label'>Low Bound:</span>
			<input class='input-group-field' type='number' name='HemaRatingsBounds[lowBound]' 
				value = '<?=$lowBound?>' min='1' max='<?=$numberOfFightersInSystem?>'>
		</div>
		

		<div class='medium-6 large-3 cell input-group no-bottom'>
			<span class='input-group-label'>High Bound:</span>
			<input class='input-group-field' type='number' name='HemaRatingsBounds[highBound]' 
				value = '<?=$highBound?>' min='1' max='<?=$numberOfFightersInSystem?>'>
		</div>

		<div class='large-2 medium-2 small-4 cell'>
			w/ ID: <input type='checkbox' name='HemaRatingsBounds[searchIDs]' <?=$searchIDs?>>
			<BR>
			w/o ID: <input type='checkbox' name='HemaRatingsBounds[searchNoIDs]' <?=$searchNoIDs?>>
		</div>

	<!-- Submit Buttons -->
		<div class='large-2 medium-2 small-4 cell'>
			<button class='button expanded no-bottom' name='formName' value='HemaRatingsList'>
				Search
			</button> 
		</div>



	</div>
	</form>


	<hr>
<!-- Display results and enter new data -->
	
	
	<?php if($ratingsInfo == null):
		displayAlert("No Results");
	else:?>
		<div class='grid-x grid-margin-x'>

		<div class='large-12 cell'>
		<form method='POST'>
		

		<div class='large-2 medium-2 small-4 cell'>
			<button class='button expanded success no-bottom' name='formName' value='HemaRatingsUpdate'>
				Update
			</button>
		</div>

		<ul class='accordion' data-accordion  data-allow-all-closed='true'>
		<li class='accordion-item is-active' data-accordion-item>
			<a class='accordion-title'>
				<h4>Names List</h4>
			</a>
			<div class='accordion-content' data-tab-content>
				<?php foreach((array)$ratingsInfo as $fighter):?>
					<?=getFighterNameSystem($fighter['systemRosterID'],'first')?>
					<BR>
				<?php endforeach ?>
			</div>
		</li>

		<li class='accordion-item' data-accordion-item>
			<a class='accordion-title'>
				<h4>Data Fields</h4>
			</a>

			<div class='accordion-content' data-tab-content>
			<table>
			<?php foreach((array)$ratingsInfo as $fighter):
				$name = getFighterNameSystem($fighter['systemRosterID'],'array');

				?>
				<tr>
					<td><?=$name['firstName']?> <?=$name['lastName']?></td>
					<td>
						<input type='number' 
							name='systemRosterID[<?=$fighter['systemRosterID']?>][HemaRatingsID]'
							value='<?=$fighter['HemaRatingsID']?>'>
					</td>
					<td>
						<input type='text' 
							name='systemRosterID[<?=$fighter['systemRosterID']?>][firstName]'
							value="<?=$name['firstName']?>">
					</td>
					<td>
						<input type='text' 
							name='systemRosterID[<?=$fighter['systemRosterID']?>][lastName]'
							value="<?=$name['lastName']?>">
					</td>
					<td>
						<?=getSchoolName($fighter['schoolID'],'full','branch')?>
					</td>
					
				</tr>

			<?php endforeach ?>
			</table>
			</div>
		</li>
		</ul>
		
		</form>
		</div>
		
		</div>
<?php endif;
	
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/


/******************************************************************************/