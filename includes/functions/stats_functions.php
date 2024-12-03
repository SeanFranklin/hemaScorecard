<?php
/*******************************************************************************
	Stats Function

	Functions explicitly for statistical usage or displaying data.
	Split out to avoid continually cluttering DB_read or display files.

*******************************************************************************/

// START OF DOCUMENT ///////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

/******************************************************************************/

function calculateWinPercent($rating1, $rating2){

	$dev1 = ESTIMATE_DEVIATION_M * $rating1 + ESTIMATE_DEVIATION_B;
	$dev2 = ESTIMATE_DEVIATION_M * $rating2 + ESTIMATE_DEVIATION_B;

	$score1 = $rating1 + 2 * $dev1;
	$score2 = $rating2 + 2 * $dev2;

	$scoreDelta = $score1 - $score2;

	$deviationRMS = sqrt(pow($dev1/GLICKO_CONSTANT,2) + pow($dev2/GLICKO_CONSTANT,2));
	$deviationFactor = 1 / sqrt(1 + (3 * pow($deviationRMS,2)) / pow(pi(),2));
	$winProbability = 1 / (1 + exp(-$deviationFactor * $scoreDelta/GLICKO_CONSTANT));

	return ($winProbability);
}


/******************************************************************************/

function calculateEventRating($eventData){

	$ratingsList = $eventData['ratings'];
	$numFighters = sizeof($ratingsList);
	$numStages = ceil(log($numFighters,2));
	$matchTableSize = 2^$numStages;

	$fightersInStage = [];
	$fightersInStage[2][0] = 1;
	$fightersInStage[2][1] = 4;

	for($stageNum = 3;$stageNum <= $numStages; $stageNum++){

		$fightersInStage[$stageNum][0] = pow(2,$stageNum-1)+1;
		$fightersInStage[$stageNum][1] = pow(2,$stageNum);
	}


	for($i = 2100; $i >= 800; $i -= 100){
		$eventData['histogram'][$i] = 0;
	}

	foreach($ratingsList as $rating){
		$bin = 100*ceil($rating/100);
		$eventData['histogram'][$bin]++;
	}

	foreach($eventData['histogram'] as $bin => $count){
		if($count == 0){
			$eventData['histogram'][$bin] = "";
		}
	}


	$highestRatingToTest = (int)(max($ratingsList) * 1.3);


	if($numFighters >= 8){
		$lowestRatingToTest = $ratingsList[7];
	} else {
		$lowestRatingToTest = $ratingsList[$numFighters - 1];
	}

	for($testRating = $lowestRatingToTest; $testRating <= $highestRatingToTest; $testRating += RATING_STEP){

		$testWinProb = 1;

		for($stageNum = 2; $stageNum <= $numStages; $stageNum++){

			$integral = 0;
			$samples = 0;

			for($i = $fightersInStage[$stageNum][0]; $i <= $fightersInStage[$stageNum][1]; $i++){

				if($i > $numFighters){
					$matchWinPercent = 1;
				} else {
					$matchWinPercent = calculateWinPercent($testRating, $ratingsList[$i-1]);
				}


				$integral += $matchWinPercent;
				$samples++;

				$dispProb = round($matchWinPercent * 100,0);
				////echo "<BR>Fighter: {$i} | Win %: {$dispProb}";
			}

			$stageProbability = $integral / $samples;
			$testWinProb *= $stageProbability;
		}

		if($testWinProb >= PROBABILITY_THRESHOLD){
			break;
		}
	}

	$eventData['eventRating'] = $testRating;


	return ($eventData);


}

/******************************************************************************/

/******************************************************************************/
// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
