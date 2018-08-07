<?php
/*************************************
	masterDuplicates

	WORK IN PROGRESS
	Allows the system administrator to search
	for the same fighter existing as separate entities.

**************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Duplicate Name Entries';
include('includes/header.php');
$jsIncludes[] = "misc_scripts.js";

if(USER_TYPE < USER_SUPER_ADMIN){
	pageError('user');

} else {

	$allDuplicates = [];

	if(isset($_SESSION['duplicateNameSearchType'])){

		$sql = "SELECT systemRosterID, firstName, lastName, schoolID
				FROM systemRoster";
		$systemRoster = mysqlQuery($sql, ASSOC);

		foreach($systemRoster as $fighter){
			switch($_SESSION['duplicateNameSearchType']){
				case 'lastName_school':
					$newDuplicates = match_LastName_School($fighter);
					break;
				default:
					$newDuplicates = null;
					break;
			}

			
			if(count($newDuplicates) == 0){ 
				continue;
			}

			$duplicateToAdd = [];
			$duplicateToAdd[0] = $fighter;
			foreach($newDuplicates as $data){
				$duplicateToAdd[] = $data;
			}


			$allDuplicates[] = $duplicateToAdd;
			
			
		}
	}


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>

<!-- Select search type -->
	<form method="POST">
		<input type='hidden' name='formName' value='duplicateNameSearchType'>
		<?php
		changeSearchButton("No Search", "");
		changeSearchButton("Match: Last Name & School", "lastName_school");
		?>
	</form>
	<hr>

<!-- Display search results -->
	<table>
	<?php 

// No search results
	if(count($allDuplicates) == 0){
		displayAlert("No Duplicates Found");
	}

// Step through duplicates
	foreach((array)$allDuplicates as $setNum => $set):
		$numInSet = count($set);

		
		$setInfo = [];

	// Display the duplicate fighters in a set
		foreach($set as $index => $fighter):
			$setInfo[$index]['systemRosterID'] = $fighter['systemRosterID'];
			$setInfo[$index]['numTournaments'] = getNumTournamentAppearances($fighter['systemRosterID']);
			$setInfo[$index]['fullName'] =  getFighterNameSystem($fighter['systemRosterID'],'last');
			$setInfo[$index]['schoolName'] = getSchoolName($fighter['schoolID'],'full');
			$setInfo[$index]['HemaRatingsID'] =  getHemaRatingsID($fighter['systemRosterID']);
			$name = getFighterNameSystem($fighter['systemRosterID'],'array');
			?>

			<tr>
				<td><?=$name['lastName']?></td>
				<td><?=$name['firstName']?></td>
				<td><?=$setInfo[$index]['schoolName']?></td>
				<td><?=$setInfo[$index]['HemaRatingsID']?></td>
				<td class='text-right'>
					<?=$setInfo[$index]['numTournaments']?>&nbsp;&nbsp;&nbsp;
					<?=tournamentEntryTooltip($fighter['systemRosterID'])?>
				</td>
			</tr>

		<?php endforeach; ?>
		
	<!-- Options to deal with duplicate entry -->
		<tr>
			<td colspan='100%' class='text-right' style='border-bottom:1px solid black'>
				<?php distinctFightersButton($setNum, $setInfo) ?>
				<?php combineFightersButton($setNum, $setInfo) ?>
			</td>
		</tr>

	<?php endforeach ?>
	</table>

<?php
	
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function match_LastName_School($fighter){

	// Ignores fighters that have ' character in their name, because
	//  the query is not properly escaped. 
	// THIS NEEDS TO BE FIXED!!!!!
	if(strpos($fighter['lastName'],"'") !== false){return null;}

	$firstLet = substr($fighter['firstName'],0,1);

	$sql = "SELECT systemRosterID, firstName, lastName, schoolID
			FROM systemRoster
			WHERE systemRosterID != {$fighter['systemRosterID']}
			AND schoolID = {$fighter['schoolID']}
			AND lastName = '{$fighter['lastName']}'
			AND firstName LIKE '{$firstLet}%'
			AND systemRosterID NOT IN (
					SELECT rosterID1
					FROM systemRosterNotDuplicate
					WHERE rosterID2 = {$fighter['systemRosterID']})
			AND systemRosterID NOT IN (
					SELECT rosterID2
					FROM systemRosterNotDuplicate
					WHERE rosterID1 = {$fighter['systemRosterID']})";

	return mysqlQuery($sql, ASSOC);

}

/******************************************************************************/

?>

<style>
/* Tooltip container */
.tooltip {
    position: relative;
    display: inline-block;
    border-bottom: 1px dotted black; /* If you want dots under the hoverable text */
}

/* Tooltip text */
.tooltip .tooltiptext {
    visibility: hidden;
    width: 330px;
    background-color: black;
    color: #fff;
    text-align: left;
    padding: 5px 0;
    border-radius: 6px;
 	top: -5px;
    right: 105%; 

    /* Position the tooltip text - see examples below! */
    position: absolute;
    z-index: 0;
}

/* Show the tooltip text when you mouse over the tooltip container */
.tooltip:hover .tooltiptext {
    visibility: visible;
}
</style>

<?php

function tournamentEntryTooltip($systemRosterID){

	$sql = "SELECT tournamentID, eventID
			FROM eventTournamentRoster 
			INNER JOIN eventRoster USING(rosterID)
			WHERE systemRosterID = {$systemRosterID}
			ORDER BY eventID DESC";
	$allTournaments = mysqlQuery($sql, ASSOC);

	$oldEventID = null;
	$str = '';

	foreach($allTournaments as $entry){
		if($entry['eventID'] != $oldEventID){
			if($oldEventID != null){ $str .= "</ul>";}
			$oldEventID = $entry['eventID'];
			$str .= "<ul><strong>".getEventName($oldEventID)."</strong><BR>";
		}
		$str .= "<li>".getTournamentName($entry['tournamentID'])."</li>";
	}
	$str .= "</ul>";
	
	?>
	<div class="tooltip">?
  		<span class="tooltiptext"><?=$str?></span>
	</div>
	
	<?php

}

/******************************************************************************/

function combineFightersButton($setNum, $setInfo){
	?>

	<a class='button warning' name='formName' value='newNotDuplicate' 
		data-open='combineFightersBox<?=$setNum?>'>
		Combine Fighters
	</a>

	<div class='reveal medium' id='combineFightersBox<?=$setNum?>' data-reveal>
		<form method='POST'>
		<center><h4>- Combine Fighters-</h4></center>
	
		This will mark the combine the following fighters into a single fighter.
		<center><h5 class='red-text'>THIS CAN NOT BE UNDONE!</h5></center>
		If you do this wrong they can not be split into two people again.
		<hr>
		
		<center><strong>WHICH FIGHTER DO YOU WANT TO KEEP?</strong></center>
		<table>

		<tr>
		<?php foreach($setInfo as $index => $fighter): ?>
			
			<tr id='set<?=$setNum?>_<?=$index?>' class='combineFighersRow strike-through'>
				<td><input type='radio' name='combineInto' value='<?=$setInfo[$index]['systemRosterID']?>'
					onchange="strikeOutDuplicateFighters('set<?=$setNum?>_<?=$index?>','combineButton<?=$setNum?>');">
					<input type='hidden' name='rosterIDs[<?=$index?>]' value='<?=$setInfo[$index]['systemRosterID']?>'>
				</td>
				<td><?=$setInfo[$index]['fullName']?></td>
				<td><?=$setInfo[$index]['schoolName']?></td>
				<td><?=$setInfo[$index]['numTournaments']?></td>
			</tr>
			
		
		<?php endforeach ?>
		
		</table>
			<hr>
	
		<!-- Submit buttons -->
			<div class='grid-x grid-margin-x'>
				<button class='alert button small-6 cell no-bottom' id='combineButton<?=$setNum?>'
					name='formName' value='combineDuplicateFighters' disabled>
					Combine Fighters
				</button>
				<button class='secondary button small-6 cell no-bottom' data-close aria-label='Close modal' type='button'>
					Cancel
				</button>
			</div>
		</form>
		
		<!-- Reveal close button -->
			<button class='close-button' data-close aria-label='Close modal' type='button'>
				<span aria-hidden='true'>&times;</span>
		</button>
		
		</div>


<?php
}

/******************************************************************************/

function distinctFightersButton($setNum, $setInfo){
	?>

	<a class='button success' name='formName' value='newNotDuplicate' 
		data-open='distinctFightersBox<?=$setNum?>'>
		Two Distinct Fighters
	</a>

	<div class='reveal medium' id='distinctFightersBox<?=$setNum?>' data-reveal>
		<form method='POST'>
		<h5>- Comfirm Distinct Fighters-</h5>
	
		This will mark the following fighters as unique, and they will no longer 
		be flaged as posisble duplicates.
		<hr>
		<table>

	

		<?php foreach($setInfo as $index => $fighter): ?>
			<tr>
				<td>
					<input type='hidden' name='rosterIDs[<?=$index?>]' value='<?=$setInfo[$index]['systemRosterID']?>'>
				</td>
				<td><?=$setInfo[$index]['fullName']?></td>
				<td><?=$setInfo[$index]['schoolName']?></td>
				<td><?=$setInfo[$index]['numTournaments']?></td>
			</tr>


		<?php endforeach ?>
		</table>
			<hr>
	
		<!-- Submit buttons -->
			<div class='grid-x grid-margin-x'>
				<button class='success button small-6 cell no-bottom' name='formName' value='addNewDuplicateException'>
					Submit
				</button>
				<button class='secondary button small-6 cell no-bottom' data-close aria-label='Close modal' type='button'>
					Cancel
				</button>
			</div>
		</form>
		
		<!-- Reveal close button -->
			<button class='close-button' data-close aria-label='Close modal' type='button'>
				<span aria-hidden='true'>&times;</span>
		</button>
		
		</div>


<?php
}

/******************************************************************************/

function getNumTournamentAppearances($systemRosterID){
	$sql = "SELECT count(tableID) as numTournaments
			FROM eventTournamentRoster
			INNER JOIN eventRoster USING(rosterID)
			INNER JOIN systemRoster USING(systemRosterID)
			WHERE systemRosterID = {$systemRosterID}";
	return (int)mysqlQuery($sql, SINGLE, 'numTournaments');

}

/******************************************************************************/

function changeSearchButton($text,$value){
	$currentSearch = '';
	if(isset($_SESSION['duplicateNameSearchType'])){
		$currentSearch = $_SESSION['duplicateNameSearchType'];
	}

	if($currentSearch != $value){
		$class = 'hollow';
	}
?>
	<button class='button no-bottom <?=$class?>' name='searchType' value='<?=$value?>'>
		<?=$text?>
	</button>
<?php
}

/******************************************************************************/