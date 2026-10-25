<?php
/*******************************************************************************
	Import Participants

	Upload a CSV of participants, confirm the parsed rows, then add them to
	the event through the same path as the roster entry form.

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Import Participants';
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false || $_SESSION['isMetaEvent'] == true){
	pageError('user');
} else {

	if(isset($_SESSION['participantsImport'])){
		importConfirmation($_SESSION['participantsImport']);
	} else {
		importUploadForm();
	}

}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

function importUploadForm(){
	?>

	<div class='callout secondary'>

		<h4>Upload a CSV</h4>
		<p>One participant per line. Columns are matched by header name
			(<b>First Name</b>, <b>Last Name</b>, <b>School</b>, <b>Tournaments</b>).
			Without a header row they are read in that order.
			Schools are matched by short name, full name or abbreviation;
			tournaments by name, separated with semicolons. Both are optional.</p>

		<form method='POST' enctype='multipart/form-data'>
		<div class='input-group'>
			<span class='input-group-label'>File:</span>
			<input class='input-group-field' type='file' name='participantsCsv' accept='.csv,.txt' required>
			<div class='input-group-button'>
				<button class='button success no-bottom' name='formName' value='uploadParticipantsCsv'>
					Upload
				</button>
			</div>
		</div>
		</form>

		<a href='participantsEvent.php'>&larr; Back to the event roster</a>

	</div>

<?php }

/******************************************************************************/

function importConfirmation($import){
// Parsed rows parked in the session by importParticipantsParseCsv().

	$statusText = ['new' => 'New fighter', 'known' => 'Known fighter', 'entered' => 'Already in this event'];
	$numToImport = 0;
	foreach($import['rows'] as &$row){
		// Fighters already in the event are only skipped when they have no new tournaments
		$row['skipped'] = $row['status'] == 'entered' && $row['tournamentIDs'] == [];
		if($row['skipped'] == false){ $numToImport++; }
	}
	unset($row);
	?>

	<h4>Confirm import from <i><?=htmlspecialchars($import['fileName'])?></i></h4>

	<table class='hover' id='importPreview'>
	<thead><tr><th>Name</th><th>School</th><th>Tournaments</th><th>Status</th></tr></thead>

	<?php foreach($import['rows'] as $row): ?>
		<tr class='import-row <?=$row['skipped'] ? 'grey-text' : ''?>'>
			<td><?=htmlspecialchars($row['firstName'].' '.$row['lastName'])?></td>
			<td>
				<?=htmlspecialchars($row['schoolLabel'])?>
				<?php if($row['schoolFound'] == false && $row['schoolText'] != ''): ?>
					<div class='red-text'>School not found: <?=htmlspecialchars($row['schoolText'])?></div>
				<?php endif ?>
			</td>
			<td>
				<?php foreach($row['tournamentNames'] as $name): ?>
					<div class='tournament-box is-static'><?=$name?></div>
				<?php endforeach ?>
				<?php if($row['ignoredTournaments'] != []): ?>
					<div class='red-text'>Ignored: <?=htmlspecialchars(implode(', ', $row['ignoredTournaments']))?></div>
				<?php endif ?>
			</td>
			<td>
				<?=$statusText[$row['status']]?>
				<?php if($row['status'] == 'entered' && $row['skipped'] == false): ?>
					<div>Adds tournaments</div>
				<?php endif ?>
			</td>
		</tr>
	<?php endforeach ?>

	</table>

	<form method='POST'>
		<button class='button success' name='formName' value='confirmParticipantsImport'
			<?=$numToImport == 0 ? 'disabled' : ''?>>
			Import <?=$numToImport?> participants
		</button>
		<button class='button secondary hollow' name='formName' value='cancelParticipantsImport'>Cancel</button>
	</form>

<?php }

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
