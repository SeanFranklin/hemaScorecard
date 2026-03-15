<?php
/*******************************************************************************


*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Event Rating';
include('includes/header.php');
{

	$textInput = @$_SESSION['eventRating']['textInput'];
	$csvInput = @$_SESSION['eventRating']['cvsData'];


	$maxAtRating = [];
	if(isset($_SESSION['eventRating']['cvsData']) == true){
		$ratingsList = [];

		$numEvents = sizeof($_SESSION['eventRating']['cvsData']);

		foreach($_SESSION['eventRating']['cvsData'] as $hrEventID => $data){

			$ratingsRaw = $data['ratings'];
			rsort($ratingsRaw);

			$tmp = [];
			$tmp['name'] = $data['name'];
			$tmp['year'] = $data['year'];
			$tmp['ratings'] = $ratingsRaw;

			$toughCompetitors = 0;
			$numCompetitors = 0;


			foreach($ratingsRaw as $i => $r){
				if($r >= 1600){
					$toughCompetitors++;
				}
				$numCompetitors++;
			}

			$tmp['numTough'] = $toughCompetitors;
			$tmp['numFighters'] = $numCompetitors;

			if($tmp['numFighters'] < 4){
				continue;
			}

			$tmp = calculateEventRating($tmp);

			$i = 0;
			foreach($tmp['histogram'] as $i => $h){



				if(isset($maxAtRating[$i]) == false || $h > $maxAtRating[$i]){
					$maxAtRating[$i] = $h;
				}

				$tmp2 = [];
				$tmp2['num'] = $h;
				//$tmp2['color'] = $color;
				$tmp['displayHistogram'][] = $tmp2;
				$i++;

			}

			$ratingsList[] = $tmp;

		}

		$eventRatings = array_column($ratingsList, 'eventRating');
		array_multisort($eventRatings, SORT_DESC, $ratingsList);

		unset($_SESSION['eventRating']['cvsData']);

	} elseif (isset($_SESSION['eventRating']['textInput']) == true){

		$ratingsList[0]['ratings'] = parseRatings($_SESSION['eventRating']['textInput']);

		$ratingsList[0]['name'] = "Unknown Event";
		$ratingsList[0]['year'] = '??';

		unset($_SESSION['eventRating']['textInput']);

	} else {
		$ratingsList = [];
	}


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<div class='callout success text-center'>
		Tournament Ratings are calculated using the <b><a href='https://swordstem.com/2024/01/01/on-rating-hema-tournaments/'>PB25 Algorithm.</a></b>
	</div>

	<?=showEventRating($ratingsList, $maxAtRating)?>
	<p><i>Note: All ratings rounded up to floor value of <?=EVENT_RATING_MIN_RATING?></i></p>


	<form method="POST">

	Ratings: (comma separated)
	<textarea name='eventRating[textInput]' required rows='4'><?=$textInput?></textarea>

	<button name='formName' value='eventRating' class='button'>
		Calculate Rating
	</button>
	</form>

	<HR>

<!-- File upload -------------------------------------------------------------->





<?php if(ALLOW['SOFTWARE_ADMIN'] == true):?>
		<a onclick="$('.csv-upload').toggle('hidden')">CSV File Method  ↓ </a>

	<div class='grid-x grid-margin-x'>
	<div class='csv-upload hidden large-7 medium-10 callout cell'>

			<ul>
			<li><u>1st Row</u>, <i>Heading Row</i>: don't have any real data here</li>
			<li><u>Column 1</u>, <i>Event ID</i>: This is any arbitrary number unique to each event, so that the software has an index to tell them appart with.</li>
			<li><u>Column 2</u>, <i>Event Name</i>: Exactly what it sounds like. Whatever name you want it to spit out next to the result.</li>
			<li><u>Column 3</u>, <i>Rating</i>: A fighters rating. The script will convert any decimal numbers into integers, and limit them to a lower bound of <?=EVENT_RATING_MIN_RATING?>.</li>
			<li><u>Column 4</u>, <i>Date (Optional)</i>: The date of the event. This is only used to parse out the year if it is not part of the Event Name.</li>
			</ul>

			<u>Example</u>:
			<pre>
EventId,EventName,Rating
1,StatsFecht 2021,1500
1,StatsFecht 2021,734
2,StatsFecht,1234,2022-01-01
2,StatsFecht,1622,2022-01-01
2,StatsFecht,987,2022-01-01
			</pre>

			<form method="POST" enctype="multipart/form-data">

				<div class='input-group'>
					<span class='input-group-label'>File To Upload:</span>

					<input class='input-group-field' type="file"
						name="eventRatingCSV" id="eventRatingCSV" required>

					<div class=' input-group-button'>
						<button class='button success no-bottom'
							value="importEventRatingCSV" name="formName">
							Upload CSV
						</button>
					</div>
				</div>
			</form>

	</div>
	</div>
<?php endif ?>




<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////


/******************************************************************************/

function showEventRating($ratingsList, $maxAtRating){

	if(sizeof($ratingsList) == 0){
		return;
	} elseif(sizeof($ratingsList) > 4){
		$tableMode = true;
	} else {
		$tableMode = false;
	}

	$showYear = "";
	foreach($ratingsList as $r){
		if((int)$r['year'] != 0){
			$showYear = "<th>Year</th>";
		}
	}

	if($tableMode == true){
		echo "<table>
		<tr>
		<th style='vertical-align:bottom'>Name</th>
		{$showYear}
		<th style='font-size:0.8em'># Total</th>
		<th style='font-size:0.8em'># > 1600</th>
		<th style='vertical-align:bottom'>Rating</th>";

		for($i = 2100; $i >= 800; $i -= 100){
			$num = $i-100;
			echo "<th style='font-size:0.6em; vertical-align:bottom''> >{$num}</th>";
		}

		echo "</tr>";
	}

	foreach($ratingsList as $eventData){


		if($tableMode == true){
			showEventRatingTable($eventData, $showYear, $maxAtRating);
		} else {
			showEventRatingCell($eventData);
		}

	}

	if($tableMode == true){
		echo "</table>";
	}

?>

<?php
}

/******************************************************************************/

function parseRatings($oldInput){

	$ratings = [];

	$input = str_replace("\n", ',', $oldInput);
	$input = str_replace("\r", ',', $input);
	$input = str_replace(" ", ',', $input);

	$parts = (array)explode(",",$input);
	$parts = (array)array_map('trim', $parts);


	foreach($parts as $part){
		if(strlen($part) != 0){
			$ratings[] = max((int)$part, EVENT_RATING_MIN_RATING);
		}
	}

	rsort($ratings);

	return ($ratings);


}

/******************************************************************************/

function showEventRatingTable($eventInfo, $showYear, $maxAtRating){


?>

	<tr class='text-right'>
		<td><?=$eventInfo['name']?></td>
		<?php if($showYear != ""): ?>
			<td><?=$eventInfo['year']?></td>
		<?php endif ?>
		<td><?=$eventInfo['numFighters']?></td>
		<td><?=$eventInfo['numTough']?></td>
		<td><?=$eventInfo['eventRating']?></td>
		<?php foreach($eventInfo['histogram'] as $i => $h):


			$color = "";
			if($maxAtRating[$i] == 0 || $h == ""){
				$color .= "#FFF";
			} else if($h == $maxAtRating[$i]) {
				$color .= "#F00";
			} else {
				$color .= "#F";
				$num = 15-floor((16*(int)$h)/$maxAtRating[$i]);
				$color .= dechex($num);
				$color .= dechex($num);
			}

			?>
			<td style='border-left:1px solid #ACD; background:<?=$color?>' class='text-right'>
				<?=$h?>
			</td>
		<?php endforeach ?>
	</tr>

<?php
}

/******************************************************************************/

function showEventRatingCell($eventInfo){
?>
	<div class='callout'>

	<h3><?=$eventInfo['name']?>: <b><u><?=$eventInfo['eventRating']?></u></b></h3>


	<b>Fighters:</b> <?=$eventInfo['numFighters']?>

	<BR><b>Ratings:</b>
	<?php foreach($eventInfo['ratings'] as $rating):?>
		<?=$rating?>,
	<?php endforeach ?>

	</BR>
	</div>

<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
