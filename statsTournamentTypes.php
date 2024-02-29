<?php
/*******************************************************************************
	Sales Page

	Information about the software and why people should use it
	LOGIN: N/A
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Tournament Types";
$createSortableDataTable[] = ['all-tournament-list',100];
include('includes/header.php');
{

	$sql = "SELECT tournamentID, eventID, eventName, eventYear, eventStartDate, tournamentWeaponID AS weaponID,
				tournamentPrefixID AS prefixID, tournamentGenderID AS genderID,
				tournamentMaterialID AS materialID
			FROM eventTournaments
			INNER JOIN systemEvents USING(eventID)
			WHERE eventID != 2
			AND formatID < 4
			AND isArchived = 1
			ORDER BY eventStartDate DESC";
	$allEvents = (array)mysqlQuery($sql, ASSOC);

	$sql = "SELECT tournamentTypeID, tournamentType
			FROM systemTournaments";
	$types = (array)mysqlQuery($sql, KEY_SINGLES, 'tournamentTypeID', 'tournamentType');

	$display = [];
	$typeList['weapon'] = [];
	$typeList['prefix'] = [];
	$typeList['material'] = [];
	$typeList['gender'] = [];


	foreach($allEvents as $e){
		$tmp = [];
		$tmp['date'] = $e['eventStartDate'];
		$tmp['eventID'] = (int)$e['eventID'];
		$tmp['eventName'] = $e['eventName']." ".$e['eventYear'];
		$tmp['prefix'] = $types[$e['prefixID']];
		$tmp['gender'] = $types[$e['genderID']];
		$tmp['material'] = $types[$e['materialID']];
		$tmp['weapon'] = $types[$e['weaponID']];
		$display[] = $tmp;

		@$typeList['weapon'][$e['weaponID']]++;
		@$typeList['prefix'][$e['prefixID']]++;
		@$typeList['material'][$e['materialID']]++;
		@$typeList['gender'][$e['genderID']]++;

	}

	arsort($typeList['weapon']);
	arsort($typeList['prefix']);
	arsort($typeList['material']);
	arsort($typeList['gender']);


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>


<!-- Tabs ---------------------------------------------------------->
	<ul class="tabs" data-tabs id="all-tournament-tabs">


		<li class="tabs-title is-active">
			<a data-tabs-target="panel-tournaments" href="#change-recent">
				All Tournaments
			</a>
		</li>


		<li class="tabs-title">
			<a data-tabs-target="panel-types" href="#change-all">
				Tournament Type List
			</a>
		</li>

	</ul>



<!-- Tabs ---------------------------------------------------------->
	<div class="tabs-content" data-tabs-content="all-tournament-tabs">

		<div class="tabs-panel is-active" id="panel-tournaments">
			<table id='all-tournament-list'>
				<thead>
					<tr>
						<th>Date</th>
						<th>Event</th>
						<th>Weapon</th>
						<th>Div</th>
						<th>Gender</th>
						<th>Material</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($display as $t): ?>
					<tr>
						<td style='white-space: nowrap;'><?=$t['date']?></td>
						<td><a href="infoSummary.php?e=<?=$t['eventID']?>"><?=$t['eventName']?></td>
						<td><?=$t['weapon']?></td>
						<td><?=$t['prefix']?></td>
						<td><?=$t['gender']?></td>
						<td><?=$t['material']?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		</div>

		<div class="tabs-panel" id="panel-types">

			<div class='grid-x grid-margin-x'>
				<?=listTournamentTypes($typeList['material'], $types, 'Material')?>
				<?=listTournamentTypes($typeList['gender'], $types, 'Gender')?>
				<?=listTournamentTypes($typeList['prefix'], $types, 'Divisioin')?>
				<?=listTournamentTypes($typeList['weapon'], $types, 'Weapon')?>

			</div>


		</div>

	</div>



<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/


function listTournamentTypes($data, $types, $title){
?>

	<div class='cell large-3 medium-6'>
		<table class='data_table special-table'>
		<tr>
			<th><?=$title?></th>
			<th>#</th>
		</tr>
		<? foreach($data as $id => $num):?>
			<tr>
				<td><?=$types[$id]?></td>
				<td style='text-align:right;font-family: monospace;font-size:1.2em'><?=$num?></td>
			</tr>
		<?php endforeach ?>
		</table>
	</div>

<?php
}

/******************************************************************************/

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////