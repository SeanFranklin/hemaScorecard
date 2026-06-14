<?php
/*******************************************************************************
	Database connection constants for mysql

*******************************************************************************/
/* Load from environment */
	$deployment = getenv('DEPLOYMENT') ?: DEPLOYMENT_UNKNOWN;

	$database = getenv('PRIMARY_DATABASE') ?: 'ScorecardV5';
	$db_host = getenv('DATABASE_HOST') ?: 'db';
	$db_user = getenv('DATABASE_USER') ?: 'user';
	$db_pwd = getenv('DATABASE_PASSWORD') ?: 'passw0rd';
	
	$hr_token = getenv('HEMA_RATINGS_TOKEN') ?: '';
	$hr_by_name = getenv('HEMA_RATINGS_BY_NAME') ?: '';
	$hr_by_id = getenv('HEMA_RATINGS_BY_ID') ?: '';

/* Defnitions */
	define("DEPLOYMENT", $deployment);

	define("PRIMARY_DATABASE", $database);
	define("DATABASE_HOST", $db_host);
	define("DATABASE_USER", $db_user);
	define("DATABASE_PASSWORD", $db_pwd);

	define("HEMA_RATINGS_TOKEN", $hr_token);
	define("HEMA_RATINGS_BY_NAME", $hr_by_name);
	define("HEMA_RATINGS_BY_ID", $hr_by_id);

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
