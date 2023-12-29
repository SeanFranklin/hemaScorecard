<?php
/*******************************************************************************
	Fighter Management

	Withdraw fighters if they are injured and can no longer compete
	LOGIN:
		- ADMIN or higher required to access

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Fighter Penalties';

$createSortableDataTable[] = ['table-penalty-totals',100];
$createSortableDataTable[] = ['table-penalty-list',100];

include('includes/header.php');

$eventID = $_SESSION['eventID'];

if(ALLOW['EVENT_SCOREKEEP'] == false && ALLOW['VIEW_SETTINGS'] == false){
	pageError('user');
} else if($eventID == null){
	pageError('event');
} else {

	$eventPenalties = getEventPenaltyList($_SESSION['eventID']);

	$penaltyCounts = [];
	$penaltyCounts[0]['name'] = "<b>GRAND TOTAL</b>";

	foreach($eventPenalties as $p){

		@$penaltyCounts[$p['rosterID']][(int)$p['refType']]++;
		@$penaltyCounts[0][(int)$p['refType']]++;

		@$penaltyCounts[$p['rosterID']]['total']++;
		@$penaltyCounts[0]['total']++;

		if((int)$p['refType'] != 0){
			@$penaltyCounts[$p['rosterID']]['totalColor']++;
			@$penaltyCounts[0]['totalColor']++;
		}

		@$penaltyCounts[$p['rosterID']]['name'] = getFighterName($p['rosterID']);
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>


<ul class="tabs" data-tabs id="penalty-list-tabs">

	<li class="tabs-title is-active">
		<a data-tabs-target="panel-div-list">
			Full Penalty List
		</a>
	</li>

	<li class="tabs-title">
		<a data-tabs-target="panel-div-totals">
			Fighter Totals
		</a>
	</li>

</ul>

<div class="tabs-content" data-tabs-content="penalty-list-tabs">

<!-- Full list of penalties ----------------------------------------->

	<div class="tabs-panel is-active" id="panel-div-list">
	<div class='grid-x grid-margin-x'>

		<table  id='table-penalty-list' >
			<thead>
				<tr>
					<th>Last</th>
					<th>First</th>
					<th>School</th>
					<th>Tournament</th>
					<th>Group</th>
					<th>Opponent</th>
					<th>Color</th>
					<th>Offense</th>
					<th>Points</th>
				</tr>

			</thead>

			<tbody>
			<?php foreach($eventPenalties as $p):?>
				<tr>
					<td><?=$p['lastName']?></td>
					<td><?=$p['firstName']?></td>
					<td><?=$p['schoolFullName']?></td>
					<td><?=getTournamentName($p['tournamentID'])?></td>
					<td><?=$p['groupName']?></td>
					<td>vs <?=$p['first2']?> <?=$p['last2']?></td>
					<td><?=$p['card']?></td>
					<td><?=$p['action']?></td>
					<td>-<?=$p['scoreValue']?></td>
				</tr>
			<?php endforeach ?>
			</tbody>

		</table>

	</div>
	</div>


<!-- Total numbers summary ----------------------------------------->

	<div class="tabs-panel" id="panel-div-totals">
	<div class='grid-x grid-margin-x'>

		<div class='cell large-9'>
		<table id='table-penalty-totals'>

			<thead>
				<tr>
					<th>Name</th>
					<th>White</th>
					<th>Yellow</th>
					<th>Red</th>
					<th>Black</th>
					<th># [Color]</th>
					<th># [All]</th>
				</tr>
			</thead>

			<tbody>
			<?php foreach($penaltyCounts as $p):?>
				<tr>
					<td><?=$p['name']?></td>
					<td><?=@$p[0]?></td>
					<td><?=@$p[PENALTY_CARD_YELLOW]?></td>
					<td><?=@$p[PENALTY_CARD_RED]?></td>
					<td><?=@$p[PENALTY_CARD_BLACK]?></td>
					<td><?=@$p['totalColor']?></td>
					<td><?=@$p['total']?></td>
				</tr>
			<?php endforeach ?>
			</tbody>
		</table>
		</div>

	</div>
	</div>

</div>


<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
