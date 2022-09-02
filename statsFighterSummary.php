<?php
/*******************************************************************************
	Fighter Management
	
	Withdraw fighters if they are injured and can no longer compete
	LOGIN:
		- ADMIN or higher required to access
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Fighter Information';
$createSortableDataTable[] = ["fighterInfoTable",300];
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if(ALLOW['VIEW_MATCHES'] == false){
	displayAlert("Event is still upcoming<BR>Matches not yet released");
} else if($tournamentID == null){
	pageError('tournament');
} elseif($_SESSION['formatID'] != FORMAT_MATCH){
	displayAlert('This data can only be displayed for <em>Sparring Matches</em> type tournaments.');
} else {

	$rawData = getTournamentFightersWithExchangeNumbers($tournamentID);

	reset($rawData);
	$first_key = key($rawData);
	$tournamentName = getTournamentName($_SESSION['tournamentID']);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>	
	
<!-- Navigate pool sets -->

	<a onclick="$('#pageExplanation').toggle()"><em>What is this?</em></a>

	<div id='pageExplanation' class='callout success hidden'>

		This page is to show summaries of the number of certain types of exchanges each fighter has 
		been involved in over the course of [<strong><?=$tournamentName?></strong>].
		<BR>
		<strong>✓</strong> indicates the fighter delivered the intial hit (Deductive AB), or higher valued hit* (Full AB).
		<BR>
		<strong>✗</strong> indicates the fighter recieved the intial hit (Deductive AB), or landed the lower valued hit (Full AB).
		<BR><em>
			&#8195;* in Full Afterblow an exchange worth equal points (eg: 1-1) it is randomly assigned which fighter had the 'initial' hit. 
		</em>
	</div>


	<?php if($rawData != null): ?>
		<div class='grid-x grid-margin-x'>
		<div class='large-7 medium-10 cell'>

		<table id="fighterInfoTable" class="display" >
			<thead>
				<tr>
					<th>
						Name
					</th>

					<?php foreach($rawData[$first_key]['exchanges'] as $name => $data):?>
						<th>
							<?=$name?>
						</th>
					<?php endforeach ?>

					<?php foreach($rawData[$first_key]['points'] as $name => $data):?>
						<th  style='white-space: nowrap'> 
							<?=$name?>
						</th>
					<?php endforeach ?>

				</tr>

			</thead>

			<?php foreach($rawData as $rosterID => $person): ?>
				<tr>
					<td>
						<?=getFighterName($rosterID)?>
					</td>
				

					<?php foreach((array)$person['exchanges'] as $num):?>
						<td>
							<?=$num?>
						</td>
					<?php endforeach ?>

					<?php foreach((array)$person['points'] as $num):?>
						<td>
							<?=$num?>
						</td>
					<?php endforeach ?>

				</tr>

			<?php endforeach ?>
		</table>

		</div>
		</div>

	<?php else: ?>
		<?=displayAlert("No data found for this tournament.")?>

	<?php endif ?>
		



<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
