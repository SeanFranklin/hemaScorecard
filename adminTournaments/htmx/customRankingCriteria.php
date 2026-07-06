<?php
/*******************************************************************************
	htmx snippet for adminTournaments.php

	Returns the custom ranking criteria selectors for the tournament settings
	form. Called when the Ranking Type select changes; echoes the populated
	<tbody> fragment when "Custom" is selected, otherwise an empty <tbody>
	so the swap removes the criteria rows.

*******************************************************************************/

define('BASE_URL' , $_SERVER['DOCUMENT_ROOT'].'/');
include_once(BASE_URL.'includes/config.php');

$tournamentID = (int)@$_REQUEST['tournamentID'];
$selectedRankingID = (int)@$_REQUEST['updateTournament']['tournamentRankingID'];

if(ALLOW['EVENT_MANAGEMENT'] == false){
	exit;
}

if($selectedRankingID != RANKING_CUSTOM){
	edit_customRankingCriteria($tournamentID, null);
	exit;
}

// Custom criteria only apply to match format tournaments
if($tournamentID != 0 && (int)getTournamentFormat($tournamentID) != FORMAT_MATCH){
	edit_customRankingCriteria($tournamentID, null);
	exit;
}

// Reverse scoring (Golf/Injury) gets a behavior warning. Prefer the form's
// current (unsaved) selection, included in the request; fall back to the
// saved tournament setting.
if(isset($_REQUEST['updateTournament']['isReverseScore']) == true){
	$isReverse = (int)$_REQUEST['updateTournament']['isReverseScore'] > REVERSE_SCORE_NO;
} else {
	$isReverse = $tournamentID != 0 && isReverseScore($tournamentID) > REVERSE_SCORE_NO;
}

// Prefill from the tournament's existing custom configuration if it has one
$eventRanking = null;
if(isCustomRanking($tournamentID) == true){
	$eventRanking = getEventRankingForTournament($tournamentID);
}

if($eventRanking == null){
	// Sensible defaults for a fresh custom ranking
	$eventRanking = [
		'orderByField1' => 'wins',        'orderBySort1' => 'DESC',
		'orderByField2' => null,          'orderBySort2' => null,
		'orderByField3' => null,          'orderBySort3' => null,
		'orderByField4' => null,          'orderBySort4' => null,
	];
}

edit_customRankingCriteria($tournamentID, $eventRanking, $isReverse);
