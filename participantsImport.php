<?php
/*******************************************************************************
	Import Participants

	Upload a CSV of participants, confirm the parsed rows, then add them to
	the event through the same path as the roster entry form.

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Import Participants';
$jsIncludes[] = 'roster_management_scripts.js';
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
// Parsed rows parked in the session by importParticipantsParseCsv(). Each row
// to import is ticked by hand, and Import waits for the final confirmation.

	$tournamentNames = getEventTournamentNames((array)getEventTournaments($_SESSION['eventID']));
	$numChecked = 0;
	foreach($import['rows'] as $row){
		$kind = importRowOutcome($row, $row['choice'])['kind'];
		if($row['checked'] && $kind != 'nothing' && $kind != 'choose'){ $numChecked++; }
	}
	?>

	<h4>Confirm import from <i><?=htmlspecialchars($import['fileName'], ENT_QUOTES | ENT_SUBSTITUTE)?></i></h4>

	<form method='POST' id='importForm'>

	<div id='importPreview'>

	<?php importReadAs($import); ?>

	<?php
	$sections = ['importReady' => ['Ready to import', []], 'importReview' => ['Needs a look', []]];
	foreach($import['rows'] as $index => $row){
		$sections[$row['needsLook'] ? 'importReview' : 'importReady'][1][$index] = $row;
	}
	foreach($sections as $id => $section){
		importSection($id, $section[0], $section[1], $tournamentNames);
	} ?>

	</div>

	<?php importSummary(count($import['rows'])); ?>

	<label>
		<input type='checkbox' id='importConfirmed' name='importConfirmed' value='1'>
		I've checked these names, schools and tournaments against my registrations
	</label>

	<button class='button success' name='formName' value='confirmParticipantsImport' disabled>
		Import <?=$numChecked?> participants
	</button>
	<button class='button secondary hollow' name='formName' value='cancelParticipantsImport'>Cancel</button>

	</form>

	<?php importEditorTemplate();

	// The row editor's schools and tournament chips, as on the roster page
	$config = [
		'schools' => rosterSchoolOptions(getSchoolList()),
		'tournamentGroups' => rosterTournamentGroups(array_keys($tournamentNames)),
		// Same threshold as the roster page
		'manyTournaments' => count($tournamentNames) > 8,
	];
	?>
	<script>
		window.addEventListener('DOMContentLoaded', function(){
			var config = <?=json_encode($config, JSON_FLAGS_INLINE)?>;
			ImportReview.init(config);
		});
	</script>

<?php }

/******************************************************************************/

function importEditorTemplate(){
// The row editor, opened under a row by ImportReview. Its Save posts the whole
// form, so the other rows' ticks and choices are kept, and edits only the
// import row (importParticipantsEditRow()).
	?>

	<template id='importEditorTemplate'>
		<tr class='roster-editor'>
			<td colspan='100%'>
				<input type='hidden' class='edit-index' form='importForm' name='importEdit[index]' value=''>
				<?php rosterEditorFields('importForm', 'importEdit', 'saveImportRow') ?>
			</td>
		</tr>
	</template>

<?php }

/******************************************************************************/

function importSummary($numRows){
// What the import will write, counted by the page as rows are ticked.

	?>

	<div class='callout secondary' id='importSummary' data-total='<?=(int)$numRows?>'>
		<h5 id='importSummaryHeading'>You're importing 0 of <?=(int)$numRows?> rows</h5>
		<ul>
			<li data-count='known'><span class='count'>0</span> already in Scorecard</li>
			<li data-count='new'><span class='count'>0</span> brand-new fighters added to the shared Scorecard database<div id='importNewNames' hidden></div></li>
			<li data-count='entered'><span class='count'>0</span> already in this event, getting new tournaments</li>
			<li data-count='noSchool'><span class='count'>0</span> entered with no school (*Unknown)</li>
			<li data-count='schoolPrompt'><span class='count'>0</span> will ask which school to use after importing</li>
			<li data-count='leftOut'><span class='count'><?=(int)$numRows?></span> rows left out</li>
		</ul>
	</div>

<?php }

/******************************************************************************/

function importSection($id, $heading, $rows, $tournamentNames){
// A table of rows under its heading; no rows, no section.

	if($rows == []){ return; }
	?>

	<div id='<?=$id?>'>
	<h5><?=$heading?> (<?=count($rows)?>)</h5>
	<table class='hover'>
	<thead><tr><th></th><th>Name</th><th>School</th><th>Tournaments</th><th>Status</th></tr></thead>

	<?php foreach($rows as $index => $row){
		importRow($index, $row, $tournamentNames);
	} ?>

	</table>
	</div>

<?php }

/******************************************************************************/

function importWarning($text){
	echo "<div class='import-warning red-text'>".htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE)."</div>";
}

/******************************************************************************/

function importFixed($text){
	echo "<div class='import-fixed success-text'>".htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE)."</div>";
}

/******************************************************************************/

function importAlsoOnLines($lines){
// "Also on line 4 of the file", "lines 2 and 4", "lines 2, 4 and 7"

	sort($lines);
	if(count($lines) == 1){
		return "Also on line {$lines[0]} of the file";
	}
	$last = array_pop($lines);
	return "Also on lines ".implode(', ', $lines)." and {$last} of the file";

}

/******************************************************************************/

function importRow($index, $row, $tournamentNames){
// One row of the confirmation table, with its tick box unless there is
// nothing to import. data-outcome is the outcome before any fighter choice.
// A row offering fighter choices keeps its box, disabled until the choice
// leaves something to import.

	$outcome = importRowOutcome($row, $row['choice']);
	$unchosen = importRowOutcome($row, null);
	$tickable = $outcome['kind'] != 'choose' && $outcome['kind'] != 'nothing';
	$name = $row['firstName'].' '.$row['lastName'];
	$status = importStatusText($outcome['kind']);
	$warnings = importRowWarnings($row);

	// The school the row will be entered with: an existing fighter keeps theirs
	$schoolLabel = $row['schoolLabel'];
	$storedLabel = '';
	foreach($row['matches'] as $match){
		if($match['systemRosterID'] == $outcome['systemRosterID']){
			$storedLabel = $match['schoolLabel'];
			if($outcome['kind'] == 'known' || $outcome['kind'] == 'entered'){
				$schoolLabel = $storedLabel;
			}
		}
	}

	// What editing changed, against the row as it was before its first edit
	$fixed = [];
	if(isset($row['original'])){
		$original = $row['original'];
		if($original['name'] !== $name){
			$fixed['name'] = "Fixed: was {$original['name']}";
		}
		if($original['schoolLabel'] !== $row['schoolLabel']){
			$fixed['school'] = "Fixed: school was {$original['schoolLabel']}";
		}
		$now = [];
		foreach($row['tournamentIDs'] as $tournamentID){
			$now[] = (string)@$tournamentNames[$tournamentID];
		}
		$was = $original['tournamentNames'];
		sort($now, SORT_STRING);
		sort($was, SORT_STRING);
		if($now !== $was){
			$fixed['tournaments'] = "Fixed: tournaments were "
				.($was == [] ? 'none' : implode(', ', $original['tournamentNames']));
		}
	}
	?>

	<tr class='import-row <?=$unchosen['kind'] == 'nothing' ? 'grey-text' : ''?>' id='importRow<?=$index?>'
		data-index='<?=$index?>'
		data-first-name='<?=htmlspecialchars($row['firstName'], ENT_QUOTES | ENT_SUBSTITUTE)?>'
		data-last-name='<?=htmlspecialchars($row['lastName'], ENT_QUOTES | ENT_SUBSTITUTE)?>'
		data-name='<?=htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE)?>'
		data-school-id='<?=(int)$row['schoolID']?>'
		data-tournament-ids='<?=implode(',', $row['tournamentIDs'])?>'
		data-outcome='<?=$unchosen['kind']?>'
		data-no-school='<?=$unchosen['noSchool'] ? 1 : 0?>'>
		<td class='import-tick'>
			<?php if($unchosen['kind'] != 'nothing'): ?>
				<input type='checkbox' class='import-check' name='importRows[<?=$index?>][checked]' value='1'
					<?=$row['checked'] && $tickable ? 'checked' : ''?> <?=$tickable ? '' : 'disabled'?>>
			<?php endif ?>
		</td>
		<td class='import-name'>
			<span class='import-name-text'><?=htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE)?></span>
			<?php if(isset($fixed['name'])){ importFixed($fixed['name']); } ?>
			<?php foreach($warnings as $warning){
				if($warning == 'similar'){ importWarning('Similar fighter already in Scorecard'); }
				if($warning == 'sameName'){ importWarning('More than one fighter in Scorecard has this name'); }
				if($warning == 'capitals'){ importWarning('Name is in all capitals'); }
				if($warning == 'lowercase'){ importWarning('Name is in all lowercase'); }
				if($warning == 'duplicate'){ importWarning(importAlsoOnLines($row['duplicateLines'])); }
			} ?>
			<?php importChoices($index, $row); ?>
		</td>
		<td class='import-school'>
			<span class='import-school-text'><?=htmlspecialchars($schoolLabel, ENT_QUOTES | ENT_SUBSTITUTE)?></span>
			<?php if(isset($fixed['school'])){ importFixed($fixed['school']); } ?>
			<?php foreach($warnings as $warning){
				if($warning == 'schoolNotFound'){ importWarning('School not found: "'.$row['schoolText'].'"'); }
				if($warning == 'schoolDiffers'){
					importWarning("Scorecard has them at {$storedLabel}; you'll be asked which to use after importing");
				}
			} ?>
		</td>
		<td class='import-tournaments'>
			<?php foreach($row['tournamentIDs'] as $tournamentID): ?>
				<div class='tournament-box is-static'><?=htmlspecialchars((string)@$tournamentNames[$tournamentID], ENT_QUOTES | ENT_SUBSTITUTE)?></div>
			<?php endforeach ?>
			<?php if(isset($fixed['tournaments'])){ importFixed($fixed['tournaments']); } ?>
			<?php if(in_array('ignoredTournaments', $warnings)){
				importWarning('Ignored: '.implode(', ', $row['ignoredTournaments']));
			} ?>
		</td>
		<td class='import-status'><?=$outcome['kind'] == 'new' ? "<b>{$status}</b>" : $status?></td>
	</tr>

<?php }

/******************************************************************************/

function importStatusText($kind){
// The status column's text for an importRowOutcome() kind.

	$statusText = [
		'choose' => 'Choose a fighter',
		'new' => 'New to Scorecard',
		'known' => 'In Scorecard',
		'schoolPrompt' => 'In Scorecard',
		'entered' => 'Already in this event, adds tournaments',
		'nothing' => 'Already in this event, nothing to add',
	];
	return $statusText[$kind];

}

/******************************************************************************/

function importChoices($index, $row){
// The fighters an ambiguous or near row could be, as radios. A near row can
// also be a new fighter; an ambiguous one can't. Each radio carries what
// choosing it does, for the page to show.

	if($row['matchType'] != 'ambiguous' && $row['matchType'] != 'near'){ return; }

	$choices = [];
	foreach($row['matches'] as $match){
		$label = "Use <b>".htmlspecialchars($match['firstName'].' '.$match['lastName'], ENT_QUOTES | ENT_SUBSTITUTE)."</b>"
			." (".htmlspecialchars($match['schoolLabel'], ENT_QUOTES | ENT_SUBSTITUTE).")";
		if($match['rosterID'] != 0){
			$label .= " (already in this event)";
		}
		$choices[] = [$match['systemRosterID'], $label];
	}
	if($row['matchType'] == 'near'){
		$choices[] = ['new', 'Add as a new fighter'];
	}
	?>

	<div class='import-choices'>
		<?php foreach($choices as $choice):
			list($value, $label) = $choice;
			$outcome = importRowOutcome($row, $value); ?>
			<label>
				<input type='radio' class='import-choice' name='importRows[<?=$index?>][choice]' value='<?=$value?>'
					data-outcome='<?=$outcome['kind']?>'
					data-no-school='<?=$outcome['noSchool'] ? 1 : 0?>'
					data-status='<?=htmlspecialchars(importStatusText($outcome['kind']), ENT_QUOTES | ENT_SUBSTITUTE)?>'
					<?=$row['choice'] === $value ? 'checked' : ''?>>
				<?=$label?>
			</label>
		<?php endforeach ?>
	</div>

<?php }

/******************************************************************************/

function importReadAs($import){
// Says which file column filled each field, so a misread header is noticed.

	$fields = ['firstName' => 'First Name', 'lastName' => 'Last Name', 'school' => 'School', 'tournaments' => 'Tournaments'];
	?>

	<div class='callout secondary' id='importReadAs'>
		<?php if($import['headers'] === null): ?>
			<p>No header row, so columns were read in order: <?=implode(', ', $fields)?></p>
		<?php else: ?>
			<p>How your file was read:
			<?php $pairs = [];
			foreach($fields as $field => $label){
				$header = $import['headers'][$field];
				$pairs[] = $label.' &larr; '.($header === null ? 'not in file' : htmlspecialchars($header, ENT_QUOTES | ENT_SUBSTITUTE));
			}
			echo implode(', ', $pairs); ?></p>
		<?php endif ?>
		<p>First line of data: <code id='importFirstLine'><?=htmlspecialchars($import['firstLine'], ENT_QUOTES | ENT_SUBSTITUTE)?></code></p>
	</div>

<?php }

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
