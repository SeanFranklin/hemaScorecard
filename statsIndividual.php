<?php
/*******************************************************************************

		
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Individual Fighter History';
$createSortableDataTable[] = 'systemRosterIdTable';
include('includes/header.php');

if(ALLOW['SOFTWARE_ASSIST'] == false){
	pageError('user');
} else {

	$filt['systemRosterID'] 		= @(int)$_SESSION['statsIDs']['systemRosterID'];
	$filt['eventID'] 				= @(int)$_SESSION['statsIDs']['eventID'];
	$filt['tournamentWeaponID'] 	= @(int)$_SESSION['statsIDs']['tournamentWeaponID'];
	$filt['tournamentPrefixID'] 	= @(int)$_SESSION['statsIDs']['tournamentPrefixID'];
	$filt['tournamentGenderID']		= @(int)$_SESSION['statsIDs']['tournamentGenderID'];
	$filt['tournamentMaterialID'] 	= @(int)$_SESSION['statsIDs']['tournamentMaterialID'];
	$filt['tournamentRankingID'] 	= @(int)$_SESSION['statsIDs']['tournamentRankingID'];

	$validExchanges = ['clean','afterblow','double','noExchange','noQuality','penalty','winner','tie'];
	$exchStr = "'clean','afterblow','double','noExchange','noQuality','penalty','winner','tie'";

	$filt1 = "";
	echo "Filters Set: ";
	if($filt['systemRosterID'] != 0){
		$filt1 .= "systemRosterID = {$filt['systemRosterID']} AND ";
		echo getFighterNameSystem($filt['systemRosterID'])." | ";
	}

	if($filt['eventID'] != 0){
		$filt1 .= "eR.eventID = {$filt['eventID']} AND ";
		echo getEventName($filt['eventID'])." | ";
	}

	if($filt['tournamentWeaponID'] != 0){
		$filt1 .= "eT.doubleTypeID = 1 AND";//"eT.tournamentWeaponID = {$filt['tournamentWeaponID']} AND ";
		echo getTournamentAttributeName($filt['tournamentWeaponID'])." | ";
	}

	$filt2 = "exchangeType IN ({$exchStr})
			GROUP BY exchangeType
			ORDER BY exchangeType ASC";

	$sql = "SELECT exchangeType, count(*) AS numExchanges
			FROM eventExchanges AS eE
			INNER JOIN eventMatches USING(matchID)
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments AS eT USING(tournamentID)
			INNER JOIN eventRoster AS eR ON eR.rosterID = eE.scoringID
			WHERE 
			{$filt1}
			{$filt2}";
	$for = mysqlQuery($sql, KEY_SINGLES,'exchangeType','numExchanges');

	$sql = "SELECT exchangeType, count(*) AS numExchanges
			FROM eventExchanges AS eE
			INNER JOIN eventRoster AS eR ON eR.rosterID = eE.receivingID
			WHERE
			{$filt1}
			{$filt2}";
	$against = mysqlQuery($sql, KEY_SINGLES,'exchangeType','numExchanges');

// List of Filters ///////////////
	$sql = "SELECT systemRosterID, firstName, lastName, schoolFullName
			FROM systemRoster
			INNER JOIN systemSchools USING(schoolID)
			ORDER BY systemRosterID";
	$allFighters = mysqlQuery($sql, ASSOC);

	$sql = "SELECT eventID, CONCAT(eventName,' ',eventYear) AS fullName
			FROM systemEvents
			ORDER BY eventStartDate ASC";
	$eventList = mysqlQuery($sql, KEY_SINGLES,'eventID','fullName');

	$sql = "SELECT tournamentTypeID, tournamentType
			FROM systemTournaments
			WHERE tournamentTypeMeta = 'weapon'
			ORDER BY tournamentType ASC";
	$weaponList = mysqlQuery($sql, KEY_SINGLES,'tournamentTypeID','tournamentType');

	$sql = "SELECT tournamentTypeID, tournamentType
			FROM systemTournaments
			WHERE tournamentTypeMeta = 'prefix'
			ORDER BY tournamentType ASC";
	$prefixList = mysqlQuery($sql, KEY_SINGLES,'tournamentTypeID','tournamentType');

	$sql = "SELECT tournamentTypeID, tournamentType
			FROM systemTournaments
			WHERE tournamentTypeMeta = 'gender'
			ORDER BY tournamentType ASC";
	$genderList = mysqlQuery($sql, KEY_SINGLES,'tournamentTypeID','tournamentType');

	$sql = "SELECT tournamentTypeID, tournamentType
			FROM systemTournaments
			WHERE tournamentTypeMeta = 'material'
			ORDER BY tournamentType ASC";
	$materialList = mysqlQuery($sql, KEY_SINGLES,'tournamentTypeID','tournamentType');

	$sql = "SELECT tournamentRankingID, name
			FROM systemRankings
			ORDER BY name ASC";
	$rankingList = mysqlQuery($sql, KEY_SINGLES,'tournamentRankingID','name');

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>	
	
	

		<h4>Exchanges For: <?=getFighterNameSystem($filt['systemRosterID'])?></h4>

		<ol>
			<li>This is raw database data, with no filtering or sanity checks. It also doesn't account for all the different types of tournament formats someone might have attended.</li>
			<li>Doubles get randomly assigned a 'for' and 'against' due to the database structure. Add the two to get total number of doubles.</li>
			<li>I threw this together as fast as I could, and didn't double check any of my work.</li>
		</ol>

		<table>
			<tr>
				<th>Type</th>
				<th>For</th>
				<th>Against</th>
			</tr>
			<?php foreach($validExchanges as $exchName): ?>
				<tr>
					<td><?=$exchName?></td>
					<td><?=@$for[$exchName]?></td>
					<td><?=@$against[$exchName]?></td>
				</tr>
			<?php endforeach ?>
		</table>
		
		<hr>

	

	<!---------------------------------------------------------------->

	<style>
		.vert{
			writing-mode: vertical-rl;
			transform: rotate(180deg)
		}

	</style>

	<form method='POST'>

		<table>
			<tr>
				<th><span class='vert'>Fighter ID</span></th>
				<th><span class='vert'>Event ID</span></th>
				<th><span class='vert'>Weapon ID</span></th>
				<th><span class='vert'>Prefix ID</span></th>
				<th><span class='vert'>Gender ID</span></th>
				<th><span class='vert'>Material ID</span></th>
				<th><span class='vert'>Alogrithm ID</span></th>
			</tr>
			<tr>
				<td><input type='number' name='statsIDs[systemRosterID]' value='<?=$filt['systemRosterID']?>' ></td>
				<td><input type='number' name='statsIDs[eventID]' value='<?=$filt['eventID']?>' ></td>
				<td><input type='number' name='statsIDs[tournamentWeaponID]' value='<?=$filt['tournamentWeaponID']?>' ></td>
				<td><input type='number' name='statsIDs[tournamentPrefixID]' value='<?=$filt['tournamentPrefixID']?>' ></td>
				<td><input type='number' name='statsIDs[tournamentGenderID]' value='<?=$filt['tournamentGenderID']?>' ></td>
				<td><input type='number' name='statsIDs[tournamentMaterialID]' value='<?=$filt['tournamentMaterialID']?>' ></td>
				<td><input type='number' name='statsIDs[tournamentRankingID]' value='<?=$filt['tournamentRankingID']?>' ></td>
			</tr>
		</table>

		<button class='button' name='formName' value='statsFilterData'>
		Update FighterID
		</button>

	</form>

	<HR>

	<!---------------------------------------------------------------->


	<a onclick="toggle('fighter-list')">
		Find FighterID
	</a>
	<div class='hidden' id='fighter-list'>
	<em>School indicates the last school they were entered in a HEMA Scorecard event from</em>

	<table  id="systemRosterIdTable" class="display">

		<thead>
			<tr>
				<th>ID</th>
				<th>Last</th>
				<th>First</th>
				<th>School</th>
			</tr>
		</thead>

		<tbody>
			<?php foreach($allFighters as $fighter):?>
				<tr>
					<td><?=$fighter['systemRosterID']?></td>
					<td><?=$fighter['lastName']?></td>
					<td><?=$fighter['firstName']?></td>
					<td><?=$fighter['schoolFullName']?></td>
				</tr>
			<?php endforeach?>
		</tbody>
	
	</table>
	</div>

	<!---------------------------------------------------------------->

	<?=displayList('EventID',$eventList);?>
	<?=displayList('WeaponID',$weaponList);?>
	<?=displayList('PrefixID',$prefixList);?>
	<?=displayList('GenderID',$genderList);?>
	<?=displayList('MaterialID',$materialList);?>
	<?=displayList('AlgorithmID',$rankingList);?>

<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayList($name, $data){

?>
<HR>
	<a onclick="toggle('<?=$name?>-list')">
		Find <?=$name?>
	</a>
	<div class='hidden' id='<?=$name?>-list'>
		<table>
			<?php foreach($data as $id => $name):?>
				<tr><td><?=$id?></td><td><?=$name?></td></tr>
			<?php endforeach?>
		</table>
	</div>
<?php
}


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
