<?php
/*************************************
	Function Library
	Participant Import Functions

	Functions relating to importing participants from a CSV file
**************************************/

/******************************************************************************/

function importParticipantsParseCsv($eventID, $file){
// Reads an uploaded CSV of participants, matches schools and tournaments by
// name, and parks the rows in the session for participantsImport.php to
// confirm. Nothing is written until importParticipantsCommit().

	$eventID = (int)$eventID;
	if($eventID == 0 || ALLOW['EVENT_MANAGEMENT'] == false){ return; }

	if(@$file['error'] != UPLOAD_ERR_OK || $file['size'] == 0){
		setAlert(USER_ERROR, "No file was uploaded.");
		return;
	}
	if($file['size'] > 1000000){
		setAlert(USER_ERROR, "File size exceeds 1 MB.");
		return;
	}

	$handle = fopen($file['tmp_name'], 'r');
	$lines = [];
	while(($data = fgetcsv($handle, 1000, ',')) !== false){
		$data = array_map('trim', $data);
		if(implode('', $data) == ''){ continue; } // blank line
		$lines[] = $data;
	}
	fclose($handle);

	if($lines == []){
		setAlert(USER_ERROR, "The file has no rows.");
		return;
	}
	$lines[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $lines[0][0]); // UTF-8 BOM

	// Column positions from the header row if one is present, else fixed order.
	$aliases = [
		'firstName' => ['first name','firstname','first','given name'],
		'lastName' => ['last name','lastname','last','surname','family name'],
		'school' => ['school','club'],
		'tournaments' => ['tournaments','tournament'],
	];
	$columns = [];
	foreach($lines[0] as $i => $header){
		foreach($aliases as $field => $names){
			if(in_array(strtolower($header), $names)){ $columns[$field] = $i; }
		}
	}
	if($columns != []){
		array_shift($lines);
	} else {
		$columns = ['firstName' => 0, 'lastName' => 1, 'school' => 2, 'tournaments' => 3];
	}

	// Lookups for matching
	$schoolsByName = [];
	foreach(getSchoolListLong() as $school){
		$names = [$school['schoolShortName'], $school['schoolFullName'], $school['schoolAbbreviation'],
					$school['schoolShortName'].", ".$school['schoolBranch']];
		foreach($names as $name){
			if($name != ''){ $schoolsByName[strtolower($name)] = $school; }
		}
	}
	// Tournaments match by name in either display format, since the file may not
	// use the organizer's. A name shared by more than one tournament is ambiguous.
	$tournamentList = (array)getEventTournaments($eventID);
	$tournamentNames = getEventTournamentNames($tournamentList);
	$tournamentsByName = [];
	foreach($tournamentList as $tournamentID){
		$names = [getTournamentName($tournamentID, 'prefix'), getTournamentName($tournamentID, 'weapon')];
		foreach(array_unique(array_map('strtolower', $names)) as $name){
			$tournamentsByName[$name][] = (int)$tournamentID;
		}
	}
	$entriesByFighter = (array)getEntriesByFighter($tournamentList);

	$rows = [];
	foreach($lines as $line){
		$cell = function($field) use ($line, $columns){
			return isset($columns[$field], $line[$columns[$field]]) ? $line[$columns[$field]] : '';
		};
		$row = [
			'firstName' => $cell('firstName'),
			'lastName' => $cell('lastName'),
			'schoolText' => $cell('school'),
			'schoolID' => SCHOOL_ID_UNKNOWN,
			'schoolLabel' => '*Unknown',
			'schoolFound' => false,
			'tournamentIDs' => [],
			'tournamentNames' => [],
			'ignoredTournaments' => [],
			'status' => 'new',
		];
		if($row['firstName'] == '' && $row['lastName'] == ''){ continue; }

		if(isset($schoolsByName[strtolower($row['schoolText'])])){
			$school = $schoolsByName[strtolower($row['schoolText'])];
			$row['schoolID'] = (int)$school['schoolID'];
			$row['schoolLabel'] = $school['schoolShortName'];
			$row['schoolFound'] = true;
		}

		$tournamentIDs = [];
		foreach(explode(';', $cell('tournaments')) as $name){
			$name = trim($name);
			if($name == ''){ continue; }
			$matches = (array)@$tournamentsByName[strtolower($name)];
			if(count($matches) == 1){
				$tournamentIDs[] = $matches[0];
			} else if(count($matches) > 1){
				$row['ignoredTournaments'][] = $name." (matches ".count($matches)." tournaments)";
			} else {
				$row['ignoredTournaments'][] = $name;
			}
		}

		$match = getSystemRosterIDbyName($row['firstName'], $row['lastName']);
		if($match['systemRosterID'] != 0){
			$row['systemRosterID'] = (int)$match['systemRosterID'];
			$rosterID = getRosterIDbySystemID($eventID, $match['systemRosterID']);
			if($rosterID != 0){
				// Already entered, so the import only adds the tournaments they aren't in yet
				$row['status'] = 'entered';
				$tournamentIDs = array_diff($tournamentIDs, (array)@$entriesByFighter[$rosterID]);
			} else {
				$row['status'] = 'known';
			}
		}

		$row['tournamentIDs'] = array_values(array_unique($tournamentIDs));
		foreach($row['tournamentIDs'] as $tournamentID){
			$row['tournamentNames'][] = $tournamentNames[$tournamentID];
		}

		$rows[] = $row;
	}

	$_SESSION['participantsImport'] = ['fileName' => basename($file['name']), 'rows' => $rows];

}

/******************************************************************************/

function importParticipantsCommit($eventID){
// Adds the rows parked by importParticipantsParseCsv() through
// addEventParticipants(), so known fighters, duplicates and school conflicts
// are handled exactly as they are for the roster entry form. Fighters already
// in the event only get their new tournaments.

	$eventID = (int)$eventID;
	if($eventID == 0 || ALLOW['EVENT_MANAGEMENT'] == false || !isset($_SESSION['participantsImport'])){ return; }

	$fighterList = [];
	$numTournamentsAdded = 0;
	foreach($_SESSION['participantsImport']['rows'] as $row){
		if($row['status'] == 'entered'){
			$rosterID = getRosterIDbySystemID($eventID, $row['systemRosterID']);
			if($rosterID != 0 && $row['tournamentIDs'] != []){
				addFighterToTournaments($rosterID, $row['tournamentIDs']);
				$numTournamentsAdded++;
			}
			continue;
		}
		$fighterList[] = [
			'firstName' => $row['firstName'],
			'lastName' => $row['lastName'],
			'schoolID' => $row['schoolID'],
			'tournamentIDs' => $row['tournamentIDs'],
		];
	}
	unset($_SESSION['participantsImport']);

	// Count what actually happened; school conflicts wait on the roster page's callout
	$numBefore = getNumEventRegistrations($eventID);
	$numConflictsBefore = count((array)@$_SESSION['rosterEntryConflicts']['alreadyExists']);
	addEventParticipants($eventID, $fighterList);
	$numImported = getNumEventRegistrations($eventID) - $numBefore;
	$numConflicts = count((array)@$_SESSION['rosterEntryConflicts']['alreadyExists']) - $numConflictsBefore;

	$message = "{$numImported} participants imported.";
	if($numTournamentsAdded > 0){
		$message .= " Added tournaments for {$numTournamentsAdded} already in the event.";
	}
	if($numConflicts > 0){
		$message .= " {$numConflicts} need a school choice before they are added.";
	}
	setAlert(USER_ALERT, $message);

}

