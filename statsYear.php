<?php
/*******************************************************************************


*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Year In Review';
$hidePageTitle = true;
$jsIncludes[] = 'stats_scripts.js';
include('includes/header.php');

define('FIRST_YEAR', 2015);

{
	$currentYear = (int)date("Y");
	if(isset($_SESSION['stats']['year']) == true){
		$year = (int)$_SESSION['stats']['year'];
	} else {
		$year = $currentYear;
	}

	if($year != 0){
		$sql = "SELECT eventID
				FROM systemEvents
				WHERE eventYear = {$year}
				AND isMetaEvent = 0
				AND eventName NOT LIKE '%=%'";
		$eventList = (array)mysqlQuery($sql, SINGLES);
		$eventListStr = "eventID IN (".implode2int($eventList).")";
		$eventListStrRaw = implode2int($eventList);
	} else {
		$eventListStr = "eventID IS NOT NULL";
	}


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>	

	<div class='grid-x grid-margin-x'>

	<div class='cell callout text-center success'><h3><?=$year?> - Year In Review</h3></div>

	<div class='cell callout alert'>
		<h3 class='text-center red-text'>Warning!</h3>
		This information is what event organizers & match table staff have entered into the database. It's accuracy/reliability will reflect their commitment to data integrity. Tournament registrations may be slightly inflated for some events if the organizer did not remove fighters dropping prior to the tournament, etc.
	</div>
	
	<?=yearlySummaryCountries(	$eventListStr,'teal')?>
	<?=yearlySummaryDates(		$eventListStr,'crimson')?>
	<?=yearlySummaryExchanges(	$eventListStr,'gray')?>
	<?=yearlySummaryMatches(	$eventListStr,'aqua')?>
	<?=yearlySummaryTournaments($eventListStr,'salmon')?>
	<?=yearlySummaryUrg(		$eventListStr,'Khaki')?>
	<?=yearlySummaryClubs(		$eventListStr,'deepskyblue')?>
	<?=yearlySummaryIndividual(	$eventListStr,'violet')?>
	
	<?=yearlySummarySoftware($year)?>

	
	<div class="cell"><HR></div>
	<div class="cell medium-6">
		<form method="POST">
			<div class='input-group'>
				<input class='hidden' name='formName' value='statsYear'>
				<span class='input-group-label'>Select Year</span>
				<select class='input-group-field' name='stats[year]'>
					<?php for($i = $currentYear; $i >=  FIRST_YEAR; $i--):?>
						<option <?=optionValue($i, $year)?>><?=$i?></option>
					<?php endfor?>
					<option <?=optionValue(0, $year)?>>Everything (very slow to load)</option>
				</select>
				<div class="input-group-button">
					<input type="submit" class="button" value="Change Year">
				</div>
			</div>
		</form>
	</div>

	</div>
<?php
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/******************************************************************************/


/******************************************************************************/

function yearlySummaryExchanges($eventListStr, $color){

	$validExchanges = "'clean','afterblow','double','noExchange','scored'";

	$sql = "SELECT COUNT(*) AS numExchanges
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
			WHERE {$eventListStr}";
	$numExchanges = mysqlQuery($sql, SINGLE, 'numExchanges');


// By Fighter ------------------------------------------------------------------

	$sql = "SELECT systemRosterID, COUNT(*) AS numExchanges
			FROM eventExchanges
				INNER JOIN eventMatches AS eM USING(matchID)
				INNER JOIN eventRoster AS eR1 ON eM.fighter1ID = eR1.rosterID
			WHERE {$eventListStr}
				AND systemRosterID IS NOT NULL
				AND exchangeType IN ($validExchanges)
			GROUP BY systemRosterID
			ORDER BY numExchanges DESC
			LIMIT 60";
	$exchByFighter1 = (array)mysqlQuery($sql, ASSOC);

	$sql = "SELECT systemRosterID, COUNT(*) AS numExchanges
			FROM eventExchanges
				INNER JOIN eventMatches AS eM USING(matchID)
				INNER JOIN eventRoster AS eR1 ON eM.fighter2ID = eR1.rosterID
			WHERE {$eventListStr}
				AND systemRosterID IS NOT NULL
				AND exchangeType IN ($validExchanges)
			GROUP BY systemRosterID
			ORDER BY numExchanges DESC
			LIMIT 60";
	$exchByFighter2 = (array)mysqlQuery($sql, ASSOC);

	$exchByFighter = [];
	foreach($exchByFighter1 as $e){
		$exchByFighter[$e['systemRosterID']] = $e['numExchanges'];
	}

	foreach($exchByFighter2 as $e){
		@$exchByFighter[$e['systemRosterID']] += $e['numExchanges'];
	}

	arsort($exchByFighter);

	$plotExchByFighter = [];
	$i = 0;
	foreach($exchByFighter as $systemRosterID => $numExchanges){
		$plotExchByFighter[$i]['value'] = $numExchanges;
		$plotExchByFighter[$i]['name'] = getFighterNameSystem($systemRosterID);
		$i++;
		if($i >= 30){
			break;
		}
	}


// By School ------------------------------------------------------------------

	$sql = "SELECT eR.schoolID, COUNT(*) AS numExchanges
			FROM eventExchanges
				INNER JOIN eventMatches AS eM USING(matchID)
				INNER JOIN eventRoster AS eR ON eM.fighter1ID = eR.rosterID
			WHERE {$eventListStr}
				AND eR.schoolID IS NOT NULL
				AND eR.schoolID != 1
				AND eR.schoolID != 2
				AND exchangeType IN ($validExchanges)
			GROUP BY eR.schoolID
			ORDER BY numExchanges DESC
			LIMIT 60";
	$exchBySchool1 = (array)mysqlQuery($sql, ASSOC);

	$sql = "SELECT eR.schoolID, COUNT(*) AS numExchanges
			FROM eventExchanges
				INNER JOIN eventMatches AS eM USING(matchID)
				INNER JOIN eventRoster AS eR ON eM.fighter2ID = eR.rosterID
			WHERE {$eventListStr}
				AND eR.schoolID IS NOT NULL
				AND eR.schoolID != 1
				AND eR.schoolID != 2
				AND exchangeType IN ($validExchanges)
			GROUP BY eR.schoolID
			ORDER BY numExchanges DESC
			LIMIT 60";
	$exchBySchool2 = (array)mysqlQuery($sql, ASSOC);

	$exchBySchool = [];
	foreach($exchBySchool1 as $e){
		$exchBySchool[$e['schoolID']] = $e['numExchanges'];
	}

	foreach($exchBySchool2 as $e){
		@$exchBySchool[$e['schoolID']] += $e['numExchanges'];
	}

	arsort($exchBySchool);

	$plotExchBySchool = [];
	$i = 0;
	foreach($exchBySchool as $schoolID => $numExchanges){
		$plotExchBySchool[$i]['value'] = $numExchanges;
		$plotExchBySchool[$i]['name'] = getSchoolName($schoolID);
		$i++;
		if($i >= 30){
			break;
		}
	}


// By Event ------------------------------------------------------------------

	$sql = "SELECT eventName as name, COUNT(*) AS value
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
				INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
				AND exchangeType IN ($validExchanges)
			GROUP BY eventName
			ORDER BY value DESC";
	$plotExchByEvent = (array)mysqlQuery($sql, ASSOC);

?>

	<div class='medium-6 cell callout'>

		<div class='yearly-summary-title'>-- Exchanges --</div>

		<p class='yearly-summary-text'>Total # of Exchanges: <b><?=number_format($numExchanges)?></b></p>

		<?=plotData($plotExchByFighter, 'exch-by-fighter', 'By Fighter', $color)?>

		<?=plotData($plotExchBySchool, 'exch-by-school', 'By Club', $color)?>

		<?=plotData($plotExchByEvent, 'exch-by-event', 'By Event', $color, 10)?>

	</div>

<?php
}

/******************************************************************************/

function yearlySummaryMatches($eventListStr, $color){

	$sql = "SELECT COUNT(*) AS numMatches
			FROM eventMatches
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
			WHERE {$eventListStr}";
	$numMatches = mysqlQuery($sql, SINGLE, 'numMatches');


// By Fighter ----------------------------------------------------------

	$sql = "SELECT systemRosterID, COUNT(*) AS numMatches
			FROM eventMatches AS eM
				INNER JOIN eventRoster AS eR ON eM.fighter1ID = eR.rosterID
			WHERE {$eventListStr}
				AND systemRosterID IS NOT NULL
			GROUP BY systemRosterID
			ORDER BY numMatches DESC
			LIMIT 60";
	$matchByFighter1 = (array)mysqlQuery($sql, ASSOC);

	$sql = "SELECT systemRosterID, COUNT(*) AS numMatches
			FROM eventMatches AS eM
				INNER JOIN eventRoster AS eR ON eM.fighter2ID = eR.rosterID
			WHERE {$eventListStr}
				AND systemRosterID IS NOT NULL
			GROUP BY systemRosterID
			ORDER BY numMatches DESC
			LIMIT 60";
	$matchByFighter2 = (array)mysqlQuery($sql, ASSOC);


	$matchByFighter = [];
	foreach($matchByFighter1 as $e){
		$matchByFighter[$e['systemRosterID']] = $e['numMatches'];
	}

	foreach($matchByFighter2 as $e){
		@$matchByFighter[$e['systemRosterID']] += $e['numMatches'];
	}

	arsort($matchByFighter);

	$plotMatchByFighter = [];
	$i = 0;
	foreach($matchByFighter as $systemRosterID => $numMatches){
		$plotMatchByFighter[$i]['value'] = $numMatches;
		$plotMatchByFighter[$i]['name'] = getFighterNameSystem($systemRosterID);
		$i++;
		if($i >= 30){
			break;
		}
	}


// By School ----------------------------

	$sql = "SELECT eR.schoolID, COUNT(*) AS numMatches
			FROM eventMatches AS eM
				INNER JOIN eventRoster AS eR ON eM.fighter1ID = eR.rosterID
			WHERE {$eventListStr}
				AND eR.schoolID IS NOT NULL
				AND eR.schoolID != 1
				AND eR.schoolID != 2
			GROUP BY eR.schoolID
			ORDER BY numMatches DESC
			LIMIT 60";
	$matchBySchool1 = (array)mysqlQuery($sql, ASSOC);

	$sql = "SELECT eR.schoolID, COUNT(*) AS numMatches
			FROM eventMatches AS eM
				INNER JOIN eventRoster AS eR ON eM.fighter2ID = eR.rosterID
			WHERE {$eventListStr}
				AND eR.schoolID IS NOT NULL
				AND eR.schoolID != 1
				AND eR.schoolID != 2
			GROUP BY eR.schoolID
			ORDER BY numMatches DESC
			LIMIT 60";
	$matchBySchool2 = (array)mysqlQuery($sql, ASSOC);


	$matchBySchool = [];
	foreach($matchBySchool1 as $e){
		$matchBySchool[$e['schoolID']] = $e['numMatches'];
	}

	foreach($matchBySchool2 as $e){
		@$matchBySchool[$e['schoolID']] += $e['numMatches'];
	}

	arsort($matchBySchool);

	$plotMatchBySchool = [];
	$i = 0;
	foreach($matchBySchool as $schoolID => $numMatches){
		$plotMatchBySchool[$i]['value'] = $numMatches;
		$plotMatchBySchool[$i]['name'] = getSchoolName($schoolID);
		$i++;
		if($i >= 30){
			break;
		}
	}


// By Event ----------------------------------------

	$sql = "SELECT eventName AS name, COUNT(*) AS value
			FROM eventMatches AS eM
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventTournaments USING(tournamentID)
			INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
			GROUP BY name
			ORDER BY value DESC, name ASC";
	$plotMatchByEvent = (array)mysqlQuery($sql, ASSOC);

?>

	

	<div class='medium-6 cell callout'>

		<div class='yearly-summary-title'>-- Matches --</div>

		<p class='yearly-summary-text'>Total # of Matches: <b><?=number_format($numMatches)?></b></p>

		<?=plotData($plotMatchByFighter, 'match-by-fighter', 'By Fighter', $color)?>
		<?=plotData($plotMatchBySchool, 'match-by-school', 'By Club', $color)?>
		<?=plotData($plotMatchByEvent, 'match-by-event', 'By Event', $color, 10)?>

	</div>

<?php
}

/******************************************************************************/

function yearlySummaryClubs($eventListStr, $color){

	$sql = "SELECT schoolID, COUNT(*) AS numReg
			FROM eventRoster AS eR
			WHERE {$eventListStr}
			GROUP BY schoolID
			ORDER BY numReg DESC
			LIMIT 30";
	$regBySchool = (array)mysqlQuery($sql, ASSOC);

    $plotRegBySchool = [];
	foreach($regBySchool as $i => $f){
		$plotRegBySchool[$i]['value'] = $f['numReg'];
		$plotRegBySchool[$i]['name'] = getSchoolName($f['schoolID']);
	}

	$sql = "SELECT eR.schoolID, COUNT(*) AS numMatches
			FROM eventMatches AS eM
			INNER JOIN eventRoster AS eR ON eM.winnerID = eR.rosterID
			WHERE {$eventListStr}
			AND eR.schoolID IS NOT NULL
			AND eR.schoolID != 1
			AND eR.schoolID != 2
			GROUP BY eR.schoolID
			ORDER BY numMatches DESC
			LIMIT 30";
	$winsBySchool = (array)mysqlQuery($sql, ASSOC);

    $plotWinsBySchool = [];
	foreach($winsBySchool as $i => $f){
		$plotWinsBySchool[$i]['value'] = $f['numMatches'];
		$plotWinsBySchool[$i]['name'] = getSchoolName($f['schoolID']);
	}



?>

	<div class='medium-6 cell callout'>

		<div class='yearly-summary-title'>-- By Club --</div>

		<?=plotData($plotWinsBySchool, 'reg-by-school', 'Event Registrations', $color, 10)?>

		<?=plotData($plotWinsBySchool, 'wins-by-school', 'Wins', $color, 10)?>

	</div>

<?php
}

/******************************************************************************/

function yearlySummaryDates($eventListStr, $color){


	$sql = "SELECT eventStartDate, eventEndDate
			FROM systemEvents
			WHERE {$eventListStr}
			ORDER BY eventStartDate ASC";
	$eventList = (array)mysqlQuery($sql, ASSOC);


	$plotEventsByMonth = [];
	$plotEventsByDays = [];

	for($i = 1; $i <= 12; $i++){
		$plotEventsByMonth[$i]['name'] = date('F',strtotime("2000-{$i}-01"));
		$plotEventsByMonth[$i]['value'] = 0;

		if($i <= 5){
			$plotEventsByDays[$i]['name'] = $i." Day".plrl($i);
			$plotEventsByDays[$i]['value'] = 0;
		}
	}

	foreach($eventList as $e){
		$month = (int)date('n',strtotime($e['eventStartDate']));
		$plotEventsByMonth[$month]['value']++;

		$start = strtotime($e['eventStartDate']);
		$end = strtotime($e['eventEndDate']);
		$datediff = 1 + (($end - $start) / (60 * 60 * 24));

		
		if(isset($plotEventsByDays[$datediff]) == true){
			$plotEventsByDays[$datediff]['value']++;
		}
	}

?>

	<div class='cell callout medium-6'>

		<div class='yearly-summary-title'>-- By Month --</div>

		<?=plotData($plotEventsByMonth, 'events-by-month','Number of Events', $color, 12)?>

		<?=plotData($plotEventsByDays, 'events-by-days','Event Length', $color)?>

	</div>

<?php
}


/******************************************************************************/

function yearlySummaryCountries($eventListStr, $color){


	$sql = "SELECT countryName, COUNT(*) AS numEvents
			FROM systemEvents
			INNER JOIN systemCountries USING(countryIso2)
			WHERE {$eventListStr}
			GROUP BY countryIso2
			ORDER BY numEvents DESC, countryIso2 ASC";
	$eventList = (array)mysqlQuery($sql, ASSOC);

	$numCountries = sizeof($eventList);

	$totalNumEvents = 0;
	foreach($eventList as $e){
		$totalNumEvents += $e['numEvents'];
	}

	$plotEventsByCountry = [];
	foreach($eventList as $i => $f){
		$plotEventsByCountry[$i]['value'] = $f['numEvents'];
		$plotEventsByCountry[$i]['name'] = $f['countryName'];
	}

?>


	<div class='medium-6 cell callout'>

		<div class='yearly-summary-title'>-- Countries --</div>

		<p class='yearly-summary-text'>
			Total # of Events: <b><?=$totalNumEvents?></b><BR>
			# Countries Using Scorecard: <b><?=$numCountries?></b>
		</p>

		<?=plotData($plotEventsByCountry, 'events-by-country','Number of Events', $color, 15)?>

	</div>

<?php
}

/******************************************************************************/

function yearlySummaryTournaments($eventListStr, $color){


	$sql = "SELECT tournamentType AS name, COUNT(*) AS value
			FROM eventTournaments AS eT
			INNER JOIN systemTournaments AS sT ON eT.tournamentWeaponID = sT.tournamentTypeID
			WHERE {$eventListStr}
			GROUP BY tournamentType
			ORDER BY value DESC, tournamentType ASC";
	$plotTournamentsByWeapon = (array)mysqlQuery($sql, ASSOC);

	$numTournaments = 0;
	foreach($plotTournamentsByWeapon as $w){
		$numTournaments += $w['value'];
	}

	$sql = "SELECT eventName AS name, COUNT(*) AS value
			FROM eventTournaments AS eT
			INNER JOIN systemEvents USING(eventID)
			WHERE {$eventListStr}
			GROUP BY eventName
			ORDER BY value DESC, eventName ASC
			LIMIT 30";
	$plotTournamentsByEvent = (array)mysqlQuery($sql, ASSOC);


?>

	<div class='medium-6 cell callout'>

		<div class='yearly-summary-title'>-- Tournaments --</div>

		<p class='yearly-summary-text'>
			Total # of Tournaments: <b><?=$numTournaments?></b>
		</p>

		<?=plotData($plotTournamentsByWeapon, 'tournaments-by-weapon','By Weapon', $color, 10)?>
		<?=plotData($plotTournamentsByEvent, 'tournaments-by-event','By Event', $color, 10)?>

	</div>

<?php
}

/******************************************************************************/

function yearlySummaryIndividual($eventListStr, $color){

	$validExchanges = "'clean','afterblow','double','noExchange','scored'";

	$sql = "SELECT systemRosterID, COUNT(*) AS numMatches
			FROM eventMatches AS eM
				INNER JOIN eventRoster AS eR ON eR.rosterID = eM.winnerID
			WHERE {$eventListStr}
				AND ABS(fighter1Score - fighter2Score) = 1
				AND fighter1Score > 3
				AND fighter2Score > 3
				AND systemRosterID IS NOT NULL
			GROUP BY systemRosterID
			ORDER BY numMatches DESC
			LIMIT 30";
	$closeMatches = (array)mysqlQuery($sql, ASSOC);

	$plotCloseMatches = [];
	foreach($closeMatches as $i => $w){
		$plotCloseMatches[$i]['value'] = $w['numMatches'];
		$plotCloseMatches[$i]['name'] = getFighterNameSystem($w['systemRosterID']);
	}

	$sql = "SELECT systemRosterID, COUNT(*) AS numMatches
			FROM eventMatches AS eM
				INNER JOIN eventRoster AS eR ON eR.rosterID = eM.winnerID
			WHERE {$eventListStr}
				AND ((fighter1Score = 0 && fighter2Score >= 4)
					OR (fighter2Score = 0 && fighter1Score >= 4))
				AND systemRosterID IS NOT NULL
			GROUP BY systemRosterID
			ORDER BY numMatches DESC
			LIMIT 30";
	$shutdoutMatches = (array)mysqlQuery($sql, ASSOC);

	$plotShutoutMatches = [];
	foreach($shutdoutMatches as $i => $w){
		$plotShutoutMatches[$i]['value'] = $w['numMatches'];
		$plotShutoutMatches[$i]['name'] = getFighterNameSystem($w['systemRosterID']);
	}

	$sql = "SELECT matchID, COUNT(*) AS numExchanges
			FROM eventExchanges
				INNER JOIN eventMatches USING(matchID)
				INNER JOIN eventGroups USING(groupID)
				INNER JOIN eventTournaments USING(tournamentID)
			WHERE {$eventListStr}
				AND exchangeType IN ({$validExchanges})
				AND formatID = 2
				AND isTeams = 0
			GROUP BY matchID
			ORDER BY numExchanges DESC
			LIMIT 30";
	$longMatches = (array)mysqlQuery($sql, ASSOC);

	$plotLongMatches = [];
	foreach($longMatches as $i => $m){

		$plotLongMatches[$i]['value'] = $m['numExchanges'];
		$matchID = (int)$m['matchID'];

		$sql = "SELECT eventName,
					(	SELECT systemRosterID
						FROM eventRoster AS eR2
						WHERE eR2.rosterID = eM.fighter1ID	
					) AS sysID1,
					(	SELECT systemRosterID
						FROM eventRoster AS eR3
						WHERE eR3.rosterID = eM.fighter2ID	
					) AS sysID2
				FROM eventMatches AS eM
					INNER JOIN eventGroups USING(groupID)
					INNER JOIN eventTournaments USING(tournamentID)
					INNER JOIN systemEvents USING(eventID)
				WHERE matchID = {$matchID}";
		$matchData = mysqlQuery($sql, SINGLE);

		$txt = getFighterNameSystem($matchData['sysID1']);
		$txt .= " vs ";
		$txt .= getFighterNameSystem($matchData['sysID2'])."";
		$txt .= "<i style='font-size:0.8em;'><BR>(".$matchData['eventName'].")</i></span>";

		$plotLongMatches[$i]['name'] = $txt;
	}





?>

	<div class='medium-6 cell callout'>

		<div class='yearly-summary-title'>-- Individuals --</div>

		<i>Close matches are matches where a fighter won by only a single point, and the lowest score was higher than 3 points.</i><BR>
		<?=plotData($plotCloseMatches, 'close-matches','Close Matches', $color)?>

		<i>Shoutout matches are matches where a fighter scored 4 or more points, and their opponent scored none.</i><BR>
		<?=plotData($plotShutoutMatches, 'shutout-matches','Shutouts', $color)?>

		<?=plotData($plotLongMatches, 'long-matches','# of Exchanges', $color)?>

	</div>

<?php
}

/******************************************************************************/

function yearlySummaryUrg($eventListStr, $color){

	$sql = "SELECT tournamentType AS weaponName, eventName, numParticipants
			FROM eventTournaments AS eT
				INNER JOIN systemEvents USING(eventID)
				INNER JOIN systemTournaments AS sT ON eT.tournamentWeaponID = sT.tournamentTypeID
			WHERE {$eventListStr}
				AND tournamentGenderID IN (21,109,125)
			ORDER BY numParticipants DESC, weaponName ASC";
	$urgTournaments = (array)mysqlQuery($sql, ASSOC);

	$numTournaments = 0;
	$byWeapon = [];
	$byEvent = [];
	foreach($urgTournaments as $t){
		$numTournaments++;

		@$tournamentsByWeapon[$t['weaponName']]++;
		@$regByWeapon[$t['weaponName']] += $t['numParticipants'];
		@$regByEvent[$t['eventName']] += $t['numParticipants'];

	}


	$numEvents = sizeof($byEvent);

	arsort($tournamentsByWeapon);
	arsort($regByWeapon);
	arsort($regByEvent);

	$plotTournamentsByWeapon = [];
	$i = 0;
	foreach($tournamentsByWeapon as $weaponName => $num){
		$plotTournamentsByWeapon[$i]['name']  = $weaponName;
		$plotTournamentsByWeapon[$i]['value'] = $num;
		$i++;
	}

	$plotRegByWeapon = [];
	$i = 0;
	foreach($regByWeapon as $weaponName => $num){
		$plotRegByWeapon[$i]['name']  = $weaponName;
		$plotRegByWeapon[$i]['value'] = $num;
		$i++;
	}

	$plotRegByEvent = [];
	$i = 0;
	foreach($regByEvent as $eventName => $num){
		$plotRegByEvent[$i]['name']  = $eventName;
		$plotRegByEvent[$i]['value'] = $num;
		$i++;
	}


?>

	<div class='medium-6 cell callout'>

		<div class='yearly-summary-title'>-- URG/Women's --</div>

		<p><i><u>Note</u>: This can only take into account tournaments which been set up with a URG/Women's designation.</i></p>

		<p class='yearly-summary-text'>
			Total # of Tournaments: <b><?=$numTournaments?></b><BR>
			# of Events Offering: <b><?=$numEvents?></b>
		</p>

		<?=plotData($plotTournamentsByWeapon, 'tournament-by-weapon','# Tournaments', $color)?>
		<?=plotData($plotRegByWeapon, 'reg-by-weapon','# Tournament Entries', $color)?>
		<?=plotData($plotRegByEvent, 'reg-by-event','# Tournament Entries', $color, 8)?>

	</div>

<?php
}

/******************************************************************************/

function yearlySummarySoftware($year){

	if(ALLOW['SOFTWARE_ADMIN'] == true && isset($_SESSION['forcePlainText']) == true){
		$wysisygClass = '';
	} else {
		$wysisygClass = 'tiny-mce';
	}

	$defaultText = "asdf";

	$year = (int)$year;

	if($year == 0){
		return;
	}

	$sql = "SELECT updateText
			FROM systemUpdates
			WHERE updateYear = {$year}";
	$updateText = mysqlQuery($sql, SINGLE, 'updateText');

?>

	<div class='cell large-12 callout'>

		<div class='yearly-summary-title'>Software Improvements/Features in <?=$year?></div>
		<i>(Not included: minor tweaks, back-end changes, or things I forgot I added.)</i>

		<?php if(ALLOW['SOFTWARE_ADMIN'] == true): ?>

			<form method="POST">

			<input type='hidden' name='updateSoftwareUpdates[updateYear]' value='<?=$year?>'>

			<textarea name='updateSoftwareUpdates[updateText]' class='<?=$wysisygClass?>'  rows='20'><?=$updateText?></textarea>

			<button class='button success' name='formName' value='updateSoftwareUpdates'>Update</button>

			</form>

		<?php else: ?>

			<p><?=$updateText?></p>

		<?php endif ?>
	</div>

<?php
}

/******************************************************************************/

function plotData($data, $id, $title = null, $color = '#D6E5FA', $numToShow = 5){

	$max = 1;
	$numDataPoints = sizeof($data);

	if($numToShow > $numDataPoints){
		$numToShow = $numDataPoints;
	}
	foreach($data as $i => $m){

		if($m['value'] > $max){
			$max = $m['value'];
		}

		if(isset($m['nameShort']) == false){
			$data[$i]['nameShort'] = $m['name'];
		}
	}

	$i = 0;

?>
	<span style='font-size:1.3em'><?=$title?></span>
	<input type="range" min="1" max="<?=$numDataPoints?>" value="<?=$numToShow?>" id="<?=$id?>-slider" onchange="listSlider('<?=$id?>')">
	(Showing <span id='<?=$id?>-count'><?=$numToShow?></span> / <?=$numDataPoints?>)

	<table class='table-compact'>
		<?php foreach($data as $m):
			$width = 100 * ($m['value'] / $max);
			$i++;

			$class = "";
			if($i > $numToShow){
				$class .= ' hidden';
			}

			$class2 = "";
			if(strlen($m['name']) > 40){
				$class2 .= 'max-width:500px;min-width:250px;';
			} else {
				$class2 .= 'width:0.1%;white-space: nowrap;';
			}

			?>
			<tr id='<?=$id?>-<?=$i?>' class='<?=$class?>'>
				<td class='hide-for-small-only' style='<?=$class2?>'><?=$m['name']?></td>
				<td class='show-for-small-only' ><?=$m['name']?></td>
				<td style='width:0.1%;white-space: nowrap; border-right: solid 1px black' class='text-right'><?=number_format($m['value'])?></td>

				<td style='min-width: 100px; padding:0px'>
					<div style='
							width:<?=$width?>%;
							display:inline-block;
							margin:0px;
							padding:0px;
							background-image: linear-gradient(to right, white, <?=$color?>);'
					>
						&nbsp;
					</div>
				</td>
			</tr>
		<?php endforeach ?>
	</table>
<?php
}

/******************************************************************************/

/******************************************************************************/
// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
