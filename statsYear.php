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
	// Show last year for the first 30 days of the new year.
	$defaultYear = (int)date("Y", strtotime("-30 day"));


	if(isset($_SESSION['stats']['year']) == true){
		$year = (int)$_SESSION['stats']['year'];
	} else {
		$year = $defaultYear;
	}

	if(ALLOW['SOFTWARE_ADMIN'] == true && $_SESSION['eventID'] == 0){
		$_SESSION['stats']['futureView'] = true;
		$_SESSION['adminOptions']['forcePlainText'] = true;
	}

	$futureView = (boolean)(@$_SESSION['stats']['futureView']);


	if($futureView == true){
		$scopeText = ' [FULL YEAR]';
	} elseif (ALLOW['SOFTWARE_ADMIN'] == true) {
		$scopeText = ' [YTD]';
	} else {
		$scopeText = "";
	}

	if($year != 0){

		$sql = "SELECT eventID
				FROM systemEvents
				WHERE eventYear = {$year}
				AND isMetaEvent = 0
				AND eventName NOT LIKE '%=%'";
		$eventList = (array)mysqlQuery($sql, SINGLES);
		$eventListStr = "eventID IN (".implode2int($eventList).")";

	} else {
		$eventListStr = "eventID IS NOT NULL";
	}


	if(ALLOW['SOFTWARE_ADMIN'] == true && $futureView == true){ // not existing is logical false
		// Show future events
	} else {
		$eventListStr .= " AND eventStartDate <= NOW() ";
	}



	$sql = "SELECT eR1.systemRosterID AS systemRosterID1, eR2.systemRosterID AS systemRosterID2
			FROM eventMatches AS eM
			INNER JOIN eventGroups USING(groupID)
			INNER JOIN eventRoster AS eR1 ON eM.fighter1ID = eR1.rosterID
			INNER JOIN eventRoster AS eR2 ON eM.fighter2ID = eR2.rosterID
			WHERE tournamentID = 1977
			ORDER BY matchNumber ASC";

	$matches = mySqlQuery($sql, ASSOC);




// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<div class='grid-x grid-margin-x'>

		<div class='cell callout text-center success'>
			<h3><?=$year?> - Year In Review <?=$scopeText?></h3>
		</div>

	</div>


	<ul class="tabs" data-tabs id="yearly-summary-tabs">

		<li class="tabs-title is-active"><a
			data-tabs-target="panel-intro"
			href            ="#panel-intro">
				            Intro
		</a></li>


		<li class="tabs-title"><a
			href            ="#panel-country"
			onclick="getDataForYear(<?=$year?>, 'events-by-country', 'teal',0)">
				            Country
		</a></li>

		<li class="tabs-title"><a
			data-tabs-target="panel-date"
			onclick="getDataForYearType(<?=$year?>, ['events-by-month','events-by-days'], 'crimson', [12,5], true)">
				            Date
		</a></li>

		<li class="tabs-title"><a
			data-tabs-target="panel-event"
			href            ="#change-event"
			onclick="getDataForYearType(<?=$year?>, ['exchanges-by-event','matches-by-event','tournaments-by-event','womens-by-event'], 'deepskyblue', 10)">
				            Events
		</a></li>

		<li class="tabs-title"><a
			data-tabs-target="panel-club"
			href            ="#change-club"
			onclick="getDataForYearType(<?=$year?>, ['entries-by-club','matches-by-club','exchanges-by-club','wins-by-club'], 'Aquamarine', 10)">
				            Clubs
		</a></li>

		<li class="tabs-title"><a
			data-tabs-target="panel-weapon"
			href            ="#change-weapon"
			onclick="getDataForYearType(<?=$year?>, ['tournaments-by-weapon','exchanges-by-weapon','wtournaments-by-weapon','womens-by-weapon'], 'gray', 10)">
				            Weapons
		</a></li>

		<li class="tabs-title"><a
			data-tabs-target="panel-fighter"
			href            ="#change-fighter"
			onclick="getDataForYearType(<?=$year?>, ['exchanges-by-fighter','close-by-fighter','entries-by-fighter','events-by-fighter','shutouts-by-fighter','matches-by-fighter'], 'salmon', 10)">
				            Fighters
		</a></li>

		<li class="tabs-title"><a
			data-tabs-target="panel-match"
			href            ="#change-match"
			onclick="getDataForYearType(<?=$year?>, ['exchanges-by-match', 'rematches-by-fighter', 'comebacks-by-match'], 'violet', 10)">
				            Matches
		</a></li>

		<li class="tabs-title">
			<a data-tabs-target="panel-software" href="#change-software">
				Software Improvements
			</a>
		</li>

	</ul>


	<div class="tabs-content" data-tabs-content="yearly-summary-tabs">

		<div class="tabs-panel is-active" id="panel-intro">
			<?=yearlySummaryIntro($year, $futureView)?>
		</div>

		<div class="tabs-panel" id="panel-country">
			<div class='grid-x grid-margin-x'>
				<div class='medium-12 cell'>
					<?=yearlySummaryItem('events-by-country', $year)?>
				</div>
			</div>
		</div>

		<div class="tabs-panel" id="panel-date">
			<div class='grid-x grid-margin-x'>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('events-by-month', $year)?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('events-by-days', $year)?>
				</div>
			</div>
		</div>

		<div class="tabs-panel" id="panel-event">
			<div class='grid-x grid-margin-x'>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('exchanges-by-event', $year)?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('matches-by-event', $year)?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('tournaments-by-event', $year)?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('womens-by-event', $year, ['unit'=>'Entries','txt'=>"Entries into tournaments which have been set up with a URG/Women's/WNBT designation."])?>
				</div>
			</div>
		</div>

		<div class="tabs-panel" id="panel-club">
			<div class='grid-x grid-margin-x'>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('entries-by-club', $year)?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('matches-by-club', $year)?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('exchanges-by-club', $year)?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('wins-by-club', $year)?>
				</div>
			</div>
		</div>

		<div class="tabs-panel" id="panel-weapon">
			<div class='grid-x grid-margin-x'>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('tournaments-by-weapon', $year)?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('exchanges-by-weapon', $year)?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('wtournaments-by-weapon', $year, ['unit'=>'Tournaments','txt'=>"Tournaments which have been set up with a URG/Women's/WNBT designation."])?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('womens-by-weapon', $year, ['unit'=>'Exchanges','txt'=>"Exchanges in tournaments which have been set up with a URG/Women's/WNBT designation."])?>
				</div>
			</div>
		</div>

		<div class="tabs-panel" id="panel-fighter">
			<div class='grid-x grid-margin-x'>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('exchanges-by-fighter', $year)?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('close-by-fighter', $year, ['unit'=>'Close Matches','txt'=>"Close matches are matches where a fighter won by only a single point, and the lowest score was higher than 3 points."])?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('entries-by-fighter', $year)?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('events-by-fighter', $year)?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('shutouts-by-fighter', $year, ['unit'=>'Shutouts','txt'=>"Shutout matches are matches where a fighter scored 4 or more points, and their opponent scored none."])?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('matches-by-fighter', $year)?>
				</div>
			</div>
		</div>

		<div class="tabs-panel" id="panel-match">
			<div class='grid-x grid-margin-x'>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('exchanges-by-match', $year)?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('comebacks-by-match', $year, ['unit'=>'Points Behind','txt'=>"The biggest point deficit a fighter has come back from and won."])?>
				</div>
				<div class='medium-6 cell'>
					<?=yearlySummaryItem('rematches-by-fighter', $year, ['unit'=>'Matches Fought','txt'=>"Number of times two fighters have met in 1v1 matches, across all weapons."])?>
				</div>

			</div>
		</div>

		<div class="tabs-panel" id="panel-software">
			<div class='grid-x grid-margin-x'>
				<?=yearlySummarySoftware($year)?>
			</div>
		</div>

	</div>




<?php
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/******************************************************************************/

/******************************************************************************/




/******************************************************************************/

function yearlySummaryItem($id, $year, $labels = []){

	if($labels == []){
		$tmp = explode('-',$id);
		$unit = ucfirst($tmp[0]);
		$txt = '';
	} else {
		$unit = $labels['unit'];
		$txt = $labels['txt']."<BR>";
	}

?>
	<div class='cell callout'>
		<i><?=$txt?></i>
		<span style='font-size:1.3em'>By # of <?=$unit?></span>
		<?=dataSliderPlot($id)?>
	</div>
<?php
}

/******************************************************************************/

function dataSliderPlot($id){
?>
    <input type="range" min="1" id="<?=$id?>-slider" onchange="listSlider('<?=$id?>')">
    (Showing <span id='<?=$id?>-count'>x</span> / <span id='<?=$id?>-total'>x</span>)

	<table id='<?=$id?>-table' class='table-compact'>
	</table>
<?php
}

/******************************************************************************/

function yearlySummaryIntro($year, $futureView){
?>

	<div class='grid-x grid-margin-x'>

	<div class='cell callout alert large-6'>
		<h3 class='text-center red-text'>Warning!</h3>
		This information is what event organizers & match table staff have entered into the database. It's accuracy/reliability will reflect their commitment to data integrity. Tournament registrations may be slightly inflated for some events if the organizer did not remove fighters dropping prior to the tournament, etc.
	</div>

	<div class='cell large-6'>
		<?=pickYearToView($year, $futureView)?>
	</div>

	</div>


<?php
}

/******************************************************************************/

function yearlySummarySoftware($year){

	if(isAdminOptionSet('forcePlainText') == true){
		$wysisygClass = '';
	} else {
		$wysisygClass = 'tiny-mce';
	}

	$defaultText = "asdf";

	$year = (int)$year;
	$index = '';

	if($year != 0){
		$sql = "SELECT updateText
				FROM systemUpdates
				WHERE updateYear = {$year}";
		$updateText = mysqlQuery($sql, SINGLE, 'updateText');

	} else {
		$sql = "SELECT updateText, updateYear
				FROM systemUpdates
				ORDER BY updateYear ASC";
		$textList = (array)mysqlQuery($sql, ASSOC);

		$updateText = "";
		foreach($textList as $text){
			$updateText .= "<a name='year_{$text['updateYear']}'></a><HR><h4>{$text['updateYear']}</h4>";
			$updateText .= "<p>{$text['updateText']}</p>";
			$index .= "<BR><a href='#year_{$text['updateYear']}'>Goto {$text['updateYear']}</a>";
		}




	}



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

			<?=$index?>
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

function pickYearToView($yearSelected, $futureView){

	$invertFutureView = (int)!$futureView;
	$currentYear = (int)date("Y");

	if(ALLOW['SOFTWARE_ADMIN'] == true){
		$displayYear = $currentYear + 1;
	} else {
		$displayYear = $currentYear;
	}

?>
	<div class="cell"><HR></div>
	<div class="cell medium-6">
		<form method="POST">
			<div class='input-group'>
				<input class='hidden' name='formName' value='statsYear'>
				<span class='input-group-label'>Viewing Year:</span>
				<select class='input-group-field' name='stats[year]'>
					<?php for($i = $displayYear; $i >=  FIRST_YEAR; $i--):?>
						<option <?=optionValue($i, $yearSelected)?>><?=$i?></option>
					<?php endfor?>
					<option <?=optionValue(0, $yearSelected)?>>Everything (very slow to load)</option>
				</select>
				<div class="input-group-button">
					<input type="submit" class="button" value="Change Year">
				</div>
			</div>
		</form>
	</div>

	<?php if(ALLOW['SOFTWARE_ADMIN'] == true): ?>
		<div class='cell medium-4'>
			<form method="POST">
				<input class='hidden' name='formName' value='statsFutureView'>
				<button class='button hollow' name='stats[futureView]' value='<?=$invertFutureView?>'>
					Set futureView == <?=$invertFutureView?>
				</button>
			</form>
		</div>
	<?php endif ?>

<?php
}

/******************************************************************************/

/******************************************************************************/
// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
