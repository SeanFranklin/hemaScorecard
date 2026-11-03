<?php
/*******************************************************************************
	htmx snippet for adminTournaments.php

	Inline validation of one custom ranking formula tier. Validation on
	save stays authoritative.

*******************************************************************************/

define('BASE_URL' , $_SERVER['DOCUMENT_ROOT'].'/');
include_once(BASE_URL.'includes/config.php');

if(ALLOW['EVENT_MANAGEMENT'] == false){
	exit;
}

$postedCriteria = @$_REQUEST['updateTournament']['customCriteria'];
if(is_array($postedCriteria) == false){
	exit;
}

// hx-include is scoped to a single row, so only one tier is present
foreach($postedCriteria as $tier){

	$source = trim((string)@$tier['formula']);
	if($source === ''){
		exit;
	}

	$fallback = trim((string)@$tier['fallback']);
	if($fallback === ''){
		$fallback = '0';
	}

	$compiled = formula_compile($source, $fallback, customRankingFormulaFields());

	if(isset($compiled['error'])){
		// Defense in depth; compiler errors already escape user input
		$message = htmlspecialchars($compiled['error'], ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
		echo "<span class='form-error is-visible' style='display:block;'>{$message}</span>";
	} else {
		echo "<span style='color:green;'>&#10003; Valid formula</span>";
	}
	exit;
}
