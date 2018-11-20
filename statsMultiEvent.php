<?php
/*******************************************************************************
	Event Summary
	
	Displays information about the event, such as fighter counts for
	each tournament and registrations from each club
	LOGIN
		- ADMIN and above can view the page
		
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Event & Tournament Stats';
include('includes/header.php');

if(USER_TYPE < USER_SUPER_ADMIN && USER_TYPE != USER_STATS){	
	pageError('user');
} else {
	
	filterBoxes();

	if(isset($_SESSION['tDataFilters'])){
		$tournamentList = getTournamentsForStats($_SESSION['tDataFilters']);
		$isDataQueried = true;
		if($_SESSION['tDataFilters']['tournamentWeaponID'] != 0){
			$wName = getTournamentAttributeName($_SESSION['tDataFilters']['tournamentWeaponID']);
		} else {
			$wName = 'All Weapoins';
		}

	} else {
		$isDataQueried = false;
	}
	
	unset($_SESSION['tDataFilters']);
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>	

		<div class='grid-x'>
		<div class='small-12 medium-10 text-center align-self-middle'>
			<?php if($isDataQueried): ?>
				Showing results for <strong><?=$wName?></strong>.
			<?php endif ?>
		</div>
		<div class='small-12 medium-2'>
			<button class='button expanded' data-open='filterBoxes'>
				New Query
			</button>
		</div>
	</div>


	<?php
		if($isDataQueried){
			displayResults($tournamentList);
		}
	?>


<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayResults($tournamentList){
 ?>

 	<table>
 		<tr>
 			<th>Event</th>
 			<th>Year</th>
 			<th>Weapon</th>
 			<th>Division</th>
 			<th>Clean Hits</th>
 			<th>Double Hits</th>
 			<th>Afterblows</th>
 			<th>No Quality</th>
 			<th>No Exchange</th>
 		</tr>


	<?php foreach($tournamentList as $t): 
		$tStats = getTournamentExchangeStats($t['tournamentID']);
		?>

		<tr>
			<td><?=$t['eventName']?></td>
			<td><?=$t['eventYear']?></td>
			<td><?=getTournamentAttributeName($t['tournamentWeaponID'])?></td>
			<td>
				<?=getTournamentAttributeName($t['tournamentPrefixID'])?>
				<?=getTournamentAttributeName($t['tournamentGenderID'])?>
				<?=getTournamentAttributeName($t['tournamentMaterialID'])?>
			</td>
			<td><?=$tStats['clean']?></td>
			<td><?=$tStats['double']?></td>
			<td><?=$tStats['afterblow']?></td>
			<td><?=$tStats['noQuality']?></td>
			<td><?=$tStats['noExchange']?></td>
		</tr>

	<?php endforeach ?>
	</table>

<?php
}

/******************************************************************************/

function getTournamentExchangeStats($tournamentID){

	$sql = "SELECT  
					COUNT(IF(exchangeType = 'clean',1,null)) AS clean,
					COUNT(IF(exchangeType = 'double',1,null)) AS 'double',
					COUNT(IF(exchangeType = 'afterblow',1,null)) AS afterblow,
					COUNT(IF(exchangeType = 'noQuality',1,null)) AS noQuality,
					COUNT(IF(exchangeType = 'noExchange',1,null)) AS noExchange
			FROM eventExchanges
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			WHERE tournamentID = {$tournamentID}";
	return  mysqlQuery($sql, SINGLE);
}


/******************************************************************************/

function filterBoxes(){
	
	$weaponList = getTournamentWeaponsList();

	?>
	
	<div class='reveal large' id='filterBoxes' data-reveal >
	<form method='POST'>
	<div class='grid-x grid-margin-x'>
			
<!-- Weapon select -->	
	<div class='input-group small-12 medium-6 large-3 cell'>
		<span class='input-group-label inline'>Weapon:</span>
		<select class='input-group-field' name='tDataFilter[tournamentWeaponID]'>
			<option value=''>- Any -</option>
			<?php foreach($weaponList as $weapon):
				if($weapon['numberOfInstances'] == 0){continue;}
				$id = $weapon['tournamentTypeID'];
				$name = $weapon['tournamentType'];
				?>
				<option value='<?=$id?>'><?=$name?></option>
			<?php endforeach ?>
		</select>
	</div>

<!-- Double Type select -->	
	<div class='input-group small-12 medium-6 large-3 cell'>
		<span class='input-group-label inline'>Double Type:</span>
		<select class='input-group-field' name='tDataFilter[doubleTypeID]'>
			<option value='0'>- Any -</option>
			<option value='1'>No Afterblow</option>
			<option value='2'>Deductive Afterblow</option>
			<option value='3'>Full Afterblow</option>
		</select>
	</div>

<!-- Submit button -->
	<div class='medium-2 cell'>
		<button class='button expanded' name='formName' value='tournamentDataFilters'>
			Lookup
		</button>
	</div>
	
	
	</div>
	</form>
	
	<?php closeRevealButton(); ?>
	
	</div>
		
<?php }

/******************************************************************************/

function getTournamentsForStats($filters){

	$whereCases = '';

	foreach($filters as $fIndex => $fValue){
		$id = (int)$fValue;
		if($id == 0){
			continue;
		}
		$whereCases .= "AND {$fIndex} = {$id} ";
	}

	$sql = "SELECT tournamentID, eventID, tournamentWeaponID, 
			tournamentPrefixID, tournamentGenderID, 
			tournamentMaterialID, tournamentRankingID, tournamentElimID,
			eventName, eventYear
			FROM eventTournaments
			INNER JOIN systemEvents USING(eventID)
			WHERE eventID != 2
			AND eventStatus = 'archived'
			{$whereCases}
			ORDER BY eventStartDate ASC";
	$list = mysqlQuery($sql, ASSOC);

	return $list;
}

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
