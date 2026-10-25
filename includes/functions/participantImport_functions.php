<?php
/*************************************
	Function Library
	Participant Import Functions

	Functions relating to importing participants from a CSV file
**************************************/

/******************************************************************************/

function importParticipantsParseCsv($eventID, $file){
// Reads an uploaded CSV of participants, matches schools, tournaments and
// fighters by name, and parks the rows in the session for participantsImport.php
// to confirm. Nothing is written until importParticipantsCommit().

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
	$contents = (string)stream_get_contents($handle); // to number the lines
	rewind($handle);
	$lines = [];
	$spans = []; // byte range of each kept record in the file
	$start = ftell($handle);
	while(($data = fgetcsv($handle, 1000, ',')) !== false){
		$end = ftell($handle);
		$data = array_map('trim', $data);
		if(implode('', $data) != ''){ // else blank line
			$lines[] = $data;
			$spans[] = [$start, $end];
		}
		$start = $end;
	}

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
		'tournaments' => ['tournaments','tournament','events','event'],
	];
	$columns = [];
	foreach($lines[0] as $i => $header){
		foreach($aliases as $field => $names){
			if(in_array(strtolower($header), $names)){ $columns[$field] = $i; }
		}
	}
	if($columns != []){
		$headers = ['firstName' => null, 'lastName' => null, 'school' => null, 'tournaments' => null];
		foreach($columns as $field => $i){
			$headers[$field] = $lines[0][$i];
		}
		array_shift($lines);
		array_shift($spans);
	} else {
		$headers = null;
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
	$tournamentsByName = [];
	foreach($tournamentList as $tournamentID){
		$names = [getTournamentName($tournamentID, 'prefix'), getTournamentName($tournamentID, 'weapon')];
		foreach(array_unique(array_map('strtolower', $names)) as $name){
			$tournamentsByName[$name][] = (int)$tournamentID;
		}
	}

	// The first data record exactly as it appears in the file
	$firstLine = '';
	if($spans != []){
		fseek($handle, $spans[0][0]);
		$firstLine = (string)fread($handle, $spans[0][1] - $spans[0][0]);
		$firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
		$firstLine = rtrim($firstLine, "\r\n");
	}
	fclose($handle);

	$rows = [];
	foreach($lines as $k => $line){
		$cell = function($field) use ($line, $columns){
			return isset($columns[$field], $line[$columns[$field]]) ? $line[$columns[$field]] : '';
		};
		$row = [
			'line' => substr_count($contents, "\n", 0, $spans[$k][0]) + 1, // the header is line 1
			'firstName' => $cell('firstName'),
			'lastName' => $cell('lastName'),
			'schoolText' => $cell('school'),
			'schoolID' => SCHOOL_ID_UNKNOWN,
			'schoolLabel' => '*Unknown',
			'schoolFound' => false,
			'tournamentIDs' => [],
			'ignoredTournaments' => [],
			'matchType' => 'none',
			'matches' => [],
			'duplicateLines' => [],
			'needsLook' => false,
			'checked' => false,
			'choice' => null,
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

		$row['tournamentIDs'] = array_values(array_unique($tournamentIDs));

		$rows[] = $row;
	}
	importParticipantsMatchFighters($eventID, $rows);
	importParticipantsFlagDuplicates($rows);
	foreach($rows as &$row){
		$row['needsLook'] = importRowWarnings($row) != [];
	}
	unset($row);

	$_SESSION['participantsImport'] = [
		'fileName' => basename($file['name']),
		'headers' => $headers,
		'firstLine' => $firstLine,
		'rows' => $rows,
	];

}

/******************************************************************************/

function importParticipantsMatchFighters($eventID, &$rows, $indexes = null){
// Finds the Scorecard fighters each row could be, for the rows in $indexes or
// for every row, in one pass over systemRoster. The same name, ignoring case,
// is the fighter, or one of several to choose from. Failing that, up to three
// fighters with a near name (importNameDistance()) are offered.

	if($indexes === null){
		$indexes = array_keys($rows);
	}
	if($indexes == []){
		return;
	}

	$exact = [];     // lowercased 'first|last' => row indexes
	$pairs = [];     // 'k1|k2' from importNameKeys() of each name => row indexes
	$names = [];     // row index => [first, last], normalized
	$limits = [];    // row index => the largest distance that counts as near
	$exactIDs = [];  // row index => systemRosterIDs
	$near = [];      // row index => [[distance, lastName, firstName, systemRosterID], ...]
	foreach($indexes as $i){
		$key = strtolower(trim($rows[$i]['firstName'])).'|'.strtolower(trim($rows[$i]['lastName']));
		$exact[$key][] = $i;
		$exactIDs[$i] = [];
		$near[$i] = [];

		$first = importNormalizeName($rows[$i]['firstName']);
		$last = importNormalizeName($rows[$i]['lastName']);
		if($first == '' && $last == ''){
			continue; // No letters to compare, so nothing is near
		}
		$names[$i] = [$first, $last];
		$limits[$i] = strlen($first) + strlen($last) < 8 ? 1 : 2;
		foreach([[$first, $last], [$last, $first]] as $order){ // swapped names too
			foreach(importNameKeys($order[0]) as $k1){
				foreach(importNameKeys($order[1]) as $k2){
					$pairs[$k1.'|'.$k2][$i] = $i;
				}
			}
		}
	}

	// Streamed rather than buffered. Nothing else may query the connection
	// until the result is freed.
	$sql = "SELECT systemRosterID, firstName, lastName, schoolID FROM systemRoster";
	$result = mysqli_query($GLOBALS["___mysqli_ston"], $sql, MYSQLI_USE_RESULT);
	checkMySQL(); // As mysqlQuery() does, rather than call every row new
	while(($fighter = mysqli_fetch_row($result)) !== null){
		$systemRosterID = (int)$fighter[0];
		$firstName = (string)$fighter[1];
		$lastName = (string)$fighter[2];

		$key = strtolower(trim($firstName)).'|'.strtolower(trim($lastName));
		if(isset($exact[$key])){
			foreach($exact[$key] as $i){
				$exactIDs[$i][] = $systemRosterID;
			}
		}

		$first = importNormalizeName($firstName);
		$last = importNormalizeName($lastName);
		if($first == '' && $last == ''){
			continue;
		}
		$reached = [];
		foreach(importNameKeys($first) as $k1){
			foreach(importNameKeys($last) as $k2){
				if(isset($pairs[$k1.'|'.$k2])){
					$reached += $pairs[$k1.'|'.$k2];
				}
			}
		}
		foreach($reached as $i){
			$distance = importNameDistance($names[$i][0], $names[$i][1], $first, $last);
			if($distance <= $limits[$i]){
				$near[$i][] = [$distance, $lastName, $firstName, $systemRosterID];
			}
		}
	}
	mysqli_free_result($result);

	foreach($indexes as $i){
		$matchIDs = $exactIDs[$i];
		if(count($matchIDs) == 1){
			$rows[$i]['matchType'] = 'exact';
		} else if(count($matchIDs) > 1){
			$rows[$i]['matchType'] = 'ambiguous';
		} else if($near[$i] != []){
			$rows[$i]['matchType'] = 'near';
			usort($near[$i], function($a, $b){ // closest first
				if($a[0] != $b[0]){ return $a[0] - $b[0]; }
				return strcasecmp($a[1], $b[1]) ?: strcasecmp($a[2], $b[2]) ?: $a[3] - $b[3];
			});
			foreach(array_slice($near[$i], 0, 3) as $candidate){
				$matchIDs[] = $candidate[3];
			}
		} else {
			$rows[$i]['matchType'] = 'none';
		}
		$rows[$i]['matches'] = [];
		foreach($matchIDs as $systemRosterID){
			$rows[$i]['matches'][] = ['systemRosterID' => $systemRosterID];
		}
	}

	importParticipantsRefreshMatches($eventID, $rows);

	foreach($indexes as $i){
		if($rows[$i]['matchType'] == 'ambiguous'){
			usort($rows[$i]['matches'], function($a, $b){ // told apart by school
				return strcasecmp($a['schoolLabel'], $b['schoolLabel']) ?: $a['systemRosterID'] - $b['systemRosterID'];
			});
		}
	}

}

/******************************************************************************/

function importNormalizeName($name){
// A name as just the letters a-z, for near matches: lowercased, accents
// dropped, and spaces, hyphens and apostrophes removed. "O'Rourke" and
// "ORourke", or "Zoë" and "ZOE", come out the same.

	$name = (string)$name;
	if(ctype_alpha($name)){
		return strtolower($name); // Most names, quickly
	}

	static $letters = [
		'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'ā' => 'a', 'ă' => 'a', 'ą' => 'a',
		'æ' => 'ae',
		'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
		'ď' => 'd', 'đ' => 'd', 'ð' => 'd',
		'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ē' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ę' => 'e', 'ě' => 'e',
		'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g',
		'ĥ' => 'h', 'ħ' => 'h',
		'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ĩ' => 'i', 'ī' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i',
		'ĵ' => 'j', 'ķ' => 'k',
		'ĺ' => 'l', 'ļ' => 'l', 'ľ' => 'l', 'ŀ' => 'l', 'ł' => 'l',
		'ñ' => 'n', 'ń' => 'n', 'ņ' => 'n', 'ň' => 'n',
		'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ō' => 'o', 'ŏ' => 'o', 'ő' => 'o',
		'œ' => 'oe',
		'ŕ' => 'r', 'ŗ' => 'r', 'ř' => 'r',
		'ś' => 's', 'ŝ' => 's', 'ş' => 's', 'š' => 's', 'ș' => 's', 'ß' => 'ss',
		'ţ' => 't', 'ť' => 't', 'ŧ' => 't', 'ț' => 't', 'þ' => 'th',
		'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ũ' => 'u', 'ū' => 'u', 'ŭ' => 'u', 'ů' => 'u', 'ű' => 'u', 'ų' => 'u',
		'ŵ' => 'w', 'ý' => 'y', 'ÿ' => 'y', 'ŷ' => 'y',
		'ź' => 'z', 'ż' => 'z', 'ž' => 'z',
	];
	$name = strtr(mb_strtolower($name, 'UTF-8'), $letters);
	return preg_replace('/[^a-z]/', '', $name);

}

/******************************************************************************/

function importNameKeys($name){
// The first two and last two letters of a normalized name. Two names within a
// typo or two of each other nearly always share one of them.

	if($name == ''){
		return [''];
	}
	return array_unique([substr($name, 0, 2), substr($name, -2)]);

}

/******************************************************************************/

function importNameDistance($first, $last, $otherFirst, $otherLast){
// How many letters differ between two normalized names, the smaller of the
// names as given and with one pair swapped.

	return min(
		levenshtein($first, $otherFirst) + levenshtein($last, $otherLast),
		levenshtein($first, $otherLast) + levenshtein($last, $otherFirst)
	);

}

/******************************************************************************/

function importParticipantsFlagDuplicates(&$rows){
// Lists on each row the lines of the other rows with the same name, ignoring
// case and surrounding spaces.

	$linesByName = [];
	foreach($rows as $row){
		$key = strtolower(trim($row['firstName'])).'|'.strtolower(trim($row['lastName']));
		$linesByName[$key][] = $row['line'];
	}
	foreach($rows as &$row){
		$key = strtolower(trim($row['firstName'])).'|'.strtolower(trim($row['lastName']));
		$row['duplicateLines'] = array_values(array_diff($linesByName[$key], [$row['line']]));
	}
	unset($row);

}

/******************************************************************************/

function importRowWarnings($row){
// The reasons a row needs a look, as keys: similar, sameName, schoolNotFound,
// schoolDiffers, ignoredTournaments, capitals, lowercase, duplicate.

	$warnings = [];

	if($row['matchType'] == 'near'){
		$warnings[] = 'similar';
	}
	if($row['matchType'] == 'ambiguous'){
		$warnings[] = 'sameName';
	}
	if($row['schoolFound'] == false && $row['schoolText'] != ''){
		$warnings[] = 'schoolNotFound';
	}
	if(importRowOutcome($row, $row['choice'])['kind'] == 'schoolPrompt'){
		$warnings[] = 'schoolDiffers';
	}
	if($row['ignoredTournaments'] != []){
		$warnings[] = 'ignoredTournaments';
	}
	if($row['matchType'] == 'none' || $row['matchType'] == 'near'){
		$letters = preg_replace('/[^A-Za-z]/', '', $row['firstName'].$row['lastName']);
		if(strlen($letters) >= 2){
			if(ctype_upper($letters)){
				$warnings[] = 'capitals';
			} else if(ctype_lower($letters)){
				$warnings[] = 'lowercase';
			}
		}
	}
	if($row['duplicateLines'] != []){
		$warnings[] = 'duplicate';
	}

	return $warnings;

}

/******************************************************************************/

function importParticipantsRefreshMatches($eventID, &$rows){
// Reads each row's candidate fighters from Scorecard again: name, stored
// school, whether they are in this event, and the event tournaments they are
// entered in. The commit calls this too, in case someone entered them since.

	$eventID = (int)$eventID;

	$systemRosterIDs = [];
	foreach($rows as $row){
		foreach($row['matches'] as $match){
			$systemRosterIDs[] = $match['systemRosterID'];
		}
	}
	if($systemRosterIDs == []){
		return;
	}

	$idList = implode2int($systemRosterIDs);
	$sql = "SELECT sR.systemRosterID, sR.firstName, sR.lastName, sR.schoolID,
				COALESCE(sS.schoolShortName, sS.schoolFullName) AS schoolName, sS.schoolBranch, eR.rosterID
			FROM systemRoster AS sR
			LEFT JOIN systemSchools AS sS USING(schoolID)
			LEFT JOIN eventRoster AS eR
				ON eR.systemRosterID = sR.systemRosterID
				AND eR.eventID = {$eventID}
			WHERE sR.systemRosterID IN ({$idList})";
	$fighters = [];
	foreach((array)mysqlQuery($sql, ASSOC) as $fighter){
		$fighters[$fighter['systemRosterID']] = $fighter;
	}
	$entriesByFighter = (array)getEntriesByFighter(getEventTournaments($eventID));

	foreach($rows as &$row){
		$matches = [];
		foreach($row['matches'] as $match){
			if(isset($fighters[$match['systemRosterID']]) == false){
				continue; // No longer in Scorecard
			}
			$fighter = $fighters[$match['systemRosterID']];
			$schoolID = (int)$fighter['schoolID'];
			if($schoolID == 0){
				$schoolID = SCHOOL_ID_UNKNOWN; // Stored without a school
			}
			$schoolLabel = (string)$fighter['schoolName'];
			if($fighter['schoolBranch'] != ''){
				$schoolLabel .= ", ".$fighter['schoolBranch']; // As the school pickers show it
			}
			$rosterID = (int)$fighter['rosterID'];
			$matches[] = [
				'systemRosterID' => (int)$fighter['systemRosterID'],
				'firstName' => $fighter['firstName'],
				'lastName' => $fighter['lastName'],
				'schoolID' => $schoolID,
				'schoolLabel' => $schoolID == SCHOOL_ID_UNKNOWN ? '*Unknown' : $schoolLabel,
				'rosterID' => $rosterID,
				'tournamentIDs' => array_map('intval', (array)@$entriesByFighter[$rosterID]),
			];
		}
		$row['matches'] = $matches;
	}
	unset($row);

}

/******************************************************************************/

function importRowOutcome($row, $choice){
// What importing a row would do, given the organizer's fighter choice: null,
// 'new', or a systemRosterID. The kinds:
//   choose        an ambiguous or near row with no valid choice
//   new           a fighter added to Scorecard
//   known         an existing fighter, entered at their stored school
//   schoolPrompt  an existing fighter the file lists at another known school;
//                 the roster page asks which to use after importing
//   entered       already in the event, so only the tournaments they aren't in
//   nothing       already in the event, with nothing new
// 'tournamentIDs' are the ones to add. Nothing here changes a stored school.

	$outcome = [
		'kind' => 'choose',
		'systemRosterID' => 0,
		'rosterID' => 0,
		'schoolID' => (int)$row['schoolID'],
		'storedSchoolID' => 0,
		'tournamentIDs' => [],
		'noSchool' => false,
	];

	if($row['matchType'] == 'none' || ($row['matchType'] == 'near' && $choice === 'new')){
		$outcome['kind'] = 'new';
		$outcome['tournamentIDs'] = $row['tournamentIDs'];
		$outcome['noSchool'] = $row['schoolID'] == SCHOOL_ID_UNKNOWN;
		return $outcome;
	}

	// The fighter the row is: its exact match, or the candidate chosen
	$fighter = null;
	foreach($row['matches'] as $match){
		if($row['matchType'] == 'exact' || (is_int($choice) && $match['systemRosterID'] == $choice)){
			$fighter = $match;
			break;
		}
	}
	if($fighter === null){
		return $outcome;
	}

	$outcome['systemRosterID'] = $fighter['systemRosterID'];
	$outcome['rosterID'] = $fighter['rosterID'];
	$outcome['schoolID'] = $fighter['schoolID'];
	$outcome['storedSchoolID'] = $fighter['schoolID'];

	if($fighter['rosterID'] != 0){
		$outcome['tournamentIDs'] = array_values(array_diff($row['tournamentIDs'], $fighter['tournamentIDs']));
		$outcome['kind'] = $outcome['tournamentIDs'] == [] ? 'nothing' : 'entered';
		return $outcome;
	}

	$outcome['tournamentIDs'] = $row['tournamentIDs'];
	// A blank or unrecognised file school keeps the stored one, unprompted
	if($row['schoolFound'] && $row['schoolID'] != SCHOOL_ID_UNKNOWN && $row['schoolID'] != $fighter['schoolID']){
		$outcome['kind'] = 'schoolPrompt';
		$outcome['schoolID'] = (int)$row['schoolID'];
	} else {
		$outcome['kind'] = 'known';
		$outcome['noSchool'] = $fighter['schoolID'] == SCHOOL_ID_UNKNOWN;
	}

	return $outcome;

}

/******************************************************************************/

function importParticipantsRememberState($postedRows){
// Keeps each row's tick and fighter choice from the posted page, so they show
// again when the page comes back. A choice is kept only if the row offered it:
// 'new' on a near match, or one of the row's candidates. A row still waiting
// on a choice can't be ticked.

	if(isset($_SESSION['participantsImport']) == false){
		return;
	}

	foreach($_SESSION['participantsImport']['rows'] as $i => &$row){
		$posted = (array)@$postedRows[$i];
		$choice = @$posted['choice'];

		$row['checked'] = !empty($posted['checked']);
		$row['choice'] = null;
		if($choice === 'new' && $row['matchType'] == 'near'){
			$row['choice'] = 'new';
		} else if(is_scalar($choice)){
			foreach($row['matches'] as $match){
				if((string)$match['systemRosterID'] === (string)$choice){
					$row['choice'] = $match['systemRosterID'];
				}
			}
		}
		if(importRowOutcome($row, $row['choice'])['kind'] == 'choose'){
			$row['checked'] = false;
		}
	}
	unset($row);

}

/******************************************************************************/

function importParticipantsCommit($eventID, $postedRows, $confirmed){
// Writes the rows posted as ticked, each as importRowOutcome() says, after
// reading the fighters again. New fighters go through addEventParticipants()
// by name, which still catches anyone added to Scorecard since the upload;
// existing ones by ID at their stored school. A different school waits on the
// roster page's conflict callout. Without the final confirmation nothing is
// written and this returns false.

	$eventID = (int)$eventID;
	if($eventID == 0 || ALLOW['EVENT_MANAGEMENT'] == false || !isset($_SESSION['participantsImport'])){ return false; }

	if($confirmed == false){
		importParticipantsRememberState($postedRows);
		setAlert(USER_ERROR, "Tick \"I've checked these names, schools and tournaments\" before importing.");
		return false;
	}

	$rows = $_SESSION['participantsImport']['rows'];
	importParticipantsRefreshMatches($eventID, $rows);
	unset($_SESSION['participantsImport']);

	// Count what actually happened; school conflicts wait on the roster page's callout
	$numBefore = getNumEventRegistrations($eventID);
	$numConflictsBefore = count((array)@$_SESSION['rosterEntryConflicts']['alreadyExists']);

	$fighterList = [];
	$numTournamentsAdded = 0;
	foreach($rows as $i => $row){
		if(empty($postedRows[$i]['checked'])){
			continue;
		}
		$outcome = importRowOutcome($row, $row['choice']);

		// 'choose' and 'nothing' rows are left out
		switch($outcome['kind']){
			case 'new':
				$fighterList[] = [
					'firstName' => $row['firstName'],
					'lastName' => $row['lastName'],
					'schoolID' => $outcome['schoolID'],
					'tournamentIDs' => $outcome['tournamentIDs'],
				];
				break;
			case 'known':
				$fighterList[] = [
					'systemRosterID' => $outcome['systemRosterID'],
					'schoolID' => $outcome['schoolID'],
					'tournamentIDs' => $outcome['tournamentIDs'],
				];
				break;
			case 'schoolPrompt':
				// By ID, so two fighters sharing a name can't be confused
				$_SESSION['rosterEntryConflicts']['alreadyExists'][] = [
					'postData' => [
						'systemRosterID' => $outcome['systemRosterID'],
						'schoolID' => $outcome['schoolID'],
						'staffCompetency' => 0,
						'tournamentIDs' => $outcome['tournamentIDs'],
					],
					'queryData' => [
						'schoolID' => $outcome['storedSchoolID'],
						'systemRosterID' => $outcome['systemRosterID'],
					],
				];
				break;
			case 'entered':
				addFighterToTournaments($outcome['rosterID'], $outcome['tournamentIDs']);
				$numTournamentsAdded++;
				break;
		}
	}

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

	return true;

}

