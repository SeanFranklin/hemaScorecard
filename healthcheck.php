<?php
/*******************************************************************************
	Health-check endpoint for CI/test readiness polling.

	Returns 200 "OK" once the app can reach the database AND the schema/seed
	is loaded (systemEvents exists), 503 otherwise. Deliberately does not
	include config.php/header.php to avoid session and POST machinery.

*******************************************************************************/

// database.php references this constant, normally defined in config.php
if(!defined('DEPLOYMENT_UNKNOWN')){ define('DEPLOYMENT_UNKNOWN', 0); }

include(__DIR__.'/includes/database.php');

mysqli_report(MYSQLI_REPORT_OFF);
$link = @mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, PRIMARY_DATABASE);

if($link === false){
	http_response_code(503);
	echo "DB UNAVAILABLE";
	exit;
}

$result = @mysqli_query($link, "SELECT 1 FROM systemEvents LIMIT 1");

if($result === false){
	http_response_code(503);
	echo "SCHEMA NOT LOADED";
} else {
	echo "OK";
}

mysqli_close($link);

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
